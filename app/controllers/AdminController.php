<?php
class AdminController extends Controller
{
    private $allowedStatuses = ['Pending', 'Confirmed', 'Packed', 'Shipped', 'Delivered', 'Cancelled'];
    private $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    public function dashboard()
    {
        $this->requireAdmin();
        $productModel = $this->model('Product');
        $orderModel = $this->model('Order');
        $productModel->syncAllProductStockFromVariants();

        $total_products = $productModel->getTotalProducts();
        $total_variant_count = $productModel->getTotalVariantCount();
        $total_variant_stock = $productModel->getTotalVariantStock();
        $total_stock_available = $productModel->getTotalStockVariantAware();
        $total_units_sold = $productModel->getTotalSold();
        $low_stock_count = $productModel->getLowStockCountVariantAware(5);
        $low_stock_variants = $productModel->getLowStockVariants(5, 10);
        $top_selling_products = $productModel->getTopSellingProducts(5);

        $total_orders = $orderModel->getTotalOrders();
        $orders_this_week = $orderModel->getOrdersCountLastDays(7);
        $pending_orders = $orderModel->getPendingOrdersCount();
        $delivered_orders = $orderModel->getDeliveredOrdersCount();
        $delivered_revenue = $orderModel->getDeliveredRevenue();
        $recent_orders = $orderModel->getRecentOrders(30);
        $status_counts = $orderModel->getOrderStatusCounts();
        $revenue_last_days = $orderModel->getRevenueLastDays(7);

        $admin_name = htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
        $this->view(
            'admin/dashboard',
            compact(
                'total_products',
                'total_stock_available',
                'total_units_sold',
                'total_variant_count',
                'total_variant_stock',
                'total_orders',
                'orders_this_week',
                'pending_orders',
                'delivered_orders',
                'delivered_revenue',
                'low_stock_count',
                'low_stock_variants',
                'recent_orders',
                'top_selling_products',
                'status_counts',
                'revenue_last_days',
                'admin_name'
            ),
            null
        );
    }

    public function products()
    {
        $this->requireAdmin();
        $productModel = $this->model('Product');
        $productModel->syncAllProductStockFromVariants();
        $products = $productModel->getAdminProductsWithVariantStats();
        $admin_name = htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
        $this->view('admin/products', compact('products', 'admin_name'), null);
    }

    public function manageVariants()
    {
        $this->requireAdmin();
        $productId = (int)($_GET['product_id'] ?? $_POST['product_id'] ?? 0);
        $productModel = $this->model('Product');
        $product = $productId > 0 ? $productModel->getById($productId) : null;
        if (!$product) {
            echo 'Product not found';
            return;
        }

        $productModel->updateProductStockFromVariants($productId);
        $variants = $productModel->getVariantsByProductId($productId, true);
        $summary = $productModel->getVariantStockSummary($productId);
        $admin_name = htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
        $flash_message = $_SESSION['admin_variant_flash_message'] ?? '';
        $flash_error = $_SESSION['admin_variant_flash_error'] ?? '';
        unset($_SESSION['admin_variant_flash_message'], $_SESSION['admin_variant_flash_error']);

        $this->view('admin/manage-variants', compact('product', 'variants', 'summary', 'admin_name', 'flash_message', 'flash_error'), null);
    }

