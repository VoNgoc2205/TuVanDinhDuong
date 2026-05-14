<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-600">Thực phẩm</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Thêm thực phẩm mới</h1>
            <p class="mt-2 text-sm text-slate-500">Nhập thông tin dinh dưỡng cơ bản và hình ảnh đại diện.</p>
        </div>
        <a href="?controller=admin&action=food" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
            <i class="fa fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <form action="?controller=admin&action=storeFood" method="POST" enctype="multipart/form-data" class="grid gap-5 xl:grid-cols-[1fr_360px]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-lg font-black text-slate-900">Thông tin món ăn</h2>
            <div class="space-y-4">
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-600">Tên món ăn / thực phẩm</label>
                    <input type="text" name="ten_mon" required class="min-h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-600">Calo (kcal)</label>
                        <input type="number" step="0.1" name="calo" class="min-h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-600">Protein (g)</label>
                        <input type="number" step="0.1" name="protein" class="min-h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-600">Carb (g)</label>
                        <input type="number" step="0.1" name="carb" class="min-h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-600">Fat (g)</label>
                        <input type="number" step="0.1" name="fat" class="min-h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    </div>
                </div>
            </div>
        </section>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-lg font-black text-slate-900">Hình ảnh</h2>
            <label class="flex min-h-[220px] cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-200 bg-slate-50 p-5 text-center transition hover:border-emerald-300 hover:bg-emerald-50">
                <i class="fa fa-image text-3xl text-emerald-600"></i>
                <span class="mt-3 text-sm font-bold text-slate-700">Chọn ảnh món ăn</span>
                <span class="mt-1 text-xs text-slate-400">JPG, PNG, WEBP</span>
                <input type="file" name="image" class="hidden" onchange="previewImage(event)">
                <img id="preview" class="mt-4 hidden h-36 w-full rounded-2xl object-cover">
            </label>
            <button type="submit" class="mt-5 inline-flex min-h-[52px] w-full items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 text-sm font-bold text-white transition hover:bg-emerald-700">
                <i class="fa fa-save"></i> Lưu thực phẩm
            </button>
        </aside>
    </form>
</div>

<script>
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
