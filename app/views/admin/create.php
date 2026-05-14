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
$image = $food['image'] ?? '';
?>

<div class="min-h-screen bg-gradient-to-br from-green-50 to-lime-50 py-10 px-4">

    <!-- BACK -->
    <a href="?controller=admin&action=food"
        class="inline-flex items-center gap-2 text-green-700 font-medium mb-6 hover:text-green-900 transition">
        <i class="fa fa-arrow-left"></i> Quay lại danh sách
    </a>

    <!-- CARD -->
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-lg p-6 md:p-10">

        <!-- TITLE -->
        <h2 class="text-2xl font-bold text-green-700 mb-8 text-center">
            <i class="fa <?= !empty($id) ? 'fa-edit' : 'fa-plus-circle' ?>"></i>
            <?= !empty($id) ? 'Cập nhật thực phẩm' : 'Thêm thực phẩm mới' ?>
        </h2>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">

            <?php if (!empty($id)): ?>
                <input type="hidden" name="id" value="<?= $id ?>">
            <?php endif; ?>

            <!-- NAME -->
            <div>
                <label class="block font-semibold text-green-700 mb-2">Tên thực phẩm</label>
                <input type="text"
                    name="ten_mon"
                    value="<?= htmlspecialchars($ten_mon) ?>"
                    class="w-full px-4 py-3 rounded-xl border border-green-100 focus:ring-2 focus:ring-green-400 focus:outline-none"
                    required>
            </div>

            <!-- MACRO -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                <div>
                    <label class="block text-red-500 font-semibold mb-2">Calo</label>
                    <input type="number" name="calo"
                        value="<?= htmlspecialchars($calo) ?>"
                        class="w-full px-3 py-3 rounded-xl border focus:ring-2 focus:ring-green-300">
                </div>

                <div>
                    <label class="block text-blue-500 font-semibold mb-2">Protein</label>
                    <input type="number" name="protein"
                        value="<?= htmlspecialchars($protein) ?>"
                        class="w-full px-3 py-3 rounded-xl border focus:ring-2 focus:ring-green-300">
                </div>

                <div>
                    <label class="block text-yellow-500 font-semibold mb-2">Carb</label>
                    <input type="number" name="carb"
                        value="<?= htmlspecialchars($carb) ?>"
                        class="w-full px-3 py-3 rounded-xl border focus:ring-2 focus:ring-green-300">
                </div>

                <div>
                    <label class="block text-cyan-500 font-semibold mb-2">Fat</label>
                    <input type="number" name="fat"
                        value="<?= htmlspecialchars($fat) ?>"
                        class="w-full px-3 py-3 rounded-xl border focus:ring-2 focus:ring-green-300">
                </div>

            </div>

            <!-- IMAGE -->
            <div>
                <label class="block font-semibold text-green-700 mb-2">Hình ảnh</label>

                <div class="flex items-center gap-4">

                    <?php if (!empty($image)): ?>
                        <img src="/tuvandinhduong/public/uploads/food/<?= htmlspecialchars($image) ?>"
                            class="w-28 h-28 object-cover rounded-xl border shadow">
                    <?php endif; ?>

                    <input type="file"
                        name="image"
                        class="w-full px-3 py-2 border rounded-xl">
                </div>
            </div>

            <!-- BUTTON -->
            <div class="flex gap-3 pt-4">

                <button type="submit"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition">
                    <i class="fa fa-save mr-1"></i> Lưu thông tin
                </button>

                <a href="?controller=admin&action=food"
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 rounded-xl text-gray-600 font-semibold">
                    Hủy
                </a>

            </div>

        </form>

    </div>
</div>