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
            background-color: #020617; 
            color: #f8fafc; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: 
                radial-gradient(at 0% 0%, rgba(234, 179, 8, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(249, 115, 22, 0.08) 0px, transparent 50%);
        }
        .glass { 
            background: rgba(255, 255, 255, 0.03); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255, 255, 255, 0.08); 
        }
        .text-gradient {
            background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .tab-active {
            background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%);
            color: #000 !important;
            font-weight: 800;
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col" x-data="{ activeTab: 'deposit', search: '', showLogoutConfirm: false, navOpen: false }">
    <nav class="p-4 glass border-b border-white/5 flex justify-between items-center px-6 md:px-12 sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <a href="dashboard.php" class="flex items-center gap-2">
                <div class="p-1.5 bg-gradient-to-br from-yellow-400 to-orange-600 rounded-xl shadow-lg shadow-orange-500/20">
                    <img src="../assets/images/logo-vip.png" alt="Logo" class="h-8 w-8 rounded-lg bg-black">
                </div>
                <span class="text-xl font-black tracking-tighter text-gradient">TOOLTX2026</span>
            </a>
        </div>

        <div class="flex items-center gap-4">
            <button @click="navOpen = !navOpen" class="p-2.5 bg-slate-800/80 backdrop-blur-md rounded-xl text-slate-400 hover:bg-slate-700/80 hover:text-white transition-all border border-white/10 shadow-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            <div x-show="navOpen" @click.away="navOpen = false" class="absolute right-6 top-20 w-64 bg-slate-900/95 backdrop-blur-xl rounded-[1.5rem] border border-white/10 shadow-2xl py-3 z-[60]" style="display: none;">
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
                <a href="javascript:void(0)" @click="showLogoutConfirm = true; navOpen = false" class="flex items-center gap-3 px-4 py-3 hover:bg-red-500/10 text-red-400 text-sm font-bold transition-all border-t border-white/5">
                    <?php echo getIcon('logout', 'w-5 h-5'); ?> Đăng xuất
                </a>
            </div>
        </div>
    </nav>

    <main class="p-6 max-w-7xl mx-auto w-full mt-4 px-4 md:px-12 flex-1">
        <!-- Header Section -->
        <div class="glass p-8 rounded-[2.5rem] border border-white/5 mb-8 relative overflow-hidden">
            <div class="absolute -right-24 -top-24 w-64 h-64 bg-yellow-500/5 rounded-full blur-3xl"></div>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-gradient-to-br from-yellow-500/10 to-orange-500/10 rounded-2xl text-yellow-500 border border-yellow-500/20">
                        <?php echo getIcon('history', 'w-7 h-7'); ?>
                    </div>
                    <div>
                        <h2 class="text-3xl font-black tracking-tight">Lịch Sử</h2>
                        <p class="text-[10px] text-slate-500 uppercase tracking-[0.2em] font-black">QUẢN LÝ GIAO DỊCH CỦA BẠN</p>
                    </div>
                </div>
                
                <div class="flex bg-black/40 p-1.5 rounded-[1.25rem] border border-white/5 self-start md:self-center">
                    <button @click="activeTab = 'deposit'" :class="activeTab === 'deposit' ? 'tab-active shadow-lg shadow-orange-500/20' : 'text-slate-400 hover:text-white'" class="px-6 py-2.5 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all">
                        LỊCH SỬ NẠP
                    </button>
                    <button @click="activeTab = 'key'" :class="activeTab === 'key' ? 'tab-active shadow-lg shadow-orange-500/20' : 'text-slate-400 hover:text-white'" class="px-6 py-2.5 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all ml-1">
                        LỊCH SỬ MUA KEY
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="mb-8 relative group">
            <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-yellow-500 transition-colors">
                <?php echo getIcon('search', 'w-5 h-5'); ?>
            </div>
            <input type="text" x-model="search" placeholder="Tìm kiếm mã giao dịch, số tiền..." 
                class="w-full bg-white/5 border border-white/10 rounded-[1.5rem] pl-14 pr-6 py-4.5 focus:outline-none focus:border-yellow-500/50 focus:bg-white/10 transition-all font-semibold text-base shadow-inner">
        </div>

        <!-- Deposit History Tab -->
        <div x-show="activeTab === 'deposit'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="glass rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white/[0.02] border-b border-white/5">
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-500">MÃ ĐƠN</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-500">SỐ TIỀN</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-500 text-center">TRẠNG THÁI</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right">THỜI GIAN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.03]">
                            <?php if (empty($userDeposits)): ?>
                                <tr><td colspan="4" class="px-8 py-20 text-center text-slate-500 font-bold italic">Chưa có giao dịch nạp tiền nào.</td></tr>
                            <?php else: ?>
                                <?php foreach ($userDeposits as $d): ?>
                                    <tr class="hover:bg-white/[0.03] transition-all duration-300 group" 
                                        x-show="'<?php echo strtolower(($d['id'] ?? $d['order_id']) . $d['amount']); ?>'.includes(search.toLowerCase())">
                                        <td class="px-8 py-6">
                                            <span class="font-black text-yellow-500/80 group-hover:text-yellow-500 transition-colors tracking-tight"><?php echo $d['id'] ?? $d['order_id']; ?></span>
                                        </td>
                                        <td class="px-8 py-6 font-black text-white text-lg"><?php echo formatMoney($d['amount']); ?></td>
                                        <td class="px-8 py-6 text-center">
                                            <?php if ($d['status'] === 'pending'): ?>
                                                <span class="status-badge bg-yellow-500/10 text-yellow-500 border border-yellow-500/20">CHỜ DUYỆT</span>
                                            <?php elseif ($d['status'] === 'completed'): ?>
                                                <span class="status-badge bg-green-500/10 text-green-500 border border-green-500/20">THÀNH CÔNG</span>
                                            <?php elseif ($d['status'] === 'expired'): ?>
                                                <span class="status-badge bg-slate-500/10 text-slate-500 border border-slate-500/20">HẾT HẠN</span>
                                            <?php else: ?>
                                                <span class="status-badge bg-red-500/10 text-red-500 border border-red-500/20">ĐÃ HỦY</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <div class="flex flex-col items-end">
                                                <span class="text-white font-black text-sm"><?php echo date('H:i', strtotime($d['created_at'])); ?></span>
                                                <span class="text-[10px] text-slate-500 font-bold uppercase tracking-tighter"><?php echo date('d/m/Y', strtotime($d['created_at'])); ?></span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Key History Tab -->
        <div x-show="activeTab === 'key'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            <div class="glass rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white/[0.02] border-b border-white/5">
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-500">MÃ KEY</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-500">GÓI DỊCH VỤ</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-500">LIÊN KẾT</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-500">TỔNG TIỀN</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right">THỜI GIAN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.03]">
                            <?php if (empty($userKeys)): ?>
                                <tr><td colspan="5" class="px-8 py-20 text-center text-slate-500 font-bold italic">Chưa có giao dịch mua key nào.</td></tr>
                            <?php else: ?>
                                <?php foreach ($userKeys as $k): ?>
                                    <tr class="hover:bg-white/[0.03] transition-all duration-300 group"
                                        x-show="'<?php echo strtolower($k['key_code'] . $k['package_name']); ?>'.includes(search.toLowerCase())">
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-3">
                                                <span class="font-black text-orange-500/80 group-hover:text-orange-500 transition-colors tracking-tight"><?php echo $k['key_code']; ?></span>
                                                <button onclick="copy('<?php echo $k['key_code']; ?>', this)" class="p-2 hover:bg-orange-500/10 rounded-xl transition-all text-slate-500 hover:text-orange-500 border border-transparent hover:border-orange-500/20">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6">
                                            <span class="status-badge bg-blue-500/10 text-blue-400 border border-blue-500/20 font-black">
                                                <?php echo strtoupper($k['package_name']); ?>
                                            </span>
                                        </td>
                                        <td class="px-8 py-6">
                                            <?php if (!empty($k['linked_account'])): ?>
                                                <div class="flex flex-col">
                                                    <span class="text-xs font-black text-green-400"><?php echo htmlspecialchars($k['linked_account']); ?></span>
                                                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-tighter"><?php echo date('H:i d/m', strtotime($k['linked_at'])); ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">CHƯA LIÊN KẾT</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-8 py-6 font-black text-white"><?php echo formatMoney($k['total_price']); ?></td>
                                        <td class="px-8 py-6 text-right">
                                            <div class="flex flex-col items-end">
                                                <span class="text-white font-black text-sm"><?php echo date('H:i', strtotime($k['created_at'])); ?></span>
                                                <span class="text-[10px] text-slate-500 font-bold uppercase tracking-tighter"><?php echo date('d/m/Y', strtotime($k['created_at'])); ?></span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        function copy(text, btn) {
            navigator.clipboard.writeText(text);
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>';
            setTimeout(() => { btn.innerHTML = originalHTML; }, 2000);
        }
    </script>
    <script src="../assets/js/transitions.js"></script>
</body>
</html>
