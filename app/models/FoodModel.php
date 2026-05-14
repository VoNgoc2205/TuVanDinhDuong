<?php
require_once "app/config/database.php";

class FoodModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // 🔥 tìm trong DB trước
    public function findByName($name)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM foods WHERE name = ?"
        );
        $stmt->bind_param("s", $name);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    // 🔥 lưu vào DB
    public function save($food)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO foods(name, calo, protein, carb, fat)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sdddd",
            $food['name'],
            $food['calo'],
            $food['protein'],
            $food['carb'],
            $food['fat']
        );

        $stmt->execute();
    }
}