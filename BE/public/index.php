<?php
require __DIR__ . '/../src/bootstrap.php';

$path = '/' . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

$routes = [
    '/api/inventaris' => __DIR__ . '/../src/handlers/inventaris.php',
    // endpoint baru nanti ditambah di sini
];

if (!isset($routes[$path])) {
    json_out(['message' => 'Endpoint tidak ditemukan', 'code' => 'NOT_FOUND'], 404);
}

require $routes[$path];