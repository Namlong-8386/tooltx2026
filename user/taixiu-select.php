<?php
require_once __DIR__ . '/../core/functions.php';

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

$keys = readJSON('keys');
$activeKey = null;
foreach ($keys as $key) {
    if ($key['user_id'] === $currentUser['id']) {
        $created_at = strtotime($key['created_at']);
        $package = $key['package_name'];
        $duration = 0;
        if (strpos($package, 'Giờ') !== false) {
            $hours = (int)filter_var($package, FILTER_SANITIZE_NUMBER_INT);
            $duration = $hours * 3600;
        } elseif (strpos($package, 'Ngày') !== false) {
            $days = (int)filter_var($package, FILTER_SANITIZE_NUMBER_INT);
            $duration = $days * 86400;
        }
        $expiry_time = $created_at + $duration;
        if (time() < $expiry_time) {
            $activeKey = $key;
            $activeKey['expiry_at'] = $expiry_time;
            break;
        }
    }
}

$games = [
    ['name' => 'Go88', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Go88', 'url' => 'go88.php', 'status' => 'Premium', 'color' => '#1e40af'],
    ['name' => 'Sunwin', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Sunwin', 'url' => 'sunwin.php', 'status' => 'VIP 1', 'color' => '#b45309'],
    ['name' => '789Club', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=789Club', 'url' => '789club.php', 'status' => 'Hot', 'color' => '#7e22ce'],
    ['name' => 'B52', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=B52', 'url' => 'b52.php', 'status' => 'Active', 'color' => '#be123c'],
    ['name' => 'Rikvip', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Rikvip', 'url' => 'rikvip.php', 'status' => 'Hoạt động', 'color' => '#047857'],
    ['name' => 'Manclub', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Manclub', 'url' => 'manclub.php', 'status' => 'Ổn định', 'color' => '#334155'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống AI Tài Xỉu - Chuyên Nghiệp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #0f172a;
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            overflow-x: hidden;
            background-image: 
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(147, 51, 234, 0.08) 0px, transparent 50%);
        }

        .main-content {
            margin-left: 0;
            padding: 2rem;
            min-height: 100vh;
        }

        .glass-panel {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .game-card {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 28px;
            padding: 1.75rem;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .game-card:hover {
            background: rgba(30, 41, 59, 0.6);
            border-color: rgba(59, 130, 246, 0.3);
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px -20px rgba(0, 0, 0, 0.5);
        }

        .game-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(600px circle at var(--x) var(--y), rgba(255,255,255,0.06), transparent 40%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .game-card:hover::after {
            opacity: 1;
        }

        .status-badge {
            padding: 4px 12px;
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            border-radius: 100px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 8px #22c55e;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }

        .btn-play {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            font-weight: 700;
            padding: 0.85rem;
            border-radius: 16px;
            text-align: center;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.75rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .game-card:hover .btn-play {
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
        }

        .header-gradient {
            background: linear-gradient(to right, #fff 20%, rgba(255,255,255,0.4) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        @media (max-width: 1024px) {
            .main-content {
                padding: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="max-w-7xl mx-auto">
        <main class="main-content">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-16">
                <div class="flex-1">
                    <div class="flex items-center gap-4 mb-6">
                        <a href="dashboard.php" class="w-12 h-12 flex items-center justify-center bg-white/5 border border-white/10 rounded-2xl text-slate-400 hover:text-white hover:bg-white/10 hover:border-white/20 transition-all group">
                            <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
                            </svg>
                        </a>
                        <span class="px-4 py-1.5 bg-blue-500/10 border border-blue-500/20 rounded-full text-blue-400 text-[10px] font-bold uppercase tracking-[0.2em]">AI Prediction System v2.0</span>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight mb-4 header-gradient">Chọn Game</h1>
                    <p class="text-slate-400 text-lg max-w-2xl leading-relaxed">Hệ thống AI đang sẵn sàng. Hãy chọn cổng game để bắt đầu phân tích dữ liệu thời gian thực và nhận kết quả chính xác nhất.</p>
                </div>

                <div class="flex items-center gap-5 glass-panel p-5 rounded-3xl border border-white/10 shadow-2xl">
                    <div class="w-14 h-14 bg-blue-500/10 rounded-2xl flex items-center justify-center text-blue-500 border border-blue-500/20">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">Thời gian Key còn lại</p>
                        <p class="text-2xl font-black text-white tracking-tight">
                            <?php 
                            if ($activeKey) {
                                $remaining = $activeKey['expiry_at'] - time();
                                if ($remaining > 86400) echo ceil($remaining / 86400) . ' <span class="text-xs font-medium text-slate-500 uppercase tracking-widest ml-1">Ngày</span>';
                                elseif ($remaining > 3600) echo ceil($remaining / 3600) . ' <span class="text-xs font-medium text-slate-500 uppercase tracking-widest ml-1">Giờ</span>';
                                else echo ceil($remaining / 60) . ' <span class="text-xs font-medium text-slate-500 uppercase tracking-widest ml-1">Phút</span>';
                            } else {
                                echo '<span class="text-rose-500 text-lg uppercase tracking-widest">Hết hạn</span>';
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Game Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <?php foreach ($games as $game): ?>
                <div class="game-card flex flex-col group" onclick="location.href='<?php echo $game['url']; ?>'">
                    <div class="flex items-start justify-between mb-8">
                        <div class="w-16 h-16 rounded-2xl bg-white/5 border border-white/5 flex items-center justify-center group-hover:bg-blue-500/10 group-hover:border-blue-500/20 transition-all duration-500 overflow-hidden">
                            <img src="<?php echo $game['image']; ?>" class="w-10 h-10 object-contain group-hover:scale-110 transition-transform duration-500" alt="<?php echo $game['name']; ?>">
                        </div>
                        <div class="status-badge">
                            <div class="status-dot"></div>
                            <span class="text-[10px] font-bold text-green-400 uppercase tracking-widest"><?php echo $game['status']; ?></span>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h4 class="text-2xl font-bold mb-2 text-white group-hover:text-blue-400 transition-colors tracking-tight"><?php echo $game['name']; ?></h4>
                        <div class="flex items-center gap-2">
                            <div class="flex -space-x-2">
                                <div class="w-5 h-5 rounded-full bg-slate-700 border border-slate-800"></div>
                                <div class="w-5 h-5 rounded-full bg-slate-600 border border-slate-800"></div>
                                <div class="w-5 h-5 rounded-full bg-slate-500 border border-slate-800"></div>
                            </div>
                            <p class="text-[11px] text-slate-500 font-medium">1.2k+ người đang dùng</p>
                        </div>
                    </div>

                    <div class="btn-play">Kích hoạt AI</div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Footer -->
            <footer class="mt-32 py-10 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-500/10 rounded-lg flex items-center justify-center">
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-ping"></div>
                    </div>
                    <p class="text-slate-500 text-[11px] font-bold uppercase tracking-[0.3em]">AI Prediction System • Secure Node</p>
                </div>
                <div class="flex gap-10">
                    <a href="#" class="text-slate-600 hover:text-white transition-colors text-[10px] font-bold uppercase tracking-widest">Analytics</a>
                    <a href="#" class="text-slate-600 hover:text-white transition-colors text-[10px] font-bold uppercase tracking-widest">Security</a>
                    <a href="#" class="text-slate-600 hover:text-white transition-colors text-[10px] font-bold uppercase tracking-widest">Terminal</a>
                </div>
            </footer>
        </main>
    </div>

    <script>
        // Spotlight effect
        document.querySelectorAll('.game-card').forEach(card => {
            card.onmousemove = e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                card.style.setProperty('--x', `${x}px`);
                card.style.setProperty('--y', `${y}px`);
            };
        });
    </script>
    <script src="../assets/js/transitions.js"></script>
</body>
</html>