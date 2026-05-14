<?php
require_once "app/config/database.php";
require_once "app/helpers/SessionHelper.php";

SessionHelper::start();

$conn = mysqli_connect("localhost", "root", "", "tuvandinhduong");

$user = SessionHelper::user();
$user_id = $user['id'] ?? 0;

$sql = "
SELECT 
    DATE(m.thoigian) as ngay,
    m.bua,
    COALESCE(SUM(mi.calo), 0) as total_calo,
    COALESCE(SUM(mi.protein), 0) as total_protein,
    COALESCE(SUM(mi.carb), 0) as total_carb,
    COALESCE(SUM(mi.fat), 0) as total_fat
FROM meals m
JOIN meal_items mi ON m.id = mi.meal_id
WHERE m.user_id = ?
GROUP BY DATE(m.thoigian), m.bua
ORDER BY ngay DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$meals = [];
while ($row = $result->fetch_assoc()) {
    $meals[] = $row;
}
?>

<div class="p-6">

    <!-- HEADER -->
    <div class="mb-6">
        <h1 class="text-4xl font-black text-slate-800 flex items-center gap-3">
           
            Nhật ký dinh dưỡng
        </h1>
        <p class="text-slate-500 mt-2">
            Theo dõi các bữa ăn và lượng calo hàng ngày của bạn.
        </p>
    </div>

    <!-- CARD -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">

        <div class="overflow-x-auto">
            <table class="min-w-full">

                <!-- HEADER -->
                <thead class="bg-slate-50 text-slate-500 text-sm">
                    <tr>
                        <th class="px-6 py-4 text-left">Ngày</th>
                        <th class="px-6 py-4 text-left">Buổi ăn</th>
                        <th class="px-6 py-4 text-left">Năng lượng</th>
                        <th class="px-6 py-4 text-left">Trạng thái</th>
                        <th class="px-6 py-4 text-center">Hành động</th>
                    </tr>
                </thead>

                <!-- BODY -->
                <tbody class="divide-y divide-slate-100">

<?php if (!empty($meals)): ?>
    <?php foreach ($meals as $m): ?>
        <tr class="hover:bg-slate-50 transition">

            <!-- NGÀY -->
            <td class="px-6 py-4 font-semibold text-slate-700">
                <?= date('d/m/Y', strtotime($m['ngay'])) ?>
            </td>

            <!-- BUỔI -->
            <td class="px-6 py-4">
                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-sm font-semibold">
                    <?= $m['bua'] ?>
                </span>
            </td>

            <!-- CALO -->
            <td class="px-6 py-4 font-bold text-emerald-600">
                <?= (int)$m['total_calo'] ?> kcal
            </td>

            <!-- TRẠNG THÁI -->
            <td class="px-6 py-4">
                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-semibold">
                    Đạt mục tiêu
                </span>
            </td>

            <!-- 🔥 HÀNH ĐỘNG -->
            <td class="px-6 py-4 text-center">
                <a href="index.php?controller=meal&action=detail&date=<?= $m['ngay'] ?>&bua=<?= $m['bua'] ?>"
   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-emerald-600 border border-emerald-200 rounded-xl hover:bg-emerald-50 transition">

    <i class="fa fa-eye"></i>
    Xem chi tiết
</a>
            </td>

        </tr>
    <?php endforeach; ?>

<?php else: ?>
    <tr>
        <td colspan="5" class="text-center py-10 text-slate-400 font-medium">
            Chưa có dữ liệu
        </td>
    </tr>
<?php endif; ?>

</tbody>
            </table>
        </div>

    </div>

</div>