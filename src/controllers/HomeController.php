<?php
require_once __DIR__ . '/../../db.php';

class HomeController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
        header('Content-Type: application/json');
    }

   private function getLocalizedSetting($key, $locale) {
    $stmt = $this->conn->prepare("SELECT * FROM business_settings WHERE `key` = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $setting = $result->fetch_assoc();

    if (!$setting) return '';

    $value = $setting['value'];
    $id = $setting['id'];

    // Get translation if exists
    $stmt2 = $this->conn->prepare("SELECT `value` FROM translations WHERE translationable_type = 'App\\\\Models\\\\DataSetting' AND translationable_id = ? AND locale = ? AND `key` = ?");
    $stmt2->bind_param("iss", $id, $locale, $key_col);
    $key_col = 'value';
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $translation = $result2->fetch_assoc();

    return $translation ? $translation['value'] : $value;
}


    public function terms_and_conditions() {
        $locale = $_SERVER['HTTP_X_LOCALIZATION'] ?? 'en';
        echo json_encode(['status' => true, 'data' => $this->getLocalizedSetting('terms_and_conditions', $locale)]);
    }

    public function about_us() {
        $locale = $_SERVER['HTTP_X_LOCALIZATION'] ?? 'en';
        echo json_encode(['status' => true, 'data' => $this->getLocalizedSetting('about_us', $locale)]);
    }

    public function privacy_policy() {
        $locale = $_SERVER['HTTP_X_LOCALIZATION'] ?? 'en';
        echo json_encode(['status' => true, 'data' => $this->getLocalizedSetting('privacy_policy', $locale)]);
    }

    public function refund_policy() {
        $locale = $_SERVER['HTTP_X_LOCALIZATION'] ?? 'en';
        echo json_encode(['status' => true, 'data' => $this->getLocalizedSetting('refund_policy', $locale)]);
    }

    public function shipping_policy() {
        $locale = $_SERVER['HTTP_X_LOCALIZATION'] ?? 'en';
        echo json_encode(['status' => true, 'data' => $this->getLocalizedSetting('shipping_policy', $locale)]);
    }

    public function cancelation() {
        $locale = $_SERVER['HTTP_X_LOCALIZATION'] ?? 'en';
        echo json_encode(['status' => true, 'data' => $this->getLocalizedSetting('cancellation_policy', $locale)]);
    }
}
