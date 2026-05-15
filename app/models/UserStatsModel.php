<?php
require_once "app/config/database.php";

class UserStatsModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    public function getOverview(int $userId, ?string $month = null): array
    {
        $month = $this->normalizeMonth($month);
        $today = $this->fetchOne("
            SELECT
                COALESCE(SUM(mi.calo), 0) AS calo,
                COALESCE(SUM(mi.protein), 0) AS protein,
                COALESCE(SUM(mi.carb), 0) AS carb,
                COALESCE(SUM(mi.fat), 0) AS fat,
                COUNT(DISTINCT m.id) AS meals
            FROM meals m
            LEFT JOIN meal_items mi ON mi.meal_id = m.id
            WHERE m.user_id = ? AND DATE(m.thoigian) = CURDATE()
        ", [$userId], "i");

        $month = $this->fetchOne("
            SELECT
                COALESCE(SUM(mi.calo), 0) AS calo,
                COALESCE(SUM(mi.protein), 0) AS protein,
                COALESCE(SUM(mi.carb), 0) AS carb,
                COALESCE(SUM(mi.fat), 0) AS fat,
                COUNT(DISTINCT m.id) AS meals,
                COUNT(mi.id) AS items
            FROM meals m
            LEFT JOIN meal_items mi ON mi.meal_id = m.id
            WHERE m.user_id = ?
              AND DATE_FORMAT(m.thoigian, '%Y-%m') = ?
        ", [$userId, $month], "is");

        $profile = $this->fetchOne("SELECT kcal_target FROM user_profiles WHERE user_id = ? LIMIT 1", [$userId], "i");

        return [
            'today' => $today,
            'month' => $month,
            'target' => intval($profile['kcal_target'] ?? 2000) ?: 2000,
            'ai_messages' => $this->countAiMessages($userId),
        ];
    }

    public function getMonthDailyData(int $userId, ?string $month = null): array
    {
        $month = $this->normalizeMonth($month);
        $start = $month . '-01';
        $daysInMonth = intval(date('t', strtotime($start)));
        $labels = [];
        $calories = [];
        $protein = [];
        $carb = [];
        $fat = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%s-%02d', $month, $day);
            $labels[$date] = date('d/m', strtotime($date));
            $calories[$date] = 0;
            $protein[$date] = 0;
            $carb[$date] = 0;
            $fat[$date] = 0;
        }

        $stmt = $this->conn->prepare("
            SELECT
                DATE(m.thoigian) AS day,
                COALESCE(SUM(mi.calo), 0) AS calo,
                COALESCE(SUM(mi.protein), 0) AS protein,
                COALESCE(SUM(mi.carb), 0) AS carb,
                COALESCE(SUM(mi.fat), 0) AS fat
            FROM meals m
            LEFT JOIN meal_items mi ON mi.meal_id = m.id
            WHERE m.user_id = ? AND DATE_FORMAT(m.thoigian, '%Y-%m') = ?
            GROUP BY DATE(m.thoigian)
            ORDER BY day ASC
        ");
        $stmt->bind_param("is", $userId, $month);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $day = $row['day'];
            if (isset($calories[$day])) {
                $calories[$day] = round(floatval($row['calo']));
                $protein[$day] = round(floatval($row['protein']), 1);
                $carb[$day] = round(floatval($row['carb']), 1);
                $fat[$day] = round(floatval($row['fat']), 1);
            }
        }

        return [
            'labels' => array_values($labels),
            'calories' => array_values($calories),
            'protein' => array_values($protein),
            'carb' => array_values($carb),
            'fat' => array_values($fat),
        ];
    }

    public function getLast7Days(int $userId): array
    {
        $labels = [];
        $calories = [];
        $protein = [];
        $carb = [];
        $fat = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $labels[$date] = date('d/m', strtotime($date));
            $calories[$date] = 0;
            $protein[$date] = 0;
            $carb[$date] = 0;
            $fat[$date] = 0;
        }

        $stmt = $this->conn->prepare("
            SELECT
                DATE(m.thoigian) AS day,
                COALESCE(SUM(mi.calo), 0) AS calo,
                COALESCE(SUM(mi.protein), 0) AS protein,
                COALESCE(SUM(mi.carb), 0) AS carb,
                COALESCE(SUM(mi.fat), 0) AS fat
            FROM meals m
            LEFT JOIN meal_items mi ON mi.meal_id = m.id
            WHERE m.user_id = ? AND DATE(m.thoigian) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(m.thoigian)
            ORDER BY day ASC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $day = $row['day'];
            if (isset($calories[$day])) {
                $calories[$day] = round(floatval($row['calo']));
                $protein[$day] = round(floatval($row['protein']), 1);
                $carb[$day] = round(floatval($row['carb']), 1);
                $fat[$day] = round(floatval($row['fat']), 1);
            }
        }

        return [
            'labels' => array_values($labels),
            'calories' => array_values($calories),
            'protein' => array_values($protein),
            'carb' => array_values($carb),
            'fat' => array_values($fat),
        ];
    }

    public function getMealBreakdown(int $userId, ?string $month = null): array
    {
        $stmt = $this->conn->prepare("
            SELECT m.bua, COALESCE(SUM(mi.calo), 0) AS calo, COUNT(mi.id) AS items
            FROM meals m
            LEFT JOIN meal_items mi ON mi.meal_id = m.id
            WHERE m.user_id = ? AND DATE(m.thoigian) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY m.bua
            ORDER BY calo DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $rowsByMeal = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rowsByMeal[mb_strtolower(trim((string)$row['bua']), 'UTF-8')] = $row;
        }

        $defaultMeals = ['Sáng', 'Trưa', 'Tối', 'Bữa phụ'];
        $rows = [];
        foreach ($defaultMeals as $mealName) {
            $key = mb_strtolower($mealName, 'UTF-8');
            $rows[] = [
                'bua' => $mealName,
                'calo' => floatval($rowsByMeal[$key]['calo'] ?? 0),
                'items' => intval($rowsByMeal[$key]['items'] ?? 0),
            ];
        }

        return $rows;
    }

    public function getTopFoods(int $userId, ?string $month = null): array
    {
        $stmt = $this->conn->prepare("
            SELECT mi.ten_mon, COUNT(*) AS times, COALESCE(SUM(mi.calo), 0) AS calo
            FROM meals m
            JOIN meal_items mi ON mi.meal_id = m.id
            WHERE m.user_id = ?
            GROUP BY mi.ten_mon
            ORDER BY times DESC, calo DESC
            LIMIT 6
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $rows = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function getAvailableMonths(int $userId): array
    {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT DATE_FORMAT(thoigian, '%Y-%m') AS month_key
            FROM meals
            WHERE user_id = ?
            ORDER BY month_key DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $months = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['month_key'])) {
                $months[] = $row['month_key'];
            }
        }

        $current = date('Y-m');
        if (!in_array($current, $months, true)) {
            array_unshift($months, $current);
        }
        return array_values(array_unique($months));
    }

    private function countAiMessages(int $userId): int
    {
        $row = $this->fetchOne("SELECT COUNT(*) AS total FROM chat_messages WHERE user_id = ? AND role = 'ai'", [$userId], "i");
        return intval($row['total'] ?? 0);
    }

    private function fetchOne(string $sql, array $params = [], string $types = ''): array
    {
        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: [];
    }

    private function normalizeMonth(?string $month): string
    {
        return preg_match('/^\d{4}-\d{2}$/', (string)$month) ? (string)$month : date('Y-m');
    }
}