    public function addVariant()
    {
        $this->requireAdmin();
        $productId = (int)($_POST['product_id'] ?? 0);
        $productModel = $this->model('Product');
        $product = $productId > 0 ? $productModel->getById($productId) : null;
        if (!$product) {
            $_SESSION['admin_variant_flash_error'] = 'Product not found.';
            $this->redirect('/ferro831/admin/products.php');
        }

        $payload = $this->normalizeVariantPayload($_POST);
        if ($payload['error'] !== '') {
            $_SESSION['admin_variant_flash_error'] = $payload['error'];
            $this->redirect('/ferro831/admin/manage-variants.php?product_id=' . $productId);
        }
        $data = $payload['data'];

        if ($productModel->variantExistsForOptions($productId, $data['size'], $data['color'])) {
            $_SESSION['admin_variant_flash_error'] = 'Variant with same size and color already exists for this product.';
            $this->redirect('/ferro831/admin/manage-variants.php?product_id=' . $productId);
        }

        if ($data['sku'] === '') {
            $data['sku'] = $productModel->generateVariantSku($productId, $data['size'], $data['color']);
        }

        $created = $productModel->addVariant($productId, $data);
        if (!$created) {
            $_SESSION['admin_variant_flash_error'] = 'Could not add variant.';
            $this->redirect('/ferro831/admin/manage-variants.php?product_id=' . $productId);
        }

        $productModel->updateProductStockFromVariants($productId);
        $_SESSION['admin_variant_flash_message'] = 'Variant added successfully.';
        $this->redirect('/ferro831/admin/manage-variants.php?product_id=' . $productId);
    }

    public function updateVariant()
    {
        $this->requireAdmin();
        $productId = (int)($_POST['product_id'] ?? 0);
        $variantId = (int)($_POST['variant_id'] ?? 0);
        $productModel = $this->model('Product');
        $product = $productId > 0 ? $productModel->getById($productId) : null;
        $variant = $variantId > 0 ? $productModel->getVariantById($variantId) : null;
        if (!$product || !$variant || (int)$variant['product_id'] !== $productId) {
            $_SESSION['admin_variant_flash_error'] = 'Invalid product/variant selection.';
            $this->redirect('/ferro831/admin/products.php');
        }

        $payload = $this->normalizeVariantPayload($_POST);
        if ($payload['error'] !== '') {
            $_SESSION['admin_variant_flash_error'] = $payload['error'];
            $this->redirect('/ferro831/admin/manage-variants.php?product_id=' . $productId);
        }
        $data = $payload['data'];

        if ($productModel->variantExistsForOptions($productId, $data['size'], $data['color'], $variantId)) {
            $_SESSION['admin_variant_flash_error'] = 'Another variant with same size and color already exists.';
            $this->redirect('/ferro831/admin/manage-variants.php?product_id=' . $productId);
        }
        if ($data['sku'] === '') {
            $data['sku'] = $productModel->generateVariantSku($productId, $data['size'], $data['color']);
        }

        if (!$productModel->updateVariant($variantId, $data)) {
            $_SESSION['admin_variant_flash_error'] = 'Could not update variant.';
            $this->redirect('/ferro831/admin/manage-variants.php?product_id=' . $productId);
        }

        $productModel->updateProductStockFromVariants($productId);
        $_SESSION['admin_variant_flash_message'] = 'Variant updated successfully.';
        $this->redirect('/ferro831/admin/manage-variants.php?product_id=' . $productId);
    }

    public function toggleVariantStatus()
    {
        $this->requireAdmin();
        $productId = (int)($_POST['product_id'] ?? 0);
        $variantId = (int)($_POST['variant_id'] ?? 0);
        $isActive = (int)($_POST['is_active'] ?? 0) === 1 ? 1 : 0;
        $productModel = $this->model('Product');
        $variant = $variantId > 0 ? $productModel->getVariantById($variantId) : null;
        if (!$variant || (int)$variant['product_id'] !== $productId) {
            $_SESSION['admin_variant_flash_error'] = 'Invalid variant selection.';
            $this->redirect('/ferro831/admin/products.php');
        }

        if (!$productModel->setVariantActive($variantId, $isActive)) {
            $_SESSION['admin_variant_flash_error'] = 'Could not update variant status.';
            $this->redirect('/ferro831/admin/manage-variants.php?product_id=' . $productId);
        }
        $productModel->updateProductStockFromVariants($productId);
        $_SESSION['admin_variant_flash_message'] = 'Variant status updated.';
        $this->redirect('/ferro831/admin/manage-variants.php?product_id=' . $productId);
    }

