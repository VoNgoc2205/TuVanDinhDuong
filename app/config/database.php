<?php

class Database
{
    private $conn;

    public function __construct()
    {

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->conn = new mysqli("localhost", "root", "", "tuvandinhduong");

            $this->conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            die(" Lỗi kết nối DB: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
