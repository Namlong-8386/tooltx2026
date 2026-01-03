<?php
require_once '../core/functions.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$packages = [
    ['id' => 1, 'name' => '1 Ngày', 'price' => 25000, 'icon' => 'rocket', 'desc' => '1 ngày sử dụng'],
    ['id' => 2, 'name' => '3 Ngày', 'price' => 65000, 'icon' => 'rocket', 'desc' => '3 ngày sử dụng'],
    ['id' => 3, 'name' => '7 Ngày', 'price' => 100000, 'icon' => 'rocket', 'desc' => '7 ngày sử dụng'],
    ['id' => 4, 'name' => '30 Ngày', 'price' => 150000, 'icon' => 'rocket', 'desc' => '30 ngày sử dụng'],
    ['id' => 5, 'name' => '999999 Ngày', 'price' => 200000, 'icon' => 'rocket', 'desc' => '999999 ngày sử dụng'],
];

$error = '';
$success = '';
$newKeyCode = '';

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
                    
                    // Store in session and redirect to avoid double post on F5
                    $_SESSION['last_purchase_success'] = [
                        'msg' => "Mua thành công {$quantity} key gói {$selectedPackage['name']}.",
                        'code' => $newKey['key_code']
                    ];
                    header('Location: buy-key.php?success=1');
                    exit;
                }
            }
        }
    }
}

