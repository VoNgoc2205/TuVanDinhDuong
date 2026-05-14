<?php
require_once "app/services/AIService.php";
require_once "app/models/ChatModel.php";
require_once "app/models/NutritionModel.php";
require_once "app/helpers/SessionHelper.php";

class ChatboxController
{
    private $ai;
    private $model;

    public function __construct()
    {
        $this->ai = new AIService();
        $this->model = new ChatModel();
        SessionHelper::start();
    }

    public function index()
    {
        SessionHelper::requireLogin();
        $user = SessionHelper::user();

        $activeConversationId = $this->resolveConversationId($user['id'], (int)($_GET['conversation_id'] ?? 0));
        $messages = $activeConversationId ? $this->model->getByConversation($user['id'], $activeConversationId) : [];
        $conversations = $this->model->getConversations($user['id']);

        ob_start();
        require "app/views/chatbox/chatbox.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    public function chatbox()
    {
        $this->index();
    }

    public function send()
    {
        SessionHelper::requireLogin();
        $user = SessionHelper::user();
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $msg = trim($body['message'] ?? $_POST['message'] ?? '');

        header('Content-Type: application/json; charset=utf-8');

        if ($msg === '') {
            echo json_encode(['error' => 'Tin nhắn trống']);
            return;
        }

        $conversationId = $this->getOrCreateConversationId($user['id'], $msg);
        $this->model->save($user['id'], 'user', $msg, null, $conversationId);

        $history = $this->model->getRecent($user['id'], $conversationId);
        $profile = $_SESSION['profile'] ?? [];
        $result = $this->ai->chatNutrition($msg, $profile, $history);
        $reply = trim($result['reply'] ?? '');
        $nutrition = $result['nutrition'] ?? null;

        if ($reply === '') {
            $reply = $this->getFallbackResponse($msg);
        }

        $this->model->save($user['id'], 'ai', $reply, null, $conversationId);

        echo json_encode([
            'reply' => $reply,
            'nutrition' => $nutrition,
            'conversation_id' => $conversationId
        ], JSON_UNESCAPED_UNICODE);
    }

    public function reset()
    {
        SessionHelper::requireLogin();
        unset($_SESSION['active_chat_conversation_id']);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    }

    public function newConversation()
    {
        $this->reset();
    }

    public function pinConversation()
    {
        SessionHelper::requireLogin();
        $user = SessionHelper::user();
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $conversationId = (int)($body['conversation_id'] ?? 0);
        $pinned = !empty($body['pinned']);

        header('Content-Type: application/json; charset=utf-8');

        if ($conversationId <= 0 || !$this->model->userOwnsConversation($user['id'], $conversationId)) {
            echo json_encode(['success' => false, 'error' => 'Không tìm thấy hội thoại'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($pinned && $this->model->countPinned($user['id']) >= 3) {
            echo json_encode(['success' => false, 'error' => 'Bạn chỉ có thể ghim tối đa 3 đoạn chat'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $ok = $this->model->setPinned($user['id'], $conversationId, $pinned);
        echo json_encode(['success' => $ok, 'pinned' => $pinned], JSON_UNESCAPED_UNICODE);
    }

    public function deleteConversation()
    {
        SessionHelper::requireLogin();
        $user = SessionHelper::user();
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $conversationId = (int)($body['conversation_id'] ?? 0);

        header('Content-Type: application/json; charset=utf-8');

        if ($conversationId <= 0 || !$this->model->userOwnsConversation($user['id'], $conversationId)) {
            echo json_encode(['success' => false, 'error' => 'Không tìm thấy hội thoại'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $ok = $this->model->deleteConversation($user['id'], $conversationId);
        if ((int)($_SESSION['active_chat_conversation_id'] ?? 0) === $conversationId) {
            unset($_SESSION['active_chat_conversation_id']);
        }

        echo json_encode(['success' => $ok], JSON_UNESCAPED_UNICODE);
    }

    public function upload()
    {
        SessionHelper::requireLogin();
        $user = SessionHelper::user();

        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['error' => 'Không có ảnh hoặc ảnh bị lỗi'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $file = $_FILES['image'];
        $filename = time() . "_" . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($file['name']));
        $path = "public/uploads/" . $filename;

        if (!move_uploaded_file($file['tmp_name'], $path)) {
            echo json_encode(['error' => 'Không thể lưu tệp ảnh'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $message = trim($_POST['message'] ?? '');

        if ($this->looksLikeMedicalUpload($message)) {
            $this->handleMedicalImage($user, $path, $message);
            return;
        }

        $this->handleFoodImage($user, $path, $message);
    }

    private function handleMedicalImage(array $user, string $path, string $message = ''): void
    {
        $conversationId = $this->getOrCreateConversationId($user['id'], 'Phân tích hồ sơ bệnh án');
        $extractedData = $this->ai->extractMedicalData($path);

        $nutritionModel = new NutritionModel();
        $saved = $nutritionModel->saveMedicalRecord($user['id'], $path, $extractedData);

        $reply = $saved
            ? "Hồ sơ bệnh án đã được lưu. AI đã trích xuất các thông tin sức khỏe chính để hỗ trợ tư vấn dinh dưỡng."
            : "Ảnh đã được phân tích, nhưng hệ thống chưa lưu được vào hồ sơ. Bạn có thể thử lại sau.";

        if (!empty($extractedData['diagnosis'])) {
            $reply .= "\n\nChẩn đoán/kết luận: " . $extractedData['diagnosis'];
        }
        if (!empty($extractedData['treatment'])) {
            $reply .= "\nHướng điều trị: " . $extractedData['treatment'];
        }
        if (!empty($extractedData['health_condition'])) {
            $reply .= "\nTình trạng sức khỏe: " . $extractedData['health_condition'];
        }

        $this->model->save($user['id'], 'user', $message !== '' ? $message : '[MEDICAL_RECORD]', $path, $conversationId);
        $this->model->save($user['id'], 'ai', $reply, null, $conversationId);

        echo json_encode([
            'image' => $path,
            'reply' => $reply,
            'type' => 'medical',
            'data' => $extractedData,
            'conversation_id' => $conversationId
        ], JSON_UNESCAPED_UNICODE);
    }

    private function looksLikeMedicalUpload(string $message): bool
    {
        $lower = mb_strtolower($message, 'UTF-8');
        return (bool)preg_match('/\b(bệnh án|hồ sơ bệnh|hồ sơ y tế|đơn thuốc|xét nghiệm|chẩn đoán|toa thuốc|bác sĩ|kết quả khám)\b/u', $lower);
    }

    private function handleFoodImage(array $user, string $path, string $message = ''): void
    {
        $conversationId = $this->getOrCreateConversationId($user['id'], 'Phân tích món ăn từ ảnh');
        $profile = $_SESSION['profile'] ?? [];
        $analysis = $this->ai->analyzeFoodImage($path, $profile);

        $reply = trim($analysis['reply'] ?? '');
        if ($reply === '') {
            $reply = 'AI chưa phân tích được ảnh này. Bạn hãy thử ảnh rõ hơn hoặc mô tả món ăn bằng chữ.';
        }

        $this->model->save($user['id'], 'user', $message !== '' ? $message : '[IMAGE]', $path, $conversationId);
        $this->model->save($user['id'], 'ai', $reply, null, $conversationId);

        echo json_encode([
            'image' => $path,
            'reply' => $reply,
            'type' => 'food',
            'nutrition' => $analysis['nutrition'] ?? null,
            'conversation_id' => $conversationId
        ], JSON_UNESCAPED_UNICODE);
    }

    public function uploadProfile()
    {
        SessionHelper::requireLogin();
        $user = SessionHelper::user();

        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_FILES['profile']) || $_FILES['profile']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['error' => 'Không có hồ sơ hoặc hồ sơ bị lỗi'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $file = $_FILES['profile'];
        $filename = time() . "_" . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($file['name']));
        $uploadDir = "public/uploads/profiles/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $path = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $path)) {
            echo json_encode(['error' => 'Không thể lưu hồ sơ dinh dưỡng'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $conversationId = $this->getOrCreateConversationId($user['id'], 'Tải hồ sơ dinh dưỡng');
        $nutritionModel = new NutritionModel();
        $saved = $nutritionModel->saveMedicalRecord($user['id'], $path);
        $reply = $saved
            ? 'Hồ sơ dinh dưỡng đã được tải lên và lưu thành công.'
            : 'Không thể lưu hồ sơ dinh dưỡng. Vui lòng thử lại.';

        $this->model->save($user['id'], 'user', '[NUTRITION_PROFILE]', $path, $conversationId);
        $this->model->save($user['id'], 'ai', $reply, null, $conversationId);

        echo json_encode([
            'reply' => $reply,
            'success' => $saved,
            'conversation_id' => $conversationId
        ], JSON_UNESCAPED_UNICODE);
    }

    public function analyzeFood()
    {
        SessionHelper::requireLogin();
        $user = SessionHelper::user();
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $foodName = trim($body['food_name'] ?? '');
        $gram = max(1, intval($body['gram'] ?? 250));

        header('Content-Type: application/json; charset=utf-8');

        if ($foodName === '') {
            echo json_encode(['error' => 'Tên thực phẩm không được để trống'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $items = $this->ai->standardizeFoods([['name' => $foodName, 'gram' => $gram]], 'USDA friendly English food names');
        $nutrition = $this->ai->estimateNutritionFromItems($items);
        $profile = $_SESSION['profile'] ?? [];
        $aiAnalysis = $this->ai->generateVietnameseNutritionAnalysis($nutrition['totals'], $nutrition['items'], $profile, $foodName);

        $summaryLines = [];
        foreach ($nutrition['items'] as $item) {
            $summaryLines[] = "- {$item['name']} {$item['gram']}g: {$item['calo']} kcal, {$item['protein']}g đạm, {$item['carb']}g tinh bột, {$item['fat']}g chất béo";
        }

        $reply = "Phân tích {$foodName} ({$gram}g):\n" . implode("\n", $summaryLines);
        $reply .= "\nTổng: {$nutrition['totals']['calo']} kcal, {$nutrition['totals']['protein']}g protein, {$nutrition['totals']['carb']}g carb, {$nutrition['totals']['fat']}g fat.";

        $advice = trim($aiAnalysis['advice'] ?? '');
        if ($advice !== '') {
            $reply .= "\n\n" . $advice;
        }

        $conversationId = $this->getOrCreateConversationId($user['id'], "{$foodName} {$gram}g");
        $this->model->save($user['id'], 'user', "[FOOD] {$foodName} {$gram}g", null, $conversationId);
        $this->model->save($user['id'], 'ai', $reply, null, $conversationId);

        echo json_encode([
            'reply' => $reply,
            'nutrition' => $nutrition,
            'conversation_id' => $conversationId
        ], JSON_UNESCAPED_UNICODE);
    }

    private function resolveConversationId(int $userId, int $requestedId = 0): ?int
    {
        if ($requestedId > 0 && $this->model->userOwnsConversation($userId, $requestedId)) {
            $_SESSION['active_chat_conversation_id'] = $requestedId;
            return $requestedId;
        }

        $sessionId = (int)($_SESSION['active_chat_conversation_id'] ?? 0);
        if ($sessionId > 0 && $this->model->userOwnsConversation($userId, $sessionId)) {
            return $sessionId;
        }

        unset($_SESSION['active_chat_conversation_id']);
        return null;
    }

    private function getOrCreateConversationId(int $userId, string $titleSeed = 'Hội thoại mới'): int
    {
        $conversationId = $this->resolveConversationId($userId);
        if (!$conversationId) {
            $conversationId = $this->model->createConversation($userId, $this->makeConversationTitle($titleSeed));
            $_SESSION['active_chat_conversation_id'] = $conversationId;
        }
        return $conversationId;
    }

    private function makeConversationTitle(string $seed): string
    {
        $title = trim(preg_replace('/\s+/', ' ', $seed));
        if ($title === '' || str_starts_with($title, '[')) {
            return 'Hội thoại mới';
        }
        return mb_strlen($title, 'UTF-8') > 48 ? mb_substr($title, 0, 48, 'UTF-8') . '...' : $title;
    }

    private function getFallbackResponse(string $message): string
    {
        return "Mình chưa nhận được phản hồi từ AI lúc này. Bạn có thể hỏi lại ngắn gọn hơn, ví dụ: “100g cơm gà bao nhiêu calo?” hoặc “Tôi muốn giảm cân nên ăn tối thế nào?”.";
    }
}
