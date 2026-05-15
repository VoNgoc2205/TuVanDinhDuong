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
        body { font-family: 'Plus Jakarta Sans', sans-serif; overflow-x: hidden; }

        .system-page-bg,
        .auth-bg {
            background:
                radial-gradient(circle at 16% 12%, rgba(16, 185, 129, .12), transparent 28%),
                radial-gradient(circle at 86% 18%, rgba(59, 130, 246, .08), transparent 30%),
                linear-gradient(180deg, #f8fafc 0%, #f1f5f9 45%, #eefdf6 100%);
        }

        .auth-fall {
            position: fixed;
            inset: 68px 0 72px;
            pointer-events: none;
            overflow: hidden;
            z-index: 0;
        }

        .auth-fall span {
            position: absolute;
            top: -70px;
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 16px;
            background: rgba(255, 255, 255, .78);
            color: rgba(16, 185, 129, .62);
            box-shadow: 0 18px 50px rgba(15, 23, 42, .08);
            animation: authFall linear infinite;
        }

        .auth-fall span:nth-child(1) { left: 5%; animation-duration: 18s; animation-delay: -8s; }
        .auth-fall span:nth-child(2) { left: 14%; animation-duration: 23s; animation-delay: -15s; color: rgba(249, 115, 22, .58); }
        .auth-fall span:nth-child(3) { left: 26%; animation-duration: 20s; animation-delay: -4s; transform: scale(.8); }
        .auth-fall span:nth-child(4) { left: 39%; animation-duration: 25s; animation-delay: -18s; color: rgba(59, 130, 246, .52); }
        .auth-fall span:nth-child(5) { left: 51%; animation-duration: 17s; animation-delay: -11s; transform: scale(.72); }
        .auth-fall span:nth-child(6) { left: 63%; animation-duration: 27s; animation-delay: -6s; color: rgba(245, 158, 11, .56); }
        .auth-fall span:nth-child(7) { left: 74%; animation-duration: 19s; animation-delay: -13s; }
        .auth-fall span:nth-child(8) { left: 85%; animation-duration: 24s; animation-delay: -9s; color: rgba(59, 130, 246, .48); }
        .auth-fall span:nth-child(9) { left: 93%; animation-duration: 21s; animation-delay: -16s; color: rgba(249, 115, 22, .52); }

        @keyframes authFall {
            0% { transform: translate3d(0, -90px, 0) rotate(0deg); opacity: 0; }
            12% { opacity: .9; }
            50% { transform: translate3d(28px, 48vh, 0) rotate(150deg); }
            100% { transform: translate3d(-22px, calc(100vh + 100px), 0) rotate(360deg); opacity: 0; }
        }

        @media (prefers-reduced-motion: reduce) {
            .auth-fall span { animation: none; opacity: .18; }
        }
    </style>
</head>

<body class="system-page-bg min-h-screen flex flex-col">
    <?php require "app/views/shares/header.php"; ?>

    <div class="auth-fall" aria-hidden="true">
        <span><i class="fa fa-leaf"></i></span>
        <span><i class="fa fa-carrot"></i></span>
        <span><i class="fa fa-apple-whole"></i></span>
        <span><i class="fa fa-droplet"></i></span>
        <span><i class="fa fa-seedling"></i></span>
        <span><i class="fa fa-bowl-food"></i></span>
        <span><i class="fa fa-heart-pulse"></i></span>
        <span><i class="fa fa-utensils"></i></span>
        <span><i class="fa fa-lemon"></i></span>
    </div>

    <main class="relative z-10 flex-grow flex items-center justify-center px-4 py-12">
        <div class="max-w-[1120px] w-full grid overflow-hidden rounded-[2.5rem] border border-white/70 bg-white/85 shadow-2xl shadow-slate-200/80 backdrop-blur md:grid-cols-[46%_54%]">
            <section class="relative min-h-[560px] overflow-hidden bg-slate-950 p-10 text-white">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(16,185,129,.58),transparent_28%),radial-gradient(circle_at_86%_28%,rgba(245,158,11,.25),transparent_30%)]"></div>
                <div class="absolute -bottom-28 -left-20 h-72 w-72 rounded-full bg-emerald-400/20 blur-3xl"></div>
                <div class="absolute bottom-8 right-8 text-7xl text-white/10"><i class="fa fa-seedling"></i></div>

                <div class="relative z-10 flex h-full flex-col justify-between">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-black uppercase tracking-widest text-emerald-100">
                            <i class="fa fa-heart-pulse"></i> Bắt đầu khỏe hơn
                        </span>
                        <h2 class="mt-8 text-4xl font-black leading-tight">Thiết lập hồ sơ dinh dưỡng của riêng bạn.</h2>
                        <p class="mt-4 max-w-sm text-base font-medium leading-relaxed text-emerald-50/90">
                            NutriAI ghi nhớ mục tiêu, thói quen và chỉ số của bạn để gợi ý bữa ăn thực tế hơn mỗi ngày.
                        </p>
                    </div>

                    <div class="grid gap-3">
                        <div class="rounded-3xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                            <div class="flex items-center gap-3">
                                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-400 text-slate-950">
                                    <i class="fa fa-robot"></i>
                                </div>
                                <div>
                                    <p class="font-black">AI gợi ý thực đơn</p>
                                    <p class="text-sm font-semibold text-white/60">Cá nhân hóa theo mục tiêu</p>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-white/10 p-4">
                                <p class="text-2xl font-black">24/7</p>
                                <p class="text-xs font-bold text-white/60">Tư vấn AI</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4">
                                <p class="text-2xl font-black">98%</p>
                                <p class="text-xs font-bold text-white/60">Theo dõi rõ ràng</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="p-8 md:p-12">
                <div class="mb-7">
                    <p class="text-sm font-black uppercase tracking-widest text-emerald-600">Tạo tài khoản</p>
                    <h1 class="mt-2 text-3xl font-black text-slate-900">Bắt đầu với NutriAI</h1>
                    <p class="mt-2 text-slate-500">Điền thông tin cơ bản để tạo hồ sơ cá nhân.</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="mb-4 flex items-center gap-3 rounded-[14px] border border-red-200 bg-red-50 px-5 py-4 text-sm font-extrabold text-red-700">
                        <span class="inline-flex h-[18px] w-[18px] items-center justify-center rounded-full bg-red-600 text-[10px] text-white"><i class="fa fa-xmark"></i></span>
                        <?php echo $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?controller=user&action=register" class="space-y-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-black uppercase tracking-widest text-slate-500">Họ tên</label>
                            <div class="mt-2 flex min-h-[56px] items-center rounded-2xl border border-slate-200 bg-slate-50 px-4 transition focus-within:border-emerald-400 focus-within:bg-white focus-within:ring-4 focus-within:ring-emerald-100">
                                <i class="fa fa-user text-slate-400"></i>
                                <input type="text" name="name" placeholder="Nguyễn Văn A" required class="ml-3 w-full bg-transparent font-semibold text-slate-800 outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-black uppercase tracking-widest text-slate-500">SĐT</label>
                            <div class="mt-2 flex min-h-[56px] items-center rounded-2xl border border-slate-200 bg-slate-50 px-4 transition focus-within:border-emerald-400 focus-within:bg-white focus-within:ring-4 focus-within:ring-emerald-100">
                                <i class="fa fa-phone text-slate-400"></i>
                                <input type="text" name="phone" placeholder="09xxxxxxxx" required class="ml-3 w-full bg-transparent font-semibold text-slate-800 outline-none">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-black uppercase tracking-widest text-slate-500">Email</label>
                        <div class="mt-2 flex min-h-[56px] items-center rounded-2xl border border-slate-200 bg-slate-50 px-4 transition focus-within:border-emerald-400 focus-within:bg-white focus-within:ring-4 focus-within:ring-emerald-100">
                            <i class="fa fa-envelope text-slate-400"></i>
                            <input type="email" name="email" placeholder="example@email.com" required class="ml-3 w-full bg-transparent font-semibold text-slate-800 outline-none">
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-black uppercase tracking-widest text-slate-500">Mật khẩu</label>
                            <div class="mt-2 flex min-h-[56px] items-center rounded-2xl border border-slate-200 bg-slate-50 px-4 transition focus-within:border-emerald-400 focus-within:bg-white focus-within:ring-4 focus-within:ring-emerald-100">
                                <i class="fa fa-lock text-slate-400"></i>
                                <input type="password" name="password" required class="ml-3 w-full bg-transparent font-semibold text-slate-800 outline-none">
                                <button type="button" class="toggle-password ml-3 grid h-9 w-9 shrink-0 place-items-center text-slate-400 transition hover:text-emerald-600" aria-label="Hiện mật khẩu">
                                    <i class="fa fa-eye pointer-events-none"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-black uppercase tracking-widest text-slate-500">Xác nhận</label>
                            <div class="mt-2 flex min-h-[56px] items-center rounded-2xl border border-slate-200 bg-slate-50 px-4 transition focus-within:border-emerald-400 focus-within:bg-white focus-within:ring-4 focus-within:ring-emerald-100">
                                <i class="fa fa-shield-heart text-slate-400"></i>
                                <input type="password" name="confirm_password" required class="ml-3 w-full bg-transparent font-semibold text-slate-800 outline-none">
                                <button type="button" class="toggle-password ml-3 grid h-9 w-9 shrink-0 place-items-center text-slate-400 transition hover:text-emerald-600" aria-label="Hiện mật khẩu">
                                    <i class="fa fa-eye pointer-events-none"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full rounded-2xl bg-slate-900 py-4 font-black text-white shadow-lg shadow-slate-200 transition hover:-translate-y-0.5 hover:bg-emerald-600 active:translate-y-0">
                        Tạo tài khoản <i class="fa fa-arrow-right ml-1 text-xs"></i>
                    </button>
                </form>

                <p class="mt-7 text-center text-sm font-semibold text-slate-400">
                    Đã có tài khoản?
                    <a href="index.php?controller=user&action=login" class="font-black text-emerald-600 hover:underline">Đăng nhập ngay</a>
                </p>
            </section>
        </div>
    </main>

    <?php require "app/views/shares/footer.php"; ?>
    <script>
        document.addEventListener("click", function(e) {
            const button = e.target.closest(".toggle-password");
            if (!button) return;

            const input = button.parentElement.querySelector("input");
            const icon = button.querySelector("i");
            if (!input || !icon) return;

            const isHidden = input.type === "password";
            input.type = isHidden ? "text" : "password";
            icon.classList.toggle("fa-eye", !isHidden);
            icon.classList.toggle("fa-eye-slash", isHidden);
            button.setAttribute("aria-label", isHidden ? "Ẩn mật khẩu" : "Hiện mật khẩu");
        });
    </script>
</body>
</html>
