<?php
require_once 'core/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? '';
if (empty($id)) {
    die("Yêu cầu không hợp lệ.");
}

$users = readJSON('users');
$currentUser = null;
foreach ($users as $user) {
    if ($user['id'] === $_SESSION['user_id']) {
        $currentUser = $user;
        break;
    }
}

if (!$currentUser) {
    header('Location: login.php');
    exit;
}

$deposits = readJSON('deposits');
$order = null;
foreach ($deposits as $d) {
    if (($d['id'] ?? '') === $id || ($d['order_id'] ?? '') === $id) {
        $order = $d;
        break;
    }
}

if (!$order) {
    die("Đơn hàng không tồn tại.");
}

// Check expiration (20 minutes)
$created_at = strtotime($order['created_at']);
$now = time();
$expiry_time = 20 * 60; // 20 minutes in seconds

if ($now - $created_at > $expiry_time && $order['status'] === 'pending') {
    // Mark as expired if not already
    foreach ($deposits as &$d) {
        if (($d['id'] ?? '') === $id || ($d['order_id'] ?? '') === $id) {
            $d['status'] = 'expired';
            $order['status'] = 'expired';
            break;
        }
    }
    writeJSON('deposits', $deposits);
}

$is_expired = ($order['status'] === 'expired' || ($now - $created_at > $expiry_time && $order['status'] === 'pending'));
$is_completed = ($order['status'] === 'completed');
$is_cancelled = ($order['status'] === 'cancelled');

