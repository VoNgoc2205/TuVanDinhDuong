<?php
$today = $overview['today'] ?? [];
$month = $overview['month'] ?? [];
$target = max(1, intval($overview['target'] ?? 2000));
$todayCalo = floatval($today['calo'] ?? 0);
$percent = min(100, ($todayCalo / $target) * 100);
$topFoods = array_slice($topFoods ?? [], 0, 5);
$selectedMonth = $selectedMonth ?? date('Y-m');
$selectedMonthLabel = date('m/Y', strtotime($selectedMonth . '-01'));
$selectedYear = intval(date('Y', strtotime($selectedMonth . '-01')));
function formatIntNoComma($value): string
{
    return (string) (int) round((float) $value);
}
$last7Days = $last7Days ?? [
    'labels' => [],
    'calories' => [],
    'protein' => [],
    'carb' => [],
    'fat' => [],
];

if (empty($last7Days['labels']) || empty($last7Days['calories'])) {
    $last7Days['labels'] = [];
    $last7Days['calories'] = [];

    for ($i = 6; $i >= 0; $i--) {
        $last7Days['labels'][] = date('d/m', strtotime("-{$i} days"));
        $last7Days['calories'][] = 0;
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .month-picker-panel {
        transform-origin: top right;
        animation: monthPickerIn 0.16s ease;
    }

    @keyframes monthPickerIn {
        from { opacity: 0; transform: translateY(-6px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
</style>

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
                <span class="text-4xl font-black text-slate-900"><?= formatIntNoComma($todayCalo) ?></span>
                <span class="mb-1 text-sm font-bold text-slate-400">/ <?= formatIntNoComma($target) ?> kcal</span>
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
            <p class="text-sm font-bold text-slate-400">Calo tháng <?= htmlspecialchars($selectedMonthLabel) ?></p>
            <p class="mt-3 text-4xl font-black text-slate-900"><?= formatIntNoComma($month['calo'] ?? 0) ?></p>
            <p class="mt-2 text-sm text-slate-500"><?= intval($month['meals'] ?? 0) ?> bữa · <?= intval($month['items'] ?? 0) ?> món</p>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
            <p class="text-sm font-bold text-slate-400">Lượt AI phản hồi</p>
            <p class="mt-3 text-4xl font-black text-slate-900"><?= formatIntNoComma($overview['ai_messages'] ?? 0) ?></p>
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
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-900">Macro tháng <?= htmlspecialchars($selectedMonthLabel) ?></h2>
                    <p class="text-sm text-slate-500">Tổng protein, carb và chất béo trong tháng</p>
                </div>
                <div class="relative" id="monthPicker">
                    <button type="button" id="monthPickerButton"
                        class="inline-flex min-h-[44px] min-w-[132px] items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 font-black text-slate-700 shadow-sm transition hover:border-emerald-300 hover:text-emerald-700">
                        <i class="fa fa-calendar-days text-slate-400"></i>
                        <span id="monthPickerLabel"><?= htmlspecialchars($selectedMonthLabel) ?></span>
                    </button>

                    <div id="monthPickerPanel" class="month-picker-panel hidden absolute right-0 top-[calc(100%+10px)] z-30 w-[300px] rounded-3xl border border-slate-100 bg-white p-4 shadow-2xl shadow-slate-200/80">
                        <div class="mb-4 flex items-center justify-between">
                            <button type="button" id="monthPickerPrevYear" class="grid h-10 w-10 place-items-center rounded-2xl text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-600">
                                <i class="fa fa-chevron-left"></i>
                            </button>
                            <span id="monthPickerYear" class="text-lg font-black text-slate-900"><?= $selectedYear ?></span>
                            <button type="button" id="monthPickerNextYear" class="grid h-10 w-10 place-items-center rounded-2xl text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-600">
                                <i class="fa fa-chevron-right"></i>
                            </button>
                        </div>
                        <div id="monthPickerGrid" class="grid grid-cols-3 gap-2"></div>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                <?php
                $macros = [
                    ['label' => 'Protein', 'value' => floatval($month['protein'] ?? 0), 'color' => 'bg-blue-500'],
                    ['label' => 'Carb', 'value' => floatval($month['carb'] ?? 0), 'color' => 'bg-amber-500'],
                    ['label' => 'Fat', 'value' => floatval($month['fat'] ?? 0), 'color' => 'bg-rose-500'],
                ];
                $maxMacro = max(1, max(array_column($macros, 'value')));
                $macroScale = max(100, (int)(ceil($maxMacro / 50) * 50));
                ?>
                <p class="text-xs font-bold text-slate-400">Thang đo <?= formatIntNoComma($macroScale) ?>g</p>
                <?php foreach ($macros as $macro): ?>
                    <div>
                        <div class="mb-2 flex justify-between text-sm">
                            <span class="font-bold text-slate-700"><?= $macro['label'] ?></span>
                            <span class="text-slate-500"><?= round($macro['value'], 1) ?>g</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100">
                            <div class="h-full rounded-full <?= $macro['color'] ?>" style="width: <?= min(100, ($macro['value'] / $macroScale) * 100) ?>%"></div>
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
                            <b class="text-emerald-600"><?= formatIntNoComma($meal['calo']) ?> kcal</b>
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
                            <b class="text-slate-700"><?= formatIntNoComma($food['calo']) ?> kcal</b>
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

const monthPicker = document.getElementById('monthPicker');
const monthPickerButton = document.getElementById('monthPickerButton');
const monthPickerPanel = document.getElementById('monthPickerPanel');
const monthPickerGrid = document.getElementById('monthPickerGrid');
const monthPickerYear = document.getElementById('monthPickerYear');
const selectedMonthValue = <?= json_encode($selectedMonth) ?>;
let pickerYear = <?= json_encode($selectedYear) ?>;

function renderMonthPicker() {
    if (!monthPickerGrid || !monthPickerYear) return;
    monthPickerYear.innerText = pickerYear;
    monthPickerGrid.innerHTML = '';

    for (let month = 1; month <= 12; month++) {
        const value = `${pickerYear}-${String(month).padStart(2, '0')}`;
        const button = document.createElement('a');
        button.href = `index.php?controller=stats&action=index&month=${value}`;
        button.className = 'rounded-2xl px-3 py-3 text-center text-sm font-black transition';
        button.innerText = String(month).padStart(2, '0');

        if (value === selectedMonthValue) {
            button.className += ' bg-emerald-600 text-white shadow-lg shadow-emerald-100';
        } else {
            button.className += ' bg-slate-50 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700';
        }

        monthPickerGrid.appendChild(button);
    }
}

monthPickerButton?.addEventListener('click', () => {
    monthPickerPanel?.classList.toggle('hidden');
    renderMonthPicker();
});

document.getElementById('monthPickerPrevYear')?.addEventListener('click', () => {
    pickerYear -= 1;
    renderMonthPicker();
});

document.getElementById('monthPickerNextYear')?.addEventListener('click', () => {
    pickerYear += 1;
    renderMonthPicker();
});

document.addEventListener('click', event => {
    if (!monthPicker?.contains(event.target)) {
        monthPickerPanel?.classList.add('hidden');
    }
});
</script>
