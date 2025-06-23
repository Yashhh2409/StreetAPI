<?php

require_once __DIR__ . '/../../db.php';

class BannerController
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
        header('Content-Type: application/json');
    }

    public function list($storeId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM banners WHERE created_by = 'store' AND data = ? ORDER BY id DESC");
        $stmt->bind_param("i", $storeId);
        $stmt->execute();
        $result = $stmt->get_result();

        $banners = [];
        while ($row = $result->fetch_assoc()) {
            $banners[] = $row;
        }

        echo json_encode($banners);
    }

    public function store()
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Image is required']);
            return;
        }

        $translations = json_decode($_POST['translations'] ?? '[]', true);
        if (!$translations || !isset($translations[0]['value'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid translations']);
            return;
        }

        $title = $translations[0]['value'];
        $default_link = $_POST['default_link'] ?? null;
        $store_id = $_POST['store_id'];
        $zone_id = $_POST['zone_id'];
        $module_id = $_POST['module_id'];

        $imageName = date('Y-m-d-His') . '-' . uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../../uploads/banner/' . $imageName);

        $now = date('Y-m-d H:i:s');
        $stmt = $this->conn->prepare("INSERT INTO banners (title, type, image, status, data, created_at, updated_at, zone_id, module_id, featured, default_link, created_by) VALUES (?, 'store_wise', ?, 1, ?, ?, ?, ?, ?, 0, ?, 'store')");
        $stmt->bind_param("ssisssiss", $title, $imageName, $store_id, $now, $now, $zone_id, $module_id, $default_link);
        $stmt->execute();

        $bannerId = $stmt->insert_id;

        foreach ($translations as $tr) {
            $stmtT = $this->conn->prepare("INSERT INTO translations (translationable_type, translationable_id, locale, `key`, value, created_at, updated_at) VALUES ('App\\\\Models\\\\Banner', ?, ?, ?, ?, ?, ?)");
            $stmtT->bind_param("isssss", $bannerId, $tr['locale'], $tr['key'], $tr['value'], $now, $now);
            $stmtT->execute();
        }

        echo json_encode(['message' => 'Banner added successfully']);
    }

    public function edit($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM banners WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $banner = $stmt->get_result()->fetch_assoc();

        $stmtT = $this->conn->prepare("SELECT * FROM translations WHERE translationable_type = 'App\\\\Models\\\\Banner' AND translationable_id = ?");
        $stmtT->bind_param("i", $id);
        $stmtT->execute();
        $translations = $stmtT->get_result()->fetch_all(MYSQLI_ASSOC);

        $banner['translations'] = $translations;
        echo json_encode($banner);
    }

    public function update()
    {
        $id = $_POST['id'];
        $default_link = $_POST['default_link'] ?? null;
        $translations = json_decode($_POST['translations'], true);
        $title = $translations[0]['value'];
        $now = date('Y-m-d H:i:s');

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $imageName = date('Y-m-d-His') . '-' . uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../../uploads/banner/' . $imageName);

            $stmt = $this->conn->prepare("UPDATE banners SET title = ?, image = ?, default_link = ?, updated_at = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $title, $imageName, $default_link, $now, $id);
        } else {
            $stmt = $this->conn->prepare("UPDATE banners SET title = ?, default_link = ?, updated_at = ? WHERE id = ?");
            $stmt->bind_param("sssi", $title, $default_link, $now, $id);
        }
        $stmt->execute();

        foreach ($translations as $tr) {
            $stmtT = $this->conn->prepare("REPLACE INTO translations (translationable_type, translationable_id, locale, `key`, value, created_at, updated_at) VALUES ('App\\\\Models\\\\Banner', ?, ?, ?, ?, ?, ?)");
            $stmtT->bind_param("isssss", $id, $tr['locale'], $tr['key'], $tr['value'], $now, $now);
            $stmtT->execute();
        }

        echo json_encode(['message' => 'Banner updated successfully']);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM translations WHERE translationable_type = 'App\\\\Models\\\\Banner' AND translationable_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $stmt2 = $this->conn->prepare("DELETE FROM banners WHERE id = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();

        echo json_encode(['message' => 'Banner deleted successfully']);
    }

    public function status($id, $status)
    {
        $stmt = $this->conn->prepare("UPDATE banners SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $id);
        $stmt->execute();

        echo json_encode(['message' => 'Banner status updated']);
    }
}
?>
