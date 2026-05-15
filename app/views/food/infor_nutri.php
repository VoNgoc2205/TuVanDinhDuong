<?php
require_once "app/helpers/SessionHelper.php";
require_once "app/config/WHO.php";

SessionHelper::start();

// ===== DATA =====

// 🔥 luôn đảm bảo có data (anti crash)
$food = $_SESSION['food'] ?? [
    "name" => "Món ăn",
    "calo" => 0,
    "protein" => 0,
    "carb" => 0,
    "fat" => 0
];

// nếu có nhiều món AI (future dùng)
$foods = $_SESSION['foods_ai'] ?? [];

// AI advice
$aiAdvice = $_SESSION['advice'] ?? 'Không có gợi ý';

// ảnh
$image = $_SESSION['food_image'] ?? "public/uploads/default.jpg";

// ===== GOAL (WHO) =====
if (!isset($_SESSION['goal'])) {
    $who = WHO::getStandard();

    $_SESSION['goal'] = [
        'calo' => $who['calo'] ?? 2000,
        'protein' => (($who['macro']['protein']['max'] ?? 20) / 100) * ($who['calo'] ?? 2000) / 4,
        'carb' => (($who['macro']['carb']['max'] ?? 60) / 100) * ($who['calo'] ?? 2000) / 4,
        'fat' => (($who['macro']['fat']['max'] ?? 30) / 100) * ($who['calo'] ?? 2000) / 9
    ];
}

$goal = $_SESSION['goal'];

$allNutrition = [
    'calo' => ['label' => 'Calories', 'unit' => 'kcal', 'value' => floatval($food['calo'] ?? 0), 'color' => 'bg-slate-900'],
    'protein' => ['label' => 'Protein', 'unit' => 'g', 'value' => floatval($food['protein'] ?? 0), 'color' => 'bg-blue-500'],
    'carb' => ['label' => 'Carbs', 'unit' => 'g', 'value' => floatval($food['carb'] ?? 0), 'color' => 'bg-amber-500'],
    'fat' => ['label' => 'Fat', 'unit' => 'g', 'value' => floatval($food['fat'] ?? 0), 'color' => 'bg-rose-500'],
    'fiber' => ['label' => 'Chất xơ', 'unit' => 'g', 'value' => floatval($food['fiber'] ?? 0), 'color' => 'bg-emerald-500'],
];
$topNutrients = $allNutrition;
uasort($topNutrients, fn($a, $b) => $b['value'] <=> $a['value']);
$topNutrients = array_slice($topNutrients, 0, 4, true);

// ===== CALCULATE =====

// 🔥 chống null + chia 0
$foodCalo = $food['calo'] ?? 0;
$goalCalo = $goal['calo'] ?? 2000;

$percentCalo = ($goalCalo > 0)
    ? ($foodCalo / $goalCalo) * 100
    : 0;

// ===== WARNING =====
if ($foodCalo == 0) {
    $warning = "Không lấy được dữ liệu dinh dưỡng";
} elseif ($percentCalo > 40) {
    $warning = "Món này chiếm " . round($percentCalo) . "% calo/ngày → cao";
} elseif ($percentCalo > 25) {
    $warning = "Món tương đối nhiều năng lượng";
} else {
    $warning = "Món ăn phù hợp";
}
?>

<?php if (!empty($data['error'])): ?>
    <div class="mb-4 flex items-center gap-3 rounded-[14px] border border-red-200 bg-red-50 px-5 py-4 font-extrabold text-red-700">
        <span class="inline-flex h-[18px] w-[18px] items-center justify-center rounded-full bg-red-600 text-[10px] text-white"><i class="fa fa-xmark"></i></span>
        <?= $data['error'] ?>
    </div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #f8fafc;
    }

    .custom-shadow {
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.04);
    }

    .glass-effect {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
    }

    .success-msg {
        background: #d1fae5;
        color: #065f46;
    }

    .error-msg {
        background: #fee2e2;
        color: #991b1b;
    }
