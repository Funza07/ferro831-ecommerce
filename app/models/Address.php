<?php
class Address extends Model
{
    public function getByUserId($userId)
    {
        $addresses = [];
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT id, full_name, phone, address_line, city, state, pincode, is_default
             FROM user_addresses
             WHERE user_id = ?
             ORDER BY is_default DESC, created_at DESC, id DESC'
        );

        if (!$stmt) {
            return $addresses;
        }

        $userId = (int)$userId;
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $addresses[] = $row;
        }

        mysqli_stmt_close($stmt);
        return $addresses;
    }

    public function getByIdForUser($addressId, $userId)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT id, full_name, phone, address_line, city, state, pincode, is_default
             FROM user_addresses
             WHERE id = ? AND user_id = ?
             LIMIT 1'
        );

        if (!$stmt) {
            return null;
        }

        $addressId = (int)$addressId;
        $userId = (int)$userId;
        mysqli_stmt_bind_param($stmt, 'ii', $addressId, $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $address = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return $address ?: null;
    }

    public function create($userId, $data)
    {
        $isDefault = $this->countByUser($userId) === 0 ? 1 : 0;
        $stmt = mysqli_prepare(
            $this->conn,
            'INSERT INTO user_addresses (user_id, full_name, phone, address_line, city, state, pincode, is_default)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        if (!$stmt) {
            return false;
        }

        $userId = (int)$userId;
        mysqli_stmt_bind_param(
            $stmt,
            'issssssi',
            $userId,
            $data['full_name'],
            $data['phone'],
            $data['address_line'],
            $data['city'],
            $data['state'],
            $data['pincode'],
            $isDefault
        );

        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function update($addressId, $userId, $data)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'UPDATE user_addresses
             SET full_name = ?, phone = ?, address_line = ?, city = ?, state = ?, pincode = ?
             WHERE id = ? AND user_id = ?
             LIMIT 1'
        );

        if (!$stmt) {
            return false;
        }

        $addressId = (int)$addressId;
        $userId = (int)$userId;
        mysqli_stmt_bind_param(
            $stmt,
            'ssssssii',
            $data['full_name'],
            $data['phone'],
            $data['address_line'],
            $data['city'],
            $data['state'],
            $data['pincode'],
            $addressId,
            $userId
        );

        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function delete($addressId, $userId)
    {
        $address = $this->getByIdForUser($addressId, $userId);
        if (!$address) {
            return false;
        }

        mysqli_begin_transaction($this->conn);

        $stmt = mysqli_prepare($this->conn, 'DELETE FROM user_addresses WHERE id = ? AND user_id = ? LIMIT 1');
        if (!$stmt) {
            mysqli_rollback($this->conn);
            return false;
        }

        $addressId = (int)$addressId;
        $userId = (int)$userId;
        mysqli_stmt_bind_param($stmt, 'ii', $addressId, $userId);
        mysqli_stmt_execute($stmt);
        $deletedRows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if ($deletedRows !== 1) {
            mysqli_rollback($this->conn);
            return false;
        }

        if ((int)$address['is_default'] === 1) {
            $this->makeAnotherDefaultIfNeeded($userId);
        }

        mysqli_commit($this->conn);
        return true;
    }

    public function setDefault($addressId, $userId)
    {
        if (!$this->getByIdForUser($addressId, $userId)) {
            return false;
        }

        mysqli_begin_transaction($this->conn);

        $userId = (int)$userId;
        $stmtClear = mysqli_prepare($this->conn, 'UPDATE user_addresses SET is_default = 0 WHERE user_id = ?');
        if (!$stmtClear) {
            mysqli_rollback($this->conn);
            return false;
        }
        mysqli_stmt_bind_param($stmtClear, 'i', $userId);
        mysqli_stmt_execute($stmtClear);
        mysqli_stmt_close($stmtClear);

        $stmtDefault = mysqli_prepare(
            $this->conn,
            'UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ? LIMIT 1'
        );
        if (!$stmtDefault) {
            mysqli_rollback($this->conn);
            return false;
        }

        $addressId = (int)$addressId;
        mysqli_stmt_bind_param($stmtDefault, 'ii', $addressId, $userId);
        mysqli_stmt_execute($stmtDefault);
        $updatedRows = mysqli_stmt_affected_rows($stmtDefault);
        mysqli_stmt_close($stmtDefault);

        if ($updatedRows < 0) {
            mysqli_rollback($this->conn);
            return false;
        }

        mysqli_commit($this->conn);
        return true;
    }

    public function countByUser($userId)
    {
        $stmt = mysqli_prepare($this->conn, 'SELECT COUNT(*) AS total_addresses FROM user_addresses WHERE user_id = ?');
        if (!$stmt) {
            return 0;
        }

        $userId = (int)$userId;
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return (int)($row['total_addresses'] ?? 0);
    }

    public function makeAnotherDefaultIfNeeded($userId)
    {
        $stmtNext = mysqli_prepare(
            $this->conn,
            'SELECT id FROM user_addresses WHERE user_id = ? ORDER BY created_at ASC, id ASC LIMIT 1'
        );

        if (!$stmtNext) {
            return false;
        }

        $userId = (int)$userId;
        mysqli_stmt_bind_param($stmtNext, 'i', $userId);
        mysqli_stmt_execute($stmtNext);
        $result = mysqli_stmt_get_result($stmtNext);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmtNext);

        $nextDefaultId = (int)($row['id'] ?? 0);
        if ($nextDefaultId <= 0) {
            return true;
        }

        $stmtDefault = mysqli_prepare(
            $this->conn,
            'UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ? LIMIT 1'
        );
        if (!$stmtDefault) {
            return false;
        }

        mysqli_stmt_bind_param($stmtDefault, 'ii', $nextDefaultId, $userId);
        $ok = mysqli_stmt_execute($stmtDefault);
        mysqli_stmt_close($stmtDefault);
        return $ok;
    }
}
