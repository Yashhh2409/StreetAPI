<?php

require_once __DIR__ . '/../controllers/ConfigController.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


$ConfigController = new ConfigController($conn);

// Match and exit immediately if matched
if ($method === 'GET' && $uri === '/api/v1/offline_payment_method_list') {
    echo $ConfigController->offline_payment_method_list();
    exit;
}

