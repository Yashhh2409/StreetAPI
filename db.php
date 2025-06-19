<?php
$host = "31.170.162.180";
$username = "xrda3main_ajit";
$password = "6s5VTS3=Wc(@";
$db_name = "xrda3main_6ammart";

$conn = new mysqli($host, $username, $password, $db_name);


if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => false, "message" => "Database connection failed"]);
    exit;
}
