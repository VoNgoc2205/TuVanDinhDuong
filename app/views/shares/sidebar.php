<?php
$currentController = $_GET['controller'] ?? 'default';
$currentAction = $_GET['action'] ?? 'index';

$user = $_SESSION['user'] ?? [];
$userRole = $user['role'] ?? 'user';

$name = $user['name'] ?? 'User';
$avatar = $user['avatar'] ?? '';
$firstChar = strtoupper(mb_substr($name, 0, 1));
$avatarPath = '';
if ($avatar !== '') {
    $avatarPath = str_starts_with($avatar, 'public/')
        ? $avatar
        : "public/uploads/avatar/" . $avatar;
}
?>

<style>
    .app-sidebar-nav a {
        font-size: 1.08rem;
        font-weight: 700;
        line-height: 1.45;
    }

    .app-sidebar-nav a i {
        width: 22px;
        min-width: 22px;
        text-align: center;
        font-size: 1.12rem;
    }

    .app-sidebar-nav a span {
        font-size: inherit;
        font-weight: 700;
    }

    #app-mobile-sidebar {
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    @media (max-width: 1023px) {
        #app-mobile-sidebar .app-sidebar-inner {
            padding: clamp(20px, 5vw, 28px);
        }

        #app-mobile-sidebar .app-sidebar-logo {
            gap: clamp(12px, 3vw, 16px);
            margin-bottom: clamp(28px, 7vh, 48px);
        }

        #app-mobile-sidebar .app-sidebar-logo-icon {
            width: clamp(44px, 12vw, 54px);
            height: clamp(44px, 12vw, 54px);
        }

        #app-mobile-sidebar .app-sidebar-logo-text {
            font-size: clamp(1.35rem, 6vw, 1.75rem);
        }

        #app-mobile-sidebar .app-sidebar-nav a {
            gap: clamp(12px, 3.8vw, 16px);
            padding: clamp(12px, 3.4vw, 16px) clamp(14px, 4vw, 20px);
            font-size: clamp(0.98rem, 4.6vw, 1.08rem);
            border-radius: 14px;
        }
    }

    @media (max-height: 680px) and (max-width: 1023px) {
        #app-mobile-sidebar .app-sidebar-logo {
            margin-bottom: 22px;
        }

        #app-mobile-sidebar .app-sidebar-nav {
            gap: 4px;
        }

        #app-mobile-sidebar .app-sidebar-nav a {
            padding-top: 10px;
            padding-bottom: 10px;
        }
    }
</style>

