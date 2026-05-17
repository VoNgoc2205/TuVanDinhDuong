<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lấy dữ liệu từ Session để dùng chung cho toàn bộ layout
$userName = $_SESSION["user"]["ten"] ?? "Khách";
$userRole = $_SESSION["user"]["vai_tro"] ?? "user";
$userAvatar = $_SESSION["user"]["avatar"] ?? "default.jpg";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#10b981">
    <meta name="application-name" content="NutriAI">
    <meta name="apple-mobile-web-app-title" content="NutriAI">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="icon" type="image/svg+xml" href="public/icons/nutriai-logo.svg">
    <link rel="apple-touch-icon" href="public/icons/nutriai-logo-192.png">
    <link rel="manifest" href="public/manifest.webmanifest">
    <title>NutriAI - Hệ thống dinh dưỡng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background:
                radial-gradient(circle at 16% 12%, rgba(16, 185, 129, .10), transparent 28%),
                radial-gradient(circle at 86% 18%, rgba(59, 130, 246, .07), transparent 30%),
                linear-gradient(180deg, #f8fafc 0%, #f1f5f9 45%, #eefdf6 100%);
            margin: 0;
            font-size: 16px;
        }
        body, button, input, select, textarea, table {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        p, li, td, th, label, input, select, textarea, button, a {
            line-height: 1.55;
        }
        .text-xs {
            font-size: 0.92rem !important;
        }
        .text-sm {
            font-size: 1rem !important;
        }
        .text-base {
            font-size: 1.05rem !important;
        }
        .text-\[10px\],
        .text-\[11px\] {
            font-size: 0.88rem !important;
        }
        .text-\[12px\] {
            font-size: 0.94rem !important;
        }
        .uppercase[class*="tracking"] {
            font-size: 0.92rem !important;
        }
        input, select, textarea, button {
            font-size: 1rem !important;
        }
        td, th {
            font-size: 1rem;
        }
        main h1 {
            font-size: 2.35rem !important;
            line-height: 1.15 !important;
            font-weight: 900 !important;
            letter-spacing: -0.01em;
            color: #0f172a;
        }
        @media (max-width: 768px) {
            main h1 {
                font-size: 2rem !important;
            }
        }
        /* Tùy chỉnh thanh cuộn cho mượt */
        .app-content-shell {
            width: min(100%, 1240px);
            margin-left: auto;
            margin-right: auto;
        }
        .app-content-shell > div:first-child,
        .app-content-shell > main:first-child {
            max-width: 100% !important;
            width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        .app-content-shell > div:first-child[class*="p-"],
        .app-content-shell > div:first-child[class*="px-"],
        .app-content-shell > div:first-child[class*="py-"],
        .app-content-shell > main:first-child[class*="p-"],
        .app-content-shell > main:first-child[class*="px-"],
        .app-content-shell > main:first-child[class*="py-"] {
            padding: 0 !important;
        }
        .app-content-shell > div:first-child > div[class*="max-w-"],
        .app-content-shell > main:first-child > div[class*="max-w-"] {
            max-width: 100% !important;
            width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        @media (min-width: 1024px) {
            main.app-main {
                padding: 44px 36px !important;
            }
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        .app-sidebar-shell {
            --sidebar-width: 300px;
            width: var(--sidebar-width);
            flex: 0 0 var(--sidebar-width);
            background: #0f172a;
        }

        .mobile-sidebar-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1090;
            background: rgba(15, 23, 42, 0.52);
            backdrop-filter: blur(4px);
        }

        @media (max-width: 1023px) {
            .app-sidebar-shell {
                display: block !important;
                width: 0;
                flex-basis: 0;
                background: transparent;
            }

            .app-sidebar-shell > aside {
                display: flex !important;
                width: min(320px, calc(100vw - 24px));
                max-width: calc(100vw - 24px);
                transform: translateX(-100%);
                transition: transform 0.25s ease;
            }

            body.sidebar-open {
                overflow: hidden;
            }

            body.sidebar-open .app-sidebar-shell > aside {
                transform: translateX(0);
            }
        }

        @media (min-width: 480px) and (max-width: 1023px) {
            .app-sidebar-shell > aside {
                width: min(340px, 72vw);
            }
        }

        @media (max-width: 360px) {
            .app-sidebar-shell > aside {
                width: calc(100vw - 16px);
                max-width: calc(100vw - 16px);
            }
        }

        @media (min-width: 1024px) {
            .app-sidebar-shell > aside {
                transform: none !important;
            }
        }

        .install-shortcut-button {
            position: fixed;
            right: 18px;
            bottom: 18px;
            z-index: 2500;
            display: none;
            align-items: center;
            gap: 10px;
            border: 0;
            border-radius: 999px;
            background: #0f172a;
            color: #fff;
            padding: 13px 18px;
            font-weight: 900;
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.24);
            cursor: pointer;
        }

        .install-shortcut-button.is-visible {
            display: inline-flex;
        }

        @media (max-width: 480px) {
            .install-shortcut-button {
                right: 12px;
                bottom: 12px;
                max-width: calc(100vw - 24px);
                padding: 12px 15px;
            }
        }
    </style>
</head>
<body>

    <div class="flex min-h-screen">

        <div class="app-sidebar-shell">
            <?php include "app/views/shares/sidebar.php"; ?>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <?php
            $currentController = $_GET['controller'] ?? 'default';
            $currentAction = $_GET['action'] ?? 'index';

            // Hiển thị header chỉ trên trang chủ và các trang auth (login, register, forgot)
            $showHeader = false;
            if ($currentController === 'default' && $currentAction === 'index') {
                $showHeader = true;
            }
            if ($currentController === 'user' && in_array($currentAction, ['login', 'register', 'forgot'])) {
                $showHeader = true;
            }

            if ($showHeader) {
                include "app/views/shares/header.php";
            } else {
                // Nếu header đầy đủ không được hiển thị, vẫn hiển thị nút hamburger nhỏ trên mobile
                ?>
                <div class="lg:hidden px-4 pt-3">
                    <button id="mobile-menu-toggle" class="inline-flex items-center justify-center p-2 rounded-lg bg-slate-100 text-slate-700" aria-label="Mở menu" aria-expanded="false" aria-controls="app-mobile-sidebar">
                        <i class="fa fa-bars"></i>
                    </button>
                </div>
                <?php
            }
            ?>

            <main class="app-main p-4 lg:p-8">
                <div class="app-content-shell">
                    <div id="notification-container" class="mb-5 space-y-3"></div>
                    <?php echo $content ?? 'Chưa có nội dung'; ?>
                </div>
            </main>

            <?php include "app/views/shares/footer.php"; ?>

        </div>
    </div>
    <button type="button" id="installShortcutButton" class="install-shortcut-button" aria-label="Tạo lối tắt NutriAI">
        <i class="fa fa-mobile-screen-button"></i>
        <span>Tạo lối tắt</span>
    </button>
<style>
.app-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 16px 18px;
    border-radius: 14px;
    font-weight: 800;
    line-height: 1.45;
    border: 1px solid transparent;
    animation: alertIn 0.2s ease;
}

.app-alert-icon {
    display: inline-flex;
    width: 18px;
    height: 18px;
    border-radius: 999px;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    color: white;
    font-size: 10px;
}

.app-alert-success { background: #ecfdf5; border-color: #86efac; color: #047857; }
.app-alert-success .app-alert-icon { background: #059669; }
.app-alert-error { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
.app-alert-error .app-alert-icon { background: #dc2626; }
.app-alert-warning { background: #fffbeb; border-color: #fde68a; color: #b45309; }
.app-alert-warning .app-alert-icon { background: #d97706; }
.app-alert-info { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
.app-alert-info .app-alert-icon { background: #2563eb; }
.app-alert-message { flex: 1; font-size: 15px; }

.app-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 3000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(15, 23, 42, 0.58);
    backdrop-filter: blur(8px);
}

.app-modal {
    width: min(100%, 440px);
    border-radius: 28px;
    border: 1px solid rgba(255, 255, 255, 0.65);
    background: rgba(255, 255, 255, 0.96);
    padding: 26px;
    box-shadow: 0 30px 90px rgba(15, 23, 42, 0.24);
    animation: modalIn 0.18s ease;
}

.app-modal-icon {
    display: grid;
    width: 48px;
    height: 48px;
    place-items: center;
    border-radius: 18px;
    background: #fef2f2;
    color: #dc2626;
}

.app-modal-title {
    margin-top: 18px;
    font-size: 22px;
    font-weight: 900;
    color: #0f172a;
}

.app-modal-message {
    margin-top: 8px;
    color: #64748b;
    font-weight: 650;
    line-height: 1.6;
}

.app-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
}

.app-modal-button {
    min-width: 104px;
    border: 0;
    border-radius: 16px;
    padding: 13px 18px;
    font-weight: 900;
    cursor: pointer;
    transition: transform 0.15s ease, background 0.15s ease;
}

.app-modal-button:active {
    transform: translateY(1px);
}

.app-modal-cancel {
    background: #f1f5f9;
    color: #475569;
}

.app-modal-confirm {
    background: #dc2626;
    color: #fff;
}

@keyframes alertIn {
    from { transform: translateY(-8px); opacity: 0 }
    to { transform: translateY(0); opacity: 1 }
}

@keyframes modalIn {
    from { transform: translateY(10px) scale(0.98); opacity: 0 }
    to { transform: translateY(0) scale(1); opacity: 1 }
}
</style>

<script>
function showToast(message, type = "success") {
    const container = document.getElementById("notification-container");
    if (!container || !message) return;

    const safeType = ["success", "error", "warning", "info"].includes(type) ? type : "info";
    const icons = {
        success: "fa-check",
        error: "fa-xmark",
        warning: "fa-exclamation",
        info: "fa-info"
    };
    const alert = document.createElement("div");
    alert.className = `app-alert app-alert-${safeType}`;
    alert.innerHTML = `
        <span class="app-alert-icon"><i class="fa ${icons[safeType]}"></i></span>
        <span class="app-alert-message"></span>
    `;
    alert.querySelector(".app-alert-message").innerText = message;

    container.innerHTML = "";
    container.appendChild(alert);

    setTimeout(() => {
        alert.remove();
    }, 5000);
}

function notify(message, type = "info") {
    let text = String(message ?? "").trim();
    let toastType = type || "info";

    if (/^\u2705/u.test(text)) {
        toastType = "success";
        text = text.replace(/^\u2705\s*/u, "");
    } else if (/^\u274c/u.test(text)) {
        toastType = "error";
        text = text.replace(/^\u274c\s*/u, "");
    } else if (/^\u26a0\ufe0f?/u.test(text)) {
        toastType = "warning";
        text = text.replace(/^\u26a0\ufe0f?\s*/u, "");
    }
    showToast(text, toastType);
}

function appConfirm(message, options = {}) {
    const title = options.title || "Xác nhận thao tác";
    const confirmText = options.confirmText || "Xác nhận";
    const cancelText = options.cancelText || "Hủy";

    return new Promise(resolve => {
        const backdrop = document.createElement("div");
        backdrop.className = "app-modal-backdrop";
        backdrop.innerHTML = `
            <div class="app-modal" role="dialog" aria-modal="true">
                <div class="app-modal-icon"><i class="fa fa-triangle-exclamation"></i></div>
                <h2 class="app-modal-title"></h2>
                <p class="app-modal-message"></p>
                <div class="app-modal-actions">
                    <button type="button" class="app-modal-button app-modal-cancel"></button>
                    <button type="button" class="app-modal-button app-modal-confirm"></button>
                </div>
            </div>
        `;

        backdrop.querySelector(".app-modal-title").innerText = title;
        backdrop.querySelector(".app-modal-message").innerText = message;
        backdrop.querySelector(".app-modal-cancel").innerText = cancelText;
        backdrop.querySelector(".app-modal-confirm").innerText = confirmText;

        function close(result) {
            backdrop.remove();
            document.removeEventListener("keydown", onKeydown);
            resolve(result);
        }

        function onKeydown(event) {
            if (event.key === "Escape") close(false);
        }

        backdrop.addEventListener("click", event => {
            if (event.target === backdrop) close(false);
        });
        backdrop.querySelector(".app-modal-cancel").addEventListener("click", () => close(false));
        backdrop.querySelector(".app-modal-confirm").addEventListener("click", () => close(true));
        document.addEventListener("keydown", onKeydown);
        document.body.appendChild(backdrop);
        backdrop.querySelector(".app-modal-confirm").focus();
    });
}

document.addEventListener("click", function(event) {
    const target = event.target.closest("[data-confirm]");
    if (!target || target.dataset.confirmBypass === "1") return;

    event.preventDefault();
    event.stopPropagation();

    appConfirm(target.dataset.confirm, {
        title: target.dataset.confirmTitle || "Xác nhận thao tác",
        confirmText: target.dataset.confirmOk || "Xác nhận",
        cancelText: target.dataset.confirmCancel || "Hủy"
    }).then(ok => {
        if (!ok) return;

        if (target.dataset.confirmAction && typeof window.setAction === "function") {
            window.setAction(target.dataset.confirmAction);
        }

        if (target.tagName === "A" && target.href) {
            window.location.href = target.href;
            return;
        }

        const form = target.closest("form");
        if (form) {
            target.dataset.confirmBypass = "1";
            if (typeof form.requestSubmit === "function") {
                form.requestSubmit(target);
            } else {
                form.submit();
            }
        }
    });
}, true);

document.addEventListener("submit", function(event) {
    const form = event.target;
    if (!form.matches("[data-confirm]") || form.dataset.confirmBypass === "1") return;

    event.preventDefault();
    appConfirm(form.dataset.confirm, {
        title: form.dataset.confirmTitle || "Xác nhận thao tác",
        confirmText: form.dataset.confirmOk || "Xác nhận",
        cancelText: form.dataset.confirmCancel || "Hủy"
    }).then(ok => {
        if (!ok) return;
        form.dataset.confirmBypass = "1";
        form.submit();
    });
});

if ("serviceWorker" in navigator) {
    window.addEventListener("load", function() {
        navigator.serviceWorker.register("sw.js").catch(() => {});
    });
}

let installPromptEvent = null;
const installShortcutButton = document.getElementById("installShortcutButton");
const isIosDevice = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
const isStandaloneMode = window.matchMedia("(display-mode: standalone)").matches || window.navigator.standalone === true;

window.addEventListener("beforeinstallprompt", function(event) {
    event.preventDefault();
    installPromptEvent = event;
    installShortcutButton?.classList.add("is-visible");
});

installShortcutButton?.addEventListener("click", async function() {
    if (!installPromptEvent) {
        if (isIosDevice) {
            alert("Trên iPhone/iPad: nhấn nút Chia sẻ của Safari, sau đó chọn Thêm vào Màn hình chính để tạo lối tắt NutriAI.");
        }
        return;
    }

    installShortcutButton.classList.remove("is-visible");
    installPromptEvent.prompt();
    await installPromptEvent.userChoice.catch(() => null);
    installPromptEvent = null;
});

window.addEventListener("appinstalled", function() {
    installShortcutButton?.classList.remove("is-visible");
    installPromptEvent = null;
});

if (isIosDevice && !isStandaloneMode) {
    installShortcutButton?.classList.add("is-visible");
}

// Mobile sidebar toggle logic
(function(){
    function openSidebar() {
        document.body.classList.add('sidebar-open');
        const toggle = document.getElementById('mobile-menu-toggle');
        if (toggle) toggle.setAttribute('aria-expanded', 'true');
        // add backdrop
        if (!document.getElementById('mobile-sidebar-backdrop')) {
            const b = document.createElement('div');
            b.id = 'mobile-sidebar-backdrop';
            b.className = 'mobile-sidebar-backdrop';
            b.addEventListener('click', closeSidebar);
            document.body.appendChild(b);
        }
    }
    function closeSidebar() {
        document.body.classList.remove('sidebar-open');
        const toggle = document.getElementById('mobile-menu-toggle');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
        const b = document.getElementById('mobile-sidebar-backdrop');
        if (b) b.remove();
    }

    document.addEventListener('click', function(e){
        const t = e.target.closest('#mobile-menu-toggle');
        if (t) {
            e.preventDefault();
            if (document.body.classList.contains('sidebar-open')) closeSidebar(); else openSidebar();
        }
    });

    document.addEventListener('click', function(e){
        if (window.innerWidth >= 1024) return;
        if (e.target.closest('.app-sidebar-nav a')) closeSidebar();
    });

    window.addEventListener('resize', function(){
        if (window.innerWidth >= 1024) closeSidebar();
    });

    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
            closeSidebar();
        }
    });
})();

<?php if (!empty($_SESSION['flash'])): ?>
document.addEventListener("DOMContentLoaded", function() {
    notify(
        <?= json_encode($_SESSION['flash']['message'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
        <?= json_encode($_SESSION['flash']['type'] ?? 'success', JSON_UNESCAPED_UNICODE) ?>
    );
});
<?php unset($_SESSION['flash']); ?>
<?php endif; ?>
</script>
    </body>
</html>
