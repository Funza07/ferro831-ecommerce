<?php
require_once __DIR__ . '/../app/bootstrap.php';
$controller = new AdminController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_variant'])) {
        $controller->addVariant();
    } elseif (isset($_POST['update_variant'])) {
        $controller->updateVariant();
    } elseif (isset($_POST['toggle_variant_status'])) {
        $controller->toggleVariantStatus();
    } elseif (isset($_POST['delete_variant'])) {
        $controller->deleteVariant();
    } else {
        $controller->manageVariants();
    }
} else {
    $controller->manageVariants();
}
