<?php
require_once __DIR__ . '/../controllers/ZoneController.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


$zoneController = new ZoneController($conn);

// Match and exit immediately if matched
if ($method === 'GET' && $uri === '/api/v1/zone/list') {
    $zoneController->getAllZones();
    exit;
}

if ($method === 'GET' && $uri === '/api/v1/zone/check') {
    $zoneController->zonesCheck();
    exit;
}

http_response_code(404);
echo json_encode(["status" => false, "message" => "Route not found"]);
