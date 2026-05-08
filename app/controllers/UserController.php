<?php
class UserController extends Controller
{
    public function profile()
    {
        $this->requireLogin();
        $user = $this->model('User')->getById((int)$_SESSION['user_id']);
        if (!$user) {
            $this->redirect('/ferro831/logout.php');
        }

        $success_message = $_SESSION['profile_success'] ?? '';
        $error_message = $_SESSION['profile_error'] ?? '';
        unset($_SESSION['profile_success'], $_SESSION['profile_error']);

        $this->view('user/profile', compact('user', 'success_message', 'error_message'));
    }

    public function updateProfile()
    {
        $this->requireLogin();

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
        ];

        if ($data['name'] === '') {
            $_SESSION['profile_error'] = 'Name is required.';
        } elseif ($this->model('User')->updateProfile((int)$_SESSION['user_id'], $data)) {
            $_SESSION['user_name'] = $data['name'];
            $_SESSION['profile_success'] = 'Profile updated successfully.';
        } else {
            $_SESSION['profile_error'] = 'Could not update profile. Please try again.';
        }

        $this->redirect('/ferro831/profile.php');
    }

    public function addresses()
    {
        $this->requireLogin();
        $addresses = $this->model('Address')->getByUserId((int)$_SESSION['user_id']);
        $this->view('user/addresses', compact('addresses'));
    }

    public function addAddress()
    {
        $this->requireLogin();
        $error_message = $_SESSION['address_error'] ?? '';
        unset($_SESSION['address_error']);
        $this->view('user/add-address', compact('error_message'));
    }

    public function storeAddress()
    {
        $this->requireLogin();
        $data = $this->addressDataFromPost();

        if (!$this->addressDataIsValid($data)) {
            $_SESSION['address_error'] = 'All fields are required.';
            $this->redirect('/ferro831/add-address.php');
        }

        if (!$this->model('Address')->create((int)$_SESSION['user_id'], $data)) {
            $_SESSION['address_error'] = 'Could not save address. Please try again.';
            $this->redirect('/ferro831/add-address.php');
        }

        $this->redirect('/ferro831/addresses.php');
    }

    public function editAddress()
    {
        $this->requireLogin();
        $address_id = (int)($_GET['id'] ?? $_POST['address_id'] ?? 0);
        if ($address_id <= 0) {
            $this->redirect('/ferro831/addresses.php');
        }

        $address = $this->model('Address')->getByIdForUser($address_id, (int)$_SESSION['user_id']);
        if (!$address) {
            $this->redirect('/ferro831/addresses.php');
        }

        $error_message = $_SESSION['address_error'] ?? '';
        unset($_SESSION['address_error']);
        $this->view('user/edit-address', compact('address', 'address_id', 'error_message'));
    }

    public function updateAddress()
    {
        $this->requireLogin();
        $address_id = (int)($_POST['address_id'] ?? $_GET['id'] ?? 0);
        if ($address_id <= 0) {
            $this->redirect('/ferro831/addresses.php');
        }

        $data = $this->addressDataFromPost();
        if (!$this->addressDataIsValid($data)) {
            $_SESSION['address_error'] = 'All fields are required.';
            $this->redirect('/ferro831/edit-address.php?id=' . $address_id);
        }

        if (!$this->model('Address')->update($address_id, (int)$_SESSION['user_id'], $data)) {
            $_SESSION['address_error'] = 'Could not update address. Please try again.';
            $this->redirect('/ferro831/edit-address.php?id=' . $address_id);
        }

        $this->redirect('/ferro831/addresses.php');
    }

    public function deleteAddress()
    {
        $this->requireLogin();
        $address_id = (int)($_POST['address_id'] ?? $_GET['id'] ?? 0);
        if ($address_id > 0) {
            $this->model('Address')->delete($address_id, (int)$_SESSION['user_id']);
        }
        $this->redirect('/ferro831/addresses.php');
    }

    public function setDefaultAddress()
    {
        $this->requireLogin();
        $address_id = (int)($_POST['address_id'] ?? $_GET['id'] ?? 0);
        if ($address_id > 0) {
            $this->model('Address')->setDefault($address_id, (int)$_SESSION['user_id']);
        }
        $this->redirect('/ferro831/addresses.php');
    }

    public function orders()
    {
        $this->requireLogin();
        $orders = $this->model('Order')->getOrdersByUserId((int)$_SESSION['user_id']);
        $this->view('user/orders', compact('orders'));
    }

    public function viewOrder()
    {
        $this->requireLogin();
        $user_id = (int)$_SESSION['user_id'];
        $order_id = (int)($_GET['id'] ?? 0);

        if ($order_id <= 0) {
            $not_found_message = 'Invalid order request.';
            $this->view('user/view-order', compact('not_found_message'));
            return;
        }

        $orderModel = $this->model('Order');
        $order = $orderModel->getOrderByIdForUser($order_id, $user_id);

        if (!$order) {
            $not_found_message = 'This order does not exist or does not belong to your account.';
            $this->view('user/view-order', compact('not_found_message'));
            return;
        }

        $order_items = $orderModel->getOrderItems($order_id);
        $status_history = $orderModel->getOrderStatusHistory($order_id);
        $this->view('user/view-order', compact('order', 'order_id', 'order_items', 'status_history'));
    }

    private function addressDataFromPost()
    {
        return [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address_line' => trim($_POST['address_line'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'state' => trim($_POST['state'] ?? ''),
            'pincode' => trim($_POST['pincode'] ?? ''),
        ];
    }

    private function addressDataIsValid($data)
    {
        foreach ($data as $value) {
            if ($value === '') {
                return false;
            }
        }
        return true;
    }
}
