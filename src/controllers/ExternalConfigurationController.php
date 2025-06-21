<?php

class ExternalConfigurationController
{
    private $conn;
    public function __construct($db)
    {
        $this->conn = $db;
        header('Content-Type: application/json');
    }

    private function getSetting($key)
    {
        $stmt = $this->conn->prepare("SELECT value FROM business_settings WHERE `key` = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['value'] ?? null;
    }

    public function getConfiguration()
    {
        $business_name = $this->getSetting('business_name') ?? '6amMart';
        $logo = $this->getSetting('logo');

        $app_min_android = $this->getSetting('app_minimum_version_android');
        $app_url_android = $this->getSetting('app_url_android');
        $app_min_ios = $this->getSetting('app_minimum_version_ios');
        $app_url_ios = $this->getSetting('app_url_ios');

        $response = [
            'business_name' => $business_name,
            'logo' => $logo ? "https://yourdomain.com/storage/$logo" : "https://yourdomain.com/assets/img/default.jpg",
            'app_minimum_version_android' => $app_min_android,
            'app_url_android' => $app_url_android,
            'app_minimum_version_ios' => $app_min_ios,
            'app_url_ios' => $app_url_ios
        ];

        echo json_encode($response);
    }

    public function updateConfiguration()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $keys = [
            'drivemond_business_name',
            'drivemond_business_logo',
            'drivemond_app_url_android',
            'drivemond_app_url_ios'
        ];

        foreach ($keys as $key) {
            if (!empty($data[$key])) {
                $stmt = $this->conn->prepare("INSERT INTO external_configurations (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)");
                $stmt->bind_param("ss", $key, $data[$key]);
                $stmt->execute();
            }
        }

        echo json_encode(['status' => true, 'message' => 'Configuration updated successfully.']);
    }

    public function getExternalConfiguration()
    {
        $data = $_GET;

        if (empty($data['drivemond_base_url']) || empty($data['drivemond_token']) || empty($data['mart_token'])) {
            echo json_encode(['status' => false]);
            return;
        }

        $activation_mode = $this->getExternalConfig('activation_mode');
        $base_url = $this->getExternalConfig('drivemond_base_url');
        $token = $this->getExternalConfig('drivemond_token');
        $mart_token = $this->getExternalConfig('system_self_token');

        if (
            $activation_mode == 1 &&
            $data['drivemond_base_url'] == $base_url &&
            $data['drivemond_token'] == $token &&
            $data['mart_token'] == $mart_token
        ) {
            echo json_encode(['status' => true]);
        } else {
            echo json_encode(['status' => false]);
        }
    }

    private function getExternalConfig($key)
    {
        $stmt = $this->conn->prepare("SELECT value FROM external_configurations WHERE `key` = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['value'] ?? null;
    }
}
