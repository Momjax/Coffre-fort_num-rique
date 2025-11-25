<?php
// src/Service/EncryptionService.php

namespace App\Service;

class EncryptionService
{
    private string $cipher = 'aes-256-cbc';
    private string $key;

    public function __construct(string $key)
    {
        // La clé doit faire 32 bytes pour AES-256
        $this->key = hash('sha256', $key, true);
    }

    /**
     * Chiffre le contenu d'un fichier
     */
    public function encryptFile(string $sourcePath, string $destinationPath): bool
    {
        $data = file_get_contents($sourcePath);
        if ($data === false) {
            return false;
        }

        $encrypted = $this->encrypt($data);
        return file_put_contents($destinationPath, $encrypted) !== false;
    }

    /**
     * Déchiffre le contenu d'un fichier
     */
    public function decryptFile(string $sourcePath, string $destinationPath): bool
    {
        $data = file_get_contents($sourcePath);
        if ($data === false) {
            return false;
        }

        $decrypted = $this->decrypt($data);
        if ($decrypted === false) {
            return false;
        }

        return file_put_contents($destinationPath, $decrypted) !== false;
    }

    /**
     * Chiffre des données
     */
    public function encrypt(string $data): string
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt($data, $this->cipher, $this->key, 0, $iv);
        
        // On stocke l'IV avec les données chiffrées (séparés par ::)
        return base64_encode($iv . '::' . $encrypted);
    }

    /**
     * Déchiffre des données
     */
    public function decrypt(string $data): string|false
    {
        $decoded = base64_decode($data);
        if ($decoded === false) {
            return false;
        }

        $parts = explode('::', $decoded, 2);
        if (count($parts) !== 2) {
            return false;
        }

        [$iv, $encrypted] = $parts;
        
        return openssl_decrypt($encrypted, $this->cipher, $this->key, 0, $iv);
    }
}
