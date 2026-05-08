<?php
class ProductController extends Controller
{
    public function index()
    {
        $productModel = $this->model('Product');
        $wishlistModel = $this->model('Wishlist');
        $search_query = trim($_GET['q'] ?? '');
        $selected_category_raw = trim($_GET['category'] ?? '');
        $min_price_raw = trim($_GET['min_price'] ?? '');
        $max_price_raw = trim($_GET['max_price'] ?? '');
        $selected_sort = trim($_GET['sort'] ?? 'newest');

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
        $this->view('products/list', compact(
            'products',
            'search_query',
            'selected_category',
            'min_price_raw',
            'max_price_raw',
            'selected_sort',
            'is_user_logged_in',
            'wished_product_ids'
        ));
    }

    public function show()
    {
        $product_id = (int)($_GET['id'] ?? 0);
        $productModel = $this->model('Product');
        $wishlistModel = $this->model('Wishlist');
        $product = $product_id > 0 ? $productModel->getById($product_id) : null;

        if (!$product) {
            $this->view('products/not-found');
            return;
        }

        $image_rows = $productModel->getImages($product_id);
        $gallery_images = array_map(function ($row) {
            return $row['image_path'];
        }, $image_rows);
        if (empty($gallery_images) && !empty($product['image'])) {
            $gallery_images[] = $product['image'];
        }

        $stock_available = (int)($product['stock'] ?? 0);
        $variants = $productModel->getVariantsByProductId($product_id);
        $available_sizes = $productModel->getAvailableSizes($product_id);
        $available_colors = $productModel->getAvailableColors($product_id);
        $has_variants = !empty($variants);
        if ($has_variants) {
            $in_stock_variant_count = 0;
            foreach ($variants as $variant) {
                if ((int)$variant['stock'] > 0) {
                    $in_stock_variant_count++;
                }
            }
            $stock_available = $in_stock_variant_count;
        }
        $is_out_of_stock = $stock_available <= 0;
        $is_user_logged_in = isset($_SESSION['user_id']);
        $is_in_wishlist = $is_user_logged_in ? $wishlistModel->exists((int)$_SESSION['user_id'], $product_id) : false;
        $main_image = !empty($gallery_images) ? $gallery_images[0] : $product['image'];

        $this->view('products/detail', compact('product', 'gallery_images', 'stock_available', 'is_out_of_stock', 'is_user_logged_in', 'is_in_wishlist', 'main_image', 'variants', 'available_sizes', 'available_colors', 'has_variants'));
    }
}
