<?php
require_once "app/helpers/SessionHelper.php";

// ===== USER =====
$user = SessionHelper::user() ?? [];
$userName = $user['name'] ?? 'Bạn';

// ===== DATA từ Controller =====
$calo_today     = $calo_today ?? 0;
$meal_today     = $meal_today ?? 0;
$proteinToday   = $proteinToday ?? 0;
$carbsToday     = $carbsToday ?? 0;
$fatToday       = $fatToday ?? 0;
$week           = $week ?? [0,0,0,0,0,0,0];
$ai_count       = $ai_count ?? 0;
$recentMeals    = $recentMeals ?? [];

// ===== TARGET (lấy từ controller) =====
$target_calo = $target_calo ?? 2000;
$percent_completed = ($target_calo > 0) 
    ? (($calo_today / $target_calo) * 100) 
    : 0;

$protein = $proteinTarget ?? 100;
$carbs = $carbsTarget ?? 250;
$fat = $fatTarget ?? 70;
$macroTargetReason = $macroTargetReason ?? 'Dựa trên hồ sơ dinh dưỡng và mục tiêu calo hiện tại.';
?>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #F8FAFC;
    }
</style>

<div class="space-y-10">

    <header class="flex flex-col md:flex-row justify-between items-end gap-6">
        <div class="pl-2">

            <h1 class="text-4xl font-black text-slate-800 tracking-tight leading-tight">
                Chào buổi sáng, <?= htmlspecialchars($userName) ?>! ☀️
            </h1>
            
        </div>

        <a href="/tuvandinhduong/index.php?controller=food&action=input"
            class="bg-[#1e293b] hover:bg-slate-800 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 shadow-lg transition-all">
            <i class="fa fa-plus"></i>
            <span>Thêm bữa ăn mới</span>
        </a>
    </header>



    <div class="grid grid-cols-12 gap-6">

        <!-- ===== CALO ===== -->
        <div class="col-span-12 lg:col-span-4 bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-50 flex flex-col items-center justify-center min-h-[350px]">

            <!-- 🔥 FIX SIZE -->
            <span class="self-start text-slate-500 text-sm font-bold uppercase tracking-widest mb-6">
                Calo hôm nay
            </span>

            <?php
            $percent = min(100, ($calo_today / $target_calo) * 100);
            $dash = 534;
            $offset = $dash - ($dash * $percent / 100);
            
            // Xác định màu sắc dựa trên phần trăm
            $circle_color = '#22C55E'; // Xanh lá (bình thường)
            $status = 'Bình thường';
            
            if ($percent >= 100) {
                $circle_color = '#EF4444'; // Đỏ (vượt quá)
                $status = 'Vượt quá';
            } elseif ($percent >= 80) {
                $circle_color = '#F97316'; // Cam (sắp đạt)
                $status = 'Sắp đạt';
            }
            ?>

            <div class="relative flex items-center justify-center w-48 h-48">
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="96" cy="96" r="85" stroke="#F1F5F9" stroke-width="12" fill="none" />

                    <!-- 🔥 DATA REAL -->
                    <circle cx="96" cy="96" r="85" stroke="<?= $circle_color ?>" stroke-width="14" fill="none"
                        stroke-dasharray="<?= $dash ?>"
                        stroke-dashoffset="<?= $offset ?>"
                        stroke-linecap="round" />
                </svg>

                <div class="absolute text-center">
                    <!-- 🔥 DATA REAL -->
                    <span class="text-5xl font-black text-slate-800">
                        <?= $calo_today ?>
                    </span>
                    <p class="text-slate-400 text-sm font-bold mt-1">kcal</p>
                    <p class="text-[10px] font-bold mt-2" style="color: <?= $circle_color ?>;"><?= $status ?></p>
                </div>
            </div>

            <p class="mt-8 text-slate-400 text-sm font-medium">
                Mục tiêu ngày: <?= $target_calo ?> kcal
            </p>
        </div>

        <div class="col-span-12 lg:col-span-8 bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-50">
            <h3 class="text-slate-500 text-base font-bold uppercase tracking-widest mb-10">
    Chỉ số dinh dưỡng (Macros)
</h3>

