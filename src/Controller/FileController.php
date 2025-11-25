<?php
// src/Controller/FileController.php

namespace App\Controller;

use App\Model\FileRepository;
use App\Service\EncryptionService;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FileController
{
    private FileRepository $files;
    private string $uploadDir;
    private EncryptionService $encryption;

    public function __construct(Medoo $db, ?EncryptionService $encryption = null)
    {
        $this->files = new FileRepository($db);
        $this->uploadDir = __DIR__ . '/../../storage/uploads';
        
        // Clé par défaut (à changer en production, la mettre dans config)
        $encryptionKey = $_ENV['ENCRYPTION_KEY'] ?? 'changez-cette-cle-secrete-en-production';
        $this->encryption = $encryption ?? new EncryptionService($encryptionKey);
    }

    // GET /files
    public function list(Request $request, Response $response): Response
    {
        $data = $this->files->listFiles();

        $payload = json_encode($data, JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    // GET /files/{id}
    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $file = $this->files->find($id);

        if (!$file) {
            $response->getBody()->write(json_encode(['error' => 'File not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode($file, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // POST /files  (upload via form-data)
    public function upload(Request $request, Response $response): Response
    {
        $uploadedFiles = $request->getUploadedFiles();

        if (!isset($uploadedFiles['file'])) {
            $response->getBody()->write(json_encode(['error' => 'No file uploaded']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $file = $uploadedFiles['file'];

        if ($file->getError() !== UPLOAD_ERR_OK) {
            $response->getBody()->write(json_encode(['error' => 'Upload error']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $size = $file->getSize();
        $totalSize = $this->files->totalSize();
        $quota = $this->files->quotaBytes();

        if ($quota > 0 && ($totalSize + $size) > $quota) {
            $response->getBody()->write(json_encode(['error' => 'Quota exceeded']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(413);
        }

        $originalName = $file->getClientFilename();
        $mimeType = $file->getClientMediaType();
        $storedName = uniqid('f_', true) . '_' . $originalName;

        // Récupérer le folder_id et l'option de chiffrement
        $parsedBody = $request->getParsedBody();
        $folderId = isset($parsedBody['folder_id']) && $parsedBody['folder_id'] !== '' 
            ? (int)$parsedBody['folder_id'] 
            : null;
        $encrypt = isset($parsedBody['encrypt']) && $parsedBody['encrypt'] === '1';

        $targetPath = $this->uploadDir . DIRECTORY_SEPARATOR . $storedName;

        // Si chiffrement demandé
        if ($encrypt) {
            $tempPath = $this->uploadDir . DIRECTORY_SEPARATOR . 'temp_' . $storedName;
            $file->moveTo($tempPath);
            
            // Chiffrer le fichier
            if (!$this->encryption->encryptFile($tempPath, $targetPath)) {
                unlink($tempPath);
                $response->getBody()->write(json_encode(['error' => 'Encryption failed']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
            
            unlink($tempPath);
            $storedName .= '.enc'; // Marquer comme chiffré
        } else {
            $file->moveTo($targetPath);
        }

        $id = $this->files->create([
            'filename'    => $originalName,
            'stored_name' => $encrypt ? $storedName : $storedName,
            'size'        => $size,
            'mime_type'   => $mimeType,
            'folder_id'   => $folderId,
            'is_encrypted' => $encrypt ? 1 : 0,
            'uploaded_at' => date('Y-m-d H:i:s')
        ]);

        $response->getBody()->write(json_encode([
            'message' => 'File uploaded',
            'id'      => $id
        ], JSON_PRETTY_PRINT));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    // GET /files/{id}/download
    public function download(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $file = $this->files->find($id);

        if (!$file) {
            $response->getBody()->write('File not found');
            return $response->withStatus(404);
        }

        $path = $this->uploadDir . DIRECTORY_SEPARATOR . $file['stored_name'];

        if (!file_exists($path)) {
            $response->getBody()->write('File missing on disk');
            return $response->withStatus(500);
        }

        // Si le fichier est chiffré, le déchiffrer avant de l'envoyer
        $isEncrypted = isset($file['is_encrypted']) && $file['is_encrypted'] == 1;
        
        if ($isEncrypted) {
            $tempPath = $this->uploadDir . DIRECTORY_SEPARATOR . 'temp_download_' . uniqid();
            
            if (!$this->encryption->decryptFile($path, $tempPath)) {
                $response->getBody()->write('Decryption failed');
                return $response->withStatus(500);
            }
            
            $stream = fopen($tempPath, 'rb');
            $response->getBody()->write(stream_get_contents($stream));
            fclose($stream);
            unlink($tempPath);
        } else {
            $stream = fopen($path, 'rb');
            $response->getBody()->write(stream_get_contents($stream));
            fclose($stream);
        }

        return $response
            ->withHeader('Content-Type', $file['mime_type'])
            ->withHeader('Content-Disposition', 'attachment; filename="' . $file['filename'] . '"')
            ->withStatus(200);
    }

    // DELETE /files/{id}
    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $file = $this->files->find($id);

        if (!$file) {
            $response->getBody()->write(json_encode(['error' => 'File not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Supprimer le fichier sur le disque
        $path = $this->uploadDir . DIRECTORY_SEPARATOR . $file['stored_name'];
        if (file_exists($path)) {
            unlink($path);
        }

        // Supprimer en base
        $this->files->delete($id);

        $response->getBody()->write(json_encode(['message' => 'File deleted']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // GET /stats
    public function stats(Request $request, Response $response): Response
    {
        $totalSize = $this->files->totalSize();
        $quota = $this->files->quotaBytes();

        // Exercice 1: utiliser countFiles() ici si l'étudiant l’a codée
        // $count = $this->files->countFiles();

        $data = [
            'total_size_bytes' => $totalSize,
            'quota_bytes'      => $quota,
            // 'file_count'        => $count,
        ];

        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}