<?php
$today = $overview['today'] ?? [];
$month = $overview['month'] ?? [];
$target = max(1, intval($overview['target'] ?? 2000));
$todayCalo = floatval($today['calo'] ?? 0);
$percent = min(100, ($todayCalo / $target) * 100);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-black uppercase tracking-widest text-emerald-600">Thống kê cá nhân</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Tổng quan dinh dưỡng của bạn</h1>
            <p class="mt-2 text-slate-500">Theo dõi calo, macro, bữa ăn và mức sử dụng AI theo thời gian.</p>
        </div>
        <a href="index.php?controller=feedback&action=index"
           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-600">
            <i class="fa fa-comment-dots"></i>
            Gửi phản hồi
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
            <p class="text-sm font-bold text-slate-400">Calo hôm nay</p>
            <div class="mt-3 flex items-end gap-2">
                <span class="text-4xl font-black text-slate-900"><?= number_format($todayCalo) ?></span>
                <span class="mb-1 text-sm font-bold text-slate-400">/ <?= number_format($target) ?> kcal</span>
            </div>
            <div class="mt-4 h-2 rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-emerald-500" style="width: <?= $percent ?>%"></div>
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
            <p class="text-sm font-bold text-slate-400">Bữa ăn hôm nay</p>
            <p class="mt-3 text-4xl font-black text-slate-900"><?= intval($today['meals'] ?? 0) ?></p>
            <p class="mt-2 text-sm text-slate-500">Tổng bữa đã lưu trong ngày</p>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
            <p class="text-sm font-bold text-slate-400">Calo tháng này</p>
            <p class="mt-3 text-4xl font-black text-slate-900"><?= number_format(floatval($month['calo'] ?? 0)) ?></p>
            <p class="mt-2 text-sm text-slate-500"><?= intval($month['meals'] ?? 0) ?> bữa · <?= intval($month['items'] ?? 0) ?> món</p>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
            <p class="text-sm font-bold text-slate-400">Lượt AI phản hồi</p>
            <p class="mt-3 text-4xl font-black text-slate-900"><?= number_format(intval($overview['ai_messages'] ?? 0)) ?></p>
            <p class="mt-2 text-sm text-slate-500">Tin nhắn AI đã hỗ trợ bạn</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100 xl:col-span-2">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-900">Calo 7 ngày gần nhất</h2>
                    <p class="text-sm text-slate-500">So sánh năng lượng nạp vào theo từng ngày</p>
                </div>
            </div>
            <canvas id="calorieChart" height="120"></canvas>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
            <h2 class="text-xl font-black text-slate-900">Macro hôm nay</h2>
            <p class="text-sm text-slate-500">Protein, carb và chất béo</p>
            <div class="mt-6 space-y-4">
                <?php
                $macros = [
                    ['label' => 'Protein', 'value' => floatval($today['protein'] ?? 0), 'color' => 'bg-blue-500'],
                    ['label' => 'Carb', 'value' => floatval($today['carb'] ?? 0), 'color' => 'bg-amber-500'],
                    ['label' => 'Fat', 'value' => floatval($today['fat'] ?? 0), 'color' => 'bg-rose-500'],
                ];
                $maxMacro = max(1, max(array_column($macros, 'value')));
                ?>
                <?php foreach ($macros as $macro): ?>
                    <div>
                        <div class="mb-2 flex justify-between text-sm">
                            <span class="font-bold text-slate-700"><?= $macro['label'] ?></span>
                            <span class="text-slate-500"><?= round($macro['value'], 1) ?>g</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100">
                            <div class="h-full rounded-full <?= $macro['color'] ?>" style="width: <?= min(100, ($macro['value'] / $maxMacro) * 100) ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
            <h2 class="text-xl font-black text-slate-900">Theo bữa ăn trong 30 ngày</h2>
            <div class="mt-5 space-y-3">
                <?php if (!empty($mealBreakdown)): ?>
                    <?php foreach ($mealBreakdown as $meal): ?>
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="font-bold text-slate-800"><?= htmlspecialchars($meal['bua'] ?: 'Bữa ăn') ?></p>
                                <p class="text-xs text-slate-400"><?= intval($meal['items']) ?> món đã lưu</p>
                            </div>
                            <b class="text-emerald-600"><?= number_format(floatval($meal['calo'])) ?> kcal</b>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="rounded-2xl bg-slate-50 p-5 text-center text-sm text-slate-400">Chưa có dữ liệu bữa ăn.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
            <h2 class="text-xl font-black text-slate-900">Món ăn xuất hiện nhiều</h2>
            <div class="mt-5 space-y-3">
                <?php if (!empty($topFoods)): ?>
                    <?php foreach ($topFoods as $food): ?>
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="font-bold text-slate-800"><?= htmlspecialchars($food['ten_mon']) ?></p>
                                <p class="text-xs text-slate-400"><?= intval($food['times']) ?> lần</p>
                            </div>
                            <b class="text-slate-700"><?= number_format(floatval($food['calo'])) ?> kcal</b>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="rounded-2xl bg-slate-50 p-5 text-center text-sm text-slate-400">Chưa có món ăn nào.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
new Chart(document.getElementById('calorieChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($last7Days['labels'], JSON_UNESCAPED_UNICODE) ?>,
        datasets: [{
            label: 'Calo',
            data: <?= json_encode($last7Days['calories']) ?>,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.12)',
            fill: true,
            tension: 0.35,
            pointRadius: 4,
            pointBackgroundColor: '#10b981'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
            x: { grid: { display: false } }
        }
    }
});
</script>
