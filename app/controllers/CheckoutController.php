<?php
class CheckoutController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            $this->view('checkout/empty');
            return;
        }

        $prefill_name = '';
        $prefill_email = '';
        $prefill_phone = '';
        $prefill_address = '';
        $saved_addresses = [];
        $default_address_id = 0;

        if (isset($_SESSION['user_id'])) {
            $user_id = (int)$_SESSION['user_id'];
            $userModel = $this->model('User');

            $prefill_name = $_SESSION['user_name'] ?? '';
            $prefill_email = $_SESSION['user_email'] ?? '';

            $user_data = $userModel->getCheckoutProfile($user_id);
            if ($user_data) {
                $prefill_name = $user_data['name'] ?? $prefill_name;
                $prefill_email = $user_data['email'] ?? $prefill_email;
                $prefill_phone = $user_data['phone'] ?? '';
                $prefill_address = $user_data['address'] ?? '';
            }

            $saved_addresses = $userModel->getSavedAddresses($user_id);
            foreach ($saved_addresses as $saved_address) {
                if ((int)$saved_address['is_default'] === 1) {
                    $default_address_id = (int)$saved_address['id'];
                    break;
                }
            }

            if (!empty($saved_addresses) && $default_address_id === 0) {
                $default_address_id = (int)$saved_addresses[0]['id'];
            }

            foreach ($saved_addresses as $saved_address) {
                if ((int)$saved_address['id'] === $default_address_id) {
                    $prefill_name = $saved_address['full_name'];
                    $prefill_phone = $saved_address['phone'];
                    $prefill_address = $this->formatAddress($saved_address);
                    break;
                }
            }
        }

        $cart = $this->model('Cart')->getItems();
        $cart_items = $cart['items'];
        $grand_total = $cart['grand_total'];

        $this->view('checkout/index', compact(
            'prefill_name',
            'prefill_email',
            'prefill_phone',
            'prefill_address',
            'saved_addresses',
            'default_address_id',
            'cart_items',
            'grand_total'
        ));
    }

    public function placeOrder()
    {
        if (!isset($_POST['place_order'])) {
            $this->redirect('/ferro831/index.php');
        }

        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            $message = 'Your cart is empty.';
            $this->view('orders/error', compact('message'));
            return;
        }

        $customer = [
            'name' => trim($_POST['customer_name'] ?? ''),
            'email' => trim($_POST['customer_email'] ?? ''),
            'phone' => trim($_POST['customer_phone'] ?? ''),
            'address' => trim($_POST['customer_address'] ?? ''),
        ];

        $selected_address_id = (int)($_POST['selected_address_id'] ?? 0);
        $user_id_for_order = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        if ($user_id_for_order !== null && $selected_address_id > 0) {
            $userModel = $this->model('User');
            $saved_address = $userModel->getAddressForUser($selected_address_id, $user_id_for_order);

            if (!$saved_address) {
                $message = 'Invalid address selection.';
                $detail = 'Please choose a valid saved address and try again.';
                $back_url = '/ferro831/checkout.php';
                $back_label = 'Back to Checkout';
                $this->view('orders/error', compact('message', 'detail', 'back_url', 'back_label'));
                return;
            }

            $customer['name'] = $saved_address['full_name'];
            $customer['phone'] = $saved_address['phone'];
            $customer['address'] = $this->formatAddress($saved_address);
            $customer['email'] = trim((string)($_SESSION['user_email'] ?? ''));

            if ($customer['email'] === '') {
                $customer['email'] = $userModel->getEmailById($user_id_for_order);
            }
        }

        $result = $this->model('Order')->placeFromCart($_SESSION['cart'], $customer, $user_id_for_order);

        if ($result['success']) {
            $_SESSION['cart'] = [];
            $order_id = $result['order_id'];
            $customer_name = $result['customer_name'];
            $this->view('orders/success', compact('order_id', 'customer_name'));
            return;
        }

        $message = 'Could not place order.';
        $detail = $result['error'];
        $back_url = '/ferro831/cart.php';
        $back_label = 'Back to Cart';
        $this->view('orders/error', compact('message', 'detail', 'back_url', 'back_label'));
    }

    private function formatAddress(array $address)
    {
        return $address['address_line'] . ', ' . $address['city'] . ', ' . $address['state'] . ' - ' . $address['pincode'];
    }
}
