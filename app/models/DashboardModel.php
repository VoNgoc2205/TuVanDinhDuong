<?php
class DashboardModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = mysqli_connect("localhost", "root", "", "tuvandinhduong");

        if (!$this->conn) {
            die("Lỗi DB");
        }
    }

    // =========================
    // � LẤY PROFILE
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
    // �🔥 CALO HÔM NAY
    // =========================
    public function getCaloToday($user_id)
    {
        $sql = "
        SELECT SUM(mi.calo) as total
        FROM meals m
        JOIN meal_items mi ON m.id = mi.meal_id
        WHERE m.user_id = ?
        AND DATE(m.thoigian) = CURDATE()
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    }

    // =========================
    // 📊 SỐ BỮA
    // =========================
    public function getMealCountToday($user_id)
    {
        $sql = "
        SELECT COUNT(*) as total
        FROM meals
        WHERE user_id = ?
        AND DATE(thoigian) = CURDATE()
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    }

    // =========================
    // 💪 MACROS
    // =========================
    public function getMacrosToday($user_id)
    {
        $sql = "
        SELECT 
            SUM(mi.protein) as protein,
            SUM(mi.carb) as carbs,
            SUM(mi.fat) as fat
        FROM meals m
        JOIN meal_items mi ON m.id = mi.meal_id
        WHERE m.user_id = ?
        AND DATE(m.thoigian) = CURDATE()
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    // =========================
    // 📅 BIỂU ĐỒ TUẦN
    // =========================
    public function getWeekData($user_id)
    {
        $week = [0,0,0,0,0,0,0];

        $sql = "
        SELECT DAYOFWEEK(m.thoigian) as day, SUM(mi.calo) as total
        FROM meals m
        JOIN meal_items mi ON m.id = mi.meal_id
        WHERE m.user_id = ?
        AND YEARWEEK(m.thoigian,1)=YEARWEEK(CURDATE(),1)
        GROUP BY day
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $day = $row['day'];
            $index = ($day == 1) ? 6 : $day - 2;
            $week[$index] = (int)$row['total'];
        }

        return $week;
    }

    // =========================
    // 🕒 RECENT (FIX QUAN TRỌNG)
    // =========================
    public function getRecentMeals($user_id)
    {
        $sql = "
        SELECT 
            m.id,
            m.bua,
            m.thoigian,
            COALESCE(SUM(mi.calo),0) as total_calo,
            COALESCE(GROUP_CONCAT(mi.ten_mon SEPARATOR ', '),'Không có món') as ten_mon
        FROM meals m
        LEFT JOIN meal_items mi ON m.id = mi.meal_id
        WHERE m.user_id = ?
        GROUP BY m.id
        ORDER BY m.thoigian DESC
        LIMIT 5
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        return $stmt->get_result();
    }

    // =========================
    // 🤖 AI COUNT
    // =========================
    public function getAICount($user_id)
    {
        $sql = "
        SELECT COUNT(*) as total 
        FROM ketqua_ai 
        WHERE user_id = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    }
}