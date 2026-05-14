<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$userId = $_SESSION['user']['id'] ?? 0;


$profile = $profile ?? [];
$currentWeight = $currentWeight ?? 0;
$tdee = $tdee ?? 0;
$protein = $protein ?? 0;
$carbs = $carbs ?? 0;
$fat = $fat ?? 0;

$data = [
    'profile' => $profile,
    'currentWeight' => $currentWeight,
    'muctieu' => $profile['muctieu'] ?? '',
    'tdee' => $tdee,
    'protein' => $protein,
    'carbs' => $carbs,
    'fat' => $fat
];

extract($data);

// ===== DỮ LIỆU NGƯỜI DÙNG =====
$userName = $_SESSION["user"]["name"] ?? "Người dùng";
$chieu_cao = $profile['chieucao'] ?? 0;
$can_nang = isset($currentWeight) ? $currentWeight : 0;
$ti_le_mo = $profile['tilemo'] ?? 0;
$tuoi = $profile['tuoi'] ?? 0;
$gioi_tinh = $profile['gioitinh'] ?? "Chưa cập nhật";

$medicalAnalysis = [];
if (!empty($profile['medical_analysis'])) {
    $medicalAnalysis = json_decode($profile['medical_analysis'], true) ?? [];
}

// ===== TÍNH TOÁN BMI =====
$isPlaceholderFinding = !empty($medicalAnalysis['findings'])
    && mb_strtolower(trim((string)$medicalAnalysis['findings']), 'UTF-8') === 'trich xuat duoc van ban tu anh, vui long xac nhan thu cong ben duoi.';

function formatMedicalAnalysisValue($value): string
{
    if (is_array($value)) {
        $parts = [];
        foreach ($value as $key => $item) {
            if ($item === '' || $item === null || $item === []) {
                continue;
            }
            $parts[] = is_int($key)
                ? formatMedicalAnalysisValue($item)
                : $key . ': ' . formatMedicalAnalysisValue($item);
        }
        return implode("\n", $parts);
    }

    return trim((string)$value);
}

$latestMedicalRecord = null;
if ($userId) {
    require_once "app/models/BenhAnModel.php";
    $benhAnModel = new BenhAnModel();
    $medicalRecords = $benhAnModel->getByType($userId, 'benh_an');
    $latestMedicalRecord = $medicalRecords[0] ?? null;
}

$bmi = 0;
$status = "Chưa có dữ liệu";
$status_color = "text-slate-400";
$bg_status = "bg-slate-100";

if ($chieu_cao > 0 && $can_nang > 0) {
    $bmi = round($can_nang / pow($chieu_cao / 100, 2), 1);
    if ($bmi < 18.5) {
        $status = "Gầy";
        $status_color = "text-blue-500";
        $bg_status = "bg-blue-50";
    } elseif ($bmi < 24.9) {
        $status = "Bình thường";
        $status_color = "text-emerald-500";
        $bg_status = "bg-emerald-50";
    } else {
        $status = "Thừa cân";
        $status_color = "text-orange-500";
        $bg_status = "bg-orange-50";
    }
}
?>

