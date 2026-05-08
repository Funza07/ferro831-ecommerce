<?php
class HomeController extends Controller
{
    public function index()
    {
        $productModel = $this->model('Product');
        $wishlistModel = $this->model('Wishlist');

        $search_query = trim($_GET['q'] ?? '');
        $selected_category_raw = trim($_GET['category'] ?? '');
        $selected_sort = trim($_GET['sort'] ?? 'newest');
        $min_price_raw = trim($_GET['min_price'] ?? '');
        $max_price_raw = trim($_GET['max_price'] ?? '');

        $allowed_sorts = ['newest', 'price_low_high', 'price_high_low', 'name_az'];
        if (!in_array($selected_sort, $allowed_sorts, true)) {
            $selected_sort = 'newest';
        }

        $available_categories = $productModel->getDistinctCategories();
        $selected_category = in_array($selected_category_raw, $available_categories, true) ? $selected_category_raw : '';

        $products = $productModel->getAllProductsWithFilters([
            'q' => $search_query,
            'category' => $selected_category,
            'min_price' => $min_price_raw,
            'max_price' => $max_price_raw,
            'sort' => $selected_sort,
        ]);

        $is_user_logged_in = isset($_SESSION['user_id']);
        $wished_product_ids = [];
        if ($is_user_logged_in && !empty($products)) {
            $ids = array_map(function ($product) {
                return (int)$product['id'];
            }, $products);
            $wished_product_ids = $wishlistModel->getProductIdsForUser((int)$_SESSION['user_id'], $ids);
        }

        $this->view('home/index', compact('products', 'available_categories', 'search_query', 'selected_category', 'selected_sort', 'min_price_raw', 'max_price_raw', 'is_user_logged_in', 'wished_product_ids'));
    }
}
