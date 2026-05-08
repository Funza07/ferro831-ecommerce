<?php
class Product extends Model
{
    public function getTotalProducts()
    {
        $stmt = mysqli_prepare($this->conn, 'SELECT COUNT(*) FROM products');
        if (!$stmt) {
            return 0;
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $total);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return (int)$total;
    }

    public function getTotalStock()
    {
        $stmt = mysqli_prepare($this->conn, 'SELECT COALESCE(SUM(stock), 0) FROM products');
        if (!$stmt) {
            return 0;
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $total);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return (int)$total;
    }

    public function getTotalStockVariantAware()
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT COALESCE(SUM(
                CASE
                    WHEN EXISTS (
                        SELECT 1 FROM product_variants v WHERE v.product_id = p.id AND v.is_active = 1
                    )
                    THEN (
                        SELECT COALESCE(SUM(v2.stock), 0) FROM product_variants v2 WHERE v2.product_id = p.id AND v2.is_active = 1
                    )
                    ELSE p.stock
                END
            ), 0)
            FROM products p'
        );
        if (!$stmt) {
            return 0;
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $total);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return (int)$total;
    }

    public function getTotalSold()
    {
        $stmt = mysqli_prepare($this->conn, 'SELECT COALESCE(SUM(sold_count), 0) FROM products');
        if (!$stmt) {
            return 0;
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $total);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return (int)$total;
    }

    public function getLowStockCount($threshold = 5)
    {
        $stmt = mysqli_prepare($this->conn, 'SELECT COUNT(*) FROM products WHERE stock <= ?');
        if (!$stmt) {
            return 0;
        }
        $threshold = (int)$threshold;
        mysqli_stmt_bind_param($stmt, 'i', $threshold);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return (int)$count;
    }

    public function getLowStockCountVariantAware($threshold = 5)
    {
        $threshold = max(0, (int)$threshold);
        $variantLow = $this->getLowStockVariantsCount($threshold);
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT COUNT(*)
             FROM products p
             WHERE p.stock <= ?
               AND NOT EXISTS (
                 SELECT 1 FROM product_variants v WHERE v.product_id = p.id AND v.is_active = 1
               )'
        );
        if (!$stmt) {
            return $variantLow;
        }
        mysqli_stmt_bind_param($stmt, 'i', $threshold);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $productLow);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return (int)$variantLow + (int)$productLow;
    }

    public function getTopSellingProducts($limit = 5)
    {
        $rows = [];
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT oi.product_id, oi.product_name, COALESCE(SUM(oi.quantity), 0) AS units_sold, COALESCE(SUM(oi.total_price), 0) AS sales_total,
                    p.image, p.price
             FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             GROUP BY product_id, product_name
             ORDER BY units_sold DESC, sales_total DESC
             LIMIT ?'
        );
        if (!$stmt) {
            return $rows;
        }
        $limit = max(1, (int)$limit);
        mysqli_stmt_bind_param($stmt, 'i', $limit);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $product_id, $product_name, $units_sold, $sales_total, $image, $price);
        while (mysqli_stmt_fetch($stmt)) {
            $rows[] = [
                'product_id' => (int)$product_id,
                'product_name' => (string)$product_name,
                'units_sold' => (int)$units_sold,
                'sales_total' => (float)$sales_total,
                'image' => (string)($image ?? ''),
                'price' => (float)($price ?? 0),
            ];
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function create($data)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'INSERT INTO products (name, category, price, description, image, stock) VALUES (?, ?, ?, ?, ?, ?)'
        );
        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param(
            $stmt,
            'ssdssi',
            $data['name'],
            $data['category'],
            $data['price'],
            $data['description'],
            $data['image'],
            $data['stock']
        );

        $ok = mysqli_stmt_execute($stmt);
        $productId = $ok ? mysqli_insert_id($this->conn) : 0;
        mysqli_stmt_close($stmt);

        return $ok ? $productId : false;
    }

    public function createProduct($data)
    {
        return $this->create($data);
    }

    public function addProductImage($productId, $imagePath, $isPrimary)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)'
        );
        if (!$stmt) {
            return false;
        }

        $productId = (int)$productId;
        $isPrimary = (int)$isPrimary;
        mysqli_stmt_bind_param($stmt, 'isi', $productId, $imagePath, $isPrimary);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok;
    }

    public function addProductImages($productId, $images)
    {
        if (!is_array($images)) {
            return true;
        }
        foreach ($images as $index => $imagePath) {
            if (!$this->addProductImage((int)$productId, (string)$imagePath, $index === 0 ? 1 : 0)) {
                return false;
            }
        }
        return true;
    }

    public function getAllProducts()
    {
        return $this->getAllProductsWithFilters([
            'sort' => 'newest',
        ]);
    }

    public function getDistinctCategories()
    {
        $categories = [];
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category <> '' ORDER BY category ASC"
        );
        if (!$stmt) {
            return $categories;
        }

        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $category);
        while (mysqli_stmt_fetch($stmt)) {
            $categories[] = (string)$category;
        }
        mysqli_stmt_close($stmt);

        return $categories;
    }

    public function getAllProductsWithFilters($filters)
    {
        $allowed_sorts = [
            'newest' => 'created_at DESC',
            'price_low_high' => 'price ASC',
            'price_high_low' => 'price DESC',
            'name_az' => 'name ASC',
        ];

        $where = [];
        $types = '';
        $values = [];

        if (($filters['q'] ?? '') !== '') {
            $like = '%' . $filters['q'] . '%';
            $where[] = '(name LIKE ? OR category LIKE ? OR description LIKE ?)';
            $types .= 'sss';
            array_push($values, $like, $like, $like);
        }

        if (($filters['category'] ?? '') !== '') {
            $where[] = 'category = ?';
            $types .= 's';
            $values[] = $filters['category'];
        }

        if (($filters['min_price'] ?? '') !== '' && is_numeric($filters['min_price'])) {
            $where[] = 'price >= ?';
            $types .= 'd';
            $values[] = (float)$filters['min_price'];
        }

        if (($filters['max_price'] ?? '') !== '' && is_numeric($filters['max_price'])) {
            $where[] = 'price <= ?';
            $types .= 'd';
            $values[] = (float)$filters['max_price'];
        }

        $sort = $filters['sort'] ?? 'newest';
        if (!isset($allowed_sorts[$sort])) {
            $sort = 'newest';
        }

        $sql = 'SELECT id, name, category, price, description, image, stock, sold_count,
                EXISTS(SELECT 1 FROM product_variants v WHERE v.product_id = products.id AND v.is_active = 1) AS has_variants,
                created_at
                FROM products';
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY ' . $allowed_sorts[$sort];

        $rows = [];
        $stmt = mysqli_prepare($this->conn, $sql);
        if (!$stmt) {
            return $rows;
        }

        if ($types !== '' && !empty($values)) {
            $params = [$types];
            foreach ($values as $key => $value) {
                $values[$key] = $value;
                $params[] = &$values[$key];
            }
            call_user_func_array([$stmt, 'bind_param'], $params);
        }

        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result(
            $stmt,
            $id,
            $name,
            $category,
            $price,
            $description,
            $image,
            $stock,
            $sold_count,
            $has_variants,
            $created_at
        );

        while (mysqli_stmt_fetch($stmt)) {
            $rows[] = [
                'id' => (int)$id,
                'name' => (string)$name,
                'category' => (string)$category,
                'price' => (float)$price,
                'description' => (string)$description,
                'image' => (string)$image,
                'stock' => (int)$stock,
                'sold_count' => (int)$sold_count,
                'has_variants' => ((int)$has_variants) === 1,
                'created_at' => (string)$created_at,
            ];
        }
        mysqli_stmt_close($stmt);

        return $rows;
    }

    public function getById($productId)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT id, name, category, price, description, image, stock, sold_count, sku, compare_price, created_at FROM products WHERE id = ? LIMIT 1'
        );
        if (!$stmt) {
            return null;
        }

        $productId = (int)$productId;
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result(
            $stmt,
            $id,
            $name,
            $category,
            $price,
            $description,
            $image,
            $stock,
            $sold_count,
            $sku,
            $compare_price,
            $created_at
        );

        $row = null;
        if (mysqli_stmt_fetch($stmt)) {
            $row = [
                'id' => (int)$id,
                'name' => (string)$name,
                'category' => (string)$category,
                'price' => (float)$price,
                'description' => (string)$description,
                'image' => (string)$image,
                'stock' => (int)$stock,
                'sold_count' => (int)$sold_count,
                'sku' => (string)($sku ?? ''),
                'compare_price' => $compare_price !== null ? (float)$compare_price : null,
                'created_at' => (string)$created_at,
            ];
        }
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function getImages($productId)
    {
        $rows = [];
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT image_path, is_primary FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC'
        );
        if (!$stmt) {
            return $rows;
        }

        $productId = (int)$productId;
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $image_path, $is_primary);

        while (mysqli_stmt_fetch($stmt)) {
            $rows[] = [
                'image_path' => (string)$image_path,
                'is_primary' => (int)$is_primary,
            ];
        }
        mysqli_stmt_close($stmt);

        return $rows;
    }

    public function getFeaturedProducts()
    {
        return $this->getAllProductsWithFilters(['sort' => 'newest']);
    }

    public function getAverageRating($productId)
    {
        return null;
    }

    public function getCategories()
    {
        return $this->getDistinctCategories();
    }

    public function search(array $filters = [])
    {
        return $this->getAllProductsWithFilters($filters);
    }

    public function getAll($query = '')
    {
        if ($query !== '') {
            return $this->getAllProductsWithFilters(['q' => $query, 'sort' => 'newest']);
        }
        return $this->getAllProductsWithFilters(['sort' => 'newest']);
    }

    public function getGalleryImages($product_id)
    {
        $images = $this->getImages($product_id);
        return array_map(function ($row) {
            return $row['image_path'];
        }, $images);
    }

    public function getVariantsByProductId($productId, $includeInactive = true)
    {
        $rows = [];
        $sql = 'SELECT id, product_id, size, color, color_hex, sku, price_override, stock, is_active, created_at
                FROM product_variants
                WHERE product_id = ?';
        if (!$includeInactive) {
            $sql .= ' AND is_active = 1';
        }
        $sql .= ' ORDER BY is_active DESC, size ASC, color ASC, id ASC';
        $stmt = mysqli_prepare($this->conn, $sql);
        if (!$stmt) {
            return $rows;
        }
        $productId = (int)$productId;
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result(
            $stmt,
            $id,
            $pid,
            $size,
            $color,
            $color_hex,
            $sku,
            $price_override,
            $stock,
            $is_active,
            $created_at
        );
        while (mysqli_stmt_fetch($stmt)) {
            $rows[] = [
                'id' => (int)$id,
                'product_id' => (int)$pid,
                'size' => (string)$size,
                'color' => (string)$color,
                'color_hex' => (string)$color_hex,
                'sku' => (string)$sku,
                'price_override' => $price_override !== null ? (float)$price_override : null,
                'stock' => (int)$stock,
                'is_active' => (int)$is_active,
                'created_at' => (string)$created_at,
            ];
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function getAvailableSizes($productId)
    {
        $rows = [];
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT size, MAX(CASE WHEN stock > 0 THEN 1 ELSE 0 END) AS has_stock
             FROM product_variants
             WHERE product_id = ? AND is_active = 1 AND size IS NOT NULL AND size <> ""
             GROUP BY size
             ORDER BY size ASC'
        );
        if (!$stmt) {
            return $rows;
        }
        $productId = (int)$productId;
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'size' => (string)$row['size'],
                'has_stock' => ((int)$row['has_stock']) === 1,
            ];
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function getAvailableColors($productId)
    {
        $rows = [];
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT color, MAX(color_hex) AS color_hex, MAX(CASE WHEN stock > 0 THEN 1 ELSE 0 END) AS has_stock
             FROM product_variants
             WHERE product_id = ? AND is_active = 1 AND color IS NOT NULL AND color <> ""
             GROUP BY color
             ORDER BY color ASC'
        );
        if (!$stmt) {
            return $rows;
        }
        $productId = (int)$productId;
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'color' => (string)$row['color'],
                'color_hex' => (string)($row['color_hex'] ?: '#111111'),
                'has_stock' => ((int)$row['has_stock']) === 1,
            ];
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function getVariantById($variantId)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT id, product_id, size, color, color_hex, sku, price_override, stock, is_active, created_at
             FROM product_variants
             WHERE id = ? LIMIT 1'
        );
        if (!$stmt) {
            return null;
        }
        $variantId = (int)$variantId;
        mysqli_stmt_bind_param($stmt, 'i', $variantId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result(
            $stmt,
            $id,
            $product_id,
            $size,
            $color,
            $color_hex,
            $sku,
            $price_override,
            $stock,
            $is_active,
            $created_at
        );
        $row = null;
        if (mysqli_stmt_fetch($stmt)) {
            $row = [
                'id' => (int)$id,
                'product_id' => (int)$product_id,
                'size' => (string)$size,
                'color' => (string)$color,
                'color_hex' => (string)$color_hex,
                'sku' => (string)$sku,
                'price_override' => $price_override !== null ? (float)$price_override : null,
                'stock' => (int)$stock,
                'is_active' => (int)$is_active,
                'created_at' => (string)$created_at,
            ];
        }
        mysqli_stmt_close($stmt);
        if (!$row) {
            return null;
        }
        return $row;
    }

    public function getVariantByOptions($productId, $size, $color)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT id, product_id, size, color, color_hex, sku, price_override, stock, is_active, created_at
             FROM product_variants
             WHERE product_id = ? AND size = ? AND color = ? AND is_active = 1
             LIMIT 1'
        );
        if (!$stmt) {
            return null;
        }
        $productId = (int)$productId;
        $size = trim((string)$size);
        $color = trim((string)$color);
        mysqli_stmt_bind_param($stmt, 'iss', $productId, $size, $color);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (!$row) {
            return null;
        }
        $row['id'] = (int)$row['id'];
        $row['product_id'] = (int)$row['product_id'];
        $row['stock'] = (int)$row['stock'];
        $row['is_active'] = (int)$row['is_active'];
        $row['price_override'] = $row['price_override'] !== null ? (float)$row['price_override'] : null;
        return $row;
    }

    public function createVariants($productId, $variants)
    {
        if (!is_array($variants) || empty($variants)) {
            return 0;
        }
        $stmt = mysqli_prepare(
            $this->conn,
            'INSERT INTO product_variants (product_id, size, color, color_hex, sku, price_override, stock, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        if (!$stmt) {
            return false;
        }

        $createdCount = 0;
        $productId = (int)$productId;
        foreach ($variants as $variant) {
            $size = trim((string)($variant['size'] ?? ''));
            $color = trim((string)($variant['color'] ?? ''));
            if ($size === '' || $color === '') {
                continue;
            }
            $colorHex = trim((string)($variant['color_hex'] ?? '#111111'));
            $sku = trim((string)($variant['sku'] ?? ''));
            $stock = max(0, (int)($variant['stock'] ?? 0));
            $isActive = isset($variant['is_active']) ? (int)$variant['is_active'] : 1;
            $priceOverride = $variant['price_override'];
            if ($priceOverride === '' || $priceOverride === null || (float)$priceOverride <= 0) {
                $priceOverride = null;
            } else {
                $priceOverride = (float)$priceOverride;
            }

            mysqli_stmt_bind_param(
                $stmt,
                'issssdii',
                $productId,
                $size,
                $color,
                $colorHex,
                $sku,
                $priceOverride,
                $stock,
                $isActive
            );

            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                return false;
            }
            $createdCount++;
        }

        mysqli_stmt_close($stmt);
        return $createdCount;
    }

    public function updateProductStockFromVariants($productId)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'UPDATE products p
             SET p.stock = (
                SELECT COALESCE(SUM(v.stock), 0)
                FROM product_variants v
                WHERE v.product_id = p.id AND v.is_active = 1
             )
             WHERE p.id = ?'
        );
        if (!$stmt) {
            return false;
        }
        $productId = (int)$productId;
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function variantExistsForOptions($productId, $size, $color, $excludeVariantId = null)
    {
        $productId = (int)$productId;
        $size = trim((string)$size);
        $color = trim((string)$color);
        $excludeVariantId = $excludeVariantId !== null ? (int)$excludeVariantId : null;

        if ($excludeVariantId !== null) {
            $stmt = mysqli_prepare(
                $this->conn,
                'SELECT COUNT(*) FROM product_variants
                 WHERE product_id = ? AND LOWER(size) = LOWER(?) AND LOWER(color) = LOWER(?) AND id <> ?'
            );
            if (!$stmt) {
                return false;
            }
            mysqli_stmt_bind_param($stmt, 'issi', $productId, $size, $color, $excludeVariantId);
        } else {
            $stmt = mysqli_prepare(
                $this->conn,
                'SELECT COUNT(*) FROM product_variants
                 WHERE product_id = ? AND LOWER(size) = LOWER(?) AND LOWER(color) = LOWER(?)'
            );
            if (!$stmt) {
                return false;
            }
            mysqli_stmt_bind_param($stmt, 'iss', $productId, $size, $color);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return ((int)$count) > 0;
    }

    public function generateVariantSku($productId, $size, $color)
    {
        $sizeCode = strtoupper(preg_replace('/[^A-Z0-9]+/i', '', (string)$size));
        $colorCode = strtoupper(substr(preg_replace('/[^A-Z0-9]+/i', '', (string)$color), 0, 3));
        if ($sizeCode === '') {
            $sizeCode = 'NA';
        }
        if ($colorCode === '') {
            $colorCode = 'CLR';
        }
        return 'FERRO-' . (int)$productId . '-' . $sizeCode . '-' . $colorCode;
    }

    public function addVariant($productId, $data)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'INSERT INTO product_variants (product_id, size, color, color_hex, sku, price_override, stock, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        if (!$stmt) {
            return false;
        }
        $productId = (int)$productId;
        $size = trim((string)$data['size']);
        $color = trim((string)$data['color']);
        $colorHex = trim((string)$data['color_hex']);
        $sku = trim((string)$data['sku']);
        $priceOverride = $data['price_override'];
        $stock = (int)$data['stock'];
        $isActive = (int)$data['is_active'];
        mysqli_stmt_bind_param($stmt, 'issssdii', $productId, $size, $color, $colorHex, $sku, $priceOverride, $stock, $isActive);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok ? (int)mysqli_insert_id($this->conn) : false;
    }

    public function updateVariant($variantId, $data)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'UPDATE product_variants
             SET size = ?, color = ?, color_hex = ?, sku = ?, price_override = ?, stock = ?, is_active = ?
             WHERE id = ?'
        );
        if (!$stmt) {
            return false;
        }
        $variantId = (int)$variantId;
        $size = trim((string)$data['size']);
        $color = trim((string)$data['color']);
        $colorHex = trim((string)$data['color_hex']);
        $sku = trim((string)$data['sku']);
        $priceOverride = $data['price_override'];
        $stock = (int)$data['stock'];
        $isActive = (int)$data['is_active'];
        mysqli_stmt_bind_param($stmt, 'ssssdiii', $size, $color, $colorHex, $sku, $priceOverride, $stock, $isActive, $variantId);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function setVariantActive($variantId, $isActive)
    {
        $stmt = mysqli_prepare($this->conn, 'UPDATE product_variants SET is_active = ? WHERE id = ?');
        if (!$stmt) {
            return false;
        }
        $variantId = (int)$variantId;
        $isActive = (int)$isActive;
        mysqli_stmt_bind_param($stmt, 'ii', $isActive, $variantId);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function deleteVariantIfSafe($variantId)
    {
        $variantId = (int)$variantId;
        $stmt = mysqli_prepare($this->conn, 'SELECT COUNT(*) FROM order_items WHERE variant_id = ?');
        if (!$stmt) {
            return ['success' => false, 'action' => 'none'];
        }
        mysqli_stmt_bind_param($stmt, 'i', $variantId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ((int)$count > 0) {
            $ok = $this->setVariantActive($variantId, 0);
            return ['success' => $ok, 'action' => 'deactivated'];
        }

        $stmtDelete = mysqli_prepare($this->conn, 'DELETE FROM product_variants WHERE id = ?');
        if (!$stmtDelete) {
            return ['success' => false, 'action' => 'none'];
        }
        mysqli_stmt_bind_param($stmtDelete, 'i', $variantId);
        $ok = mysqli_stmt_execute($stmtDelete);
        mysqli_stmt_close($stmtDelete);
        return ['success' => $ok, 'action' => 'deleted'];
    }

    public function getVariantStockSummary($productId)
    {
        $summary = [
            'total_variant_stock' => 0,
            'active_variant_count' => 0,
            'inactive_variant_count' => 0,
            'low_stock_variant_count' => 0,
        ];
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT
                COALESCE(SUM(CASE WHEN is_active = 1 THEN stock ELSE 0 END), 0) AS total_variant_stock,
                COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END), 0) AS active_variant_count,
                COALESCE(SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END), 0) AS inactive_variant_count,
                COALESCE(SUM(CASE WHEN is_active = 1 AND stock <= 5 THEN 1 ELSE 0 END), 0) AS low_stock_variant_count
             FROM product_variants
             WHERE product_id = ?'
        );
        if (!$stmt) {
            return $summary;
        }
        $productId = (int)$productId;
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $totalStock, $activeCount, $inactiveCount, $lowCount);
        if (mysqli_stmt_fetch($stmt)) {
            $summary = [
                'total_variant_stock' => (int)$totalStock,
                'active_variant_count' => (int)$activeCount,
                'inactive_variant_count' => (int)$inactiveCount,
                'low_stock_variant_count' => (int)$lowCount,
            ];
        }
        mysqli_stmt_close($stmt);
        return $summary;
    }

    public function getTotalVariantCount()
    {
        $stmt = mysqli_prepare($this->conn, 'SELECT COUNT(*) FROM product_variants WHERE is_active = 1');
        if (!$stmt) {
            return 0;
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $total);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return (int)$total;
    }

    public function getTotalVariantStock()
    {
        $stmt = mysqli_prepare($this->conn, 'SELECT COALESCE(SUM(stock), 0) FROM product_variants WHERE is_active = 1');
        if (!$stmt) {
            return 0;
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $total);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return (int)$total;
    }

    public function getLowStockVariantsCount($threshold = 5)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT COUNT(*) FROM product_variants WHERE is_active = 1 AND stock <= ?'
        );
        if (!$stmt) {
            return 0;
        }
        $threshold = max(0, (int)$threshold);
        mysqli_stmt_bind_param($stmt, 'i', $threshold);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return (int)$count;
    }

    public function getLowStockVariants($threshold = 5, $limit = 10)
    {
        $rows = [];
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT v.id, v.product_id, v.size, v.color, v.sku, v.stock, p.name AS product_name, p.category
             FROM product_variants v
             INNER JOIN products p ON p.id = v.product_id
             WHERE v.is_active = 1 AND v.stock <= ?
             ORDER BY v.stock ASC, p.name ASC
             LIMIT ?'
        );
        if (!$stmt) {
            return $rows;
        }
        $threshold = max(0, (int)$threshold);
        $limit = max(1, (int)$limit);
        mysqli_stmt_bind_param($stmt, 'ii', $threshold, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function syncAllProductStockFromVariants()
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'UPDATE products p
             SET p.stock = (
                SELECT COALESCE(SUM(v.stock), 0)
                FROM product_variants v
                WHERE v.product_id = p.id AND v.is_active = 1
             )
             WHERE EXISTS (
                SELECT 1 FROM product_variants v2 WHERE v2.product_id = p.id AND v2.is_active = 1
             )'
        );
        if (!$stmt) {
            return false;
        }
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function productHasVariants($productId)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT COUNT(*) FROM product_variants WHERE product_id = ? AND is_active = 1'
        );
        if (!$stmt) {
            return false;
        }
        $productId = (int)$productId;
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return ((int)$count) > 0;
    }

    public function getAdminProductsWithVariantStats()
    {
        $rows = [];
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT p.id, p.name, p.category, p.image, p.price, p.compare_price, p.stock, p.sold_count, p.created_at,
                    COUNT(v.id) AS variant_count,
                    COALESCE(SUM(v.stock), 0) AS variant_stock_sum,
                    COALESCE(SUM(CASE WHEN v.stock <= 5 THEN 1 ELSE 0 END), 0) AS low_stock_variant_count
             FROM products p
             LEFT JOIN product_variants v ON v.product_id = p.id AND v.is_active = 1
             GROUP BY p.id, p.name, p.category, p.image, p.price, p.compare_price, p.stock, p.sold_count, p.created_at
             ORDER BY p.created_at DESC'
        );
        if (!$stmt) {
            return $rows;
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $row['id'] = (int)$row['id'];
            $row['price'] = (float)$row['price'];
            $row['compare_price'] = $row['compare_price'] !== null ? (float)$row['compare_price'] : null;
            $row['stock'] = (int)$row['stock'];
            $row['sold_count'] = (int)$row['sold_count'];
            $row['variant_count'] = (int)$row['variant_count'];
            $row['variant_stock_sum'] = (int)$row['variant_stock_sum'];
            $row['low_stock_variant_count'] = (int)$row['low_stock_variant_count'];
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }
}
