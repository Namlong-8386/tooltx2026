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
    ['name' => 'HitClub', 'image' => '../assets/images/hitclub-logo.png', 'url' => 'hitclub.php', 'status' => 'Premium', 'color' => '#1e40af'],
    ['name' => 'Sunwin', 'image' => '../assets/images/sunwin-logo.png', 'url' => 'sunwin.php', 'status' => 'VIP 1', 'color' => '#b45309'],
    ['name' => '789Club', 'image' => '../assets/images/789club-logo.png', 'url' => '789club.php', 'status' => 'Hot', 'color' => '#7e22ce'],
    ['name' => 'B52', 'image' => '../assets/images/b52-logo.png', 'url' => 'b52.php', 'status' => 'Active', 'color' => '#be123c'],
    ['name' => 'XócĐĩa88', 'image' => '../assets/images/xocdia88-logo.png', 'url' => 'xocdia88.php', 'status' => 'Hoạt động', 'color' => '#047857'],
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
        :root {
            --primary: #fbbf24;
            --primary-dark: #f59e0b;
            --accent: #f97316;
            --bg-dark: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
        }

        body {
            background-color: var(--bg-dark);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            overflow-x: hidden;
            background-image: 
                radial-gradient(at 0% 0%, rgba(251, 191, 36, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(249, 115, 22, 0.1) 0px, transparent 50%),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .main-content {
            padding: 3rem 1.5rem;
            min-height: 100vh;
        }

        .key-panel {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1), rgba(249, 115, 22, 0.1));
            border: 1px solid rgba(251, 191, 36, 0.2);
            padding: 1rem 1.5rem;
            border-radius: 24px;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        .key-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(251, 191, 36, 0.1) 0%, transparent 70%);
            animation: rotate 10s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .key-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #fbbf24, #f97316);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
            box-shadow: 0 5px 15px rgba(249, 115, 22, 0.3);
            flex-shrink: 0;
            z-index: 1;
        }

        .key-info {
            z-index: 1;
        }

        .key-label {
            font-size: 0.65rem;
            font-weight: 800;
            color: #fbbf24;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 2px;
        }

        .key-value {
            font-size: 1.5rem;
            font-weight: 900;
            color: #fff;
            line-height: 1;
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        .key-unit {
            font-size: 0.75rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
        }

        .game-card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.4), rgba(15, 23, 42, 0.6));
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-radius: 32px;
            padding: 2.5rem 2rem;
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            z-index: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
            backdrop-filter: blur(10px);
        }

        .game-card:hover {
            transform: translateY(-12px) scale(1.02);
            border-color: rgba(251, 191, 36, 0.4);
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.6), rgba(15, 23, 42, 0.8));
            box-shadow: 
                0 30px 60px -12px rgba(0, 0, 0, 0.5),
                0 0 40px -10px rgba(251, 191, 36, 0.2);
        }

        .game-logo-container {
            width: 100px;
            height: 100px;
            border-radius: 28px;
            padding: 4px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.3);
        }

        .game-logo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 18px;
        }

        .status-badge {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 4px 12px;
            border-radius: 20px;
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            background: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 10px #22c55e;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.2); }
            100% { opacity: 1; transform: scale(1); }
        }

        .btn-play {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #000;
            font-weight: 900;
            padding: 1.25rem;
            border-radius: 20px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.85rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            margin-top: auto;
            box-shadow: 0 10px 20px -5px rgba(245, 158, 11, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .game-card:hover .btn-play {
            background: #fff;
            color: #000;
            transform: scale(1.05);
            box-shadow: 0 15px 30px -10px rgba(255, 255, 255, 0.4);
            border-color: #fff;
        }

        .header-gradient {
            background: linear-gradient(135deg, #fff 0%, #fbbf24 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stats-chip {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1.5rem 1rem;
            }
            h1 {
                font-size: 2.5rem !important;
            }
        }
    </style>
</head>
<body>
    <div class="max-w-7xl mx-auto">
        <main class="main-content">
            <!-- Header Section -->
            <div class="header-section flex flex-col md:flex-row md:items-center justify-between gap-8 mb-16">
                <div class="flex-1">
                    <div class="flex items-center gap-4 mb-4 md:mb-6">
                        <a href="dashboard.php" class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-white/5 border border-white/10 rounded-xl md:rounded-2xl text-slate-400 hover:text-white hover:bg-white/10 hover:border-white/20 transition-all group">
                            <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
                            </svg>
                        </a>
                        <span class="px-3 py-1 md:px-4 md:py-1.5 bg-yellow-500/10 border border-yellow-500/20 rounded-full text-yellow-500 text-[9px] md:text-[10px] font-bold uppercase tracking-[0.2em]">Hệ thống phân tích cao cấp</span>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight mb-4 header-gradient">Chọn Game</h1>
                    <p class="text-slate-400 text-base md:text-lg max-w-2xl leading-relaxed">Hệ thống AI đang sẵn sàng. Hãy chọn cổng game để bắt đầu phân tích dữ liệu thời gian thực.</p>
                </div>

                <div class="key-panel">
                    <div class="key-icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </div>
                    <div class="key-info">
                        <p class="key-label">Thời gian còn lại</p>
                        <div class="key-value">
                            <?php 
                            if ($activeKey) {
                                $remaining = $activeKey['expiry_at'] - time();
                                if ($remaining > 86400) {
                                    echo ceil($remaining / 86400) . '<span class="key-unit">Ngày</span>';
                                } elseif ($remaining > 3600) {
                                    echo ceil($remaining / 3600) . '<span class="key-unit">Giờ</span>';
                                } else {
                                    echo ceil($remaining / 60) . '<span class="key-unit">Phút</span>';
                                }
                            } else {
                                echo '<span class="text-rose-500 text-base uppercase tracking-widest">Hết hạn</span>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Game Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8 md:gap-10">
                <?php foreach ($games as $game): ?>
                <div class="game-card group cursor-pointer" onclick="location.href='<?php echo $game['url']; ?>'">
                    <div class="flex items-start justify-between mb-6">
                        <div class="game-logo-container">
                            <img src="<?php echo $game['image']; ?>" class="group-hover:scale-110 transition-transform duration-500" alt="<?php echo $game['name']; ?>">
                        </div>
                        <div class="status-badge">
                            <div class="status-dot"></div>
                            <span><?php echo $game['status']; ?></span>
                        </div>
                    </div>

                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-2xl md:text-3xl font-extrabold text-white group-hover:text-yellow-400 transition-colors tracking-tight"><?php echo $game['name']; ?></h4>
                            <div class="flex items-center gap-1.5 px-3 py-1 bg-white/5 rounded-full border border-white/10">
                                <div class="w-1.5 h-1.5 rounded-full bg-yellow-500 animate-pulse"></div>
                                <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Live</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <div class="stats-chip flex items-center gap-2">
                                <svg class="w-3 h-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path></svg>
                                1.2k+
                            </div>
                            <div class="stats-chip flex items-center gap-2">
                                <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                98% Acc
                            </div>
                        </div>
                    </div>

                    <div class="btn-play">Kích hoạt Tool</div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Footer -->
            <footer class="mt-32 py-10 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-yellow-500/10 rounded-lg flex items-center justify-center">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full animate-ping"></div>
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
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                card.style.setProperty('--x', `${x}px`);
                card.style.setProperty('--y', `${y}px`);
            });
        });
    </script>
    <script src="../assets/js/transitions.js"></script>
</body>
</html>