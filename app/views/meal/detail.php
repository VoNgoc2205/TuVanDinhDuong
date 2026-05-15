<?php
require_once "app/config/database.php";
require_once "app/helpers/SessionHelper.php";

SessionHelper::start();

$db = new Database();
$conn = $db->getConnection();
$user = SessionHelper::user();
$user_id = $user['id'] ?? 0;

$date = $_GET['date'] ?? date('Y-m-d');
$bua = $_GET['bua'] ?? 'Sáng';

$stmt = $conn->prepare("
    SELECT ct.*
    FROM meals b
    JOIN meal_items ct ON b.id = ct.meal_id
    WHERE b.user_id = ?
      AND DATE(b.thoigian) = ?
      AND LOWER(b.bua) = LOWER(?)
");
$stmt->bind_param("iss", $user_id, $date, $bua);
$stmt->execute();
$result = $stmt->get_result();

$foods = [];
while ($row = $result->fetch_assoc()) {
    $row['ingredients'] = !empty($row['thanh_phan_json']) ? (json_decode($row['thanh_phan_json'], true) ?: []) : [];
    $row['nutrition_detail'] = !empty($row['nutrition_json']) ? (json_decode($row['nutrition_json'], true) ?: []) : [];
    $foods[] = $row;
}

$mealTotal = 0;
foreach ($foods as $f) {
    $mealTotal += floatval($f['calo'] ?? 0);
}

$dailyStmt = $conn->prepare("
    SELECT COALESCE(SUM(ct.calo), 0) AS daily_total
    FROM meals b
    JOIN meal_items ct ON b.id = ct.meal_id
    WHERE b.user_id = ?
      AND DATE(b.thoigian) = ?
");
$dailyStmt->bind_param("is", $user_id, $date);
$dailyStmt->execute();
$dailyTotal = floatval($dailyStmt->get_result()->fetch_assoc()['daily_total'] ?? 0);

$profileStmt = $conn->prepare("SELECT kcal_target FROM user_profiles WHERE user_id = ? LIMIT 1");
$profileStmt->bind_param("i", $user_id);
$profileStmt->execute();
$dailyTarget = intval($profileStmt->get_result()->fetch_assoc()['kcal_target'] ?? 2000);
$dailyTarget = $dailyTarget > 0 ? $dailyTarget : 2000;

$mealRatios = [
    'sáng' => 0.25,
    'trưa' => 0.35,
    'tối' => 0.30,
    'bữa phụ' => 0.10
];
$mealRatio = $mealRatios[mb_strtolower($bua, 'UTF-8')] ?? 0.30;
$mealTarget = max(1, (int)round($dailyTarget * $mealRatio));
$dailyPercent = min(100, ($dailyTotal / max(1, $dailyTarget)) * 100);
$mealPercent = min(100, ($mealTotal / max(1, $mealTarget)) * 100);
?>

<div>
    <div class="mb-6">
        <a href="index.php?controller=meal&action=history"
           class="inline-flex items-center gap-2 text-emerald-600 font-semibold hover:underline">
            <i class="fa fa-arrow-left"></i>
            Quay lại
        </a>

        <p class="mt-4 text-sm font-black uppercase tracking-widest text-emerald-600">Chi tiết bữa ăn</p>
        <h1 class="text-3xl font-black text-slate-800 mt-2">
            <?= htmlspecialchars($bua) ?> ngày <?= date('d/m/Y', strtotime($date)) ?>
        </h1>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-8">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="font-bold text-slate-700">Danh sách món ăn</h3>
                    <p class="text-xs text-slate-400 mt-1">Nhấn vào từng món để xem đầy đủ dữ liệu đã phân tích.</p>
                </div>

                <div class="divide-y divide-slate-100">
                    <?php if (!empty($foods)): ?>
                        <?php foreach ($foods as $f): ?>
                            <?php
                            $displayGram = floatval($f['gram'] ?? 0);
                            if ($displayGram <= 0 && !empty($f['ingredients'])) {
                                $displayGram = array_sum(array_map(fn($item) => floatval($item['gram'] ?? 0), $f['ingredients']));
                            }
                            ?>
                            <div class="p-5 hover:bg-slate-50 transition cursor-pointer"
                                 onclick="this.querySelector('.meal-extra')?.classList.toggle('hidden')">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <?php if (!empty($f['hinh_anh'])): ?>
                                            <img src="<?= htmlspecialchars($f['hinh_anh']) ?>"
                                                 class="w-14 h-14 rounded-xl object-cover border border-slate-100"
                                                 alt="<?= htmlspecialchars($f['ten_mon']) ?>">
                                        <?php else: ?>
                                            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                                                <i class="fa fa-utensils"></i>
                                            </div>
                                        <?php endif; ?>

                                        <div>
                                            <p class="font-bold text-slate-800"><?= htmlspecialchars($f['ten_mon']) ?></p>
                                            <p class="text-sm text-slate-400">
                                                <?= $displayGram > 0 ? round($displayGram) . 'g' : 'Chưa có khối lượng' ?> &bull;
                                                
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <p class="font-bold text-emerald-600 text-lg"><?= round($f['calo'] ?? 0) ?> kcal</p>
                                        <a href="index.php?controller=meal&action=delete&id=<?= intval($f['id']) ?>"
                                           class="text-red-500 hover:bg-red-50 p-2 rounded-full transition"
                                           onclick="event.stopPropagation(); return confirm('Xóa món này?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="meal-extra hidden mt-4 rounded-2xl bg-slate-50 border border-slate-100 p-4">
                                    <?php if (!empty($f['hinh_anh'])): ?>
                                        <img src="<?= htmlspecialchars($f['hinh_anh']) ?>"
                                             class="w-full max-h-64 object-cover rounded-xl mb-4"
                                             alt="<?= htmlspecialchars($f['ten_mon']) ?>">
                                    <?php endif; ?>

                                    <p class="text-xs font-black uppercase tracking-widest text-slate-400 mb-3">Thông tin đã phân tích</p>
                                    <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm mb-4">
                                        <?php if (!empty($f['nutrition_detail'])): ?>
                                            <?php foreach ($f['nutrition_detail'] as $nutrient): ?>
                                                <div class="rounded-xl bg-white p-3">
                                                    <b><?= round($nutrient['value'] ?? 0, 1) ?></b> <?= htmlspecialchars($nutrient['unit'] ?? '') ?>
                                                    <p class="text-xs text-slate-400 mt-1"><?= htmlspecialchars($nutrient['label'] ?? '') ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="rounded-xl bg-white p-3"><b><?= round($f['calo'] ?? 0) ?></b> kcal</div>
                                            <div class="rounded-xl bg-white p-3"><b><?= round($f['protein'] ?? 0, 1) ?></b>g protein</div>
                                            <div class="rounded-xl bg-white p-3"><b><?= round($f['carb'] ?? 0, 1) ?></b>g carb</div>
                                            <div class="rounded-xl bg-white p-3"><b><?= round($f['fat'] ?? 0, 1) ?></b>g fat</div>
                                            <div class="rounded-xl bg-white p-3"><b><?= round($f['fiber'] ?? 0, 1) ?></b>g chất xơ</div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($f['ingredients'])): ?>
                                        <p class="text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Thành phần</p>
                                        <div class="space-y-2">
                                            <?php foreach ($f['ingredients'] as $ing): ?>
                                                <div class="flex justify-between gap-3 rounded-xl bg-white px-3 py-2 text-sm">
                                                    <span class="font-semibold text-slate-700"><?= htmlspecialchars($ing['name'] ?? '') ?></span>
                                                    <span class="text-slate-500"><?= round($ing['gram'] ?? 0) ?>g &bull; <?= round($ing['calo'] ?? 0) ?> kcal</span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-10 text-center">
                            <p class="text-slate-400 mb-2">Chưa có món nào</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4">
            <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-3xl p-6 text-white shadow-lg">
                <h4 class="text-sm opacity-90">Calo nạp trong ngày</h4>

                <h1 class="text-5xl font-black mt-2"><?= round($dailyTotal) ?></h1>

                <p class="opacity-80 mt-1">
                    / <?= number_format($dailyTarget) ?> kcal mục tiêu ngày
                </p>

                <div class="mt-4 bg-white/30 h-2 rounded-full overflow-hidden">
                    <div class="bg-white h-full" style="width: <?= $dailyPercent ?>%"></div>
                </div>

                <div class="mt-4">
                    <?php if ($dailyPercent < 50): ?>
                        <span class="bg-white/20 px-3 py-1 rounded-full text-sm">Ăn chưa đủ</span>
                    <?php elseif ($dailyPercent < 100): ?>
                        <span class="bg-white/20 px-3 py-1 rounded-full text-sm">Gần đạt mục tiêu ngày</span>
                    <?php else: ?>
                        <span class="bg-white/20 px-3 py-1 rounded-full text-sm">Đã đạt mục tiêu ngày</span>
                    <?php endif; ?>
                </div>

                <div class="mt-5 border-t border-white/20 pt-4 text-sm">
                    <div class="flex justify-between gap-3">
                        <span class="opacity-80">Riêng bữa <?= htmlspecialchars($bua) ?></span>
                        <b><?= round($mealTotal) ?> / <?= $mealTarget ?> kcal</b>
                    </div>
                    <div class="mt-2 bg-white/20 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-white/80 h-full" style="width: <?= $mealPercent ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
