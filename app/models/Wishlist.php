<?php
class Wishlist extends Model
{
    public function getByUserId($userId)
    {
        return $this->getForUser($userId);
    }

    public function getForUser($user_id)
    {
        $items = [];
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT w.id AS wishlist_id, p.id AS product_id, p.name, p.category, p.price, p.image
             FROM wishlist w
             INNER JOIN products p ON p.id = w.product_id
             WHERE w.user_id = ?
             ORDER BY w.created_at DESC, w.id DESC'
        );
        if (!$stmt) {
            return $items;
        }
        $user_id = (int)$user_id;
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $items;
    }

    public function getProductIdsForUser($user_id, array $product_ids)
    {
        if (empty($product_ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $sql = "SELECT product_id FROM wishlist WHERE user_id = ? AND product_id IN ($placeholders)";
        $stmt = mysqli_prepare($this->conn, $sql);
        if (!$stmt) {
            return [];
        }

        $types = 'i' . str_repeat('i', count($product_ids));
        $values = array_merge([(int)$user_id], array_map('intval', $product_ids));
        $params = [$types];
        foreach ($values as $key => $value) {
            $values[$key] = $value;
            $params[] = &$values[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $ids = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $ids[] = (int)$row['product_id'];
        }
        mysqli_stmt_close($stmt);
        return $ids;
    }

    public function exists($user_id, $product_id)
    {
        return $this->isInWishlist($user_id, $product_id);
    }

    public function isInWishlist($userId, $productId)
    {
        $stmt = mysqli_prepare($this->conn, 'SELECT id FROM wishlist WHERE user_id = ? AND product_id = ? LIMIT 1');
        if (!$stmt) {
            return false;
        }
        $userId = (int)$userId;
        $productId = (int)$productId;
        mysqli_stmt_bind_param($stmt, 'ii', $userId, $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $exists = mysqli_fetch_assoc($result) ? true : false;
        mysqli_stmt_close($stmt);
        return $exists;
    }

    public function add($userId, $productId)
    {
        if ($this->isInWishlist($userId, $productId)) {
            return true;
        }

        $stmt = mysqli_prepare($this->conn, 'INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)');
        if (!$stmt) {
            return false;
        }

        $userId = (int)$userId;
        $productId = (int)$productId;
        mysqli_stmt_bind_param($stmt, 'ii', $userId, $productId);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function remove($userId, $productId)
    {
        $stmt = mysqli_prepare($this->conn, 'DELETE FROM wishlist WHERE user_id = ? AND product_id = ? LIMIT 1');
        if (!$stmt) {
            return false;
        }

        $userId = (int)$userId;
        $productId = (int)$productId;
        mysqli_stmt_bind_param($stmt, 'ii', $userId, $productId);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function removeByWishlistId($userId, $wishlistId)
    {
        $stmt = mysqli_prepare($this->conn, 'DELETE FROM wishlist WHERE id = ? AND user_id = ? LIMIT 1');
        if (!$stmt) {
            return false;
        }

        $userId = (int)$userId;
        $wishlistId = (int)$wishlistId;
        mysqli_stmt_bind_param($stmt, 'ii', $wishlistId, $userId);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function getWishlistItemForUser($userId, $productId)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT id, user_id, product_id FROM wishlist WHERE user_id = ? AND product_id = ? LIMIT 1'
        );
        if (!$stmt) {
            return null;
        }

        $userId = (int)$userId;
        $productId = (int)$productId;
        mysqli_stmt_bind_param($stmt, 'ii', $userId, $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $item = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $item ?: null;
    }

    public function getWishlistItemByIdForUser($userId, $wishlistId, $productId = 0)
    {
        $sql = 'SELECT id, user_id, product_id FROM wishlist WHERE id = ? AND user_id = ?';
        $types = 'ii';
        $values = [(int)$wishlistId, (int)$userId];

        if ((int)$productId > 0) {
            $sql .= ' AND product_id = ?';
            $types .= 'i';
            $values[] = (int)$productId;
        }

        $sql .= ' LIMIT 1';
        $stmt = mysqli_prepare($this->conn, $sql);
        if (!$stmt) {
            return null;
        }

        $params = [$types];
        foreach ($values as $key => $value) {
            $values[$key] = $value;
            $params[] = &$values[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $item = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $item ?: null;
    }
}
