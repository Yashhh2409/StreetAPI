<?php
header("Content-Type: application/json");

require_once __DIR__ . '/db.php';

ob_start();


require_once __DIR__ . '/src/routes/home.php';
require_once __DIR__ . '/src/routes/zone.php';
require_once __DIR__ . '/src/routes/config.php';
require_once __DIR__ . '/src/routes/ExternalConfiguration.php';


$output = ob_get_clean();
if (!empty($output)) {
    echo $output;
    exit; 
} else {
    http_response_code(404);
    echo json_encode(["status" => false, "message" => "Route not found"]);
    exit; 
}
