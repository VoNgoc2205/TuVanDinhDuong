<?php
require_once "app/config/api.php";

class AIService
{
    public function callOpenAI(array $data): ?string
    {
        $payload = [
            'model' => $data['model'] ?? OPENAI_MODEL,
            'messages' => $this->buildOpenAIChatMessages($data),
            'temperature' => $data['temperature'] ?? 0.3,
            'max_tokens' => $data['max_tokens'] ?? ($data['max_output_tokens'] ?? ($data['maxOutputTokens'] ?? 1200)),
        ];

        if (!empty($data['response_format']) && is_array($data['response_format'])) {
            $payload['response_format'] = $data['response_format'];
        }

        if (empty($payload['messages'])) {
            return null;
        }

        $result = $this->sendOpenAIRequest($payload);
        $text = $result ? $this->extractOpenAIText($result) : null;
        return $text !== null ? trim($text) : null;
    }

    private function sendOpenAIRequest(array $payload): ?array
    {
        if (!$this->hasOpenAIApiKey()) {
            error_log('OpenAI API key is not configured.');
            return null;
        }

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Accept: application/json",
                "Authorization: Bearer " . OPENAI_API_KEY
            ],
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 90,
        ]);

        $res = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            error_log("OpenAI cURL error: {$error}");
            return null;
        }

        $json = json_decode($res, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("OpenAI JSON parse failed [{$httpCode}]: " . json_last_error_msg() . " / raw: " . substr($res, 0, 1000));
            return null;
        }

        if (isset($json['error'])) {
            error_log("OpenAI API error [{$httpCode}]: " . json_encode($json, JSON_UNESCAPED_UNICODE));
            return null;
        }

        return is_array($json) ? $json : null;
    }

    private function hasOpenAIApiKey(): bool
    {
        $key = trim((string)OPENAI_API_KEY);
        return $key !== '' && !in_array($key, ['KEY_OPENAI', 'YOUR_OPENAI_API_KEY'], true);
    }

    private function buildOpenAIChatMessages(array $data): array
    {
        if (isset($data['text']) && trim($data['text']) !== '') {
            return [[
                'role' => 'user',
                'content' => trim($data['text'])
            ]];
        }

        if (isset($data['contents']) && is_array($data['contents'])) {
            $messages = [];
            foreach ($data['contents'] as $contentBlock) {
                if (!isset($contentBlock['parts']) || !is_array($contentBlock['parts'])) {
                    continue;
                }
                $content = $this->buildOpenAIChatContentParts($contentBlock['parts']);
                if (!empty($content)) {
                    $messages[] = [
                        'role' => $contentBlock['role'] ?? 'user',
                        'content' => $content
                    ];
                }
            }

            return $messages;
        }

        if (isset($data['content']) && is_array($data['content'])) {
            $content = $this->buildOpenAIChatContentParts($data['content']);
            return !empty($content) ? [['role' => 'user', 'content' => $content]] : [];
        }

        return [];
    }

    private function buildOpenAIChatContentParts(array $parts): array
    {
        $content = [];

        foreach ($parts as $part) {
            if (!is_array($part)) {
                continue;
            }

            if (isset($part['text'])) {
                $content[] = [
                    'type' => 'text',
                    'text' => $part['text']
                ];
                continue;
            }

            if (($part['type'] ?? '') === 'input_text' && isset($part['text'])) {
                $content[] = [
                    'type' => 'text',
                    'text' => $part['text']
                ];
                continue;
            }

            if (isset($part['inline_data'])) {
                $inline = $part['inline_data'];
                $mimeType = $inline['mime_type'] ?? 'image/jpeg';
                $content[] = [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => "data:{$mimeType};base64," . ($inline['data'] ?? ''),
                        'detail' => 'high'
                    ]
                ];
                continue;
            }

            if (($part['type'] ?? '') === 'input_image') {
                $imageData = $part['image_url'] ?? '';
                $mimeType = 'image/jpeg';

                if (isset($part['image']) && is_array($part['image'])) {
                    $mimeType = $part['image']['mime_type'] ?? $mimeType;
                    $imageData = $part['image']['data'] ?? $imageData;
                }

                if ($imageData !== '') {
                    $content[] = [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => strpos($imageData, 'data:') === 0 ? $imageData : "data:{$mimeType};base64,{$imageData}",
                            'detail' => $part['detail'] ?? 'high'
                        ]
                    ];
                }
            }
        }

        return $content;
    }

    private function extractOpenAIText(array $json): ?string
    {
        if (isset($json['choices'][0]['message']['content']) && is_string($json['choices'][0]['message']['content'])) {
            return $json['choices'][0]['message']['content'];
        }

        if (isset($json['output_text']) && is_string($json['output_text'])) {
            return $json['output_text'];
        }

        if (isset($json['content']) && is_string($json['content'])) {
            return $json['content'];
        }

        if (isset($json['output']) && is_array($json['output'])) {
            foreach ($json['output'] as $output) {
                if (($output['type'] ?? '') !== 'message' || empty($output['content']) || !is_array($output['content'])) {
                    continue;
                }

                foreach ($output['content'] as $part) {
                    if (isset($part['text']) && is_string($part['text'])) {
                        return $part['text'];
                    }
                }
            }
        }

        return null;
    }

    private function callUSDA(string $endpoint, string $method = 'GET', array $data = [])
    {
        if (!$this->hasUsdaApiKey()) {
            return null;
        }

        $url = "https://api.nal.usda.gov/fdc/v1/{$endpoint}?api_key=" . USDA_API_KEY;
        $ch = curl_init();

        if ($method === 'GET') {
            if (!empty($data)) {
                $url .= '&' . http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }

        curl_close($ch);
        $result = json_decode($response, true);
        return is_array($result) ? $result : null;
    }

    private function hasUsdaApiKey(): bool
    {
        $key = trim((string)USDA_API_KEY);
        return $key !== '' && !in_array($key, ['KEY_USDA', 'YOUR_USDA_API_KEY'], true);
    }

    private function extractJson($text)
    {
        if (!$text) {
            return null;
        }

        $text = trim($text);
        $text = preg_replace('/```[a-zA-Z]*|```/', '', $text);

        $decoded = json_decode($text, true);
        if ($decoded !== null) {
            return $decoded;
        }

        $start = min(array_filter([
            strpos($text, '{'),
            strpos($text, '[')
        ], fn($pos) => $pos !== false));

        if ($start === null) {
            return null;
        }

        for ($i = strlen($text) - 1; $i > $start; $i--) {
            $raw = substr($text, $start, $i - $start + 1);
            $decoded = json_decode($raw, true);
            if ($decoded !== null) {
                return $decoded;
            }
        }

        return null;
    }

    private function extractDishNameFromText($text)
    {
        if (!$text) {
            return null;
        }

        $text = trim($text);
        $text = preg_replace('/[`"\']+/', '', $text);

        if (preg_match('/dish[_\s]?name[:=]\s*([^\n]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }

        if (
            preg_match('/^([A-Za-zÀ-ỹ0-9\s\-\/]+)$/u', $text) && strlen($text) < 50
            && !preg_match('/\b(gì|bao nhiêu|như thế nào|làm sao|làm thế nào|cần|nên|có nên|được không|thế nào|vì sao|tại sao)\b/ui', $text)
        ) {
            return trim($text);
        }

        if (
            preg_match('/\b(phở|cơm|salad|bún|mì|pizza|sushi|hamburger|gà|cá|trứng|rau|canh|soup|curry|stew|sandwich|bánh mì|xôi|bún chả|phở bò|bún bò|mì quảng|gỏi cuốn)\b/iu', $text)
            && strlen($text) < 60
            && !preg_match('/\b(gì|bao nhiêu|như thế nào|làm sao|làm thế nào|cần|nên|có nên|được không|thế nào|vì sao|tại sao|là gì|những gì)\b/ui', $text)
        ) {
            return trim($text);
        }

        return null;
    }

    private function extractDishNameFromQuestion(string $text): ?string
    {
        $text = $this->sanitizeText($text);
        $text = preg_replace('/\b(bao nhiêu|mấy|là bao nhiêu|có bao nhiêu)\s*(calo|kcal|năng lượng)?\b/ui', ' ', $text);
        $text = preg_replace('/\b(calo|kcal|năng lượng|dinh dưỡng|món này|đây là món gì|là món gì|cho tôi biết|hãy tính|tính giúp|phân tích)\b/ui', ' ', $text);
        $text = preg_replace('/[?？!.,:;]+/u', ' ', $text);
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        if ($text === '' || mb_strlen($text, 'UTF-8') > 80) {
            return null;
        }

        return $text;
    }

    private function extractItemsFromText($text)
    {
        if (!$text) {
            return [];
        }

        $text = trim($text);
        $text = preg_replace('/```[a-zA-Z]*|```/', '', $text);
        $lines = preg_split('/\r?\n/', $text);
        $items = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (preg_match('/^[-\*]\s*([^\(\:\,]+?)(?:\s*\((\d+)\s*g\))?\s*$/iu', $line, $matches)) {
                $name = trim($matches[1]);
                $gram = isset($matches[2]) ? max(1, floatval($matches[2])) : 100;
                if ($name !== '') {
                    $items[] = ['name' => $name, 'gram' => $gram];
                }
                continue;
            }

            if (preg_match('/^(\d+)\.\s*([^\(\:\,]+?)(?:\s*\((\d+)\s*g\))?\s*$/iu', $line, $matches)) {
                $name = trim($matches[2]);
                $gram = isset($matches[3]) ? max(1, floatval($matches[3])) : 100;
                if ($name !== '') {
                    $items[] = ['name' => $name, 'gram' => $gram];
                }
                continue;
            }

            if (stripos($line, 'dish_name') !== false || stripos($line, 'item') !== false || stripos($line, 'ingredients') !== false) {
                continue;
            }

            if (preg_match('/\b(gì|bao nhiêu|như thế nào|làm sao|làm thế nào|cần|nên|có nên|được không|thế nào|vì sao|tại sao|là gì|những gì)\b/ui', $line)) {
                continue;
            }

            if (preg_match('/[A-Za-zÀ-ỹ0-9\s]+/u', $line)) {
                $name = trim(preg_replace('/\s{2,}/', ' ', preg_replace('/[^A-Za-zÀ-ỹ0-9\s\-]/u', ' ', $line)));
                if ($name !== '' && strlen($name) < 100) {
                    $items[] = ['name' => $name, 'gram' => 100];
                }
            }
        }

        return array_values(array_filter($items, fn($item) => !empty($item['name'])));
    }

    private function sanitizeText(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/\s+/u', ' ', $text);
        return $text;
    }

    private function cleanAssistantReply(?string $text): string
    {
        $text = trim((string)$text);
        if ($text === '') {
            return '';
        }

        $text = preg_replace('/\*\*(.*?)\*\*/u', '$1', $text);
        $text = preg_replace('/__(.*?)__/u', '$1', $text);
        $text = preg_replace('/^\s{0,3}#{1,6}\s*/mu', '', $text);
        $text = preg_replace('/^\s*[-*]\s+/mu', '• ', $text);
        $text = preg_replace("/\n{3,}/u", "\n\n", $text);
        return trim($text);
    }

    private function understandUserMessage(string $text): ?array
    {
        if (!$this->hasOpenAIApiKey()) {
            return null;
        }

        $prompt = "Bạn là bộ não hiểu ngôn ngữ cho trợ lý dinh dưỡng tiếng Việt.\n"
            . "Hãy tự hiểu câu người dùng, không dựa vào từ khóa cố định.\n"
            . "Phân loại ý định và trích món ăn/khẩu phần nếu người dùng hỏi về một món, một bữa ăn hoặc muốn tính calo.\n"
            . "Nếu người dùng chỉ hỏi tư vấn chung như nên ăn gì, giảm cân ra sao, bệnh này ăn thế nào, hãy đặt intent=nutrition và answer_mode=advice, items rỗng.\n"
            . "Nếu là lời chào, đặt intent=greeting. Nếu không liên quan dinh dưỡng, đặt intent=general.\n\n"
            . "Chỉ trả về JSON hợp lệ theo schema:\n"
            . "{\"intent\":\"nutrition|greeting|general\",\"answer_mode\":\"nutrition_lookup|advice|chat\",\"dish_name\":\"\",\"items\":[{\"name\":\"\",\"gram\":100}],\"confidence\":0.0}\n\n"
            . "Quy tắc:\n"
            . "- items chỉ chứa thực phẩm/món ăn thật sự có trong câu.\n"
            . "- Nếu người dùng không nói gram, hãy ước tính khẩu phần hợp lý: món chính 250g, đồ uống 250ml quy đổi 250g, nguyên liệu nhỏ 100g.\n"
            . "- Không bịa món nếu câu không nhắc món cụ thể.\n"
            . "- dish_name là tên món chính tự nhiên bằng tiếng Việt nếu có.\n"
            . "Câu người dùng: {$text}";

        $response = $this->callOpenAI([
            'model' => OPENAI_MODEL,
            'temperature' => 0.1,
            'max_output_tokens' => 700,
            'response_format' => ['type' => 'json_object'],
            'contents' => [[
                'parts' => [[
                    'text' => $prompt
                ]]
            ]]
        ]);

        $json = $this->extractJson($response);
        if (!is_array($json)) {
            return null;
        }

        $intent = $json['intent'] ?? 'general';
        if (!in_array($intent, ['nutrition', 'greeting', 'general'], true)) {
            $intent = 'general';
        }

        $answerMode = $json['answer_mode'] ?? 'chat';
        if (!in_array($answerMode, ['nutrition_lookup', 'advice', 'chat'], true)) {
            $answerMode = 'chat';
        }

        return [
            'intent' => $intent,
            'answer_mode' => $answerMode,
            'dish_name' => trim((string)($json['dish_name'] ?? '')),
            'items' => $this->normalizeFoodItemsFromArray($json['items'] ?? []),
            'confidence' => max(0, min(1, floatval($json['confidence'] ?? 0)))
        ];
    }

    private function normalizeFoodItemsFromArray($items): array
    {
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($item) {
            if (is_string($item)) {
                $name = trim($item);
                return $name === '' ? null : ['name' => $name, 'gram' => 100];
            }

            if (!is_array($item) || empty($item['name'])) {
                return null;
            }

            $name = trim((string)$item['name']);
            if ($name === '') {
                return null;
            }

            return [
                'name' => $name,
                'gram' => max(1, floatval($item['gram'] ?? 100))
            ];
        }, $items)));
    }

    private function detectIntent(string $text): string
    {
        $understanding = $this->understandUserMessage($text);
        if (is_array($understanding) && !empty($understanding['intent'])) {
            return $understanding['intent'];
        }

        return $this->detectIntentByRules($text);
    }

    private function detectIntentByRules(string $text): string
    {
        $lower = mb_strtolower($text, 'UTF-8');

        if (preg_match('/\b(calo|năng lượng|dinh dưỡng|thực đơn|món ăn|thực phẩm|ăn gì|uống gì|bữa sáng|bữa trưa|bữa tối|ăn kiêng|ăn giảm cân|ăn tăng cơ)\b/ui', $lower)) {
            return 'nutrition';
        }

        if (
            preg_match('/\b(tư vấn|giúp|gợi ý|đề xuất|khuyên|nên|cần|hướng dẫn|làm thế nào)\b/ui', $lower)
            && preg_match('/\b(calo|dinh dưỡng|ăn|thực đơn|món ăn|uống|khẩu phần|bữa ăn)\b/ui', $lower)
        ) {
            return 'nutrition';
        }

        if (preg_match('/\b(xin chào|chào bạn|chào|hello|hi|hey|gửi lời chào)\b/ui', $lower)) {
            return 'greeting';
        }

        return 'general';
    }

    public function detectFoodsFromImage($imagePath)
    {
        $fileContents = @file_get_contents($imagePath);
        if ($fileContents === false) {
            return ['dishName' => null, 'items' => []];
        }

        $base64 = base64_encode($fileContents);
        $mimeType = 'image/jpeg';
        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($imagePath) ?: $mimeType;
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $type = finfo_file($finfo, $imagePath);
                if ($type) {
                    $mimeType = $type;
                }
                finfo_close($finfo);
            }
        }

        $prompt = "Bạn là AI thị giác chuyên nhận diện món ăn cho người Việt. Hãy phân tích ảnh món ăn này thật kỹ và trả về ONLY JSON hợp lệ, không giải thích ngoài JSON.\n"
            . "Yêu cầu:\n"
            . "- dish_name: tên món ăn cụ thể bằng tiếng Việt, ví dụ \"thịt heo quay\", \"cơm gà\", \"bún bò\".\n"
            . "- items: các thành phần/món nhìn thấy được, mỗi phần tử có name và gram.\n"
            . "- Ước tính gram thực tế theo khẩu phần trong ảnh.\n"
            . "- Nếu thấy một món chính, vẫn phải trả ít nhất 1 item chính.\n"
            . "- Nếu không chắc 100%, hãy đưa ra nhận diện gần đúng hợp lý nhất, không trả rỗng.\n"
            . "Schema: {\"dish_name\":\"\",\"items\":[{\"name\":\"\",\"gram\":100}]}";

        $data = [
            "model" => "gpt-4o",
            "temperature" => 0.2,
            "max_output_tokens" => 900,
            "response_format" => ["type" => "json_object"],
            "contents" => [[
                "parts" => [
                    ["text" => $prompt],
                    [
                        "inline_data" => [
                            "mime_type" => $mimeType,
                            "data" => $base64
                        ]
                    ]
                ]
            ]]
        ];

        $res = $this->callOpenAI($data);

        if ($res === null || trim($res) === '') {
            return ['dishName' => null, 'items' => []];
        }

        $json = $this->extractJson($res);
        $dishName = null;
        $items = [];

        if (is_array($json)) {
            if (isset($json['dish_name'])) {
                $dishName = trim($json['dish_name']);
            }

            if (isset($json['items']) && is_array($json['items'])) {
                foreach ($json['items'] as $item) {
                    if (is_string($item)) {
                        $items[] = ['name' => trim($item), 'gram' => 100];
                        continue;
                    }
                    if (!empty($item['name'])) {
                        $items[] = [
                            'name' => trim($item['name']),
                            'gram' => max(1, floatval($item['gram'] ?? 100))
                        ];
                    }
                }
            }

            if (empty($items)) {
                $rawItems = $this->extractItemsFromText($res);
                if (!empty($rawItems)) {
                    $items = $rawItems;
                }
            }
        }

        if ($dishName === null) {
            $dishName = $this->extractDishNameFromText($res);
        }

        if (empty($items) && $dishName) {
            $items[] = [
                'name' => $dishName,
                'gram' => 250
            ];
        }

        return [
            'dishName' => $dishName,
            'items' => $items
        ];
    }

    private function parseFoodListFromMessage(string $message): array
    {
        if (!$this->hasOpenAIApiKey()) {
            return [];
        }

        $response = $this->callOpenAI([
            'contents' => [[
                'parts' => [[
                    'text' => "Bạn là trợ lý dinh dưỡng. Trích xuất chính xác tên các thực phẩm và khối lượng (gram) từ câu sau. Chỉ trả về JSON mảng items với các phần tử {\"name\": \"...\", \"gram\": 100}. Nếu không có gram, dùng 100. Nếu không thể trích, trả về mảng rỗng. Text:\n{$message}"
                ]]
            ]]
        ]);

        $items = $this->extractJson($response);
        if (!is_array($items)) {
            $items = $this->extractItemsFromText($response);
        }

        if (is_array($items) && isset($items['items'])) {
            $items = $items['items'];
        }

        if (!is_array($items)) {
            return [];
        }

        return $this->normalizeFoodItemsFromArray($items);
    }

    public function searchUSDAFoods(string $query): array
    {
        $data = [
            'generalSearchInput' => $query,
            'pageSize' => 5,
            'requireAllWords' => true
        ];

        $result = $this->callUSDA('foods/search', 'POST', $data);
        if (empty($result['foods']) || !is_array($result['foods'])) {
            return [];
        }

        return array_slice($result['foods'], 0, 3);
    }

    public function getUSDANutritionByFdcId(int $fdcId, float $gram = 100)
    {
        $result = $this->callUSDA("food/{$fdcId}", 'GET');
        if (!$result || !is_array($result)) {
            return null;
        }

        $nutrients = $this->parseUSDAFoodNutrients($result, $gram);
        return [
            'name' => $result['description'] ?? ($result['lowercaseDescription'] ?? 'Unknown'),
            'description' => $result['description'] ?? ($result['lowercaseDescription'] ?? ''),
            'gram' => $gram,
            'calo' => round($nutrients['calo'], 2),
            'protein' => round($nutrients['protein'], 2),
            'carb' => round($nutrients['carb'], 2),
            'fat' => round($nutrients['fat'], 2),
            'fiber' => round($nutrients['fiber'], 2),
        ];
    }

    public function getUSDANutrition(string $query, float $gram = 100)
    {
        $search = $this->searchUSDAFoods($query);
        if (empty($search)) {
            return null;
        }

        $best = $search[0];
        if (empty($best['fdcId'])) {
            return null;
        }

        return $this->getUSDANutritionByFdcId((int)$best['fdcId'], $gram);
    }

    public function estimateNutritionFromItems(array $foods)
    {
        $details = [];
        $missingItems = [];
        $totals = [
            'calo' => 0,
            'protein' => 0,
            'carb' => 0,
            'fat' => 0,
            'fiber' => 0
        ];

        foreach ($foods as $food) {
            $itemName = trim($food['name'] ?? '');
            $gram = max(1, floatval($food['gram'] ?? 100));
            if ($itemName === '') {
                continue;
            }

            $nutrition = $this->getUSDANutrition($itemName, $gram);
            if ($nutrition === null || !$this->hasMeaningfulNutrition($nutrition)) {
                $missingItems[] = [
                    'name' => $itemName,
                    'gram' => $gram
                ];
                continue;
            }

            $totals['calo'] += $nutrition['calo'];
            $totals['protein'] += $nutrition['protein'];
            $totals['carb'] += $nutrition['carb'];
            $totals['fat'] += $nutrition['fat'];
            $totals['fiber'] += $nutrition['fiber'];

            $details[] = [
                'name' => $itemName,
                'gram' => $gram,
                'calo' => $nutrition['calo'],
                'protein' => $nutrition['protein'],
                'carb' => $nutrition['carb'],
                'fat' => $nutrition['fat'],
                'fiber' => $nutrition['fiber'],
                'matched' => $nutrition['description']
            ];
        }

        if (!empty($missingItems)) {
            $estimatedItems = $this->estimateNutritionForFoodItems($missingItems, implode(', ', array_column($missingItems, 'name')));
            $usedEstimatedIndexes = [];

            foreach ($estimatedItems as $estimatedIndex => $estimated) {
                $item = [
                    'name' => $estimated['name'] ?? '',
                    'gram' => max(1, floatval($estimated['gram'] ?? 100)),
                    'calo' => max(0, floatval($estimated['calo'] ?? 0)),
                    'protein' => max(0, floatval($estimated['protein'] ?? 0)),
                    'carb' => max(0, floatval($estimated['carb'] ?? 0)),
                    'fat' => max(0, floatval($estimated['fat'] ?? 0)),
                    'fiber' => max(0, floatval($estimated['fiber'] ?? 0)),
                    'vitamins' => $estimated['vitamins'] ?? '',
                    'minerals' => $estimated['minerals'] ?? '',
                    'matched' => 'OpenAI estimate'
                ];

                if ($item['name'] === '' || !$this->hasMeaningfulNutrition($item)) {
                    continue;
                }

                $totals['calo'] += $item['calo'];
                $totals['protein'] += $item['protein'];
                $totals['carb'] += $item['carb'];
                $totals['fat'] += $item['fat'];
                $totals['fiber'] += $item['fiber'];
                $details[] = $item;
                $usedEstimatedIndexes[$estimatedIndex] = true;
            }

            foreach ($missingItems as $missingIndex => $missing) {
                if (isset($usedEstimatedIndexes[$missingIndex])) {
                    continue;
                }

                $fallback = $this->estimateNutritionLocally($missing['name'], $missing['gram']);
                if ($fallback !== null) {
                    $totals['calo'] += $fallback['calo'];
                    $totals['protein'] += $fallback['protein'];
                    $totals['carb'] += $fallback['carb'];
                    $totals['fat'] += $fallback['fat'];
                    $totals['fiber'] += $fallback['fiber'];
                    $details[] = $fallback;
                    continue;
                }

                $details[] = [
                    'name' => $missing['name'],
                    'gram' => $missing['gram'],
                    'calo' => 0,
                    'protein' => 0,
                    'carb' => 0,
                    'fat' => 0,
                    'fiber' => 0,
                    'note' => 'Chưa tìm thấy dữ liệu dinh dưỡng phù hợp'
                ];
            }
        }

        return [
            'items' => $details,
            'totals' => [
                'calo' => round($totals['calo'], 2),
                'protein' => round($totals['protein'], 2),
                'carb' => round($totals['carb'], 2),
                'fat' => round($totals['fat'], 2),
                'fiber' => round($totals['fiber'], 2)
            ]
        ];
    }

    private function hasMeaningfulNutrition(array $nutrition): bool
    {
        return floatval($nutrition['calo'] ?? 0) > 0
            || floatval($nutrition['protein'] ?? 0) > 0
            || floatval($nutrition['carb'] ?? 0) > 0
            || floatval($nutrition['fat'] ?? 0) > 0;
    }

    private function estimateNutritionLocally(string $name, float $gram): ?array
    {
        $key = $this->normalizeFoodKey($name);
        $database = $this->localNutritionPer100g();

        $best = null;
        $bestScore = 0;
        foreach ($database as $pattern => $data) {
            $score = 0;
            if ($key === $pattern) {
                $score = 100;
            } elseif (strpos($key, $pattern) !== false || strpos($pattern, $key) !== false) {
                $score = 80;
            } else {
                foreach (explode(' ', $pattern) as $token) {
                    if (strlen($token) >= 3 && strpos($key, $token) !== false) {
                        $score += 15;
                    }
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $data;
            }
        }

        if ($best === null || $bestScore < 30) {
            return null;
        }

        $factor = $gram / 100;
        return [
            'name' => $name,
            'gram' => $gram,
            'calo' => round($best['calo'] * $factor, 1),
            'protein' => round($best['protein'] * $factor, 1),
            'carb' => round($best['carb'] * $factor, 1),
            'fat' => round($best['fat'] * $factor, 1),
            'fiber' => round(($best['fiber'] ?? 0) * $factor, 1),
            'vitamins' => $best['vitamins'] ?? '',
            'minerals' => $best['minerals'] ?? '',
            'matched' => 'Ước tính dữ liệu món Việt'
        ];
    }

    private function localNutritionPer100g(): array
    {
        return [
            'thit heo quay' => ['calo' => 520, 'protein' => 19, 'carb' => 0, 'fat' => 48, 'fiber' => 0, 'minerals' => 'sắt, kẽm, natri'],
            'heo quay' => ['calo' => 520, 'protein' => 19, 'carb' => 0, 'fat' => 48, 'fiber' => 0, 'minerals' => 'sắt, kẽm, natri'],
            'thit ba chi quay' => ['calo' => 520, 'protein' => 19, 'carb' => 0, 'fat' => 48, 'fiber' => 0, 'minerals' => 'sắt, kẽm, natri'],
            'da heo' => ['calo' => 545, 'protein' => 61, 'carb' => 0, 'fat' => 31, 'fiber' => 0, 'minerals' => 'natri'],
            'thit heo' => ['calo' => 297, 'protein' => 26, 'carb' => 0, 'fat' => 21, 'fiber' => 0, 'minerals' => 'sắt, kẽm'],
            'thit ga' => ['calo' => 239, 'protein' => 27, 'carb' => 0, 'fat' => 14, 'fiber' => 0, 'minerals' => 'phốt pho, selen'],
            'ga luoc' => ['calo' => 190, 'protein' => 28, 'carb' => 0, 'fat' => 8, 'fiber' => 0, 'minerals' => 'phốt pho, selen'],
            'ga ran' => ['calo' => 320, 'protein' => 20, 'carb' => 11, 'fat' => 22, 'fiber' => 1, 'minerals' => 'natri'],
            'thit bo' => ['calo' => 250, 'protein' => 26, 'carb' => 0, 'fat' => 15, 'fiber' => 0, 'minerals' => 'sắt, kẽm'],
            'ca' => ['calo' => 150, 'protein' => 22, 'carb' => 0, 'fat' => 6, 'fiber' => 0, 'minerals' => 'i-ốt, selen'],
            'tom' => ['calo' => 99, 'protein' => 24, 'carb' => 0.2, 'fat' => 0.3, 'fiber' => 0, 'minerals' => 'i-ốt, selen'],
            'trung' => ['calo' => 155, 'protein' => 13, 'carb' => 1.1, 'fat' => 11, 'fiber' => 0, 'vitamins' => 'vitamin A, B12, D'],
            'com trang' => ['calo' => 130, 'protein' => 2.7, 'carb' => 28, 'fat' => 0.3, 'fiber' => 0.4],
            'com' => ['calo' => 130, 'protein' => 2.7, 'carb' => 28, 'fat' => 0.3, 'fiber' => 0.4],
            'pho bo' => ['calo' => 90, 'protein' => 5, 'carb' => 12, 'fat' => 2.5, 'fiber' => 0.5, 'minerals' => 'natri, sắt'],
            'pho ga' => ['calo' => 85, 'protein' => 5.5, 'carb' => 11, 'fat' => 2, 'fiber' => 0.5, 'minerals' => 'natri'],
            'bun bo' => ['calo' => 95, 'protein' => 5, 'carb' => 13, 'fat' => 2.5, 'fiber' => 0.7, 'minerals' => 'natri, sắt'],
            'bun' => ['calo' => 110, 'protein' => 1.7, 'carb' => 25, 'fat' => 0.2, 'fiber' => 0.5],
            'mi' => ['calo' => 138, 'protein' => 4.5, 'carb' => 25, 'fat' => 2, 'fiber' => 1.2],
            'banh mi' => ['calo' => 265, 'protein' => 9, 'carb' => 49, 'fat' => 3.2, 'fiber' => 2.7],
            'xoi' => ['calo' => 170, 'protein' => 3.5, 'carb' => 37, 'fat' => 0.5, 'fiber' => 1],
            'rau' => ['calo' => 35, 'protein' => 2, 'carb' => 7, 'fat' => 0.3, 'fiber' => 3, 'vitamins' => 'vitamin A, C, K'],
            'salad' => ['calo' => 45, 'protein' => 1.5, 'carb' => 7, 'fat' => 1.5, 'fiber' => 2.5, 'vitamins' => 'vitamin A, C, K'],
        ];
    }

    private function normalizeFoodKey(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        $text = strtr($text, [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd'
        ]);
        $text = preg_replace('/[^a-z0-9\s]+/', ' ', $text);
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    private function isGenericNutritionQuestion(string $text): bool
    {
        $lower = mb_strtolower($text);

        $questionPatterns = [
            '/\b(gì|bao nhiêu|như thế nào|làm sao|làm thế nào|nên|có nên|được không|thế nào|vì sao|tại sao|là gì|những gì)\b/ui',
            '/\b(kiêng|ăn gì|uống gì|thực đơn|dinh dưỡng|calo|năng lượng|tối ưu|khuyến nghị|cách|mục tiêu|mục đích)\b/ui',
            '/\b(giảm cân|tăng cân|tăng cơ|duy trì|phục hồi|sức khỏe|bệnh|vận động|tập|gym)\b/ui',
            '/^(tôi|bạn|mình|chúng tôi)\s+(muốn|cần|muốn biết|cần tìm|cần biết|muốn học|muốn hiểu|cần hiểu)/ui',
        ];

        foreach ($questionPatterns as $pattern) {
            if (preg_match($pattern, $lower)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeFoodDescription(string $text): bool
    {
        $lower = mb_strtolower($text);

        if (preg_match('/\b(\d+\s*(g|gram|kg|ml|l|bát|chén|muỗng|thìa|miếng))\b/u', $lower)) {
            return true;
        }

        if (
            preg_match('/\b(phở|cơm|bún|mì|salad|pizza|sushi|hamburger|gà|cá|thịt|heo|bò|rau|canh|soup|trứng|bánh|burger|curry|sandwich|bánh mì|xôi|bún chả|phở bò|bún bò|mì quảng|gỏi cuốn)\b/u', $lower)
            && preg_match('/\b(calo|kcal|năng lượng|bao nhiêu)\b/u', $lower)
        ) {
            return true;
        }

        if (
            preg_match('/\b(phở|cơm|bún|mì|salad|pizza|sushi|hamburger|gà|cá|thịt|rau|canh|soup|trứng|bánh|burger|curry|sandwich|bánh mì|xôi|bún chả|phở bò|bún bò|mì quảng|gỏi cuốn)\b/u', $lower)
            && !preg_match('/\b(gì|bao nhiêu|như thế nào|làm sao|làm thế nào|cần|nên|có nên|được không|thế nào|vì sao|tại sao|là gì|những gì)\b/ui', $lower)
        ) {
            return true;
        }

        if (preg_match('/(^|\n)[\-\*]\s*[A-Za-zÀ-ỹ0-9]/u', $text)) {
            return true;
        }

        return false;
    }

    public function getNutritionDataFromText(string $text)
    {
        $text = $this->sanitizeText($text);
        if ($text === '') {
            return null;
        }

        $understanding = $this->understandUserMessage($text);
        if (is_array($understanding)) {
            $items = $understanding['items'] ?? [];
            if (empty($items) && !empty($understanding['dish_name']) && ($understanding['answer_mode'] ?? '') === 'nutrition_lookup') {
                $items = [['name' => $understanding['dish_name'], 'gram' => 250]];
            }

            if (!empty($items)) {
                return $this->estimateNutritionFromItems($this->standardizeFoods($items, 'AI understood food items'));
            }

            if (($understanding['intent'] ?? '') !== 'nutrition' || ($understanding['answer_mode'] ?? '') !== 'nutrition_lookup') {
                return null;
            }
        } elseif (!$this->looksLikeFoodDescription($text)) {
            return null;
        }

        $items = $this->extractItemsFromText($text);
        if (empty($items)) {
            $items = $this->parseFoodListFromMessage($text);
        }

        if (empty($items)) {
            $dishName = $this->extractDishNameFromQuestion($text) ?: $this->extractDishNameFromText($text);
            if ($dishName) {
                $items = [['name' => $dishName, 'gram' => 250]];
            }
        }

        if (empty($items)) {
            return null;
        }

        $items = $this->standardizeFoods($items, 'USDA friendly English food names');
        return $this->estimateNutritionFromItems($items);
    }

    public function analyzeFoodImage(string $imagePath, array $profile = [])
    {
        $detected = $this->detectFoodsFromImage($imagePath);
        $items = $detected['items'] ?? [];
        $dishName = $detected['dishName'] ?? null;

        if (empty($items) && $dishName) {
            $items = [['name' => $dishName, 'gram' => 250]];
        }

        if (empty($items)) {
            return [
                'reply' => 'AI chưa đọc được món ăn từ ảnh này. Bạn hãy gửi ảnh rõ hơn, gần món ăn hơn hoặc nhập tên món để tôi phân tích chỉ số dinh dưỡng.',
                'nutrition' => null
            ];
        }

        $nutrition = $this->estimateNutritionFromItems($items);
        $aiAnalysis = $this->generateVietnameseNutritionAnalysis($nutrition['totals'], $nutrition['items'], $profile, $dishName ?: '');
        if (!empty($aiAnalysis['items'])) {
            foreach ($nutrition['items'] as $index => $item) {
                if (!empty($aiAnalysis['items'][$index]['name_vi'])) {
                    $nutrition['items'][$index]['name'] = $aiAnalysis['items'][$index]['name_vi'];
                }
            }
        }

        $displayDishName = $aiAnalysis['dish_name_vi'] ?? $dishName;
        $reply = $this->generateNaturalNutritionReply(
            $displayDishName ? "Ảnh món {$displayDishName}" : 'Ảnh món ăn',
            $nutrition,
            $aiAnalysis,
            $profile,
            $displayDishName ?: ''
        );

        return ['reply' => $reply, 'nutrition' => $nutrition];
    }

    public function chatNutrition($message, $profile = [], $history = [])
    {
        $message = $this->sanitizeText($message);
        $understanding = $this->understandUserMessage($message);
        $intent = is_array($understanding) ? ($understanding['intent'] ?? 'general') : $this->detectIntentByRules($message);

        if ($intent === 'greeting' && empty($understanding['items'])) {
            return [
                'reply' => 'Chào bạn! Tôi là trợ lý dinh dưỡng AI. Bạn có thể hỏi về calo, thực đơn hoặc cách ăn uống lành mạnh.',
                'nutrition' => null
            ];
        }

        $nutritionData = null;
        if (is_array($understanding)) {
            $items = $understanding['items'] ?? [];
            if (empty($items) && !empty($understanding['dish_name']) && ($understanding['answer_mode'] ?? '') === 'nutrition_lookup') {
                $items = [['name' => $understanding['dish_name'], 'gram' => 250]];
            }

            if (!empty($items)) {
                $nutritionData = $this->estimateNutritionFromItems($this->standardizeFoods($items, 'AI understood food items'));
            }
        }

        if ($nutritionData === null) {
            $nutritionData = $this->getNutritionDataFromText($message);
        }

        if ($nutritionData && !empty($nutritionData['items'])) {
            $aiAnalysis = $this->generateVietnameseNutritionAnalysis($nutritionData['totals'], $nutritionData['items'], $profile, '');
            if (!empty($aiAnalysis['items'])) {
                foreach ($nutritionData['items'] as $index => $item) {
                    if (!empty($aiAnalysis['items'][$index]['name_vi'])) {
                        $nutritionData['items'][$index]['name'] = $aiAnalysis['items'][$index]['name_vi'];
                    }
                }
            }

            return [
                'reply' => $this->generateNaturalNutritionReply($message, $nutritionData, $aiAnalysis, $profile, $understanding['dish_name'] ?? ''),
                'nutrition' => $nutritionData
            ];
        }

        if ($intent === 'nutrition' || (!is_array($understanding) && $this->isGenericNutritionQuestion($message))) {
            $context = "";
            foreach ($history as $h) {
                $context .= "{$h['role']}: {$h['message']}\n";
            }

            $goal = $profile['muctieu'] ?? 'bình thường';
            $health = $profile['tinhtrang_suckhoe'] ?? 'không có bệnh đặc biệt';

            $prompt = "Bạn là chuyên gia dinh dưỡng.\n\n" .
                "Thông tin người dùng:\n" .
                "- Mục tiêu: {$goal}\n" .
                "- Tình trạng sức khỏe: {$health}\n\n" .
                "Lịch sử hội thoại:\n" . $context . "\n" .
                "Câu hỏi: {$message}\n\n" .
                "Hãy tự hiểu ý người dùng và trả lời tự nhiên bằng tiếng Việt. Không dùng markdown như **in đậm**, heading hoặc bảng. Có thể trả lời theo đoạn văn ngắn hoặc vài ý rõ ràng nếu cần.";

            $reply = $this->callOpenAI([
                'temperature' => 0.7,
                "contents" => [[
                    "parts" => [["text" => $prompt]]
                ]]
            ]);

            return [
                'reply' => $this->cleanAssistantReply($reply),
                'nutrition' => null
            ];
        }

        // Không tìm được thực phẩm, gọi OpenAI để trả lời chung
        $context = "";
        foreach ($history as $h) {
            $context .= "{$h['role']}: {$h['message']}\n";
        }

        $goal = $profile['muctieu'] ?? 'bình thường';
        $health = $profile['tinhtrang_suckhoe'] ?? 'không có bệnh đặc biệt';

        $prompt = "Bạn là chuyên gia dinh dưỡng.\n\n" .
            "Thông tin người dùng:\n" .
            "- Mục tiêu: {$goal}\n" .
            "- Tình trạng sức khỏe: {$health}\n\n" .
            "Lịch sử hội thoại:\n" . $context . "\n" .
            "Câu hỏi: {$message}\n\n" .
            "Hãy tự hiểu ý người dùng và trả lời tự nhiên, đúng trọng tâm bằng tiếng Việt. Không dùng markdown như **in đậm**, heading hoặc bảng. Nếu câu hỏi liên quan dinh dưỡng thì tư vấn hữu ích; nếu không liên quan, trả lời lịch sự và ngắn gọn.";

        $reply = $this->callOpenAI([
            'temperature' => 0.7,
            "contents" => [[
                "parts" => [["text" => $prompt]]
            ]]
        ]);

        return [
            'reply' => $this->cleanAssistantReply($reply),
            'nutrition' => null
        ];
    }

    private function generateNaturalNutritionReply(string $userMessage, array $nutrition, array $aiAnalysis = [], array $profile = [], string $dishName = ''): string
    {
        $totals = $nutrition['totals'] ?? [];
        $items = $nutrition['items'] ?? [];
        $payload = json_encode([
            'user_question' => $userMessage,
            'dish_name' => $dishName,
            'totals' => $totals,
            'items' => array_map(function ($item) {
                return [
                    'name' => $item['name'] ?? '',
                    'gram' => floatval($item['gram'] ?? 0),
                    'calo' => floatval($item['calo'] ?? 0),
                    'protein' => floatval($item['protein'] ?? 0),
                    'carb' => floatval($item['carb'] ?? 0),
                    'fat' => floatval($item['fat'] ?? 0),
                    'fiber' => floatval($item['fiber'] ?? 0),
                ];
            }, $items),
            'profile' => $profile,
            'analysis_hint' => $aiAnalysis['advice'] ?? ''
        ], JSON_UNESCAPED_UNICODE);

        if ($this->hasOpenAIApiKey()) {
            $prompt = "Bạn là trợ lý dinh dưỡng AI đang trò chuyện tự nhiên với người dùng Việt Nam.\n"
                . "Hãy dùng dữ liệu dinh dưỡng bên dưới để tự viết câu trả lời phù hợp với câu hỏi, không theo mẫu cố định.\n"
                . "Yêu cầu phong cách:\n"
                . "- Trả lời như người tư vấn thật: tự nhiên, gọn, có ngữ cảnh.\n"
                . "- Không dùng markdown: không **in đậm**, không heading, không bảng.\n"
                . "- Không bắt buộc liệt kê tất cả item; chỉ nêu các số quan trọng nhất với câu hỏi.\n"
                . "- Đa dạng cách diễn đạt giữa các lần hỏi; đừng luôn mở đầu bằng cùng một câu.\n"
                . "- Nếu món nhiều béo/calo, nhắc nhẹ cách ăn hợp lý. Nếu phù hợp, có thể đưa gợi ý khẩu phần.\n"
                . "- Không nói rằng bạn dựa trên JSON hay dữ liệu backend.\n"
                . "- Dài khoảng 2 đến 5 câu, trừ khi người dùng yêu cầu chi tiết.\n\n"
                . "Dữ liệu: {$payload}";

            $reply = $this->callOpenAI([
                'model' => OPENAI_MODEL,
                'temperature' => 0.75,
                'max_output_tokens' => 650,
                'contents' => [[
                    'parts' => [[
                        'text' => $prompt
                    ]]
                ]]
            ]);

            $reply = $this->cleanAssistantReply($reply);
            if ($reply !== '') {
                return $reply;
            }
        }

        $firstItem = $items[0] ?? [];
        $name = trim((string)($dishName ?: ($firstItem['name'] ?? 'món này')));
        $gramText = !empty($firstItem['gram']) ? ' khoảng ' . round(floatval($firstItem['gram'])) . 'g' : '';
        $calo = round(floatval($totals['calo'] ?? 0));
        $protein = round(floatval($totals['protein'] ?? 0), 1);
        $carb = round(floatval($totals['carb'] ?? 0), 1);
        $fat = round(floatval($totals['fat'] ?? 0), 1);

        $reply = "{$name}{$gramText} ước tính khoảng {$calo} kcal, gồm {$protein}g đạm, {$carb}g tinh bột và {$fat}g chất béo.";
        if ($fat > 25 || $calo > 600) {
            $reply .= " Món này khá giàu năng lượng và chất béo, nên ăn khẩu phần vừa phải và kết hợp thêm rau hoặc món ít dầu để cân bằng hơn.";
        } else {
            $reply .= " Khẩu phần này có thể dùng trong bữa ăn, miễn là bạn cân đối thêm rau, chất xơ và tổng calo trong ngày.";
        }

        return $reply;
    }

    public function standardizeFoods(array $items, string $hint = ''): array
    {
        return array_values(array_filter(array_map(function ($item) {
            if (!is_array($item) || empty($item['name'])) {
                return null;
            }

            $name = trim($item['name']);
            if ($name === '') {
                return null;
            }

            return [
                'name' => $name,
                'gram' => max(1, floatval($item['gram'] ?? 100))
            ];
        }, $items)));
    }

    public function estimateNutritionForFoodItems(array $items, string $dishName = '', array $profile = []): array
    {
        if (!$this->hasOpenAIApiKey()) {
            return [];
        }

        $normalizedItems = $this->standardizeFoods($items, $dishName);
        if (empty($normalizedItems) && trim($dishName) !== '') {
            $normalizedItems = [[
                'name' => trim($dishName),
                'gram' => 100
            ]];
        }

        if (empty($normalizedItems)) {
            return [];
        }

        $itemsText = json_encode($normalizedItems, JSON_UNESCAPED_UNICODE);
        $goal = $profile['muctieu'] ?? 'chưa cập nhật';
        $health = $profile['tinhtrang_suckhoe'] ?? 'chưa cập nhật';
        $diet = $profile['chedo_an'] ?? 'bình thường';

        $prompt = "Bạn là chuyên gia dinh dưỡng cho người dùng Việt Nam. Ước tính dinh dưỡng cho bữa ăn dựa trên dữ liệu AI nhận diện từ ảnh.\n"
            . "Món chính: {$dishName}\n"
            . "Thành phần và khối lượng ước tính: {$itemsText}\n"
            . "Hồ sơ người dùng: mục tiêu={$goal}; sức khỏe={$health}; chế độ ăn={$diet}.\n\n"
            . "Chỉ trả về JSON hợp lệ theo cấu trúc:\n"
            . "{\"items\":[{\"name\":\"\",\"gram\":100,\"calo\":0,\"protein\":0,\"carb\":0,\"fat\":0,\"fiber\":0,\"vitamins\":\"\",\"minerals\":\"\"}]}\n"
            . "Yêu cầu: tên món và thành phần trong trường name phải viết bằng tiếng Việt tự nhiên. Mỗi item phải có ít nhất 4 chỉ số số học: calo, protein, carb, fat. Nếu không chắc, ước tính hợp lý theo khẩu phần Việt Nam. Không giải thích ngoài JSON.";

        $response = $this->callOpenAI([
            'model' => 'gpt-4o',
            'temperature' => 0.2,
            'max_output_tokens' => 1200,
            'response_format' => ['type' => 'json_object'],
            'contents' => [[
                'parts' => [[
                    'text' => $prompt
                ]]
            ]]
        ]);

        $json = $this->extractJson($response);
        if (empty($json['items']) || !is_array($json['items'])) {
            return [];
        }

        $estimated = [];
        foreach ($json['items'] as $idx => $item) {
            if (!is_array($item)) {
                continue;
            }

            $source = $normalizedItems[$idx] ?? [];
            $name = trim((string)($item['name'] ?? ($source['name'] ?? '')));
            if ($name === '') {
                continue;
            }

            $estimated[] = [
                'name' => $name,
                'gram' => max(1, floatval($item['gram'] ?? ($source['gram'] ?? 100))),
                'description' => 'OpenAI estimate',
                'brandOwner' => '',
                'calo' => max(0, round(floatval($item['calo'] ?? 0), 1)),
                'protein' => max(0, round(floatval($item['protein'] ?? 0), 1)),
                'carb' => max(0, round(floatval($item['carb'] ?? 0), 1)),
                'fat' => max(0, round(floatval($item['fat'] ?? 0), 1)),
                'fiber' => max(0, round(floatval($item['fiber'] ?? 0), 1)),
                'vitamins' => trim((string)($item['vitamins'] ?? '')),
                'minerals' => trim((string)($item['minerals'] ?? '')),
                'baseGram' => 100,
                'fdcId' => 0
            ];
        }

        return $estimated;
    }

    public function generateVietnameseNutritionAnalysis(array $totals, array $items, array $profile = [], string $dishName = ''): array
    {
        if (!$this->hasOpenAIApiKey()) {
            return [];
        }

        $compactItems = array_map(function ($item) {
            return [
                'name' => $item['name'] ?? '',
                'gram' => floatval($item['gram'] ?? 0),
                'calo' => floatval($item['calo'] ?? 0),
                'protein' => floatval($item['protein'] ?? 0),
                'carb' => floatval($item['carb'] ?? 0),
                'fat' => floatval($item['fat'] ?? 0),
                'fiber' => floatval($item['fiber'] ?? 0),
                'vitamins' => $item['vitamins'] ?? '',
                'minerals' => $item['minerals'] ?? ''
            ];
        }, $items);

        $payload = json_encode([
            'dish_name' => $dishName,
            'totals' => $totals,
            'items' => $compactItems,
            'profile' => $profile
        ], JSON_UNESCAPED_UNICODE);

        $prompt = "Bạn là NutriAI, chuyên gia dinh dưỡng cho người dùng Việt Nam.\n"
            . "Dựa trên dữ liệu sau, hãy tự phân tích mức độ phù hợp của bữa ăn với hồ sơ sức khỏe, phát hiện điểm dư/thiếu chất và đưa ra gợi ý cá nhân hóa.\n"
            . "Dữ liệu: {$payload}\n\n"
            . "Chỉ trả về JSON hợp lệ theo cấu trúc:\n"
            . "{\"dish_name_vi\":\"\",\"items\":[{\"name_vi\":\"\"}],\"advice\":\"\"}\n"
            . "Yêu cầu bắt buộc:\n"
            . "- dish_name_vi và items.name_vi phải là tiếng Việt tự nhiên, không để tiếng Anh nếu có thể dịch.\n"
            . "- advice viết tiếng Việt, thân thiện, cụ thể cho người dùng Việt Nam, 3 đến 5 câu.\n"
            . "- Nêu rõ món/bữa này phù hợp hay cần điều chỉnh, chất nào dư hoặc thiếu nếu thấy có cơ sở.\n"
            . "- Đưa ra ít nhất một gợi ý thay thế lành mạnh hơn hoặc cách chỉnh khẩu phần.\n"
            . "- Không dùng dấu ngoặc kép bao toàn bộ lời khuyên, không giải thích ngoài JSON.";

        $response = $this->callOpenAI([
            'model' => 'gpt-4o',
            'temperature' => 0.35,
            'max_output_tokens' => 900,
            'response_format' => ['type' => 'json_object'],
            'contents' => [[
                'parts' => [[
                    'text' => $prompt
                ]]
            ]]
        ]);

        $json = $this->extractJson($response);
        if (!is_array($json)) {
            return [];
        }

        return [
            'dish_name_vi' => trim((string)($json['dish_name_vi'] ?? '')),
            'items' => is_array($json['items'] ?? null) ? $json['items'] : [],
            'advice' => trim((string)($json['advice'] ?? ''))
        ];
    }

    private function parseUSDAFoodNutrients(array $foodData, float $gram = 100)
    {
        $nutrients = [
            'calo' => 0,
            'protein' => 0,
            'carb' => 0,
            'fat' => 0,
            'fiber' => 0
        ];

        if (empty($foodData['foodNutrients']) || !is_array($foodData['foodNutrients'])) {
            return $nutrients;
        }

        foreach ($foodData['foodNutrients'] as $item) {
            $name = '';
            $value = 0;

            if (isset($item['nutrient']) && is_array($item['nutrient'])) {
                $name = $item['nutrient']['name'] ?? '';
                $value = $item['amount'] ?? $item['nutrient']['amount'] ?? 0;
            } else {
                $name = $item['nutrientName'] ?? '';
                $value = $item['value'] ?? 0;
            }

            $name = mb_strtolower(trim($name));
            $amount = floatval($value) * $gram / 100;

            if (strpos($name, 'energy') !== false) {
                $nutrients['calo'] = $amount;
            }
            if (strpos($name, 'protein') !== false) {
                $nutrients['protein'] = $amount;
            }
            if (strpos($name, 'carbohydrate') !== false || strpos($name, 'carb') !== false) {
                $nutrients['carb'] = $amount;
            }
            if (strpos($name, 'lipid') !== false || strpos($name, 'fat') !== false) {
                $nutrients['fat'] = $amount;
            }
            if (strpos($name, 'fiber') !== false) {
                $nutrients['fiber'] = $amount;
            }
        }

        return $nutrients;
    }

    // =========================
    // NHẬN DIỆN LOẠI ẢNH
    // =========================
    public function detectImageType($imagePath): string
    {
        // Loại ảnh có thể: medical, food, other
        $keywords = [
            'medical' => ['bệnh', 'bệnh án', 'hồ sơ', 'y tế', 'khám phá', 'xét nghiệm', 'đơn thuốc', 'chẩn đoán', 'viện', 'bác sĩ', 'test', 'blood', 'report', 'medical', 'diagnosis'],
            'food' => ['phở', 'cơm', 'mì', 'salad', 'pizza', 'gà', 'cá', 'thịt', 'rau', 'canh', 'bánh', 'burger', 'sushi', 'food', 'eat', 'meal', 'dish']
        ];

        // Nếu OpenAI hoạt động, dùng nó để phân loại
        $prompt = "Nhìn vào ảnh này, đây có phải là:\n1. Hồ sơ bệnh án / báo cáo y tế / kết quả xét nghiệm\n2. Ảnh thực phẩm / bữa ăn\n3. Hình ảnh khác\n\nChỉ trả lời: medical, food, hoặc other";

        $base64 = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';

        $data = [
            "contents" => [[
                "parts" => [
                    ["text" => $prompt],
                    [
                        "inline_data" => [
                            "mime_type" => $mimeType,
                            "data" => $base64
                        ]
                    ]
                ]
            ]]
        ];

        $response = $this->callOpenAI($data);
        if ($response) {
            $lower = mb_strtolower($response);
            if (mb_strpos($lower, 'medical') !== false || mb_strpos($lower, 'bệnh') !== false) {
                return 'medical';
            }
            if (mb_strpos($lower, 'food') !== false || mb_strpos($lower, 'ăn') !== false) {
                return 'food';
            }
        }

        // Fallback: dùng filename
        $filename = basename($imagePath);
        foreach ($keywords['medical'] as $keyword) {
            if (mb_strpos($filename, $keyword) !== false) {
                return 'medical';
            }
        }
        foreach ($keywords['food'] as $keyword) {
            if (mb_strpos($filename, $keyword) !== false) {
                return 'food';
            }
        }

        return 'other';
    }

    private function commandExists(string $command): bool
    {
        if (stripos(PHP_OS, 'WIN') !== false) {
            $check = shell_exec("where {$command} 2>NUL");
            return !empty(trim($check));
        }

        $check = shell_exec("command -v {$command} 2>/dev/null");
        return !empty(trim($check));
    }

    private function extractTextWithTesseract(string $imagePath): ?string
    {
        if (!$this->commandExists('tesseract')) {
            return null;
        }

        $outputFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ocr_' . uniqid();
        $command = sprintf('tesseract %s %s -l vie 2>&1', escapeshellarg($imagePath), escapeshellarg($outputFile));
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            return null;
        }

        $txtPath = $outputFile . '.txt';
        if (!file_exists($txtPath)) {
            return null;
        }

        $text = file_get_contents($txtPath);
        @unlink($txtPath);
        return $text !== false ? trim($text) : null;
    }

    private function extractTextFromImageUsingOpenAI(string $imagePath): ?string
    {
        $base64 = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';

        $prompt = "Hãy trích xuất toàn bộ văn bản hiển thị trong ảnh này. Chỉ trả về văn bản thuần, không giải thích. Giữ nguyên xuống dòng nếu có.\n";

        $data = [
            "contents" => [[
                "parts" => [
                    ["text" => $prompt],
                    [
                        "inline_data" => [
                            "mime_type" => $mimeType,
                            "data" => $base64
                        ]
                    ]
                ]
            ]]
        ];

        return $this->callOpenAI($data);
    }

    // =========================
    // TRÍCH XUẤT DỮ LIỆU TỪ ẢNH BỆNH ÁN
    // =========================
    public function extractMedicalData($imagePath): array
    {
        $rawText = $this->extractTextWithTesseract($imagePath);
        if (empty($rawText)) {
            $rawText = $this->extractTextFromImageUsingOpenAI($imagePath) ?? '';
        }

        $base64 = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';

        $prompt = "Bạn là chuyên gia y tế. Dựa vào văn bản sau đây, hãy trích xuất các thông tin y tế chính từ hồ sơ bệnh án / báo cáo khám bệnh:\n" .
            "- diagnosis: Chẩn đoán chính hoặc bệnh lý\n" .
            "- treatment: Điều trị, thuốc, hoặc hướng dẫn của bác sĩ\n" .
            "- vitals: Nhịp tim, huyết áp, đường huyết hoặc các chỉ số sinh hiệu khác nếu có\n" .
            "- health_condition: Tình trạng sức khỏe tổng quan\n" .
            "- raw_text: Toàn bộ văn bản đã đọc được từ ảnh\n\n" .
            "Văn bản nguồn:\n" . $rawText . "\n\n" .
            "Trả về JSON hợp lệ với các trường trên. Nếu không có giá trị nào, để trống chuỗi.";

        $data = [
            "contents" => [[
                "parts" => [
                    ["text" => $prompt],
                    [
                        "inline_data" => [
                            "mime_type" => $mimeType,
                            "data" => $base64
                        ]
                    ]
                ]
            ]]
        ];

        $response = $this->callOpenAI($data);
        $result = ['raw_text' => $rawText];

        if ($response) {
            $json = $this->extractJson($response);
            if (is_array($json)) {
                $result['diagnosis'] = $json['diagnosis'] ?? '';
                $result['treatment'] = $json['treatment'] ?? '';
                $result['vitals'] = $json['vitals'] ?? '';
                $result['tinhtrang_suckhoe'] = $json['health_condition'] ?? '';
                $result['findings'] = $json['findings'] ?? '';
                $result['recommendations'] = $json['recommendations'] ?? '';
            }
        }

        return $result;
    }
}
