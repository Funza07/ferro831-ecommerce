<?php
class User extends Model
{
    public function getById($id)
    {
        $stmt = mysqli_prepare($this->conn, 'SELECT id, name, email, phone, address FROM users WHERE id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }

        $id = (int)$id;
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return $user ?: null;
    }

    public function updateProfile($id, $data)
    {
        $stmt = mysqli_prepare($this->conn, 'UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ? LIMIT 1');
        if (!$stmt) {
            return false;
        }

        $id = (int)$id;
        mysqli_stmt_bind_param($stmt, 'sssi', $data['name'], $data['phone'], $data['address'], $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok;
    }

    public function findByEmail($email)
    {
        $stmt = mysqli_prepare($this->conn, 'SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $user ?: null;
    }

    public function emailExists($email)
    {
        $stmt = mysqli_prepare($this->conn, 'SELECT id FROM users WHERE email = ? LIMIT 1');
        if (!$stmt) {
            return false;
        }
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $exists = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        return $exists;
    }

    public function create($data)
    {
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $role = $data['role'] ?? 'user';
        $stmt = mysqli_prepare($this->conn, 'INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        if (!$stmt) {
            return false;
        }
        mysqli_stmt_bind_param($stmt, 'ssss', $data['name'], $data['email'], $hash, $role);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function verifyPassword($email, $password)
    {
        $user = $this->findByEmail($email);
        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password'])) {
            return null;
        }

        return $user;
    }

    public function getCheckoutProfile($user_id)
    {
        $stmt = mysqli_prepare($this->conn, 'SELECT name, email, phone, address FROM users WHERE id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }

        $user_id = (int)$user_id;
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $user ?: null;
    }

    public function getSavedAddresses($user_id)
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

        $user_id = (int)$user_id;
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $addresses[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $addresses;
    }

    public function getAddressForUser($address_id, $user_id)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            'SELECT full_name, phone, address_line, city, state, pincode
             FROM user_addresses
             WHERE id = ? AND user_id = ?
             LIMIT 1'
        );
        if (!$stmt) {
            return null;
        }

        $address_id = (int)$address_id;
        $user_id = (int)$user_id;
        mysqli_stmt_bind_param($stmt, 'ii', $address_id, $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $address = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $address ?: null;
    }

    public function getEmailById($user_id)
    {
        $stmt = mysqli_prepare($this->conn, 'SELECT email FROM users WHERE id = ? LIMIT 1');
        if (!$stmt) {
            return '';
        }

        $user_id = (int)$user_id;
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ? trim((string)$row['email']) : '';
    }
}
