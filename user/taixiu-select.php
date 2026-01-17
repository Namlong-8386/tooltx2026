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

// Lấy thông tin Key của người dùng
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
    ['name' => 'Go88', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Go88', 'url' => 'go88.php', 'status' => 'Hoạt động', 'color' => 'from-blue-600 to-indigo-600'],
    ['name' => 'Sunwin', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Sunwin', 'url' => 'sunwin.php', 'status' => 'Hoạt động', 'color' => 'from-yellow-500 to-orange-600'],
    ['name' => '789Club', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=789Club', 'url' => '789club.php', 'status' => 'Hoạt động', 'color' => 'from-purple-600 to-pink-600'],
    ['name' => 'B52', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=B52', 'url' => 'b52.php', 'status' => 'Hoạt động', 'color' => 'from-red-600 to-orange-600'],
    ['name' => 'Rikvip', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Rikvip', 'url' => 'rikvip.php', 'status' => 'Hoạt động', 'color' => 'from-emerald-600 to-teal-600'],
    ['name' => 'Manclub', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Manclub', 'url' => 'manclub.php', 'status' => 'Hoạt động', 'color' => 'from-slate-700 to-slate-900'],
];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn Game Tool Tài Xỉu - TOOLTX2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/transitions.css">
    <style>
        :root {
            --primary: #fbbf24;
            --secondary: #f97316;
        }
        body { 
            background-color: #0f172a; 
            color: #f8fafc; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background-image: 
                radial-gradient(at 0% 0%, rgba(234, 179, 8, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(249, 115, 22, 0.1) 0px, transparent 50%);
        }
        .glass-card { 
            background: rgba(30, 41, 59, 0.4); 
            backdrop-filter: blur(20px); 
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08); 
            transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
            position: relative;
            overflow: hidden;
        }
        .glass-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(251, 191, 36, 0.1), transparent 60%);
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        .glass-card:hover::before {
            opacity: 1;
        }
        .glass-card:hover {
            transform: translateY(-12px) scale(1.03);
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(251, 191, 36, 0.4);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 20px -5px rgba(251, 191, 36, 0.2);
        }
        .text-gradient {
            background: linear-gradient(135deg, #fcd34d 0%, #fbbf24 50%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 0 8px rgba(251, 191, 36, 0.3));
        }
        .btn-gradient {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #ea580c 100%);
            box-shadow: 0 4px 15px -3px rgba(249, 115, 22, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-gradient:hover {
            filter: brightness(1.2);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 30px -8px rgba(249, 115, 22, 0.5);
        }
        .game-icon-container {
            position: relative;
            z-index: 1;
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.5);
        }
        .status-badge {
            background: rgba(34, 197, 94, 0.15);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #4ade80;
            box-shadow: 0 0 15px rgba(34, 197, 94, 0.2);
        }
        .key-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.8) 100%);
            border: 1px solid rgba(251, 191, 36, 0.2);
            box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.5);
        }
        .back-btn {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            background: rgba(251, 191, 36, 0.1);
            border-color: rgba(251, 191, 36, 0.4);
            color: #fbbf24;
        }
        /* Optimization for different screen sizes */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem !important;
            }
            header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 1.5rem !important;
                margin-bottom: 2rem !important;
            }
            .glass-card {
                padding: 1.25rem !important;
            }
            .game-icon-container {
                width: 5rem !important;
                height: 5rem !important;
            }
            h1 {
                font-size: 1.75rem !important;
            }
            h3 {
                font-size: 1.25rem !important;
            }
        }

        @media (min-width: 1024px) {
            .games-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
        }
        @media (min-width: 1280px) {
            .games-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            }
        }
    </style>
</head>
<body class="p-4 md:p-8">
    <div class="max-w-7xl mx-auto main-container">
        <!-- Header Section -->
        <header class="flex flex-col md:flex-row items-center justify-between gap-6 mb-16">
            <div class="flex items-center gap-6">
                <a href="dashboard.php" class="group p-4 back-btn rounded-2xl transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6 transition-colors">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] opacity-80">Hệ thống AI Tài Xỉu</p>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-black text-gradient uppercase tracking-tight drop-shadow-2xl">Chọn Game</h1>
                </div>
            </div>

            <!-- Key Status -->
            <div class="key-card px-8 py-5 rounded-[2rem] flex items-center gap-5 relative group overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <div class="relative w-12 h-12 bg-gradient-to-tr from-yellow-500/20 to-orange-500/20 rounded-2xl flex items-center justify-center border border-yellow-500/30 group-hover:scale-110 transition-transform duration-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6 text-yellow-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="relative">
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-0.5">Thời gian Key còn lại</p>
                    <span class="text-2xl font-black text-white tracking-tight">
                        <?php 
                        if ($activeKey) {
                            $remaining = $activeKey['expiry_at'] - time();
                            if ($remaining > 86400) {
                                echo ceil($remaining / 86400) . ' <span class="text-sm font-medium text-slate-400">Ngày</span>';
                            } elseif ($remaining > 3600) {
                                echo ceil($remaining / 3600) . ' <span class="text-sm font-medium text-slate-400">Giờ</span>';
                            } else {
                                echo ceil($remaining / 60) . ' <span class="text-sm font-medium text-slate-400">Phút</span>';
                            }
                        } else {
                            echo '<span class="text-red-500">Hết hạn</span>';
                        }
                        ?>
                    </span>
                </div>
            </div>
        </header>

        <!-- Games Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-8 games-grid">
            <?php foreach ($games as $game): ?>
            <div class="glass-card rounded-[2.5rem] p-6 flex flex-col h-full group">
                <!-- Game Icon -->
                <div class="relative mb-8 pt-4">
                    <div class="absolute -top-4 -right-2 z-10">
                        <span class="status-badge px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">
                            <?php echo $game['status']; ?>
                        </span>
                    </div>
                    <div class="game-icon-container w-28 h-28 mx-auto p-1 bg-gradient-to-br <?php echo $game['color']; ?> rounded-3xl shadow-2xl transition-transform duration-500 group-hover:rotate-3 group-hover:scale-110">
                        <div class="w-full h-full rounded-[1.25rem] bg-slate-900/90 p-3 flex items-center justify-center overflow-hidden">
                            <img src="<?php echo $game['image']; ?>" alt="<?php echo $game['name']; ?>" class="w-full h-full object-contain">
                        </div>
                    </div>
                </div>
                
                <!-- Game Info -->
                <div class="text-center mb-8 flex-grow">
                    <h3 class="text-2xl font-black text-white mb-2 tracking-tight"><?php echo $game['name']; ?></h3>
                    <p class="text-slate-400 text-sm font-medium">Phân tích dữ liệu thời gian thực</p>
                </div>

                <!-- Action Button -->
                <button onclick="location.href='<?php echo $game['url']; ?>'" class="w-full py-4 rounded-2xl text-sm font-black text-slate-900 btn-gradient uppercase tracking-[0.15em]">
                    Kích hoạt Tool
                </button>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer Info -->
        <footer class="mt-20 text-center">
            <p class="text-slate-500 text-xs font-medium uppercase tracking-[0.2em]">© 2026 TOOLTX2026 - Advanced AI System</p>
        </footer>
    </div>

    <script src="../assets/js/transitions.js"></script>
</body>
</html>