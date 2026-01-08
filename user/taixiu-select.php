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
        // Ưu tiên key còn hạn
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
    ['name' => 'Go88', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Go88', 'status' => 'Hoạt động'],
    ['name' => 'Sunwin', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Sunwin', 'status' => 'Hoạt động'],
    ['name' => '789Club', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=789Club', 'status' => 'Hoạt động'],
    ['name' => 'B52', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=B52', 'status' => 'Hoạt động'],
    ['name' => 'Rikvip', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Rikvip', 'status' => 'Hoạt động'],
    ['name' => 'Manclub', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Manclub', 'status' => 'Hoạt động'],
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
            min-height: screen;
        }
        .glass { 
            background: rgba(255, 255, 255, 0.08); 
            backdrop-filter: blur(16px); 
            border: 1px solid rgba(255, 255, 255, 0.15); 
        }
        .text-gradient {
            background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="max-w-7xl mx-auto px-6 md:px-12 py-8">
        <div class="flex items-center justify-between mb-12">
            <div class="flex items-center gap-6">
                <a href="dashboard.php" class="p-3 bg-white/5 border border-white/10 rounded-2xl text-slate-400 hover:text-white hover:bg-white/10 transition-all shadow-lg">
                    <?php echo getIcon('arrow-left', 'w-6 h-6'); ?>
                </a>
                <div>
                    <p class="text-slate-500 text-[10px] font-black uppercase tracking-[0.3em] mb-1">Hệ thống Tool AI</p>
                    <h1 class="text-3xl font-black text-gradient uppercase tracking-tight">Chọn Game Tool Tài Xỉu</h1>
                </div>
            </div>
            <div class="glass px-6 py-3 rounded-2xl flex items-center gap-4">
                <div class="p-2 bg-green-500 rounded-xl text-white shadow-[0_0_15px_rgba(34,197,94,0.5)]">
                    <?php echo getIcon('clock', 'w-6 h-6'); ?>
                </div>
                <div>
                    <p class="text-[10px] text-gradient font-black uppercase tracking-widest">Thời gian Key</p>
                    <span class="font-bold text-white">
                        <?php 
                        if ($activeKey) {
                            $remaining = $activeKey['expiry_at'] - time();
                            if ($remaining > 86400) {
                                echo ceil($remaining / 86400) . ' Ngày';
                            } elseif ($remaining > 3600) {
                                echo ceil($remaining / 3600) . ' Giờ';
                            } else {
                                echo ceil($remaining / 60) . ' Phút';
                            }
                        } else {
                            echo 'Chưa có Key';
                        }
                        ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            <?php foreach ($games as $game): ?>
            <div class="group relative glass rounded-[2.5rem] p-8 hover:border-yellow-500/30 transition-all duration-500 hover:shadow-2xl hover:shadow-yellow-500/10 flex flex-col items-center">
                <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity rounded-[2.5rem]"></div>
                
                <div class="relative flex flex-col items-center w-full">
                    <div class="w-24 h-24 mb-6 p-1 bg-gradient-to-br from-yellow-400 to-orange-600 rounded-3xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-500 shadow-xl shadow-orange-500/20">
                        <div class="w-full h-full rounded-2xl bg-slate-900/90 p-2 flex items-center justify-center overflow-hidden">
                            <img src="<?php echo $game['image']; ?>" alt="<?php echo $game['name']; ?>" class="w-full h-full object-contain rounded-xl">
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-black text-slate-100 mb-1 tracking-tight"><?php echo $game['name']; ?></h3>
                    <div class="flex items-center gap-2 mb-8">
                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                        <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest"><?php echo $game['status']; ?></span>
                    </div>

                    <button onclick="alert('Đang chuyển hướng tới Tool cho <?php echo $game['name']; ?>...')" class="w-full py-4 glass rounded-2xl text-xs font-black hover:bg-yellow-500 hover:text-black transition-all border border-white/5 shadow-lg active:scale-95 uppercase tracking-widest">
                        Bắt đầu Hack
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="../assets/js/transitions.js"></script>
</body>
</html>