    public function deleteVariant()
    {
        $this->requireAdmin();
        $productId = (int)($_POST['product_id'] ?? 0);
        $variantId = (int)($_POST['variant_id'] ?? 0);
        $productModel = $this->model('Product');
        $variant = $variantId > 0 ? $productModel->getVariantById($variantId) : null;
        if (!$variant || (int)$variant['product_id'] !== $productId) {
            $_SESSION['admin_variant_flash_error'] = 'Invalid variant selection.';
            $this->redirect('/ferro831/admin/products.php');
        }

        $result = $productModel->deleteVariantIfSafe($variantId);
        if (!$result['success']) {
            $_SESSION['admin_variant_flash_error'] = 'Could not delete/deactivate variant.';
            $this->redirect('/ferro831/admin/manage-variants.php?product_id=' . $productId);
        }

        $productModel->updateProductStockFromVariants($productId);
        $_SESSION['admin_variant_flash_message'] = $result['action'] === 'deleted'
            ? 'Variant deleted successfully.'
            : 'Variant has order history, so it was deactivated instead.';
        $this->redirect('/ferro831/admin/manage-variants.php?product_id=' . $productId);
    }

    public function orders()
    {
        $this->requireAdmin();
        $orders = $this->model('Order')->getAllOrders();
        $admin_name = htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
        $this->view('admin/orders', compact('orders', 'admin_name'), null);
    }

    public function viewOrder()
    {
        $this->requireAdmin();
        $order_id = (int)($_GET['id'] ?? 0);
        if ($order_id <= 0) {
            echo 'Order not found';
            return;
        }

        $orderModel = $this->model('Order');
        $order = $orderModel->getOrderById($order_id);
        if (!$order) {
            echo 'Order not found';
            return;
        }

        $order_items = $orderModel->getOrderItems($order_id);
        $status_history = $orderModel->getOrderStatusHistory($order_id);
        $allowed_statuses = $this->allowedStatuses;
        $admin_name = htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
        $flash_message = $_SESSION['admin_order_flash_message'] ?? '';
        $flash_error = $_SESSION['admin_order_flash_error'] ?? '';
        unset($_SESSION['admin_order_flash_message'], $_SESSION['admin_order_flash_error']);

        $this->view(
            'admin/view-order',
            compact('order', 'order_id', 'order_items', 'status_history', 'allowed_statuses', 'admin_name', 'flash_message', 'flash_error'),
            null
        );
    }

    public function updateOrderStatus()
    {
        $this->requireAdmin();
        $order_id = (int)($_GET['id'] ?? $_POST['order_id'] ?? 0);
        if ($order_id <= 0) {
            $this->redirect('/ferro831/admin/orders.php');
        }

        $new_status = trim($_POST['order_status'] ?? '');
        $new_tracking_number = trim($_POST['tracking_number'] ?? '');
        $status_note = trim($_POST['status_note'] ?? '');

        if (!in_array($new_status, $this->allowedStatuses, true)) {
            $_SESSION['admin_order_flash_error'] = 'Invalid order status selected.';
            $this->redirect('/ferro831/admin/view-order.php?id=' . $order_id);
        }

        $result = $this->model('Order')->updateStatusAndTracking(
            $order_id,
            $new_status,
            $new_tracking_number,
            $status_note
        );

        if ($result['success']) {
            $_SESSION['admin_order_flash_message'] = $result['message'];
        } else {
            $_SESSION['admin_order_flash_error'] = $result['message'];
        }

        $this->redirect('/ferro831/admin/view-order.php?id=' . $order_id);
    }

