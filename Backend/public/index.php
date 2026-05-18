<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Session;
use App\Controllers\AuthController;
use App\Controllers\BookController;
use App\Controllers\HomeController;
use App\Controllers\ProfileController;
use App\Controllers\ShelfController;
use App\Controllers\ApiController;

Session::start();

$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$basePath = rtrim($scriptName, '/');
if ($basePath === '') {
    define('BASE_PATH', '');
} else {
    define('BASE_PATH', $basePath);
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($basePath !== '' && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}

$uri = rtrim($uri, '/') ?: '/';

if (str_starts_with($uri, '/uploads/')) {
    $file = basename($uri);
    $path = __DIR__ . '/../storage/uploads/' . $file;

    if (!file_exists($path)) {
        http_response_code(404);
        echo 'File not found';
        exit;
    }

    $mime = mime_content_type($path) ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=86400');
    readfile($path);
    exit;
}

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/home', [HomeController::class, 'index']);

$router->get('/login', [AuthController::class, 'loginForm']);
$router->get('/signup', [AuthController::class, 'signupForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/signup', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/books', [BookController::class, 'index']);
$router->get('/books/create', [BookController::class, 'createForm']);
$router->post('/books/create', [BookController::class, 'create']);
$router->get('/books/edit', [BookController::class, 'editForm']);
$router->post('/books/update', [BookController::class, 'update']);
$router->post('/books/delete', [BookController::class, 'delete']);

$router->get('/profile', [ProfileController::class, 'index']);
$router->post('/profile/update', [ProfileController::class, 'update']);

$router->get('/shelf', [ShelfController::class, 'index']);
$router->post('/shelf/add', [ShelfController::class, 'add']);
$router->post('/shelf/remove', [ShelfController::class, 'remove']);
$router->get('/shelf/edit', [ShelfController::class, 'editForm']);
$router->post('/shelf/update', [ShelfController::class, 'update']);

$router->get('/api/books', [ApiController::class, 'books']);
$router->post('/api/register', [ApiController::class, 'register']);
$router->post('/api/login', [ApiController::class, 'login']);
$router->post('/api/logout', [ApiController::class, 'logout']);
$router->get('/api/shelf', [ApiController::class, 'shelf']);
$router->post('/api/shelf/add', [ApiController::class, 'shelfAdd']);
$router->post('/api/shelf/remove', [ApiController::class, 'shelfRemove']);
$router->post('/api/profile/update', [ApiController::class, 'profileUpdate']);
$router->post('/api/books/create', [ApiController::class, 'booksCreate']);

$router->dispatch($uri, $_SERVER['REQUEST_METHOD']);
