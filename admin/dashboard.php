<?php
require_once '../core/functions.php';
require_once '../core/auth.php';
require_once '../core/icons.php';

// Kiểm tra quyền admin
requireAdmin();

$admin = getCurrentAdmin();
$users = readJSON('users');
$banks = readJSON('banks');
$deposits = readJSON('deposits');
$keys = readJSON('keys');

// Tính toán thống kê
$totalUsers = count($users);
$totalBalance = 0;
foreach ($users as $user) {
    $totalBalance += (int)($user['balance'] ?? 0);
}
$totalBanks = count($banks);
$totalDeposits = count($deposits);
$totalKeys = count($keys);

// Lấy 5 người dùng mới nhất
$recentUsers = array_slice(array_reverse($users), 0, 5);
// Lấy 5 giao dịch nạp tiền mới nhất
$recentDeposits = array_slice(array_reverse($deposits), 0, 5);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TOOLTX2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/transitions.css">
    <style>
        html {
            zoom: 0.8;
        }
        body { 
            background-color: #1e293b; 
            color: #f8fafc; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            background-image: 
                radial-gradient(at 0% 0%, rgba(234, 179, 8, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(249, 115, 22, 0.15) 0px, transparent 50%);
        }
        .glass { 
            background: rgba(255, 255, 255, 0.08); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255, 255, 255, 0.15); 
        }
        .admin-card {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(249, 115, 22, 0.1) 100%);
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 1.25rem;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <nav class="sticky top-0 z-50 p-4 glass border-b border-white/5 flex justify-between items-center px-6 md:px-12">
        <div class="flex items-center gap-3">
            <div class="p-1 bg-gradient-to-br from-red-500 to-orange-600 rounded-xl">
                <div class="h-10 w-10 rounded-lg bg-black flex items-center justify-center">
                    <?php echo getIcon('shield', 'w-6 h-6 text-red-500'); ?>
                </div>
            </div>
            <span class="text-2xl font-extrabold tracking-tighter text-red-500">ADMIN PANEL</span>
        </div>
        <div class="flex gap-6 items-center">
            <div class="hidden sm:flex flex-col items-end">
                <span class="text-[10px] text-slate-500 uppercase font-black tracking-[0.2em]">Quản trị viên</span>
                <span class="text-sm font-bold text-slate-200"><?php echo htmlspecialchars($admin['username']); ?></span>
            </div>
            <a href="../logout.php" class="text-red-400 hover:text-red-300 transition-colors flex items-center gap-2">
                <?php echo getIcon('logout', 'w-5 h-5'); ?>
                Đăng xuất
            </a>
        </div>
    </nav>

    <main class="flex-grow p-6 max-w-7xl mx-auto w-full">
        <!-- Thống kê tổng quan -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div class="admin-card p-6 rounded-2xl">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-2">Tổng người dùng</p>
                        <p class="text-3xl font-black text-red-400"><?php echo $totalUsers; ?></p>
                    </div>
                    <div class="opacity-50">
                        <?php echo getIcon('user', 'w-8 h-8 text-red-500'); ?>
                    </div>
                </div>
            </div>

            <div class="admin-card p-6 rounded-2xl">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-2">Tổng số dư</p>
                        <p class="text-2xl font-black text-orange-400"><?php echo formatMoney($totalBalance); ?></p>
                    </div>
                    <div class="opacity-50">
                        <?php echo getIcon('wallet', 'w-8 h-8 text-orange-500'); ?>
                    </div>
                </div>
            </div>

            <div class="admin-card p-6 rounded-2xl">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-2">Ngân hàng</p>
                        <p class="text-3xl font-black text-yellow-400"><?php echo $totalBanks; ?></p>
                    </div>
                    <div class="opacity-50">
                        <?php echo getIcon('bank', 'w-8 h-8 text-yellow-500'); ?>
                    </div>
                </div>
            </div>

            <div class="admin-card p-6 rounded-2xl">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-2">Key đã bán</p>
                        <p class="text-3xl font-black text-blue-400"><?php echo $totalKeys; ?></p>
                    </div>
                    <div class="opacity-50">
                        <?php echo getIcon('key', 'w-8 h-8 text-blue-500'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quản lý chức năng -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
            <a href="banks.php" class="glass p-8 rounded-2xl border border-white/5 hover:border-yellow-500/20 transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-4 bg-yellow-500/10 rounded-2xl text-yellow-500 group-hover:bg-yellow-500 group-hover:text-black transition-all">
                        <?php echo getIcon('bank', 'w-8 h-8'); ?>
                    </div>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]"><?php echo $totalBanks; ?> ngân hàng</span>
                </div>
                <h4 class="text-xl font-black mb-2">Quản lý Ngân hàng</h4>
                <p class="text-sm text-slate-400 mb-6">Thêm, sửa, xóa thông tin tài khoản ngân hàng nhận tiền nạp.</p>
                <div class="w-full py-3 glass rounded-xl text-sm font-black text-center hover:bg-yellow-500 hover:text-black transition-all border border-white/5">QUẢN LÝ NGAY →</div>
            </a>

            <a href="users.php" class="glass p-8 rounded-2xl border border-white/5 hover:border-blue-500/20 transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-4 bg-blue-500/10 rounded-2xl text-blue-500 group-hover:bg-blue-500 group-hover:text-black transition-all">
                        <?php echo getIcon('user', 'w-8 h-8'); ?>
                    </div>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]"><?php echo $totalUsers; ?> người dùng</span>
                </div>
                <h4 class="text-xl font-black mb-2">Quản lý Người dùng</h4>
                <p class="text-sm text-slate-400 mb-6">Xem, chỉnh sửa, khóa tài khoản người dùng và quản lý quyền hạn.</p>
                <div class="w-full py-3 glass rounded-xl text-sm font-black text-center hover:bg-blue-500 hover:text-black transition-all border border-white/5">QUẢN LÝ NGAY →</div>
            </a>

            <a href="keys.php" class="glass p-8 rounded-2xl border border-white/5 hover:border-purple-500/20 transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-4 bg-purple-500/10 rounded-2xl text-purple-500 group-hover:bg-purple-500 group-hover:text-black transition-all">
                        <?php echo getIcon('key', 'w-8 h-8'); ?>
                    </div>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]"><?php echo $totalKeys; ?> key</span>
                </div>
                <h4 class="text-xl font-black mb-2">Quản lý Key</h4>
                <p class="text-sm text-slate-400 mb-6">Tạo, kích hoạt, vô hiệu hóa key sử dụng cho các công cụ.</p>
                <div class="w-full py-3 glass rounded-xl text-sm font-black text-center hover:bg-purple-500 hover:text-black transition-all border border-white/5">QUẢN LÝ NGAY →</div>
            </a>

            <a href="deposits.php" class="glass p-8 rounded-2xl border border-white/5 hover:border-green-500/20 transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-4 bg-green-500/10 rounded-2xl text-green-500 group-hover:bg-green-500 group-hover:text-black transition-all">
                        <?php echo getIcon('wallet', 'w-8 h-8'); ?>
                    </div>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]"><?php echo $totalDeposits; ?> giao dịch</span>
                </div>
                <h4 class="text-xl font-black mb-2">Quản lý Nạp tiền</h4>
                <p class="text-sm text-slate-400 mb-6">Xem lịch sử nạp tiền, xác nhận giao dịch và quản lý số dư.</p>
                <div class="w-full py-3 glass rounded-xl text-sm font-black text-center hover:bg-green-500 hover:text-black transition-all border border-white/5">QUẢN LÝ NGAY →</div>
            </a>
        </div>

        <!-- Thông tin hệ thống -->
        <div class="glass p-8 rounded-2xl border border-white/5">
            <h3 class="text-lg font-black mb-6 flex items-center gap-3">
                <?php echo getIcon('settings', 'w-6 h-6 text-blue-500'); ?>
                Thông tin hệ thống
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-slate-500 text-sm mb-2">Phiên bản</p>
                    <p class="text-lg font-bold">TOOLTX2026 v2.0</p>
                </div>
                <div>
                    <p class="text-slate-500 text-sm mb-2">Trạng thái hệ thống</p>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        <span class="text-lg font-bold text-green-400">Ổn định</span>
                    </div>
                </div>
                <div>
                    <p class="text-slate-500 text-sm mb-2">Thời gian cập nhật</p>
                    <p class="text-lg font-bold"><?php echo date('d/m/Y H:i'); ?></p>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/transitions.js"></script>
</body>
</html>