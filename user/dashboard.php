<?php
require_once '../core/functions.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$users = readJSON('users');
$currentUser = null;
foreach ($users as $user) {
    if ($user['id'] === $_SESSION['user_id']) {
        $currentUser = $user;
        break;
    }
}

if (!$currentUser) {
    session_destroy();
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TOOLTX2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/transitions.css">
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
            backdrop-filter: blur(16px); 
            border: 1px solid rgba(255, 255, 255, 0.15); 
        }
        .vip-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid rgba(251, 191, 36, 0.2);
            position: relative;
            overflow: hidden;
        }
        .vip-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(251, 191, 36, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }
        .text-gradient {
            background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .btn-action:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <nav class="p-4 glass border-b border-white/5 flex justify-between items-center px-6 lg:px-32 sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <a href="../index.php" class="flex items-center gap-2">
                <div class="p-1.5 bg-gradient-to-br from-yellow-400 to-orange-600 rounded-xl shadow-lg shadow-orange-500/20">
                    <img src="../assets/images/logo-vip.png" alt="Logo" class="h-8 w-8 rounded-lg bg-black">
                </div>
                <span class="text-xl font-black tracking-tighter text-gradient">TOOLTX2026</span>
            </a>
        </div>
        
        <div class="flex items-center gap-4" x-data="{ open: false }">
            <button @click="open = !open" class="p-2.5 bg-slate-800/80 backdrop-blur-md rounded-xl text-slate-400 hover:bg-slate-700/80 hover:text-white transition-all border border-white/10 shadow-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-[-10px]"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-[-10px]"
                 @click.away="open = false" 
                 class="absolute right-6 top-20 w-64 bg-slate-900/95 backdrop-blur-xl rounded-[1.5rem] border border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.5)] py-3 overflow-hidden z-[60]" 
                 style="display: none;">
                <div class="px-4 py-3 border-b border-white/5 mb-2">
                    <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest">Tài khoản</p>
                    <p class="text-sm font-bold text-slate-200 truncate"><?php echo htmlspecialchars($currentUser['username']); ?></p>
                </div>
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 text-sm font-semibold transition-all">
                    <?php echo getIcon('home', 'w-5 h-5 text-yellow-500'); ?>
                    Trang chủ
                </a>
                <a href="deposit.php" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 text-sm font-semibold transition-all">
                    <?php echo getIcon('wallet', 'w-5 h-5 text-orange-500'); ?>
                    Nạp tiền
                </a>
                <a href="buy-key.php" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 text-sm font-semibold transition-all">
                    <?php echo getIcon('key', 'w-5 h-5 text-blue-500'); ?>
                    Mua Key
                </a>
                <a href="history.php" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 text-sm font-semibold transition-all border-b border-white/5">
                    <?php echo getIcon('history', 'w-5 h-5 text-purple-500'); ?>
                    Lịch sử
                </a>
                <a href="../logout.php" class="flex items-center gap-3 px-4 py-3 hover:bg-red-500/10 text-red-400 text-sm font-bold transition-all">
                    <?php echo getIcon('logout', 'w-5 h-5'); ?>
                    Đăng xuất
                </a>
            </div>
        </div>
    </nav>

        <!-- Notifications Section -->
        <div id="notifications-container" class="space-y-4 mb-8">
            <!-- Dynamic notifications will be loaded here -->
            <div class="glass p-6 rounded-[2rem] border-l-4 border-blue-500 flex items-center justify-between group">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-500/10 rounded-2xl text-blue-500">
                        <?php echo getIcon('rocket', 'w-6 h-6'); ?>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-blue-500 uppercase tracking-[0.2em] mb-1">Cập nhật hệ thống</p>
                        <p class="text-sm font-bold text-slate-200">Chào mừng bạn trở lại! Chúc bạn một ngày làm việc hiệu quả.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Info Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <!-- VIP Card -->
            <div class="lg:col-span-2 vip-card p-8 rounded-[2.5rem] shadow-2xl flex flex-col justify-between min-h-[240px]">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 text-[10px] font-black uppercase tracking-[0.3em] mb-1">Tài khoản thành viên</p>
                        <h2 class="text-3xl font-black text-slate-100 tracking-tight"><?php echo htmlspecialchars($currentUser['username']); ?></h2>
                    </div>
                    <div class="p-3 bg-yellow-500/10 rounded-2xl text-yellow-500 border border-yellow-500/20 relative z-0">
                        <?php echo getIcon('user', 'w-8 h-8'); ?>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-12 mt-8">
                    <div>
                        <p class="text-slate-500 text-[10px] font-black uppercase tracking-[0.2em] mb-1">Mã định danh ID</p>
                        <p class="text-xl font-mono font-bold text-slate-300"><?php echo $currentUser['id']; ?></p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-[10px] font-black uppercase tracking-[0.2em] mb-1">Số dư hiện tại</p>
                        <p class="text-2xl font-black text-gradient"><?php echo formatMoney($currentUser['balance']); ?></p>
                    </div>
                </div>

                <div class="absolute bottom-0 right-0 p-8 opacity-5">
                    <?php echo getIcon('shield', 'w-32 h-32'); ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 gap-4">
                <a href="deposit.php" class="glass p-6 rounded-[2rem] flex items-center gap-5 border-l-4 border-yellow-500 hover:bg-white/5 transition-all">
                    <div class="p-3 bg-yellow-500/10 rounded-2xl text-yellow-500">
                        <?php echo getIcon('wallet', 'w-6 h-6'); ?>
                    </div>
                    <div>
                        <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Nạp tiền</p>
                        <span class="text-sm font-bold text-slate-200">Thực hiện nạp ngay →</span>
                    </div>
                </a>
                <a href="buy-key.php" class="glass p-6 rounded-[2rem] flex items-center gap-5 border-l-4 border-orange-500 hover:bg-white/5 transition-all">
                    <div class="p-3 bg-orange-500/10 rounded-2xl text-orange-500">
                        <?php echo getIcon('key', 'w-6 h-6'); ?>
                    </div>
                    <div>
                        <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Mua Key</p>
                        <span class="text-sm font-bold text-slate-200">Kích hoạt Tool ngay →</span>
                    </div>
                </a>
                <a href="history.php" class="glass p-6 rounded-[2rem] flex items-center gap-5 border-l-4 border-blue-500 hover:bg-white/5 transition-all">
                    <div class="p-3 bg-blue-500/10 rounded-2xl text-blue-500">
                        <?php echo getIcon('history', 'w-6 h-6'); ?>
                    </div>
                    <div>
                        <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Lịch sử</p>
                        <span class="text-sm font-bold text-slate-200">Xem lại giao dịch →</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- Tool Section -->
        <div class="mb-12">
            <div class="flex items-center justify-between mb-8 px-2">
                <h3 class="text-2xl font-black flex items-center gap-3">
                    <span class="p-2 bg-yellow-500/10 rounded-xl text-yellow-500"><?php echo getIcon('rocket', 'w-6 h-6'); ?></span>
                    Hệ Thống Dự Đoán AI
                </h3>
                <div class="flex items-center gap-2 px-3 py-1 bg-green-500/10 rounded-full border border-green-500/20">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    <span class="text-[10px] font-black text-green-500 uppercase tracking-widest">Máy chủ ổn định</span>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Link Key Section -->
                <div class="glass p-8 rounded-[2.5rem] border border-white/5 hover:border-blue-500/20 transition-all group md:col-span-4">
                    <div class="flex justify-between items-start mb-6">
                        <div class="p-4 bg-blue-500/10 rounded-2xl text-blue-500 group-hover:bg-blue-500 group-hover:text-black transition-all">
                            <?php echo getIcon('key', 'w-8 h-8'); ?>
                        </div>
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Liên Kết Key</span>
                    </div>
                    <h4 class="text-xl font-black mb-2">Liên Kết Tài Khoản Game</h4>
                    <p class="text-sm text-slate-400 mb-6 leading-relaxed">Mỗi key chỉ có thể liên kết với một tài khoản duy nhất. Sau khi liên kết sẽ không thể thay đổi.</p>
                    
                    <div x-data="{ keyCode: '', loading: false, message: '', isError: false }" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1 mb-2 block">Mã Key</label>
                                <input type="text" x-model="keyCode" placeholder="Nhập mã key của bạn" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500/50 transition-all text-sm font-semibold">
                            </div>
                        </div>
                        
                        <div x-show="message" x-transition :class="isError ? 'text-red-400 bg-red-400/10' : 'text-green-400 bg-green-400/10'" class="p-3 rounded-xl text-xs font-bold" x-text="message"></div>

                        <button @click="
                            if(!keyCode) { message = 'Vui lòng nhập mã key'; isError = true; return; }
                            loading = true;
                            const formData = new FormData();
                            formData.append('key_code', keyCode);
                            fetch('/user/api/link-key.php', { method: 'POST', body: formData })
                            .then(r => r.json())
                            .then(data => {
                                message = data.message;
                                isError = !data.success;
                                if(data.success) { keyCode = ''; }
                            })
                            .finally(() => loading = false);
                        " :disabled="loading" class="w-full py-4 glass rounded-2xl text-sm font-black hover:bg-blue-500 hover:text-black transition-all border border-white/5 disabled:opacity-50">
                            <span x-show="!loading">LIÊN KẾT NGAY</span>
                            <span x-show="loading">ĐANG XỬ LÝ...</span>
                        </button>
                    </div>
                </div>

                <!-- Tai Xiu Tool -->
                <div class="glass p-6 rounded-[2.5rem] border border-white/5 hover:border-yellow-500/20 transition-all group">
                    <div class="w-20 h-20 mx-auto p-1 bg-gradient-to-br from-yellow-400 via-orange-500 to-yellow-600 rounded-3xl flex items-center justify-center mb-6 overflow-hidden group-hover:scale-110 group-hover:rotate-3 transition-all shadow-lg shadow-orange-500/20">
                        <div class="w-full h-full rounded-2xl overflow-hidden bg-black/50">
                            <img src="../assets/images/taixiu.png" alt="Tai Xiu Logo" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <h4 class="text-lg font-black mb-2 text-center text-gradient">Tool Tài Xỉu</h4>
                    <p class="text-[10px] text-slate-400 mb-6 text-center leading-relaxed line-clamp-2">Dự đoán kết quả theo thời gian thực với độ chính xác vượt trội.</p>
                    <button class="w-full py-3 glass rounded-xl text-xs font-black hover:bg-yellow-500 hover:text-black transition-all border border-white/5">KÍCH HOẠT</button>
                </div>

                <!-- Sicbo Tool -->
                <div class="glass p-6 rounded-[2.5rem] border border-white/5 hover:border-orange-500/20 transition-all group">
                    <div class="w-20 h-20 mx-auto p-1 bg-gradient-to-br from-yellow-400 via-orange-500 to-yellow-600 rounded-3xl flex items-center justify-center mb-6 overflow-hidden group-hover:scale-110 group-hover:rotate-3 transition-all shadow-lg shadow-orange-500/20">
                        <div class="w-full h-full rounded-2xl overflow-hidden bg-black/50">
                            <img src="../assets/images/sicbo.png" alt="Sicbo Logo" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <h4 class="text-lg font-black mb-2 text-center text-gradient">Tool Sicbo</h4>
                    <p class="text-[10px] text-slate-400 mb-6 text-center leading-relaxed line-clamp-2">Phân tích xác suất các mặt xúc xắc chuyên sâu.</p>
                    <button class="w-full py-3 glass rounded-xl text-xs font-black hover:bg-orange-500 hover:text-black transition-all border border-white/5">KÍCH HOẠT</button>
                </div>

                <!-- Xoc Dia Tool -->
                <div class="glass p-6 rounded-[2.5rem] border border-white/5 hover:border-blue-500/20 transition-all group">
                    <div class="w-20 h-20 mx-auto p-1 bg-gradient-to-br from-yellow-400 via-orange-500 to-yellow-600 rounded-3xl flex items-center justify-center mb-6 overflow-hidden group-hover:scale-110 group-hover:rotate-3 transition-all shadow-lg shadow-blue-500/20">
                        <div class="w-full h-full rounded-2xl overflow-hidden bg-black/50">
                            <img src="../assets/images/xocdia.png" alt="Xoc Dia Logo" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <h4 class="text-lg font-black mb-2 text-center text-gradient">Tool Xóc Đĩa</h4>
                    <p class="text-[10px] text-slate-400 mb-6 text-center leading-relaxed line-clamp-2">Bắt vị chẵn lẻ, tứ tử cực chuẩn với AI nhận diện cầu.</p>
                    <button class="w-full py-3 glass rounded-xl text-xs font-black hover:bg-blue-500 hover:text-black transition-all border border-white/5">KÍCH HOẠT</button>
                </div>

                <!-- Baccarat Tool -->
                <div class="glass p-6 rounded-[2.5rem] border border-white/5 hover:border-yellow-500/20 transition-all group">
                    <div class="w-20 h-20 mx-auto p-1 bg-gradient-to-br from-yellow-400 via-orange-500 to-yellow-600 rounded-3xl flex items-center justify-center mb-6 overflow-hidden group-hover:scale-110 group-hover:rotate-3 transition-all shadow-lg shadow-orange-500/20">
                        <div class="w-full h-full rounded-2xl overflow-hidden bg-black/50">
                            <img src="../assets/images/baccarat-logo.png" alt="Baccarat Logo" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <h4 class="text-lg font-black mb-2 text-center text-gradient">Tool Baccarat</h4>
                    <p class="text-[10px] text-slate-400 mb-6 text-center leading-relaxed line-clamp-2">Hỗ trợ soi cầu Player/Banker và quản lý vốn thông minh.</p>
                    <button class="w-full py-3 glass rounded-xl text-xs font-black hover:bg-yellow-500 hover:text-black transition-all border border-white/5">KÍCH HOẠT</button>
                </div>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="glass p-8 rounded-[2.5rem] border border-white/5 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-500/10 rounded-2xl text-blue-500">
                    <?php echo getIcon('shield', 'w-6 h-6'); ?>
                </div>
                <div>
                    <p class="text-sm font-bold">Bảo mật dữ liệu 100%</p>
                    <p class="text-xs text-slate-500">Hệ thống mã hóa JSON an toàn tuyệt đối</p>
                </div>
            </div>
            <div class="flex gap-4">
                <a href="#" class="p-3 glass rounded-xl hover:bg-white/5 transition-all"><?php echo getIcon('settings', 'w-5 h-5'); ?></a>
                <a href="#" class="p-3 glass rounded-xl hover:bg-white/5 transition-all"><?php echo getIcon('user', 'w-5 h-5'); ?></a>
            </div>
        </div>
    </main>
    <script>
    // Load notifications from server
    async function loadNotifications() {
        try {
            const response = await fetch('/user/api/get-notifications.php');
            const data = await response.json();
            if (data.success && data.notifications.length > 0) {
                const container = document.getElementById('notifications-container');
                data.notifications.slice(0, 3).forEach(notif => {
                    const notifEl = document.createElement('div');
                    notifEl.className = 'glass p-4 rounded-2xl border-l-4 border-l-green-500 flex justify-between items-center';
                    notifEl.innerHTML = `
                        <div>
                            <p class="font-bold text-green-400">${notif.title}</p>
                            <p class="text-sm text-slate-400">${notif.message}</p>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-slate-500 hover:text-white">✕</button>
                    `;
                    container.appendChild(notifEl);
                });
            }
        } catch (error) {
            console.log('Notification load error:', error);
        }
    }
    
    // Load on page load
    window.addEventListener('load', loadNotifications);
    // Refresh every 10 seconds
    setInterval(loadNotifications, 10000);
    </script>
    <script src="../assets/js/transitions.js"></script>
    <script src="../assets/js/security.js"></script>
</body>
</html>
