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
    <title>NutriAI - Hệ thống dinh dưỡng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #F8FAFC; 
            margin: 0;
        }
        /* Tùy chỉnh thanh cuộn cho mượt */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        
    </style>
</head>
<body class="bg-[#F8FAFC]">

    <div class="flex h-screen overflow-hidden">

        <aside class="w-[320px] h-full bg-white border-r border-slate-100 flex-shrink-0 hidden md:block">
            <?php include "app/views/shares/sidebar.php"; ?>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
            
            

            <main class="flex-1 overflow-y-auto p-4 lg:p-8">
                <div class="max-w-[1400px] mx-auto">
                    <div id="notification-container" class="mb-5 space-y-3"></div>
                    <?php echo $content ?? 'Chưa có nội dung'; ?>
                </div>
            </main>

        </div>
    </div>
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

@keyframes alertIn {
    from { transform: translateY(-8px); opacity: 0 }
    to { transform: translateY(0); opacity: 1 }
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
