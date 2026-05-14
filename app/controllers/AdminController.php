<?php
require_once "app/models/AdminModel.php";
require_once "app/models/FeedbackModel.php";
require_once "app/config/Database.php";

class AdminController
{
    private $model;
    private $conn;

    public function __construct()
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php?controller=user&action=login");
            exit();
        }

        $db = new Database();
        $this->conn = $db->getConnection();
        $this->ensureUserStatusColumn();
        $this->model = new AdminModel();
    }

    private function ensureUserStatusColumn()
    {
        $result = $this->conn->query("SHOW COLUMNS FROM users LIKE 'status'");
        if ($result && $result->num_rows === 0) {
            $this->conn->query("ALTER TABLE users ADD COLUMN status ENUM('active','locked') NOT NULL DEFAULT 'active' AFTER role");
        }
    }

    // ================= DASHBOARD =================
    public function dashboard()
    {
        $conn = $this->conn;

        // ================= TỔNG =================
        $totalUsers = $conn->query("SELECT COUNT(*) as total FROM users")
            ->fetch_assoc()['total'] ?? 0;

        $totalMeals = $conn->query("SELECT COUNT(*) as total FROM meals")
            ->fetch_assoc()['total'] ?? 0;

        $totalFoods = $conn->query("SELECT COUNT(*) as total FROM ketqua_ai")
            ->fetch_assoc()['total'] ?? 0;

        $totalAI = $conn->query("SELECT COUNT(*) as total FROM chat_messages WHERE role='ai'")
            ->fetch_assoc()['total'] ?? 0;


        // ================= LINE CHART (users theo tháng) =================
        $userLabels = [];
        $userData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));

            $count = $conn->query("
            SELECT COUNT(*) as total
            FROM users
            WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'
        ")->fetch_assoc()['total'] ?? 0;

            $userLabels[] = "T" . date('m', strtotime($month));
            $userData[] = (int)$count;
        }


        // ================= TOP FOOD =================
        $topFoods = $conn->query("
    SELECT ten_mon, COUNT(*) as total
    FROM meal_items
    WHERE ten_mon IS NOT NULL AND ten_mon != ''
    GROUP BY ten_mon
    ORDER BY total DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

        // ================= AI CHART =================
        $aiStats = $conn->query("
        SELECT DAYNAME(created_at) as day, COUNT(*) as total
        FROM chat_messages
        WHERE role='ai'
        GROUP BY DAYNAME(created_at)
    ")->fetch_all(MYSQLI_ASSOC);

        $daysMap = [
            'Monday' => 'T2',
            'Tuesday' => 'T3',
            'Wednesday' => 'T4',
            'Thursday' => 'T5',
            'Friday' => 'T6',
            'Saturday' => 'T7',
            'Sunday' => 'CN'
        ];

        $aiData = array_fill(0, 7, 0);

        foreach ($aiStats as $row) {
            $map = [
                'Monday' => 0,
                'Tuesday' => 1,
                'Wednesday' => 2,
                'Thursday' => 3,
                'Friday' => 4,
                'Saturday' => 5,
                'Sunday' => 6
            ];
            if (isset($map[$row['day']])) {
                $aiData[$map[$row['day']]] = (int)$row['total'];
            }
        }

        // ================= VIEW =================
        ob_start();
        require "app/views/dashboard/dashboard_admin.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    // ================= USER =================
    public function user()
    {
        $conn = $this->conn;

        $keyword = trim($_GET['keyword'] ?? '');
        $role = trim($_GET['role'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $sql = "SELECT * FROM users WHERE 1=1";

        if (!empty($keyword)) {
            $keyword = $conn->real_escape_string($keyword);
            $sql .= " AND (name LIKE '%$keyword%' OR email LIKE '%$keyword%')";
        }

        if (in_array($role, ['admin', 'user'], true)) {
            $role = $conn->real_escape_string($role);
            $sql .= " AND role = '$role'";
        }

        if (in_array($status, ['active', 'locked'], true)) {
            $status = $conn->real_escape_string($status);
            $sql .= " AND status = '$status'";
        }

        $sql .= " ORDER BY id DESC";

        $result = $conn->query($sql);

        if (!$result) {
            die("SQL lỗi: " . $conn->error);
        }

        $users = $result->fetch_all(MYSQLI_ASSOC);
        $totalUsers = count($users);

        ob_start();
        require "app/views/admin/QLNguoiDung.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    public function feedback()
    {
        $feedbackModel = new FeedbackModel();
        $status = trim($_GET['status'] ?? '');
        $feedbacks = $feedbackModel->getAll($status);
        $newCount = $feedbackModel->countNew();

        ob_start();
        require "app/views/admin/feedback.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    public function updateFeedbackStatus()
    {
        $feedbackModel = new FeedbackModel();
        $id = intval($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? 'new');
        $feedbackModel->updateStatus($id, $status);

        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "index.php?controller=admin&action=feedback"));
        exit;
    }

    public function addUser()
    {
        ob_start();
        require "app/views/admin/add_user.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    public function storeUser()
    {
        $conn = $this->conn;

        $name  = $_POST['name'];
        $email = $_POST['email'];
        $role  = $_POST['role'];

        $password = password_hash("123456", PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        $stmt->execute();

        header("Location: index.php?controller=admin&action=user");
    }

    public function deleteUser()
    {
        $conn = $this->conn;
        $id = intval($_POST['id']);

        $conn->query("DELETE FROM meal_items WHERE meal_id IN (SELECT id FROM meals WHERE user_id = $id)");
        $conn->query("DELETE FROM meals WHERE user_id = $id");
        $conn->query("DELETE FROM user_profiles WHERE user_id = $id");
        $conn->query("DELETE FROM weight_logs WHERE user_id = $id");
        $conn->query("DELETE FROM chat_messages WHERE user_id = $id");
        $conn->query("DELETE FROM ketqua_ai WHERE user_id = $id");
        $conn->query("DELETE FROM users WHERE id = $id");

        header("Location: index.php?controller=admin&action=user");
    }

    public function updateUserRole()
    {
        $id = intval($_POST['id'] ?? 0);
        $role = $_POST['role'] ?? 'user';
        if ($id > 0 && $id !== (int)($_SESSION['user']['id'] ?? 0) && in_array($role, ['admin', 'user'], true)) {
            $stmt = $this->conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $role, $id);
            $stmt->execute();
        }

        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "index.php?controller=admin&action=user"));
        exit;
    }

    public function toggleUserStatus()
    {
        $id = intval($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        if ($id > 0 && $id !== (int)($_SESSION['user']['id'] ?? 0) && in_array($status, ['active', 'locked'], true)) {
            $stmt = $this->conn->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
            $stmt->execute();
        }

        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "index.php?controller=admin&action=user"));
        exit;
    }

    public function editUser()
    {
        $id = intval($_GET['id'] ?? 0);
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            header("Location: index.php?controller=admin&action=user");
            exit;
        }

        ob_start();
        require "app/views/admin/edit_user.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    public function updateUser()
    {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $status = $_POST['status'] ?? 'active';

        if ($id > 0 && $name !== '' && $email !== '' && in_array($role, ['admin', 'user'], true) && in_array($status, ['active', 'locked'], true)) {
            if ($id === (int)($_SESSION['user']['id'] ?? 0)) {
                $status = 'active';
            }
            $stmt = $this->conn->prepare("UPDATE users SET name = ?, email = ?, role = ?, status = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $role, $status, $id);
            $stmt->execute();
        }

        header("Location: index.php?controller=admin&action=user");
        exit;
    }

    // ================= FOOD =================
    public function food()
    {
        $conn = $this->conn;

        $keyword = trim($_GET['keyword'] ?? '');
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $where = "";

        if (!empty($keyword)) {
            $keyword = $conn->real_escape_string($keyword);
            $where = " WHERE ten_mon LIKE '%$keyword%'";
        }

        $totalFoods = (int)($conn->query("SELECT COUNT(*) AS total FROM ketqua_ai $where")->fetch_assoc()['total'] ?? 0);
        $totalPages = max(1, (int)ceil($totalFoods / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $perPage;
        }

        $sql = "SELECT * FROM ketqua_ai $where ORDER BY id DESC LIMIT $perPage OFFSET $offset";
        $result = $conn->query($sql);
        $foods = $result->fetch_all(MYSQLI_ASSOC);

        ob_start();
        require "app/views/admin/ql_thucpham.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    // ================= ADD FOOD =================
    public function addFood()
    {
        ob_start();
        require "app/views/admin/add_food.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    public function editFood()
    {
        $conn = $this->conn;
        $id = intval($_GET['id']);

        $result = $conn->query("SELECT * FROM ketqua_ai WHERE id = $id");
        $food = $result->fetch_assoc();

        if (!$food) {
            die("❌ Không tìm thấy thực phẩm");
        }

        ob_start();
        require "app/views/admin/edit_food.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    public function updateFood()
    {
        $conn = $this->conn;

        $id      = $_POST['id'];
        $ten_mon = $_POST['ten_mon'];
        $calo    = $_POST['calo'];
        $protein = $_POST['protein'];
        $carb    = $_POST['carb'];
        $fat     = $_POST['fat'];

        // lấy ảnh cũ
        $currentImage = $_POST['current_hinh_anh'] ?? null;
        $imagePath = $currentImage;

        // nếu có upload ảnh mới
        if (!empty($_FILES['hinh_anh']['name'])) {

            $targetDir = "public/uploads/food/";

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileName = time() . "_" . basename($_FILES["hinh_anh"]["name"]);
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $targetFile)) {
                $imagePath = $targetFile;
            }
        }

        // update DB
        $stmt = $conn->prepare("
        UPDATE ketqua_ai 
        SET ten_mon=?, calo=?, protein=?, carb=?, fat=?, hinh_anh=? 
        WHERE id=?
    ");

        $stmt->bind_param("sddddsi", $ten_mon, $calo, $protein, $carb, $fat, $imagePath, $id);
        $stmt->execute();

        header("Location: index.php?controller=admin&action=food");
    }

    // ================= STORE FOOD =================
    public function storeFood()
    {
        $conn = $this->conn;

        $ten_mon = $_POST['ten_mon'];
        $calo    = $_POST['calo'];
        $protein = $_POST['protein'];
        $carb    = $_POST['carb'];
        $fat     = $_POST['fat'];

        $imagePath = null;

        // ================= UPLOAD IMAGE =================
        if (!empty($_FILES['image']['name'])) {

            $targetDir = "public/uploads/food/";

            // tạo folder nếu chưa có
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileName = time() . "_" . basename($_FILES["image"]["name"]);
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $imagePath = $targetFile;
            }
        }

        // ================= INSERT =================
        $stmt = $conn->prepare("
        INSERT INTO ketqua_ai (ten_mon, calo, protein, carb, fat, hinh_anh)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

        $stmt->bind_param("sdddds", $ten_mon, $calo, $protein, $carb, $fat, $imagePath);
        $stmt->execute();

        header("Location: index.php?controller=admin&action=food");
    }

    public function deleteFood()
    {
        $conn = $this->conn;

        $id = intval($_GET['id']);
        $conn->query("DELETE FROM ketqua_ai WHERE id = $id");

        header("Location: index.php?controller=admin&action=food");
    }

    // ================= THỐNG KÊ =================
    public function thongke()
    {
        $conn = $this->conn;

        // ===== FILTER =====
        $type = $_GET['type'] ?? 'day';
        $date = $_GET['date'] ?? date('Y-m-d');

        if ($type == 'month') {
            $filterUsers = "DATE_FORMAT(created_at, '%Y-%m') = '" . date('Y-m', strtotime($date)) . "'";
            $filterProfiles = $filterUsers;
            $filterAI = $filterUsers;
            $filterMeals = "DATE_FORMAT(thoigian, '%Y-%m') = '" . date('Y-m', strtotime($date)) . "'";
        } else {
            $filterUsers = "DATE(created_at) = '" . date('Y-m-d', strtotime($date)) . "'";
            $filterProfiles = $filterUsers;
            $filterAI = $filterUsers;
            $filterMeals = "DATE(thoigian) = '" . date('Y-m-d', strtotime($date)) . "'";
        }

        // ===== TOTAL =====
        $newUsers = $conn->query("SELECT COUNT(*) as total FROM users WHERE $filterUsers")
            ->fetch_assoc()['total'] ?? 0;

        $newMeals = $conn->query("SELECT COUNT(*) as total FROM meals WHERE $filterMeals")
            ->fetch_assoc()['total'] ?? 0;

        $newFoods = $conn->query("SELECT COUNT(*) as total FROM ketqua_ai WHERE $filterUsers")
            ->fetch_assoc()['total'] ?? 0;

        $totalAI = $conn->query("SELECT COUNT(*) as total FROM chat_messages WHERE role='ai' AND $filterAI")
            ->fetch_assoc()['total'] ?? 0;

        $activeUsers = $conn->query("
            SELECT COUNT(DISTINCT user_id) as total
            FROM meals
            WHERE thoigian >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ")->fetch_assoc()['total'] ?? 0;

        $lockedUsers = $conn->query("SELECT COUNT(*) as total FROM users WHERE status = 'locked'")
            ->fetch_assoc()['total'] ?? 0;

        // ===== BMI =====
        $avgBMI = $conn->query("
        SELECT AVG(cannang / POW(chieucao/100,2)) as bmi
        FROM user_profiles
        WHERE cannang > 0 AND chieucao > 0
    ")->fetch_assoc()['bmi'] ?? 0;

        // ===== PIE (MỤC TIÊU) =====
        $goalData = $conn->query("
        SELECT muctieu, COUNT(*) as total
        FROM user_profiles
        GROUP BY muctieu
    ")->fetch_all(MYSQLI_ASSOC);

        $goalLabels = [];
        $goalValues = [];

        foreach ($goalData as $g) {
            $goalLabels[] = $g['muctieu'] ?: 'Không rõ';
            $goalValues[] = (int)$g['total'];
        }

        // ===== LINE (USER 6 THÁNG) =====
        $userLabels = [];
        $userData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));

            $count = $conn->query("
            SELECT COUNT(*) as total
            FROM users
            WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'
        ")->fetch_assoc()['total'] ?? 0;

            $userLabels[] = "T" . date('m', strtotime($month));
            $userData[] = (int)$count;
        }

        // ===== BAR (DINH DƯỠNG TB) =====
        $macro = $conn->query("
        SELECT 
            AVG(protein) as protein,
            AVG(carb) as carb,
            AVG(fat) as fat
        FROM ketqua_ai
        WHERE protein > 0 OR carb > 0 OR fat > 0
    ")->fetch_assoc();

        $macroData = [
            round($macro['protein'] ?? 0, 1),
            round($macro['carb'] ?? 0, 1),
            round($macro['fat'] ?? 0, 1),
        ];

        $mealLabels = [];
        $mealData = [];
        $aiLabels = [];
        $aiData = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-$i days"));
            $mealLabels[] = date('d/m', strtotime($day));
            $aiLabels[] = date('d/m', strtotime($day));

            $mealData[] = (int)($conn->query("
                SELECT COUNT(*) as total
                FROM meals
                WHERE DATE(thoigian) = '$day'
            ")->fetch_assoc()['total'] ?? 0);

            $aiData[] = (int)($conn->query("
                SELECT COUNT(*) as total
                FROM chat_messages
                WHERE role='ai' AND DATE(created_at) = '$day'
            ")->fetch_assoc()['total'] ?? 0);
        }

        $topFoodRows = $conn->query("
            SELECT COALESCE(NULLIF(ten_mon, ''), 'Không rõ') AS ten_mon, COUNT(*) AS total
            FROM ketqua_ai
            GROUP BY COALESCE(NULLIF(ten_mon, ''), 'Không rõ')
            ORDER BY total DESC
            LIMIT 6
        ")->fetch_all(MYSQLI_ASSOC);

        $topFoodLabels = array_map(fn($item) => $item['ten_mon'], $topFoodRows);
        $topFoodData = array_map(fn($item) => (int)$item['total'], $topFoodRows);

        // ===== VIEW =====
        ob_start();
        require "app/views/admin/thongke.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    public function ai_management()
    {
        $conn = $this->conn;

        // ===== STATS =====
        $stats = [];

        // tổng request AI
        $stats['total'] = $conn->query("
        SELECT COUNT(*) as total 
        FROM chat_messages 
        WHERE role='ai'
    ")->fetch_assoc()['total'] ?? 0;

        // số user dùng AI
        $stats['users'] = $conn->query("
        SELECT COUNT(DISTINCT user_id) as total 
        FROM chat_messages
    ")->fetch_assoc()['total'] ?? 0;

        // lỗi AI
        $stats['errors'] = $conn->query("
        SELECT COUNT(*) as total 
        FROM chat_messages 
        WHERE message IS NULL OR message = ''
    ")->fetch_assoc()['total'] ?? 0;

        // ===== AI SERVICES =====
        $result = $conn->query("SELECT * FROM ai_services");

        if (!$result) {
            die("❌ Chưa có bảng ai_services");
        }

        // ====== AI SERVICES ======
        $ai_services = $conn->query("
    SELECT 
        s.*,
        (
            SELECT COUNT(*) 
            FROM chat_messages c
            WHERE c.role = 'ai'
        ) as real_usage
    FROM ai_services s
")->fetch_all(MYSQLI_ASSOC);
        // ===== CHART 7 NGÀY =====
        $chartLabels = [];
        $chartData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));

            $count = $conn->query("
            SELECT COUNT(*) as total 
            FROM chat_messages 
            WHERE role='ai' AND DATE(created_at) = '$date'
        ")->fetch_assoc()['total'] ?? 0;

            $chartLabels[] = date('d/m', strtotime($date));
            $chartData[] = (int)$count;
        }

        // ===== VIEW =====
        ob_start();
        require "app/views/admin/ai_management.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    public function updateAIStatus()
    {
        $conn = $this->conn;

        $id = intval($_GET['id']);
        $status = $_GET['status'];

        $stmt = $conn->prepare("
        UPDATE ai_services 
        SET trang_thai = ? 
        WHERE id = ?
    ");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();

        header("Location: index.php?controller=admin&action=ai_management");
    }

    // ================= SETTING =================
    public function setting()
    {
        $conn = $this->conn;

        $id = $_SESSION['user']['id'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        ob_start();
        require "app/views/setting/setting.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }
}
