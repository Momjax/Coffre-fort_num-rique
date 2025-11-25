<?php
// Afficher toutes les erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Test de chargement...\n\n";

try {
    require __DIR__ . '/../vendor/autoload.php';
    echo "✅ Autoload OK\n";
} catch (Exception $e) {
    echo "❌ Erreur autoload: " . $e->getMessage() . "\n";
    exit;
}

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    echo "✅ .env chargé\n";
} catch (Exception $e) {
    echo "❌ Erreur .env: " . $e->getMessage() . "\n";
    exit;
}

try {
    $database = new Medoo\Medoo([
        'type' => $_ENV['DB_TYPE'] ?? 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'database' => $_ENV['DB_NAME'] ?? 'file_vault',
        'username' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? '',
        'port' => $_ENV['DB_PORT'] ?? 3306,
    ]);
    echo "✅ Base de données OK\n";
} catch (Exception $e) {
    echo "❌ Erreur DB: " . $e->getMessage() . "\n";
    exit;
}

try {
    $app = Slim\Factory\AppFactory::create();
    echo "✅ Slim App OK\n";
} catch (Exception $e) {
    echo "❌ Erreur Slim: " . $e->getMessage() . "\n";
    exit;
}

try {
    $fileController = new App\Controller\FileController($database);
    echo "✅ FileController OK\n";
} catch (Exception $e) {
    echo "❌ Erreur FileController: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit;
}

try {
    $folderController = new App\Controller\FolderController($database);
    echo "✅ FolderController OK\n";
} catch (Exception $e) {
    echo "❌ Erreur FolderController: " . $e->getMessage() . "\n";
    exit;
}

try {
    $shareController = new App\Controller\ShareController($database);
    echo "✅ ShareController OK\n";
} catch (Exception $e) {
    echo "❌ Erreur ShareController: " . $e->getMessage() . "\n";
    exit;
}

echo "\n🎉 Tous les tests passent !\n";
echo "\nSi vous voyez ce message, le problème vient de la configuration Slim.\n";
