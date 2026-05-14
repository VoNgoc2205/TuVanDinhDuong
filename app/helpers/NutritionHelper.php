<?php

class NutritionHelper
{
    public static function checkRange($value, $min, $max)
    {
        if ($value < $min) return "low";
        if ($value > $max) return "high";
        return "normal";
    }

    public static function getMessage($value, $min, $max, $name)
    {
        if ($value < $min) return "Bạn đang thiếu $name";
        if ($value > $max) return "Bạn đang dư $name";
        return "$name ở mức hợp lý";
    }

    public static function suggest($status, $name)
    {
        if ($status == "low") return "Nên bổ sung $name";
        if ($status == "high") return "Nên giảm $name";
        return "$name đang ổn";
    }
}