    public function addProduct()
    {
        $this->requireAdmin();
        $admin_name = htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
        $success = (bool)($_SESSION['admin_add_product_success'] ?? false);
        $success_message = (string)($_SESSION['admin_add_product_success_message'] ?? '');
        $errors = $_SESSION['admin_add_product_errors'] ?? [];
        $db_error = $_SESSION['admin_add_product_db_error'] ?? '';
        $old_input = $_SESSION['admin_add_product_old'] ?? [];
        unset(
            $_SESSION['admin_add_product_success'],
            $_SESSION['admin_add_product_success_message'],
            $_SESSION['admin_add_product_errors'],
            $_SESSION['admin_add_product_db_error'],
            $_SESSION['admin_add_product_old']
        );

        $this->view('admin/add-product', compact('admin_name', 'success', 'success_message', 'errors', 'db_error', 'old_input'), null);
    }

    public function storeProduct()
    {
        $this->requireAdmin();
        global $conn;

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'price_raw' => trim($_POST['price'] ?? ''),
            'stock_raw' => trim($_POST['stock'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
        ];

        $_SESSION['admin_add_product_old'] = [
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => $data['price_raw'],
            'stock' => $data['stock_raw'],
            'description' => $data['description'],
            'variants' => $_POST['variants'] ?? [],
        ];

        $errors = [];
        if ($data['name'] === '') {
            $errors[] = 'Product name is required.';
        }
        if ($data['category'] === '') {
            $errors[] = 'Category is required.';
        }
        if ($data['price_raw'] === '' || !is_numeric($data['price_raw']) || (float)$data['price_raw'] < 0) {
            $errors[] = 'Price must be a valid non-negative number.';
        }
        if ($data['stock_raw'] === '' || !is_numeric($data['stock_raw']) || (int)$data['stock_raw'] < 0) {
            $errors[] = 'Stock must be a valid non-negative number.';
        }

        $basePrice = is_numeric($data['price_raw']) ? (float)$data['price_raw'] : 0.0;
        $variants = $this->parseVariantRows($_POST['variants'] ?? [], $errors, $basePrice);

        $saved_images = [];
        $db_image_paths = [];
        $this->handleProductUploads($errors, $saved_images, $db_image_paths);

        if (!empty($errors)) {
            $this->cleanupUploadedFiles($saved_images);
            $_SESSION['admin_add_product_errors'] = $errors;
            $this->redirect('/ferro831/admin/add-product.php');
        }

        $productData = [
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => (float)$data['price_raw'],
            'description' => $data['description'],
            'image' => $db_image_paths[0] ?? '',
            'stock' => (int)$data['stock_raw'],
        ];

        $db_error = '';
        mysqli_begin_transaction($conn);
        $productModel = $this->model('Product');
        $productId = $productModel->createProduct($productData);
        $variantsCreated = 0;

        if (!$productId) {
            $db_error = mysqli_error($conn);
            mysqli_rollback($conn);
        } else {
            if (!$productModel->addProductImages($productId, $db_image_paths)) {
                $db_error = mysqli_error($conn);
            }

            if ($db_error === '' && !empty($variants)) {
                $variants = $this->attachMissingVariantSkus($productId, $variants);
                $variantsCreated = $productModel->createVariants($productId, $variants);
                if ($variantsCreated === false) {
                    $db_error = mysqli_error($conn);
                }
            }

            if ($db_error === '' && $variantsCreated > 0 && !$productModel->updateProductStockFromVariants($productId)) {
                $db_error = mysqli_error($conn);
            }

            if ($db_error === '') {
                mysqli_commit($conn);
                unset($_SESSION['admin_add_product_old']);
                $_SESSION['admin_add_product_success'] = true;
                $_SESSION['admin_add_product_success_message'] = 'Product created successfully. Variants created: ' . (int)$variantsCreated . '.';
                $this->redirect('/ferro831/admin/add-product.php');
            }

            mysqli_rollback($conn);
        }

        $this->cleanupUploadedFiles($saved_images);
        $_SESSION['admin_add_product_db_error'] = $db_error !== '' ? $db_error : 'Could not save product right now.';
        $this->redirect('/ferro831/admin/add-product.php');
    }

    private function parseVariantRows($rawVariants, &$errors, $basePrice = 0.0)
    {
        $rows = [];
        $seenKeys = [];
        if (!is_array($rawVariants)) {
            return $rows;
        }

        foreach ($rawVariants as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $size = trim((string)($row['size'] ?? ''));
            $color = trim((string)($row['color'] ?? ''));
            $colorHex = trim((string)($row['color_hex'] ?? '#111111'));
            $sku = trim((string)($row['sku'] ?? ''));
            $stockRaw = trim((string)($row['stock'] ?? '0'));
            $priceOverrideRaw = trim((string)($row['price_override'] ?? ''));
            $isActiveRaw = (string)($row['is_active'] ?? '1');

            $isEmpty = ($size === '' && $color === '' && $sku === '' && $stockRaw === '' && $priceOverrideRaw === '');
            if ($isEmpty) {
                continue;
            }

            if ($size === '' || $color === '') {
                $errors[] = 'Variant row ' . ((int)$index + 1) . ': size and color are required.';
                continue;
            }

            if ($stockRaw === '' || !is_numeric($stockRaw) || (int)$stockRaw < 0) {
                $errors[] = 'Variant row ' . ((int)$index + 1) . ': stock must be a non-negative integer.';
                continue;
            }

            $priceOverride = null;
            if ($priceOverrideRaw !== '') {
                if (!is_numeric($priceOverrideRaw)) {
                    $errors[] = 'Variant row ' . ((int)$index + 1) . ': price override must be numeric.';
                    continue;
                }
                $priceOverrideFloat = (float)$priceOverrideRaw;
                if ($priceOverrideFloat > 0) {
                    if ($basePrice > 0 && $priceOverrideFloat < ($basePrice * 0.5)) {
                        $errors[] = 'Variant price override must be the full final price. Leave blank to use base price, or enter a realistic final price.';
                        continue;
                    }
                    $priceOverride = $priceOverrideFloat;
                } else {
                    $priceOverride = null;
                }
            }

            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $colorHex)) {
                $colorHex = '#111111';
            }

            $isActive = ($isActiveRaw === '0') ? 0 : 1;
            $dedupeKey = strtolower($size . '|' . $color);
            if (isset($seenKeys[$dedupeKey])) {
                $errors[] = 'Duplicate variant found for Size "' . $size . '" and Color "' . $color . '".';
                continue;
            }
            $seenKeys[$dedupeKey] = true;

            $rows[] = [
                'size' => $size,
                'color' => $color,
                'color_hex' => $colorHex,
                'sku' => $sku,
                'stock' => (int)$stockRaw,
                'price_override' => $priceOverride,
                'is_active' => $isActive,
            ];
        }

