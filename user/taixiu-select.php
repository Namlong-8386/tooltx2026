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
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn Game Tài Xỉu - TOOLTX2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/transitions.css">
</head>
<body class="bg-[#0f172a] text-slate-200 font-['Inter'] min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="p-2 hover:bg-slate-800 rounded-full transition-colors">
                    <?php echo getIcon('arrow-left', 'w-6 h-6'); ?>
                </a>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Chọn Game Tài Xỉu</h1>
            </div>
            <div class="flex items-center gap-3 px-4 py-2 bg-slate-800/50 rounded-2xl border border-slate-700/50">
                <div class="p-1.5 bg-yellow-500/10 rounded-lg text-yellow-500">
                    <?php echo getIcon('wallet', 'w-5 h-5'); ?>
                </div>
                <span class="font-bold text-yellow-500"><?php echo formatMoney($currentUser['balance']); ?></span>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($games as $game): ?>
            <div class="group relative bg-slate-800/40 rounded-3xl p-6 border border-slate-700/50 hover:border-blue-500/50 transition-all duration-300 hover:shadow-2xl hover:shadow-blue-500/10 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                
                <div class="relative flex flex-col items-center">
                    <div class="w-24 h-24 mb-4 rounded-2xl bg-slate-900/80 p-2 flex items-center justify-center border border-slate-700/50 group-hover:scale-105 transition-transform duration-300">
                        <img src="<?php echo $game['image']; ?>" alt="<?php echo $game['name']; ?>" class="w-full h-full object-contain rounded-xl">
                    </div>
                    
                    <h3 class="text-lg font-bold text-slate-100 mb-1"><?php echo $game['name']; ?></h3>
                    <div class="flex items-center gap-1.5 mb-6">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        <span class="text-xs font-medium text-green-500"><?php echo $game['status']; ?></span>
                    </div>

                    <button onclick="alert('Đang chuyển hướng tới Tool cho <?php echo $game['name']; ?>...')" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-bold text-sm transition-all shadow-lg shadow-blue-600/20 active:scale-95">
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