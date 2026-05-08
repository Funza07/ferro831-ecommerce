<?php
class Controller
{
    protected function model($model)
    {
        return new $model();
    }

    protected function view($view, $data = [], $layout = 'layouts/main')
    {
        extract($data, EXTR_SKIP);
        $view_path = BASE_PATH . '/app/views/' . $view . '.php';

        if (!file_exists($view_path)) {
            http_response_code(500);
            echo 'View not found.';
            return;
        }

        if ($layout === null) {
            require $view_path;
            return;
        }

        require BASE_PATH . '/app/views/' . $layout . '.php';
    }

    protected function redirect($path)
    {
        redirect($path);
    }

    protected function requireLogin()
    {
        if (!isLoggedIn()) {
            $this->redirect(url('login.php'));
        }
    }

    protected function requireAdmin()
    {
        if (!isLoggedIn() || !isAdmin()) {
            $this->redirect(url('login.php'));
        }
    }
}
