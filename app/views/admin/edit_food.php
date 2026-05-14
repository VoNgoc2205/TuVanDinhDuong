<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?php
$food = $food ?? [];

$id = $food['id'] ?? '';
$ten_mon = $food['ten_mon'] ?? '';
$calo = $food['calo'] ?? '';
$protein = $food['protein'] ?? '';
$carb = $food['carb'] ?? '';
$fat = $food['fat'] ?? '';
$hinh_anh = $food['hinh_anh'] ?? '';
?>

<div class="min-h-screen bg-gradient-to-br from-green-50 to-lime-50 py-10 px-4">

    <!-- TITLE -->
    <div class="max-w-5xl mx-auto mb-6">
        <h2 class="text-2xl font-bold text-green-700 flex items-center gap-2">
            <i class="fa fa-edit"></i> Cập nhật thực phẩm
        </h2>
    </div>

    <!-- FORM -->
    <form action="?controller=admin&action=updateFood"
        method="POST"
        enctype="multipart/form-data"
        class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg p-6 md:p-10">

        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="current_hinh_anh" value="<?= $hinh_anh ?>">

        <!-- NAME -->
        <div class="mb-6">
            <label class="block font-semibold text-green-700 mb-2">Tên thực phẩm</label>
            <input type="text"
                name="ten_mon"
                value="<?= htmlspecialchars($ten_mon) ?>"
                class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-green-300 focus:outline-none">
        </div>

        <!-- NUTRITION -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

            <div>
                <label class="text-red-500 font-semibold">Calo (kcal)</label>
                <input type="number" name="calo"
                    value="<?= htmlspecialchars($calo) ?>"
                    class="w-full mt-2 px-3 py-3 border rounded-xl focus:ring-2 focus:ring-green-300">
            </div>

            <div>
                <label class="text-blue-500 font-semibold">Protein (g)</label>
                <input type="number" name="protein"
                    value="<?= htmlspecialchars($protein) ?>"
                    class="w-full mt-2 px-3 py-3 border rounded-xl focus:ring-2 focus:ring-green-300">
            </div>

            <div>
                <label class="text-yellow-500 font-semibold">Carb (g)</label>
                <input type="number" name="carb"
                    value="<?= htmlspecialchars($carb) ?>"
                    class="w-full mt-2 px-3 py-3 border rounded-xl focus:ring-2 focus:ring-green-300">
            </div>

            <div>
                <label class="text-cyan-500 font-semibold">Fat (g)</label>
                <input type="number" name="fat"
                    value="<?= htmlspecialchars($fat) ?>"
                    class="w-full mt-2 px-3 py-3 border rounded-xl focus:ring-2 focus:ring-green-300">
            </div>

        </div>

        <!-- IMAGE -->
        <div class="mb-6">
            <label class="block font-semibold text-green-700 mb-2">Hình ảnh</label>

            <div class="flex items-center gap-4">

                <?php if (!empty($hinh_anh)): ?>
                    <img src="/tuvandinhduong/<?= htmlspecialchars($hinh_anh) ?>"
                        class="w-32 h-32 object-cover rounded-xl border shadow">
                <?php endif; ?>

                <input type="file"
                    name="hinh_anh"
                    class="w-full border px-3 py-2 rounded-xl">
            </div>

            <p class="text-sm text-gray-500 mt-2">
                Chọn file mới nếu muốn thay đổi ảnh
            </p>
        </div>

        <!-- BUTTON -->
        <div class="flex gap-3">

            <button type="submit"
                class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition">
                <i class="fa fa-save mr-1"></i> Cập nhật
            </button>

            <a href="?controller=admin&action=food"
                class="px-6 py-3 bg-gray-100 hover:bg-gray-200 rounded-xl text-gray-600 font-semibold">
                Hủy
            </a>

        </div>

    </form>

</div>