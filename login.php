<?php
require_once __DIR__ . '/app/bootstrap.php';
$controller = new AuthController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->authenticate();
} else {
    $controller->login();
}
