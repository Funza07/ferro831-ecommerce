<?php
class CartController extends Controller
{
    public function index()
    {
        $cartModel = $this->model('Cart');
        $page_notice = $cartModel->normalize();
        $cart_notice = $_SESSION['cart_notice'] ?? '';
        unset($_SESSION['cart_notice']);

        if ($page_notice !== '') {
            $cart_notice = trim($cart_notice . ' ' . $page_notice);
        }

        $cartModel->ensureCart();
        $cart_items = $cartModel->getCartItems($_SESSION['cart']);
        $totals = $cartModel->calculateTotals($cart_items);
        $grand_total = $totals['grand_total'];
        $this->view('cart/index', compact('cart_items', 'grand_total', 'cart_notice'));
    }

    public function add()
    {
        $cartModel = $this->model('Cart');
        $notice = $cartModel->add(
            (int)($_POST['product_id'] ?? 0),
            (int)($_POST['quantity'] ?? 1),
            (int)($_POST['variant_id'] ?? 0)
        );
        $isAjax = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest'
            || strpos(strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json') !== false;

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            if ($notice !== '') {
                echo json_encode(['success' => false, 'message' => $notice]);
            } else {
                echo json_encode(['success' => true, 'message' => 'Added to cart']);
            }
            return;
        }

        if ($notice !== '') {
            $_SESSION['cart_notice'] = $notice;
        }
        $this->redirect('/ferro831/cart.php');
    }

    public function update()
    {
        $cartModel = $this->model('Cart');
        $notice = $cartModel->update(is_array($_POST['quantities'] ?? null) ? $_POST['quantities'] : []);
        if ($notice !== '') {
            $_SESSION['cart_notice'] = $notice;
        }
        $this->redirect('/ferro831/cart.php');
    }

    public function remove()
    {
        if (isset($_GET['remove'])) {
            $remove = (string)$_GET['remove'];
            unset($_SESSION['cart'][$remove], $_SESSION['cart'][(int)$remove]);
        }
        $this->redirect('/ferro831/cart.php');
    }
}
