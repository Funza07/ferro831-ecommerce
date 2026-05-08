<?php
require_once __DIR__ . '/app/bootstrap.php';
$controller = new UserController();
if (isset($_POST['save_profile'])) {
    $controller->updateProfile();
} else {
    $controller->profile();
}
