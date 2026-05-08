<?php
class Cart extends Model
{
    public function ensureCart()
    {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    private function makeCartKey($productId, $variantId = 0)
    {
        return (int)$productId . ':' . (int)$variantId;
    }

    private function parseCartKey($key)
    {
        $key = (string)$key;
        if (strpos($key, ':') !== false) {
            [$productId, $variantId] = explode(':', $key, 2);
            return ['product_id' => (int)$productId, 'variant_id' => (int)$variantId];
        }
        return ['product_id' => (int)$key, 'variant_id' => 0];
    }

    public function getProductForCart($productId)
    {
        $stmt = mysqli_prepare($this->conn, 'SELECT id, name, image, price, stock, sku FROM products WHERE id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $productId = (int)$productId;
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $product ?: null;
    }

    private function getVariantForCart($variantId)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT id, product_id, size, color, color_hex, sku, price_override, stock, is_active
             FROM product_variants
             WHERE id = ? LIMIT 1'
        );
        if (!$stmt) {
            return null;
        }
        $variantId = (int)$variantId;
        mysqli_stmt_bind_param($stmt, 'i', $variantId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $variant = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $variant ?: null;
    }

    private function productHasVariants($productId)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT COUNT(*) AS total FROM product_variants WHERE product_id = ? AND is_active = 1'
        );
        if (!$stmt) {
            return false;
        }
        $productId = (int)$productId;
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return ((int)($row['total'] ?? 0)) > 0;
    }

    public function validateQuantityAgainstStock($productId, $quantity, $variantId = 0)
    {
        $product = $this->getProductForCart($productId);
        if (!$product) {
            return ['valid' => false, 'reason' => 'not_found', 'product' => null, 'variant' => null, 'allowed_quantity' => 0];
        }

        $variantId = (int)$variantId;
        $variant = null;
        if ($variantId > 0) {
            $variant = $this->getVariantForCart($variantId);
            if (!$variant || (int)$variant['is_active'] !== 1 || (int)$variant['product_id'] !== (int)$product['id']) {
                return ['valid' => false, 'reason' => 'invalid_variant', 'product' => $product, 'variant' => null, 'allowed_quantity' => 0];
            }
        } elseif ($this->productHasVariants($productId)) {
            return ['valid' => false, 'reason' => 'variant_required', 'product' => $product, 'variant' => null, 'allowed_quantity' => 0];
        }

        $stock = $variant ? (int)$variant['stock'] : (int)$product['stock'];
        if ($stock <= 0) {
            return ['valid' => false, 'reason' => 'out_of_stock', 'product' => $product, 'variant' => $variant, 'allowed_quantity' => 0];
        }

        $quantity = (int)$quantity;
        if ($quantity > $stock) {
            return ['valid' => false, 'reason' => 'exceeds_stock', 'product' => $product, 'variant' => $variant, 'allowed_quantity' => $stock];
        }

        return ['valid' => true, 'reason' => 'ok', 'product' => $product, 'variant' => $variant, 'allowed_quantity' => $quantity];
    }

    public function getCartItems($cartSession)
    {
        $items = [];
        if (!is_array($cartSession)) {
            return $items;
        }

        foreach ($cartSession as $key => $quantity) {
            $parsed = $this->parseCartKey($key);
            $productId = (int)$parsed['product_id'];
            $variantId = (int)$parsed['variant_id'];

            $product = $this->getProductForCart($productId);
            if (!$product) {
                continue;
            }

            $variant = null;
            if ($variantId > 0) {
                $variant = $this->getVariantForCart($variantId);
                if (!$variant || (int)$variant['is_active'] !== 1 || (int)$variant['product_id'] !== $productId) {
                    continue;
                }
            }

            $quantity = (int)$quantity;
            $variantOverride = $variant && $variant['price_override'] !== null ? (float)$variant['price_override'] : null;
            $unitPrice = ($variantOverride !== null && $variantOverride > 0) ? $variantOverride : (float)$product['price'];
            $stock = $variant ? (int)$variant['stock'] : (int)$product['stock'];
            $lineTotal = $unitPrice * $quantity;
            $items[] = [
                'cart_key' => $this->makeCartKey($productId, $variantId),
                'product_id' => $productId,
                'variant_id' => $variantId,
                'product' => $product,
                'variant' => $variant,
                'unit_price' => $unitPrice,
                'stock' => $stock,
                'quantity' => $quantity,
                'total' => $lineTotal,
            ];
        }

        return $items;
    }

    public function calculateTotals($items)
    {
        $grandTotal = 0.0;
        foreach ($items as $item) {
            $grandTotal += (float)($item['total'] ?? 0);
        }
        return [
            'grand_total' => $grandTotal,
        ];
    }

    public function add($product_id, $quantity, $variant_id = 0)
    {
        $this->ensureCart();
        $product_id = (int)$product_id;
        $variant_id = (int)$variant_id;
        $quantity = max(1, (int)$quantity);

        $validation = $this->validateQuantityAgainstStock($product_id, $quantity, $variant_id);
        if (($validation['reason'] ?? '') === 'not_found') {
            return 'Product not found.';
        }
        if (($validation['reason'] ?? '') === 'variant_required') {
            return 'Please select a valid size and colour.';
        }
        if (($validation['reason'] ?? '') === 'invalid_variant') {
            return 'Selected variant is invalid for this product.';
        }

        $product = $validation['product'];
        $variant = $validation['variant'];
        if (($validation['reason'] ?? '') === 'out_of_stock') {
            $name = htmlspecialchars((string)$product['name'], ENT_QUOTES, 'UTF-8');
            return $variant ? ($name . ' (' . htmlspecialchars((string)$variant['size'] . ' / ' . (string)$variant['color'], ENT_QUOTES, 'UTF-8') . ') is out of stock.') : ($name . ' is out of stock.');
        }

        $stock = $variant ? (int)$variant['stock'] : (int)$product['stock'];
        $cartKey = $this->makeCartKey($product_id, $variant_id);
        $currentQty = isset($_SESSION['cart'][$cartKey]) ? (int)$_SESSION['cart'][$cartKey] : 0;
        $requestedTotal = $currentQty + $quantity;
        $finalQty = min($requestedTotal, $stock);
        $_SESSION['cart'][$cartKey] = $finalQty;

        if ($finalQty < $requestedTotal) {
            $name = htmlspecialchars((string)$product['name'], ENT_QUOTES, 'UTF-8');
            return 'Quantity adjusted for ' . $name . ' (only ' . $stock . ' left).';
        }
        return '';
    }

    public function update(array $quantities)
    {
        $this->ensureCart();
        $warnings = [];
        foreach ($quantities as $cartKey => $quantity) {
            $parsed = $this->parseCartKey($cartKey);
            $product_id = (int)$parsed['product_id'];
            $variant_id = (int)$parsed['variant_id'];
            $normalizedKey = $this->makeCartKey($product_id, $variant_id);
            $quantity = (int)$quantity;

            if ($quantity <= 0) {
                unset($_SESSION['cart'][$normalizedKey], $_SESSION['cart'][(string)$cartKey]);
                continue;
            }

            $validation = $this->validateQuantityAgainstStock($product_id, $quantity, $variant_id);
            if (($validation['reason'] ?? '') === 'not_found' || ($validation['reason'] ?? '') === 'invalid_variant') {
                unset($_SESSION['cart'][$normalizedKey], $_SESSION['cart'][(string)$cartKey]);
                continue;
            }

            $product = $validation['product'];
            $stock = (int)($validation['variant'] ? $validation['variant']['stock'] : $product['stock']);
            if (($validation['reason'] ?? '') === 'out_of_stock') {
                unset($_SESSION['cart'][$normalizedKey], $_SESSION['cart'][(string)$cartKey]);
                $warnings[] = htmlspecialchars((string)$product['name'], ENT_QUOTES, 'UTF-8') . ' is now out of stock and was removed.';
            } elseif (($validation['reason'] ?? '') === 'exceeds_stock') {
                $_SESSION['cart'][$normalizedKey] = $stock;
                $warnings[] = 'Quantity adjusted for ' . htmlspecialchars((string)$product['name'], ENT_QUOTES, 'UTF-8') . ' to ' . $stock . '.';
            } else {
                $_SESSION['cart'][$normalizedKey] = $quantity;
            }
            if ((string)$cartKey !== $normalizedKey) {
                unset($_SESSION['cart'][(string)$cartKey]);
            }
        }
        return implode(' ', $warnings);
    }

    public function normalize()
    {
        $this->ensureCart();
        $warnings = [];
        $normalized = [];

        foreach ($_SESSION['cart'] as $key => $quantity) {
            $parsed = $this->parseCartKey($key);
            $product_id = (int)$parsed['product_id'];
            $variant_id = (int)$parsed['variant_id'];
            $normalizedKey = $this->makeCartKey($product_id, $variant_id);
            $quantity = (int)$quantity;
            if ($quantity <= 0) {
                continue;
            }

            $validation = $this->validateQuantityAgainstStock($product_id, $quantity, $variant_id);
            if (($validation['reason'] ?? '') === 'not_found' || ($validation['reason'] ?? '') === 'invalid_variant' || ($validation['reason'] ?? '') === 'variant_required') {
                continue;
            }

            $product = $validation['product'];
            $stock = (int)($validation['variant'] ? $validation['variant']['stock'] : $product['stock']);
            if (($validation['reason'] ?? '') === 'out_of_stock') {
                $warnings[] = htmlspecialchars((string)$product['name'], ENT_QUOTES, 'UTF-8') . ' is out of stock and was removed from your cart.';
                continue;
            }
            if (($validation['reason'] ?? '') === 'exceeds_stock') {
                $normalized[$normalizedKey] = $stock;
                $warnings[] = 'Quantity adjusted for ' . htmlspecialchars((string)$product['name'], ENT_QUOTES, 'UTF-8') . ' to ' . $stock . '.';
                continue;
            }
            if (!isset($normalized[$normalizedKey])) {
                $normalized[$normalizedKey] = 0;
            }
            $normalized[$normalizedKey] += $quantity;
            if ($normalized[$normalizedKey] > $stock) {
                $normalized[$normalizedKey] = $stock;
            }
        }

        $_SESSION['cart'] = $normalized;
        return implode(' ', $warnings);
    }

    public function getItems()
    {
        $this->ensureCart();
        $items = $this->getCartItems($_SESSION['cart']);
        $totals = $this->calculateTotals($items);
        return ['items' => $items, 'grand_total' => $totals['grand_total']];
    }

    public function fetchProduct($product_id)
    {
        return $this->getProductForCart($product_id);
    }
}
