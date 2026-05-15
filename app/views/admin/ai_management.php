<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-600">AI</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Quản lý AI hệ thống</h1>
            <p class="mt-2 text-sm text-slate-500">Theo dõi request, người dùng, lỗi và trạng thái dịch vụ AI.</p>
        </div>
        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-sm font-black text-emerald-700">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Monitoring
        </span>
    </div>

    <?php
    $activeCount = !empty($ai_services) ? count(array_filter($ai_services, fn($a) => ($a['trang_thai'] ?? '') === 'Hoạt động')) : 0;
    $cards = [
        ['label' => 'Tổng request', 'value' => $stats['total'] ?? 0, 'icon' => 'fa-chart-line', 'bg' => 'bg-emerald-50', 'color' => 'text-emerald-600'],
        ['label' => 'User sử dụng', 'value' => $stats['users'] ?? 0, 'icon' => 'fa-users', 'bg' => 'bg-blue-50', 'color' => 'text-blue-600'],
        ['label' => 'Lỗi AI', 'value' => $stats['errors'] ?? 0, 'icon' => 'fa-triangle-exclamation', 'bg' => 'bg-red-50', 'color' => 'text-red-600'],
        ['label' => 'AI hoạt động', 'value' => $activeCount, 'icon' => 'fa-robot', 'bg' => 'bg-violet-50', 'color' => 'text-violet-600'],
    ];
    ?>
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

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900">AI Usage</h2>
                <p class="text-sm text-slate-500">Lượt phản hồi AI trong 7 ngày gần nhất</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500">7 ngày</span>
        </div>
        <div class="h-[260px]"><canvas id="aiChart"></canvas></div>
    </section>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-5">
            <h2 class="font-black text-slate-900">Danh sách dịch vụ AI</h2>
            <p class="mt-1 text-sm text-slate-500">Bật/tắt dịch vụ và theo dõi mức sử dụng.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-black uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-4">Tên AI</th>
                        <th class="px-5 py-4">Chức năng</th>
                        <th class="px-5 py-4">Trạng thái</th>
                        <th class="px-5 py-4 text-right">Lượt dùng</th>
                        <th class="px-5 py-4 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($ai_services)): ?>
                        <?php foreach ($ai_services as $service): ?>
                            <?php $isActive = ($service['trang_thai'] ?? '') === 'Hoạt động'; ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                            <i class="<?= htmlspecialchars($service['icon'] ?? 'fa fa-robot') ?>"></i>
                                        </div>
                                        <span class="font-black text-slate-900"><?= htmlspecialchars($service['ten_ai'] ?? '') ?></span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-slate-600"><?= htmlspecialchars($service['chuc_nang'] ?? '') ?></td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-black <?= $isActive ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' ?>">
                                        <?= htmlspecialchars($service['trang_thai'] ?? '') ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right font-black text-slate-700"><?= number_format($service['real_usage'] ?? 0) ?></td>
                                <td class="px-5 py-4 text-right">
                                    <button onclick="toggleAI(<?= (int)$service['id'] ?>, '<?= $isActive ? 'Tạm dừng' : 'Hoạt động' ?>')" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl px-4 text-xs font-black text-white transition <?= $isActive ? 'bg-red-500 hover:bg-red-600' : 'bg-emerald-600 hover:bg-emerald-700' ?>">
                                        <i class="fa fa-power-off"></i> <?= $isActive ? 'Tắt' : 'Bật' ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="px-5 py-12 text-center text-slate-400">Chưa có dữ liệu AI</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('aiChart'), {
    type: 'line',
    data: { labels: <?= json_encode($chartLabels ?? []) ?>, datasets: [{ data: <?= json_encode($chartData ?? []) ?>, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.12)', fill: true, tension: 0.35 }] },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
});
async function toggleAI(id, status) {
    const ok = typeof appConfirm === 'function'
        ? await appConfirm('Bạn có chắc muốn đổi trạng thái AI?', {
            title: 'Đổi trạng thái AI',
            confirmText: 'Đổi trạng thái',
            cancelText: 'Hủy'
        })
        : false;
    if (ok) {
        window.location.href = `index.php?controller=admin&action=updateAIStatus&id=${id}&status=${encodeURIComponent(status)}`;
    }
}
</script>
