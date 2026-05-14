<?php
$totalUsers = $totalUsers ?? 0;
$totalMeals = $totalMeals ?? 0;
$totalFoods = $totalFoods ?? 0;
$totalAI = $totalAI ?? 0;

$userLabels = !empty($userLabels) ? json_encode($userLabels) : json_encode(['T1', 'T2', 'T3', 'T4', 'T5', 'T6']);
$userData = !empty($userData) ? json_encode($userData) : json_encode([0, 0, 0, 0, 0, 0]);
$aiData = !empty($aiData) ? json_encode($aiData) : json_encode([0, 0, 0, 0, 0, 0, 0]);

$cards = [
    ['label' => 'Người dùng', 'value' => $totalUsers, 'icon' => 'fa-users', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'],
    ['label' => 'Bữa ăn', 'value' => $totalMeals, 'icon' => 'fa-utensils', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
    ['label' => 'Thực phẩm', 'value' => $totalFoods, 'icon' => 'fa-apple-alt', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50'],
    ['label' => 'Lượt AI', 'value' => $totalAI, 'icon' => 'fa-robot', 'color' => 'text-violet-600', 'bg' => 'bg-violet-50'],
];
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-600">NutriAI Admin</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Tổng quan hệ thống</h1>
            <p class="mt-2 text-sm text-slate-500">Theo dõi người dùng, món ăn, hoạt động AI và dữ liệu nổi bật.</p>
        </div>
        <a href="index.php?controller=admin&action=ai_management" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-600">
            <i class="fa fa-robot"></i> Quản lý AI
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
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
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Tăng trưởng người dùng</h2>
                    <p class="text-sm text-slate-500">Theo chu kỳ gần đây</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600">Users</span>
            </div>
            <div class="h-[300px]"><canvas id="userChart"></canvas></div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Hoạt động AI</h2>
                    <p class="text-sm text-slate-500">Số phản hồi AI trong 7 ngày</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-600">AI</span>
            </div>
            <div class="h-[300px]"><canvas id="aiChart"></canvas></div>
        </section>
    </div>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 p-5">
            <div>
                <h2 class="font-black text-slate-900">Top thực phẩm</h2>
                <p class="text-sm text-slate-500">Các món được ghi nhận nhiều nhất</p>
            </div>
            <a href="index.php?controller=admin&action=food" class="text-sm font-bold text-emerald-600 hover:text-emerald-700">Xem tất cả</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-black uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-4">#</th>
                        <th class="px-5 py-4">Tên món</th>
                        <th class="px-5 py-4 text-right">Lượt dùng</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($topFoods)): ?>
                        <?php foreach ($topFoods as $index => $f): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4 font-bold text-slate-400"><?= $index + 1 ?></td>
                                <td class="px-5 py-4 font-bold text-slate-800"><?= htmlspecialchars($f['ten_mon']) ?></td>
                                <td class="px-5 py-4 text-right font-black text-emerald-600"><?= number_format($f['total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="px-5 py-10 text-center text-slate-400">Chưa có dữ liệu</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
new Chart(document.getElementById("userChart"), {
    type: 'line',
    data: { labels: <?= $userLabels ?>, datasets: [{ data: <?= $userData ?>, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.10)', tension: 0.35, fill: true }] },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
});
new Chart(document.getElementById("aiChart"), {
    type: 'bar',
    data: { labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'], datasets: [{ data: <?= $aiData ?>, backgroundColor: '#10b981', borderRadius: 10 }] },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
});
</script>
