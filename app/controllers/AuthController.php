<?php
class AuthController extends Controller
{
    public function login()
    {
        if (isLoggedIn()) {
            $this->redirect(isAdmin() ? url('admin/dashboard.php') : url('index.php'));
        }

        $errors = flash('auth_login_errors') ?? [];
        $old_input = flash('auth_login_old') ?? [];
        $this->view('auth/login', compact('errors', 'old_input'));
    }

    public function authenticate()
    {
        if (isLoggedIn()) {
            $this->redirect(isAdmin() ? url('admin/dashboard.php') : url('index.php'));
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $errors[] = 'Invalid email or password.';
        } else {
            $user = $this->model('User')->verifyPassword($email, $password);
            if ($user) {
                $this->signInUser($user);
                $this->redirect($user['role'] === 'admin' ? url('admin/dashboard.php') : url('index.php'));
            }
            $errors[] = 'Invalid email or password.';
        }

        flash('auth_login_errors', $errors);
        flash('auth_login_old', ['email' => $email]);
        $this->redirect(url('login.php'));
    }

    public function register()
    {
        if (isLoggedIn()) {
            $this->redirect(isAdmin() ? url('admin/dashboard.php') : url('index.php'));
        }

        $errors = flash('auth_register_errors') ?? [];
        $success = (bool)(flash('auth_register_success') ?? false);
        $old_input = flash('auth_register_old') ?? [];
        $this->view('auth/register', compact('errors', 'success', 'old_input'));
    }

    public function storeUser()
    {
        if (isLoggedIn()) {
            $this->redirect(isAdmin() ? url('admin/dashboard.php') : url('index.php'));
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'role' => 'user',
        ];

        $errors = [];
        if ($data['name'] === '') {
            $errors[] = 'Name is required.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (strlen($data['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'Confirm password does not match.';
        }

        $userModel = $this->model('User');
        if (empty($errors) && $userModel->emailExists($data['email'])) {
            $errors[] = 'This email is already registered.';
        }

        if (!empty($errors)) {
            flash('auth_register_errors', $errors);
            flash('auth_register_old', ['name' => $data['name'], 'email' => $data['email']]);
            $this->redirect(url('register.php'));
        }

        if ($userModel->create($data)) {
            flash('auth_register_success', true);
        } else {
            flash('auth_register_errors', ['Unable to create account right now.']);
            flash('auth_register_old', ['name' => $data['name'], 'email' => $data['email']]);
        }

        $this->redirect(url('register.php'));
    }

    public function logout()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        $this->redirect(url('index.php'));
    }

    public function adminLogin()
    {
        if (isLoggedIn() && isAdmin()) {
            $this->redirect(url('admin/dashboard.php'));
        }

        $error = flash('admin_login_error') ?? '';
        $old_input = flash('admin_login_old') ?? [];
        $this->view('admin/login', compact('error', 'old_input'), null);
    }

    public function adminAuthenticate()
    {
        if (isLoggedIn() && isAdmin()) {
            $this->redirect(url('admin/dashboard.php'));
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            flash('admin_login_error', 'Please enter valid login details.');
            flash('admin_login_old', ['email' => $email]);
            $this->redirect(url('admin/login.php'));
        }

        $user = $this->model('User')->verifyPassword($email, $password);
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            flash('admin_login_error', 'Invalid admin credentials.');
            flash('admin_login_old', ['email' => $email]);
            $this->redirect(url('admin/login.php'));
        }

        $this->signInUser($user);
        $this->redirect(url('admin/dashboard.php'));
    }

    private function signInUser($user)
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
    }
}
