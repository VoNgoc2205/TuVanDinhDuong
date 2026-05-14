<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo tài khoản | NutriAI</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex flex-col">

    <!-- HEADER -->
    <?php require "app/views/shares/header.php"; ?>

    <main class="flex-grow flex items-center justify-center px-4 py-12">

        <!-- GRID 45% - 55% -->
        <div class="max-w-[1100px] w-full grid md:grid-cols-[45%_55%] 
                    bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden">

            <!-- LEFT (KHUNG XANH TO HƠN) -->
            <div class="relative p-10 flex flex-col justify-center text-white 
                        bg-gradient-to-br from-green-500 to-emerald-600 overflow-hidden">

                <!-- 🌿 LÁ LỚN -->
                <div class="absolute bottom-[-40px] left-[-40px] w-[320px] h-[320px] 
                            bg-white/10 rounded-[60%] rotate-12 blur-2xl"></div>

                <!-- 🌿 LÁ NHỎ -->
                <div class="absolute bottom-10 left-10 w-[180px] h-[120px] 
                            bg-white/10 rounded-[50%] rotate-[-20deg] blur-xl"></div>

                <!-- 🌊 CONG DƯỚI -->
                <div class="absolute bottom-0 left-0 w-full h-24 
                            bg-white/5 rounded-t-[100%]"></div>

                <!-- ICON MỜ -->
                <div class="absolute right-6 bottom-6 text-white/10 text-6xl">
                    <i class="fas fa-leaf"></i>
                </div>
                
                <!-- CONTENT -->
                <div class="relative z-10">
                    <h2 class="text-3xl font-black mb-4 leading-snug">
                        Bắt đầu hành trình sức khỏe
                    </h2>

                    <p class="text-green-100 font-medium leading-relaxed">
                        Tham gia cùng cộng đồng NutriAI để nhận lộ trình dinh dưỡng cá nhân hóa ngay hôm nay.
                    </p>
                </div>

            </div>

            <!-- RIGHT -->
            <div class="p-8 md:p-10">

                <h1 class="text-2xl font-black text-slate-800 mb-2">Tạo tài khoản mới</h1>
                <p class="text-slate-400 text-sm mb-6">Vui lòng điền đầy đủ thông tin bên dưới</p>

                <?php if (isset($error)): ?>
                    <div class="mb-4 flex items-center gap-3 rounded-[14px] border border-red-200 bg-red-50 px-5 py-4 text-sm font-extrabold text-red-700">
                        <span class="inline-flex h-[18px] w-[18px] items-center justify-center rounded-full bg-red-600 text-[10px] text-white"><i class="fa fa-xmark"></i></span>
                        <?php echo $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?controller=user&action=register" class="space-y-5">

                    <!-- NAME + PHONE -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-400">HỌ TÊN</label>
                            <input type="text" name="name" placeholder="Nguyễn Văn A" required
                                class="w-full mt-2 p-4 bg-slate-50 rounded-xl border outline-none focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-400">SĐT</label>
                            <input type="text" name="phone" placeholder="09xxxxxxxx" required
                                class="w-full mt-2 p-4 bg-slate-50 rounded-xl border outline-none focus:border-emerald-500">
                        </div>
                    </div>

                    <!-- EMAIL -->
                    <div>
                        <label class="text-xs font-bold text-slate-400">EMAIL</label>
                        <input type="email" name="email" placeholder="example@email.com" required
                            class="w-full mt-2 p-4 bg-slate-50 rounded-xl border outline-none focus:border-emerald-500">
                    </div>

                    <!-- PASSWORD -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-400">MẬT KHẨU</label>
                            <input type="password" name="password" required
                                class="w-full mt-2 p-4 bg-slate-50 rounded-xl border outline-none focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-400">XÁC NHẬN</label>
                            <input type="password" name="confirm_password" required
                                class="w-full mt-2 p-4 bg-slate-50 rounded-xl border outline-none focus:border-emerald-500">
                        </div>
                    </div>

                    <!-- BUTTON -->
                    <button type="submit"
                        class="w-full py-4 bg-slate-800 text-white rounded-xl font-bold 
                               hover:bg-black hover:scale-[1.02] active:scale-95 transition">
                        Tạo tài khoản →
                    </button>

                </form>

                <!-- LOGIN -->
                <p class="text-center text-sm text-slate-400 mt-6">
                    Đã có tài khoản?
                    <a href="index.php?controller=user&action=login"
                       class="text-emerald-600 font-bold hover:underline">
                       Đăng nhập ngay
                    </a>
                </p>

            </div>
        </div>

    </main>

    <!-- FOOTER -->
    <?php require "app/views/shares/footer.php"; ?>

</body>
</html>
