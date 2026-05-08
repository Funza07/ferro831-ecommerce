<?php
require_once __DIR__ . '/app/bootstrap.php';
$controller = new UserController();
if (isset($_POST['update_address'])) {
    $controller->updateAddress();
} else {
    $controller->editAddress();
}
