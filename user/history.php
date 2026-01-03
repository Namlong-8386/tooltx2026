<?php
require_once '../core/functions.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$deposits = readJSON('deposits');
$userDeposits = array_filter($deposits, function($d) {
    return $d['user_id'] === $_SESSION['user_id'];
});
usort($userDeposits, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

$keys = readJSON('keys');
$userKeys = array_filter($keys, function($k) {
    return $k['user_id'] === $_SESSION['user_id'];
});
usort($userKeys, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Giao Dịch - TOOLTX2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        html { zoom: 0.9; }
        body { 
            background-color: #0f172a; 
            color: #f8fafc; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: 
                radial-gradient(at 0% 0%, rgba(234, 179, 8, 0.04) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(249, 115, 22, 0.04) 0px, transparent 50%);
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
        .tab-active {
            background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%);
            color: #000;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col" x-data="{ activeTab: 'deposit', search: '' }">
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
            <div x-show="open" @click.away="open = false" class="absolute right-6 top-20 w-64 bg-slate-900/95 backdrop-blur-xl rounded-[1.5rem] border border-white/10 shadow-2xl py-3 z-[60]" style="display: none;">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 text-sm font-semibold transition-all">
                    <?php echo getIcon('home', 'w-5 h-5 text-yellow-500'); ?> Trang chủ
                </a>
                <a href="deposit.php" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 text-sm font-semibold transition-all">
                    <?php echo getIcon('wallet', 'w-5 h-5 text-orange-500'); ?> Nạp tiền
                </a>
                <a href="buy-key.php" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 text-sm font-semibold transition-all">
                    <?php echo getIcon('key', 'w-5 h-5 text-blue-500'); ?> Mua Key
                </a>
                <a href="history.php" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 text-sm font-semibold transition-all border-b border-white/5">
                    <?php echo getIcon('history', 'w-5 h-5 text-purple-500'); ?> Lịch sử
                </a>
                <a href="../logout.php" class="flex items-center gap-3 px-4 py-3 hover:bg-red-500/10 text-red-400 text-sm font-bold transition-all">
                    <?php echo getIcon('logout', 'w-5 h-5'); ?> Đăng xuất
                </a>
            </div>
        </div>
    </nav>

    <main class="p-6 max-w-7xl mx-auto w-full mt-6 flex-grow">
        <div class="glass p-8 rounded-[2.5rem] border border-white/5 mb-8 relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-yellow-500/10 rounded-xl text-yellow-500">
                        <?php echo getIcon('history', 'w-6 h-6'); ?>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black">Lịch Sử</h2>
                        <p class="text-xs text-slate-400 uppercase tracking-widest font-bold">Quản lý giao dịch của bạn</p>
                    </div>
                </div>
                
                <div class="flex bg-white/5 p-1.5 rounded-2xl border border-white/10">
                    <button @click="activeTab = 'deposit'" :class="activeTab === 'deposit' ? 'tab-active shadow-lg shadow-orange-500/20' : 'text-slate-400 hover:text-white'" class="px-6 py-2.5 rounded-xl font-black text-xs uppercase tracking-widest transition-all">
                        Lịch sử nạp
                    </button>
                    <button @click="activeTab = 'key'" :class="activeTab === 'key' ? 'tab-active shadow-lg shadow-orange-500/20' : 'text-slate-400 hover:text-white'" class="px-6 py-2.5 rounded-xl font-black text-xs uppercase tracking-widest transition-all">
                        Lịch sử mua key
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="mb-6 flex flex-col md:flex-row gap-4">
            <div class="relative flex-grow">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">
                    <?php echo getIcon('search', 'w-5 h-5'); ?>
                </div>
                <input type="text" x-model="search" placeholder="Tìm kiếm mã giao dịch, số tiền..." class="w-full bg-white/5 border border-white/10 rounded-2xl pl-12 pr-4 py-3.5 focus:outline-none focus:border-yellow-500/50 focus:bg-white/10 transition-all font-semibold text-sm">
            </div>
        </div>

        <!-- Deposit History Tab -->
        <div x-show="activeTab === 'deposit'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="glass rounded-[2.5rem] border border-white/5 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-white/5 border-b border-white/10">
                                <th class="px-8 py-5 text-xs font-black uppercase tracking-widest text-slate-500">Mã đơn</th>
                                <th class="px-8 py-5 text-xs font-black uppercase tracking-widest text-slate-500">Số tiền</th>
                                <th class="px-8 py-5 text-xs font-black uppercase tracking-widest text-slate-500">Trạng thái</th>
                                <th class="px-8 py-5 text-xs font-black uppercase tracking-widest text-slate-500">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php if (empty($userDeposits)): ?>
                                <tr><td colspan="4" class="px-8 py-12 text-center text-slate-500 font-bold">Chưa có giao dịch nạp tiền nào.</td></tr>
                            <?php else: ?>
                                <?php foreach ($userDeposits as $d): ?>
                                    <tr class="hover:bg-white/[0.02] transition-colors" 
                                        x-show="'<?php echo strtolower($d['order_id'] . $d['amount']); ?>'.includes(search.toLowerCase())">
                                        <td class="px-8 py-5 font-mono text-yellow-500 font-bold"><?php echo $d['order_id']; ?></td>
                                        <td class="px-8 py-5 font-black text-white"><?php echo formatMoney($d['amount']); ?></td>
                                        <td class="px-8 py-5">
                                            <?php if ($d['status'] === 'pending'): ?>
                                                <span class="px-3 py-1 bg-yellow-500/10 text-yellow-500 rounded-lg text-xs font-black border border-yellow-500/20">CHỜ DUYỆT</span>
                                            <?php elseif ($d['status'] === 'completed'): ?>
                                                <span class="px-3 py-1 bg-green-500/10 text-green-500 rounded-lg text-xs font-black border border-green-500/20">THÀNH CÔNG</span>
                                            <?php else: ?>
                                                <span class="px-3 py-1 bg-red-500/10 text-red-500 rounded-lg text-xs font-black border border-red-500/20">ĐÃ HỦY</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-8 py-5 text-slate-400 text-sm font-semibold"><?php echo date('H:i d/m/Y', strtotime($d['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Key History Tab -->
        <div x-show="activeTab === 'key'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="glass rounded-[2.5rem] border border-white/5 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-white/5 border-b border-white/10">
                                <th class="px-8 py-5 text-xs font-black uppercase tracking-widest text-slate-500">Mã Key</th>
                                <th class="px-8 py-5 text-xs font-black uppercase tracking-widest text-slate-500">Gói dịch vụ</th>
                                <th class="px-8 py-5 text-xs font-black uppercase tracking-widest text-slate-500">Liên kết</th>
                                <th class="px-8 py-5 text-xs font-black uppercase tracking-widest text-slate-500">Tổng tiền</th>
                                <th class="px-8 py-5 text-xs font-black uppercase tracking-widest text-slate-500">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php if (empty($userKeys)): ?>
                                <tr><td colspan="4" class="px-8 py-12 text-center text-slate-500 font-bold">Chưa có giao dịch mua key nào.</td></tr>
                            <?php else: ?>
                                <?php foreach ($userKeys as $k): ?>
                                    <tr class="hover:bg-white/[0.02] transition-colors"
                                        x-show="'<?php echo strtolower($k['key_code'] . $k['package_name']); ?>'.includes(search.toLowerCase())">
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono text-orange-500 font-bold"><?php echo $k['key_code']; ?></span>
                                                <button onclick="navigator.clipboard.writeText('<?php echo $k['key_code']; ?>')" class="p-1.5 hover:bg-white/10 rounded-md transition-colors text-slate-500 hover:text-white" title="Copy key">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <span class="px-3 py-1 bg-blue-500/10 text-blue-400 rounded-lg text-xs font-black border border-blue-500/20">
                                                <?php echo strtoupper($k['package_name']); ?>
                                            </span>
                                        </td>
                                        <td class="px-8 py-5">
                                            <?php if (!empty($k['linked_account'])): ?>
                                                <div class="flex flex-col">
                                                    <span class="text-xs font-bold text-green-400"><?php echo htmlspecialchars($k['linked_account']); ?></span>
                                                    <span class="text-[9px] text-slate-500"><?php echo date('H:i d/m', strtotime($k['linked_at'])); ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-xs font-bold text-slate-500 italic">Chưa liên kết</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-8 py-5 font-black text-white"><?php echo formatMoney($k['total_price']); ?></td>
                                        <td class="px-8 py-5 text-slate-400 text-sm font-semibold"><?php echo date('H:i d/m/Y', strtotime($k['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <script src="../assets/js/transitions.js"></script>
</body>
</html>
