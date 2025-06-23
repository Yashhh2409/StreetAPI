<?php

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../helpers/addon_helper.php';

class SubscriptionController
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
        header('Content-Type: application/json');
    }

    public function package_view($request)
    {
        $module_id = isset($request['module_id']) ? $request['module_id'] : null;
        $module_type = 'all';

        if ($module_id) {
            $query = "SELECT * FROM modules WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $module_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $module = $result->fetch_assoc();

            if ($module && $module['module_type'] === 'rental' && addon_published_status('Rental')) {
                $module_type = 'rental';
            }
        }

        $query = "SELECT * FROM subscription_packages WHERE status = 1 AND module_type = ? ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $module_type);
        $stmt->execute();
        $result = $stmt->get_result();

        $packages = [];
        while ($row = $result->fetch_assoc()) {
            $packages[] = $row;
        }

        echo json_encode(['packages' => $packages], JSON_UNESCAPED_UNICODE);
    }

    public function business_plan()
        {
            $requestBody = json_decode(file_get_contents("php://input"), true);

            if (!isset($requestBody['store_id']) || !isset($requestBody['business_plan'])) {
                http_response_code(403);
                echo json_encode(['error' => 'store_id and business_plan are required']);
                exit;
            }

            $storeId = $requestBody['store_id'];
            $businessPlan = $requestBody['business_plan'];
            $packageId = $requestBody['package_id'] ?? null;
            $now = date('Y-m-d H:i:s');

            // Fetch store
            $stmt = $this->conn->prepare("SELECT * FROM stores WHERE id = ?");
            $stmt->bind_param("i", $storeId);
            $stmt->execute();
            $result = $stmt->get_result();
            $store = $result->fetch_assoc();

            if (!$store) {
                http_response_code(404);
                echo json_encode(['error' => 'Store not found']);
                exit;
            }

            if ($businessPlan === 'subscription' && $packageId) {
                $stmtPkg = $this->conn->prepare("SELECT * FROM subscription_packages WHERE id = ?");
                $stmtPkg->bind_param("i", $packageId);
                $stmtPkg->execute();
                $resultPkg = $stmtPkg->get_result();
                $package = $resultPkg->fetch_assoc();

                if (!$package) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Package not found']);
                    exit;
                }

                $expiryDate = date('Y-m-d', strtotime("+{$package['validity']} days"));

                // Extra values
                $status = 1;
                $is_trial = 0;
                $total_package_renewed = 0;
                $renewed_at = $now;
                $is_canceled = 0;
                $created_at = $now;
                $updated_at = $now;

                $stmtInsert = $this->conn->prepare("
                    INSERT INTO store_subscriptions (
                        package_id, store_id, expiry_date, validity,
                        max_order, max_product, pos, mobile_app,
                        chat, review, self_delivery, status,
                        is_trial, total_package_renewed, renewed_at,
                        is_canceled, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmtInsert->bind_param(
                    "iisisiiiiiiiiissss",
                    $packageId,
                    $storeId,
                    $expiryDate,
                    $package['validity'],
                    $package['max_order'],
                    $package['max_product'],
                    $package['pos'],
                    $package['mobile_app'],
                    $package['chat'],
                    $package['review'],
                    $package['self_delivery'],
                    $status,
                    $is_trial,
                    $total_package_renewed,
                    $renewed_at,
                    $is_canceled,
                    $created_at,
                    $updated_at
                );

                $stmtInsert->execute();

                // Update store
                $stmtUpdate = $this->conn->prepare("UPDATE stores SET store_business_model = 'subscription', package_id = ?, updated_at = ? WHERE id = ?");
                $stmtUpdate->bind_param("isi", $packageId, $updated_at, $storeId);
                $stmtUpdate->execute();

                echo json_encode([
                    'store_business_model' => 'subscription',
                    'logo' => $store['logo'],
                    'message' => 'Application placed successfully'
                ]);
                exit;
            }

            if ($businessPlan === 'commission') {
                $stmt1 = $this->conn->prepare("UPDATE stores SET store_business_model = 'commission', updated_at = ? WHERE id = ?");
                $stmt1->bind_param("si", $now, $storeId);
                $stmt1->execute();

                $stmt2 = $this->conn->prepare("UPDATE store_subscriptions SET status = 0 WHERE store_id = ?");
                $stmt2->bind_param("i", $storeId);
                $stmt2->execute();

                echo json_encode([
                    'store_business_model' => 'commission',
                    'logo' => $store['logo'],
                    'message' => 'Application placed successfully'
                ]);
                exit;
            }

            http_response_code(400);
            echo json_encode(['error' => 'Invalid request']);
            exit;
        }

}
