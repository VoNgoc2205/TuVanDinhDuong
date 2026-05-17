<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<footer class="mt-auto w-full border-t border-slate-200/70 bg-white/85 px-5 py-6 backdrop-blur md:px-8">
    <div class="flex w-full flex-col gap-5 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-wrap items-center gap-4">
            <a href="index.php?controller=default&action=index" class="flex items-center gap-2 no-underline">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-sm">
                    <i class="fas fa-leaf text-xs"></i>
                </div>
                <span class="font-black text-slate-900">Nutri<span class="text-emerald-500">AI</span></span>
            </a>
            <div class="hidden h-5 w-px bg-slate-200 md:block"></div>
            <p class="text-sm font-semibold text-slate-400">© 2026 NutriAI. Smart Nutrition for Better Health.</p>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-8">
            <nav class="flex flex-wrap items-center gap-5 text-sm font-black uppercase tracking-widest text-slate-500">
                <a href="#" class="transition hover:text-emerald-600">Điều khoản</a>
                <a href="#" class="transition hover:text-emerald-600">Bảo mật</a>
                <a href="#" class="transition hover:text-emerald-600">Hỗ trợ</a>
                <?php if (!empty($_SESSION['user']) && (($_SESSION['user']['vai_tro'] ?? $_SESSION['user']['role'] ?? '') === 'admin')): ?>
                    <a href="index.php?controller=admin&action=dashboard" class="transition hover:text-emerald-600">Quản trị</a>
                <?php endif; ?>
            </nav>

            <div class="flex items-center gap-3">
                <a href="#" class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-600" aria-label="Facebook">
                    <i class="fab fa-facebook-f text-sm"></i>
                </a>
                <a href="#" class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-600" aria-label="YouTube">
                    <i class="fab fa-youtube text-sm"></i>
                </a>
                <a href="#" class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-600" aria-label="Instagram">
                    <i class="fab fa-instagram text-sm"></i>
                </a>
            </div>
        </div>
    </div>
</footer>
