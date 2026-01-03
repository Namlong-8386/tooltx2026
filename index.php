<?php
require_once 'core/functions.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TOOLTX2026 - Đỉnh Cao Dự Đoán</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/transitions.css">
    <style>
        html {
            zoom: 0.9;
        }
        body { 
            background-color: #1e293b; 
            color: #f8fafc; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: 
                radial-gradient(at 0% 0%, rgba(234, 179, 8, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(249, 115, 22, 0.15) 0px, transparent 50%);
        }
        .glass { 
            background: rgba(255, 255, 255, 0.08); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255, 255, 255, 0.15); 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
        }
        .glass-hover:hover {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
        .text-gradient {
            background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .btn-primary {
            background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%);
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3);
        }
        .btn-primary:hover {
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.5);
            transform: scale(1.02);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <nav class="sticky top-0 z-50 p-4 glass border-b border-white/5 flex justify-between items-center px-6 md:px-12">
        <div class="flex items-center gap-3">
            <div class="p-1 bg-gradient-to-br from-yellow-400 to-orange-600 rounded-xl">
                <img src="assets/images/logo-vip.png" alt="Logo" class="h-10 w-10 rounded-lg bg-black">
            </div>
            <span class="text-2xl font-extrabold tracking-tighter text-gradient">TOOLTX2026</span>
        </div>
        <div class="flex gap-6 items-center">
            <?php if (isLoggedIn()): ?>
                <a href="user/dashboard.php" class="flex items-center gap-2 text-sm font-semibold hover:text-yellow-500 transition-colors">
                    <?php echo getIcon('user', 'w-5 h-5'); ?>
                    Trang cá nhân
                </a>
                <a href="logout.php" class="text-red-400 hover:text-red-300 transition-colors">
                    <?php echo getIcon('logout', 'w-5 h-5'); ?>
                </a>
            <?php else: ?>
                <a href="login.php" class="text-sm font-semibold hover:text-yellow-500 transition-colors">Đăng nhập</a>
                <a href="register.php" class="btn-primary text-black px-6 py-2 rounded-xl font-bold text-sm transition-all">Đăng ký</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="flex-grow flex flex-col items-center justify-center p-6 text-center mt-12">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass mb-6 border border-yellow-500/20">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-yellow-500"></span>
            </span>
            <span class="text-xs font-bold text-yellow-500 uppercase tracking-widest">Hệ thống AI dự đoán 2026</span>
        </div>

        <h1 class="text-5xl md:text-7xl font-black mb-6 leading-tight">
            Làm Chủ Cuộc Chơi<br>
            <span class="text-gradient">Với TOOLTX2026</span>
        </h1>
        
        <p class="text-slate-400 max-w-2xl mb-12 text-lg leading-relaxed">
            Nền tảng cung cấp công cụ dự đoán kết quả Tài Xỉu, Sicbo, Xóc Đĩa, Baccarat hàng đầu Việt Nam. Sử dụng thuật toán AI thế hệ mới để phân tích cầu và tối ưu hóa lợi nhuận.
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 w-full max-w-7xl px-4">
            <div class="glass p-8 rounded-3xl glass-hover group">
                <div class="w-24 h-24 mx-auto p-1 bg-gradient-to-br from-yellow-400 via-orange-500 to-yellow-600 rounded-3xl flex items-center justify-center mb-6 overflow-hidden group-hover:scale-110 group-hover:rotate-3 transition-all shadow-[0_0_20px_rgba(249,115,22,0.4)]">
                    <div class="w-full h-full rounded-2xl overflow-hidden bg-black/50">
                        <img src="assets/images/taixiu.png" alt="Tai Xiu Logo" class="w-full h-full object-cover">
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-3">Tool Tài Xỉu</h3>
                <p class="text-sm text-slate-400 leading-relaxed">Dự đoán kết quả theo thời gian thực với độ chính xác vượt trội.</p>
            </div>
            <div class="glass p-8 rounded-3xl glass-hover group">
                <div class="w-24 h-24 mx-auto p-1 bg-gradient-to-br from-yellow-400 via-orange-500 to-yellow-600 rounded-3xl flex items-center justify-center mb-6 overflow-hidden group-hover:scale-110 group-hover:rotate-3 transition-all shadow-[0_0_20px_rgba(249,115,22,0.4)]">
                    <div class="w-full h-full rounded-2xl overflow-hidden bg-black/50">
                        <img src="assets/images/sicbo.png" alt="Sicbo Logo" class="w-full h-full object-cover">
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-3">Tool Sicbo</h3>
                <p class="text-sm text-slate-400 leading-relaxed">Phân tích xác suất các mặt xúc xắc chuyên sâu từ dữ liệu nhà cái.</p>
            </div>
            <div class="glass p-8 rounded-3xl glass-hover group">
                <div class="w-24 h-24 mx-auto p-1 bg-gradient-to-br from-yellow-400 via-orange-500 to-yellow-600 rounded-3xl flex items-center justify-center mb-6 overflow-hidden group-hover:scale-110 group-hover:rotate-3 transition-all shadow-[0_0_20px_rgba(249,115,22,0.4)]">
                    <div class="w-full h-full rounded-2xl overflow-hidden bg-black/50">
                        <img src="assets/images/xocdia.png" alt="Xoc Dia Logo" class="w-full h-full object-cover">
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-3">Tool Xóc Đĩa</h3>
                <p class="text-sm text-slate-400 leading-relaxed">Bắt vị chẵn lẻ, tứ tử cực chuẩn với thuật toán nhận diện cầu.</p>
            </div>
            <div class="glass p-8 rounded-3xl glass-hover group">
                <div class="w-24 h-24 mx-auto p-1 bg-gradient-to-br from-yellow-400 via-orange-500 to-yellow-600 rounded-3xl flex items-center justify-center mb-6 overflow-hidden group-hover:scale-110 group-hover:rotate-3 transition-all shadow-[0_0_20px_rgba(249,115,22,0.4)]">
                    <div class="w-full h-full rounded-2xl overflow-hidden bg-black/50">
                        <img src="assets/images/baccarat-logo.png" alt="Bacarat Logo" class="w-full h-full object-cover">
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-3">Tool Bacarat</h3>
                <p class="text-sm text-slate-400 leading-relaxed">Hỗ trợ soi cầu Player/Banker và quản lý vốn thông minh.</p>
            </div>
        </div>

        <div class="mt-16 mb-24">
            <a href="register.php" class="btn-primary text-black px-12 py-4 rounded-2xl font-extrabold text-xl transition-all inline-flex items-center gap-3">
                BẮT ĐẦU NGAY
                <?php echo getIcon('rocket', 'w-6 h-6'); ?>
            </a>
        </div>
    </main>

    <footer class="p-8 text-center text-slate-500 text-sm border-t border-white/5 glass">
        <div class="flex justify-center gap-8 mb-4">
            <a href="#" class="hover:text-yellow-500 transition-colors">Điều khoản</a>
            <a href="#" class="hover:text-yellow-500 transition-colors">Bảo mật</a>
            <a href="#" class="hover:text-yellow-500 transition-colors">Hỗ trợ</a>
        </div>
        <p>&copy; 2026 TOOLTX2026. Thiết kế bởi Manus Team.</p>
    </footer>

    <!-- Global Notification Toast -->
    <div x-data="{ show: false, title: '', message: '' }" 
         x-init="
            setTimeout(async () => {
                const r = await fetch('user/api/get-notifications.php');
                const d = await r.json();
                if(d.success && d.notifications.length > 0) {
                    const latest = d.notifications[0];
                    title = latest.title;
                    message = latest.message;
                    show = true;
                    setTimeout(() => show = false, 8000);
                }
            }, 2000)
         "
         x-show="show"
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 translate-y-10 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-10 scale-95"
         class="fixed bottom-6 right-6 z-[100] max-w-sm w-full"
         style="display: none;">
        <div class="glass p-5 rounded-[2rem] border-l-4 border-yellow-500 shadow-2xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-2">
                <button @click="show = false" class="text-slate-500 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="flex items-start gap-4">
                <div class="p-3 bg-yellow-500/10 rounded-2xl text-yellow-500 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </div>
                <div>
                    <h4 class="font-black text-sm text-yellow-500 uppercase tracking-wider mb-1" x-text="title"></h4>
                    <p class="text-xs text-slate-300 leading-relaxed" x-text="message"></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="assets/js/transitions.js"></script>
    <script src="assets/js/security.js"></script>
</body>
</html>
