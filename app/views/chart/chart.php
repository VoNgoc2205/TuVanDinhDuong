<style>
    body {
        background: linear-gradient(135deg, #e8f5e9, #f1f8e9);
        font-family: 'Segoe UI', sans-serif;
    }

    .title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 25px;
        color: #2E7D32;
    }

    .stat-box {
        border-radius: 16px;
        padding: 18px;
        color: white;
        text-align: center;
        font-weight: 600;
        transition: 0.3s;
    }

    .bg-calo {
        background: linear-gradient(135deg, #66bb6a, #43a047);
    }

    .bg-protein {
        background: linear-gradient(135deg, #4CAF50, #2E7D32);
    }

    .bg-carb {
        background: linear-gradient(135deg, #ffd54f, #ffb300);
        color: #333;
    }

    .bg-fat {
        background: linear-gradient(135deg, #ef5350, #e53935);
    }

    .card {
        border-radius: 18px;
        border: none;
        background: #fff;
        transition: 0.3s;
    }
</style>

<h3 class="title">
    <i class="fa fa-chart-column icon-title"></i> Biểu đồ dinh dưỡng
</h3>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-box bg-calo shadow-sm">🔥 calo <h4><?= number_format($total_calo_val) ?> kcal</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box bg-protein shadow-sm">💪 Protein <h4><?= $p_pct ?>%</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box bg-carb shadow-sm">🍞 Carb <h4><?= $c_pct ?>%</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box bg-fat shadow-sm">🥑 Fat <h4><?= $f_pct ?>%</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>📅 calo</h5>
                <select id="filterType" onchange="location.href='index.php?controller=nutrition&action=chart&days='+this.value">
                    <option value="1" <?= $days_filter == 1 ? 'selected' : '' ?>>Ngày</option>
                    <option value="7" <?= $days_filter == 7 ? 'selected' : '' ?>>Tuần</option>
                    <option value="30" <?= $days_filter == 30 ? 'selected' : '' ?>>Tháng</option>
                </select>
            </div>
            <canvas id="caloChart"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm p-3">
            <h5>⚖️ Tỷ lệ dinh dưỡng</h5>
            <canvas id="macroChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Biểu đồ cột Calo
    new Chart(document.getElementById("caloChart"), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'calo',
                data: <?= json_encode($caloData) ?>,
                backgroundColor: '#43A047',
                borderRadius: 10
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

    // Biểu đồ tròn Macros
    new Chart(document.getElementById("macroChart"), {
        type: 'doughnut',
        data: {
            labels: ['Protein', 'Carb', 'Fat'],
            datasets: [{
                data: [<?= $protein ?>, <?= $carb ?>, <?= $fat ?>],
                backgroundColor: ['#4CAF50', '#FFC107', '#F44336']
            }]
        },
        options: {
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>