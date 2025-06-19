<?php
require_once __DIR__ . '/../controllers/HomeController.php';

$homeController = new HomeController($conn);
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($uri) {
    case '/api/v1/terms-and-conditions':
        $homeController->terms_and_conditions();
        exit;
    case '/api/v1/about-us':
        $homeController->about_us();
        exit;
    case '/api/v1/privacy-policy':
        $homeController->privacy_policy();
        exit;
    case '/api/v1/refund-policy':
        $homeController->refund_policy();
        exit;
    case '/api/v1/shipping-policy':
        $homeController->shipping_policy();
        exit;
    case '/api/v1/cancelation':
        $homeController->cancelation();
        exit;
}
