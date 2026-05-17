<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentController = $_GET['controller'] ?? 'default';
$currentAction = $_GET['action'] ?? 'index';
$currentUser = $_SESSION['user'] ?? null;
$dashboardUrl = (($currentUser['role'] ?? 'user') === 'admin')
    ? 'index.php?controller=admin&action=dashboard'
    : 'index.php?controller=dashboard&action=index';
?>

<style>
    .system-page-bg {
        background:
            radial-gradient(circle at 16% 12%, rgba(16, 185, 129, .12), transparent 28%),
            radial-gradient(circle at 86% 18%, rgba(59, 130, 246, .08), transparent 30%),
            linear-gradient(180deg, #f8fafc 0%, #f1f5f9 45%, #eefdf6 100%);
    }

    .system-header {
        background: rgba(255, 255, 255, .88);
        backdrop-filter: blur(16px);
    }
</style>

<header class="system-header sticky top-0 z-50 w-full border-b border-slate-200/70">
    <div class="flex w-full items-center justify-between px-5 py-4 md:px-8">
        <a href="index.php?controller=default&action=index" class="flex items-center gap-3 no-underline">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-100">
                <i class="fas fa-leaf"></i>
            </div>
            <span class="text-xl font-black tracking-tight text-slate-900">
                Nutri<span class="text-emerald-500">AI</span>
            </span>
        </a>

        <nav class="ml-auto flex items-center gap-3">
            <?php if (!empty($currentUser)): ?>
                <a href="<?= $dashboardUrl ?>" class="hidden rounded-2xl px-4 py-2.5 text-sm font-black text-slate-600 transition hover:bg-slate-100 hover:text-emerald-600 sm:inline-flex">
                    Bảng điều khiển
                </a>
                <a href="index.php?controller=user&action=logout" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-slate-200 transition hover:bg-emerald-600">
                    <i class="fa fa-right-from-bracket"></i>
                    Đăng xuất
                </a>
            <?php else: ?>
                <a href="index.php?controller=user&action=login" class="rounded-2xl px-4 py-2.5 text-sm font-black text-slate-600 transition hover:bg-slate-100 hover:text-emerald-600 <?= ($currentController === 'user' && $currentAction === 'login') ? 'bg-slate-100 text-emerald-600' : '' ?>">
                    Đăng nhập
                </a>
                <a href="index.php?controller=user&action=register" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-slate-200 transition hover:bg-emerald-600 <?= ($currentController === 'user' && $currentAction === 'register') ? 'bg-emerald-600' : '' ?>">
                    Bắt đầu ngay
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>