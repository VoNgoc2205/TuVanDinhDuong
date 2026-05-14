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
                    <?php echo $content ?? 'Chưa có nội dung'; ?>
                </div>
            </main>

        </div>
    </div>
<!-- TOAST CONTAINER -->
<div id="toast-container" class="fixed top-5 right-5 z-50 space-y-3"></div>

<style>
.toast {
    padding: 12px 18px;
    border-radius: 12px;
    color: white;
    font-weight: 600;
    min-width: 250px;
    animation: slideIn 0.3s ease;
}

.toast-success { background: #22c55e; }
.toast-error { background: #ef4444; }
.toast-warning { background: #f59e0b; }

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0 }
    to { transform: translateX(0); opacity: 1 }
}
</style>

<script>
function showToast(message, type = "success") {
    const container = document.getElementById("toast-container");

    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;
    toast.innerText = message;

    container.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
    </body>
</html>