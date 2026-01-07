<?php
require_once 'core/functions.php';

$id = $_GET['id'] ?? '';
if (empty($id)) {
    die("Yêu cầu không hợp lệ.");
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #0f172a; 
            color: #f8fafc; 
            font-family: 'Plus Jakarta Sans', sans-serif;
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
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full glass rounded-[2.5rem] p-8 text-center relative overflow-hidden">
        <?php if ($is_completed): ?>
            <div class="text-green-500 mb-4"><?php echo getIcon('check', 'w-16 h-16 mx-auto'); ?></div>
            <h1 class="text-2xl font-black mb-2">THANH TOÁN THÀNH CÔNG</h1>
            <p class="text-slate-400 mb-6">Đơn hàng #<?php echo htmlspecialchars($order['id']); ?> đã được xử lý.</p>
            <a href="user/dashboard.php" class="inline-block bg-yellow-500 text-black font-bold px-8 py-3 rounded-xl">VỀ TRANG CHỦ</a>
        <?php elseif ($is_cancelled || $is_expired): ?>
            <div class="text-red-500 mb-4"><?php echo getIcon('x', 'w-16 h-16 mx-auto'); ?></div>
            <h1 class="text-2xl font-black mb-2"><?php echo $is_expired ? 'ĐƠN HÀNG HẾT HẠN' : 'ĐƠN HÀNG ĐÃ HỦY'; ?></h1>
            <p class="text-slate-400 mb-6">Vui lòng tạo đơn hàng mới để tiếp tục.</p>
            <a href="user/deposit.php" class="inline-block bg-white/10 text-white font-bold px-8 py-3 rounded-xl border border-white/10">QUAY LẠI</a>
        <?php else: ?>
            <h1 class="text-2xl font-black mb-6 text-gradient">QUÉT MÃ THANH TOÁN</h1>
            
            <div class="bg-white p-4 rounded-3xl mb-6 inline-block">
                <img src="<?php echo $qr_url; ?>" alt="QR Code" class="w-64 h-64">
            </div>

            <div class="space-y-4 text-left mb-8">
                <div class="glass p-4 rounded-2xl border-white/5">
                    <p class="text-[10px] text-slate-500 uppercase font-black mb-1">Ngân hàng</p>
                    <p class="font-bold"><?php echo htmlspecialchars($order['bank_name']); ?></p>
                </div>
                <div class="glass p-4 rounded-2xl border-white/5">
                    <p class="text-[10px] text-slate-500 uppercase font-black mb-1">Số tài khoản</p>
                    <p class="font-bold flex justify-between">
                        <span><?php echo htmlspecialchars($order['account_no']); ?></span>
                        <button onclick="navigator.clipboard.writeText('<?php echo $order['account_no']; ?>')" class="text-yellow-500 text-xs">SAO CHÉP</button>
                    </p>
                </div>
                <div class="glass p-4 rounded-2xl border-white/5">
                    <p class="text-[10px] text-slate-500 uppercase font-black mb-1">Số tiền</p>
                    <p class="font-bold text-yellow-500"><?php echo formatMoney($order['amount']); ?></p>
                </div>
                <div class="glass p-4 rounded-2xl border-white/5">
                    <p class="text-[10px] text-slate-500 uppercase font-black mb-1">Nội dung chuyển tiền</p>
                    <p class="font-bold flex justify-between">
                        <span class="text-orange-500"><?php echo htmlspecialchars($order['description']); ?></span>
                        <button onclick="navigator.clipboard.writeText('<?php echo $order['description']; ?>')" class="text-yellow-500 text-xs">SAO CHÉP</button>
                    </p>
                </div>
            </div>

            <div class="text-sm text-slate-400 mb-4">
                <p>Đơn hàng sẽ hết hạn sau:</p>
                <p id="timer" class="text-xl font-black text-white">20:00</p>
            </div>

            <p class="text-xs text-slate-500 italic">Vui lòng chuyển chính xác số tiền và nội dung để được cộng tiền tự động.</p>
        <?php endif; ?>
    </div>

    <script>
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
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>