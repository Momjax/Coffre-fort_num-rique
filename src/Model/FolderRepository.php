<?php
// src/Model/FolderRepository.php

namespace App\Model;

use Medoo\Medoo;

class FolderRepository
{
    private Medoo $db;

    public function __construct(Medoo $db)
    {
        $this->db = $db;
    }

    public function listFolders(?int $parentId = null): array
    {
        $where = $parentId === null 
            ? ['parent_id' => null] 
            : ['parent_id' => $parentId];
        
        return $this->db->select('folders', '*', array_merge($where, ['ORDER' => ['name' => 'ASC']]));
    }

    public function find(int $id): ?array
    {
        $folder = $this->db->get('folders', '*', ['id' => $id]);
        return $folder ?: null;
    }

    public function create(array $data): int
    {
        $this->db->insert('folders', $data);
        return (int)$this->db->id();
    }

    public function update(int $id, array $data): void
    {
        $this->db->update('folders', $data, ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->db->delete('folders', ['id' => $id]);
    }

    public function getFilesInFolder(?int $folderId): array
    {
        $where = $folderId === null 
            ? ['folder_id' => null] 
            : ['folder_id' => $folderId];
        
        return $this->db->select('files', '*', $where);
    }

    public function countFolders(?int $parentId = null): int
    {
        $where = $parentId === null 
            ? ['parent_id' => null] 
            : ['parent_id' => $parentId];
        
        return $this->db->count('folders', $where);
    }
}