<div class="max-w-full mx-auto py-6 px-4 lg:px-8 space-y-10 animate-fade-in">
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 pb-8 border-b border-slate-100">
        <div>
            <div class="flex items-center gap-4 mb-2">
                <div class="w-1.5 h-10 bg-emerald-500 rounded-full"></div>
                <h1 class="text-4xl font-[1000] text-slate-800 tracking-tight">Hồ sơ dinh dưỡng</h1>
            </div>
            <p class="text-slate-500 text-lg font-medium">Phân tích sinh học và lộ trình sức khỏe cá nhân của bạn.</p>
        </div>

        <button onclick="window.location.href='index.php?controller=nutrition&action=edit&id=<?= $userId ?>'"
            class="group bg-slate-900 hover:bg-black text-white px-8 py-4 rounded-[2rem] font-bold flex items-center gap-3 shadow-2xl shadow-slate-200 transition-all active:scale-95">
            <i class="fa-regular fa-pen-to-square text-lg group-hover:rotate-12 transition-transform"></i>
            <span>Chỉnh sửa hồ sơ</span>
        </button>
    </header>

    <div class="grid grid-cols-12 gap-8">
        <div class="col-span-12 lg:col-span-4 space-y-8">
            <div class="bg-white rounded-[3.5rem] p-10 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.05)] border border-slate-50 flex flex-col items-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-br from-emerald-50/50 to-transparent -z-10"></div>

                <div class="relative group">
                    <div class="absolute -inset-4 bg-emerald-500/10 rounded-[4rem] blur-2xl opacity-0 group-hover:opacity-100 transition duration-500"></div>
                    <?php
                    $user = $_SESSION['user'];
                    $avatar = $user['avatar'] ?? '';
                    $name = $user['name'] ?? 'U';
                    $firstChar = strtoupper(mb_substr($name, 0, 1));
                    ?>

                    <?php if (!empty($avatar) && file_exists("public/uploads/avatar/" . $avatar)): ?>
                        <img src="public/uploads/avatar/<?= $avatar ?>?v=<?= time() ?>"
                            class="w-32 h-32 rounded-full object-cover">
                    <?php else: ?>
                        <div class="w-32 h-32 rounded-full bg-emerald-500 flex items-center justify-center text-white text-4xl font-bold">
                            <?= $firstChar ?>
                        </div>
                    <?php endif; ?>
                    <div class="absolute bottom-2 right-2 w-12 h-12 bg-emerald-500 border-4 border-white rounded-2xl flex items-center justify-center text-white shadow-lg cursor-pointer hover:bg-emerald-600 transition-colors">
                        <i class="fa fa-camera text-sm"></i>
                    </div>
                </div>

                <h2 class="text-3xl font-[900] text-slate-800 mt-8 tracking-tight"><?= $userName ?></h2>
                <div class="mt-3 px-5 py-1.5 bg-emerald-50 text-emerald-600 text-[11px] font-black uppercase tracking-[2px] rounded-full">
                    Thành viên Premium
                </div>

                <div class="grid grid-cols-2 gap-4 w-full mt-6">
                    <div class="bg-slate-50/50 p-6 rounded-[2.5rem] border border-slate-100">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tuổi</p>
                        <p class="text-2xl font-[900] text-slate-800"><?= $tuoi ?> <span class="text-xs text-slate-400">Y/O</span></p>
                    </div>
                    <div class="bg-slate-50/50 p-6 rounded-[2.5rem] border border-slate-100">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Giới tính</p>
                        <p class="text-2xl font-[900] text-slate-800"><?= $gioi_tinh ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-emerald-500 rounded-[3.5rem] p-10 text-white shadow-2xl shadow-emerald-200/50 relative overflow-hidden">
                <div class="absolute right-[-10%] top-[-10%] opacity-10 text-[180px] rotate-12">
                    <i class="fa fa-gauge-high"></i>
                </div>

                <h3 class="text-[11px] font-black uppercase tracking-[3px] opacity-80 mb-8 flex items-center gap-2">
                    <span class="w-2 h-2 bg-white rounded-full animate-ping"></span> Chỉ số BMI hiện tại
                </h3>
                <div class="flex items-center gap-6">
                    <span class="text-7xl font-[1000] tracking-tighter"><?= $bmi ?></span>
                    <div class="bg-white/20 backdrop-blur-md px-5 py-2 rounded-2xl border border-white/30">
                        <span class="text-[12px] font-black tracking-widest uppercase italic"><?= $status ?></span>
                    </div>
                </div>
                <p class="mt-10 text-emerald-50 text-base leading-relaxed font-medium italic">
                    "Dựa trên BMI, bạn đang có trạng thái sức khỏe <?= $status ?>. Hãy tối ưu hóa bữa ăn bằng AI."
                </p>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-8 space-y-8">
            <div class="bg-white rounded-[3.5rem] p-10 shadow-sm border border-slate-100">
                <h3 class="text-slate-400 text-[11px] font-black uppercase tracking-[4px] mb-10 pl-4 border-l-4 border-emerald-500">Thông số sinh học</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="group p-8 bg-slate-50 rounded-[3rem] hover:bg-white hover:shadow-2xl transition-all duration-500 border border-transparent hover:border-emerald-100">
                        <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mb-6 group-hover:rotate-12 transition-transform">
                            <i class="fa fa-arrows-up-down text-2xl"></i>
                        </div>
                        <p class="text-slate-400 text-[11px] font-black uppercase tracking-widest">Chiều cao</p>
                        <p class="text-4xl font-[900] text-slate-800 mt-2"><?= $profile['chieucao'] ?? 0 ?><span class="text-sm font-bold text-slate-400 ml-2">CM</span></p>
                    </div>
                    <div class="group p-8 bg-slate-50 rounded-[3rem] hover:bg-white hover:shadow-2xl transition-all duration-500 border border-transparent hover:border-orange-100">
                        <div class="w-14 h-14 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center mb-6 group-hover:rotate-12 transition-transform">
                            <i class="fa fa-weight-hanging text-2xl"></i>
                        </div>
                        <p class="text-slate-400 text-[11px] font-black uppercase tracking-widest">
                            Cân nặng
                        </p>

                        <p class="text-4xl font-[900] text-slate-800 mt-2">
                            <span id="currentWeight">
                                <?= isset($currentWeight) ? $currentWeight : 0 ?>
                            </span>
                            <span class="text-sm font-bold text-slate-400 ml-2">KG</span>
                        </p>
                    </div>
                    <div class="group p-8 bg-slate-50 rounded-[3rem] hover:bg-white hover:shadow-2xl transition-all duration-500 border border-transparent hover:border-red-100">
                        <div class="w-14 h-14 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center mb-6 group-hover:rotate-12 transition-transform">
                            <i class="fa fa-fire text-2xl"></i>
                        </div>
                        <p class="text-slate-400 text-[11px] font-black uppercase tracking-widest">Tỉ lệ mỡ</p>
                        <p class="text-4xl font-[900] text-slate-800 mt-2"><?= $tilemo ?> %<span class="text-sm font-bold text-slate-400 ml-2">%</span></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[3.5rem] p-12 shadow-sm border border-slate-100 relative overflow-hidden">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 gap-4">
                    <div>
                        <h3 class="text-slate-400 text-[11px] font-black uppercase tracking-[4px] mb-2">Mục tiêu hằng ngày</h3>
                        <p class="text-3xl font-[900] text-slate-800 tracking-tight italic"><?= $muctieu ?></p>
                    </div>
                    <div class="bg-slate-900 text-white px-6 py-3 rounded-2xl text-[11px] font-black uppercase tracking-widest flex items-center gap-3 shadow-xl shadow-slate-200">
                        <i class="fa fa-bullseye text-emerald-400"></i> Đang thực hiện
                    </div>
                </div>

                <div class="space-y-12">
                    <div>
                        <?php
                        $kcal_percent = $kcal_target > 0 ? min(100, ($kcal_today / $kcal_target) * 100) : 0;
                        $bar_color = 'from-emerald-400 to-emerald-600';
                        $text_color = 'text-emerald-600';
                        $status_text = 'Bình thường';

                        if ($kcal_percent >= 100) {
                            $bar_color = 'from-red-400 to-red-600';
                            $text_color = 'text-red-600';
                            $status_text = 'Vượt quá mục tiêu';
                        } elseif ($kcal_percent >= 80) {
                            $bar_color = 'from-orange-400 to-orange-600';
                            $text_color = 'text-orange-600';
                            $status_text = 'Sắp đạt mục tiêu';
                        }
                        ?>
                        <div class="flex justify-between items-end mb-4">
                            <span class="text-slate-500 text-sm font-black uppercase tracking-widest">Dinh dưỡng hôm nay</span>
                            <div class="flex items-center gap-4">
                                <span class="text-3xl font-[1000] text-slate-800"><?= number_format($kcal_today) ?> <span class="text-[12px] font-bold text-slate-400">/</span> <?= number_format($kcal_target) ?> <span class="text-[12px] font-bold text-slate-400">KCAL</span></span>
                                <span class="<?= $text_color ?> text-[11px] font-black uppercase tracking-widest px-3 py-1 bg-slate-50 rounded-full"><?= $status_text ?></span>
                            </div>
                        </div>
                        <div class="w-full bg-slate-100 h-6 rounded-full overflow-hidden shadow-inner p-1.5 border border-slate-200/50">
                            <div class="bg-gradient-to-r <?= $bar_color ?> h-full rounded-full shadow-lg transition-all duration-1000" style="width: <?= $kcal_percent ?>%"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="p-8 bg-blue-50/50 rounded-[2.5rem] border border-blue-100 flex flex-col items-center">
                            <p class="text-blue-600 text-[11px] font-black uppercase tracking-widest mb-2">Protein</p>
                            <p class="text-4xl font-[900] text-slate-800"><?= $protein ?><span class="text-lg text-slate-400 ml-1">g</span></p>
                        </div>
                        <div class="p-8 bg-orange-50/50 rounded-[2.5rem] border border-orange-100 flex flex-col items-center">
                            <p class="text-orange-600 text-[11px] font-black uppercase tracking-widest mb-2">Carbs</p>
                            <p class="text-4xl font-[900] text-slate-800"><?= $carbs ?><span class="text-lg text-slate-400 ml-1">g</span></p>
                        </div>
                        <div class="p-8 bg-red-50/50 rounded-[2.5rem] border border-red-100 flex flex-col items-center">
                            <p class="text-red-600 text-[11px] font-black uppercase tracking-widest mb-2">Chất béo</p>
                            <p class="text-4xl font-[900] text-slate-800"><?= $fat ?><span class="text-lg text-slate-400 ml-1">g</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HEALTH STATUS & EATING STYLE CARD -->
            <div class="bg-white rounded-[3.5rem] p-10 shadow-sm border border-slate-100">
                <h3 class="text-slate-400 text-[11px] font-black uppercase tracking-[4px] mb-10 pl-4 border-l-4 border-emerald-500">Thông tin cá nhân</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="group p-8 bg-gradient-to-br from-blue-50 to-blue-50/50 rounded-[3rem] hover:shadow-lg transition-all duration-500 border border-blue-100">
                        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center mb-4 text-xl">
                            <i class="fa fa-heart-pulse"></i>
                        </div>
                        <p class="text-slate-400 text-[11px] font-black uppercase tracking-widest mb-3">Tình trạng sức khỏe</p>
                        <p class="text-2xl font-[900] text-slate-800"><?= htmlspecialchars($profile['tinhtrang_suckhoe'] ?? 'Chưa cập nhật') ?></p>
                    </div>
                    <div class="group p-8 bg-gradient-to-br from-amber-50 to-amber-50/50 rounded-[3rem] hover:shadow-lg transition-all duration-500 border border-amber-100">
                        <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center mb-4 text-xl">
                            <i class="fa fa-utensils"></i>
                        </div>
                        <p class="text-slate-400 text-[11px] font-black uppercase tracking-widest mb-3">Chế độ ăn</p>
                        <p class="text-2xl font-[900] text-slate-800"><?= htmlspecialchars($profile['chedo_an'] ?? 'Bình thường') ?></p>
                    </div>
                </div>
            </div>

            <!-- WEIGHT TRACKING CARD -->
            <div class="bg-white rounded-[3.5rem] p-10 shadow-sm border border-slate-100">
                <div class="flex justify-between items-center mb-10">
                    <h3 class="text-slate-400 text-[11px] font-black uppercase tracking-[4px] pl-4 border-l-4 border-emerald-500">Theo dõi cân nặng</h3>

                </div>

                <div class="space-y-3">
                    <?php
                    // Show only first 3 weight records
                    $displayWeights = array_slice($weights, 0, 3);
                    $isFirstRecord = true;
                    foreach ($displayWeights as $weight):
                        $dateStr = $weight['ngay'] ?? '';

                        $dateOnly = date('d/m/Y', strtotime($dateStr)); // ngày
                        $timeStr = date('H:i', strtotime($dateStr));    // giờ
                        $weightVal = floatval($weight['can_nang'] ?? 0);
                    ?>
                        <div class="flex justify-between items-center p-6 bg-slate-50 rounded-[2rem] hover:bg-slate-100/50 transition-colors border border-slate-200/50">
                            <div class="flex items-center gap-4">
                                <div class="<?= $isFirstRecord ? 'w-4 h-4 bg-emerald-500 rounded-full' : 'w-3 h-3 bg-slate-300 rounded-full' ?>"></div>
                                <div>
                                    <p class="text-slate-700 font-semibold">
                                        <?= $isFirstRecord ? 'Hôm nay' : $dateOnly ?>
                                    </p>

                                    <p class="text-slate-400 text-[12px] font-medium">
                                        <?= $timeStr ?>
                                    </p>
                                </div>
                            </div>
                            <p class="text-2xl font-[900] text-slate-800"><?= $weightVal ?> <span class="text-sm text-slate-400 ml-1">kg</span></p>
                        </div>
                    <?php
                        $isFirstRecord = false;
                    endforeach;
                    ?>
                </div>

                <?php if (count($weights) > 3): ?>
                    <div class="mt-6 p-4 bg-slate-50 rounded-2xl text-center border border-slate-200/50">
                        <p class="text-[12px] text-slate-500 font-medium">
                            Bạn có <strong><?= count($weights) ?></strong> bản ghi cân nặng •
                            <button onclick="openWeightModal()" class="text-emerald-600 font-bold hover:text-emerald-700">Xem tất cả</button>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- MEDICAL RECORDS CARD - DISPLAY ONLY -->
            <div class="bg-white rounded-[3.5rem] p-10 shadow-sm border border-slate-100">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-slate-400 text-[11px] font-black uppercase tracking-[4px] pl-4 border-l-4 border-purple-500">Hồ sơ bệnh án</h3>
                    <div class="flex items-center gap-2 px-4 py-2 bg-purple-50 rounded-full">
                        <i class="fa fa-sparkles text-purple-600 text-sm"></i>
                        <span class="text-[10px] font-black text-purple-600 uppercase tracking-widest">AI phân tích</span>
                    </div>
                </div>

                <?php if (!empty($profile['medical_record_image']) || !empty($profile['tinhtrang_suckhoe']) || !empty($medicalAnalysis) || !empty($latestMedicalRecord)): ?>
                    <?php $healthMetrics = $medicalAnalysis['health_metrics'] ?? []; ?>
                    <!-- Display Medical Record Summary -->
                    <div class="space-y-6">
                        <?php if (!empty($latestMedicalRecord)): ?>
                            <div class="bg-purple-50 p-6 rounded-[2rem] border border-purple-100">
                                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-4">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-purple-500 mb-2">Bệnh án cập nhật gần nhất</p>
                                        <h4 class="text-lg font-black text-slate-800"><?= htmlspecialchars($latestMedicalRecord['ten_muc'] ?? 'Hồ sơ bệnh án') ?></h4>
                                        <?php if (!empty($latestMedicalRecord['ngay_cap_nhat'])): ?>
                                            <p class="text-xs text-slate-500 mt-1">Cập nhật: <?= date('d/m/Y H:i', strtotime($latestMedicalRecord['ngay_cap_nhat'])) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <a href="index.php?controller=benh_an&action=view&id=<?= intval($latestMedicalRecord['id']) ?>" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-4 py-3 text-sm font-bold text-purple-700 border border-purple-100 hover:bg-purple-100 transition">
                                        <i class="fa fa-eye"></i> Xem chi tiết
                                    </a>
                                </div>
                                <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line line-clamp-6"><?= htmlspecialchars($latestMedicalRecord['gia_tri'] ?? '') ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($profile['medical_record_image']) && file_exists('public/uploads/avatar/' . $profile['medical_record_image'])): ?>
                            <?php $medicalRecordImageSrc = 'public/uploads/avatar/' . htmlspecialchars($profile['medical_record_image'], ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button"
                                class="group relative block w-full rounded-[2rem] overflow-hidden shadow-md border border-slate-100 bg-white text-left cursor-zoom-in"
                                data-full-image="<?= $medicalRecordImageSrc ?>"
                                onclick="openMedicalImageModal(this.dataset.fullImage)">
                                <img src="<?= $medicalRecordImageSrc ?>" alt="Medical record" class="w-full h-auto max-h-96 object-cover transition duration-300 group-hover:scale-[1.01]">
                                <span class="absolute bottom-4 right-4 inline-flex items-center gap-2 rounded-2xl bg-slate-900/85 px-4 py-2 text-xs font-bold text-white shadow-lg backdrop-blur">
                                    <i class="fa fa-up-right-and-down-left-from-center"></i>
                                    Xem ảnh đầy đủ
                                </span>
                            </button>
                        <?php endif; ?>

                       

                        <?php if (!empty($healthMetrics['health_condition'])): ?>
                            <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-200">
                                <?php if (!empty($healthMetrics['health_condition'])): ?>
                                    <div class="mb-4">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Tình trạng chung</p>
                                        <p class="text-slate-700 leading-relaxed"><?= nl2br(htmlspecialchars($healthMetrics['health_condition'])) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ((!empty($medicalAnalysis['findings']) && !$isPlaceholderFinding) || !empty($medicalAnalysis['recommendations']) || !empty($medicalAnalysis['diagnosis']) || !empty($medicalAnalysis['treatment']) || !empty($medicalAnalysis['vitals']) || !empty($medicalAnalysis['ket_qua_xet_nghiem']) || !empty($medicalAnalysis['xquang']) || !empty($medicalAnalysis['lam_sang']) || !empty($medicalAnalysis['loi_khuyen_dinh_duong']) || !empty($healthMetrics['raw_text'])): ?>
                            <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-200">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-4">Phân tích hồ sơ bệnh án AI</p>

                                <?php if (!empty($medicalAnalysis['diagnosis'])): ?>
                                    <div class="mb-4">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Chẩn đoán</p>
                                        <p class="text-slate-700 leading-relaxed"><?= nl2br(htmlspecialchars($medicalAnalysis['diagnosis'])) ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($medicalAnalysis['treatment'])): ?>
                                    <div class="mb-4">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Điều trị / Đơn thuốc</p>
                                        <p class="text-slate-700 leading-relaxed"><?= nl2br(htmlspecialchars($medicalAnalysis['treatment'])) ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($medicalAnalysis['vitals'])): ?>
                                    <div class="mb-4">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Dữ liệu sinh hiệu</p>
                                        <p class="text-slate-700 leading-relaxed"><?= nl2br(htmlspecialchars($medicalAnalysis['vitals'])) ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($medicalAnalysis['findings']) && !$isPlaceholderFinding): ?>
                                    <div class="mb-4">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Kết luận</p>
                                        <p class="text-slate-700 leading-relaxed"><?= nl2br(htmlspecialchars($medicalAnalysis['findings'])) ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($medicalAnalysis['recommendations'])): ?>
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-purple-400 mb-2">Khuyến nghị</p>
                                        <p class="text-slate-700 leading-relaxed"><?= nl2br(htmlspecialchars($medicalAnalysis['recommendations'])) ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php
                                $normalizedRecommendations = trim(preg_replace('/\s+/u', ' ', (string)($medicalAnalysis['recommendations'] ?? '')));
                                $extraMedicalFields = [
                                    'ket_qua_xet_nghiem' => 'Ket qua xet nghiem',
                                    'xquang' => 'X-quang / Chan doan hinh anh',
                                    'lam_sang' => 'Lam sang',
                                    'loi_khuyen_dinh_duong' => 'Loi khuyen dinh duong',
                                ];
                                $hasCoreAIAnalysis = !empty($medicalAnalysis['diagnosis'])
                                    || !empty($medicalAnalysis['treatment'])
                                    || (!empty($medicalAnalysis['findings']) && !$isPlaceholderFinding)
                                    || !empty($medicalAnalysis['recommendations'])
                                    || !empty($medicalAnalysis['vitals'])
                                    || !empty($medicalAnalysis['chan_doan'])
                                    || !empty($medicalAnalysis['huong_dieu_tri'])
                                    || !empty($medicalAnalysis['ket_qua_xet_nghiem'])
                                    || !empty($medicalAnalysis['xquang'])
                                    || !empty($medicalAnalysis['lam_sang'])
                                    || !empty($medicalAnalysis['loi_khuyen_dinh_duong']);
                                if (!empty($healthMetrics['raw_text']) && !$hasCoreAIAnalysis):
                                ?>
                                    <div class="mb-4">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Van ban trich xuat (OCR)</p>
                                        <p class="text-slate-700 leading-relaxed max-h-80 overflow-auto"><?= nl2br(htmlspecialchars($healthMetrics['raw_text'])) ?></p>
                                    </div>
                                <?php
                                endif;
                                foreach ($extraMedicalFields as $fieldKey => $fieldLabel):
                                    $fieldValue = formatMedicalAnalysisValue($medicalAnalysis[$fieldKey] ?? '');
                                    if ($fieldValue === '') {
                                        continue;
                                    }
                                    if (
                                        $fieldKey === 'loi_khuyen_dinh_duong'
                                        && $normalizedRecommendations !== ''
                                        && trim(preg_replace('/\s+/u', ' ', $fieldValue)) === $normalizedRecommendations
                                    ) {
                                        continue;
                                    }
                                ?>
                                    <div class="mb-4">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2"><?= htmlspecialchars($fieldLabel) ?></p>
                                        <p class="text-slate-700 leading-relaxed"><?= nl2br(htmlspecialchars($fieldValue)) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-200 flex justify-between items-center">
                        <a href="index.php?controller=benh_an&action=index" class="flex items-center gap-2 px-6 py-3 bg-slate-600 hover:bg-slate-700 text-white font-bold rounded-2xl transition-colors">
                            <i class="fa fa-list"></i> Xem tất cả
                        </a>
                        
                    </div>
                <?php else: ?>
                    <!-- No Medical Records -->
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center mb-4 mx-auto text-2xl">
                            <i class="fa fa-file-medical"></i>
                        </div>
                        <p class="text-slate-700 font-semibold mb-2">Chưa có hồ sơ bệnh án</p>
                        <p class="text-slate-500 text-sm max-w-xs mx-auto mb-6">Chụp hồ sơ bệnh án của bạn để AI phân tích chi tiết và lưu vào hồ sơ dinh dưỡng</p>
                        <a href="index.php?controller=nutrition&action=edit&id=<?= $userId ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-2xl transition-colors">
                            <i class="fa fa-upload"></i> Tải lên hồ sơ bệnh án
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="medicalImageModal" class="hidden fixed inset-0 z-[60] bg-black/85 backdrop-blur-sm p-4 md:p-8">
    <button type="button" onclick="closeMedicalImageModal()" class="fixed right-4 top-4 md:right-8 md:top-8 z-[61] w-12 h-12 rounded-2xl bg-white/95 hover:bg-white text-slate-800 shadow-xl flex items-center justify-center transition-colors">
        <i class="fa fa-xmark text-xl"></i>
    </button>
    <div class="h-full w-full overflow-auto rounded-[2rem]" onclick="if (event.target === this) closeMedicalImageModal()">
        <img id="medicalImageModalImg" src="" alt="Medical record full image" class="mx-auto h-auto max-w-full rounded-[1.5rem] bg-white shadow-2xl">
    </div>
