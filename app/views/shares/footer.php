<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<footer class="w-full bg-white border-t border-slate-100 py-6 px-8 mt-auto">
    
    <div class="w-full flex items-center justify-between">

        <!-- LEFT (SÁT TRÁI) -->
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-green-500 rounded-lg flex items-center justify-center">
                    <i class="fas fa-leaf text-[10px] text-white"></i>
                </div>
                <span class="text-slate-900 font-bold text-sm">NutriAI</span>
            </div>

            <div class="h-4 w-[1px] bg-slate-200 hidden md:block"></div>

            <p class="text-slate-400 text-xs">
                © 2026 AI Nutrition System. All rights reserved.
            </p>
        </div>

        <!-- RIGHT (SÁT PHẢI) -->
        <div class="flex items-center gap-10 ml-auto">

            <!-- MENU -->
            <div class="flex items-center gap-6 text-xs font-bold text-slate-500 uppercase tracking-widest">
                <a href="#" class="hover:text-green-600 transition">Điều khoản</a>
                <a href="#" class="hover:text-green-600 transition">Bảo mật</a>
                <a href="#" class="hover:text-green-600 transition">Hỗ trợ</a>
            </div>

            <!-- SOCIAL -->
            <div class="flex items-center gap-3">
                <a href="#" class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-green-50 hover:text-green-600 transition">
                    <i class="fab fa-facebook-f text-sm"></i>
                </a>
                <a href="#" class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-green-50 hover:text-green-600 transition">
                    <i class="fab fa-youtube text-sm"></i>
                </a>
                <a href="#" class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-green-50 hover:text-green-600 transition">
                    <i class="fab fa-instagram text-sm"></i>
                </a>
            </div>

        </div>

    </div>

</footer>