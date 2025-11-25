<?php
/**
 * Exemple d'utilisation du service de chiffrement
 * Démontre comment utiliser EncryptionService indépendamment
 */

require __DIR__ . '/vendor/autoload.php';

use App\Service\EncryptionService;

// Initialiser le service avec une clé
$encryptionKey = 'ma-cle-secrete-de-32-caracteres-min';
$encryption = new EncryptionService($encryptionKey);

echo "=== Démonstration du service de chiffrement ===\n\n";

// Test 1: Chiffrer et déchiffrer du texte
echo "1. Chiffrement de texte\n";
$texteOriginal = "Ceci est un message secret !";
echo "   Texte original: $texteOriginal\n";

$texteChiffre = $encryption->encrypt($texteOriginal);
echo "   Texte chiffré: " . substr($texteChiffre, 0, 50) . "...\n";

$texteDechiffre = $encryption->decrypt($texteChiffre);
echo "   Texte déchiffré: $texteDechiffre\n";
echo "   ✓ Le texte correspond: " . ($texteOriginal === $texteDechiffre ? "OUI" : "NON") . "\n\n";

// Test 2: Chiffrer un fichier
echo "2. Chiffrement de fichier\n";

// Créer un fichier de test
$testFile = __DIR__ . '/storage/uploads/test_original.txt';
$testContent = "Contenu confidentiel à protéger\nLigne 2\nLigne 3";
file_put_contents($testFile, $testContent);
echo "   ✓ Fichier créé: $testFile\n";

// Chiffrer le fichier
$encryptedFile = __DIR__ . '/storage/uploads/test_encrypted.enc';
if ($encryption->encryptFile($testFile, $encryptedFile)) {
    echo "   ✓ Fichier chiffré: $encryptedFile\n";
    echo "   Taille originale: " . filesize($testFile) . " octets\n";
    echo "   Taille chiffrée: " . filesize($encryptedFile) . " octets\n";
} else {
    echo "   ✗ Échec du chiffrement\n";
}

// Déchiffrer le fichier
$decryptedFile = __DIR__ . '/storage/uploads/test_decrypted.txt';
if ($encryption->decryptFile($encryptedFile, $decryptedFile)) {
    echo "   ✓ Fichier déchiffré: $decryptedFile\n";
    
    $decryptedContent = file_get_contents($decryptedFile);
    echo "   ✓ Contenu identique: " . ($testContent === $decryptedContent ? "OUI" : "NON") . "\n";
} else {
    echo "   ✗ Échec du déchiffrement\n";
}

// Nettoyer
unlink($testFile);
unlink($encryptedFile);
unlink($decryptedFile);
echo "   ✓ Fichiers de test supprimés\n\n";

// Test 3: Sécurité - tentative de déchiffrement avec mauvaise clé
echo "3. Test de sécurité\n";
$wrongEncryption = new EncryptionService('mauvaise-cle-differente-32-chars');
$encrypted = $encryption->encrypt("Message secret");
$decrypted = $wrongEncryption->decrypt($encrypted);

if ($decrypted === false || $decrypted !== "Message secret") {
    echo "   ✓ Le déchiffrement avec la mauvaise clé échoue (sécurité OK)\n";
} else {
    echo "   ✗ ATTENTION: Le déchiffrement a réussi avec une mauvaise clé !\n";
}

echo "\n=== Tests terminés ===\n";
echo "\n💡 Points importants:\n";
echo "   - Le chiffrement utilise AES-256-CBC\n";
echo "   - Un IV (vecteur d'initialisation) aléatoire est généré pour chaque opération\n";
echo "   - La clé doit être conservée précieusement (dans .env)\n";
echo "   - Sans la clé, impossible de déchiffrer les fichiers\n";
echo "   - Le format chiffré: base64(iv::encrypted_data)\n";
