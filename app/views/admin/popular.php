<?php
error_reporting(0);
ini_set('display_errors', 0);
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-600">Phổ biến</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Thực phẩm phổ biến</h1>
            <p class="mt-2 text-sm text-slate-500">Danh sách món ăn được dùng nhiều trong hệ thống.</p>
        </div>
        <a href="index.php?controller=admin&action=food" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
            <i class="fa fa-utensils"></i> Quản lý thực phẩm
        </a>
    </div>

    <form method="GET" class="grid gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_auto]">
        <input type="hidden" name="controller" value="admin">
        <input type="hidden" name="action" value="popular">
        <input type="text" name="keyword" placeholder="Tìm tên thực phẩm..." value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" class="min-h-[48px] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
        <button class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 text-sm font-bold text-white transition hover:bg-emerald-700">
            <i class="fa fa-search"></i> Tìm
        </button>
    </form>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <?php if (!empty($foods)): ?>
            <?php foreach ($foods as $food): ?>
                <?php
                    $image = !empty($food['hinh_anh']) ? $food['hinh_anh'] : 'public/uploads/default.jpg';
                    $imageSrc = '/tuvandinhduong/' . ltrim($image, '/');
                ?>
                <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                    <div class="relative">
                        <img src="<?= htmlspecialchars($imageSrc) ?>" class="h-44 w-full object-cover" alt="">
                        <a href="index.php?controller=admin&action=deletePopular&ten=<?= urlencode($food['ten_mon']) ?>" data-confirm="Xóa món này?" data-confirm-title="Xóa món phổ biến" data-confirm-ok="Xóa" class="absolute right-3 top-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white/90 text-red-600 shadow-sm transition hover:bg-red-50">
                            <i class="fa fa-trash"></i>
                        </a>
                    </div>
                    <div class="p-5">
                        <h2 class="truncate text-base font-black text-slate-900"><?= htmlspecialchars($food['ten_mon']) ?></h2>
                        <p class="mt-2 text-sm font-bold text-emerald-600"><?= number_format((float)($food['calo'] ?? 0)) ?> kcal / 100g</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">P: <?= number_format((float)($food['protein'] ?? 0), 1) ?>g</span>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">Thực phẩm</span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full rounded-3xl border border-dashed border-slate-200 bg-white p-12 text-center text-slate-400">Không có dữ liệu thực phẩm phổ biến</div>
        <?php endif; ?>
    </div>
</div>
