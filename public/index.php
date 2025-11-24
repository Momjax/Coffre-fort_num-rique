<?php
use Slim\Factory\AppFactory;
use Medoo\Medoo;
use App\Controller\FileController;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// ---------------------------
// Charger les variables d'environnement
// ---------------------------
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// ---------------------------
// Connexion à la base
// ---------------------------
$database = new Medoo([
    'type' => $_ENV['DB_TYPE'],
    'host' => $_ENV['DB_HOST'],
    'database' => $_ENV['DB_NAME'],
    'username' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASS'],
    'port' => $_ENV['DB_PORT'] ?? 3306,
]);

// ---------------------------
// Création de l'app Slim
// ---------------------------
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// ---------------------------
// Middleware CORS
// ---------------------------
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

// ---------------------------
// Gestion d'erreurs JSON
// ---------------------------
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler(function ($request, $exception, $displayErrorDetails, $logErrors, $logErrorDetails) use ($app) {
    $payload = [
        'error' => $exception->getMessage(),
        'code' => 'SERVER_ERROR'
    ];
    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
});

// ---------------------------
// Base path si l'app est dans un sous-dossier
// ---------------------------
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = rtrim(str_ireplace('index.php', '', $scriptName), '/');
if ($basePath !== '') {
    $app->setBasePath($basePath);
}

// ---------------------------
// Contrôleur
// ---------------------------
$fileController = new FileController($database);

// ---------------------------
// Routes
// ---------------------------
$app->get('/files', [$fileController, 'list']);
$app->get('/files/{id}', [$fileController, 'show']);
$app->post('/files', [$fileController, 'upload']);
$app->delete('/files/{id}', [$fileController, 'delete']);

// Route racine
$app->get('/', function ($request, $response) {
    $response->getBody()->write(json_encode([
        'message' => 'File Vault API',
        'endpoints' => [
            'GET /files',
            'GET /files/{id}',
            'POST /files',
            'DELETE /files/{id}',
        ]
    ], JSON_PRETTY_PRINT));
    return $response->withHeader('Content-Type', 'application/json');
});

// ---------------------------
// Lancement de l'app
// ---------------------------
$app->run();
