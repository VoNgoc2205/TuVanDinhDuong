<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-50 p-6">

    <!-- BACK -->
    <a href="?controller=admin&action=food"
        class="inline-flex items-center gap-2 text-green-700 font-medium hover:text-green-900 transition mb-6">
        <i class="fa fa-arrow-left"></i> Quay lại danh sách
    </a>

    <!-- TITLE -->
    <h2 class="text-2xl font-bold text-green-800 mb-6 flex items-center gap-2">
        <i class="fa fa-plus-circle"></i> Thêm thực phẩm mới
    </h2>

    <form action="?controller=admin&action=storeFood" method="POST" enctype="multipart/form-data">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- LEFT -->
            <div class="md:col-span-2 bg-white rounded-2xl shadow-sm p-6 border border-green-100">

                <!-- NAME -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-600 mb-2">
                        Tên món ăn / Thực phẩm
                    </label>
                    <input type="text" name="ten_mon"
                        class="w-full px-4 py-3 rounded-xl border border-green-200
                                  focus:ring-2 focus:ring-green-300 focus:border-green-500 outline-none"
                        required>
                </div>

                <!-- CALO + PROTEIN -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-2">Calo (kcal)</label>
                        <input type="number" name="calo"
                            class="w-full px-4 py-3 rounded-xl border border-green-200
                                      focus:ring-2 focus:ring-green-300 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-2">Protein (g)</label>
                        <input type="number" name="protein"
                            class="w-full px-4 py-3 rounded-xl border border-green-200
                                      focus:ring-2 focus:ring-green-300 outline-none">
                    </div>
                </div>

                <!-- CARB + FAT -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-2">Carb (g)</label>
                        <input type="number" name="carb"
                            class="w-full px-4 py-3 rounded-xl border border-green-200
                                      focus:ring-2 focus:ring-green-300 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-2">Fat (g)</label>
                        <input type="number" name="fat"
                            class="w-full px-4 py-3 rounded-xl border border-green-200
                                      focus:ring-2 focus:ring-green-300 outline-none">
                    </div>
                </div>

            </div>

            <!-- RIGHT IMAGE UPLOAD -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-green-100">

                <label class="block text-sm font-medium text-slate-600 mb-3">
                    Hình ảnh thực phẩm
                </label>

                <div class="border-2 border-dashed border-green-200 rounded-xl p-4 text-center bg-green-50">

                    <input type="file" name="image"
                        class="w-full text-sm mb-3"
                        onchange="previewImage(event)">

                    <p class="text-xs text-slate-500 mb-3">JPG, PNG, WEBP</p>

                    <img id="preview"
                        class="w-full h-48 object-cover rounded-xl hidden shadow-sm">

                </div>

            </div>

        </div>

        <!-- BUTTON -->
        <div class="mt-6">
            <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl
                           font-semibold shadow-md transition flex items-center gap-2">
                <i class="fa fa-save"></i> Lưu thực phẩm
            </button>
        </div>

    </form>
</div>

<script>
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('preview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove("hidden");
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>