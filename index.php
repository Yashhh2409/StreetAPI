<?php
header("Content-Type: application/json");

// ✅ 1. DB connection
require_once __DIR__ . '/db.php';

// ✅ 2. Buffer output
ob_start();

// ✅ 3. Route files
require_once __DIR__ . '/src/routes/home.php';
require_once __DIR__ . '/src/routes/zone.php';

// ✅ 4. Check and send output
$output = ob_get_clean();
if (!empty($output)) {
    echo $output;
    exit; // 🔥 important to avoid double response
} else {
    http_response_code(404);
    echo json_encode(["status" => false, "message" => "Route not found"]);
    exit; // 🔥 avoid hanging requests
}
