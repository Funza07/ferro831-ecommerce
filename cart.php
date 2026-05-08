<?php
require_once __DIR__ . '/app/bootstrap.php';
$controller = new CartController();
if (isset($_POST['add_to_cart'])) {
    $controller->add();
} elseif (isset($_POST['update_cart'])) {
    $controller->update();
} elseif (isset($_GET['remove'])) {
    $controller->remove();
} else {
    $controller->index();
}
