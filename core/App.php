<?php
class App
{
    protected $routes = [
        'GET' => [
            '/' => ['HomeController', 'index'],
            '/home' => ['HomeController', 'index'],
            '/products' => ['ProductController', 'index'],
            '/product' => ['ProductController', 'show'],
            '/cart' => ['CartController', 'index'],
            '/checkout' => ['CheckoutController', 'index'],
            '/login' => ['AuthController', 'login'],
            '/register' => ['AuthController', 'register'],
            '/logout' => ['AuthController', 'logout'],
            '/wishlist' => ['WishlistController', 'index'],
            '/profile' => ['UserController', 'profile'],
            '/addresses' => ['UserController', 'addresses'],
            '/add-address' => ['UserController', 'addAddress'],
            '/edit-address' => ['UserController', 'editAddress'],
            '/delete-address' => ['UserController', 'deleteAddress'],
            '/set-default-address' => ['UserController', 'setDefaultAddress'],
            '/user/orders' => ['UserController', 'orders'],
            '/user/view-order' => ['UserController', 'viewOrder'],
            '/admin/login' => ['AuthController', 'adminLogin'],
            '/admin/dashboard' => ['AdminController', 'dashboard'],
            '/admin/add-product' => ['AdminController', 'addProduct'],
            '/admin/orders' => ['AdminController', 'orders'],
            '/admin/view-order' => ['AdminController', 'viewOrder'],
        ],
        'POST' => [
            '/login' => ['AuthController', 'authenticate'],
            '/register' => ['AuthController', 'storeUser'],
            '/cart' => ['CartController', 'update'],
            '/order-success' => ['CheckoutController', 'placeOrder'],
            '/add-to-wishlist' => ['WishlistController', 'add'],
            '/remove-wishlist' => ['WishlistController', 'remove'],
            '/move-wishlist-to-cart' => ['WishlistController', 'moveToCart'],
            '/profile' => ['UserController', 'updateProfile'],
            '/add-address' => ['UserController', 'storeAddress'],
            '/edit-address' => ['UserController', 'updateAddress'],
            '/delete-address' => ['UserController', 'deleteAddress'],
            '/set-default-address' => ['UserController', 'setDefaultAddress'],
            '/admin/login' => ['AuthController', 'adminAuthenticate'],
            '/admin/view-order' => ['AdminController', 'updateOrderStatus'],
            '/admin/add-product' => ['AdminController', 'storeProduct'],
        ],
    ];

    public function run()
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path = $this->resolvePath();

        if ($method === 'POST' && $path === '/cart') {
            if (isset($_POST['add_to_cart'])) {
                return $this->dispatch(['CartController', 'add']);
            }
            return $this->dispatch(['CartController', 'update']);
        }

        if ($method === 'GET' && $path === '/cart' && isset($_GET['remove'])) {
            return $this->dispatch(['CartController', 'remove']);
        }

        $route = $this->routes[$method][$path] ?? null;
        if ($route === null) {
            if ($path === '/index') {
                $route = ['HomeController', 'index'];
            } elseif ($method === 'GET' && isset($this->routes['GET'][$path . '.php'])) {
                $route = $this->routes['GET'][$path . '.php'];
            } else {
                http_response_code(404);
                echo 'Page not found.';
                return;
            }
        }

        $this->dispatch($route);
    }

    protected function resolvePath()
    {
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $clean = '/' . trim((string)$requestPath, '/');
        if ($clean === '//') {
            $clean = '/';
        }

        $baseCandidates = [
            '/ferro831/public',
            '/ferro831',
            '/public',
        ];

        foreach ($baseCandidates as $base) {
            if ($clean === $base) {
                return '/';
            }
            if (strpos($clean, $base . '/') === 0) {
                $clean = substr($clean, strlen($base));
                break;
            }
        }

        if ($clean === '' || $clean === false) {
            $clean = '/';
        }
        if ($clean[0] !== '/') {
            $clean = '/' . $clean;
        }

        if (substr($clean, -4) === '.php') {
            $clean = substr($clean, 0, -4);
            if ($clean === '') {
                $clean = '/';
            }
        }

        return rtrim($clean, '/') === '' ? '/' : rtrim($clean, '/');
    }

    protected function dispatch($route)
    {
        [$controllerName, $method] = $route;
        if (!class_exists($controllerName) || !method_exists($controllerName, $method)) {
            http_response_code(404);
            echo 'Page not found.';
            return;
        }

        $controller = new $controllerName();
        $controller->$method();
    }
}
