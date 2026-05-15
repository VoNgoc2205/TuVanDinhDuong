<?php
$foods = $foods ?? [];
$totalFoods = $totalFoods ?? count($foods);
$page = $page ?? 1;
$perPage = $perPage ?? 10;
$totalPages = $totalPages ?? 1;
$keyword = $_GET['keyword'] ?? '';
$from = $totalFoods > 0 ? (($page - 1) * $perPage + 1) : 0;
$to = min($totalFoods, $page * $perPage);
$pageUrl = function ($targetPage) use ($keyword) {
    $params = [
        'controller' => 'admin',
        'action' => 'food',
        'page' => $targetPage,
    ];
    if ($keyword !== '') {
        $params['keyword'] = $keyword;
    }
    return 'index.php?' . http_build_query($params);
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-600">Thực phẩm</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Quản lý thực phẩm</h1>
            <p class="mt-2 text-sm text-slate-500">Mỗi trang hiển thị 10 món ăn cùng các chỉ số dinh dưỡng.</p>
        </div>
        <a href="?controller=admin&action=addFood" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-600">
            <i class="fa fa-plus"></i> Thêm thực phẩm
        </a>
    </div>

    <form method="GET" action="index.php" class="grid gap-3 md:grid-cols-[1fr_auto_auto]">
        <input type="hidden" name="controller" value="admin">
        <input type="hidden" name="action" value="food">
        <input type="text" name="keyword" placeholder="Tìm kiếm thực phẩm..." value="<?= htmlspecialchars($keyword) ?>" class="min-h-[48px] rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100">
        <button class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 text-sm font-bold text-white transition hover:bg-emerald-700">
            <i class="fa fa-search"></i> Tìm
        </button>
        <div class="inline-flex min-h-[48px] items-center justify-center rounded-2xl bg-white px-4 text-sm font-black text-emerald-700">
            <?= number_format($totalFoods) ?> món
        </div>
    </form>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-black uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-4">Món ăn</th>
                        <th class="px-5 py-4 text-right">Calo</th>
                        <th class="px-5 py-4 text-right">Protein</th>
                        <th class="px-5 py-4 text-right">Carb</th>
                        <th class="px-5 py-4 text-right">Fat</th>
                        <th class="px-5 py-4 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($foods)): ?>
                        <?php foreach ($foods as $food): ?>
                            <?php
                                $image = !empty($food['hinh_anh']) ? $food['hinh_anh'] : 'public/uploads/default.jpg';
                                $imageSrc = '/' . basename(dirname(__DIR__, 3)) . '/' . ltrim($image, '/');
                            ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="<?= htmlspecialchars($imageSrc) ?>" class="h-12 w-12 rounded-2xl border border-slate-200 object-cover" alt="">
                                        <div>
                                            <p class="font-black text-slate-900"><?= htmlspecialchars($food['ten_mon'] ?? 'Không rõ') ?></p>
                                            <p class="text-xs text-slate-400">ID #<?= (int)($food['id'] ?? 0) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right font-bold text-slate-700"><?= number_format((float)($food['calo'] ?? 0)) ?> kcal</td>
                                <td class="px-5 py-4 text-right text-slate-600"><?= number_format((float)($food['protein'] ?? 0), 1) ?>g</td>
                                <td class="px-5 py-4 text-right text-slate-600"><?= number_format((float)($food['carb'] ?? 0), 1) ?>g</td>
                                <td class="px-5 py-4 text-right text-slate-600"><?= number_format((float)($food['fat'] ?? 0), 1) ?>g</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="?controller=admin&action=editFood&id=<?= (int)$food['id'] ?>" class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition hover:bg-amber-100" title="Sửa">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="?controller=admin&action=deleteFood&id=<?= (int)$food['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa?')" class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600 transition hover:bg-red-100" title="Xóa">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400">Không có dữ liệu thực phẩm</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
            <p class="font-semibold">Hiển thị <?= number_format($from) ?> - <?= number_format($to) ?> / <?= number_format($totalFoods) ?> món</p>
            <div class="flex flex-wrap gap-2">
                <a href="<?= $pageUrl(max(1, $page - 1)) ?>" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 px-3 font-bold text-slate-600 transition hover:bg-slate-50 <?= $page <= 1 ? 'pointer-events-none opacity-40' : '' ?>">
                    <i class="fa fa-chevron-left"></i>
                </a>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= $pageUrl($i) ?>" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl px-3 font-bold transition <?= $i === (int)$page ? 'bg-emerald-600 text-white' : 'border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                <a href="<?= $pageUrl(min($totalPages, $page + 1)) ?>" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 px-3 font-bold text-slate-600 transition hover:bg-slate-50 <?= $page >= $totalPages ? 'pointer-events-none opacity-40' : '' ?>">
                    <i class="fa fa-chevron-right"></i>
                </a>
            </div>
        </div>
    </section>
</div>
