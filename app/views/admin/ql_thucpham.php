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
<h2 class="text-2xl font-bold text-green-800 flex items-center gap-2 mb-6">
    🍽️ Quản lý thực phẩm
</h2>

<!-- TOOLBAR -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">

    <!-- SEARCH -->
    <form method="GET" action="index.php" class="flex gap-2 w-full md:w-1/2">

        <input type="hidden" name="controller" value="admin">
        <input type="hidden" name="action" value="food">

        <input type="text"
            name="keyword"
            placeholder="🔍 Tìm kiếm thực phẩm..."
            value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>"
            class="w-full px-4 py-2 rounded-xl border border-gray-200 shadow-sm focus:ring-2 focus:ring-green-400 outline-none">

        <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl transition">
            <i class="fa fa-search"></i>
        </button>

    </form>

    <!-- ADD BUTTON -->
    <a href="?controller=admin&action=addFood"
        class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl shadow-md flex items-center gap-2 transition">
        <i class="fa fa-plus"></i> Thêm thực phẩm
    </a>

</div>

<!-- TOTAL -->
<div class="mb-4">
    <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 rounded-full font-medium">
        <i class="fa fa-database"></i>
        Tổng thực phẩm: <b><?= count($foods ?? []) ?></b>
    </div>
</div>

<!-- TABLE -->
<div class="bg-white rounded-2xl shadow-md overflow-hidden">

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-center">

            <!-- HEADER -->
            <thead class="bg-green-700 text-white">
                <tr>
                    <th class="p-3">ID</th>
                    <th class="p-3">Hình</th>
                    <th class="p-3">Tên thực phẩm</th>
                    <th class="p-3">Calo</th>
                    <th class="p-3">Protein</th>
                    <th class="p-3">Carb</th>
                    <th class="p-3">Fat</th>
                    <th class="p-3">Hành động</th>
                </tr>
            </thead>

            <!-- BODY -->
            <tbody>

                <?php if (!empty($foods)): ?>
                    <?php foreach ($foods as $food): ?>

                        <tr class="border-b hover:bg-green-50 transition">

                            <!-- ID -->
                            <td class="p-3">
                                <?= $food['id'] ?>
                            </td>

                            <!-- IMAGE -->
                            <td class="p-3 flex justify-center">
                                <img
                                    src="<?= '/' . basename(dirname(__DIR__, 3)) . '/' . $food['hinh_anh'] ?>"
                                    class="w-12 h-12 rounded-xl object-cover border">
                            </td>

                            <!-- NAME (FIX LỖI Ở ĐÂY) -->
                            <td class="p-3 font-semibold text-gray-700">
                                <?= htmlspecialchars($food['ten_mon'] ?? 'Không rõ') ?>
                            </td>

                            <!-- CALO -->
                            <td class="p-3">
                                <?= $food['calo'] ?? 0 ?> kcal
                            </td>

                            <!-- PROTEIN -->
                            <td class="p-3">
                                <?= $food['protein'] ?? 0 ?> g
                            </td>

                            <!-- CARB -->
                            <td class="p-3">
                                <?= $food['carb'] ?? 0 ?> g
                            </td>

                            <!-- FAT -->
                            <td class="p-3">
                                <?= $food['fat'] ?? 0 ?> g
                            </td>

                            <!-- ACTION -->
                            <td class="p-3 flex justify-center gap-2">

                                <a href="?controller=admin&action=editFood&id=<?= $food['id'] ?>"
                                    class="w-9 h-9 flex items-center justify-center bg-yellow-400 hover:bg-yellow-500 text-white rounded-lg transition">
                                    <i class="fa fa-edit text-sm"></i>
                                </a>

                                <a href="?controller=admin&action=deleteFood&id=<?= $food['id'] ?>"
                                    onclick="return confirm('Bạn có chắc muốn xóa?')"
                                    class="w-9 h-9 flex items-center justify-center bg-red-500 hover:bg-red-600 text-white rounded-lg transition">
                                    <i class="fa fa-trash text-sm"></i>
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="8" class="py-10 text-gray-400">
                            🍽️ Không có dữ liệu thực phẩm
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>
    </div>

</div>

</div>