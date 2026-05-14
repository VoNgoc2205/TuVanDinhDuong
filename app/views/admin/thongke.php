<?php
$newUsers = $newUsers ?? 0;
$newMeals = $newMeals ?? 0;
$newFoods = $newFoods ?? 0;
$totalAI = $totalAI ?? 0;
$activeUsers = $activeUsers ?? 0;
$lockedUsers = $lockedUsers ?? 0;
$mealLabels = $mealLabels ?? [];
$mealData = $mealData ?? [];
$aiLabels = $aiLabels ?? [];
$aiData = $aiData ?? [];
$topFoodLabels = $topFoodLabels ?? [];
$topFoodData = $topFoodData ?? [];
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-600">Thống kê</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Thống kê hệ thống</h1>
            <p class="mt-2 text-sm text-slate-500">Theo dõi tài khoản, bữa ăn, dữ liệu món ăn và lượt dùng AI.</p>
        </div>
        <form method="GET" class="flex flex-col gap-3 sm:flex-row">
            <input type="hidden" name="controller" value="admin">
            <input type="hidden" name="action" value="thongke">
            <select name="type" class="min-h-[48px] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-700 outline-none focus:ring-4 focus:ring-emerald-100">
                <option value="day" <?= ($_GET['type'] ?? 'day') === 'day' ? 'selected' : '' ?>>Ngày</option>
                <option value="month" <?= ($_GET['type'] ?? '') === 'month' ? 'selected' : '' ?>>Tháng</option>
            </select>
            <input type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? date('Y-m-d')) ?>" class="min-h-[48px] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-700 outline-none focus:ring-4 focus:ring-emerald-100">
            <button class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 text-sm font-bold text-white transition hover:bg-emerald-700">
                <i class="fa fa-filter"></i> Lọc
            </button>
        </form>
    </div>

    <?php
    $cards = [
        ['label' => 'Người dùng mới', 'value' => $newUsers, 'icon' => 'fa-user-plus', 'bg' => 'bg-blue-50', 'color' => 'text-blue-600'],
        ['label' => 'Bữa ăn đã lưu', 'value' => $newMeals, 'icon' => 'fa-bowl-food', 'bg' => 'bg-emerald-50', 'color' => 'text-emerald-600'],
        ['label' => 'Món AI phân tích', 'value' => $newFoods, 'icon' => 'fa-camera', 'bg' => 'bg-amber-50', 'color' => 'text-amber-600'],
        ['label' => 'Lượt trả lời AI', 'value' => $totalAI, 'icon' => 'fa-robot', 'bg' => 'bg-violet-50', 'color' => 'text-violet-600'],
        ['label' => 'User hoạt động 30 ngày', 'value' => $activeUsers, 'icon' => 'fa-chart-line', 'bg' => 'bg-cyan-50', 'color' => 'text-cyan-600'],
        ['label' => 'Tài khoản bị khóa', 'value' => $lockedUsers, 'icon' => 'fa-lock', 'bg' => 'bg-red-50', 'color' => 'text-red-600'],
    ];
    ?>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($cards as $card): ?>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-slate-400"><?= $card['label'] ?></p>
                        <p class="mt-2 text-3xl font-black text-slate-900"><?= number_format((float)$card['value']) ?></p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl <?= $card['bg'] ?> <?= $card['color'] ?>">
                        <i class="fa <?= $card['icon'] ?>"></i>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-lg font-black text-slate-900">Bữa ăn trong 7 ngày</h2>
            <p class="mb-4 text-sm text-slate-500">Số lượt người dùng lưu bữa ăn theo ngày.</p>
            <div class="h-[300px]"><canvas id="mealChart"></canvas></div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-lg font-black text-slate-900">Lượt AI trong 7 ngày</h2>
            <p class="mb-4 text-sm text-slate-500">Số phản hồi AI tạo ra theo ngày.</p>
            <div class="h-[300px]"><canvas id="aiChart"></canvas></div>
        </section>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-1 text-lg font-black text-slate-900">Món được phân tích nhiều</h2>
        <p class="mb-4 text-sm text-slate-500">Top món ăn xuất hiện nhiều nhất trong dữ liệu AI.</p>
        <div class="h-[320px]"><canvas id="topFoodChart"></canvas></div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
const gridColor = '#f1f5f9';

new Chart(document.getElementById('mealChart'), {
    type: 'bar',
    data: { labels: <?= json_encode($mealLabels, JSON_UNESCAPED_UNICODE) ?>, datasets: [{ data: <?= json_encode($mealData) ?>, backgroundColor: '#10b981', borderRadius: 10 }] },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { color: gridColor }, beginAtZero: true }, x: { grid: { display: false } } } }
});

new Chart(document.getElementById('aiChart'), {
    type: 'line',
    data: { labels: <?= json_encode($aiLabels, JSON_UNESCAPED_UNICODE) ?>, datasets: [{ data: <?= json_encode($aiData) ?>, borderColor: '#7c3aed', backgroundColor: 'rgba(124,58,237,0.12)', fill: true, tension: 0.35 }] },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { color: gridColor }, beginAtZero: true }, x: { grid: { display: false } } } }
});

new Chart(document.getElementById('topFoodChart'), {
    type: 'bar',
    data: { labels: <?= json_encode($topFoodLabels, JSON_UNESCAPED_UNICODE) ?>, datasets: [{ data: <?= json_encode($topFoodData) ?>, backgroundColor: '#2563eb', borderRadius: 10 }] },
    options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { color: gridColor }, beginAtZero: true }, y: { grid: { display: false } } } }
});
</script>
