<?php
require_once "app/config/database.php";
require_once "app/services/FoodService.php";
require_once "app/services/AIService.php";
require_once "app/helpers/SessionHelper.php";
require_once "app/models/NutritionModel.php";

class FoodController
{
    private $foodService;
    private $ai;
    private $nutritionModel;

    public function __construct()
    {
        SessionHelper::start();
        $this->foodService = new FoodService();
        $this->ai = new AIService();
        $this->nutritionModel = new NutritionModel();
    }

    // =========================
    // FORM
    // =========================
    public function index()
    {
        ob_start();
        require "app/views/food/food_input.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    public function input()
    {
        $this->index();
    }

    // =========================
    // NHẬP TAY
    // =========================
    public function handle()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=food&action=index");
            exit;
        }

        $result = $this->processRequest($_POST, $_FILES);
        if (!$result['status']) {
            $_SESSION['error'] = $result['message'];
            header("Location: index.php?controller=food&action=index");
            exit;
        }

        header("Location: index.php?controller=food&action=result");
        exit;
    }

    // =========================
    // NHẬN DIỆN ẢNH / POST AJAX
    // =========================
    public function infor_nutri()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=food&action=index");
            exit;
        }

        $result = $this->processRequest($_POST, $_FILES);
        if (!$result['status']) {
            $_SESSION['error'] = $result['message'];
            header("Location: index.php?controller=food&action=index");
            exit;
        }

        header("Location: index.php?controller=food&action=result");
        exit;
    }

    public function analyze()
    {
        $result = $this->processRequest($_POST, $_FILES);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit;
    }

    private function processRequest(array $input, array $files)
    {
        $user = SessionHelper::user();
        if (!$user) {
            return [
                'status' => false,
                'message' => 'Chưa đăng nhập'
            ];
        }

        $dishName = trim($input['ten_mon'] ?? '');
        $ingredientNames = $input['thanh_phan'] ?? [];
        $ingredientQuantities = $input['so_luong'] ?? [];
        $mode = trim($input['mode'] ?? 'manual');

        $uploadImage = null;
        if ($mode !== 'manual' && isset($files['image']) && $files['image']['error'] === 0) {
            $uploadDir = "public/uploads/food/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . "_" . basename($files['image']['name']);
            $uploadImage = $uploadDir . $fileName;
            $moved = move_uploaded_file($files['image']['tmp_name'], $uploadImage);
            if (!$moved) {
                $copied = copy($files['image']['tmp_name'], $uploadImage);
                if (!$copied) {
                    return [
                        'status' => false,
                        'message' => 'Không thể lưu ảnh đã tải lên. Vui lòng thử lại.'
                    ];
                }
            }
        }

        $items = [];
        $needsStandardize = true;

        if ($uploadImage) {
            $imageResult = $this->ai->detectFoodsFromImage($uploadImage);
            $imageItems = $imageResult['items'] ?? [];
            $imageDishName = trim($imageResult['dishName'] ?? '');

            if (!empty($imageItems)) {
                if ($dishName === '') {
                    $dishName = $imageDishName ?: ($imageItems[0]['name'] ?? '');
                }
                $items = $imageItems;
            } elseif ($imageDishName !== '') {
                $dishName = $imageDishName;
                $items = [[
                    'name' => $imageDishName,
                    'gram' => 100
                ]];
            }
        }

        if (empty($items)) {
            $items = $this->buildManualItems($dishName, $ingredientNames, $ingredientQuantities);
        }

        if (empty($items)) {
            $message = 'Vui lòng cung cấp tên món hoặc tải ảnh món ăn.';
            if ($uploadImage) {
                $message = 'Ảnh đã được tải lên nhưng AI chưa nhận diện được món ăn. Vui lòng thử lại với ảnh rõ hơn.';
            }
            return [
                'status' => false,
                'message' => $message
            ];
        }

        if ($needsStandardize) {
            $items = $this->ai->standardizeFoods($items, $dishName);
            if (empty($items)) {
                return [
                    'status' => false,
                    'message' => 'Không thể chuẩn hóa tên món để tìm kiếm USDA.'
                ];
            }
        }

        $processedItems = $this->fetchNutritionForItems($items, $dishName);
        if (empty($processedItems) && !empty($dishName)) {
            $fallbackItems = [
                [
                    'name' => $dishName,
                    'gram' => 100
                ]
            ];
            $fallbackItems = $this->ai->standardizeFoods($fallbackItems, $dishName);
            $processedItems = $this->fetchNutritionForItems($fallbackItems, $dishName);
        }

        $profile = $this->nutritionModel->getProfile($user['id']) ?? [];
        if (empty($processedItems) || count($processedItems) < count($items)) {
            $estimatedItems = $this->ai->estimateNutritionForFoodItems($items, $dishName, $profile);
            if (!empty($estimatedItems)) {
                $processedItems = $estimatedItems;
            }
        }

        if (empty($processedItems)) {
            return [
                'status' => false,
                'message' => 'Không tìm thấy dữ liệu dinh dưỡng phù hợp từ USDA. Vui lòng thử lại với tên món khác.'
            ];
        }

        $total = $this->aggregateTotals($processedItems);
        $aiAnalysis = $this->ai->generateVietnameseNutritionAnalysis($total, $processedItems, $profile, $dishName);
        if (!empty($aiAnalysis['items']) && is_array($aiAnalysis['items'])) {
            foreach ($processedItems as $index => $item) {
                if (!empty($aiAnalysis['items'][$index]['name_vi'])) {
                    $processedItems[$index]['name'] = $aiAnalysis['items'][$index]['name_vi'];
                }
            }
        }

        $displayName = trim($aiAnalysis['dish_name_vi'] ?? '');
        if ($displayName === '') {
            $displayName = $dishName !== '' ? $dishName : ($processedItems[0]['name'] ?? implode(', ', array_column($processedItems, 'name')));
        }

        $advice = trim($aiAnalysis['advice'] ?? '');
        if ($advice === '') {
            $advice = 'AI chưa tạo được gợi ý cá nhân hóa. Bạn có thể kiểm tra khẩu phần, ưu tiên rau xanh, đủ đạm và hạn chế món nhiều dầu mỡ.';
        }

        $_SESSION['food'] = [
            'name' => $displayName,
            'image' => $uploadImage ?? 'public/uploads/default.jpg',
            'calo' => $total['calo'],
            'protein' => $total['protein'],
            'carb' => $total['carb'],
            'fat' => $total['fat'],
            'fiber' => $total['fiber'] ?? 0,
            'gram' => array_sum(array_map(fn($item) => floatval($item['gram'] ?? 0), $processedItems)),
            'ingredients' => $processedItems,
            'nutrition' => $total,
            'advice' => $advice
        ];
        $_SESSION['foods_ai'] = $processedItems;
        $_SESSION['advice'] = $advice;
        $_SESSION['food_image'] = $uploadImage ?? 'public/uploads/default.jpg';
        $_SESSION['nutrition_profile'] = $profile;

        $this->saveNutritionRecord($user['id'], $_SESSION['food'], $_SESSION['food_image']);

        return [
            'status' => true,
            'data' => $_SESSION['food']
        ];
    }

    private function buildManualItems(string $dishName, array $names, array $quantities)
    {
        $items = [];

        foreach ($names as $index => $rawName) {
            $name = trim($rawName);
            if ($name === '') {
                continue;
            }

            $gram = max(100, floatval($quantities[$index] ?? 0));
            $items[] = [
                'name' => $name,
                'gram' => $gram
            ];
        }

        if (empty($items) && $dishName !== '') {
            $items[] = [
                'name' => $dishName,
                'gram' => 100
            ];
        }

        return $items;
    }

    private function fetchNutritionForItems(array $items, string $hint = '')
    {
        $processed = [];
        $retry = 0;
        $lookupCache = [];

        while ($retry < 2) {
            $processed = [];
            $missing = [];

            foreach ($items as $item) {
                $query = trim($item['name']);
                $gram = max(1, floatval($item['gram'] ?? 100));

                if (isset($lookupCache[$query])) {
                    $best = $lookupCache[$query];
                } else {
                    $results = $this->foodService->searchUSDAFood($query, 3);
                    $best = $this->foodService->pickBestUSDAResult($results, $query);

                    if (!$best) {
                        $translatedQuery = $this->foodService->translateToEnglish($query);
                        if ($translatedQuery !== $query) {
                            $results = $this->foodService->searchUSDAFood($translatedQuery, 3);
                            $best = $this->foodService->pickBestUSDAResult($results, $translatedQuery);
                        }
                    }

                    $lookupCache[$query] = $best;
                }

                if (!$best) {
                    $missing[] = $item;
                    continue;
                }

                $scaled = $this->foodService->scaleNutrition($best, $gram);
                $processed[] = array_merge($item, [
                    'description' => $scaled['description'],
                    'brandOwner' => $scaled['brandOwner'],
                    'calo' => $scaled['calo'],
                    'protein' => $scaled['protein'],
                    'carb' => $scaled['carb'],
                    'fat' => $scaled['fat'],
                    'fiber' => $scaled['fiber'] ?? 0,
                    'baseGram' => $scaled['baseGram'],
                    'fdcId' => $scaled['fdcId']
                ]);
            }

            if (empty($missing)) {
                break;
            }

            $retry++;
            $items = $this->ai->standardizeFoods($missing, $hint ?: ($items[0]['name'] ?? ''));
            if (empty($items)) {
                break;
            }
        }

        return $processed;
    }

    private function aggregateTotals(array $items)
    {
        $totals = [
            'calo' => 0,
            'protein' => 0,
            'carb' => 0,
            'fat' => 0,
            'fiber' => 0
        ];

        foreach ($items as $item) {
            $totals['calo'] += floatval($item['calo'] ?? 0);
            $totals['protein'] += floatval($item['protein'] ?? 0);
            $totals['carb'] += floatval($item['carb'] ?? 0);
            $totals['fat'] += floatval($item['fat'] ?? 0);
            $totals['fiber'] += floatval($item['fiber'] ?? 0);
        }

        foreach ($totals as $key => $value) {
            $totals[$key] = round($value, 1);
        }

        return $totals;
    }

    private function saveNutritionRecord($userId, array $food, string $image)
    {
        $conn = (new Database())->getConnection();
        $stmt = $conn->prepare(
            "INSERT INTO ketqua_ai (user_id, ten_mon, calo, protein, carb, fat, hinh_anh, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "isdddds",
            $userId,
            $food['name'],
            $food['calo'],
            $food['protein'],
            $food['carb'],
            $food['fat'],
            $image
        );

        return $stmt->execute();
    }

    // =========================
    // RESULT
    // =========================
    public function result()
    {
        $food = $_SESSION['food'] ?? null;
        $advice = $_SESSION['advice'] ?? null;

        if (!$food) {
            header("Location: index.php?controller=food&action=index");
            exit;
        }

        ob_start();
        require "app/views/food/infor_nutri.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }
}
