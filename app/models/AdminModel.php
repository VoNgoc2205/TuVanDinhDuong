<?php

class AdminModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli("localhost", "root", "", "tuvandinhduong");

        if ($this->conn->connect_error) {
            die("Lỗi DB: " . $this->conn->connect_error);
        }
    }

    // 🔥 dữ liệu toàn bộ user (admin dùng)
    public function getAllUsersData()
    {
        $sql = "SELECT * FROM hosodinhduong";
        return $this->conn->query($sql);
    }

    public function __destruct()
    {
        $this->conn->close();
    }
}
