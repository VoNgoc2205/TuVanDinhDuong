<?php
$user = $_SESSION['user'] ?? [];

$userName = $user['name'] ?? '';
$userEmail = $user['email'] ?? '';
$userPhone = $user['phone'] ?? '';
$userAvatar = $user['avatar'] ?? 'default.jpg';
$userAvatarPath = '';
if (!empty($userAvatar) && $userAvatar !== 'default.jpg') {
    $userAvatarPath = str_starts_with($userAvatar, 'public/')
        ? $userAvatar
        : "public/uploads/avatar/" . $userAvatar;
}
?>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
    }

    /* Tùy chỉnh thanh cuộn cho giống trang Chat */
    ::-webkit-scrollbar {
        width: 6px;
    }

    ::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
</style>

<div>


    <div class="flex items-center gap-4 mb-10">

        <div>
            <p class="text-sm font-black uppercase tracking-widest text-emerald-600">Cài đặt</p>
            <h1 class="mt-2 text-4xl font-extrabold text-slate-800 tracking-tight">Cài đặt tài khoản</h1>
            <p class="text-slate-500 text-sm font-medium">Quản lý thông tin cá nhân và bảo mật</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-6 flex items-center gap-3 rounded-[14px] border border-green-300 bg-green-50 px-5 py-4 font-extrabold text-emerald-700 animate-fade-in">
            <span class="inline-flex h-[18px] w-[18px] items-center justify-center rounded-full bg-emerald-600 text-[10px] text-white"><i class="fa fa-check"></i></span>
            <span class="font-bold text-sm"><?= $_SESSION['success'];
                                            unset($_SESSION['success']); ?></span>
        </div>
    <?php endif; ?>
<?php if (isset($_GET['success'])): ?>
    <div class="mb-6 flex items-center gap-3 rounded-[14px] border border-green-300 bg-green-50 px-5 py-4 font-extrabold text-emerald-700">
        <span class="inline-flex h-[18px] w-[18px] items-center justify-center rounded-full bg-emerald-600 text-[10px] text-white"><i class="fa fa-check"></i></span>
        Cập nhật thông tin thành công
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="mb-6 flex items-center gap-3 rounded-[14px] border border-red-200 bg-red-50 px-5 py-4 font-extrabold text-red-700">
        <span class="inline-flex h-[18px] w-[18px] items-center justify-center rounded-full bg-red-600 text-[10px] text-white"><i class="fa fa-xmark"></i></span>
        Có lỗi xảy ra, vui lòng thử lại
    </div>