// Check for redirect success
if (isset($_GET['success']) && isset($_SESSION['last_purchase_success'])) {
    $success = $_SESSION['last_purchase_success']['msg'];
    $newKeyCode = $_SESSION['last_purchase_success']['code'];
    // We don't unset here to allow F5, but we should clear it if they navigate away or buy again
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
            background: rgba(255, 255, 255, 0.08); 
            backdrop-filter: blur(16px); 
            border: 1px solid rgba(255, 255, 255, 0.15); 
        }
        .warning-box {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-left: 4px solid #ef4444;
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
        .package-card { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .package-card:hover { transform: translateY(-10px); background: rgba(255, 255, 255, 0.08); border-color: rgba(251, 191, 36, 0.4); }
        [x-cloak] { display: none !important; }
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
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-6 top-20 w-64 bg-slate-900/95 backdrop-blur-xl rounded-[1.5rem] border border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.5)] py-3 overflow-hidden z-[60]">
                <div class="px-4 py-3 border-b border-white/5 mb-2">
                    <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest">Tài khoản</p>
                    <p class="text-sm font-bold text-slate-200 truncate"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                </div>
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 text-sm font-semibold transition-all"><?php echo getIcon('home', 'w-5 h-5 text-yellow-500'); ?> Trang chủ</a>
                <a href="deposit.php" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 text-sm font-semibold transition-all"><?php echo getIcon('wallet', 'w-5 h-5 text-orange-500'); ?> Nạp tiền</a>
                <a href="buy-key.php" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 text-sm font-semibold transition-all"><?php echo getIcon('key', 'w-5 h-5 text-blue-500'); ?> Mua Key</a>
                <a href="history.php" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 text-sm font-semibold transition-all border-b border-white/5"><?php echo getIcon('history', 'w-5 h-5 text-purple-500'); ?> Lịch sử</a>
                <a href="../logout.php" class="flex items-center gap-3 px-4 py-3 hover:bg-red-500/10 text-red-400 text-sm font-bold transition-all"><?php echo getIcon('logout', 'w-5 h-5'); ?> Đăng xuất</a>
            </div>
        </div>
    </nav>

    <main class="p-6 max-w-7xl mx-auto w-full mt-8" x-data="{ selectedId: 1, quantity: 1, packages: <?php echo htmlspecialchars(json_encode($packages)); ?> }">
        <div class="glass p-8 rounded-[2.5rem] border border-white/5 mb-12 relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-64 h-64 bg-yellow-500/5 rounded-full blur-3xl"></div>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                <div class="flex items-center gap-4">
                    <a href="dashboard.php" class="p-2.5 bg-slate-800/80 backdrop-blur-md rounded-xl text-slate-400 hover:bg-slate-700/80 hover:text-white transition-all border border-white/10 shadow-lg group">
                        <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
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
                    <div class="p-3 bg-yellow-500/10 rounded-2xl text-yellow-500"><?php echo getIcon('wallet', 'w-7 h-7'); ?></div>
                    <div>
                        <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest mb-1">Số dư tài khoản:</p>
                        <?php 
                            $users = readJSON('users');
                            $balance = 0;
                            foreach($users as $u) if($u['id'] === $_SESSION['user_id']) $balance = $u['balance'];
                        ?>
                        <p class="text-2xl font-black text-[#10b981]"><?php echo number_format($balance, 0, ',', '.') . '₫'; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-2xl mb-6 text-sm flex items-center gap-3"><?php echo getIcon('x', 'w-5 h-5'); ?><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="glass p-8 rounded-3xl border border-white/5 mb-8 text-center relative overflow-hidden animate-fade-in">
                <div class="absolute -right-12 -top-12 w-64 h-64 bg-green-500/5 rounded-full blur-3xl"></div>
                <div class="w-12 h-12 bg-green-500/10 rounded-full flex items-center justify-center text-green-500 mx-auto mb-4 relative z-10"><?php echo getIcon('check', 'w-6 h-6'); ?></div>
                <h3 class="text-xl font-bold text-green-500 mb-2 relative z-10"><?php echo $success; ?></h3>
                <p class="text-white text-sm font-semibold mb-6 relative z-10">Mã key của bạn đã được tạo thành công:</p>
                <div class="bg-slate-800/50 border border-white/5 p-4 rounded-2xl font-mono text-xl text-yellow-500 tracking-[0.2em] mb-6 relative z-10"><?php echo $newKeyCode; ?></div>
                <div class="warning-box p-3 rounded-2xl mb-6 relative z-10 max-w-sm mx-auto">
                    <p class="text-[10px] font-black text-red-400 flex items-center justify-center gap-2 uppercase tracking-widest">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Lưu ý
                    </p>
                    <p class="text-[10px] text-slate-300 mt-1 leading-relaxed">Key chỉ dùng cho <span class="text-red-400 font-bold">1 thiết bị</span> & <span class="text-red-400 font-bold">không thể thay đổi</span> sau khi kích hoạt.</p>
                </div>
                <div class="mt-6 flex justify-center gap-4">
                    <a href="buy-key.php" class="px-6 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-bold hover:bg-white/10 transition-all">Mua thêm</a>
                    <a href="dashboard.php" class="px-6 py-2 bg-yellow-500 text-black rounded-xl text-xs font-bold hover:scale-105 transition-all">Trang chủ</a>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <div class="lg:col-span-3 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <?php foreach ($packages as $p): ?>
                            <div @click="selectedId = <?php echo $p['id']; ?>" 
                                 :class="selectedId === <?php echo $p['id']; ?> ? 'border-blue-500 bg-blue-500/5 ring-2 ring-blue-500/20' : 'border-white/5'"
                                 class="glass p-6 rounded-[1.5rem] border package-card group relative overflow-hidden flex flex-col cursor-pointer text-center">
                                <h3 class="text-xl font-bold mb-3"><?php echo $p['name']; ?></h3>
                                <div class="text-2xl font-black mb-3"><?php echo number_format($p['price'], 0, ',', '.') . '₫'; ?></div>
                                <p class="text-slate-500 text-xs"><?php echo $p['desc']; ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="glass p-8 rounded-[1.5rem] border border-white/5 relative overflow-hidden">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                            <div><h3 class="text-2xl font-bold mb-1">Tổng thanh toán</h3></div>
                            <div class="text-right">
                                <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mb-1">Tổng cộng</p>
                                <div class="text-4xl font-black" x-text="new Intl.NumberFormat('vi-VN').format(packages.find(p => p.id === selectedId).price * quantity) + '₫'"></div>
                            </div>
                        </div>
                        <form method="POST" class="mt-8">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="package_id" :value="selectedId">
                            <input type="hidden" name="quantity" :value="quantity">
                            <button type="submit" class="w-full py-5 rounded-2xl text-lg font-black bg-gradient-to-r from-[#6366f1] to-[#a855f7] hover:opacity-90 transition-all flex items-center justify-center gap-3 shadow-xl shadow-indigo-500/20 uppercase">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path></svg>
                                MUA NGAY
                            </button>
                        </form>
                    </div>
                </div>
                <div class="space-y-6">
                    <div class="glass p-8 rounded-[2.5rem] border-l-4 border-yellow-500">
                        <h3 class="text-xl font-black mb-6 flex items-center gap-3"><span class="p-2 bg-yellow-500/10 rounded-xl text-yellow-500"><?php echo getIcon('shield', 'w-5 h-5'); ?></span> Lưu Ý VIP</h3>
                        <ul class="space-y-4">
                            <li class="text-xs text-slate-400 flex gap-3"><span class="text-yellow-500 mt-1">✦</span><span>Hệ thống tự động kích hoạt key ngay sau khi thanh toán.</span></li>
                            <li class="text-xs text-red-400 font-bold flex gap-3"><span class="text-red-500 mt-1">✦</span><span>Mỗi mã key chỉ sử dụng được trên 1 thiết bị và không thể thay đổi thiết bị.</span></li>
                            <li class="text-xs text-slate-400 flex gap-3"><span class="text-yellow-500 mt-1">✦</span><span>Hỗ trợ kỹ thuật 24/7 qua kênh Telegram.</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <script src="../assets/js/security.js"></script>
</body>
</html>