// Generate VietQR URL
$qr_url = "https://img.vietqr.io/image/" . urlencode($order['bank_name'] ?? '') . "-" . urlencode($order['account_no'] ?? '') . "-compact2.png?amount=" . urlencode($order['amount']) . "&addInfo=" . urlencode($order['description']) . "&accountName=" . urlencode($order['account_name'] ?? '');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán Đơn Hàng - TOOLTX2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/transitions.css">
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
        .qr-container { background: white; padding: 20px; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <nav class="p-4 glass border-b border-white/5 flex justify-between items-center px-6 md:px-12 sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <a href="user/dashboard.php" class="flex items-center gap-2">
                <div class="p-1.5 bg-gradient-to-br from-yellow-400 to-orange-600 rounded-xl shadow-lg shadow-orange-500/20">
                    <img src="assets/images/logo-vip.png" alt="Logo" class="h-8 w-8 rounded-lg bg-black">
                </div>
                <span class="text-xl font-black tracking-tighter text-gradient">TOOLTX2026</span>
            </a>
        </div>
        
        <div class="flex items-center gap-6">
            <div class="hidden md:block">
                <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest mb-0.5">Số dư hiện tại</p>
                <p class="text-lg font-black text-gradient leading-none"><?php echo formatMoney($currentUser['balance']); ?></p>
            </div>
            <a href="user/deposit.php" class="p-2.5 bg-slate-800/80 backdrop-blur-md rounded-xl text-slate-400 hover:bg-slate-700/80 hover:text-white transition-all border border-white/10 shadow-lg group">
                <svg class="w-6 h-6 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
        </div>
    </nav>

    <main class="flex-1 flex items-center justify-center p-6 py-12">
        <div class="max-w-xl w-full">
            <div class="glass rounded-[3rem] p-8 md:p-12 text-center relative overflow-hidden border-white/10 shadow-2xl">
                <div class="absolute -right-24 -top-24 w-64 h-64 bg-yellow-500/5 rounded-full blur-3xl"></div>
                <div class="absolute -left-24 -bottom-24 w-64 h-64 bg-orange-500/5 rounded-full blur-3xl"></div>

                <?php if ($is_completed): ?>
                    <div class="w-24 h-24 bg-green-500/10 rounded-full flex items-center justify-center text-green-500 mx-auto mb-8 border border-green-500/20">
                        <?php echo getIcon('check', 'w-12 h-12'); ?>
                    </div>
                    <h1 class="text-4xl font-black mb-4 text-white uppercase tracking-tight">Thanh Toán Thành Công</h1>
                    <p class="text-slate-400 text-lg mb-10 leading-relaxed">Mã đơn #<?php echo htmlspecialchars($order['id']); ?> đã được xử lý tự động.</p>
                    <a href="user/dashboard.php" class="inline-flex items-center gap-3 bg-gradient-to-r from-yellow-400 to-orange-600 text-black font-black px-12 py-5 rounded-2xl text-lg hover:scale-105 active:scale-95 transition-all shadow-xl shadow-orange-500/20">
                        <?php echo getIcon('home', 'w-6 h-6'); ?> VỀ TRANG CHỦ
                    </a>

                <?php elseif ($is_cancelled || $is_expired): ?>
                    <div class="w-24 h-24 bg-red-500/10 rounded-full flex items-center justify-center text-red-500 mx-auto mb-8 border border-red-500/20">
                        <?php echo getIcon('x', 'w-12 h-12'); ?>
                    </div>
                    <h1 class="text-4xl font-black mb-4 text-white uppercase tracking-tight"><?php echo $is_expired ? 'Đơn Hàng Hết Hạn' : 'Đơn Hàng Đã Hủy'; ?></h1>
                    <p class="text-slate-400 text-lg mb-10 leading-relaxed">Giao dịch này không còn khả dụng. Vui lòng tạo đơn mới.</p>
                    <a href="user/deposit.php" class="inline-flex items-center gap-3 bg-white/5 text-white font-black px-12 py-5 rounded-2xl text-lg border border-white/10 hover:bg-white/10 transition-all">
                        <?php echo getIcon('wallet', 'w-6 h-6'); ?> TẠO ĐƠN MỚI
                    </a>

                <?php else: ?>
                    <div class="inline-flex items-center gap-3 px-4 py-2 bg-yellow-500/10 rounded-full border border-yellow-500/20 mb-8">
                        <span class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></span>
                        <span class="text-[10px] font-black text-yellow-500 uppercase tracking-widest">Đang chờ thanh toán</span>
                    </div>

                    <h1 class="text-3xl font-black mb-10 text-white uppercase tracking-tight">Quét Mã QR Để Nạp Tiền</h1>
                    
                    <div class="qr-container mb-10 inline-block p-6">
                        <img src="<?php echo $qr_url; ?>" alt="QR Code" class="w-64 h-64 md:w-80 md:h-80 object-contain">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left mb-10">
                        <div class="glass p-5 rounded-2xl border-white/5 group">
                            <p class="text-[10px] text-slate-500 uppercase font-black mb-1.5 tracking-widest">Ngân hàng</p>
                            <p class="font-bold text-slate-200"><?php echo htmlspecialchars($order['bank_name']); ?></p>
                        </div>
                        <div class="glass p-5 rounded-2xl border-white/5 group relative">
                            <p class="text-[10px] text-slate-500 uppercase font-black mb-1.5 tracking-widest">Số tiền nạp</p>
                            <p class="font-black text-xl text-yellow-500"><?php echo formatMoney($order['amount']); ?></p>
                        </div>
                        <div class="glass p-5 rounded-2xl border-white/5 group col-span-full">
                            <p class="text-[10px] text-slate-500 uppercase font-black mb-1.5 tracking-widest">Số tài khoản</p>
                            <div class="flex justify-between items-center">
                                <p class="font-bold text-slate-200 text-lg"><?php echo htmlspecialchars($order['account_no']); ?></p>
                                <button onclick="copy('<?php echo $order['account_no']; ?>', this)" class="text-[10px] font-black bg-yellow-500/10 text-yellow-500 px-3 py-1.5 rounded-lg border border-yellow-500/20 hover:bg-yellow-500 hover:text-black transition-all">SAO CHÉP</button>
                            </div>
                        </div>
                        <div class="glass p-5 rounded-2xl border-white/5 border-orange-500/20 group col-span-full bg-orange-500/5">
                            <p class="text-[10px] text-orange-500 uppercase font-black mb-1.5 tracking-widest">Nội dung chuyển khoản</p>
                            <div class="flex justify-between items-center">
                                <p class="font-black text-xl text-orange-500"><?php echo htmlspecialchars($order['description']); ?></p>
                                <button onclick="copy('<?php echo $order['description']; ?>', this)" class="text-[10px] font-black bg-orange-500/10 text-orange-500 px-3 py-1.5 rounded-lg border border-orange-500/20 hover:bg-orange-500 hover:text-black transition-all">SAO CHÉP</button>
                            </div>
                        </div>
                    </div>

                    <div class="glass p-6 rounded-3xl border-white/5 mb-8 flex items-center justify-center gap-6">
                        <div class="text-left">
                            <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest mb-1">Thời gian còn lại</p>
                            <p id="timer" class="text-3xl font-black text-white leading-none">20:00</p>
                        </div>
                        <div class="w-px h-10 bg-white/10"></div>
                        <div class="text-left">
                            <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest mb-1">Mã đơn hàng</p>
                            <p class="text-lg font-bold text-slate-400 leading-none">#<?php echo htmlspecialchars($order['id']); ?></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-slate-500 bg-black/20 p-4 rounded-2xl">
                        <div class="p-2 bg-yellow-500/10 rounded-lg text-yellow-500">
                            <?php echo getIcon('alert-triangle', 'w-5 h-5'); ?>
                        </div>
                        <p class="text-xs font-semibold leading-relaxed text-left italic">
                            Hệ thống sẽ tự động cộng tiền sau 1-3 phút. Vui lòng chuyển <span class="text-white font-bold">Chính xác số tiền</span> và <span class="text-white font-bold">Nội dung</span>.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-8 text-center">
                <a href="user/deposit.php" class="text-slate-500 hover:text-white transition-all text-sm font-bold flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Quay lại trang nạp tiền
                </a>
            </div>
        </div>
    </main>

    <script>
        function copy(text, btn) {
            navigator.clipboard.writeText(text);
            const originalText = btn.innerText;
            btn.innerText = 'ĐÃ COPY';
            btn.classList.replace('bg-yellow-500/10', 'bg-green-500/20');
            btn.classList.replace('text-yellow-500', 'text-green-500');
            setTimeout(() => {
                btn.innerText = originalText;
                btn.classList.replace('bg-green-500/20', 'bg-yellow-500/10');
                btn.classList.replace('text-green-500', 'text-yellow-500');
            }, 2000);
        }

        <?php if (!$is_completed && !$is_cancelled && !$is_expired): ?>
        // Countdown timer
        let timeLeft = <?php echo ($expiry_time - ($now - $created_at)); ?>;
        const timerElement = document.getElementById('timer');

        function updateTimer() {
            if (timeLeft <= 0) {
                location.reload();
                return;
            }
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            timeLeft--;
        }
        setInterval(updateTimer, 1000);
        updateTimer();

        // Check status polling
        setInterval(async () => {
            const r = await fetch('user/api/check-status.php?order_id=<?php echo $order['id']; ?>');
            const d = await r.json();
            if(d.status === 'completed' || d.status === 'cancelled' || d.status === 'expired') {
                location.reload();
            }
        }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>