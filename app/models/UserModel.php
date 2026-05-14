<?php
require_once "app/config/database.php";

class UserModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->ensureStatusColumn();
    }

    private function ensureStatusColumn()
    {
        $result = $this->conn->query("SHOW COLUMNS FROM users LIKE 'status'");
        if ($result && $result->num_rows === 0) {
            $this->conn->query("ALTER TABLE users ADD COLUMN status ENUM('active','locked') NOT NULL DEFAULT 'active' AFTER role");
        }
    }

    public function createUser($name, $phone, $email, $password)
    {
        $sql = "INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $phone, $email, $password);
        return $stmt->execute();
    }

    public function checkEmail($email)
    {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getUserByEmailOrPhone($account)
{
    $sql = "SELECT * FROM users WHERE email = ? OR phone = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ss", $account, $account);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

public function updatePasswordById($id, $password)
{
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("si", $password, $id);
    return $stmt->execute();
}

public function createProfile($user_id)
{
    $sql = "INSERT INTO user_profiles 
            (user_id, chieucao, cannang, tuoi, gioitinh, tilemo, muctieu, muctieu_cannang, tinhtrang_suckhoe, chedo_an)
            VALUES (?, 0, 0, 0, '', 0, '', 0, '', '')";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

public function getById($id)
{
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

public function updateUser($id, $name, $email, $avatar)
{
    $sql = "UPDATE users SET name=?, email=?, avatar=? WHERE id=?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $email, $avatar, $id);
    return $stmt->execute();
}

public function updatePassword($id, $password)
{
    $sql = "UPDATE users SET password=? WHERE id=?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("si", $password, $id);
    return $stmt->execute();
}

public function updateProfile($id, $name, $phone)
{
    $sql = "UPDATE users SET name = ?, phone = ? WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $phone, $id);

    return $stmt->execute();
}


}
