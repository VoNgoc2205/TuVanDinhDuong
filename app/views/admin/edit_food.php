<?php
$food = $food ?? [];
$id = $food['id'] ?? '';
$ten_mon = $food['ten_mon'] ?? '';
$calo = $food['calo'] ?? '';
$protein = $food['protein'] ?? '';
$carb = $food['carb'] ?? '';
$fat = $food['fat'] ?? '';
$hinh_anh = $food['hinh_anh'] ?? '';
$imageSrc = !empty($hinh_anh) ? '/' . basename(dirname(__DIR__, 3)) . '/' . ltrim($hinh_anh, '/') : '';
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-600">Thực phẩm</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Cập nhật thực phẩm</h1>
            <p class="mt-2 text-sm text-slate-500">Chỉnh sửa tên món, chỉ số dinh dưỡng và hình ảnh.</p>
        </div>
        <a href="?controller=admin&action=food" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
            <i class="fa fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <form action="?controller=admin&action=updateFood" method="POST" enctype="multipart/form-data" class="grid gap-5 xl:grid-cols-[1fr_360px]">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <input type="hidden" name="current_hinh_anh" value="<?= htmlspecialchars($hinh_anh) ?>">

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-lg font-black text-slate-900">Thông tin món ăn</h2>
            <div class="space-y-4">
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-600">Tên thực phẩm</label>
                    <input type="text" name="ten_mon" value="<?= htmlspecialchars($ten_mon) ?>" required class="min-h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <?php foreach ([['calo', 'Calo (kcal)', $calo], ['protein', 'Protein (g)', $protein], ['carb', 'Carb (g)', $carb], ['fat', 'Fat (g)', $fat]] as $field): ?>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-600"><?= $field[1] ?></label>
                            <input type="number" step="0.1" name="<?= $field[0] ?>" value="<?= htmlspecialchars($field[2]) ?>" class="min-h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-lg font-black text-slate-900">Hình ảnh</h2>
            <?php if ($imageSrc): ?>
                <img src="<?= htmlspecialchars($imageSrc) ?>" class="mb-4 h-44 w-full rounded-3xl border border-slate-200 object-cover" alt="">
            <?php endif; ?>
            <label class="block text-sm font-bold text-slate-600">Thay ảnh mới</label>
            <input type="file" name="hinh_anh" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
            <button type="submit" class="mt-5 inline-flex min-h-[52px] w-full items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 text-sm font-bold text-white transition hover:bg-emerald-700">
                <i class="fa fa-save"></i> Cập nhật
            </button>
            <a href="?controller=admin&action=food" class="mt-3 inline-flex min-h-[52px] w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Hủy</a>
        </aside>
    </form>
</div>
