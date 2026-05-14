<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header class="w-full bg-white border-b border-slate-100">
    <div class="w-full flex items-center justify-between px-8 py-4">

        <!-- LOGO (TRÁI HẲN) -->
        <a href="index.php?controller=default&action=index"
   class="flex items-center gap-3">
            <div class="w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center">
                <i class="fas fa-leaf text-white"></i>
            </div>
            <span class="text-lg font-black text-slate-800">
                Nutri<span class="text-green-500">AI</span>
            </span>
        </a>

        <!-- RIGHT (PHẢI HẲN) -->
        <div class="flex items-center gap-4 ml-auto">

            

                <a href="index.php?controller=user&action=login"
                   class="text-sm font-bold text-slate-600 hover:text-green-600">
                   Đăng nhập
                </a>

                <a href="index.php?controller=user&action=register"
                   class="px-5 py-2.5 bg-slate-800 text-white rounded-xl font-bold text-sm hover:bg-black transition">
                   Bắt đầu ngay
                </a>

            
        </div>

    </div>
</header>