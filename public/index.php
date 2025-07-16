<?php
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Database;
use App\Controllers\NewsController;
use App\Controllers\AuthController;
use App\Helpers\AuthMiddleware;
use App\Controllers\AnnouncementController;
use App\Controllers\CategoryController;
use App\Controllers\UserController;
use App\Controllers\MediaController;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$db = (new Database())->connect();

$uri = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$method = $_SERVER['REQUEST_METHOD'];

if ($uri[0] === 'login' && $method === 'POST') {
    $controller = new AuthController($db);
    $controller->login();
    return;
}
if ($uri[0] === 'refresh-token' && $method === 'POST') {
    $controller = new AuthController($db);
    $controller->refreshToken();
    return;
}
if ($uri[0] === 'news' && $method === 'GET') {
    $controller = new NewsController($db);
    $controller->getAll();
    return;
}

if ($uri[0] === 'news' && $method === 'POST') {
    AuthMiddleware::authorize();
    $controller = new NewsController($db);
    $controller->create();
    return;
}

if ($uri[0] === 'news' && $method === 'PUT' && isset($uri[1])) {
    AuthMiddleware::authorize();
    $controller = new NewsController($db);
    $controller->update($uri[1]);
    return;
}
if ($uri[0] === 'news' && $method === 'DELETE' && isset($uri[1])) {
    AuthMiddleware::authorize();
    $controller = new NewsController($db);
    $controller->delete($uri[1]);
    return;
}

if ($uri[0] === 'announcements' && $method === 'GET') {
    $controller = new AnnouncementController($db);
    $controller->getAll();
    return;
}

if ($uri[0] === 'announcements' && $method === 'POST') {
    AuthMiddleware::authorize();
    $controller = new AnnouncementController($db);
    $controller->create();
    return;
}

if ($uri[0] === 'announcements' && $method === 'PUT' && isset($uri[1])) {
    AuthMiddleware::authorize();
    $controller = new AnnouncementController($db);
    $controller->update($uri[1]);
    return;
}

if ($uri[0] === 'announcements' && $method === 'DELETE' && isset($uri[1])) {
    AuthMiddleware::authorize();
    $controller = new AnnouncementController($db);
    $controller->delete($uri[1]);
    return;
}

if ($uri[0] === 'categories' && $method === 'GET') {
    $controller = new CategoryController($db);
    $controller->getAll();
    return;
}

if ($uri[0] === 'categories' && $method === 'POST') {
    AuthMiddleware::authorize();
    $controller = new CategoryController($db);
    $controller->create();
    return;
}

if ($uri[0] === 'categories' && $method === 'PUT' && isset($uri[1])) {
    AuthMiddleware::authorize();
    $controller = new CategoryController($db);
    $controller->update($uri[1]);
    return;
}

if ($uri[0] === 'categories' && $method === 'DELETE' && isset($uri[1])) {
    AuthMiddleware::authorize();
    $controller = new CategoryController($db);
    $controller->delete($uri[1]);
    return;
}


if ($uri[0] === 'users' && $method === 'GET') {
    AuthMiddleware::authorize();
    $controller = new UserController($db);
    $controller->getAll();
    return;
}


if ($uri[0] === 'users' && $method === 'POST') {
    AuthMiddleware::authorize();
    $controller = new UserController($db);
    $controller->create();
    return;
}


if ($uri[0] === 'users' && $method === 'PUT' && isset($uri[1])) {
    AuthMiddleware::authorize();
    $controller = new UserController($db);
    $controller->update($uri[1]);
    return;
}


if ($uri[0] === 'users' && $method === 'DELETE' && isset($uri[1])) {
    AuthMiddleware::authorize();
    $controller = new UserController($db);
    $controller->delete($uri[1]);
    return;
}


if ($uri[0] === 'media' && $method === 'GET') {
    AuthMiddleware::authorize();
    $controller = new MediaController($db);
    $controller->getAll();
    return;
}


if ($uri[0] === 'media' && $method === 'POST') {
    AuthMiddleware::authorize();
    $controller = new MediaController($db);
    $controller->upload();
    return;
}


if ($uri[0] === 'media' && $method === 'DELETE' && isset($uri[1])) {
    AuthMiddleware::authorize();
    $controller = new MediaController($db);
    $controller->delete($uri[1]);
    return;
}

http_response_code(404);
echo json_encode([
    'status' => 'error',
    'message' => 'Endpoint tidak ditemukan'
]);