<div class="space-y-10">

    <!-- PROTEIN -->
    <div>
        <div class="flex justify-between mb-3 text-lg">
            <span class="font-bold text-slate-700 flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-blue-500 rounded-full"></span> Protein
            </span>
            <span class="text-slate-400 font-bold text-base">
                <?= round($proteinToday ?? 0) ?>g
                <span class="font-medium text-sm">/<?= round($protein ?? 0) ?>g</span>
            </span>
        </div>

        <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
            <?php 
                $pTarget = max(1, $protein ?? 0);
                $pToday  = $proteinToday ?? 0;
                $pPercent = min(100, ($pToday / $pTarget) * 100);
            ?>
            <div class="bg-blue-500 h-full rounded-full"
                 style="width: <?= $pPercent ?>%">
            </div>
        </div>
    </div>

    <!-- CARBS -->
    <div>
        <div class="flex justify-between mb-3 text-lg">
            <span class="font-bold text-slate-700 flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-orange-400 rounded-full"></span> Carbs
            </span>
            <span class="text-slate-400 font-bold text-base">
                <?= round($carbsToday ?? 0) ?>g
                <span class="font-medium text-sm">/<?= round($carbs ?? 0) ?>g</span>
            </span>
        </div>

        <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
            <?php 
                $cTarget = max(1, $carbs ?? 0);
                $cToday  = $carbsToday ?? 0;
                $cPercent = min(100, ($cToday / $cTarget) * 100);
            ?>
            <div class="bg-orange-400 h-full rounded-full"
                 style="width: <?= $cPercent ?>%">
            </div>
        </div>
    </div>

    <!-- FAT -->
    <div>
        <div class="flex justify-between mb-3 text-lg">
            <span class="font-bold text-slate-700 flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-red-500 rounded-full"></span> Chất béo (Fat)
            </span>
            <span class="text-slate-400 font-bold text-base">
                <?= round($fatToday ?? 0) ?>g
                <span class="font-medium text-sm">/<?= round($fat ?? 0) ?>g</span>
            </span>
        </div>

        <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
            <?php 
                $fTarget = max(1, $fat ?? 0);
                $fToday  = $fatToday ?? 0;
                $fPercent = min(100, ($fToday / $fTarget) * 100);
            ?>
            <div class="bg-red-500 h-full rounded-full"
                 style="width: <?= $fPercent ?>%">
            </div>
        </div>
    </div>

</div>
        </div>

        <div class="col-span-12 lg:col-span-7 bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-50">
    
    <div class="flex justify-between items-center mb-8">
        <h3 class="font-black text-slate-800 text-xl">Bữa ăn gần đây</h3>
        <a href="index.php?controller=meal&action=history"
           class="text-green-500 text-sm font-bold flex items-center gap-1">
            Xem tất cả <i class="fa fa-arrow-right"></i>
        </a>
    </div>

    <div class="space-y-5">

        <?php if ($recentMeals && $recentMeals->num_rows > 0): ?>
            <?php while ($meal = $recentMeals->fetch_assoc()): ?>

                <div class="flex items-center justify-between p-4 rounded-[1.5rem] hover:bg-slate-50 transition-all border border-transparent hover:border-slate-100">

                    <div class="flex items-center gap-4">

                        <!-- badge -->
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center font-black text-xs
                            <?= $meal['bua'] == 'Sáng' ? 'bg-orange-50 text-orange-400' : 'bg-blue-50 text-blue-400' ?>">
                            <?= strtoupper($meal['bua']) ?>
                        </div>

                        <div>
                            <!-- tên món -->
                            <h4 class="font-bold text-slate-800 text-lg">
                                <?= $meal['ten_mon'] ?? 'Không có món' ?>
                            </h4>

                            <!-- giờ + calo -->
                            <p class="text-slate-400 text-xs font-semibold uppercase mt-1">
                                <?= date("H:i", strtotime($meal['thoigian'])) ?>
                                • <?= $meal['total_calo'] ?? 0 ?> kcal
                            </p>
                        </div>

                    </div>

                    <!-- link chi tiết -->
                    <a href="index.php?controller=meal&action=detail&date=<?= date('Y-m-d', strtotime($meal['thoigian'])) ?>&bua=<?= $meal['bua'] ?>">
                        <i class="fa fa-chevron-right text-slate-300 text-sm"></i>
                    </a>

                </div>

            <?php endwhile; ?>

        <?php else: ?>
            <div class="text-center text-slate-400 py-10">
                Chưa có dữ liệu bữa ăn
            </div>
        <?php endif; ?>

    </div>
</div>

        <div class="col-span-12 lg:col-span-5 bg-[#22C55E] rounded-[2.5rem] p-10 text-white relative overflow-hidden shadow-xl shadow-green-100">
            <div class="absolute right-[-20px] bottom-[-20px] opacity-10 text-[120px]">
                <i class="fa fa-brain"></i>
            </div>

            <div class="inline-flex items-center gap-2 bg-white/20 px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider mb-6">
                <i class="fa fa-robot"></i> Trải nghiệm AI
            </div>

            <h2 class="text-3xl font-black mb-4 leading-tight">Hỏi AI về bữa ăn, calo và hồ sơ sức khỏe của bạn</h2>
            <p class="text-white/80 text-sm leading-relaxed mb-8">
                Trò chuyện với AI để phân tích món ăn, gợi ý thực đơn, kiểm tra calo và nhận lời khuyên dinh dưỡng theo hồ sơ cá nhân.
            </p>

            <a href="index.php?controller=chatbox&action=index"
               class="inline-flex bg-white text-green-600 px-6 py-3 rounded-xl font-bold text-sm shadow-lg hover:scale-105 transition-transform items-center gap-2">
                Khám phá trợ lý AI <i class="fa fa-wand-magic-sparkles"></i>
            </a>
        </div>

    </div>
</div>
