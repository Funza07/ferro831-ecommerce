<?php
class WishlistController extends Controller
{
    public function index()
    {
        $this->requireLogin();
        $wishlist_products = $this->model('Wishlist')->getByUserId((int)$_SESSION['user_id']);
        $this->view('user/wishlist', compact('wishlist_products'));
    }

    public function add()
    {
        $is_ajax = $this->isAjaxRequest();

        if (!isset($_SESSION['user_id'])) {
            $this->loginRequiredResponse($is_ajax);
        }

        $redirect_to = $this->buildSafeRedirect('/ferro831/index.php#products');
        $user_id = (int)$_SESSION['user_id'];
        $product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

        if ($product_id <= 0) {
            $this->failOrRedirect($is_ajax, ['success' => false, 'message' => 'Invalid product.'], 400, $redirect_to);
        }

        if (!$this->model('Product')->getById($product_id)) {
            $this->failOrRedirect($is_ajax, ['success' => false, 'message' => 'Product not found.'], 404, $redirect_to);
        }

        $this->model('Wishlist')->add($user_id, $product_id);

        if ($is_ajax) {
            $this->sendJson(['success' => true, 'action' => 'added']);
        }

        $this->redirect($redirect_to);
    }

    public function remove()
    {
        $is_ajax = $this->isAjaxRequest();

        if (!isset($_SESSION['user_id'])) {
            $this->loginRequiredResponse($is_ajax);
        }

        $redirect_to = $this->buildSafeRedirect('/ferro831/wishlist.php');
        $user_id = (int)$_SESSION['user_id'];
        $wishlist_id = (int)($_POST['wishlist_id'] ?? $_GET['wishlist_id'] ?? 0);
        $product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        $wishlistModel = $this->model('Wishlist');

        if ($wishlist_id > 0) {
            $item = $wishlistModel->getWishlistItemByIdForUser($user_id, $wishlist_id, $product_id);
            if ($item) {
                $wishlistModel->removeByWishlistId($user_id, $wishlist_id);
            }
        } elseif ($product_id > 0) {
            $wishlistModel->remove($user_id, $product_id);
        }

        if ($is_ajax) {
            $this->sendJson(['success' => true, 'action' => 'removed']);
        }

        $this->redirect($redirect_to);
    }

    public function moveToCart()
    {
        $this->requireLogin();

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $user_id = (int)$_SESSION['user_id'];
        $wishlist_id = (int)($_POST['wishlist_id'] ?? $_GET['wishlist_id'] ?? 0);
        $product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

        if ($product_id <= 0 || $wishlist_id <= 0) {
            $this->redirect('/ferro831/cart.php');
        }

        $wishlistModel = $this->model('Wishlist');
        $wishlist_item = $wishlistModel->getWishlistItemByIdForUser($user_id, $wishlist_id, $product_id);

        if (!$wishlist_item) {
            $this->redirect('/ferro831/cart.php');
        }

        $product = $this->model('Cart')->fetchProduct($product_id);
        if ($product && (int)$product['stock'] > 0) {
            $stock = (int)$product['stock'];
            $current_qty = isset($_SESSION['cart'][$product_id]) ? (int)$_SESSION['cart'][$product_id] : 0;
            $_SESSION['cart'][$product_id] = min($current_qty + 1, $stock);
            $wishlistModel->removeByWishlistId($user_id, $wishlist_id);
        }

        $this->redirect('/ferro831/cart.php');
    }

    private function isAjaxRequest()
    {
        $requested_with = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
        $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));

        return $requested_with === 'xmlhttprequest'
            || strpos($accept, 'application/json') !== false
            || isset($_POST['ajax'])
            || isset($_GET['ajax']);
    }

    private function sendJson(array $payload, $status_code = 200)
    {
        http_response_code($status_code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload);
        exit;
    }

    private function loginRequiredResponse($is_ajax)
    {
        if ($is_ajax) {
            $this->sendJson([
                'success' => false,
                'login_required' => true,
                'redirect' => '/ferro831/login.php',
            ], 401);
        }

        $this->redirect('/ferro831/login.php');
    }

    private function failOrRedirect($is_ajax, array $payload, $status_code, $redirect_to)
    {
        if ($is_ajax) {
            $this->sendJson($payload, $status_code);
        }

        $this->redirect($redirect_to);
    }

    private function buildSafeRedirect($default)
    {
        $raw_target = $_POST['redirect'] ?? $_GET['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? '';

        if ($raw_target === '') {
            return $default;
        }

        if (strpos($raw_target, '/ferro831/') === 0) {
            return $raw_target;
        }

        $parts = parse_url($raw_target);
        if (
            $parts !== false
            && isset($parts['host'], $_SERVER['HTTP_HOST'])
            && strcasecmp($parts['host'], (string)$_SERVER['HTTP_HOST']) === 0
        ) {
            $path = $parts['path'] ?? '';
            $query = isset($parts['query']) ? ('?' . $parts['query']) : '';
            if ($path !== '' && strpos($path, '/ferro831/') === 0) {
                return $path . $query;
            }
        }

        return $default;
    }
}
