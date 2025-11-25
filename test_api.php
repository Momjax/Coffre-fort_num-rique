<?php
/**
 * Script de test rapide pour l'API Coffre-fort Numérique
 * Usage: php test_api.php
 */

$baseUrl = 'http://localhost';

echo "=== Test de l'API Coffre-fort Numérique ===\n\n";

// Test 1: Créer un dossier
echo "1. Création d'un dossier...\n";
$ch = curl_init("$baseUrl/folders");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'name' => 'Test Folder ' . date('Y-m-d H:i:s'),
    'parent_id' => null
]));
$response = curl_exec($ch);
$folderData = json_decode($response, true);
echo "   ✓ Dossier créé (ID: {$folderData['id']})\n\n";

// Test 2: Lister les dossiers
echo "2. Liste des dossiers...\n";
$ch = curl_init("$baseUrl/folders");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$folders = json_decode($response, true);
echo "   ✓ " . count($folders) . " dossier(s) trouvé(s)\n\n";

// Test 3: Statistiques
echo "3. Statistiques...\n";
$ch = curl_init("$baseUrl/stats");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$stats = json_decode($response, true);
$sizeMB = round($stats['total_size_bytes'] / 1024 / 1024, 2);
$quotaMB = round($stats['quota_bytes'] / 1024 / 1024, 2);
echo "   ✓ Espace utilisé: {$sizeMB} Mo / {$quotaMB} Mo\n\n";

// Test 4: Lister les fichiers
echo "4. Liste des fichiers...\n";
$ch = curl_init("$baseUrl/files");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$files = json_decode($response, true);
echo "   ✓ " . count($files) . " fichier(s) trouvé(s)\n\n";

// Test 5: Lister les partages
echo "5. Liste des partages...\n";
$ch = curl_init("$baseUrl/shares");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$shares = json_decode($response, true);
echo "   ✓ " . count($shares) . " partage(s) actif(s)\n\n";

echo "=== Tests terminés ===\n";
echo "\nPour tester l'upload de fichiers, utilisez:\n";
echo "  - Postman (collection fournie dans postman_collection.json)\n";
echo "  - cURL: curl -F 'file=@votrefichier.pdf' -F 'encrypt=1' $baseUrl/files\n";
echo "\nInterface web: $baseUrl/index.html\n";
