<?php
// src/Model/ShareRepository.php

namespace App\Model;

use Medoo\Medoo;

class ShareRepository
{
    private Medoo $db;

    public function __construct(Medoo $db)
    {
        $this->db = $db;
    }

    public function listShares(): array
    {
        return $this->db->select('shares', '*', ['ORDER' => ['created_at' => 'DESC']]);
    }

    public function find(int $id): ?array
    {
        $share = $this->db->get('shares', '*', ['id' => $id]);
        return $share ?: null;
    }

    public function findByToken(string $token): ?array
    {
        $share = $this->db->get('shares', '*', ['token' => $token]);
        return $share ?: null;
    }

    public function create(array $data): int
    {
        $this->db->insert('shares', $data);
        return (int)$this->db->id();
    }

    public function delete(int $id): void
    {
        $this->db->delete('shares', ['id' => $id]);
    }

    public function generateToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function incrementDownloads(int $id): void
    {
        $this->db->update('shares', [
            'downloads[+]' => 1
        ], ['id' => $id]);
    }

    public function isExpired(array $share): bool
    {
        if (!$share['expires_at']) {
            return false;
        }
        return strtotime($share['expires_at']) < time();
    }

    public function canDownload(array $share): bool
    {
        if ($this->isExpired($share)) {
            return false;
        }
        
        if ($share['max_downloads'] > 0 && $share['downloads'] >= $share['max_downloads']) {
            return false;
        }
        
        return true;
    }
}
