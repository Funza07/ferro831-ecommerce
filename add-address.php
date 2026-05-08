<?php
require_once __DIR__ . '/app/bootstrap.php';
$controller = new UserController();
if (isset($_POST['save_address'])) {
    $controller->storeAddress();
} else {
    $controller->addAddress();
}
