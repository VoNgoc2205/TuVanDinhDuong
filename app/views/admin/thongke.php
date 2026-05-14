<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #F1F5F9;
    }
</style>

<!-- TITLE -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-green-800 flex items-center gap-2">
        📊 Thống kê hệ thống
    </h1>
    <p class="text-gray-500 text-sm mt-1">
        Tổng quan dữ liệu dinh dưỡng & người dùng
    </p>
</div>

<!-- FILTER -->
<div class="flex flex-wrap gap-3 mb-6">

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="hidden" name="controller" value="admin">
        <input type="hidden" name="action" value="thongke">

        <select name="type" class="px-4 py-2 rounded-xl border">
            <option value="day" <?= ($_GET['type'] ?? '') == 'day' ? 'selected' : '' ?>>Ngày</option>
            <option value="month" <?= ($_GET['type'] ?? '') == 'month' ? 'selected' : '' ?>>Tháng</option>
        </select>

        <input type="date" name="date"
            value="<?= $_GET['date'] ?? date('Y-m-d') ?>"
            class="px-4 py-2 rounded-xl border">

        <button class="px-5 py-2 bg-green-600 text-white rounded-xl">
            <i class="fa fa-filter mr-1"></i> Lọc
        </button>
    </form>

</div>

<!-- STAT CARDS -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">

    <!-- USERS -->
    <div class="bg-gradient-to-r from-green-600 to-green-500 text-white p-5 rounded-2xl shadow-lg relative overflow-hidden">
        <i class="fa fa-users text-4xl opacity-20 absolute right-4 top-4"></i>
        <div class="text-sm">Người dùng</div>
        <div class="text-3xl font-bold"><?= $totalUsers ?? 0 ?></div>
    </div>

    <!-- PROFILES -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white p-5 rounded-2xl shadow-lg relative overflow-hidden">
        <i class="fa fa-file-alt text-4xl opacity-20 absolute right-4 top-4"></i>
        <div class="text-sm">Hồ sơ</div>
        <div class="text-3xl font-bold"><?= $totalProfiles ?? 0 ?></div>
    </div>

    <!-- BMI -->
    <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 text-white p-5 rounded-2xl shadow-lg relative overflow-hidden">
        <i class="fa fa-heart text-4xl opacity-20 absolute right-4 top-4"></i>
        <div class="text-sm">BMI TB</div>
        <div class="text-3xl font-bold"><?= number_format($avgBMI ?? 0, 1) ?></div>
    </div>

    <!-- AI -->
    <div class="bg-gradient-to-r from-orange-500 to-orange-400 text-white p-5 rounded-2xl shadow-lg relative overflow-hidden">
        <i class="fa fa-robot text-4xl opacity-20 absolute right-4 top-4"></i>
        <div class="text-sm">AI dùng</div>
        <div class="text-3xl font-bold"><?= $totalAI ?? 0 ?></div>
    </div>

</div>

<!-- CHARTS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">

    <!-- PIE -->
    <div class="bg-white p-5 rounded-2xl shadow-md">
        <h2 class="font-semibold text-gray-700 mb-3">🎯 Phân bố mục tiêu</h2>
        <canvas id="pieChart"></canvas>
    </div>

    <!-- LINE -->
    <div class="bg-white p-5 rounded-2xl shadow-md">
        <h2 class="font-semibold text-gray-700 mb-3">📈 Tăng trưởng user</h2>
        <canvas id="lineChart"></canvas>
    </div>

    <!-- BAR -->
    <div class="bg-white p-5 rounded-2xl shadow-md">
        <h2 class="font-semibold text-gray-700 mb-3">🥗 Dinh dưỡng TB</h2>
        <canvas id="barChart"></canvas>
    </div>

</div>

</div>

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // PIE
    new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: {
            labels: <?= json_encode($goalLabels ?? []) ?>,
            datasets: [{
                data: <?= json_encode($goalValues ?? []) ?>,
                backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444']
            }]
        },
        options: {
            responsive: true
        }
    });

    // LINE
    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($userLabels ?? []) ?>,
            datasets: [{
                label: 'Users',
                data: <?= json_encode($userData ?? []) ?>,
                borderColor: '#16a34a',
                backgroundColor: 'rgba(22,163,74,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true
        }
    });

    // BAR
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: ['Protein', 'Carb', 'Fat'],
            datasets: [{
                data: <?= json_encode($macroData ?? [0, 0, 0]) ?>,
                backgroundColor: ['#3b82f6', '#22c55e', '#f97316']
            }]
        },
        options: {
            responsive: true
        }
    });
</script>