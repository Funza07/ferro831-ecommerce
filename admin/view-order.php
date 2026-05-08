<?php
require_once __DIR__ . '/../app/bootstrap.php';
$controller = new AdminController();
if (isset($_POST['update_order'])) {
    $controller->updateOrderStatus();
} else {
    $controller->viewOrder();
}