<aside id="app-mobile-sidebar" class="fixed top-0 left-0 w-[300px] h-screen bg-slate-900 text-slate-300 z-[1100] shadow-2xl flex flex-col">

    <div class="app-sidebar-inner p-7 flex flex-col min-h-full">

        <!-- LOGO -->
        <div class="app-sidebar-logo flex items-center gap-4 mb-12 no-underline select-none">
            <div class="app-sidebar-logo-icon w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center shadow-lg shadow-green-500/20">
                <i class="fa fa-leaf text-white text-xl"></i>
            </div>
            <span class="app-sidebar-logo-text text-white font-extrabold text-2xl tracking-tight">
                Nutri<span class="text-green-500">AI</span>
            </span>
        </div>

        <!-- MENU -->
        <nav class="app-sidebar-nav space-y-2 flex-1">

            <?php if (!$user): ?>
                <!-- CHƯA LOGIN -->
                <a href="index.php"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all text-base
            <?= $currentController == '' ? 'bg-green-600 text-white shadow-lg shadow-green-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">
                    <i class="fa fa-home text-lg"></i>
                    <span class="font-semibold">Trang chủ</span>
                </a>

                <a href="index.php?controller=user&action=login"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl hover:bg-slate-800">
                    <i class="fa fa-sign-in-alt"></i>
                    Đăng nhập
                </a>

            <?php elseif ($userRole == "user"): ?>

                <a href="index.php?controller=dashboard&action=index"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all text-base
                    <?= $currentController == 'dashboard' ? 'bg-green-600 text-white shadow-lg shadow-green-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">
                    <i class="fa fa-home text-lg"></i>
                    <span class="font-semibold">Trang chủ</span>
                </a>

                <a href="index.php?controller=nutrition&action=list"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all text-base
                    <?= $currentController == 'nutrition' ? 'bg-green-600 text-white shadow-lg shadow-green-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">

                    <i class="fa fa-user-doctor text-lg"></i>
                    <span class="font-semibold">Hồ sơ dinh dưỡng</span>
                </a>

                <a href="index.php?controller=food&action=input"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all text-base
                    <?= $currentController == 'food' ? 'bg-green-600 text-white shadow-lg shadow-green-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">

                    <i class="fa fa-camera text-lg"></i>
                    <span class="font-semibold">Nhận diện món ăn</span>
                </a>

                <a href="index.php?controller=meal&action=history"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all text-base
                    <?= $currentController == 'meal' ? 'bg-green-600 text-white shadow-lg shadow-green-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">

                    <i class="fa fa-clock-rotate-left text-lg"></i>
                    <span class="font-semibold">Lịch sử bữa ăn</span>
                </a>

                
                <a href="index.php?controller=chatbox&action=chatbox"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all text-base
                    <?= $currentController == 'chatbox' ? 'bg-green-600 text-white shadow-lg shadow-green-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">

                    <i class="fa fa-robot text-lg"></i>
                    <span class="font-semibold">Chat AI dinh dưỡng</span>
                </a>

                <a href="index.php?controller=stats&action=index"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all text-base
                    <?= $currentController == 'stats' ? 'bg-green-600 text-white shadow-lg shadow-green-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">

                    <i class="fa fa-chart-line text-lg"></i>
                    <span class="font-semibold">Thống kê của tôi</span>
                </a>

                <a href="index.php?controller=feedback&action=index"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all text-base
                    <?= $currentController == 'feedback' ? 'bg-green-600 text-white shadow-lg shadow-green-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">

                    <i class="fa fa-message text-lg"></i>
                    <span class="font-semibold">Phản hồi hệ thống</span>
                </a>

                <a href="index.php?controller=user&action=setting"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all text-base
                    <?= $currentController == 'user' ? 'bg-green-600 text-white shadow-lg shadow-green-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">

                    <i class="fa fa-cog text-lg"></i>
                    <span class="font-semibold">Cài đặt</span>
                </a>

            <?php endif; ?>

            <?php if ($userRole == "admin"): ?>

                <!-- DASHBOARD -->
                <a href="index.php?controller=admin&action=dashboard"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all text-base
        <?= ($currentController == 'admin' && $currentAction == 'dashboard')
                    ? 'bg-green-600 text-white shadow-lg shadow-green-600/20'
                    : 'hover:bg-slate-800 hover:text-white' ?>">

                    <i class="fa fa-chart-line text-lg"></i>
                    <span class="font-semibold">Trang chủ</span>
                </a>

                <!-- USER -->
                <a href="index.php?controller=admin&action=user"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all
        <?= ($currentAction == 'user')
                    ? 'bg-green-600 text-white shadow-lg shadow-green-600/20'
                    : 'hover:bg-slate-800 hover:text-white' ?>">

                    <i class="fa fa-users text-lg"></i>
                    <span class="font-semibold">Quản lý người dùng</span>
                </a>

                <!-- THỐNG KÊ -->
                <a href="index.php?controller=admin&action=thongke"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all
        <?= ($currentAction == 'thongke')
                    ? 'bg-green-600 text-white shadow-lg shadow-green-600/20'
                    : 'hover:bg-slate-800 hover:text-white' ?>">

                    <i class="fa fa-chart-pie text-lg"></i>
                    <span class="font-semibold">Thống kê</span>
                </a>

                <!-- FOOD -->
                <a href="index.php?controller=admin&action=food"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all
        <?= ($currentAction == 'food')
                    ? 'bg-green-600 text-white shadow-lg shadow-green-600/20'
                    : 'hover:bg-slate-800 hover:text-white' ?>">

                    <i class="fa fa-utensils text-lg"></i>
                    <span class="font-semibold">Quản lý thực phẩm</span>
                </a>

                <!-- AI -->
                <a href="index.php?controller=admin&action=ai_management"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all
    <?= ($currentAction == 'ai_management')
                    ? 'bg-green-600 text-white shadow-lg shadow-green-600/20'
                    : 'hover:bg-slate-800 hover:text-white' ?>">

                    <i class="fa fa-robot text-lg"></i>
                    <span class="font-semibold">Quản lý AI</span>
                </a>

                <a href="index.php?controller=admin&action=feedback"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all
    <?= ($currentAction == 'feedback')
                    ? 'bg-green-600 text-white shadow-lg shadow-green-600/20'
                    : 'hover:bg-slate-800 hover:text-white' ?>">

                    <i class="fa fa-comments text-lg"></i>
                    <span class="font-semibold">Phản hồi người dùng</span>
                </a>

                <!-- SETTING -->
                <a href="index.php?controller=admin&action=setting"
                    class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all
        <?= ($currentAction == 'setting')
                    ? 'bg-green-600 text-white shadow-lg shadow-green-600/20'
                    : 'hover:bg-slate-800 hover:text-white' ?>">

                    <i class="fa fa-cog text-lg"></i>
                    <span class="font-semibold">Cài đặt</span>
                </a>

            <?php endif; ?>
        </nav>

        <!-- USER -->
        <?php if (!empty($user)): ?>
            <div class="mt-auto border-t border-slate-800 pt-6 flex items-center gap-4">

                <?php if (!empty($avatarPath) && file_exists($avatarPath)): ?>
                    <img src="<?= htmlspecialchars($avatarPath) ?>?v=<?= time() ?>"
                        class="w-10 h-10 rounded-full object-cover">
                <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center text-white font-bold">
                        <?= $firstChar ?>
                    </div>
                <?php endif; ?>

                <div class="overflow-hidden">
                    <p class="text-base font-bold text-white truncate" title="<?= htmlspecialchars($name) ?>">
                        <?= htmlspecialchars(mb_convert_case($name, MB_CASE_TITLE, "UTF-8")) ?>
                    </p>
                    <p class="text-xs text-green-500 font-medium mt-1 uppercase tracking-wider">
                        Premium
                    </p>
                </div>

                <a href="index.php?controller=user&action=logout"
                    class="ml-auto text-slate-500 hover:text-red-400 transition-colors">
                    <i class="fa fa-sign-out-alt text-sm"></i>
                </a>
            </div>
        <?php endif; ?>

    </div>
</aside>
