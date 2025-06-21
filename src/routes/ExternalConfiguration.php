<?php
require_once __DIR__ . '/../controllers/ExternalConfigurationController.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$controller = new ExternalConfigurationController($conn);

if ($method === 'GET' && $uri === '/api/v1/configurations') {
    $controller->getConfiguration();
    exit;
}

if ($method === 'GET' && $uri === '/api/v1/configurations/get-external') {
    $controller->getExternalConfiguration();
    exit;
}

if ($method === 'POST' && $uri === '/api/v1/configurations/store') {
    $controller->updateConfiguration();
    exit;
}
