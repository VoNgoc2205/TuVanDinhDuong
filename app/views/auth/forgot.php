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
    <title>Quên mật khẩu | NutriAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex flex-col">

    <?php require "app/views/shares/header.php"; ?>

    <main class="flex-grow flex items-center justify-center px-4 py-12">
        
        <div class="max-w-[450px] w-full">
            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/60 overflow-hidden border border-slate-100">
                
                <div class="bg-slate-900 p-8 text-center relative overflow-hidden">
                    <div class="absolute top-[-50%] left-[-10%] w-32 h-32 bg-emerald-500/20 rounded-full blur-3xl"></div>
                    
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-500 rounded-2xl text-white text-2xl mb-4 shadow-lg shadow-emerald-500/30 relative z-10">
                        <i class="fa fa-key"></i>
                    </div>
                    <h1 class="text-2xl font-black text-white relative z-10 tracking-tight">Quên mật khẩu?</h1>
                    <p class="text-slate-400 text-sm mt-2 relative z-10">Đừng lo lắng, chúng tôi sẽ giúp bạn lấy lại quyền truy cập.</p>
                </div>

                <div class="p-8 md:p-10">
                    
                    <?php if (isset($error)): ?>
                        <div class="mb-6 flex items-center gap-3 rounded-[14px] border border-red-200 bg-red-50 px-5 py-4 text-sm font-extrabold text-red-700">
                            <span class="inline-flex h-[18px] w-[18px] items-center justify-center rounded-full bg-red-600 text-[10px] text-white"><i class="fa fa-xmark"></i></span>
                            <span><?php echo $error ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($success)): ?>
                        <div class="mb-6 flex items-center gap-3 rounded-[14px] border border-green-300 bg-green-50 px-5 py-4 text-sm font-extrabold text-emerald-700">
                            <span class="inline-flex h-[18px] w-[18px] items-center justify-center rounded-full bg-emerald-600 text-[10px] text-white"><i class="fa fa-check"></i></span>
                            <span><?php echo $success ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-5">
                        <div class="space-y-2">
                            <label class="text-xs font-black text-slate-400 uppercase tracking-[2px] ml-1">
    Email hoặc số điện thoại
</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" name="account" placeholder="Email hoặc số điện thoại" required
    class="w-full pl-11 pr-4 py-4 bg-slate-50 border-2 border-transparent focus:border-emerald-500 focus:bg-white rounded-2xl font-bold transition-all outline-none text-slate-700">
                            </div>
                        </div>

                        <button class="w-full py-4 bg-emerald-500 text-white rounded-2xl font-black hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-200 transition-all flex items-center justify-center gap-3">
                            <i class="fa fa-paper-plane text-xs"></i> Gửi link đặt lại mật khẩu
                        </button>
                    </form>

                    <div class="mt-8 pt-8 border-t border-slate-100 text-center">
                        <a href="index.php?controller=user&action=login" class="text-sm font-bold text-slate-400 hover:text-emerald-600 transition-colors inline-flex items-center gap-2">
                            <i class="fa fa-arrow-left text-xs"></i> Quay lại đăng nhập
                        </a>
                    </div>
                </div>
            </div>

            <p class="text-center text-slate-400 text-xs mt-8 font-medium">
                Bạn cần hỗ trợ thêm? <a href="#" class="text-emerald-500 font-bold hover:underline">Liên hệ bộ phận CSKH</a>
            </p>
        </div>

    </main>

    <?php require "app/views/shares/footer.php"; ?>

</body>
</html>
