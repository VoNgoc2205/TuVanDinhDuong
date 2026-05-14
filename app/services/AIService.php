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
        if (OPENAI_API_KEY === '' || OPENAI_API_KEY === 'YOUR_OPENAI_API_KEY') {
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

    private function detectIntent(string $text): string
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

        if (!is_array($items)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($item) {
            if (!is_array($item) || empty($item['name'])) {
                return null;
            }
            return [
                'name' => trim($item['name']),
                'gram' => max(1, floatval($item['gram'] ?? 100))
            ];
        }, $items)));
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
            if ($nutrition === null) {
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

            foreach ($estimatedItems as $estimated) {
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

                $totals['calo'] += $item['calo'];
                $totals['protein'] += $item['protein'];
                $totals['carb'] += $item['carb'];
                $totals['fat'] += $item['fat'];
                $totals['fiber'] += $item['fiber'];
                $details[] = $item;
            }

            if (empty($estimatedItems)) {
                foreach ($missingItems as $missing) {
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

        // Chỉ phân tích nếu thực sự giống mô tả thực phẩm
        if (!$this->looksLikeFoodDescription($text)) {
            return null;
        }

        $items = $this->extractItemsFromText($text);
        if (empty($items)) {
            $items = $this->parseFoodListFromMessage($text);
        }

        if (empty($items)) {
            $dishName = $this->extractDishNameFromText($text);
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

        $summaryLines = [];
        foreach ($nutrition['items'] as $item) {
            $note = !empty($item['matched']) ? " (matched: {$item['matched']})" : '';
            $summaryLines[] = "- {$item['name']} {$item['gram']}g: {$item['calo']} kcal, {$item['protein']}g đạm, {$item['carb']}g tinh bột, {$item['fat']}g chất béo{$note}";
        }

        $displayDishName = $aiAnalysis['dish_name_vi'] ?? $dishName;
        $reply = "Tôi xác định được" . ($displayDishName ? " món: {$displayDishName}" : " các thành phần sau") . ":\n" . implode("\n", $summaryLines) . "\nTổng: {$nutrition['totals']['calo']} kcal, {$nutrition['totals']['protein']}g đạm, {$nutrition['totals']['carb']}g tinh bột, {$nutrition['totals']['fat']}g chất béo.";
        $advice = trim($aiAnalysis['advice'] ?? '');
        $reply .= "\n\n" . $advice;

        return ['reply' => $reply, 'nutrition' => $nutrition];
    }

    public function chatNutrition($message, $profile = [], $history = [])
    {
        $message = $this->sanitizeText($message);
        $intent = $this->detectIntent($message);

        if ($intent === 'greeting' && !$this->looksLikeFoodDescription($message)) {
            return [
                'reply' => 'Chào bạn! Tôi là trợ lý dinh dưỡng AI. Bạn có thể hỏi về calo, thực đơn hoặc cách ăn uống lành mạnh.',
                'nutrition' => null
            ];
        }

        // Nếu câu hỏi có món ăn/khối lượng/calo thì ưu tiên phân tích chỉ số dinh dưỡng.
        $nutritionData = $this->getNutritionDataFromText($message);
        if ($nutritionData && !empty($nutritionData['items'])) {
            $aiAnalysis = $this->generateVietnameseNutritionAnalysis($nutritionData['totals'], $nutritionData['items'], $profile, '');
            if (!empty($aiAnalysis['items'])) {
                foreach ($nutritionData['items'] as $index => $item) {
                    if (!empty($aiAnalysis['items'][$index]['name_vi'])) {
                        $nutritionData['items'][$index]['name'] = $aiAnalysis['items'][$index]['name_vi'];
                    }
                }
            }

            $summary = "Tôi đã phân tích được các thành phần sau:\n";
            foreach ($nutritionData['items'] as $item) {
                $summary .= "- {$item['name']} ({$item['gram']}g): {$item['calo']} kcal, {$item['protein']}g đạm, {$item['carb']}g tinh bột, {$item['fat']}g chất béo\n";
            }
            $summary .= "Tổng: {$nutritionData['totals']['calo']} kcal, {$nutritionData['totals']['protein']}g đạm, {$nutritionData['totals']['carb']}g tinh bột, {$nutritionData['totals']['fat']}g chất béo.\n";
            $advice = trim($aiAnalysis['advice'] ?? '');
            return [
                'reply' => trim($summary . "\n" . $advice),
                'nutrition' => $nutritionData
            ];
        }

        // Kiểm tra xem đây có phải là câu hỏi chung về dinh dưỡng không
        if ($this->isGenericNutritionQuestion($message)) {
            // Gọi OpenAI trực tiếp
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
                "Hãy trả lời ngắn gọn, dễ hiểu, bằng tiếng Việt.";

            $reply = $this->callOpenAI([
                "contents" => [[
                    "parts" => [["text" => $prompt]]
                ]]
            ]);

            return [
                'reply' => $reply ?: '',
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
            "Hãy trả lời ngắn gọn, dễ hiểu, bằng tiếng Việt. Nếu người dùng hỏi chung về dinh dưỡng, hãy trả lời với lời khuyên hữu ích. Nếu người dùng hỏi về món ăn hoặc calo, trả lời chính xác và cụ thể.";

        $reply = $this->callOpenAI([
            "contents" => [[
                "parts" => [["text" => $prompt]]
            ]]
        ]);

        return [
            'reply' => $reply ?: '',
            'nutrition' => null
        ];
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
