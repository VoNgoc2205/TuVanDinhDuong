<?php
$user = $_SESSION['user'] ?? [];

$name = $user['name'] ?? 'User';
$avatar = $user['avatar'] ?? '';

$firstChar = strtoupper(mb_substr($name, 0, 1));

$medicalAnalysis = [];
$medicalAnalysisJson = '';
$healthMetrics = [];
if (!empty($profile['medical_analysis'])) {
    $medicalAnalysis = json_decode($profile['medical_analysis'], true) ?? [];
    $medicalAnalysisJson = htmlspecialchars(json_encode($medicalAnalysis, JSON_UNESCAPED_UNICODE));
    $healthMetrics = $medicalAnalysis['health_metrics'] ?? [];
}
?>

<div class="max-w-[1600px] mx-auto px-4 lg:px-8 py-6 space-y-8">

    <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/50 p-6 rounded-[2rem] backdrop-blur-md">
        <div>
            <h1 class="text-4xl font-black text-slate-800 tracking-tight">Thiết lập chỉ số</h1>

            <p class="text-slate-500 mt-1">Cập nhật thông số sinh học và mục tiêu để AI tối ưu thực đơn.</p>
        </div>
        <div class="col-span-12 lg:col-span-4 space-y-8">

            <div class="bg-white rounded-[3rem] p-10 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.05)] border border-slate-100 flex flex-col items-center relative overflow-hidden">

                
                <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-br from-emerald-50/60 to-transparent"></div>



                <!-- AVATAR -->
                <div class="relative group mt-2">

                    <!-- glow effect -->
                    <div class="absolute -inset-3 bg-emerald-500/20 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition duration-500"></div>

                    <?php if (!empty($avatar) && file_exists("public/uploads/avatar/" . $avatar)): ?>
                        <img src="public/uploads/avatar/<?= $avatar ?>?v=<?= time() ?>"
                            class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-xl">
                    <?php else: ?>
                        <div class="w-32 h-32 rounded-full bg-emerald-500 flex items-center justify-center text-white text-4xl font-bold shadow-xl">
                            <?= $firstChar ?>
                        </div>
                    <?php endif; ?>

                    <!-- camera icon -->
                    <div class="absolute bottom-2 right-2 w-11 h-11 bg-emerald-500 border-4 border-white rounded-xl flex items-center justify-center text-white shadow-md cursor-pointer hover:bg-emerald-600 transition">
                        <i class="fa fa-camera text-sm"></i>
                    </div>

                </div>

                <!-- NAME -->
                <h2 class="text-2xl font-black text-slate-800 mt-6 tracking-tight text-center">
                    <?= htmlspecialchars($name) ?>
                </h2>

                <!-- BADGE -->
                <div class="mt-2 px-4 py-1 bg-emerald-100 text-emerald-600 text-[11px] font-bold uppercase tracking-wider rounded-full">
                    Thành viên Premium
                </div>

            </div>

        </div>

    </header>

    <form id="edit-profile-form"
        method="POST"
        action="index.php?controller=nutrition&action=update"
        class="grid grid-cols-12 gap-8">
        <input type="hidden" name="id" value="<?= $profile['id'] ?? '' ?>">

        <div class="col-span-12 lg:col-span-7 space-y-8">
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                <div class="flex items-center gap-3 mb-8">
                    <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center font-black text-sm">1</span>
                    <h3 class="text-slate-400 text-sm font-black uppercase tracking-[3px]">Thông số sinh học</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="group space-y-2">
                        <label class="text-sm font-bold text-slate-600 ml-1">Chiều cao</label>
                        <div class="relative">
                            <input type="number" step="0.1" name="chieucao" value="<?= $profile['chieucao'] ?? '' ?>" required
                                class="w-full bg-slate-50 border-2 border-transparent group-focus-within:border-blue-500 group-focus-within:bg-white p-4 rounded-2xl font-bold transition-all outline-none">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">CM</span>
                        </div>
                    </div>

                    <div class="group space-y-2">
                        <label class="text-sm font-bold text-slate-600 ml-1">Cân nặng hiện tại</label>
                        <div class="relative">
                            <input type="number" step="0.1" name="cannang"
                                value="<?= htmlspecialchars($profile['cannang'] ?? '') ?>"
                                id="inputWeight"
                                class="w-full bg-slate-50 p-4 rounded-2xl font-bold outline-none">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">KG</span>
                        </div>
                    </div>

                    <!-- Tuổi -->
                    <div class="group space-y-2">
                        <label class="text-sm font-bold text-slate-600 ml-1">Tuổi</label>
                        <input
                            type="number"
                            name="tuoi"
                            value="<?= htmlspecialchars($profile['tuoi'] ?? '') ?>"
                            class="w-full bg-slate-50 border-2 border-transparent focus:border-blue-500 p-4 rounded-2xl font-bold"
                            required>
                    </div>

                    <!-- Giới tính -->
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-600 ml-1">Giới tính</label>

                        <div class="flex p-1 bg-slate-50 rounded-2xl h-[58px]">

                            <label class="flex-1 flex items-center justify-center rounded-xl cursor-pointer font-bold text-sm transition-all has-[:checked]:bg-white has-[:checked]:shadow-sm has-[:checked]:text-blue-600">
                                <input type="radio" name="gioitinh" value="Nam"
                                    <?= ($profile['gioitinh'] ?? '') == 'Nam' ? 'checked' : '' ?> class="hidden">
                                Nam
                            </label>

                            <label class="flex-1 flex items-center justify-center rounded-xl cursor-pointer font-bold text-sm transition-all has-[:checked]:bg-white has-[:checked]:shadow-sm has-[:checked]:text-pink-600">
                                <input type="radio" name="gioitinh" value="Nữ"
                                    <?= ($profile['gioitinh'] ?? '') == 'Nữ' ? 'checked' : '' ?> class="hidden">
                                Nữ
                            </label>

                        </div>
                    </div>

                </div>
            </div>
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                <div class="flex items-center gap-3 mb-8">
                    <span class="w-8 h-8 bg-green-100 text-green-600 rounded-lg flex items-center justify-center font-black text-sm">2</span>
                    <h3 class="text-slate-400 text-sm font-black uppercase tracking-[3px]">Tình trạng sức khỏe & Ăn uống</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-bold text-slate-700">Tiền sử bệnh lý hoặc dùng thuốc</label>
                        <textarea name="tinhtrang_suckhoe" rows="2"
                            class="w-full bg-slate-50 border-2 border-transparent focus:border-green-500 focus:bg-white p-4 rounded-2xl font-bold transition-all outline-none"
                            placeholder="Ghi rõ dị ứng (hải sản, đậu phộng...) hoặc bệnh mãn tính"><?= $profile['tinhtrang_suckhoe'] ?? '' ?></textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700">Trường hợp ăn uống</label>

                        <?php $chedo = $profile['chedo_an'] ?? 'Bình thường'; ?>

                        <select name="chedo_an"
                            class="w-full bg-slate-50 p-4 rounded-2xl font-bold outline-none border-2 border-transparent focus:border-green-500 transition-all appearance-none">

                            <option value="Bình thường" <?= $chedo == 'Bình thường' ? 'selected' : '' ?>>
                                Bình thường
                            </option>

                            <option value="An chay" <?= $chedo == 'An chay' ? 'selected' : '' ?>>
                                Ăn chay
                            </option>

                            <option value="Keto" <?= $chedo == 'Keto' ? 'selected' : '' ?>>
                                Keto (Low Carbs)
                            </option>

                            <option value="Eat Clean" <?= $chedo == 'Eat Clean' ? 'selected' : '' ?>>
                                Eat Clean
                            </option>

                        </select>
                    </div>
                    <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100 flex items-center gap-4">
                        <i class="fa fa-info-circle text-blue-500 text-xl"></i>
                        <p class="text-xs text-blue-700 leading-relaxed">AI sẽ dựa vào thông tin này để loại bỏ các nguyên liệu không phù hợp khỏi thực đơn của bạn..</p>
                    </div>
                </div>
            </div>
            <!-- MEDICAL RECORD UPLOAD SECTION -->
            <div class="bg-white rounded-[3.5rem] p-10 shadow-sm border border-slate-100">
                <div class="flex items-center gap-3 mb-8">
                    <span class="w-8 h-8 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center font-black text-sm">3</span>
                    <h3 class="text-slate-400 text-sm font-black uppercase tracking-[3px]">Hồ sơ bệnh án (AI phân tích)</h3>
                </div>

                <!-- Upload Zone -->
                <div class="flex flex-col items-center justify-center p-8 bg-gradient-to-br from-purple-50 to-purple-50/30 rounded-[2rem] border-2 border-dashed border-purple-200 hover:border-purple-400 transition-colors cursor-pointer"
                    id="medicalRecordDropZone"
                    onclick="document.getElementById('medicalRecordInput').click()">
                    <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center mb-3 text-xl">
                        <i class="fa fa-file-medical"></i>
                    </div>
                    <p class="text-slate-800 font-bold text-sm text-center mb-1">Chụp hồ sơ bệnh án của bạn</p>
                    <p class="text-slate-500 text-xs text-center max-w-xs mb-3">Tải lên hình ảnh để AI phân tích chi tiết</p>
                    <input type="file" id="medicalRecordInput" class="hidden" accept="image/*,.pdf" onchange="handleMedicalRecordUpload(this)">
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-widest">JPG PNG Tối đa 10MB</p>
                </div>

                <div id="medicalImagePreviewWrap" class="hidden mt-6 overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-50">
                    <img
                        id="medicalImagePreview"
                        src=""
                        alt="Xem trước hồ sơ bệnh án"
                        class="w-full max-h-[380px] object-contain bg-slate-100">
                </div>

                <!-- Analysis Results Preview -->
                <div id="medicalAnalysisPreview" class="hidden mt-6 space-y-4">
                    <div id="medicalAnalysisStatusCard" class="rounded-[1.75rem] border border-slate-200 bg-white px-5 py-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                                <i class="fa fa-wand-magic-sparkles text-sm"></i>
                            </span>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[2px] text-slate-400">Trạng thái phân tích</p>
                                <p class="text-sm font-bold text-slate-800" id="medicalAnalysisStatusText">Đã phân tích thành công</p>
                            </div>
                        </div>
                    </div>
                    <div id="extractedDataDisplay" class="space-y-3"></div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-4 border-t border-slate-200">
                        <button type="button" onclick="saveMedicalData()" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-2xl font-bold flex items-center justify-center gap-2 transition-colors">
                            <i class="fa fa-save"></i> Lưu vào hồ sơ dinh dưỡng
                        </button>
                        <button type="button" onclick="editMedicalData()" class="flex-1 bg-slate-600 hover:bg-slate-700 text-white px-6 py-3 rounded-2xl font-bold flex items-center justify-center gap-2 transition-colors">
                            <i class="fa fa-edit"></i> Chỉnh sửa
                        </button>
                    </div>
                </div>

                <!-- Hidden extracted data fields (for form submission) -->
                <input type="hidden" name="medical_analysis_data" id="medicalAnalysisData" value="<?= $medicalAnalysisJson ?>">
            </div>
        </div>

        <div class="col-span-12 lg:col-span-5 space-y-8">

            

            <div class="bg-slate-900 p-8 rounded-[2.5rem] text-white shadow-2xl">
                <div class="flex items-center gap-3 mb-8">
                    <span class="w-8 h-8 bg-white/10 text-white rounded-lg flex items-center justify-center font-black text-sm">4</span>
                    <h3 class="text-white/40 text-sm font-black uppercase tracking-[3px]">Mục tiêu chiến lược</h3>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    <?php $goal = $profile['muctieu'] ?? ''; ?>

                    <label class="relative flex items-center gap-4 p-4 rounded-2xl border-2 border-white/5 cursor-pointer transition-all has-[:checked]:border-green-500 has-[:checked]:bg-green-500/10 group">

                        <input type="radio" name="muctieu" value="Tăng cân - Giảm mỡ"
                            <?= $goal == 'Tăng cân - Giảm mỡ' ? 'checked' : '' ?> class="hidden">

                        <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center text-green-500 group-has-[:checked]:bg-green-500 group-has-[:checked]:text-white transition-all">
                            <i class="fa fa-dumbbell"></i>
                        </div>

                        <div>
                            <p class="font-bold">Tăng cân - Giảm mỡ</p>
                            <p class="text-[10px] text-white/40 uppercase tracking-widest font-black">Muscle Gain</p>
                        </div>

                        <i class="fa fa-check-circle absolute right-4 opacity-0 has-[:checked]:opacity-100 text-green-500"></i>
                    </label>


                    <label class="relative flex items-center gap-4 p-4 rounded-2xl border-2 border-white/5 cursor-pointer transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-500/10 group">

                        <input type="radio" name="muctieu" value="Giảm cân"
                            <?= $goal == 'Giảm cân' ? 'checked' : '' ?> class="hidden">

                        <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center text-blue-500 group-has-[:checked]:bg-blue-500 group-has-[:checked]:text-white transition-all">
                            <i class="fa fa-fire"></i>
                        </div>

                        <div>
                            <p class="font-bold">Giảm cân</p>
                            <p class="text-[10px] text-white/40 uppercase tracking-widest font-black">Weight Loss</p>
                        </div>
                    </label>


                    <label class="relative flex items-center gap-4 p-4 rounded-2xl border-2 border-white/5 cursor-pointer transition-all has-[:checked]:border-purple-500 has-[:checked]:bg-purple-500/10 group">

                        <input type="radio" name="muctieu" value="Duy trì sức khỏe"
                            <?= $goal == 'Duy trì sức khỏe' ? 'checked' : '' ?> class="hidden">

                        <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center text-purple-500 group-has-[:checked]:bg-purple-500 group-has-[:checked]:text-white transition-all">
                            <i class="fa fa-heartbeat"></i>
                        </div>

                        <div>
                            <p class="font-bold">Duy trì sức khỏe</p>
                            <p class="text-[10px] text-white/40 uppercase tracking-widest font-black">Health Balance</p>
                        </div>
                    </label>
                    <?php
                    $target = $profile['muctieu_cannang'] ?? '';
                    ?>

                    <div class="mt-6 pt-6 border-t border-white/5">
                        <label class="text-xs font-bold text-white/40 uppercase tracking-widest">
                            Cân nặng mục tiêu (KG)
                        </label>

                        <input
                            type="number"
                            step="0.1"
                            name="muctieu_cannang"
                            value="<?= htmlspecialchars($target) ?>"
                            class="w-full bg-white/5 border-2 border-white/10 focus:border-green-500 p-4 mt-2 rounded-2xl font-bold text-white outline-none transition-all text-2xl">
                    </div>

                    <div class="mt-6 pt-6 border-t border-white/5">
                        <label class="text-xs font-bold text-white/40 uppercase tracking-widest block mb-3">
                            Mục tiêu calo hàng ngày (KCAL)
                        </label>
                        <div class="space-y-3">
                            <div class="bg-white/5 border border-white/10 rounded-2xl p-4">
                                <p class="text-xs text-white/60 mb-3">Gợi ý dựa trên thể trạng:</p>
                                <div id="calorieRecommendation" class="text-sm text-white/80 leading-relaxed">
                                    <p>Hãy nhập đầy đủ thông số trên để nhận gợi ý</p>
                                </div>
                            </div>
                            <input
                                type="number"
                                step="50"
                                name="kcal_target"
                                id="kcalTarget"
                                value="<?= htmlspecialchars($profile['kcal_target'] ?? '') ?>"
                                class="w-full bg-white/5 border-2 border-white/10 focus:border-orange-500 p-4 rounded-2xl font-bold text-white outline-none transition-all text-2xl"
                                onchange="validateCalorieTarget()">
                            <p id="calorieWarning" class="text-xs text-orange-300 hidden flex items-center gap-2">
                                <i class="fa fa-exclamation-triangle"></i>
                                <span id="warningText"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">

                <!-- HEADER -->
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-slate-800 font-bold italic underline decoration-green-500 decoration-4">
                        Theo dõi cân nặng
                    </h3>
                </div>

                <!-- HIỂN THỊ -->
                <div class="space-y-4">

                    <?php if (!empty($weights)): ?>

                        <?php
                        // Show only 3 most recent records
                        $displayWeights = array_slice($weights, 0, 3);
                        foreach ($displayWeights as $index => $w):
                        ?>

                            <div class="flex items-center justify-between p-4 rounded-2xl
            <?= $index == 0 ? 'bg-slate-50' : 'opacity-60 border border-dashed border-slate-200' ?>">

                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full 
                    <?= $index == 0 ? 'bg-green-500' : 'bg-slate-300' ?>"></div>

                                    <span class="text-sm font-bold">
                                        <?= $index == 0 ? 'Hôm nay' : date('d/m/Y', strtotime($w['ngay'])) ?>
                                    </span>
                                </div>

                                <span class="font-black <?= $index == 0 ? 'text-slate-800' : 'text-slate-500' ?>">
                                    <?= $w['can_nang'] ?> kg
                                </span>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <!-- chua có dữ liệu -->
                        <div class="text-center text-slate-400 py-4">
                            Chưa có dữ liệu cân nặng
                        </div>

                    <?php endif; ?>

                    <p class="text-[11px] text-slate-400 text-center uppercase font-black">
                        Hệ thống sẽ cập nhật biểu đồ sau khi có 3 bản ghi
                    </p>

                </div>
            </div>
            <div class="flex gap-3 w-full md:w-auto mt-6">
                <button type="submit"
                    class="bg-[#1e293b] hover:bg-slate-800 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 shadow-lg transition-all">
                    <i class="fa fa-check"></i> Lưu thay đổi
                </button>
                <a href="index.php?controller=nutrition&action=list"
                    class="flex-1 md:flex-none text-center px-6 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-200 transition-all">
                    Hủy bỏ
                </a>



            </div>

        </div>
    </form>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        const btn = document.getElementById("btnAddWeight");
        const formBox = document.getElementById("weightForm");
        const form = document.getElementById("formAddWeight");
        const weightEl = document.getElementById("currentWeight");

        // ===== MỞ / ĐÓNG FORM =====
        if (btn && formBox) {
            btn.addEventListener("click", function() {
                formBox.classList.toggle("hidden");
            });
        }

        // ===== SUBMIT AJAX =====
        if (form) {
            form.addEventListener("submit", function(e) {
                e.preventDefault();

                const input = form.querySelector("input[name='cannang']");
                const value = input ? input.value.trim() : "";

                if (!value || value <= 0) {
                    notify("Nhập cân nặng hợp lệ!", "error");
                    return;
                }

                fetch("index.php?controller=nutrition&action=saveWeight", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "cannang=" + encodeURIComponent(value)
                    })
                    .then(res => res.json())
                    .then(res => {

                        if (res.success) {

                            // UPDATE UI NGAY
                            if (weightEl) {
                                weightEl.innerText = value + " kg";
                            }

                            // ?N FORM
                            if (formBox) {
                                formBox.classList.add("hidden");
                            }

                            // RESET INPUT
                            form.reset();

                        } else {
                            if (res.message) {
                                notify(res.message, "error");
                                return;
                            }
                            notify("Lưu thất bại!", "error");
                            console.log(res);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        notify("Lỗi kết nối server!", "error");
                    });
            });
        }

        // Initialize calorie recommendation on page load
        calculateAndRecommendCalories();

        // Kh?ng t? hi?n th? k?t qu? c? khi m? trang.
        // Ch? hi?n th? sau khi ng??i d?ng t?i ?nh m?i v? AI ph?n t?ch xong.
    });

    // ===== MEDICAL RECORD UPLOAD & AI ANALYSIS =====
    const dropZone = document.getElementById('medicalRecordDropZone');
    if (dropZone) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('border-purple-500', 'bg-purple-100/50');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('border-purple-500', 'bg-purple-100/50');
            });
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                document.getElementById('medicalRecordInput').files = files;
                handleMedicalRecordUpload(document.getElementById('medicalRecordInput'));
            }
        });
    }

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function setMedicalAnalysisStatus(message) {
        const preview = document.getElementById('medicalAnalysisPreview');
        const statusText = document.getElementById('medicalAnalysisStatusText');
        if (statusText) statusText.textContent = message;
        if (preview) preview.classList.remove('hidden');
    }

    function handleMedicalRecordUpload(input) {
        if (!input.files || input.files.length === 0) return;

        const file = input.files[0];

        const previewWrap = document.getElementById('medicalImagePreviewWrap');
        const previewImage = document.getElementById('medicalImagePreview');
        if (previewWrap && previewImage) {
            previewImage.src = URL.createObjectURL(file);
            previewWrap.classList.remove('hidden');
        }

        if (file.size > 10 * 1024 * 1024) {
            notify('Kích thước file không được vượt quá 10MB', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('image', file);
        formData.append('action', 'analyzeMedicalRecord');

        // Show loading state
        const dropZone = document.getElementById('medicalRecordDropZone');
        const originalHTML = dropZone.innerHTML;
        const restoreDropZone = () => {
            dropZone.innerHTML = originalHTML;
            dropZone.style.pointerEvents = 'auto';
        };
        dropZone.innerHTML = `
            <div class="w-full rounded-[2rem] border border-purple-100 bg-white/90 p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="relative h-14 w-14 shrink-0">
                        <div class="absolute inset-0 rounded-2xl bg-purple-100"></div>
                        <div class="absolute inset-2 rounded-xl border-4 border-purple-200 border-t-purple-600 animate-spin"></div>
                        <div class="absolute inset-0 flex items-center justify-center text-purple-700">
                            <i class="fa fa-file-medical text-sm"></i>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <p class="text-slate-900 font-black text-base">Đang phân tích hồ sơ</p>
                            <span class="rounded-full bg-purple-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-purple-600">AI</span>
                        </div>
                        <p class="text-slate-500 text-sm">Đang trích xuất kết quả cần lưu từ ảnh bệnh án.</p>
                        <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full w-2/3 rounded-full bg-gradient-to-r from-purple-500 via-indigo-500 to-emerald-500 animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        dropZone.style.pointerEvents = 'none';

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 90000);
        const watchdogId = setTimeout(() => {
            restoreDropZone();
            setMedicalAnalysisStatus('Quá trình xử lý mất quá lâu. Vui lòng thử lại.');
            notify('Quá trình xử lý mất quá lâu. Vui lòng thử lại.', 'warning');
        }, 95000);
        setMedicalAnalysisStatus('Đang phân tích...');

        fetch('index.php?controller=nutrition&action=analyzeMedicalRecord', {
                method: 'POST',
                body: formData,
                signal: controller.signal
            })
            .then(res => {
                if (!res.ok) {
                    return res.text().then(text => {
                        throw new Error('HTTP ' + res.status + ' ' + res.statusText + ': ' + text);
                    });
                }
                return res.json();
            })
            .then(data => {
                if (data.success && data.analysis) {
                    // Display analysis results
                    displayMedicalAnalysis(data.analysis);
                    setMedicalAnalysisStatus('Đã phân tích thành công');

                    // Store data for form submission
                    document.getElementById('medicalAnalysisData').value = JSON.stringify(data.analysis);

                    // Auto-populate form fields with extracted data
                    const analysis = data.analysis.health_metrics || {};

                    if (analysis.tuoi) {
                        const ageInput = document.querySelector('input[name="tuoi"]');
                        if (ageInput) ageInput.value = analysis.tuoi;
                    }

                    if (analysis.chieucao) {
                        const heightInput = document.querySelector('input[name="chieucao"]');
                        if (heightInput) heightInput.value = analysis.chieucao;
                    }

                    if (analysis.cannang) {
                        const weightInput = document.querySelector('input[name="cannang"]');
                        if (weightInput) weightInput.value = analysis.cannang;
                    }

                    if (analysis.gioitinh) {
                        const genderRadios = document.querySelectorAll('input[name="gioitinh"]');
                        genderRadios.forEach(radio => {
                            if (radio.value === analysis.gioitinh) radio.checked = true;
                        });
                    }

                    // Recalculate calorie recommendation after data population
                    setTimeout(() => {
                        calculateAndRecommendCalories();
                    }, 300);
                } else {
                    setMedicalAnalysisStatus('Phân tích thất bại');
                    notify('Phân tích thất bại: ' + (data.message || 'Không xác định'), 'error');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                if (err.name === 'AbortError') {
                    setMedicalAnalysisStatus('Phân tích quá lâu, vui lòng thử lại.');
                    notify('Quá trình phân tích quá lâu. Vui lòng thử lại ảnh rõ hơn.', 'warning');
                } else {
                    setMedicalAnalysisStatus('Lỗi kết nối khi phân tích.');
                    notify('Lỗi kết nối server!', 'error');
                }
            })
            .finally(() => {
                clearTimeout(timeoutId);
                clearTimeout(watchdogId);
                restoreDropZone();
            });
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function hasMedicalAnalysisContent(analysis) {
        if (!analysis || typeof analysis !== 'object') return false;

        const ignoredPhrases = [
            'Không thể phân tích ảnh',
            'Vui lòng tham khảo ý kiến bác sĩ để có hướng dẫn chi tiết',
            'Không thể phân tích hình ảnh',
            'Không có dữ liệu',
            'Không có tệp ảnh',
            'Không có kết quả'
        ];

        const normalize = (value) => String(value || '').trim().replace(/\s+/g, ' ');
        const isIgnored = (value) => {
            const text = normalize(value).toLowerCase();
            return ignoredPhrases.some(phrase => text === phrase.toLowerCase());
        };

        const topFields = ['findings', 'recommendations', 'diagnosis', 'treatment', 'vitals'];
        for (const field of topFields) {
            if (analysis[field] && !isIgnored(analysis[field])) {
                return true;
            }
        }

        // Nếu chỉ có health_metrics thông tin cá nhân / số liệu, không xem là phân tích AI hoàn chỉnh
        if (analysis.health_metrics && typeof analysis.health_metrics === 'object') {
            const metrics = { ...analysis.health_metrics };
            delete metrics.medical_record_image;

            for (const value of Object.values(metrics)) {
                if (value && !isIgnored(value)) {
                    return false;
                }
            }
        }

        return false;
    }

    function displayMedicalAnalysis(analysis) {
        if (!hasMedicalAnalysisContent(analysis)) {
            document.getElementById('medicalAnalysisPreview').classList.add('hidden');
            return;
        }

        const preview = document.getElementById('medicalAnalysisPreview');
        const display = document.getElementById('extractedDataDisplay');
        display.innerHTML = '';

        const contentContainer = document.createElement('div');
        contentContainer.className = 'space-y-3';
        display.appendChild(contentContainer);

        const metrics = analysis.health_metrics || {};
        const vitalsObj = analysis.chi_so_sinh_hieu || {};

        const normalizeText = (value) => String(value || '').trim();
        const formatReadableLines = (value) => {
            const normalized = normalizeText(value)
                .replace(/\r/g, '')
                .replace(/;\s*/g, ';\n')
                .replace(/\s*(\d+\.)\s*/g, '\n$1 ')
                .replace(/\n{2,}/g, '\n')
                .trim();
            return escapeHtml(normalized).replace(/\n/g, '<br>');
        };

        const diagnosisText = analysis.diagnosis || (Array.isArray(analysis.chan_doan) ? analysis.chan_doan.join(', ') : analysis.chan_doan) || metrics.chan_doan || '';
        const treatmentText = analysis.huong_dieu_tri || analysis.treatment_plan || '';
        const prescriptionText = analysis.treatment || analysis.ke_don || metrics.dieu_tri || '';
        const recommendationText = analysis.recommendations || analysis.loi_khuyen_dinh_duong || '';
        const labText = analysis.ket_qua_xet_nghiem || '';
        const xrayText = analysis.xquang || '';
        const clinicalText = analysis.lam_sang || '';
        const findingsText = analysis.findings || '';

        const canNang = vitalsObj.can_nang || metrics.cannang || '';
        const bmi = vitalsObj.bmi || metrics.bmi || '';
        const huyetAp = vitalsObj.huyet_ap || metrics.huyet_ap || '';

        if (diagnosisText) {
            contentContainer.innerHTML += `<div class="mt-4 p-4 rounded-2xl bg-amber-50 border border-amber-200"><p class="text-[10px] font-black uppercase tracking-widest text-amber-500 mb-2">CHẨN ĐÓAN</p><p class="text-sm text-slate-700 leading-relaxed">${formatReadableLines(diagnosisText)}</p></div>`;
        }

        if (treatmentText) {
            contentContainer.innerHTML += `<div class="mt-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-200"><p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-2">ĐIỀU TRỊ</p><p class="text-sm text-slate-700 leading-relaxed">${formatReadableLines(treatmentText)}</p></div>`;
        }

        if (prescriptionText) {
            contentContainer.innerHTML += `<div class="mt-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-200"><p class="text-[10px] font-black uppercase tracking-widest text-emerald-700 mb-2">ĐƠN THUỐC</p><p class="text-sm text-slate-700 leading-relaxed">${formatReadableLines(prescriptionText)}</p></div>`;
        }

        const vitalLines = [];
        if (canNang) vitalLines.push(`Can nang: ${canNang}`);
        if (bmi) vitalLines.push(`BMI: ${bmi}`);
        if (huyetAp) vitalLines.push(`Huyet ap: ${huyetAp}`);
        if (vitalLines.length > 0) {
            contentContainer.innerHTML += `<div class="mt-4 p-4 rounded-2xl bg-slate-100 border border-slate-200"><p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">DỮ LIỆU SINH HIỆU</p><p class="text-sm text-slate-700 leading-relaxed">${escapeHtml(vitalLines.join('\n')).replace(/\n/g, '<br>')}</p></div>`;
        }

        if (recommendationText) {
            contentContainer.innerHTML += `<div class="mt-4 p-4 rounded-2xl bg-purple-50 border border-purple-200"><p class="text-[10px] font-black uppercase tracking-widest text-purple-500 mb-2">KHUYẾN NGHỊ</p><p class="text-sm text-slate-700 leading-relaxed">${formatReadableLines(recommendationText)}</p></div>`;
        }


        if (labText) {
            contentContainer.innerHTML += `<div class="mt-4 p-4 rounded-2xl bg-cyan-50 border border-cyan-200"><p class="text-[10px] font-black uppercase tracking-widest text-cyan-600 mb-2">KET QUA XET NGHIEM</p><p class="text-sm text-slate-700 leading-relaxed">${formatReadableLines(labText)}</p></div>`;
        }

        if (xrayText) {
            contentContainer.innerHTML += `<div class="mt-4 p-4 rounded-2xl bg-indigo-50 border border-indigo-200"><p class="text-[10px] font-black uppercase tracking-widest text-indigo-600 mb-2">XQUANG</p><p class="text-sm text-slate-700 leading-relaxed">${formatReadableLines(xrayText)}</p></div>`;
        }

        if (clinicalText) {
            contentContainer.innerHTML += `<div class="mt-4 p-4 rounded-2xl bg-rose-50 border border-rose-200"><p class="text-[10px] font-black uppercase tracking-widest text-rose-600 mb-2">LAM SANG</p><p class="text-sm text-slate-700 leading-relaxed">${formatReadableLines(clinicalText)}</p></div>`;
        }

        if (findingsText) {
            contentContainer.innerHTML += `<div class="mt-4 p-4 rounded-2xl bg-slate-100 border border-slate-200"><p class="text-[10px] font-black uppercase tracking-widest text-slate-600 mb-2">KET LUAN LAM SANG</p><p class="text-sm text-slate-700 leading-relaxed">${formatReadableLines(findingsText)}</p></div>`;
        }

        if (!diagnosisText && !treatmentText && !prescriptionText && vitalLines.length === 0 && !recommendationText && !labText && !xrayText && !clinicalText && !findingsText) {
            contentContainer.innerHTML += `<div class="mt-4 p-4 rounded-2xl bg-slate-100 border border-slate-200"><p class="text-sm text-slate-700">Chua co du lieu phan tich ro rang.</p></div>`;
        }

        preview.classList.remove('hidden');
    }

    function saveMedicalData() {
        const analysisData = document.getElementById('medicalAnalysisData').value;
        if (!analysisData) {
            notify('Không có dữ liệu để lưu', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('analysis', analysisData);

        fetch('index.php?controller=nutrition&action=saveMedicalAnalysis', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                notify('Đã lưu dữ liệu vào hồ sơ dinh dưỡng thành công!', 'success');
                // Có thể redirect hoặc cập nhật UI
                window.location.href = 'index.php?controller=nutrition&action=list';
            } else {
                notify('Lỗi lưu dữ liệu: ' + (data.message || 'Không xác định'), 'error');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            notify('Lỗi kết nối server!', 'error');
        });
    }

    function editMedicalData() {
        const display = document.getElementById('extractedDataDisplay');
        const isEditing = display.classList.contains('editing');

        if (isEditing) {
            // Save changes
            const inputs = display.querySelectorAll('input[data-key], textarea[data-key]');
            const updatedData = {};

            inputs.forEach(input => {
                const key = input.getAttribute('data-key');
                if (key) {
                    updatedData[key] = input.value;
                }
            });

            // Update the hidden field
            const currentAnalysis = JSON.parse(document.getElementById('medicalAnalysisData').value || '{}');
            if (!currentAnalysis.health_metrics) currentAnalysis.health_metrics = {};
            Object.assign(currentAnalysis.health_metrics, updatedData);
            document.getElementById('medicalAnalysisData').value = JSON.stringify(currentAnalysis);

            // Switch back to display mode
            displayMedicalAnalysis(currentAnalysis);
            display.classList.remove('editing');
        } else {
            // Enter edit mode
            display.classList.add('editing');
            makeEditable(display);
        }
    }

    function makeEditable(container) {
        // Skip the image preview, only edit the content after it
        const contentContainer = container.querySelector('.space-y-3') || container;

        const sections = contentContainer.querySelectorAll('.bg-white');
        sections.forEach(section => {
            const text = section.textContent.trim();
            const key = section.querySelector('span.font-bold')?.previousElementSibling?.textContent?.replace(':', '').trim().toLowerCase();
            if (key) {
                const input = document.createElement('input');
                input.type = 'text';
                input.value = section.querySelector('span.font-bold').textContent;
                input.className = 'w-full px-3 py-2 border border-slate-300 rounded-lg text-sm';
                input.setAttribute('data-key', key);
                section.innerHTML = `<span class="text-slate-500">${section.querySelector('span.text-slate-500').textContent}</span>`;
                section.appendChild(input);
            }
        });

        const textBlocks = contentContainer.querySelectorAll('.bg-amber-50, .bg-emerald-50, .bg-slate-100, .bg-purple-50');
        textBlocks.forEach(block => {
            const label = block.querySelector('p:first-child').textContent;
            const content = block.querySelector('p:last-child').textContent;
            const key = label.toLowerCase().replace(/\s+/g, '_').replace(/[:\/]/g, '');

            const textarea = document.createElement('textarea');
            textarea.value = content;
            textarea.className = 'w-full px-3 py-2 border border-slate-300 rounded-lg text-sm';
            textarea.rows = 3;
            textarea.setAttribute('data-key', key);

            block.innerHTML = `<p class="text-[10px] font-black uppercase tracking-widest text-${block.classList.contains('bg-amber-50') ? 'amber' : block.classList.contains('bg-emerald-50') ? 'emerald' : block.classList.contains('bg-purple-50') ? 'purple' : 'slate'}-500 mb-2">${label}</p>`;
            block.appendChild(textarea);
        });
    }

    // ===== CALORIE TARGET CALCULATION & VALIDATION =====
    function getProfileGoalType(goal) {
        const first = String(goal || '').trim().charAt(0).toLowerCase();
        if (first === 'g') return 'loss';
        if (first === 't') return 'gain';
        if (first === 'd') return 'maintain';
        return 'other';
    }

    function showProfileMessage(message, type = "error") {
        notify(message, type);
    }

    function validateWeightGoalForm() {
        const current = parseFloat(document.querySelector('input[name="cannang"]')?.value || 0);
        const target = parseFloat(document.querySelector('input[name="muctieu_cannang"]')?.value || 0);
        const goal = document.querySelector('input[name="muctieu"]:checked')?.value || '';
        const goalType = getProfileGoalType(goal);

        if (!current || current < 20 || current > 300) {
            return 'Cân nặng hiện tại không hợp lệ. Vui lòng nhập trong khoảng 20 - 300 kg.';
        }

        if (!target || target < 20 || target > 300) {
            return 'Cân nặng mục tiêu không hợp lệ. Vui lòng nhập trong khoảng 20 - 300 kg.';
        }

        if (goalType === 'other') {
            return 'Vui lòng chọn mục tiêu cân nặng phù hợp.';
        }

        if (goalType === 'loss' && target >= current) {
            return 'Mục tiêu giảm cân cần có cân nặng mục tiêu thấp hơn cân nặng hiện tại.';
        }

        if (goalType === 'gain' && target <= current) {
            return 'Mục tiêu tăng cân cần có cân nặng mục tiêu cao hơn cân nặng hiện tại.';
        }

        if (goalType === 'maintain' && Math.abs(target - current) > 2) {
            return 'Mục tiêu duy trì sức khỏe chỉ nên lệch tối đa 2 kg so với cân nặng hiện tại.';
        }

        return '';
    }

    document.getElementById('edit-profile-form')?.addEventListener('submit', function(e) {
        const message = validateWeightGoalForm();
        if (message) {
            e.preventDefault();
            showProfileMessage(message, "error");
        }
    });

    function calculateAndRecommendCalories() {
        const age = parseFloat(document.querySelector('input[name="tuoi"]').value) || 0;
        const height = parseFloat(document.querySelector('input[name="chieucao"]').value) || 0;
        const weight = parseFloat(document.querySelector('input[name="cannang"]').value) || 0;
        const genderInputs = document.querySelectorAll('input[name="gioitinh"]');
        const goal = document.querySelector('input[name="muctieu"]:checked')?.value || '';

        let gender = '';
        genderInputs.forEach(radio => {
            if (radio.checked) gender = radio.value;
        });

        const recommendation = document.getElementById('calorieRecommendation');

        if (!age || !height || !weight || !gender || !goal) {
            recommendation.innerHTML = '<p class="text-sm">Hãy nhập đầy đủ thông tin trên để nhận gợi ý</p>';
            return;
        }

        // Calculate BMR using Mifflin-St Jeor formula
        let bmr = 0;
        if (gender === 'Nam') {
            bmr = 10 * weight + 6.25 * height - 5 * age + 5;
        } else {
            bmr = 10 * weight + 6.25 * height - 5 * age - 161;
        }

        // TDEE with moderate activity level (1.4x multiplier)
        const tdee = Math.round(bmr * 1.4);

        // Calculate recommendations based on goal
        let minCal, maxCal, suggested;

        if (goal === 'Giảm cân') {
            minCal = Math.max(gender === 'Nam' ? 1500 : 1200, Math.round(tdee - 500));
            maxCal = Math.round(tdee - 300);
            suggested = Math.round(tdee - 400);
        } else if (goal === 'Tăng cân - Giảm mỡ') {
            minCal = Math.round(tdee - 200);
            maxCal = Math.round(tdee + 200);
            suggested = Math.round(tdee + 100);
        } else { // Duy trì sức khỏe
            minCal = Math.round(tdee - 100);
            maxCal = Math.round(tdee + 100);
            suggested = tdee;
        }

        // Ensure minimum thresholds
        minCal = Math.max(minCal, gender === 'Nam' ? 1500 : 1200);
        maxCal = Math.min(maxCal, 3500);

        recommendation.innerHTML = `
            <div class="space-y-2 text-white/80">
                <p class="text-xs"><span class="text-white/60">BMR:</span> <span class="font-bold">${Math.round(bmr)}</span> kcal/ngày</p>
                <p class="text-xs"><span class="text-white/60">TDEE (Hoạt động vừa):</span> <span class="font-bold">${tdee}</span> kcal/ngày</p>
                <p class="text-xs text-white/60"><strong>Gợi ý:</strong> ${minCal} - ${maxCal} kcal/ngày<br><span class="text-orange-300">Khuyến nghị: ${suggested} kcal/ngày</span></p>
            </div>
        `;

        // Store calorie limits for validation
        document.getElementById('kcalTarget').dataset.minCal = minCal;
        document.getElementById('kcalTarget').dataset.maxCal = maxCal;
        document.getElementById('kcalTarget').dataset.suggestedCal = suggested;
    }

    function validateCalorieTarget() {
        const input = document.getElementById('kcalTarget');
        const value = parseInt(input.value) || 0;
        const minCal = parseInt(input.dataset.minCal) || 1200;
        const maxCal = parseInt(input.dataset.maxCal) || 3500;
        const warning = document.getElementById('calorieWarning');
        const warningText = document.getElementById('warningText');

        if (!value) {
            warning.classList.add('hidden');
            return;
        }

        if (value < minCal || value > maxCal) {
            warningText.textContent = `Mức tiêu calo nên nằm trong khoảng ${minCal} - ${maxCal} kcal. Bạn có chắc chắn muốn thiết lập ${value} kcal không?`;
            warning.classList.remove('hidden');
        } else {
            warning.classList.add('hidden');
        }
    }

    // Watch for changes in goal selection to recalculate recommendations
    document.querySelectorAll('input[name="muctieu"]').forEach(radio => {
        radio.addEventListener('change', calculateAndRecommendCalories);
    });

    // Watch for changes in age, height, weight to recalculate
    document.querySelector('input[name="tuoi"]')?.addEventListener('change', calculateAndRecommendCalories);
    document.querySelector('input[name="chieucao"]')?.addEventListener('change', calculateAndRecommendCalories);
    document.querySelector('input[name="cannang"]')?.addEventListener('change', calculateAndRecommendCalories);
    document.querySelectorAll('input[name="gioitinh"]').forEach(radio => {
        radio.addEventListener('change', calculateAndRecommendCalories);
    });
</script>

