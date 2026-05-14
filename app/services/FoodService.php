<?php
require_once "app/config/api.php";

class FoodService
{
    public function searchUSDAFood($foodName, $limit = 3)
    {
        $results = $this->searchUSDAByName($foodName, $limit);
        if (!empty($results)) {
            return $results;
        }

        $translated = $this->translateToEnglish($foodName);
        if ($translated !== $foodName) {
            $results = $this->searchUSDAByName($translated, $limit);
            if (!empty($results)) {
                return $results;
            }
        }

        $fallback = $this->getFallbackNames($foodName);
        foreach ($fallback as $name) {
            $results = $this->searchUSDAByName($name, $limit);
            if (!empty($results)) {
                return $results;
            }
        }

        return [];
    }

    private function searchUSDAByName($foodName, $limit = 3)
    {
        $url = "https://api.nal.usda.gov/fdc/v1/foods/search?query="
            . urlencode($foodName) . "&pageSize=" . intval($limit) . "&api_key=" . USDA_API_KEY;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 8,
        ]);
        $json = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($errno || !$json) {
            return [];
        }

        $data = json_decode($json, true);
        if (empty($data['foods']) || !is_array($data['foods'])) {
            return [];
        }

        $results = [];
        foreach ($data['foods'] as $food) {
            $nutrients = $food['foodNutrients'] ?? [];
            $item = [
                'description' => $food['description'] ?? $food['lowercaseDescription'] ?? '',
                'brandOwner' => $food['brandOwner'] ?? '',
                'servingSize' => $food['servingSize'] ?? 100,
                'servingSizeUnit' => $food['servingSizeUnit'] ?? 'g',
                'calo' => 0,
                'protein' => 0,
                'carb' => 0,
                'fat' => 0,
                'fiber' => 0,
                'dataType' => $food['dataType'] ?? '',
                'fdcId' => $food['fdcId'] ?? 0,
            ];

            foreach ($nutrients as $n) {
                $name = $n['nutrientName'] ?? '';
                $value = $n['value'] ?? 0;

                if ($name === 'Energy' || $name === 'Energy (kcal)') {
                    $item['calo'] = $value;
                }
                if ($name === 'Protein') {
                    $item['protein'] = $value;
                }
                if ($name === 'Carbohydrate, by difference') {
                    $item['carb'] = $value;
                }
                if ($name === 'Total lipid (fat)') {
                    $item['fat'] = $value;
                }
                if ($name === 'Fiber, total dietary') {
                    $item['fiber'] = $value;
                }
            }

            $item['baseGram'] = 100;
            if ($item['servingSizeUnit'] === 'g' && $item['servingSize'] > 0) {
                $item['baseGram'] = $item['servingSize'];
            }

            $results[] = $item;
        }

        return $results;
    }

    public function pickBestUSDAResult($results, $query)
    {
        $query = trim(mb_strtolower($query));
        $best = null;
        $bestScore = -1;

        foreach ($results as $item) {
            $desc = trim(mb_strtolower($item['description'] ?? ''));
            if ($desc === '') {
                continue;
            }

            $score = 0;
            if ($desc === $query) {
                $score += 50;
            }
            if (mb_strpos($desc, $query) !== false) {
                $score += 30;
            }

            similar_text($query, $desc, $percent);
            $score += intval($percent);

            if (!empty($item['brandOwner']) && mb_strpos(mb_strtolower($item['brandOwner']), $query) !== false) {
                $score += 10;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $item;
            }
        }

        return $best;
    }

    public function scaleNutrition(array $item, float $gram)
    {
        $baseGram = $item['baseGram'] ?? 100;
        if ($baseGram <= 0) {
            $baseGram = 100;
        }

        $factor = $gram / $baseGram;

        return [
            'calo' => round($item['calo'] * $factor, 1),
            'protein' => round($item['protein'] * $factor, 1),
            'carb' => round($item['carb'] * $factor, 1),
            'fat' => round($item['fat'] * $factor, 1),
            'fiber' => round(($item['fiber'] ?? 0) * $factor, 1),
            'description' => $item['description'] ?? '',
            'brandOwner' => $item['brandOwner'] ?? '',
            'baseGram' => $baseGram,
            'fdcId' => $item['fdcId'] ?? 0,
        ];
    }

    public function getNutritionFromUSDA($foodName, $gram = 100)
    {
        $results = $this->searchUSDAFood($foodName, 3);
        if (empty($results)) {
            return null;
        }

        $best = $this->pickBestUSDAResult($results, $foodName);
        if (!$best) {
            return null;
        }

        return $this->scaleNutrition($best, $gram);
    }

    public function translateToEnglish($text)
    {
        $map = [
            "bánh canh tôm" => "shrimp noodle soup",
            "bánh canh cua" => "crab noodle soup",
            "bánh canh" => "noodle soup",
            "phở bò" => "beef pho",
            "phở gà" => "chicken pho",
            "cơm gà" => "chicken rice",
            "cơm trắng" => "white rice",
            "cơm" => "rice",
            "bún bò" => "beef noodle soup",
            "tôm luộc" => "boiled shrimp",
            "tôm" => "shrimp",
            "mực" => "squid",
            "bánh mì" => "bread",
            "gà" => "chicken",
            "nấm" => "mushroom",
            "cá" => "fish",
            "thịt" => "meat",
            "rau" => "vegetables",
            "đậu" => "beans",
            "xôi" => "sticky rice",
            "cháo" => "porridge",
            "canh" => "soup",
            "mì" => "noodles",
            "trứng" => "egg",
            "thăn" => "loin",
            "bò" => "beef"
        ];

        $key = strtolower(trim($text));
        if (isset($map[$key])) {
            return $map[$key];
        }

        // Thử thay thế các từ tiếng Việt chung bằng tiếng Anh
        foreach ($map as $vi => $en) {
            if (mb_strpos($key, $vi) !== false) {
                $key = str_replace($vi, $en, $key);
            }
        }

        return trim($key) === '' ? $text : $key;
    }

    private function getFallbackNames($text)
    {
        $key = trim(mb_strtolower($text));
        $fallbacks = [];

        if (mb_strpos($key, 'gà') !== false) {
            $fallbacks[] = 'chicken';
            $fallbacks[] = 'roast chicken';
        }
        if (mb_strpos($key, 'nấm') !== false) {
            $fallbacks[] = 'mushroom';
            $fallbacks[] = 'mushrooms';
        }
        if (mb_strpos($key, 'cá') !== false) {
            $fallbacks[] = 'fish';
        }
        if (mb_strpos($key, 'mực') !== false) {
            $fallbacks[] = 'squid';
        }
        if (mb_strpos($key, 'cơm') !== false) {
            $fallbacks[] = 'rice';
            $fallbacks[] = 'white rice';
        }
        if (mb_strpos($key, 'phở') !== false) {
            $fallbacks[] = 'pho';
        }
        if (mb_strpos($key, 'bánh mì') !== false) {
            $fallbacks[] = 'bread';
        }
        if (mb_strpos($key, 'bánh canh') !== false) {
            $fallbacks[] = 'noodle soup';
            $fallbacks[] = 'shrimp noodle soup';
        }
        if (mb_strpos($key, 'canh') !== false) {
            $fallbacks[] = 'soup';
        }
        if (mb_strpos($key, 'rau') !== false) {
            $fallbacks[] = 'vegetables';
        }
        if (mb_strpos($key, 'tôm') !== false) {
            $fallbacks[] = 'shrimp';
        }
        if (mb_strpos($key, 'thịt') !== false) {
            $fallbacks[] = 'meat';
        }
        if (mb_strpos($key, 'mì') !== false) {
            $fallbacks[] = 'noodles';
        }

        return array_unique($fallbacks);
    }
}
