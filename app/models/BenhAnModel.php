<?php
require_once "app/config/database.php";

class BenhAnModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->ensureImageColumn();
    }

    private function ensureImageColumn()
    {
        $check = $this->conn->query("SHOW COLUMNS FROM benh_an LIKE 'hinh_anh'");
        if ($check && $check->num_rows > 0) {
            return;
        }

        $sql = "ALTER TABLE benh_an ADD COLUMN hinh_anh VARCHAR(255) DEFAULT NULL AFTER gia_tri";
        if (!$this->conn->query($sql)) {
            error_log("BenhAnModel::ensureImageColumn error: " . $this->conn->error);
        }
    }

    // =========================
    // LẤY TẤT CẢ HỒ SƠ BỆNH ÁN
    // =========================
    public function getAll($user_id)
    {
        $sql = "SELECT * FROM benh_an 
                WHERE user_id = ? 
                ORDER BY loai ASC, ngay_cap_nhat DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // =========================
    // LẤY HỒ SƠ BỆNH ÁN THEO LOẠI
    // =========================
    public function getByType($user_id, $loai)
    {
        $sql = "SELECT * FROM benh_an 
                WHERE user_id = ? AND loai = ? 
                ORDER BY ngay_cap_nhat DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $loai);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // =========================
    // THÊM HỒ SƠ BỆNH ÁN
    // =========================
    public function add($user_id, $loai, $ten_muc, $gia_tri, $hinh_anh = null)
    {
        $sql = "INSERT INTO benh_an (user_id, loai, ten_muc, gia_tri, hinh_anh) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("BenhAnModel::add prepare error: " . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param("issss", $user_id, $loai, $ten_muc, $gia_tri, $hinh_anh);
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("BenhAnModel::add execute error: " . $stmt->error);
            return false;
        }
        
        return $stmt->insert_id;
    }

    // =========================
    // CẬP NHẬT HỒ SƠ BỆNH ÁN
    // =========================
    public function update($id, $user_id, $ten_muc, $gia_tri, $hinh_anh = null)
    {
        if ($hinh_anh !== null) {
            $sql = "UPDATE benh_an 
                    SET ten_muc = ?, gia_tri = ?, hinh_anh = ? 
                    WHERE id = ? AND user_id = ?";
        } else {
            $sql = "UPDATE benh_an 
                    SET ten_muc = ?, gia_tri = ? 
                    WHERE id = ? AND user_id = ?";
        }
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("BenhAnModel::update prepare error: " . $this->conn->error);
            return false;
        }
        
        if ($hinh_anh !== null) {
            $stmt->bind_param("sssii", $ten_muc, $gia_tri, $hinh_anh, $id, $user_id);
        } else {
            $stmt->bind_param("ssii", $ten_muc, $gia_tri, $id, $user_id);
        }
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("BenhAnModel::update execute error: " . $stmt->error);
            return false;
        }
        
        return true;
    }

    // =========================
    // XÓA HỒ SƠ BỆNH ÁN
    // =========================
    public function delete($id, $user_id)
    {
        $sql = "DELETE FROM benh_an WHERE id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $user_id);
        
        return $stmt->execute();
    }

    // =========================
    // XÓA HẾT THEO LOẠI
    // =========================
    public function deleteByType($user_id, $loai)
    {
        $sql = "DELETE FROM benh_an WHERE user_id = ? AND loai = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $loai);
        
        return $stmt->execute();
    }

    // =========================
    // NHẬP BATCH DỮ LIỆU (từ AI)
    // =========================
    public function importBatch($user_id, $data_array)
    {
        $inserted = 0;
        
        foreach ($data_array as $item) {
            $loai = $item['loai'] ?? 'chi_so';
            $ten_muc = $item['ten_muc'] ?? '';
            $gia_tri = $item['gia_tri'] ?? '';
            $hinh_anh = $item['hinh_anh'] ?? null;
            
            if (!empty($ten_muc)) {
                $id = $this->add($user_id, $loai, $ten_muc, $gia_tri, $hinh_anh);
                if ($id) {
                    $inserted++;
                }
            }
        }
        
        return $inserted;
    }

    // =========================
    // THỐNG KÊ
    // =========================
    public function getStats($user_id)
    {
        $sql = "SELECT 
                    loai,
                    COUNT(*) as count,
                    MAX(ngay_cap_nhat) as last_update
                FROM benh_an 
                WHERE user_id = ? 
                GROUP BY loai";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
