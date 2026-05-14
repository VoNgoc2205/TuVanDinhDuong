<?php

class Lang
{
    public static function get($key)
    {
        $lang = $_SESSION['lang'] ?? 'vi';

        $data = [

            "vi" => [
                "food_analysis" => "Kết quả phân tích AI",
                "calo" => "Năng lượng",
                "protein" => "Chất đạm",
                "carb" => "Tinh bột",
                "fat" => "Chất béo",
                "warning" => "Đánh giá",
                "suggest" => "Gợi ý",
                "save" => "Lưu bữa ăn",
                "favorite" => "Yêu thích",
                "cancel" => "Hủy",
                "retry" => "Phân tích lại"
            ],

            "en" => [
                "food_analysis" => "AI Analysis Result",
                "calo" => "Calories",
                "protein" => "Protein",
                "carb" => "Carbs",
                "fat" => "Fat",
                "warning" => "Evaluation",
                "suggest" => "Suggestion",
                "save" => "Save meal",
                "favorite" => "Favorite",
                "cancel" => "Cancel",
                "retry" => "Retry"
            ]

        ];

        return $data[$lang][$key] ?? $key;
    }
}