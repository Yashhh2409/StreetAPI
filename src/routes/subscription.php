<?php

require_once __DIR__ . '/../controllers/SubscriptionController.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$SubscriptionController = new SubscriptionController($conn);

// Route: GET /api/v1/vendor/package-view
if ($method === 'GET' && $uri === '/api/v1/vendor/package-view') {
    $SubscriptionController->package_view($_GET);
    exit;
}

if ($method === 'POST' && $uri === '/api/v1/vendor/business_plan') {
    $SubscriptionController->business_plan($_POST);
}