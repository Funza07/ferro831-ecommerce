<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/ferro831');
}

if (!function_exists('url')) {
    function url($path = '')
    {
        $base = rtrim(BASE_URL, '/');
        $path = ltrim((string)$path, '/');
        if ($path === '') {
            return $base . '/';
        }
        return $base . '/' . $path;
    }
}

if (!function_exists('redirect')) {
    function redirect($path)
    {
        $target = (string)$path;
        if (!preg_match('#^https?://#i', $target) && strpos($target, '/') !== 0) {
            $target = url($target);
        }
        header('Location: ' . $target);
        exit;
    }
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

if (!function_exists('e')) {
    function e($string)
    {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('old')) {
    function old($key, $default = '')
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('flash')) {
    function flash($key, $value = null)
    {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }

        $message = $_SESSION['_flash'][$key] ?? null;
        if (isset($_SESSION['_flash'][$key])) {
            unset($_SESSION['_flash'][$key]);
        }
        return $message;
    }
}