</div>

<!-- REMOVE Medical Record Modal and related scripts from profile view -->
<div id="weightModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-[3.5rem] max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col shadow-2xl animate-fade-in">
        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-50 to-transparent p-8 border-b border-slate-200">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-[900] text-slate-800">Lịch sử cân nặng</h2>
                    <p class="text-slate-500 text-sm mt-2">Tổng <strong><?= count($weights) ?></strong> bản ghi</p>
                </div>
                <button onclick="closeWeightModal()" class="w-12 h-12 bg-slate-100 hover:bg-slate-200 rounded-2xl flex items-center justify-center transition-colors">
                    <i class="fa fa-xmark text-slate-600 text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="p-8 border-b border-slate-200 bg-slate-50">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-[11px] font-black uppercase tracking-widest text-slate-500 block mb-2">Từ ngày</label>
                    <input type="date" id="filterFromDate" class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-emerald-500" onchange="filterWeights()">
                </div>
                <div>
                    <label class="text-[11px] font-black uppercase tracking-widest text-slate-500 block mb-2">Đến ngày</label>
                    <input type="date" id="filterToDate" class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-emerald-500" onchange="filterWeights()">
                </div>
                <div>
                    <label class="text-[11px] font-black uppercase tracking-widest text-slate-500 block mb-2">Sắp xếp</label>
                    <select id="filterSort" class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-emerald-500" onchange="filterWeights()">
                        <option value="desc">Mới nhất trước</option>
                        <option value="asc">Cũ nhất trước</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Weight List -->
        <div class="flex-1 overflow-y-auto p-8">
            <div id="weightList" class="space-y-4">
                <?php foreach ($weights as $idx => $weight):
                    $dateStr = $weight['ngay'] ?? '';
                    $weightVal = floatval($weight['can_nang'] ?? 0);
                ?>
                    <div class="weightItem flex justify-between items-center p-6 bg-slate-50 rounded-[2rem] hover:bg-slate-100/50 transition-colors border border-slate-200/50"
                        data-date="<?= $dateStr ?>"
                        data-weight="<?= $weightVal ?>"
                        data-timestamp="<?= strtotime($dateStr) ?>">
                        <div class="flex items-center gap-4">
                            <div class="text-center">
                                <p class="text-slate-700 font-semibold text-sm"><?= date('d', strtotime($dateStr)) ?></p>
                                <p class="text-[11px] text-slate-400 font-bold uppercase"><?= date('M Y', strtotime($dateStr)) ?></p>
                            </div>
                            <div class="h-12 w-1 bg-gradient-to-b from-emerald-500 to-emerald-500/30 rounded-full"></div>
                            <div>
                                <p class="text-slate-600 font-semibold"><?= date('l, d/m/Y', strtotime($dateStr)) ?></p>
                                <p class="text-[12px] text-slate-400"><?= date('H:i', strtotime($dateStr)) ?> • Bản ghi số <?= count($weights) - $idx ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-3xl font-[900] text-slate-800"><?= $weightVal ?></p>
                            <p class="text-[11px] text-slate-400 font-bold">kg</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="emptyState" class="hidden text-center py-12">
                <i class="fa fa-inbox text-6xl text-slate-300 mb-4"></i>
                <p class="text-slate-400 text-lg font-semibold">Không tìm thấy bản ghi nào</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-slate-50 border-t border-slate-200 p-8">
            <button onclick="closeWeightModal()" class="w-full bg-slate-900 hover:bg-black text-white font-bold py-4 rounded-[2rem] transition-colors">
                Đóng
            </button>
        </div>
    </div>