</style>

<div class="w-full">

    <main class="w-full overflow-x-hidden">

        <div class="max-w-full mx-auto space-y-6">

            <header class="flex items-center gap-4 mb-6">
                <button onclick="window.history.back()" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl shadow-sm text-slate-400 hover:text-emerald-500 transition-all">
                    <i class="fa fa-arrow-left text-sm"></i>
                </button>
                <div>
                    <p class="text-sm font-black uppercase tracking-widest text-emerald-600">Kết quả AI</p>
                    <h1 class="text-4xl font-bold text-slate-800 flex items-center gap-2">

                        Kết quả phân tích AI
                    </h1>
                    <p class="text-xs font-medium text-slate-500">Dữ liệu dinh dưỡng chi tiết dựa trên thành phần</p>
                </div>
            </header>
<?php if (isset($_GET['success'])): ?>
    <div class="mb-6 flex items-center gap-3 rounded-[14px] border border-green-300 bg-green-50 px-5 py-4 font-extrabold text-emerald-700">
        <span class="inline-flex h-[18px] w-[18px] items-center justify-center rounded-full bg-emerald-600 text-[10px] text-white"><i class="fa fa-check"></i></span>
        Đã lưu  thành công
    </div>
<?php endif; ?>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white p-4 rounded-[2rem] custom-shadow border border-slate-50">
                        <div class="aspect-square rounded-[1.5rem] bg-slate-50 flex items-center justify-center overflow-hidden border border-slate-100">
                            <img
                                src="<?= !empty($_SESSION['food_image']) ? $_SESSION['food_image'] : 'public/uploads/default.jpg' ?>"
                                class="w-full h-full object-cover"
                                alt="Food"
                                onerror="this.src='https://cdn-icons-png.flaticon.com/512/1046/1046747.png'">
                        </div>
                        <div class="mt-4 flex justify-center">
                            <button onclick="location.reload()" class="text-xs font-bold text-slate-400 hover:text-emerald-600 transition-all flex items-center gap-2">
                                <i class="fa fa-rotate-right"></i> Phân tích lại
                            </button>
                        </div>
                    </div>

                    <div class="bg-emerald-500 p-6 rounded-[2rem] text-white relative overflow-hidden shadow-lg shadow-emerald-100">
                        <div class="relative z-10">
                            <span class="text-[10px] font-black uppercase tracking-widest opacity-80">NutriAI Tư Vấn</span>
                            <p class="mt-2 text-sm font-bold leading-relaxed italic">
                                "<?= !empty($aiAdvice) ? $aiAdvice : 'Không có gợi ý' ?>"
                            </p>
                        </div>
                        <i class="fa-solid fa-robot absolute -right-4 -bottom-4 text-6xl opacity-10"></i>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="bg-white p-6 md:p-10 rounded-[2.5rem] custom-shadow border border-slate-50 h-full">

                        <div class="flex flex-col md:flex-row justify-between items-start mb-8 border-b border-slate-50 pb-6 gap-4">
                            <div>
                                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight"><?= $food['name'] ?? 'Món ăn' ?></h2>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Phân tích thành công</span>
                                </div>
                            </div>
                            <div class="bg-slate-50 px-4 py-2 rounded-xl text-right">
                                <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Mục tiêu ngày</span>
                                <span class="text-sm font-bold text-emerald-600"><?= $goal['calo'] ?? 2000 ?> kcal/ngày</span>
                            </div>
                        </div>

                        <?php if (!empty($foods) && count($foods) > 0): ?>
                        <div class="mb-6 p-6 rounded-[2rem] bg-slate-50 border border-slate-100">
                            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-[0.3em] mb-4">Món ăn AI nhận diện</h3>
                            <div class="space-y-3">
                                <?php foreach ($foods as $item): ?>
                                    <div class="rounded-2xl bg-white p-4 border border-slate-100 shadow-sm">
                                        <p class="font-bold text-slate-700"><?= htmlspecialchars($item['name'] ?? '') ?></p>
                                        <p class="text-xs text-slate-500 mt-1">Lượng: <?= round($item['gram'] ?? 0) ?>g • <?= round($item['calo'] ?? 0) ?> kcal • <?= round($item['protein'] ?? 0) ?>g đạm • <?= round($item['carb'] ?? 0) ?>g tinh bột • <?= round($item['fat'] ?? 0) ?>g béo</p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="flex items-center gap-4 p-4 rounded-2xl mb-8 <?= ($percentCalo > 40) ? 'bg-orange-50 text-orange-700' : 'bg-emerald-50 text-emerald-700' ?>">
                            <i class="fa <?= ($percentCalo > 40) ? 'fa-circle-exclamation' : 'fa-circle-check' ?> text-lg"></i>
                            <p class="text-xs font-bold"><?= $warning ?></p>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <?php foreach ($topNutrients as $key => $nutrient): ?>
                                <div class="<?= $nutrient['color'] ?> p-6 rounded-[1.8rem] text-center text-white shadow-lg shadow-slate-100">
                                    <span class="block text-2xl font-black"><?= round($nutrient['value'], 1) ?><?= $nutrient['unit'] === 'g' ? 'g' : '' ?></span>
                                    <span class="text-[9px] font-bold uppercase opacity-80 tracking-wider"><?= htmlspecialchars($nutrient['label']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="button" onclick="toggleNutritionDetails()" class="mb-10 inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-3 text-sm font-black text-slate-600 hover:bg-slate-200 transition">
                            <i class="fa fa-list-ul"></i> Khác
                        </button>

                        <div id="nutritionDetails" class="hidden mb-10 rounded-[2rem] bg-slate-50 border border-slate-100 p-5">
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.25em] mb-4">Tất cả chỉ số dinh dưỡng</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <?php foreach ($allNutrition as $nutrient): ?>
                                    <div class="flex items-center justify-between rounded-2xl bg-white px-4 py-3 border border-slate-100">
                                        <span class="text-sm font-bold text-slate-600"><?= htmlspecialchars($nutrient['label']) ?></span>
                                        <span class="text-sm font-black text-slate-900"><?= round($nutrient['value'], 1) ?> <?= $nutrient['unit'] ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Thời điểm ăn</label>
                                <select id="meal_type" class="w-full bg-slate-50 border-none p-4 rounded-xl font-bold text-slate-700 focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                                    <option>Sáng</option>
                                    <option>Trưa</option>
                                    <option>Tối</option>
                                    <option>Bữa phụ</option>
                                </select>
                            </div>

                        </div>
                        <div id="saveMessage"
                            class="hidden mb-4 px-5 py-3 rounded-2xl font-semibold text-sm">
                        </div>
                        <div class="flex gap-4 mt-10">
                            <button id="saveMealButton" onclick="saveMeal()" class="flex-[2] bg-slate-900 text-white py-4 rounded-xl font-black text-sm hover:bg-emerald-600 transition-all flex items-center justify-center gap-2 shadow-lg shadow-slate-200">
                                <i class="fa fa-cloud-arrow-up"></i> XÁC NHẬN LƯU
                            </button>
                            <button onclick="window.history.back()" class="flex-1 bg-slate-100 text-slate-400 py-4 rounded-xl font-black text-sm hover:bg-rose-50 hover:text-rose-500 transition-all">
                                HỦY BỎ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>





