<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Diagnostic du projet\n\n";

// Test 1 : Autoload
echo "1️⃣ Test autoload...\n";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
    echo "✅ Autoload OK\n\n";
} else {
    echo "❌ vendor/autoload.php manquant. Lancez: composer install\n";
    exit(1);
}

// Test 2 : Fichier .env
echo "2️⃣ Test fichier .env...\n";
if (file_exists(__DIR__ . '/.env')) {
    echo "✅ Fichier .env trouvé\n";
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        echo "✅ .env chargé\n\n";
    } catch (Exception $e) {
        echo "❌ Erreur .env: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "❌ Fichier .env manquant !\n";
    echo "Créez-le en copiant .env.example\n";
    exit(1);
}

// Test 3 : Variables d'environnement
echo "3️⃣ Test variables...\n";
$vars = ['DB_TYPE', 'DB_HOST', 'DB_NAME', 'DB_USER'];
foreach ($vars as $var) {
    if (isset($_ENV[$var])) {
        echo "✅ $var = " . $_ENV[$var] . "\n";
    } else {
        echo "❌ $var manquant\n";
    }
}
echo "\n";

// Test 4 : Connexion base de données
echo "4️⃣ Test connexion MySQL...\n";
try {
    $database = new Medoo\Medoo([
        'type' => $_ENV['DB_TYPE'] ?? 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'database' => $_ENV['DB_NAME'] ?? 'file_vault',
        'username' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? '',
        'port' => $_ENV['DB_PORT'] ?? 3306,
    ]);
    echo "✅ Connexion MySQL OK\n\n";
} catch (Exception $e) {
    echo "❌ Erreur MySQL: " . $e->getMessage() . "\n";
    echo "Vérifiez que MySQL est démarré et que les identifiants sont corrects dans .env\n";
    exit(1);
}

// Test 5 : Tables
echo "5️⃣ Test tables...\n";
try {
    $tables = $database->query("SHOW TABLES")->fetchAll();
    echo "✅ Tables trouvées: " . count($tables) . "\n";
    foreach ($tables as $table) {
        echo "  - " . array_values($table)[0] . "\n";
    }
    echo "\n";
    
    if (count($tables) == 0) {
        echo "⚠️  Aucune table trouvée. Lancez la migration SQL:\n";
        echo "   mysql -u root -p file_vault < database/migration_day2.sql\n\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur tables: " . $e->getMessage() . "\n";
}

// Test 6 : Contrôleurs
echo "6️⃣ Test contrôleurs...\n";
try {
    $fileController = new App\Controller\FileController($database);
    echo "✅ FileController OK\n";
} catch (Exception $e) {
    echo "❌ FileController: " . $e->getMessage() . "\n";
}

try {
    $folderController = new App\Controller\FolderController($database);
    echo "✅ FolderController OK\n";
} catch (Exception $e) {
    echo "❌ FolderController: " . $e->getMessage() . "\n";
}

try {
    $shareController = new App\Controller\ShareController($database);
    echo "✅ ShareController OK\n";
} catch (Exception $e) {
    echo "❌ ShareController: " . $e->getMessage() . "\n";
}

echo "\n🎉 Diagnostic terminé !\n";
