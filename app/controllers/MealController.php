<?php
require_once "app/models/MealModel.php";
require_once "app/helpers/SessionHelper.php";

class MealController
{
    private $model;

    public function __construct()
    {
        $this->model = new MealModel();
        SessionHelper::start();
    }

    // =========================
    // 🔥 SAVE (CHUẨN 2 BẢNG)
    // =========================
    public function save()
{
    $data = json_decode(file_get_contents("php://input"), true);
    $user = SessionHelper::user();

    if (!$data || !$user) {
        echo "FAIL";
        exit;
    }

    // 👉 tạo meal trước
    $meal_id = $this->model->createMeal(
        $user['id'],
        $data['bua'] ?? 'Trưa'
    );

    if (!$meal_id) {
        echo "FAIL_MEAL";
        exit;
    }

    // 👉 đảm bảo có items
    if (!isset($data['items'])) {
        $data['items'] = [[
            "ten_mon" => $data['ten_mon'],
            "calo" => $data['calo'],
            "protein" => $data['protein'],
            "carb" => $data['carb'],
            "fat" => $data['fat'],
            "fiber" => $data['fiber'] ?? 0,
            "gram" => $data['gram'] ?? 0,
            "hinh_anh" => $data['hinh_anh'] ?? ($data['image'] ?? null),
            "ingredients" => $data['ingredients'] ?? [],
            "nutrition" => $data['nutrition'] ?? []
        ]];
    }

    foreach ($data['items'] as $item) {

        if (empty($item['ten_mon'])) continue;

        $this->model->addMealItem(
            $meal_id,
            $item['ten_mon'],
            $item['calo'] ?? 0,
            $item['protein'] ?? 0,
            $item['carb'] ?? 0,
            $item['fat'] ?? 0,
            [
                'fiber' => $item['fiber'] ?? 0,
                'gram' => $item['gram'] ?? 0,
                'hinh_anh' => $item['hinh_anh'] ?? ($item['image'] ?? null),
                'ingredients' => $item['ingredients'] ?? [],
                'nutrition' => $item['nutrition'] ?? []
            ]
        );
    }

    echo "OK";
}

    // =========================
    // 📊 HISTORY
    // =========================
    public function history()
    {
        $user = SessionHelper::user();

        if (!$user) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $result = $this->model->getDailySummary($user['id']);

        $meals = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $meals[] = $row;
            }
        }

        ob_start();
        require "app/views/meal/history.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    // =========================
    // 📅 DETAIL
    // =========================
    public function detail()
    {
        $user = SessionHelper::user();

        if (!$user) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $date = $_GET['date'] ?? date('Y-m-d');
        $bua = $_GET['bua'] ?? 'Sáng';

        $result = $this->model->getMealDetail($user['id'], $date, $bua);

        $foods = [];

        $totalCalo = 0;
        $totalProtein = 0;
        $totalCarb = 0;
        $totalFat = 0;

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $foods[] = $row;

                $totalCalo += (int)$row['calo'];
                $totalProtein += (int)$row['protein'];
                $totalCarb += (int)$row['carb'];
                $totalFat += (int)$row['fat'];
            }
        }

        ob_start();
        require "app/views/meal/detail.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    // =========================
    // ❌ DELETE ITEM
    // =========================
    public function delete()
    {
        $user = SessionHelper::user();

        if (!$user) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $id = $_GET['id'] ?? 0;

        if ($id) {
            $this->model->deleteItem($id, $user['id']);
        }

        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "index.php"));
        exit;
    }
}
