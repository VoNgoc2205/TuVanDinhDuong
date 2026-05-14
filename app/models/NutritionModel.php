<?php
require_once "app/config/database.php";

class NutritionModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // =========================
    // LẤY PROFILE
    // =========================
    public function getProfile($user_id)
    {
        $sql = "SELECT * FROM user_profiles WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    // =========================
    // TẠO PROFILE MẶC ĐỊNH
    // =========================
    public function createProfile($user_id)
    {
        $sql = "INSERT INTO user_profiles 
                (user_id, chieucao, cannang, tuoi, gioitinh, tilemo, muctieu, muctieu_cannang, tinhtrang_suckhoe, chedo_an, medical_record_image, medical_analysis)
                VALUES (?, 0, 0, 0, '', 0, '', 0, '', '', '', '')";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);

        return $stmt->execute();
    }

    // =========================
    // UPDATE PROFILE
    // =========================
    public function updateProfile($user_id, $data)
    {
        // 🔥 nếu chưa có profile → tạo mới
        $check = $this->getProfile($user_id);

        if (!$check) {
            $this->createProfile($user_id);
        }

        $sql = "UPDATE user_profiles SET 
                    chieucao = ?, 
                    tuoi = ?, 
                    gioitinh = ?, 
                    tinhtrang_suckhoe = ?, 
                    chedo_an = ?, 
                    muctieu = ?, 
                    muctieu_cannang = ?, 
                    kcal_target = ?
                WHERE user_id = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die("SQL lỗi: " . $this->conn->error);
        }

        $stmt->bind_param(
            "dissssiii",
            $data['chieucao'],
            $data['tuoi'],
            $data['gioitinh'],
            $data['tinhtrang_suckhoe'],
            $data['chedo_an'],
            $data['muctieu'],
            $data['muctieu_cannang'],
            $data['kcal_target'],
            $user_id
        );

        return $stmt->execute();
    }

    // =========================
    // LƯU CÂN NẶNG
    // =========================
    public function saveWeight($user_id, $weight)
    {
        $sql = "INSERT INTO weight_logs (user_id, can_nang, ngay) VALUES (?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("id", $user_id, $weight);
        $success = $stmt->execute();

        if ($success) {
            $this->updateCurrentWeight($user_id, $weight);
        }

        return $success;
    }

    public function updateCurrentWeight($user_id, $weight)
    {
        $check = $this->getProfile($user_id);
        if (!$check) {
            $this->createProfile($user_id);
        }

        $sql = "UPDATE user_profiles SET cannang = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("di", $weight, $user_id);
        return $stmt->execute();
    }

    // =========================
    // LẤY LỊCH SỬ CÂN NẶNG
    // =========================
    public function getWeightLogs($user_id)
    {
        $sql = "SELECT * FROM weight_logs 
                WHERE user_id = ? 
                ORDER BY ngay DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // =========================
    // LƯU ẢNH HỒ SƠ BỆNH ÁN
    // =========================
    public function saveMedicalRecord($user_id, $imagePath = null, $extractedData = [], $analysisJson = null)
    {
        // Đảm bảo profile tồn tại
        $check = $this->getProfile($user_id);
        if (!$check) {
            $this->createProfile($user_id);
        }

        $sql = "UPDATE user_profiles SET ";
        $fields = [];
        $params = [];
        $paramTypes = "";

        if (!empty($imagePath)) {
            $fields[] = "medical_record_image = ?";
            $params[] = $imagePath;
            $paramTypes .= "s";
        }

        if (!empty($analysisJson)) {
            $fields[] = "medical_analysis = ?";
            $params[] = $analysisJson;
            $paramTypes .= "s";
        }

        if (!empty($extractedData['tuoi'])) {
            $fields[] = "tuoi = ?";
            $params[] = intval($extractedData['tuoi']);
            $paramTypes .= "i";
        }
        if (!empty($extractedData['gioitinh'])) {
            $fields[] = "gioitinh = ?";
            $params[] = $extractedData['gioitinh'];
            $paramTypes .= "s";
        }
        if (!empty($extractedData['chieucao'])) {
            $fields[] = "chieucao = ?";
            $params[] = floatval($extractedData['chieucao']);
            $paramTypes .= "d";
        }
        if (!empty($extractedData['cannang'])) {
            $fields[] = "cannang = ?";
            $params[] = floatval($extractedData['cannang']);
            $paramTypes .= "d";
        }
        if (!empty($extractedData['tilemo'])) {
            $fields[] = "tilemo = ?";
            $params[] = floatval($extractedData['tilemo']);
            $paramTypes .= "d";
        }
        if (!empty($extractedData['tinhtrang_suckhoe'])) {
            $fields[] = "tinhtrang_suckhoe = ?";
            $params[] = $extractedData['tinhtrang_suckhoe'];
            $paramTypes .= "s";
        }

        if (empty($fields)) {
            return false;
        }

        $sql .= implode(", ", $fields) . " WHERE user_id = ?";
        $params[] = $user_id;
        $paramTypes .= "i";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $bindParams = [$paramTypes];
        foreach ($params as &$param) {
            $bindParams[] = &$param;
        }

        call_user_func_array([$stmt, 'bind_param'], $bindParams);

        return $stmt->execute();
    }

    public function saveMedicalAnalysis($user_id, $analysisJson)
    {
        $check = $this->getProfile($user_id);
        if (!$check) {
            $this->createProfile($user_id);
        }

        $sql = "UPDATE user_profiles SET medical_analysis = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $analysisJson, $user_id);
        return $stmt->execute();
    }
}