<?php endif; ?>
    <form id="formProfile"
        class="space-y-8"
        method="POST"
        enctype="multipart/form-data"
        action="index.php?controller=user&action=updateProfile">
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <div class="flex flex-col md:flex-row">

                <div class="md:w-1/3 bg-slate-50/50 p-10 flex flex-col items-center justify-center border-r border-slate-100">
                    <div class="relative group cursor-pointer" onclick="document.getElementById('avatarInput').click()">
                        <div class="absolute -inset-1.5 bg-emerald-500/20 rounded-full blur opacity-0 group-hover:opacity-100 transition duration-500"></div>
                        <?php
                        $avatar = $userAvatar ?? '';
                        $name = $userName ?? 'U';
                        $firstChar = strtoupper(mb_substr($name, 0, 1));
                        ?>

                        <?php if (!empty($userAvatarPath) && file_exists($userAvatarPath)): ?>
                            <img id="preview"
                                src="<?= htmlspecialchars($userAvatarPath) ?>?v=<?= time() ?>"
                                class="w-40 h-40 rounded-full object-cover">
                        <?php else: ?>
                            <div id="preview"
                                class="w-40 h-40 rounded-full bg-emerald-500 flex items-center justify-center text-white text-5xl font-bold">
                                <?= $firstChar ?>
                            </div>
                        <?php endif; ?>
                        <div class="absolute bottom-2 right-2 w-10 h-10 bg-emerald-600 rounded-2xl flex items-center justify-center text-white border-4 border-white shadow-lg">
                            <i class="fa fa-camera text-xs"></i>
                        </div>
                    </div>
                    <input type="file" id="avatarInput" name="avatar" class="hidden" accept="image/*">

                    <div class="mt-6 text-center">
                        <h3 class="font-extrabold text-slate-800 text-lg"><?= htmlspecialchars($userName) ?></h3>
                        <span class="inline-block mt-2 px-4 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded-full tracking-widest uppercase">Premium Member</span>
                    </div>
                </div>

                <div class="md:w-2/3 p-8 md:p-12">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Họ và tên</label>
                            <input type="text" name="name"
               value="<?= htmlspecialchars($userName) ?>"
               required
               class="w-full bg-slate-100 rounded-2xl px-5 py-4">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Số điện thoại</label>
                                <input type="text" name="phone"
                   value="<?= htmlspecialchars($userPhone) ?>"
                   class="w-full bg-slate-100 rounded-2xl px-5 py-4">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Email</label>
                                <input type="email"
                   value="<?= htmlspecialchars($userEmail) ?>"
                   disabled
                   class="w-full bg-slate-200 rounded-2xl px-5 py-4 cursor-not-allowed">
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 flex justify-end">
                        <button type="submit"
                            class="bg-[#1e293b] hover:bg-black text-white px-10 py-4 rounded-2xl font-bold shadow-lg shadow-slate-200 transition-all active:scale-95 flex items-center gap-3">
                            <i class="fa fa-save"></i> Lưu thay đổi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form id="formPassword"
        method="POST"
        action="index.php?controller=user&action=changePassword"></form>

    <div class="bg-white rounded-[2.5rem] p-8 md:p-12 shadow-xl shadow-slate-200/50 border border-slate-100 mt-10">
        <div class="flex items-center gap-3 mb-8">
            <i class="fa fa-shield-halved text-emerald-600 text-xl"></i>
            <h3 class="text-xl font-extrabold text-slate-800 tracking-tight">Bảo mật & Mật khẩu</h3>
        </div>
        <!-- đặt NGAY TRÊN 3 ô password -->
        <?php if (isset($_SESSION['error'])): ?>
            <div id="errorMsg"
                class="mb-6 flex items-center gap-3 rounded-[14px] border border-red-200 bg-red-50 px-5 py-4 font-extrabold text-red-700">

                <span class="inline-flex h-[18px] w-[18px] items-center justify-center rounded-full bg-red-600 text-[10px] text-white"><i class="fa fa-xmark"></i></span>
                <span><?= $_SESSION['error'] ?></span>
            </div>

            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- MẬT KHẨU CŨ -->
            <div class="relative">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Mật khẩu cũ</label>
                <div class="relative">
                    <!-- MẬT KHẨU CŨ -->
<input type="password" 
       name="old_password"
       placeholder="•••••••••"
       class="w-full bg-slate-100 border-2 border-transparent rounded-2xl px-5 py-4
              focus:bg-white focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                    <i class="fa fa-eye toggle-password absolute right-5 top-5 text-slate-400 cursor-pointer"></i>
                </div>
                <p class="text-red-500 text-sm mt-1 hidden" id="oldError"></p>
            </div>

            <!-- MẬT KHẨU MỚI -->
            <div class="relative">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Mật khẩu mới</label>
                <div class="relative">
                    <!-- MẬT KHẨU MỚI -->
<input type="password" 
       name="new_password"
       placeholder="•••••••••"
       class="w-full bg-slate-100 border-2 border-transparent rounded-2xl px-5 py-4
              focus:bg-white focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                    <i class="fa fa-eye toggle-password absolute right-5 top-5 text-slate-400 cursor-pointer"></i>
                </div>
                <p class="text-red-500 text-sm mt-1 hidden" id="newError"></p>
            </div>

            <!-- XÁC NHẬN -->
            <div class="relative">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Xác nhận</label>
                <div class="relative">
                    <!-- XÁC NHẬN -->
<input type="password" 
       name="confirm_password"
       placeholder="•••••••••"
       class="w-full bg-slate-100 border-2 border-transparent rounded-2xl px-5 py-4
              focus:bg-white focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                    <i class="fa fa-eye toggle-password absolute right-5 top-5 text-slate-400 cursor-pointer"></i>
                </div>
                <p class="text-red-500 text-sm mt-1 hidden" id="confirmError"></p>
            </div>
        </div>

        <div class="mt-10 pt-8 border-t border-slate-50 flex flex-col md:flex-row justify-between items-center gap-6">
            <button type="submit"
                class="w-full md:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-4 rounded-2xl font-bold shadow-lg shadow-emerald-100 transition-all">
                Cập nhật mật khẩu
            </button>
            <?php if (isset($_SESSION['error'])): ?>
                <div id="errorMsg"
                    class="mb-6 flex items-center gap-3 rounded-[14px] border border-red-200 bg-red-50 px-5 py-4 font-extrabold text-red-700">

                    <span class="inline-flex h-[18px] w-[18px] items-center justify-center rounded-full bg-red-600 text-[10px] text-white"><i class="fa fa-xmark"></i></span>

                    <span><?= $_SESSION['error'] ?></span>
                </div>

                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <button type="submit" onclick="setAction('deleteAccount'); return confirm('Xóa vĩnh viễn tài khoản?')"
                class="text-red-500 font-bold hover:text-red-700 transition-colors flex items-center gap-2">
                <i class="fa fa-trash-can"></i> Xóa tài khoản
            </button>
        </div>
    </div>
    </form>
