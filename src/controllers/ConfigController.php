<?php

require_once __DIR__ . '/../../db.php';

class ConfigController 
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function offline_payment_method_list()
    {
        $query = "SELECT * FROM offline_payment_methods WHERE status = '1'";
        $results = mysqli_query($this->conn, $query);

        if (!$results) {
            return json_encode([
                'status' => false,
                'message' => 'Database error: ' . mysqli_error($this->conn)
            ]);
        }

        $data = [];
        while ($row = mysqli_fetch_assoc($results)) {
            $data[] = $row;
        }

        return json_encode($data);
    }
}
