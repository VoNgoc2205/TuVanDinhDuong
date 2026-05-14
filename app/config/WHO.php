<?php

class WHO
{
    public static function getStandard()
    {
        return [
            "calo" => 2000, // 🔥 Lượng calo khuyến nghị mỗi ngày

            "macro" => [
                "protein" => [
                    "min" => 10,
                    "max" => 15,
                    "label" => "Chất đạm"
                ],
                "carb" => [
                    "min" => 55,
                    "max" => 75,
                    "label" => "Tinh bột"
                ],
                "fat" => [
                    "min" => 15,
                    "max" => 30,
                    "label" => "Chất béo"
                ]
            ],

            "bmi" => [
                "underweight" => 18.5, // Thiếu cân
                "normal" => [18.5, 24.9], // Bình thường
                "overweight" => [25, 29.9], // Thừa cân
                "obese" => 30 // Béo phì
            ],

            "water" => [
                "male" => 3.7,   // Nam (lít/ngày)
                "female" => 2.7  // Nữ (lít/ngày)
            ],

            "salt" => 5,   // Muối tối đa (g/ngày)
            "sugar" => 25  // Đường tối đa (g/ngày)
        ];
    }
}