</div>
<script>
    let cropper;

    document.addEventListener("DOMContentLoaded", function() {

        // ===== TOGGLE PASSWORD =====
        document.body.addEventListener("click", function(e) {
            if (e.target.classList.contains("toggle-password")) {
                const input = e.target.parentElement.querySelector("input");
                if (!input) return;

                if (input.type === "password") {
                    input.type = "text";
                    e.target.classList.replace("fa-eye", "fa-eye-slash");
                } else {
                    input.type = "password";
                    e.target.classList.replace("fa-eye-slash", "fa-eye");
                }
            }
        });

        // ===== VALIDATE PASSWORD =====
        const form = document.getElementById("formPassword");

        const oldPass = document.querySelector("[name='old_password']");
        const newPass = document.querySelector("[name='new_password']");
        const confirmPass = document.querySelector("[name='confirm_password']");

        const errorBox = document.getElementById("errorMsg");

        function showError(msg) {
            if (!errorBox) return;
            errorBox.innerText = msg;
            errorBox.style.display = "block";
        }

        function hideError() {
            if (!errorBox) return;
            errorBox.style.display = "none";
        }

        // realtime check confirm
        confirmPass?.addEventListener("input", () => {
            if (confirmPass.value !== newPass.value) {
                showError("Mật khẩu xác nhận không khớp");
            } else {
                hideError();
            }
        });

        // submit validate
        form?.addEventListener("submit", function(e) {

            // chỉ validate khi đổi mật khẩu
            if (form.action.includes("changePassword")) {

                if (!oldPass.value || !newPass.value || !confirmPass.value) {
                    showError("Vui lòng nhập đầy đủ thông tin");
                    e.preventDefault();
                    return;
                }

                if (newPass.value.length < 6) {
                    showError("Mật khẩu phải >= 6 ký tự");
                    e.preventDefault();
                    return;
                }

                if (newPass.value !== confirmPass.value) {
                    showError("Mật khẩu xác nhận không khớp");
                    e.preventDefault();
                    return;
                }
            }
        });

        // ===== CHỌN ẢNH + CROP =====
        document.getElementById("avatarInput")?.addEventListener("change", function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const url = URL.createObjectURL(file);
            let preview = document.getElementById("preview");

            if (preview && preview.tagName === "DIV") {
                const img = document.createElement("img");
                img.id = "preview";
                img.className = "w-40 h-40 rounded-full object-cover";
                preview.replaceWith(img);
                preview = img;
            }

            if (preview) {
                preview.src = url;
            }
        });

    });

    // ===== CROP =====
    function closeCrop() {
        document.getElementById("cropModal").style.display = "none";
    }

    function saveCrop() {
        const canvas = cropper.getCroppedCanvas({
            width: 300,
            height: 300
        });

        canvas.toBlob(function(blob) {
            let file = new File([blob], "avatar.png", {
                type: "image/png"
            });

            let container = new DataTransfer();
            container.items.add(file);

            document.querySelector('input[name="avatar"]').files = container.files;

            let preview = document.getElementById("preview");

            if (preview.tagName === "DIV") {
                const img = document.createElement("img");
                img.id = "preview";
                img.className = preview.className + " object-cover";
                preview.replaceWith(img);
                preview = img;
            }

            preview.src = URL.createObjectURL(blob);

            closeCrop();
        });
    }

    // ===== SUBMIT PASSWORD =====


    // ===== AUTO HIDE SUCCESS =====
    setTimeout(() => {
    document.querySelectorAll(".alert-msg").forEach(el => {
        el.style.display = "none";
    });
}, 3000);
</script>
