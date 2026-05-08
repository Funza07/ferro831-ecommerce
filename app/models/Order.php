<?php
class Order extends Model
{
    public function getTotalOrders()
    {
        return (int)$this->single('SELECT COUNT(*) FROM orders');
    }

    public function getPendingOrdersCount()
    {
        return (int)$this->single("SELECT COUNT(*) FROM orders WHERE order_status = 'Pending'");
    }

    public function getDeliveredOrdersCount()
    {
        return (int)$this->single("SELECT COUNT(*) FROM orders WHERE order_status = 'Delivered'");
    }

    public function getDeliveredRevenue()
    {
        return (float)$this->single("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status = 'Delivered'");
    }

    public function getRecentOrders($limit = 5)
    {
        $rows = [];
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT id, customer_name, customer_phone, total_amount, order_status, created_at
             FROM orders
             ORDER BY created_at DESC
             LIMIT ?'
        );
        if (!$stmt) {
            return $rows;
        }
        $limit = max(1, (int)$limit);
        mysqli_stmt_bind_param($stmt, 'i', $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function getOrdersCountLastDays($days = 7)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT COUNT(*) FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)'
        );
        if (!$stmt) {
            return 0;
        }
        $days = max(1, (int)$days);
        mysqli_stmt_bind_param($stmt, 'i', $days);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_row($result);
        mysqli_stmt_close($stmt);
        return (int)($row[0] ?? 0);
    }

    public function getRevenueLastDays($days = 7)
    {
        $days = max(1, (int)$days);
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $dateKey = date('Y-m-d', strtotime('-' . $i . ' days'));
            $data[$dateKey] = [
                'date' => $dateKey,
                'revenue' => 0.0,
                'orders' => 0,
            ];
        }

        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT DATE(created_at) AS day_key, COALESCE(SUM(total_amount), 0) AS day_revenue, COUNT(*) AS day_orders
             FROM orders
             WHERE order_status = 'Delivered'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(created_at)
             ORDER BY day_key ASC"
        );
        if (!$stmt) {
            return array_values($data);
        }

        mysqli_stmt_bind_param($stmt, 'i', $days);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $key = (string)$row['day_key'];
            if (isset($data[$key])) {
                $data[$key]['revenue'] = (float)$row['day_revenue'];
                $data[$key]['orders'] = (int)$row['day_orders'];
            }
        }
        mysqli_stmt_close($stmt);

        return array_values($data);
    }

    public function getOrderStatusCounts()
    {
        $base = [
            'Pending' => 0,
            'Confirmed' => 0,
            'Packed' => 0,
            'Shipped' => 0,
            'Delivered' => 0,
            'Cancelled' => 0,
        ];

        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT order_status, COUNT(*) AS count_status FROM orders GROUP BY order_status'
        );
        if (!$stmt) {
            return $base;
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $status = (string)$row['order_status'];
            if (array_key_exists($status, $base)) {
                $base[$status] = (int)$row['count_status'];
            }
        }
        mysqli_stmt_close($stmt);

        return $base;
    }

    public function getOrdersByUserId($userId)
    {
        return $this->getByUser($userId);
    }

    public function getOrderByIdForUser($orderId, $userId)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT id, customer_name, customer_email, customer_phone, customer_address, total_amount, order_status, tracking_number, created_at
             FROM orders
             WHERE id = ? AND user_id = ?
             LIMIT 1'
        );
        if (!$stmt) {
            return null;
        }

        $orderId = (int)$orderId;
        $userId = (int)$userId;
        mysqli_stmt_bind_param($stmt, 'ii', $orderId, $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return $order ?: null;
    }

    public function getAllOrders()
    {
        return $this->rows(
            'SELECT id, customer_name, customer_phone, total_amount, order_status, tracking_number, created_at
             FROM orders
             ORDER BY created_at DESC'
        );
    }

    public function getOrderById($orderId)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT id, customer_name, customer_email, customer_phone, customer_address, total_amount, order_status, tracking_number, created_at
             FROM orders
             WHERE id = ?
             LIMIT 1'
        );

        if (!$stmt) {
            return null;
        }

        $orderId = (int)$orderId;
        mysqli_stmt_bind_param($stmt, 'i', $orderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return $order ?: null;
    }

    public function getOrderItems($orderId)
    {
        $items = [];
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT product_name, product_price, quantity, total_price, variant_id, size, color, sku
             FROM order_items
             WHERE order_id = ?
             ORDER BY id ASC'
        );

        if (!$stmt) {
            return $items;
        }

        $orderId = (int)$orderId;
        mysqli_stmt_bind_param($stmt, 'i', $orderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($item = mysqli_fetch_assoc($result)) {
            $items[] = $item;
        }

        mysqli_stmt_close($stmt);
        return $items;
    }

    public function getOrderStatusHistory($orderId)
    {
        $history = [];
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT status, note, created_at
             FROM order_status_history
             WHERE order_id = ?
             ORDER BY created_at ASC, id ASC'
        );

        if (!$stmt) {
            return $history;
        }

        $orderId = (int)$orderId;
        mysqli_stmt_bind_param($stmt, 'i', $orderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $history[] = $row;
        }

        mysqli_stmt_close($stmt);
        return $history;
    }

    public function updateStatusAndTracking($orderId, $status, $trackingNumber, $note)
    {
        $orderId = (int)$orderId;
        $current = $this->getOrderById($orderId);

        if (!$current) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        $currentStatus = (string)$current['order_status'];
        $currentTrackingNumber = trim((string)($current['tracking_number'] ?? ''));
        $statusChanged = ($status !== $currentStatus);
        $trackingChanged = ($trackingNumber !== $currentTrackingNumber);

        if (!$statusChanged && !$trackingChanged) {
            return ['success' => true, 'message' => 'No changes were made.'];
        }

        mysqli_begin_transaction($this->conn);

        $stmtUpdate = mysqli_prepare(
            $this->conn,
            'UPDATE orders SET order_status = ?, tracking_number = ? WHERE id = ? LIMIT 1'
        );

        if (!$stmtUpdate) {
            mysqli_rollback($this->conn);
            return ['success' => false, 'message' => 'Could not prepare order update.'];
        }

        mysqli_stmt_bind_param($stmtUpdate, 'ssi', $status, $trackingNumber, $orderId);
        if (!mysqli_stmt_execute($stmtUpdate)) {
            mysqli_stmt_close($stmtUpdate);
            mysqli_rollback($this->conn);
            return ['success' => false, 'message' => 'Could not update order details.'];
        }
        mysqli_stmt_close($stmtUpdate);

        if ($statusChanged) {
            $statusNote = trim((string)$note);
            if ($statusNote === '') {
                $statusNote = 'Status updated by admin';
            }

            $stmtHistory = mysqli_prepare(
                $this->conn,
                'INSERT INTO order_status_history (order_id, status, note) VALUES (?, ?, ?)'
            );

            if (!$stmtHistory) {
                mysqli_rollback($this->conn);
                return ['success' => false, 'message' => 'Could not prepare status history update.'];
            }

            mysqli_stmt_bind_param($stmtHistory, 'iss', $orderId, $status, $statusNote);
            if (!mysqli_stmt_execute($stmtHistory)) {
                mysqli_stmt_close($stmtHistory);
                mysqli_rollback($this->conn);
                return ['success' => false, 'message' => 'Could not save status history.'];
            }
            mysqli_stmt_close($stmtHistory);
        }

        mysqli_commit($this->conn);

        return [
            'success' => true,
            'message' => $statusChanged ? 'Order status updated successfully.' : 'Tracking number updated successfully.',
        ];
    }

    public function placeFromCart(array $cart, array $customer, $user_id_for_order = null)
    {
        $grand_total = 0;
        $validated_items = [];
        $order_id = null;
        $order_error = '';

        mysqli_begin_transaction($this->conn);

        foreach ($cart as $cart_key => $quantity) {
            $cart_key = (string)$cart_key;
            $parts = strpos($cart_key, ':') !== false ? explode(':', $cart_key, 2) : [$cart_key, '0'];
            $product_id = (int)($parts[0] ?? 0);
            $variant_id = (int)($parts[1] ?? 0);
            $quantity = (int)$quantity;

            if ($quantity <= 0) {
                $order_error = 'Invalid quantity found in cart.';
                break;
            }

            $stmt_product = mysqli_prepare(
                $this->conn,
                'SELECT id, name, price, stock, sku FROM products WHERE id = ? LIMIT 1 FOR UPDATE'
            );

            if (!$stmt_product) {
                $order_error = 'Could not validate cart products. Please try again.';
                break;
            }

            mysqli_stmt_bind_param($stmt_product, 'i', $product_id);
            mysqli_stmt_execute($stmt_product);
            $product_result = mysqli_stmt_get_result($stmt_product);
            $product = mysqli_fetch_assoc($product_result);
            mysqli_stmt_close($stmt_product);

            if (!$product) {
                $order_error = 'One of the products in your cart no longer exists.';
                break;
            }

            $selected_variant = null;
            $has_variants = false;
            $stmt_variant_count = mysqli_prepare(
                $this->conn,
                'SELECT COUNT(*) AS total FROM product_variants WHERE product_id = ? AND is_active = 1'
            );
            if ($stmt_variant_count) {
                mysqli_stmt_bind_param($stmt_variant_count, 'i', $product_id);
                mysqli_stmt_execute($stmt_variant_count);
                $variant_count_result = mysqli_stmt_get_result($stmt_variant_count);
                $variant_count_row = mysqli_fetch_assoc($variant_count_result);
                $has_variants = ((int)($variant_count_row['total'] ?? 0)) > 0;
                mysqli_stmt_close($stmt_variant_count);
            }

            if ($variant_id > 0) {
                $stmt_variant = mysqli_prepare(
                    $this->conn,
                    'SELECT id, product_id, size, color, sku, price_override, stock, is_active
                     FROM product_variants
                     WHERE id = ?
                     LIMIT 1
                     FOR UPDATE'
                );
                if (!$stmt_variant) {
                    $order_error = 'Could not validate product variant. Please try again.';
                    break;
                }
                mysqli_stmt_bind_param($stmt_variant, 'i', $variant_id);
                mysqli_stmt_execute($stmt_variant);
                $variant_result = mysqli_stmt_get_result($stmt_variant);
                $selected_variant = mysqli_fetch_assoc($variant_result);
                mysqli_stmt_close($stmt_variant);
                if (!$selected_variant || (int)$selected_variant['is_active'] !== 1 || (int)$selected_variant['product_id'] !== $product_id) {
                    $order_error = 'Invalid variant selected for ' . htmlspecialchars((string)$product['name'], ENT_QUOTES, 'UTF-8') . '.';
                    break;
                }
            } elseif ($has_variants) {
                $order_error = 'Please re-add ' . htmlspecialchars((string)$product['name'], ENT_QUOTES, 'UTF-8') . ' with a valid size and colour.';
                break;
            }

            $stock = $selected_variant ? (int)$selected_variant['stock'] : (int)$product['stock'];
            if ($stock < $quantity) {
                $order_error = 'Insufficient stock for ' . htmlspecialchars((string)$product['name'], ENT_QUOTES, 'UTF-8') . '. Available: ' . $stock . '.';
                break;
            }

            $variant_override = ($selected_variant && $selected_variant['price_override'] !== null)
                ? (float)$selected_variant['price_override']
                : null;
            $unit_price = ($variant_override !== null && $variant_override > 0)
                ? $variant_override
                : (float)$product['price'];
            $item_total = $unit_price * $quantity;
            $grand_total += $item_total;

            $validated_items[] = [
                'product_id' => (int)$product['id'],
                'variant_id' => $selected_variant ? (int)$selected_variant['id'] : null,
                'product_name' => $product['name'],
                'product_price' => $unit_price,
                'quantity' => $quantity,
                'total_price' => $item_total,
                'size' => $selected_variant ? (string)$selected_variant['size'] : null,
                'color' => $selected_variant ? (string)$selected_variant['color'] : null,
                'sku' => $selected_variant && !empty($selected_variant['sku']) ? (string)$selected_variant['sku'] : (string)($product['sku'] ?? ''),
            ];
        }

        if ($order_error === '') {
            $stmt_order = mysqli_prepare(
                $this->conn,
                'INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, customer_address, total_amount)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );

            if (!$stmt_order) {
                $order_error = 'Could not create order. Please try again.';
            } else {
                $customer_name = $customer['name'];
                $customer_email = $customer['email'];
                $customer_phone = $customer['phone'];
                $customer_address = $customer['address'];

                mysqli_stmt_bind_param(
                    $stmt_order,
                    'issssd',
                    $user_id_for_order,
                    $customer_name,
                    $customer_email,
                    $customer_phone,
                    $customer_address,
                    $grand_total
                );

                if (!mysqli_stmt_execute($stmt_order)) {
                    $order_error = 'Could not create order. Please try again.';
                } else {
                    $order_id = mysqli_insert_id($this->conn);
                }
                mysqli_stmt_close($stmt_order);
            }
        }

        if ($order_error === '') {
            $stmt_status_history = mysqli_prepare(
                $this->conn,
                'INSERT INTO order_status_history (order_id, status, note) VALUES (?, ?, ?)'
            );

            if ($stmt_status_history) {
                $initial_status = 'Pending';
                $initial_note = 'Order placed successfully';
                mysqli_stmt_bind_param($stmt_status_history, 'iss', $order_id, $initial_status, $initial_note);
                if (!mysqli_stmt_execute($stmt_status_history)) {
                    $order_error = 'Could not save order status history. Please try again.';
                }
                mysqli_stmt_close($stmt_status_history);
            } else {
                $order_error = 'Could not initialize order status history. Please try again.';
            }
        }

        if ($order_error === '') {
            foreach ($validated_items as $item) {
                $stmt_item = mysqli_prepare(
                    $this->conn,
                    'INSERT INTO order_items (order_id, product_id, variant_id, product_name, product_price, quantity, total_price, size, color, sku)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );

                if (!$stmt_item) {
                    $order_error = 'Could not save order items. Please try again.';
                    break;
                }

                mysqli_stmt_bind_param(
                    $stmt_item,
                    'iiisiddsss',
                    $order_id,
                    $item['product_id'],
                    $item['variant_id'],
                    $item['product_name'],
                    $item['product_price'],
                    $item['quantity'],
                    $item['total_price'],
                    $item['size'],
                    $item['color'],
                    $item['sku']
                );

                if (!mysqli_stmt_execute($stmt_item)) {
                    $order_error = 'Could not save order items. Please try again.';
                    mysqli_stmt_close($stmt_item);
                    break;
                }
                mysqli_stmt_close($stmt_item);

                $usedVariantStock = false;
                if (!empty($item['variant_id'])) {
                    $stmt_stock = mysqli_prepare(
                        $this->conn,
                        'UPDATE product_variants SET stock = stock - ? WHERE id = ? AND product_id = ? AND stock >= ?'
                    );
                    $usedVariantStock = true;
                } else {
                    $stmt_stock = mysqli_prepare(
                        $this->conn,
                        'UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?'
                    );
                }

                if (!$stmt_stock) {
                    $order_error = 'Could not update stock. Please try again.';
                    break;
                }

                if (!empty($item['variant_id'])) {
                    mysqli_stmt_bind_param(
                        $stmt_stock,
                        'iiii',
                        $item['quantity'],
                        $item['variant_id'],
                        $item['product_id'],
                        $item['quantity']
                    );
                } else {
                    mysqli_stmt_bind_param(
                        $stmt_stock,
                        'iii',
                        $item['quantity'],
                        $item['product_id'],
                        $item['quantity']
                    );
                }
                mysqli_stmt_execute($stmt_stock);
                $affected_rows = mysqli_stmt_affected_rows($stmt_stock);
                mysqli_stmt_close($stmt_stock);

                if ($affected_rows !== 1) {
                    $order_error = 'Stock changed for ' . htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') . '. Please review cart and try again.';
                    break;
                }

                if ($usedVariantStock) {
                    $stmt_sync = mysqli_prepare(
                        $this->conn,
                        'UPDATE products p
                         SET p.stock = (
                            SELECT COALESCE(SUM(v.stock), 0)
                            FROM product_variants v
                            WHERE v.product_id = p.id AND v.is_active = 1
                         )
                         WHERE p.id = ?'
                    );
                    if (!$stmt_sync) {
                        $order_error = 'Could not sync product stock from variants. Please try again.';
                        break;
                    }
                    mysqli_stmt_bind_param($stmt_sync, 'i', $item['product_id']);
                    if (!mysqli_stmt_execute($stmt_sync)) {
                        mysqli_stmt_close($stmt_sync);
                        $order_error = 'Could not sync product stock from variants. Please try again.';
                        break;
                    }
                    mysqli_stmt_close($stmt_sync);
                }

                $stmt_sold = mysqli_prepare(
                    $this->conn,
                    'UPDATE products SET sold_count = sold_count + ? WHERE id = ?'
                );
                if (!$stmt_sold) {
                    $order_error = 'Could not update sold count. Please try again.';
                    break;
                }
                mysqli_stmt_bind_param($stmt_sold, 'ii', $item['quantity'], $item['product_id']);
                if (!mysqli_stmt_execute($stmt_sold)) {
                    mysqli_stmt_close($stmt_sold);
                    $order_error = 'Could not update sold count. Please try again.';
                    break;
                }
                mysqli_stmt_close($stmt_sold);
            }
        }

        if ($order_error === '') {
            mysqli_commit($this->conn);
            return [
                'success' => true,
                'order_id' => $order_id,
                'customer_name' => $customer['name'],
            ];
        }

        mysqli_rollback($this->conn);
        return [
            'success' => false,
            'error' => $order_error,
        ];
    }

    public function getByUser($user_id)
    {
        $orders = [];
        $stmt = mysqli_prepare($this->conn, 'SELECT id, total_amount, order_status, tracking_number, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC');
        if (!$stmt) {
            return $orders;
        }
        $user_id = (int)$user_id;
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $orders;
    }

    public function dashboardStats()
    {
        return [
            'total_products' => $this->single('SELECT COUNT(*) FROM products'),
            'total_stock_available' => $this->single('SELECT COALESCE(SUM(stock), 0) FROM products'),
            'total_units_sold' => $this->single('SELECT COALESCE(SUM(sold_count), 0) FROM products'),
            'total_orders' => $this->single('SELECT COUNT(*) FROM orders'),
            'pending_orders' => $this->single("SELECT COUNT(*) FROM orders WHERE order_status = 'Pending'"),
            'delivered_orders' => $this->single("SELECT COUNT(*) FROM orders WHERE order_status = 'Delivered'"),
            'delivered_revenue' => $this->single("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status = 'Delivered'"),
            'low_stock_count' => $this->single('SELECT COUNT(*) FROM products WHERE stock <= 5'),
        ];
    }

    public function recentOrders()
    {
        return $this->getRecentOrders(5);
    }

    public function topSellingProducts()
    {
        return $this->rows('SELECT product_id, product_name, COALESCE(SUM(quantity), 0) AS units_sold, COALESCE(SUM(total_price), 0) AS sales_total FROM order_items GROUP BY product_id, product_name ORDER BY units_sold DESC, sales_total DESC LIMIT 5');
    }

    private function single($sql)
    {
        $stmt = mysqli_prepare($this->conn, $sql);
        if (!$stmt) {
            return 0;
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_row($result);
        mysqli_stmt_close($stmt);
        return $row[0] ?? 0;
    }

    private function rows($sql)
    {
        $rows = [];
        $stmt = mysqli_prepare($this->conn, $sql);
        if (!$stmt) {
            return $rows;
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }
}
