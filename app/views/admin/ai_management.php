<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #F1F5F9;
    }
</style>

<div class="max-w-7xl mx-auto">

    <!-- TITLE -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
            🤖 Quản lý AI hệ thống
        </h2>
        <p class="text-sm text-gray-500 mt-1">Theo dõi hoạt động AI trong hệ thống</p>
    </div>

    <!-- ================= STATS ================= -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">

        <!-- CARD -->
        <div class="bg-gradient-to-r from-green-600 to-green-500 text-white p-5 rounded-2xl shadow relative overflow-hidden">
            <i class="fa fa-chart-line text-4xl opacity-20 absolute right-4 top-4"></i>
            <p class="text-sm">Tổng request</p>
            <h3 class="text-3xl font-bold"><?= number_format($stats['total'] ?? 0) ?></h3>
        </div>

        <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white p-5 rounded-2xl shadow relative overflow-hidden">
            <i class="fa fa-users text-4xl opacity-20 absolute right-4 top-4"></i>
            <p class="text-sm">User sử dụng</p>
            <h3 class="text-3xl font-bold"><?= number_format($stats['users'] ?? 0) ?></h3>
        </div>

        <div class="bg-gradient-to-r from-red-500 to-red-400 text-white p-5 rounded-2xl shadow relative overflow-hidden">
            <i class="fa fa-exclamation-triangle text-4xl opacity-20 absolute right-4 top-4"></i>
            <p class="text-sm">Lỗi AI</p>
            <h3 class="text-3xl font-bold"><?= number_format($stats['errors'] ?? 0) ?></h3>
        </div>

        <div class="bg-gradient-to-r from-orange-500 to-orange-400 text-white p-5 rounded-2xl shadow relative overflow-hidden">
            <i class="fa fa-robot text-4xl opacity-20 absolute right-4 top-4"></i>
            <p class="text-sm">AI hoạt động</p>
            <h3 class="text-3xl font-bold">
                <?= !empty($ai_services)
                    ? count(array_filter($ai_services, fn($a) => $a['trang_thai'] === 'Hoạt động'))
                    : 0 ?>
            </h3>
        </div>

    </div>

    <!-- ================= CHART ================= -->
    <div class="bg-white p-6 rounded-2xl shadow mb-6 max-w-3xl mx-auto">
        <h3 class="font-semibold text-slate-700 mb-3">📊 AI Usage (7 ngày)</h3>
        <canvas id="aiChart" style="height:220px;"></canvas>
    </div>

    <!-- ================= TABLE ================= -->
    <div class="bg-white rounded-2xl shadow overflow-hidden">

        <div class="p-5 border-b">
            <h3 class="font-semibold text-slate-700">Danh sách dịch vụ AI</h3>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-slate-100 text-slate-600 text-xs uppercase">
                    <tr>
                        <th class="text-left p-4">Tên AI</th>
                        <th>Chức năng</th>
                        <th>Trạng thái</th>
                        <th>Số lần dùng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (!empty($ai_services)): ?>
                        <?php foreach ($ai_services as $service): ?>

                            <?php $isActive = $service['trang_thai'] === 'Hoạt động'; ?>

                            <tr class="border-b hover:bg-slate-50 transition">

                                <!-- NAME -->
                                <td class="p-4 flex items-center gap-2 font-medium text-slate-700">
                                    <i class="<?= htmlspecialchars($service['icon']) ?> text-green-600"></i>
                                    <?= htmlspecialchars($service['ten_ai']) ?>
                                </td>

                                <!-- FUNCTION -->
                                <td class="text-center text-slate-600">
                                    <?= htmlspecialchars($service['chuc_nang']) ?>
                                </td>

                                <!-- STATUS -->
                                <td class="text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        <?= $isActive ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                        <?= htmlspecialchars($service['trang_thai']) ?>
                                    </span>
                                </td>

                                <!-- COUNT -->
                                <td class="text-center font-semibold text-slate-700">
                                    <?= number_format($service['real_usage'] ?? 0) ?>
                                </td>

                                <!-- ACTION -->
                                <td class="text-center">

                                    <button
                                        onclick="toggleAI(<?= $service['id'] ?>, '<?= $isActive ? 'Tạm dừng' : 'Hoạt động' ?>')"
                                        class="px-3 py-1 rounded-lg text-xs font-semibold transition
                                        <?= $isActive
                                            ? 'bg-red-500 hover:bg-red-600 text-white'
                                            : 'bg-green-600 hover:bg-green-700 text-white' ?>">

                                        <i class="fa fa-power-off mr-1"></i>
                                        <?= $isActive ? 'Tắt' : 'Bật' ?>

                                    </button>

                                </td>

                            </tr>

                        <?php endforeach; ?>
                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="text-center p-6 text-gray-400">
                                Chưa có dữ liệu AI
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- ================= CHART JS ================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    new Chart(document.getElementById('aiChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($chartLabels ?? []) ?>,
            datasets: [{
                label: 'Requests',
                data: <?= json_encode($chartData ?? []) ?>,
                borderColor: '#16a34a',
                backgroundColor: 'rgba(22,163,74,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    function toggleAI(id, status) {
        if (confirm('Bạn có chắc muốn đổi trạng thái AI?')) {
            window.location.href =
                `index.php?controller=admin&action=updateAIStatus&id=${id}&status=${status}`;
        }
    }
</script>