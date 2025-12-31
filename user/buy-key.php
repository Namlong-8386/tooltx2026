<?php
require_once '../core/functions.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$packages = [
    ['id' => 1, 'name' => '1 Giờ', 'price' => 5000, 'icon' => 'rocket'],
    ['id' => 2, 'name' => '10 Giờ', 'price' => 10000, 'icon' => 'rocket'],
    ['id' => 3, 'name' => '1 Ngày', 'price' => 20000, 'icon' => 'rocket'],
    ['id' => 4, 'name' => '3 Ngày', 'price' => 45000, 'icon' => 'rocket'],
    ['id' => 5, 'name' => '7 Ngày', 'price' => 80000, 'icon' => 'rocket'],
    ['id' => 6, 'name' => '1 Tháng', 'price' => 120000, 'icon' => 'rocket'],
    ['id' => 7, 'name' => 'Vĩnh Viễn', 'price' => 250000, 'icon' => 'rocket'],
];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Lỗi xác thực CSRF. Vui lòng thử lại.';
    } else {
        $package_id = (int)$_POST['package_id'];
    $quantity = (int)$_POST['quantity'];
    
    $selectedPackage = null;
    foreach ($packages as $p) {
        if ($p['id'] === $package_id) {
            $selectedPackage = $p;
            break;
        }
    }

    if (!$selectedPackage) {
        $error = 'Gói key không hợp lệ.';
    } elseif ($quantity < 1) {
        $error = 'Số lượng phải lớn hơn 0.';
    } else {
        $total_price = $selectedPackage['price'] * $quantity;
        $discount = 0;
        if ($quantity >= 10) {
            $discount = 0.35;
        } elseif ($quantity >= 6) {
            $discount = 0.25;
        } elseif ($quantity >= 3) {
            $discount = 0.15;
        }
        
        $final_price = $total_price * (1 - $discount);
        
        $users = readJSON('users');
        $userIndex = -1;
        foreach ($users as $index => $user) {
            if ($user['id'] === $_SESSION['user_id']) {
                $userIndex = $index;
                break;
            }
        }

        if ($userIndex !== -1) {
            if ($users[$userIndex]['balance'] < $final_price) {
                $error = 'Số dư không đủ. Vui lòng nạp thêm tiền.';
            } else {
                $users[$userIndex]['balance'] -= $final_price;
                writeJSON('users', $users);
                
                $keys = readJSON('keys');
                $newKey = [
                    'id' => generateID('KEY'),
                    'user_id' => $_SESSION['user_id'],
                    'package_name' => $selectedPackage['name'],
                    'quantity' => $quantity,
                    'total_price' => $final_price,
                    'key_code' => strtoupper(generateRandomString(12)),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $keys[] = $newKey;
                writeJSON('keys', $keys);
                
                $success = "Mua thành công {$quantity} key gói {$selectedPackage['name']}.";
                $newKeyCode = $newKey['key_code'];
            }
        }
    }
}
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mua Key - TOOLTX2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/transitions.css">
    <style>
        html { zoom: 0.9; }
        body { 
            background-color: #0f172a; 
            color: #f8fafc; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: 
                radial-gradient(at 0% 0%, rgba(234, 179, 8, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(249, 115, 22, 0.05) 0px, transparent 50%);
        }
        .glass { 
            background: rgba(255, 255, 255, 0.05); 
            backdrop-filter: blur(16px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
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
        .package-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .package-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(251, 191, 36, 0.4);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <nav class="p-4 glass border-b border-white/5 flex justify-between items-center px-6 md:px-12 sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <a href="dashboard.php" class="flex items-center gap-2">
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
                    <p class="text-sm font-bold text-slate-200 truncate"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
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

    <main class="p-6 max-w-7xl mx-auto w-full mt-8">
        <div class="glass p-8 rounded-[2.5rem] border border-white/5 mb-12 relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-64 h-64 bg-yellow-500/5 rounded-full blur-3xl"></div>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                <div class="flex items-center gap-4">
                    <a href="dashboard.php" class="p-2.5 bg-slate-800/80 backdrop-blur-md rounded-xl text-slate-400 hover:bg-slate-700/80 hover:text-white transition-all border border-white/10 shadow-lg group">
                        <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <div class="inline-flex items-center gap-2 px-2 py-0.5 rounded-full bg-orange-500/10 border border-orange-500/20 mb-1">
                            <span class="w-1 h-1 bg-orange-500 rounded-full animate-pulse"></span>
                            <span class="text-[8px] font-black text-orange-500 uppercase tracking-widest">Tự động</span>
                        </div>
                        <h2 class="text-2xl font-black tracking-tight">Mua Key</h2>
                    </div>
                </div>
                
                <div class="glass p-5 rounded-3xl flex items-center gap-4 bg-white/5 border-white/10">
                    <div class="p-3 bg-yellow-500/10 rounded-2xl text-yellow-500">
                        <?php echo getIcon('wallet', 'w-7 h-7'); ?>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest mb-1">Số dư hiện có</p>
                        <?php 
                            $users = readJSON('users');
                            $balance = 0;
                            foreach($users as $u) if($u['id'] === $_SESSION['user_id']) $balance = $u['balance'];
                        ?>
                        <p class="text-2xl font-black text-gradient"><?php echo formatMoney($balance); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-2xl mb-6 text-sm flex items-center gap-3">
                <?php echo getIcon('x', 'w-5 h-5'); ?>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="glass p-8 rounded-3xl border-green-500/30 mb-8 text-center">
                <div class="w-16 h-16 bg-green-500/10 rounded-full flex items-center justify-center text-green-500 mx-auto mb-4">
                    <?php echo getIcon('check', 'w-8 h-8'); ?>
                </div>
                <h3 class="text-xl font-black text-green-500 mb-2"><?php echo $success; ?></h3>
                <p class="text-slate-400 text-sm mb-6">Mã key của bạn đã được tạo thành công:</p>
                <div class="bg-white/5 border border-white/10 p-4 rounded-2xl font-mono text-2xl text-yellow-500 tracking-widest mb-6">
                    <?php echo $newKeyCode; ?>
                </div>
                <p class="text-xs text-slate-500 italic">Vui lòng sao chép và lưu lại mã key này.</p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php foreach ($packages as $p): ?>
                    <div class="glass p-6 rounded-[2.5rem] border border-white/5 package-card group relative overflow-hidden flex flex-col">
                        <div class="absolute -right-6 -top-6 opacity-[0.03] group-hover:opacity-[0.08] transition-all group-hover:scale-110 duration-500">
                            <?php echo getIcon($p['icon'], 'w-32 h-32'); ?>
                        </div>
                        
                        <div class="flex justify-between items-start mb-6">
                            <div class="p-4 bg-white/5 rounded-2xl text-yellow-500 group-hover:bg-yellow-500 group-hover:text-black transition-all duration-300">
                                <?php echo getIcon($p['icon'], 'w-6 h-6'); ?>
                            </div>
                            <?php if($p['name'] === 'Vĩnh Viễn'): ?>
                                <span class="px-3 py-1 bg-red-500/20 text-red-500 rounded-full text-[10px] font-black uppercase tracking-widest border border-red-500/30">Hời nhất</span>
                            <?php endif; ?>
                        </div>

                        <div class="flex-grow">
                            <h3 class="text-2xl font-black mb-1"><?php echo $p['name']; ?></h3>
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-4">Gói dịch vụ</p>
                            <div class="text-3xl font-black text-gradient mb-8"><?php echo formatMoney($p['price']); ?></div>
                        </div>
                        
                        <form method="POST" class="mt-auto space-y-4">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="package_id" value="<?php echo $p['id']; ?>">
                            <div class="flex gap-3">
                                <div class="w-24 relative">
                                    <input type="number" name="quantity" value="1" min="1" class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:border-yellow-500/50 focus:bg-white/10 transition-all font-bold">
                                    <span class="absolute -top-2 left-3 bg-[#0f172a] px-1 text-[8px] font-black text-slate-500 uppercase">Số lượng</span>
                                </div>
                                <button type="submit" class="flex-1 btn-primary text-black py-3 rounded-2xl text-xs font-black hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                                    MUA NGAY
                                    <?php echo getIcon('rocket', 'w-4 h-4'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="space-y-6">
                <div class="glass p-8 rounded-[2.5rem] border-l-4 border-green-500 relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 opacity-5">
                        <?php echo getIcon('check', 'w-24 h-24'); ?>
                    </div>
                    <h3 class="text-xl font-black mb-6 flex items-center gap-3">
                        <span class="p-2 bg-green-500/10 rounded-xl text-green-500"><?php echo getIcon('check', 'w-5 h-5'); ?></span>
                        Siêu Ưu Đãi
                    </h3>
                    <div class="space-y-4 relative z-10">
                        <div class="p-4 bg-white/5 rounded-2xl border border-white/5 hover:border-green-500/30 transition-all">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Mua từ 3 key</span>
                                <span class="px-2 py-0.5 bg-green-500 text-black rounded-lg text-[10px] font-black">-15%</span>
                            </div>
                            <p class="text-[10px] text-slate-500 italic">Tiết kiệm đáng kể</p>
                        </div>
                        <div class="p-4 bg-white/5 rounded-2xl border border-white/5 hover:border-green-500/30 transition-all">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Mua từ 6 key</span>
                                <span class="px-2 py-0.5 bg-green-500 text-black rounded-lg text-[10px] font-black">-25%</span>
                            </div>
                            <p class="text-[10px] text-slate-500 italic">Lựa chọn thông minh</p>
                        </div>
                        <div class="p-4 bg-white/5 rounded-2xl border border-white/5 hover:border-green-500/30 transition-all">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Mua từ 10 key</span>
                                <span class="px-2 py-0.5 bg-green-500 text-black rounded-lg text-[10px] font-black">-35%</span>
                            </div>
                            <p class="text-[10px] text-slate-500 italic">Giá cực sốc</p>
                        </div>
                    </div>
                </div>

                <div class="glass p-8 rounded-[2.5rem] border-l-4 border-yellow-500">
                    <h3 class="text-xl font-black mb-6 flex items-center gap-3">
                        <span class="p-2 bg-yellow-500/10 rounded-xl text-yellow-500"><?php echo getIcon('shield', 'w-5 h-5'); ?></span>
                        Lưu Ý VIP
                    </h3>
                    <ul class="space-y-4">
                        <li class="text-xs text-slate-400 flex gap-3">
                            <span class="text-yellow-500 mt-1">✦</span>
                            <span>Hệ thống tự động kích hoạt key ngay sau khi thanh toán.</span>
                        </li>
                        <li class="text-xs text-slate-400 flex gap-3">
                            <span class="text-yellow-500 mt-1">✦</span>
                            <span>Mỗi key có thể sử dụng cho một tài khoản duy nhất.</span>
                        </li>
                        <li class="text-xs text-slate-400 flex gap-3">
                            <span class="text-yellow-500 mt-1">✦</span>
                            <span>Hỗ trợ kỹ thuật 24/7 qua kênh Telegram.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
    <script src="../assets/js/security.js"></script>
</body>
</html>
