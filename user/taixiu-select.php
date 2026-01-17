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
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #1e293b;
            color: #f8fafc;
            font-family: 'Space Grotesk', sans-serif;
            margin: 0;
            overflow-x: hidden;
            background-image: 
                radial-gradient(at 0% 0%, rgba(234, 179, 8, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(249, 115, 22, 0.15) 0px, transparent 50%);
        }

        .main-content {
            margin-left: 0;
            padding: 3rem;
            min-height: 100vh;
        }

        .game-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.03) 0%, rgba(255, 255, 255, 0.01) 100%);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 1.5rem;
            transition: all 0.5s cubic-bezier(0.19, 1, 0.22, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .game-card:hover {
            background: rgba(255, 255, 255, 0.07);
            border-color: rgba(251, 191, 36, 0.3);
            transform: translateY(-10px);
        }

        .game-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.05), transparent);
            transition: 0.5s;
        }

        .game-card:hover::before {
            left: 100%;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 10px #22c55e;
        }

        .btn-play {
            background: #ffffff;
            color: #000000;
            font-weight: 700;
            padding: 0.75rem;
            border-radius: 12px;
            text-align: center;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.8rem;
        }

        .game-card:hover .btn-play {
            background: #fbbf24;
        }

        @media (max-width: 1024px) {
            .main-content {
                padding: 1.5rem;
            }
        }

        .custom-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto">
        <main class="main-content">
            <!-- Back Button & Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <a href="dashboard.php" class="p-3 bg-white/5 border border-white/10 rounded-2xl text-slate-400 hover:text-yellow-500 hover:bg-white/10 transition-all group">
                            <svg class="w-6 h-6 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
                            </svg>
                        </a>
                        <p class="text-yellow-500 text-xs font-bold uppercase tracking-[0.2em]">Hệ thống phân tích cao cấp</p>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-bold tracking-tight mb-2">Chọn Game</h1>
                    <p class="text-slate-400 max-w-lg">Vui lòng chọn cổng game bạn đang chơi để hệ thống bắt đầu phân tích dữ liệu và đưa ra kết quả dự đoán chính xác.</p>
                </div>

                <div class="flex items-center gap-4 bg-white/5 p-4 rounded-2xl border border-white/5 backdrop-blur-md">
                    <div class="w-12 h-12 bg-white/5 rounded-xl flex items-center justify-center text-yellow-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-0.5">Thời gian Key còn lại</p>
                        <p class="text-xl font-bold text-white">
                            <?php 
                            if ($activeKey) {
                                $remaining = $activeKey['expiry_at'] - time();
                                if ($remaining > 86400) echo ceil($remaining / 86400) . ' <span class="text-xs font-normal text-slate-500">Ngày</span>';
                                elseif ($remaining > 3600) echo ceil($remaining / 3600) . ' <span class="text-xs font-normal text-slate-500">Giờ</span>';
                                else echo ceil($remaining / 60) . ' <span class="text-xs font-normal text-slate-500">Phút</span>';
                            } else {
                                echo '<span class="text-red-500 text-sm">Hết hạn</span>';
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6">
                <?php foreach ($games as $game): ?>
                <div class="game-card flex flex-col group" onclick="location.href='<?php echo $game['url']; ?>'">
                    <div class="flex items-start justify-between mb-6">
                        <div class="p-3 rounded-2xl bg-white/5 border border-white/5 group-hover:bg-yellow-500/10 group-hover:border-yellow-500/20 transition-all">
                            <img src="<?php echo $game['image']; ?>" class="w-12 h-12 object-contain filter grayscale group-hover:grayscale-0 transition-all duration-500" alt="">
                        </div>
                        <div class="px-3 py-1 bg-white/5 rounded-full border border-white/5 flex items-center gap-2">
                            <div class="status-dot"></div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?php echo $game['status']; ?></span>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h4 class="text-xl font-bold mb-1 text-slate-200 group-hover:text-white transition-colors"><?php echo $game['name']; ?></h4>
                        <p class="text-xs text-slate-500">Phân tích thuật toán AI chuẩn 99%</p>
                    </div>

                    <div class="btn-play">Kích hoạt ngay</div>
                </div>
                <?php endforeach; ?>
            </div>

            <footer class="mt-20 py-8 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-slate-600 text-[10px] font-bold uppercase tracking-[0.2em]">© 2026 AI SYSTEM TECHNOLOGY</p>
                <div class="flex gap-6">
                    <a href="#" class="text-slate-600 hover:text-white transition-colors text-[10px] font-bold uppercase tracking-widest">Privacy</a>
                    <a href="#" class="text-slate-600 hover:text-white transition-colors text-[10px] font-bold uppercase tracking-widest">Terms</a>
                </div>
            </footer>
        </main>
    </div>

    <script src="../assets/js/transitions.js"></script>
</body>
</html>