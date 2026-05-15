<?php

class NutritionTargetHelper
{
    public static function calculateMacroTargets(array $profile): array
    {
        $kcal = intval($profile['kcal_target'] ?? 2000);
        $kcal = $kcal > 0 ? $kcal : 2000;

        $weight = floatval($profile['cannang'] ?? 0);
        if ($weight <= 0 && !empty($profile['medical_analysis'])) {
            $analysis = json_decode((string)$profile['medical_analysis'], true);
            if (is_array($analysis)) {
                $weight = self::extractNumber($analysis['health_metrics']['cannang'] ?? $analysis['health_metrics']['can_nang'] ?? 0);
                if ($weight <= 0) {
                    $weight = self::extractNumber($analysis['chi_so_sinh_hieu']['can_nang'] ?? 0);
                }
            }
        }

        $goal = self::normalizeText((string)($profile['muctieu'] ?? ''));
        $diet = self::normalizeText((string)($profile['chedo_an'] ?? ''));
        $healthText = self::buildHealthText($profile);

        $hasKidney = self::containsAny($healthText, ['than', 'suy than', 'benh than', 'than man', 'kidney', 'renal']);
        $hasDiabetes = self::containsAny($healthText, ['tieu duong', 'dai thao duong', 'diabetes', 'duong huyet']);
        $hasFattyLiver = self::containsAny($healthText, ['gan nhiem mo', 'men gan', 'benh gan', 'fatty liver']);
        $hasDyslipidemia = self::containsAny($healthText, ['mo mau', 'cholesterol', 'triglyceride', 'roi loan lipid']);
        $hasGout = self::containsAny($healthText, ['gout', 'gut', 'acid uric']);
        $hasHypertension = self::containsAny($healthText, ['tang huyet ap', 'cao huyet ap', 'hypertension']);

        $proteinPercent = 15;
        $carbPercent = 60;
        $fatPercent = 25;
        $reason = 'Theo khuyen nghi chung va muc tieu calo hien tai.';

        if (self::containsAny($goal, ['giam can'])) {
            $proteinPercent = 25;
            $carbPercent = 45;
            $fatPercent = 30;
            $reason = 'Uu tien protein cao hon de ho tro giam can va giu khoi co.';
        } elseif (self::containsAny($goal, ['tang can', 'giam mo'])) {
            $proteinPercent = 25;
            $carbPercent = 50;
            $fatPercent = 25;
            $reason = 'Uu tien protein va carb de ho tro tang co, van kiem soat chat beo.';
        } elseif (self::containsAny($goal, ['duy tri'])) {
            $proteinPercent = 20;
            $carbPercent = 55;
            $fatPercent = 25;
            $reason = 'Can bang macro cho muc tieu duy tri suc khoe.';
        }

        if (self::containsAny($diet, ['keto']) && !$hasKidney && !$hasFattyLiver && !$hasDyslipidemia) {
            $proteinPercent = 25;
            $carbPercent = 10;
            $fatPercent = 65;
            $reason = 'Theo che do Keto da chon, carb duoc ha thap va fat cao hon.';
        } elseif (self::containsAny($diet, ['an chay', 'chay'])) {
            $proteinPercent = max($proteinPercent, 18);
            $fatPercent = min($fatPercent, 28);
            $carbPercent = 100 - $proteinPercent - $fatPercent;
            $reason = 'Dieu chinh cho che do an chay, dam bao du dam thuc vat.';
        } elseif (self::containsAny($diet, ['eat clean'])) {
            $proteinPercent = max($proteinPercent, 22);
            $fatPercent = min($fatPercent, 25);
            $carbPercent = 100 - $proteinPercent - $fatPercent;
            $reason = 'Dieu chinh theo Eat Clean: protein vua cao, chat beo vua phai.';
        }

        if ($hasDiabetes) {
            $proteinPercent = 25;
            $carbPercent = 40;
            $fatPercent = 35;
            $reason = 'Co dau hieu tieu duong/duong huyet, giam ti le carb va tang dam-chat beo vua phai.';
        }

        if ($hasFattyLiver || $hasDyslipidemia) {
            $proteinPercent = max($proteinPercent, 22);
            $fatPercent = 22;
            $carbPercent = 100 - $proteinPercent - $fatPercent;
            $reason = 'Co dau hieu gan nhiem mo/mo mau, gioi han chat beo va giu carb o muc vua phai.';
        }

        if ($hasHypertension && !$hasFattyLiver && !$hasDyslipidemia) {
            $fatPercent = min($fatPercent, 25);
            $proteinPercent = max($proteinPercent, 20);
            $carbPercent = 100 - $proteinPercent - $fatPercent;
            $reason = 'Co dau hieu tang huyet ap, giu chat beo vua phai va uu tien can bang dinh duong.';
        }

        if ($hasGout) {
            $proteinPercent = min($proteinPercent, 18);
            $fatPercent = min($fatPercent, 27);
            $carbPercent = 100 - $proteinPercent - $fatPercent;
            $reason = 'Co dau hieu gout/acid uric, khong day protein qua cao.';
        }

        if ($hasKidney) {
            if ($weight > 0) {
                $proteinGram = max(35, round($weight * 0.8));
                $proteinPercent = min(15, max(10, round(($proteinGram * 4 / $kcal) * 100)));
            } else {
                $proteinPercent = 12;
            }
            $fatPercent = min($fatPercent, 28);
            $carbPercent = 100 - $proteinPercent - $fatPercent;
            $reason = 'Co dau hieu benh than, han che protein cao theo huong an toan.';
        } elseif ($weight > 0) {
            $proteinPerKg = self::containsAny($goal, ['giam can', 'tang can', 'giam mo']) ? 1.6 : 1.2;
            if ($hasDiabetes || $hasFattyLiver || $hasDyslipidemia) {
                $proteinPerKg = max($proteinPerKg, 1.3);
            }
            if ($hasGout) {
                $proteinPerKg = min($proteinPerKg, 1.0);
            }

            $proteinByWeightPercent = round((($weight * $proteinPerKg) * 4 / $kcal) * 100);
            $proteinPercent = max($proteinPercent, min(30, $proteinByWeightPercent));
            $carbPercent = 100 - $proteinPercent - $fatPercent;
        }

        $proteinPercent = max(10, min(35, $proteinPercent));
        $fatPercent = max(15, min(65, $fatPercent));
        $carbPercent = max(10, 100 - $proteinPercent - $fatPercent);

        return [
            'protein' => max(1, round(($kcal * $proteinPercent / 100) / 4)),
            'carbs' => max(1, round(($kcal * $carbPercent / 100) / 4)),
            'fat' => max(1, round(($kcal * $fatPercent / 100) / 9)),
            'percent' => [
                'protein' => $proteinPercent,
                'carbs' => $carbPercent,
                'fat' => $fatPercent,
            ],
            'reason' => $reason,
        ];
    }

