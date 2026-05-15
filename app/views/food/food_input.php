<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #f8fafc;
    }

    .input-focus:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        outline: none;
    }

    .tab-transition {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .custom-shadow {
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.04);
    }
</style>

<div>

    <main class="w-full">
        <div class="w-full">
            <header class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-black uppercase tracking-widest text-emerald-600">Phân tích thực phẩm</p>
                    <h1 class="text-4xl font-[800] text-slate-800 tracking-tight flex items-center gap-3">

                        Nhận diện món ăn
                    </h1>
                    <p class="mt-2 text-slate-500 font-semibold">
                        Nhập thông tin hoặc tải ảnh món ăn để AI phân tích calo và thành phần dinh dưỡng.
                    </p>
                </div>

                <div class="bg-slate-200/50 p-1 rounded-[1.5rem] flex gap-1 w-full md:w-auto">
                    <button id="btnManual" onclick="showTab('manual')"
                        class="tab-transition flex-1 md:flex-none px-6 py-2.5 rounded-[1.2rem] font-bold text-sm bg-white text-emerald-600 shadow-sm">
                        Nhập thủ công
                    </button>
                    <button id="btnAI" onclick="showTab('ai')"
                        class="tab-transition flex-1 md:flex-none px-6 py-2.5 rounded-[1.2rem] font-bold text-sm text-slate-500 hover:text-slate-700">
                        Tải ảnh AI
                    </button>
                </div>
            </header>

            <div class="relative">
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="mb-6 flex items-center gap-3 rounded-[14px] border border-red-200 bg-red-50 px-5 py-4 font-extrabold text-red-700">
                        <span class="inline-flex h-[18px] w-[18px] items-center justify-center rounded-full bg-red-600 text-[10px] text-white"><i class="fa fa-xmark"></i></span>
                        <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                <div id="manualTab" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <form id="manualForm" action="index.php?controller=food&action=handle" method="post"
                        class="bg-white p-8 md:p-12 rounded-[2.5rem] custom-shadow border border-slate-50">
                        <div class="space-y-8">
                            <div class="group">
                                <label class="block text-sm font-bold text-slate-700 mb-3 ml-1">Tên món ăn</label>
                                <input type="text" id="ten_mon" name="ten_mon" placeholder="Ví dụ: Phở bò, Salad..."
                                    class="w-full bg-slate-50/50 border-2 border-slate-100 input-focus p-4 rounded-2xl text-xl font-bold transition-all">
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <label class="text-sm font-bold text-slate-700 ml-1">Thành phần chi tiết</label>
                                    <button type="button" onclick="addIngredient()"
                                        class="text-xs font-bold bg-emerald-50 text-emerald-600 px-4 py-2 rounded-lg hover:bg-emerald-100 transition-all">
                                        + Thêm hàng
                                    </button>
                                </div>
                                <div id="ingredients" class="space-y-3"></div>
                            </div>
                        </div>
                        <button type="button" onclick="analyzeFoodFixed()" class="w-full mt-10 bg-slate-900 text-white py-5 rounded-2xl text-xl font-black hover:bg-emerald-600 transition-all flex items-center justify-center gap-3">
                            <i class="fa fa-magnifying-glass"></i> PHÂN TÍCH NGAY
                        </button>
                    </form>
                </div>

                <div id="aiTab" class="hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <form id="aiForm" action="index.php?controller=food&action=infor_nutri" method="post" enctype="multipart/form-data"
                        class="bg-white p-8 md:p-12 rounded-[2.5rem] custom-shadow border border-slate-50">

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="flex flex-col justify-center space-y-6">
                                <div>
                                    <h3 class="text-xl font-bold text-slate-800">Tải ảnh món ăn</h3>
                                    <p class="text-sm text-slate-500 mt-1">Phân tích dinh dưỡng chuyên sâu bằng AI.</p>
                                </div>
                                
                                <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                                    <p class="text-xs text-emerald-700 font-medium">
                                        <i class="fa fa-lightbulb mr-1"></i> Mẹo: Chụp rõ món ăn, đủ sáng và thấy toàn bộ khẩu phần để kết quả phân tích chính xác hơn.
                                    </p>
                                </div>
                            </div>

                            <div class="relative">
                                <input type="file" name="image" id="imageInput" class="hidden" accept="image/*">
                                <label for="imageInput" class="cursor-pointer group block">
                                    <div class="border-2 border-dashed border-slate-200 rounded-[2rem] p-8 text-center group-hover:border-emerald-400 group-hover:bg-emerald-50/30 transition-all min-h-[250px] flex flex-col items-center justify-center relative overflow-hidden">
                                        <div id="uploadPlaceholder" class="space-y-3">
                                            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                                <i class="fa fa-cloud-arrow-up text-xl"></i>
                                            </div>
                                            <p class="font-bold text-slate-700 text-sm">Chọn ảnh món ăn</p>
                                            <p class="text-sm text-slate-400 font-medium uppercase tracking-wider">Hỗ trợ JPG, PNG</p>
                                        </div>
                                        <img id="preview" class="hidden absolute inset-0 w-full h-full object-cover rounded-[1.8rem]">
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button type="button" onclick="analyzeFoodFixed()" class="w-full mt-10 bg-slate-900 text-white py-5 rounded-2xl text-xl font-black hover:bg-emerald-600 transition-all flex items-center justify-center gap-3">
                            <i class="fa fa-wand-magic-sparkles"></i> AI NHẬN DIỆN NGAY
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    let currentMode = 'manual';

    function showTab(tab) {
        const manualTab = document.getElementById("manualTab");
        const aiTab = document.getElementById("aiTab");
        const btnManual = document.getElementById("btnManual");
        const btnAI = document.getElementById("btnAI");

        currentMode = tab;

        if (tab === 'manual') {
            manualTab.classList.remove("hidden");
            aiTab.classList.add("hidden");
            btnManual.className = "tab-transition flex-1 md:flex-none px-6 py-2.5 rounded-[1.2rem] font-bold text-sm bg-white text-emerald-600 shadow-sm";
            btnAI.className = "tab-transition flex-1 md:flex-none px-6 py-2.5 rounded-[1.2rem] font-bold text-sm text-slate-500 hover:text-slate-700";
        } else {
            manualTab.classList.add("hidden");
            aiTab.classList.remove("hidden");
            btnAI.className = "tab-transition flex-1 md:flex-none px-6 py-2.5 rounded-[1.2rem] font-bold text-sm bg-white text-emerald-600 shadow-sm";
            btnManual.className = "tab-transition flex-1 md:flex-none px-6 py-2.5 rounded-[1.2rem] font-bold text-sm text-slate-500 hover:text-slate-700";
        }
    }

    function addIngredient() {
        const container = document.getElementById("ingredients");
        const html = `
            <div class="flex items-center gap-3 animate-in slide-in-from-left-2 duration-300">
                <input type="text" name="thanh_phan[]" placeholder="Thành phần" class="flex-[2] bg-slate-50/80 border border-slate-100 input-focus p-3.5 rounded-xl font-bold text-sm">
                <div class="flex-[1] relative">
                    <input type="number" name="so_luong[]" placeholder="Lượng" class="w-full bg-slate-50/80 border border-slate-100 input-focus p-3.5 rounded-xl font-bold text-sm">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-black text-slate-300">GAM</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="w-10 h-10 flex items-center justify-center bg-red-50 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition-all"><i class="fa fa-times text-xs"></i></button>
            </div>`;
        container.insertAdjacentHTML("beforeend", html);
    }

    document.getElementById("imageInput").addEventListener("change", function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById("preview");
        const placeholder = document.getElementById("uploadPlaceholder");
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.classList.remove("hidden");
            placeholder.classList.add("opacity-0");
        }
    });

    addIngredient();

    function analyzeFood() {
        const formData = new FormData();
        const aiForm = document.getElementById('aiForm');
        const manualForm = document.getElementById('manualForm');

        if (currentMode === 'ai') {
            const tenMonInput = aiForm.querySelector('input[name="ten_mon"]');
            const tenMon = tenMonInput ? tenMonInput.value : '';
            formData.append("ten_mon", tenMon);

            const fileInput = aiForm.querySelector('#imageInput');
            if (fileInput.files.length > 0) {
                formData.append("image", fileInput.files[0]);
            } else {
                notify("Vui lòng chọn ảnh món ăn trước khi nhận diện.", "warning");
                return;
            }
        } else {
            const tenMonInput = manualForm.querySelector('input[name="ten_mon"]');
            const tenMon = tenMonInput ? tenMonInput.value : '';
            formData.append("ten_mon", tenMon);

            const ingredients = manualForm.querySelectorAll('input[name="thanh_phan[]"]');
            const quantities = manualForm.querySelectorAll('input[name="so_luong[]"]');

            ingredients.forEach((input, index) => {
                const name = input.value.trim();
                const qty = quantities[index] ? quantities[index].value.trim() : '';
                if (name !== '') {
                    formData.append('thanh_phan[]', name);
                    formData.append('so_luong[]', qty || 100);
                }
            });
        }

        formData.append('mode', currentMode);

        fetch("?controller=food&action=analyze", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status) {
                    window.currentFood = data.data;
                    window.location.href = "?controller=food&action=result";
                } else {
                    notify(data.message || data.msg || 'Lỗi phân tích', "error");
                }
            })
            .catch(err => {
                console.error(err);
                notify("Lỗi server", "error");
            });
    }
    async function analyzeFoodFixed() {
        const aiTab = document.getElementById('aiTab');
        const mode = aiTab && !aiTab.classList.contains('hidden') ? 'ai' : 'manual';
        currentMode = mode;

        const formData = new FormData();
        const aiForm = document.getElementById('aiForm');
        const manualForm = document.getElementById('manualForm');

        if (mode === 'ai') {
            const fileInput = aiForm ? aiForm.querySelector('#imageInput') : null;
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                notify('Vui lòng chọn ảnh món ăn trước khi nhận diện.', "warning");
                return;
            }

            const tenMonInput = aiForm.querySelector('input[name="ten_mon"]');
            formData.append('ten_mon', tenMonInput ? tenMonInput.value.trim() : '');
            formData.append('image', fileInput.files[0]);
        } else {
            const tenMonInput = manualForm ? manualForm.querySelector('input[name="ten_mon"]') : null;
            formData.append('ten_mon', tenMonInput ? tenMonInput.value.trim() : '');

            const ingredients = manualForm ? manualForm.querySelectorAll('input[name="thanh_phan[]"]') : [];
            const quantities = manualForm ? manualForm.querySelectorAll('input[name="so_luong[]"]') : [];

            ingredients.forEach((input, index) => {
                const name = input.value.trim();
                if (name === '') {
                    return;
                }

                const qty = quantities[index] ? quantities[index].value.trim() : '';
                formData.append('thanh_phan[]', name);
                formData.append('so_luong[]', qty || 100);
            });
        }

        formData.append('mode', mode);

        const activeButton = mode === 'ai'
            ? document.querySelector('#aiForm button[onclick="analyzeFoodFixed()"]')
            : document.querySelector('#manualForm button[onclick="analyzeFoodFixed()"]');
        const originalButtonHtml = activeButton ? activeButton.innerHTML : '';

        if (activeButton) {
            activeButton.disabled = true;
            activeButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ĐANG PHÂN TÍCH...';
        }

        try {
            const response = await fetch('index.php?controller=food&action=analyze', {
                method: 'POST',
                body: formData
            });

            const raw = await response.text();
            let data;
            try {
                data = JSON.parse(raw);
            } catch (error) {
                console.error('Invalid JSON response:', raw);
                throw new Error('Máy chủ trả về dữ liệu không hợp lệ.');
            }

            if (data.status) {
                window.location.href = 'index.php?controller=food&action=result';
                return;
            }

            notify(data.message || data.msg || 'Không thể phân tích món ăn. Vui lòng thử lại.', "error");
        } catch (error) {
            console.error(error);
            notify(error.message || 'Lỗi server, vui lòng thử lại.', "error");
        } finally {
            if (activeButton) {
                activeButton.disabled = false;
                activeButton.innerHTML = originalButtonHtml;
            }
        }
    }
</script>

