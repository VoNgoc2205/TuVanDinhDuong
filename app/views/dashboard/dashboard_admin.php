<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #F1F5F9;
    }
</style>

<?php
$totalUsers  = $totalUsers ?? 0;
$totalMeals  = $totalMeals ?? 0;
$totalFoods  = $totalFoods ?? 0;
$totalAI     = $totalAI ?? 0;

$userLabels  = !empty($userLabels) ? json_encode($userLabels) : json_encode(['T1', 'T2', 'T3', 'T4', 'T5', 'T6']);
$userData    = !empty($userData) ? json_encode($userData) : json_encode([0, 0, 0, 0, 0, 0]);

$aiData = !empty($aiData) ? json_encode($aiData) : json_encode([0, 0, 0, 0, 0, 0, 0]); ?>

<div class="p-6 lg:p-10 space-y-8">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800">
                🛠️ Dashboard Admin
            </h1>
            <p class="text-slate-500 mt-1">
                Theo dõi hệ thống dinh dưỡng
            </p>
        </div>
    </div>


    <!-- STATS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- CARD -->
        <div class="bg-white rounded-2xl p-6 shadow hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm">Người dùng</p>
                    <h2 class="text-3xl font-bold text-blue-600"><?= $totalUsers ?></h2>
                </div>
                <i class="fa fa-users text-blue-500 text-2xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm">Bữa ăn</p>
                    <h2 class="text-3xl font-bold text-green-600"><?= $totalMeals ?></h2>
                </div>
                <i class="fa fa-utensils text-green-500 text-2xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm">Thực phẩm</p>
                    <h2 class="text-3xl font-bold text-orange-500"><?= $totalFoods ?></h2>
                </div>
                <i class="fa fa-apple-alt text-orange-500 text-2xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm">AI xử lý</p>
                    <h2 class="text-3xl font-bold text-purple-600"><?= $totalAI ?></h2>
                </div>
                <i class="fa fa-robot text-purple-500 text-2xl"></i>
            </div>
        </div>

    </div>

    <!-- CHART -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white p-6 rounded-2xl shadow">
            <h3 class="font-bold text-slate-700 mb-4">📈 Tăng trưởng người dùng</h3>
            <div class="h-[300px]">
                <canvas id="userChart"></canvas>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow">
            <h3 class="font-bold text-slate-700 mb-4">🤖 Hiệu suất AI</h3>
            <div class="h-[300px]">
                <canvas id="aiChart"></canvas>
            </div>
        </div>

    </div>

    <!-- TABLE -->
    <div class="bg-white p-6 rounded-2xl shadow">

        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-slate-700">🔥 Top thực phẩm</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="text-slate-500 border-b">
                    <tr>
                        <th class="py-3 text-left">#</th>
                        <th class="py-3 text-left">Tên</th>
                        <th class="py-3 text-center">Lượt dùng</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (!empty($topFoods)): ?>
                        <?php foreach ($topFoods as $index => $f): ?>
                            <tr class="border-b hover:bg-slate-50 transition">
                                <td class="py-3"><?= $index + 1 ?></td>
                                <td class="py-3 font-semibold"><?= htmlspecialchars($f['ten_mon']) ?></td>
                                <td class="py-3 text-center"><?= number_format($f['total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-gray-400 py-4">
                                Chưa có dữ liệu
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>

            </table>
        </div>

    </div>

</div>

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";

    // USER
    new Chart(document.getElementById("userChart"), {
        type: 'line',
        data: {
            labels: <?= $userLabels ?>,
            datasets: [{
                data: <?= $userData ?>,
                borderColor: '#3B82F6',
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(59,130,246,0.1)'
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    display: false
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // AI
    new Chart(document.getElementById("aiChart"), {
        type: 'bar',
        data: {
            labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
            datasets: [{
                data: <?= $aiData ?>,
                backgroundColor: '#22C55E',
                borderRadius: 8
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    display: false
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>