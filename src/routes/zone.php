<?php
require_once __DIR__ . '/../controllers/ZoneController.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Init controller
$zoneController = new ZoneController();

// Match and exit immediately if matched
if ($method === 'GET' && $uri === '/zone/list') {
    $zoneController->getAllZones();
    exit;
}

if ($method === 'GET' && $uri === '/zone/check') {
    $zoneController->zonesCheck();
    exit;
}
