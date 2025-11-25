<?php
// src/Controller/FolderController.php

namespace App\Controller;

use App\Model\FolderRepository;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FolderController
{
    private FolderRepository $folders;

    public function __construct(Medoo $db)
    {
        $this->folders = new FolderRepository($db);
    }

    // GET /folders
    public function list(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $parentId = isset($params['parent_id']) ? (int)$params['parent_id'] : null;
        
        $data = $this->folders->listFolders($parentId);

        $payload = json_encode($data, JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    // GET /folders/{id}
    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $folder = $this->folders->find($id);

        if (!$folder) {
            $response->getBody()->write(json_encode(['error' => 'Folder not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode($folder, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // POST /folders
    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        if (empty($data['name'])) {
            $response->getBody()->write(json_encode(['error' => 'Folder name is required']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $folderData = [
            'name' => $data['name'],
            'parent_id' => isset($data['parent_id']) ? (int)$data['parent_id'] : null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->folders->create($folderData);

        $response->getBody()->write(json_encode([
            'message' => 'Folder created',
            'id' => $id
        ], JSON_PRETTY_PRINT));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    // PUT /folders/{id}
    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $folder = $this->folders->find($id);

        if (!$folder) {
            $response->getBody()->write(json_encode(['error' => 'Folder not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $data = $request->getParsedBody();
        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['parent_id'])) {
            $updateData['parent_id'] = $data['parent_id'] ? (int)$data['parent_id'] : null;
        }

        if (empty($updateData)) {
            $response->getBody()->write(json_encode(['error' => 'No data to update']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $this->folders->update($id, $updateData);

        $response->getBody()->write(json_encode(['message' => 'Folder updated']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // DELETE /folders/{id}
    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $folder = $this->folders->find($id);

        if (!$folder) {
            $response->getBody()->write(json_encode(['error' => 'Folder not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $this->folders->delete($id);

        $response->getBody()->write(json_encode(['message' => 'Folder deleted']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // GET /folders/{id}/files
    public function getFiles(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $files = $this->folders->getFilesInFolder($id);

        $response->getBody()->write(json_encode($files, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
