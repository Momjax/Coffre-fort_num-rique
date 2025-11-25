<?php
// src/Controller/ShareController.php

namespace App\Controller;

use App\Model\ShareRepository;
use App\Model\FileRepository;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ShareController
{
    private ShareRepository $shares;
    private FileRepository $files;

    public function __construct(Medoo $db)
    {
        $this->shares = new ShareRepository($db);
        $this->files = new FileRepository($db);
    }

    // GET /shares
    public function list(Request $request, Response $response): Response
    {
        $data = $this->shares->listShares();

        $payload = json_encode($data, JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    // POST /shares
    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        if (!isset($data['file_id'])) {
            $response->getBody()->write(json_encode(['error' => 'file_id is required']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $fileId = (int)$data['file_id'];
        $file = $this->files->find($fileId);

        if (!$file) {
            $response->getBody()->write(json_encode(['error' => 'File not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $token = $this->shares->generateToken();
        $expiresAt = isset($data['expires_at']) ? $data['expires_at'] : null;
        $maxDownloads = isset($data['max_downloads']) ? (int)$data['max_downloads'] : 0;

        $shareId = $this->shares->create([
            'file_id' => $fileId,
            'token' => $token,
            'expires_at' => $expiresAt,
            'max_downloads' => $maxDownloads,
            'downloads' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $response->getBody()->write(json_encode([
            'message' => 'Share created',
            'id' => $shareId,
            'token' => $token,
            'url' => '/s/' . $token
        ], JSON_PRETTY_PRINT));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    // GET /s/{token} - Page publique de téléchargement
    public function showPublic(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'];
        $share = $this->shares->findByToken($token);

        if (!$share) {
            $response->getBody()->write('Lien de partage invalide');
            return $response->withStatus(404);
        }

        if (!$this->shares->canDownload($share)) {
            $response->getBody()->write('Ce lien de partage a expiré ou a atteint sa limite de téléchargements');
            return $response->withStatus(410);
        }

        $file = $this->files->find($share['file_id']);
        
        if (!$file) {
            $response->getBody()->write('Fichier introuvable');
            return $response->withStatus(404);
        }

        // Afficher une page HTML avec Bootstrap
        $html = $this->renderPublicSharePage($share, $file, $token);
        $response->getBody()->write($html);
        
        return $response->withHeader('Content-Type', 'text/html')->withStatus(200);
    }

    // GET /s/{token}/download - Téléchargement via lien public
    public function downloadPublic(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'];
        $share = $this->shares->findByToken($token);

        if (!$share) {
            $response->getBody()->write('Lien de partage invalide');
            return $response->withStatus(404);
        }

        if (!$this->shares->canDownload($share)) {
            $response->getBody()->write('Ce lien de partage a expiré ou a atteint sa limite de téléchargements');
            return $response->withStatus(410);
        }

        $file = $this->files->find($share['file_id']);
        
        if (!$file) {
            $response->getBody()->write('Fichier introuvable');
            return $response->withStatus(404);
        }

        // Incrémenter le compteur de téléchargements
        $this->shares->incrementDownloads($share['id']);

        // Rediriger vers l'endpoint de téléchargement standard
        return $response
            ->withHeader('Location', '/files/' . $file['id'] . '/download')
            ->withStatus(302);
    }

    // DELETE /shares/{id}
    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $share = $this->shares->find($id);

        if (!$share) {
            $response->getBody()->write(json_encode(['error' => 'Share not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $this->shares->delete($id);

        $response->getBody()->write(json_encode(['message' => 'Share deleted']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    private function renderPublicSharePage(array $share, array $file, string $token): string
    {
        $filename = htmlspecialchars($file['filename']);
        $size = round($file['size'] / 1024 / 1024, 2);
        $downloads = $share['downloads'];
        $maxDownloads = $share['max_downloads'];
        $expiresAt = $share['expires_at'] ? date('d/m/Y H:i', strtotime($share['expires_at'])) : 'Aucune';
        
        $downloadInfo = '';
        if ($maxDownloads > 0) {
            $downloadInfo = "Téléchargements : {$downloads} / {$maxDownloads}";
        } else {
            $downloadInfo = "Téléchargements : {$downloads}";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Télécharger {$filename} - Coffre-fort Numérique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .share-card {
            max-width: 500px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .file-icon {
            font-size: 4rem;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card share-card">
            <div class="card-body text-center p-5">
                <i class="bi bi-file-earmark-arrow-down file-icon mb-4"></i>
                <h1 class="card-title h3 mb-3">{$filename}</h1>
                
                <div class="mb-4">
                    <p class="text-muted mb-2">
                        <i class="bi bi-hdd"></i> Taille : {$size} Mo
                    </p>
                    <p class="text-muted mb-2">
                        <i class="bi bi-download"></i> {$downloadInfo}
                    </p>
                    <p class="text-muted">
                        <i class="bi bi-clock"></i> Expire : {$expiresAt}
                    </p>
                </div>
                
                <a href="/s/{$token}/download" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-download me-2"></i>
                    Télécharger le fichier
                </a>
                
                <p class="text-muted small mt-4 mb-0">
                    <i class="bi bi-shield-check"></i> Lien sécurisé
                </p>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-white">
                <i class="bi bi-lock-fill me-2"></i>
                Coffre-fort Numérique - Partage sécurisé
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
HTML;
    }
}