</div>

<script>
    function openMedicalImageModal(src) {
        const modal = document.getElementById('medicalImageModal');
        const image = document.getElementById('medicalImageModalImg');

        if (!modal || !image || !src) {
            return;
        }

        image.src = src;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeMedicalImageModal() {
        const modal = document.getElementById('medicalImageModal');
        const image = document.getElementById('medicalImageModalImg');

        if (!modal || !image) {
            return;
        }

        modal.classList.add('hidden');
        image.src = '';
        document.body.style.overflow = 'auto';
    }

    function openWeightModal() {
        document.getElementById('weightModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        // Reset filters
        document.getElementById('filterFromDate').value = '';
        document.getElementById('filterToDate').value = '';
        document.getElementById('filterSort').value = 'desc';
        filterWeights();
    }

    function closeWeightModal() {
        document.getElementById('weightModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function filterWeights() {
        const fromDate = document.getElementById('filterFromDate').value;
        const toDate = document.getElementById('filterToDate').value;
        const sortOrder = document.getElementById('filterSort').value;

        let items = Array.from(document.querySelectorAll('.weightItem'));

        // Filter by date range
        items = items.filter(item => {
            const itemDate = item.getAttribute('data-date');
            if (fromDate && itemDate < fromDate) return false;
            if (toDate && itemDate > toDate) return false;
            return true;
        });

        // Sort
        items.sort((a, b) => {
            const tsA = parseInt(a.getAttribute('data-timestamp'));
            const tsB = parseInt(b.getAttribute('data-timestamp'));
            return sortOrder === 'desc' ? tsB - tsA : tsA - tsB;
        });

        // Display results
        const weightList = document.getElementById('weightList');
        const emptyState = document.getElementById('emptyState');

        if (items.length === 0) {
            weightList.innerHTML = '';
            emptyState.classList.remove('hidden');
        } else {
            weightList.innerHTML = '';
            items.forEach((item, idx) => {
                const clone = item.cloneNode(true);
                weightList.appendChild(clone);
            });
            emptyState.classList.add('hidden');
        }
    }

    // Close modal when clicking outside
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('weightModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeWeightModal();
            }
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMedicalImageModal();
        }
    });
</script>