    private static function buildHealthText(array $profile): string
    {
        $parts = [
            $profile['tinhtrang_suckhoe'] ?? '',
            $profile['medical_analysis'] ?? '',
        ];

        return self::normalizeText(implode(' ', array_map('strval', $parts)));
    }

    private static function normalizeText(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $map = [
            'à'=>'a','á'=>'a','ạ'=>'a','ả'=>'a','ã'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a','ă'=>'a','ằ'=>'a','ắ'=>'a','ặ'=>'a','ẳ'=>'a','ẵ'=>'a',
            'è'=>'e','é'=>'e','ẹ'=>'e','ẻ'=>'e','ẽ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e',
            'ì'=>'i','í'=>'i','ị'=>'i','ỉ'=>'i','ĩ'=>'i',
            'ò'=>'o','ó'=>'o','ọ'=>'o','ỏ'=>'o','õ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o','ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o',
            'ù'=>'u','ú'=>'u','ụ'=>'u','ủ'=>'u','ũ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u',
            'ỳ'=>'y','ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y','đ'=>'d',
        ];
        return strtr($text, $map);
    }

    private static function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && strpos($text, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    private static function extractNumber($value): float
    {
        if (is_numeric($value)) {
            return floatval($value);
        }
        if (preg_match('/\d+(?:[\.,]\d+)?/', (string)$value, $matches)) {
            return floatval(str_replace(',', '.', $matches[0]));
        }
        return 0;
    }
}
