<?php
require_once __DIR__ . '/../app/bootstrap.php';
$controller = new AdminController();
if (isset($_POST['add_product'])) {
    $controller->storeProduct();
} else {
    $controller->addProduct();
}