<script>
function saveMeal() {

    if (!window.currentFood) {
        showSaveNotice("Không có dữ liệu món ăn", "error");
        return;
    }

    const saveButton = document.getElementById("saveMealButton");
    const originalButtonHtml = saveButton ? saveButton.innerHTML : "";
    if (saveButton) {
        saveButton.disabled = true;
        saveButton.classList.add("opacity-70", "cursor-not-allowed");
        saveButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ĐANG LƯU...';
    }

    const ingredientGram = (window.currentFood.ingredients || []).reduce((sum, item) => {
        return sum + (parseFloat(item.gram || 0) || 0);
    }, 0);
    const totalGram = parseFloat(window.currentFood.gram || 0) || ingredientGram;

    const data = {
        bua: document.getElementById("meal_type").value,
        items: [{
            ten_mon: window.currentFood.ten_mon || window.currentFood.name || "Món ăn",
            calo: window.currentFood.calo || 0,
            protein: window.currentFood.protein || 0,
            carb: window.currentFood.carb || 0,
            fat: window.currentFood.fat || 0,
            fiber: window.currentFood.fiber || 0,
            gram: totalGram,
            hinh_anh: window.currentFood.image || "",
            ingredients: window.currentFood.ingredients || [],
            nutrition: window.currentFood.nutrition || {}
        }]
    };

    fetch("?controller=meal&action=save", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
    })
    .then(res => res.text())
    .then(res => {
        const result = String(res || "").trim();
        console.log(result);

        if (result === "OK") {
            showSaveNotice("Đã lưu thành công", "success");
            if (saveButton) {
                saveButton.innerHTML = '<i class="fa fa-check"></i> ĐÃ LƯU';
            }
            setTimeout(() => {
                const url = new URL(window.location.href);
                url.searchParams.set("success", "1");
                window.location.href = url.toString();
            }, 900);
        } else {
            showSaveNotice(result || "Lưu thất bại", "error");
            resetSaveButton();
        }
    })
    .catch(err => {
        console.error(err);
        showSaveNotice("Lỗi server, vui lòng thử lại", "error");
        resetSaveButton();
    });

    function resetSaveButton() {
        if (!saveButton) return;
        saveButton.disabled = false;
        saveButton.classList.remove("opacity-70", "cursor-not-allowed");
        saveButton.innerHTML = originalButtonHtml;
    }
}
function showSaveNotice(message, type = "info") {
    if (typeof notify === "function") {
        notify(message, type);
        return;
    }

    if (window.Swal) {
        Swal.fire({
            icon: type === "success" ? "success" : "error",
            title: message,
            timer: 1800,
            showConfirmButton: false
        });
        return;
    }

    console[type === "success" ? "log" : "error"](message);
}
function toggleNutritionDetails() {
    const box = document.getElementById("nutritionDetails");
    if (box) box.classList.toggle("hidden");
}
window.currentFood = {
    name: <?= json_encode($food['name'] ?? 'Món ăn', JSON_UNESCAPED_UNICODE) ?>,
    ten_mon: <?= json_encode($food['name'] ?? 'Món ăn', JSON_UNESCAPED_UNICODE) ?>,
    image: <?= json_encode($image, JSON_UNESCAPED_UNICODE) ?>,
    calo: <?= json_encode(floatval($food['calo'] ?? 0)) ?>,
    protein: <?= json_encode(floatval($food['protein'] ?? 0)) ?>,
    carb: <?= json_encode(floatval($food['carb'] ?? 0)) ?>,
    fat: <?= json_encode(floatval($food['fat'] ?? 0)) ?>,
    fiber: <?= json_encode(floatval($food['fiber'] ?? 0)) ?>,
    gram: <?= json_encode(floatval($food['gram'] ?? array_sum(array_map(fn($item) => floatval($item['gram'] ?? 0), $foods)))) ?>,
    ingredients: <?= json_encode($foods, JSON_UNESCAPED_UNICODE) ?>,
    nutrition: <?= json_encode($allNutrition, JSON_UNESCAPED_UNICODE) ?>
};

function analyzeFood() {

    const formData = new FormData();

    formData.append("ten_mon", document.getElementById("ten_mon").value);
    formData.append("calo", document.getElementById("calo").value);
    formData.append("protein", document.getElementById("protein").value);
    formData.append("carb", document.getElementById("carb").value);
    formData.append("fat", document.getElementById("fat").value);

    fetch("?controller=food&action=analyze", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        window.currentFood = data;

        console.log("AI result:", data);
    });
}

</script>

