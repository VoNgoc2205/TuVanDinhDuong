<?php
require_once "app/config/database.php";

class MealModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();

        if (!$this->conn) {
            die("Lỗi kết nối DB");
        }

        $this->ensureExtendedColumns();
    }

    private function ensureExtendedColumns()
    {
        $columns = [];
        $result = $this->conn->query("SHOW COLUMNS FROM meal_items");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $columns[$row['Field']] = true;
            }
        }

        $required = [
            'gram' => "ALTER TABLE meal_items ADD COLUMN gram DOUBLE DEFAULT 0",
            'fiber' => "ALTER TABLE meal_items ADD COLUMN fiber DOUBLE DEFAULT 0",
            'hinh_anh' => "ALTER TABLE meal_items ADD COLUMN hinh_anh VARCHAR(255) NULL",
            'thanh_phan_json' => "ALTER TABLE meal_items ADD COLUMN thanh_phan_json LONGTEXT NULL",
            'nutrition_json' => "ALTER TABLE meal_items ADD COLUMN nutrition_json LONGTEXT NULL"
        ];

        foreach ($required as $column => $sql) {
            if (empty($columns[$column])) {
                @$this->conn->query($sql);
            }
        }
    }

    // =========================
    // 🔥 SAVE MEAL + ITEMS
    // =========================
    public function saveMealWithItems($data)
    {
        if (empty($data['items']) || !is_array($data['items'])) {
            return false;
        }

        $this->conn->begin_transaction();

        try {

            // 👉 1. tạo meal
            $sqlMeal = "INSERT INTO meals (user_id, bua, thoigian)
                        VALUES (?, ?, NOW())";

            $stmtMeal = $this->conn->prepare($sqlMeal);
            if (!$stmtMeal) throw new Exception("Prepare meal fail");

            $stmtMeal->bind_param("is", $data['user_id'], $data['bua']);
            $stmtMeal->execute();

            $meal_id = $this->conn->insert_id;

            if (!$meal_id) throw new Exception("Insert meal fail");

            // 👉 2. insert items
            $sqlItem = "INSERT INTO meal_items
                        (meal_id, ten_mon, calo, protein, carb, fat, gram, fiber, hinh_anh, thanh_phan_json, nutrition_json)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmtItem = $this->conn->prepare($sqlItem);
            if (!$stmtItem) throw new Exception("Prepare item fail");

            foreach ($data['items'] as $item) {

                // 🔥 CLEAN DATA (TRÁNH NULL)
                $ten_mon = trim($item['ten_mon'] ?? '');
                if ($ten_mon === '') continue;

                $calo = (float)($item['calo'] ?? 0);
                $protein = (float)($item['protein'] ?? 0);
                $carb = (float)($item['carb'] ?? 0);
                $fat = (float)($item['fat'] ?? 0);
                $gram = (float)($item['gram'] ?? 0);
                $fiber = (float)($item['fiber'] ?? 0);
                $image = $item['hinh_anh'] ?? ($item['image'] ?? null);
                $ingredientsJson = json_encode($item['ingredients'] ?? [], JSON_UNESCAPED_UNICODE);
                $nutritionJson = json_encode($item['nutrition'] ?? $item, JSON_UNESCAPED_UNICODE);

                $stmtItem->bind_param(
                    "isddddddsss",
                    $meal_id,
                    $ten_mon,
                    $calo,
                    $protein,
                    $carb,
                    $fat,
                    $gram,
                    $fiber,
                    $image,
                    $ingredientsJson,
                    $nutritionJson
                );

                $stmtItem->execute();

                if ($stmtItem->affected_rows <= 0) {
                    throw new Exception("Insert item fail");
                }
            }

            $stmtMeal->close();
            $stmtItem->close();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {

            $this->conn->rollback();

            // 👉 debug nếu cần
            error_log("MealModel ERROR: " . $e->getMessage());

            return false;
        }
    }

    // =========================
    // 📊 SUMMARY
    // =========================
    public function getDailySummary($user_id)
    {
        $sql = "
        SELECT 
            DATE(m.thoigian) as ngay,
            m.bua,
            SUM(mi.calo) as total_calo,
            SUM(mi.protein) as total_protein,
            SUM(mi.carb) as total_carb,
            SUM(mi.fat) as total_fat
        FROM meals m
        JOIN meal_items mi ON m.id = mi.meal_id
        WHERE m.user_id = ?
        GROUP BY ngay, m.bua
        ORDER BY ngay DESC
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        return $stmt->get_result();
    }

    // =========================
    // 📅 DETAIL
    // =========================
    public function getMealDetail($user_id, $date, $bua)
    {
        $sql = "
        SELECT mi.*
        FROM meals m
        JOIN meal_items mi ON m.id = mi.meal_id
        WHERE m.user_id = ?
        AND DATE(m.thoigian) = ?
        AND LOWER(m.bua) = LOWER(?)
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("iss", $user_id, $date, $bua);
        $stmt->execute();

        return $stmt->get_result();
    }

    // =========================
    // ❌ DELETE ITEM
    // =========================
    public function deleteItem($id, $user_id)
    {
        $sql = "
        DELETE mi FROM meal_items mi
        JOIN meals m ON mi.meal_id = m.id
        WHERE mi.id = ? AND m.user_id = ?
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("ii", $id, $user_id);
        return $stmt->execute();
    }

    // =========================
    // (OPTIONAL - KHÔNG CẦN DÙNG NẾU DÙNG saveMealWithItems)
    // =========================
    public function createMeal($user_id, $bua)
    {
        $sql = "INSERT INTO meals (user_id, bua, thoigian)
                VALUES (?, ?, NOW())";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $bua);
        $stmt->execute();

        return $stmt->insert_id;
    }

    public function addMealItem($meal_id, $ten, $calo, $protein, $carb, $fat, $extra = [])
    {
        $sql = "INSERT INTO meal_items
                (meal_id, ten_mon, calo, protein, carb, fat, gram, fiber, hinh_anh, thanh_phan_json, nutrition_json)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $gram = (float)($extra['gram'] ?? 0);
        $fiber = (float)($extra['fiber'] ?? 0);
        $image = $extra['hinh_anh'] ?? ($extra['image'] ?? null);
        $ingredientsJson = json_encode($extra['ingredients'] ?? [], JSON_UNESCAPED_UNICODE);
        $nutritionJson = json_encode($extra['nutrition'] ?? [], JSON_UNESCAPED_UNICODE);

        $stmt->bind_param(
            "isddddddsss",
            $meal_id,
            $ten,
            $calo,
            $protein,
            $carb,
            $fat,
            $gram,
            $fiber,
            $image,
            $ingredientsJson,
            $nutritionJson
        );

        return $stmt->execute();
    }
}
