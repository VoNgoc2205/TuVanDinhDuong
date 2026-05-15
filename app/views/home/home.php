<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriAI - Hệ thống tư vấn dinh dưỡng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
        }

        .system-page-bg {
            background:
                radial-gradient(circle at 16% 12%, rgba(16, 185, 129, .12), transparent 28%),
                radial-gradient(circle at 86% 18%, rgba(59, 130, 246, .08), transparent 30%),
                linear-gradient(180deg, #f8fafc 0%, #f1f5f9 45%, #eefdf6 100%);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .falling-pattern {
            position: fixed;
            inset: 66px 0 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .falling-pattern span {
            position: absolute;
            top: -80px;
            display: flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.72);
            color: rgba(16, 185, 129, 0.72);
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
            animation: nutrientFall linear infinite;
        }

        .falling-pattern span:nth-child(1) { left: 6%; animation-duration: 18s; animation-delay: -7s; }
        .falling-pattern span:nth-child(2) { left: 14%; animation-duration: 24s; animation-delay: -14s; color: rgba(249, 115, 22, 0.62); }
        .falling-pattern span:nth-child(3) { left: 23%; animation-duration: 20s; animation-delay: -3s; transform: scale(.82); }
        .falling-pattern span:nth-child(4) { left: 35%; animation-duration: 27s; animation-delay: -18s; color: rgba(59, 130, 246, 0.58); }
        .falling-pattern span:nth-child(5) { left: 47%; animation-duration: 22s; animation-delay: -11s; transform: scale(.9); }
        .falling-pattern span:nth-child(6) { left: 58%; animation-duration: 26s; animation-delay: -5s; color: rgba(245, 158, 11, 0.62); }
        .falling-pattern span:nth-child(7) { left: 69%; animation-duration: 19s; animation-delay: -13s; }
        .falling-pattern span:nth-child(8) { left: 78%; animation-duration: 25s; animation-delay: -8s; color: rgba(59, 130, 246, 0.55); }
        .falling-pattern span:nth-child(9) { left: 88%; animation-duration: 21s; animation-delay: -16s; color: rgba(249, 115, 22, 0.6); }
        .falling-pattern span:nth-child(10) { left: 95%; animation-duration: 28s; animation-delay: -2s; transform: scale(.78); }
        .falling-pattern span:nth-child(11) { left: 2%; animation-duration: 23s; animation-delay: -19s; transform: scale(.68); color: rgba(16, 185, 129, 0.5); }
        .falling-pattern span:nth-child(12) { left: 18%; animation-duration: 17s; animation-delay: -9s; transform: scale(.72); color: rgba(59, 130, 246, 0.48); }
        .falling-pattern span:nth-child(13) { left: 31%; animation-duration: 29s; animation-delay: -24s; transform: scale(.65); color: rgba(245, 158, 11, 0.5); }
        .falling-pattern span:nth-child(14) { left: 42%; animation-duration: 16s; animation-delay: -6s; transform: scale(.7); color: rgba(249, 115, 22, 0.52); }
        .falling-pattern span:nth-child(15) { left: 53%; animation-duration: 18s; animation-delay: -21s; transform: scale(.62); color: rgba(16, 185, 129, 0.52); }
        .falling-pattern span:nth-child(16) { left: 64%; animation-duration: 30s; animation-delay: -15s; transform: scale(.74); color: rgba(59, 130, 246, 0.46); }
        .falling-pattern span:nth-child(17) { left: 73%; animation-duration: 15s; animation-delay: -4s; transform: scale(.6); color: rgba(245, 158, 11, 0.5); }
        .falling-pattern span:nth-child(18) { left: 84%; animation-duration: 20s; animation-delay: -12s; transform: scale(.7); color: rgba(16, 185, 129, 0.5); }
        .falling-pattern span:nth-child(19) { left: 91%; animation-duration: 18s; animation-delay: -20s; transform: scale(.58); color: rgba(249, 115, 22, 0.48); }
        .falling-pattern span:nth-child(20) { left: 99%; animation-duration: 24s; animation-delay: -10s; transform: scale(.66); color: rgba(59, 130, 246, 0.44); }

        @keyframes nutrientFall {
            0% {
                transform: translate3d(0, -90px, 0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: .85;
            }
            50% {
                transform: translate3d(32px, 50vh, 0) rotate(160deg);
            }
            100% {
                transform: translate3d(-24px, calc(100vh + 110px), 0) rotate(360deg);
                opacity: 0;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .falling-pattern span {
                animation: none;
                opacity: .18;
            }
        }
    </style>
</head>

<body class="system-page-bg min-h-screen flex flex-col">

    <?php require "app/views/shares/header.php"; ?>

    <div class="falling-pattern" aria-hidden="true">
        <span><i class="fa fa-leaf"></i></span>
        <span><i class="fa fa-carrot"></i></span>
        <span><i class="fa fa-apple-whole"></i></span>
        <span><i class="fa fa-droplet"></i></span>
        <span><i class="fa fa-seedling"></i></span>
        <span><i class="fa fa-bowl-food"></i></span>
        <span><i class="fa fa-lemon"></i></span>
        <span><i class="fa fa-heart-pulse"></i></span>
        <span><i class="fa fa-utensils"></i></span>
        <span><i class="fa fa-wheat-awn"></i></span>
        <span><i class="fa fa-leaf"></i></span>
        <span><i class="fa fa-droplet"></i></span>
        <span><i class="fa fa-bowl-rice"></i></span>
        <span><i class="fa fa-carrot"></i></span>
        <span><i class="fa fa-seedling"></i></span>
        <span><i class="fa fa-heart"></i></span>
        <span><i class="fa fa-lemon"></i></span>
        <span><i class="fa fa-apple-whole"></i></span>
        <span><i class="fa fa-utensils"></i></span>
        <span><i class="fa fa-droplet"></i></span>
    </div>

    <main class="relative z-10 flex-grow flex flex-col items-center justify-center px-4 py-12">

        <div class="max-w-4xl text-center space-y-6 mb-16">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold tracking-wide uppercase mb-4 animate-bounce">
                <i class="fa fa-sparkles"></i> Sức mạnh từ Trí tuệ nhân tạo
            </div>
            <h1 class="text-4xl md:text-6xl font-black text-slate-900 tracking-tight leading-[1.1]">
                Nutri<span class="text-emerald-500">AI</span> - Cá nhân hóa <br> dinh dưỡng của bạn
            </h1>
            <p class="text-lg md:text-xl text-slate-500 max-w-2xl mx-auto leading-relaxed">
                Theo dõi chế độ ăn uống, phân tích thực phẩm và nhận gợi ý thực đơn thông minh được thiết kế riêng cho cơ thể bạn.
            </p>
        </div>

        <div class="max-w-[1200px] w-full grid grid-cols-1 md:grid-cols-3 gap-8">

            <div class="group bg-white p-8 rounded-[2.5rem] shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 border border-slate-100 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-[2rem] bg-blue-50 flex items-center justify-center text-blue-500 text-3xl mb-6 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                    <i class="fa fa-user-astronaut"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800 mb-3">Tài khoản cá nhân</h3>
                <p class="text-slate-500 text-sm leading-relaxed mb-8 flex-grow">
                    Quản lý hồ sơ dinh dưỡng, lưu trữ lịch sử cân nặng và chỉ số sinh học của riêng bạn.
                </p>
                <a href="index.php?controller=user&action=register"
                    class="w-full py-4 bg-slate-900 text-white rounded-2xl font-bold hover:bg-blue-600 shadow-lg shadow-slate-200 transition-all flex items-center justify-center gap-2">
                    Bắt đầu ngay <i class="fa fa-arrow-right text-xs"></i>
                </a>
            </div>

            <div class="group bg-slate-900 p-8 rounded-[2.5rem] shadow-2xl hover:-translate-y-2 transition-all duration-300 flex flex-col items-center text-center relative overflow-hidden">
                <div class="absolute top-[-20%] right-[-10%] w-32 h-32 bg-emerald-500/20 rounded-full blur-3xl"></div>

                <div class="w-20 h-20 rounded-[2rem] bg-emerald-500 flex items-center justify-center text-white text-3xl mb-6 shadow-lg shadow-emerald-500/20">
                    <i class="fa fa-robot"></i>
                </div>
                <h3 class="text-xl font-black text-white mb-3">Khám phá AI</h3>
                <p class="text-slate-400 text-sm leading-relaxed mb-8 flex-grow">
                    Nhận diện món ăn qua hình ảnh và trò chuyện với chuyên gia AI để hiểu rõ giá trị dinh dưỡng.
                </p>
                <a href="index.php?controller=chatbox&action=index"
                    class="w-full py-4 bg-emerald-500 text-white rounded-2xl font-bold hover:bg-emerald-400 transition-all flex items-center justify-center gap-2">
                    Trải nghiệm AI <i class="fa fa-wand-magic-sparkles text-xs"></i>
                </a>
            </div>

            <div class="group bg-white p-8 rounded-[2.5rem] shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 border border-slate-100 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-[2rem] bg-orange-50 flex items-center justify-center text-orange-500 text-3xl mb-6 group-hover:bg-orange-500 group-hover:text-white transition-colors">
                    <i class="fa fa-utensils"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800 mb-3">Phân tích dinh dưỡng</h3>
                <p class="text-slate-500 text-sm leading-relaxed mb-8 flex-grow">
                    Theo dõi và đánh giá thành phần dinh dưỡng của bữa ăn như calo, protein, carbohydrate và chất béo để hỗ trợ xây dựng chế độ ăn uống hợp lý.
                </p>
                <a href="index.php?controller=food&action=input"
                    class="w-full py-4 bg-slate-50 text-slate-800 border-2 border-slate-100 rounded-2xl font-bold hover:bg-orange-50 hover:border-orange-200 hover:text-orange-600 transition-all flex items-center justify-center gap-2">
                    Nhận diện món ăn
                </a>
            </div>

        </div>

        <div class="mt-20 flex flex-wrap justify-center gap-12 border-t border-slate-200 pt-12 w-full max-w-4xl">
            <div class="text-center">
                <p class="text-3xl font-black text-slate-800">10k+</p>
                <p class="text-xs text-slate-400 uppercase font-black tracking-widest mt-1">Món ăn AI nhận diện</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-black text-slate-800">98%</p>
                <p class="text-xs text-slate-400 uppercase font-black tracking-widest mt-1">Chính xác dinh dưỡng</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-black text-slate-800">24/7</p>
                <p class="text-xs text-slate-400 uppercase font-black tracking-widest mt-1">Hỗ trợ bởi AI</p>
            </div>
        </div>

    </main>

    <?php require "app/views/shares/footer.php"; ?>

</body>

</html>