        return $rows;
    }

    private function attachMissingVariantSkus($productId, $variants)
    {
        $output = [];
        foreach ($variants as $variant) {
            $sku = trim((string)($variant['sku'] ?? ''));
            if ($sku === '') {
                $size = strtoupper(preg_replace('/[^A-Z0-9]+/', '', (string)($variant['size'] ?? '')));
                $color = strtoupper((string)($variant['color'] ?? ''));
                $colorCode = strtoupper(substr(preg_replace('/[^A-Z0-9]+/', '', $color), 0, 3));
                if ($colorCode === '') {
                    $colorCode = 'CLR';
                }
                $sku = 'FERRO-' . (int)$productId . '-' . ($size !== '' ? $size : 'NA') . '-' . $colorCode;
            }
            $variant['sku'] = $sku;
            $output[] = $variant;
        }
        return $output;
    }

    private function handleProductUploads(&$errors, &$savedImages, &$dbImagePaths)
    {
        if (!isset($_FILES['images']) || !isset($_FILES['images']['name']) || !is_array($_FILES['images']['name'])) {
            return;
        }

        $fileNames = $_FILES['images']['name'];
        $fileTmpNames = $_FILES['images']['tmp_name'];
        $fileSizes = $_FILES['images']['size'];
        $fileErrors = $_FILES['images']['error'];
        $selectedCount = 0;

        foreach ($fileNames as $fileName) {
            if (trim((string)$fileName) !== '') {
                $selectedCount++;
            }
        }

        if ($selectedCount === 0) {
            return;
        }

        $uploadDirFs = BASE_PATH . '/assets/images/products/';
        if (!is_dir($uploadDirFs) && !mkdir($uploadDirFs, 0755, true)) {
            $errors[] = 'Failed to create upload directory.';
            return;
        }

        if (!is_writable($uploadDirFs)) {
            $errors[] = 'Upload directory is not writable.';
            return;
        }

        $maxSize = 2 * 1024 * 1024;
        foreach ($fileNames as $index => $originalName) {
            $originalName = (string)$originalName;
            if (trim($originalName) === '') {
                continue;
            }

            $currentError = (int)$fileErrors[$index];
            $currentTmpName = $fileTmpNames[$index];
            $currentSize = (int)$fileSizes[$index];

            if ($currentError !== UPLOAD_ERR_OK) {
                $errors[] = 'One image failed to upload. Please try again.';
                return;
            }

            if ($currentSize > $maxSize) {
                $errors[] = 'Each image must be 2MB or smaller.';
                return;
            }

            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if (!in_array($extension, $this->allowedImageExtensions, true)) {
                $errors[] = 'Invalid image type. Allowed: JPG, JPEG, PNG, WEBP.';
                return;
            }

            $imageInfo = @getimagesize($currentTmpName);
            if ($imageInfo === false) {
                $errors[] = 'Invalid image file detected.';
                return;
            }

            $uniqueName = time() . '_' . random_int(1000, 9999) . '_' . $index . '.' . $extension;
            $targetFs = $uploadDirFs . $uniqueName;
            while (file_exists($targetFs)) {
                $uniqueName = time() . '_' . random_int(1000, 9999) . '_' . $index . '.' . $extension;
                $targetFs = $uploadDirFs . $uniqueName;
            }

            if (!move_uploaded_file($currentTmpName, $targetFs)) {
                $errors[] = 'Failed to save uploaded image.';
                return;
            }

            $savedImages[] = $targetFs;
            $dbImagePaths[] = 'products/' . $uniqueName;
        }
    }

    private function cleanupUploadedFiles($savedImages)
    {
        foreach ($savedImages as $savedImage) {
            if (file_exists($savedImage)) {
                @unlink($savedImage);
            }
        }
    }

    private function normalizeVariantPayload($input)
    {
        $size = trim((string)($input['size'] ?? ''));
        $color = trim((string)($input['color'] ?? ''));
        $colorHex = trim((string)($input['color_hex'] ?? '#111111'));
        $sku = trim((string)($input['sku'] ?? ''));
        $stockRaw = trim((string)($input['stock'] ?? '0'));
        $priceRaw = trim((string)($input['price_override'] ?? ''));
        $isActive = ((string)($input['is_active'] ?? '1') === '0') ? 0 : 1;

        if ($size === '' || $color === '') {
            return ['error' => 'Size and color are required.', 'data' => null];
        }
        if (!is_numeric($stockRaw) || (int)$stockRaw < 0) {
            return ['error' => 'Stock must be a non-negative number.', 'data' => null];
        }
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $colorHex)) {
            $colorHex = '#111111';
        }
        $price = null;
        if ($priceRaw !== '') {
            if (!is_numeric($priceRaw)) {
                return ['error' => 'Price override must be numeric.', 'data' => null];
            }
            $priceFloat = (float)$priceRaw;
            $price = $priceFloat > 0 ? $priceFloat : null;
        }

        return [
            'error' => '',
            'data' => [
                'size' => $size,
                'color' => $color,
                'color_hex' => $colorHex,
                'sku' => $sku,
                'stock' => (int)$stockRaw,
                'price_override' => $price,
                'is_active' => $isActive,
            ],
        ];
    }
}
