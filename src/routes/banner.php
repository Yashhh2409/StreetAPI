<?php

require_once __DIR__ . '/../controllers/BannerController.php';
$controller = new BannerController($conn);

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // Important: Strip query params like ?store_id=1

// Log details for debugging
error_log("Method: $method");
error_log("Full URI: " . $_SERVER['REQUEST_URI']);
error_log("Parsed URI: $uri");

// GET /api/v1/vendor/banner/list?store_id=1
if ($method === 'GET' && $uri === '/api/v1/vendor/banner/list') {
    $controller->list($_GET);
    exit;
}

// POST /api/v1/vendor/banner/store
elseif ($method === 'POST' && $uri === '/api/v1/vendor/banner/store') {
    $controller->store($_POST, $_FILES); // Pass $_FILES for image upload
    exit;
}

// GET /api/v1/vendor/banner/edit/{id}
elseif ($method === 'GET' && preg_match("#^/api/v1/vendor/banner/edit/(\d+)$#", $uri, $matches)) {
    $controller->edit($matches[1]);
    exit;
}

// POST /api/v1/vendor/banner/update
elseif ($method === 'POST' && $uri === '/api/v1/vendor/banner/update') {
    $controller->update($_POST, $_FILES); // Also allow image update
    exit;
}

// POST /api/v1/vendor/banner/delete
elseif ($method === 'POST' && $uri === '/api/v1/vendor/banner/delete') {
    if (!isset($_POST['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID is required']);
        exit;
    }
    $controller->delete($_POST['id']);
    exit;
}

// POST /api/v1/vendor/banner/status
elseif ($method === 'POST' && $uri === '/api/v1/vendor/banner/status') {
    if (!isset($_POST['id'], $_POST['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID and status are required']);
        exit;
    }
    $controller->status($_POST['id'], $_POST['status']);
    exit;
}

// Fallback if no route matched
http_response_code(404);
echo json_encode(['error' => 'Route not found']);
exit;
