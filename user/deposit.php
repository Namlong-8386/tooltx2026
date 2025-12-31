<?php
require_once '../core/functions.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Load current user data
$users = readJSON('users');
$currentUser = null;
foreach ($users as $u) {
    if ($u['id'] === $_SESSION['user_id']) {
        $currentUser = $u;
        break;
    }
}

if (!$currentUser) {
    header('Location: ../login.php');
    exit;
}

$banks = readJSON('banks');
$error = '';
$success = false;
$order = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Lỗi xác thực CSRF. Vui lòng thử lại.';
    } else {
        $amount = (int)$_POST['amount'];
        $bank_id = $_POST['bank_id'];
        
        if ($amount < 10000) {
            $error = 'Số tiền nạp tối thiểu là 10.000 VND.';
        } else {
            $selectedBank = null;
            foreach ($banks as $b) {
                if ($b['id'] == $bank_id) {
                    $selectedBank = $b;
                    break;
                }
            }

            if ($selectedBank) {
                $order_id = 'DEP' . strtoupper(generateRandomString(8));
                $description = 'NAP' . strtoupper(generateRandomString(6));
                
                $order = [
                    'id' => $order_id,
                    'order_id' => $order_id,
                    'user_id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'],
                    'amount' => $amount,
                    'bank_name' => $selectedBank['bank_name'],
                    'account_no' => $selectedBank['account_no'],
                    'account_name' => $selectedBank['account_name'],
                    'description' => $description,
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s'),
                    'expires_at' => date('Y-m-d H:i:s', strtotime('+20 minutes'))
                ];

                $deposits = readJSON('deposits');
                $deposits[] = $order;
                writeJSON('deposits', $deposits);
                
                $_SESSION['current_deposit_order'] = $order;
                header('Location: deposit.php?success=1');
                exit;
            } else {
                $error = 'Vui lòng chọn ngân hàng hợp lệ.';
            }
        }
    }
}

if (isset($_GET['success']) && isset($_SESSION['current_deposit_order'])) {
    $order = $_SESSION['current_deposit_order'];
    $deposits = readJSON('deposits');
    $found = false;
    foreach ($deposits as $d) {
        if (isset($d['id']) && $d['id'] === $order['id']) {
            $order = $d;
            $found = true;
            break;
        }
    }
    
    if ($found) {
        $success = true;
    } else {
        unset($_SESSION['current_deposit_order']);
        header('Location: deposit.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nạp Tiền - TOOLTX2026</title>
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
        .bank-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .bank-card:hover { transform: translateY(-5px); background: rgba(255, 255, 255, 0.08); }
        .qr-container { background: white; padding: 20px; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen flex flex-col" x-data="{ 
    isSuccess: <?php echo ($order && $order['status'] === 'completed') ? 'true' : 'false'; ?>,
    isCancelled: <?php echo ($order && ($order['status'] === 'cancelled' || $order['status'] === 'expired')) ? 'true' : 'false'; ?>,
    cancelReason: '<?php echo ($order && $order['status'] === 'expired') ? 'Giao dịch đã hết hạn' : 'Giao dịch đã bị hủy'; ?>',
    orderId: '<?php echo $order['id'] ?? ''; ?>',
    amount: '<?php echo formatMoney($order['amount'] ?? 0); ?>'
}">
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

    <main class="p-6 max-w-5xl mx-auto w-full mt-8">
        <template x-if="isSuccess">
            <div class="glass p-12 rounded-[3rem] border border-white/5 text-center relative overflow-hidden animate-fade-in">
                <div class="absolute -right-24 -top-24 w-96 h-96 bg-green-500/10 rounded-full blur-3xl"></div>
                <div class="w-20 h-20 bg-green-500/10 rounded-full flex items-center justify-center text-green-500 mx-auto mb-8">
                    <?php echo getIcon('check', 'w-10 h-10'); ?>
                </div>
                <h2 class="text-4xl font-black text-white mb-4">Nạp Tiền Thành Công!</h2>
                <p class="text-slate-400 text-lg mb-12">Số tiền đã được cộng vào tài khoản của bạn.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl mx-auto mb-12">
                    <div class="glass p-6 rounded-3xl border border-white/10">
                        <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Số tiền nạp</p>
                        <p class="text-2xl font-black text-yellow-500" x-text="amount"></p>
                    </div>
                    <div class="glass p-6 rounded-3xl border border-white/10">
                        <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Mã giao dịch</p>
                        <p class="text-2xl font-black text-white" x-text="orderId"></p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="dashboard.php" class="btn-primary text-black font-black px-12 py-5 rounded-[2rem] text-lg">TRANG CHỦ</a>
                    <a href="buy-key.php" class="glass text-white font-black px-12 py-5 rounded-[2rem] border-white/10 hover:bg-white/10 text-lg">MUA KEY NGAY</a>
                </div>
            </div>
        </template>

        <template x-if="isCancelled">
            <div class="glass p-12 rounded-[3rem] border border-white/5 text-center relative overflow-hidden animate-fade-in">
                <div class="absolute -right-24 -top-24 w-96 h-96 bg-red-500/10 rounded-full blur-3xl"></div>
                <div class="w-20 h-20 bg-red-500/10 rounded-full flex items-center justify-center text-red-500 mx-auto mb-8">
                    <?php echo getIcon('x', 'w-10 h-10'); ?>
                </div>
                <h2 class="text-4xl font-black text-white mb-4">Giao Dịch Bị Hủy</h2>
                <p class="text-slate-400 text-lg mb-12" x-text="cancelReason"></p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl mx-auto mb-12">
                    <div class="glass p-6 rounded-3xl border border-white/10">
                        <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Số tiền nạp</p>
                        <p class="text-2xl font-black text-red-500" x-text="amount"></p>
                    </div>
                    <div class="glass p-6 rounded-3xl border border-white/10">
                        <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Mã giao dịch</p>
                        <p class="text-2xl font-black text-white" x-text="orderId"></p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="deposit.php" class="btn-primary text-black font-black px-12 py-5 rounded-[2rem] text-lg">THỬ LẠI</a>
                    <a href="dashboard.php" class="glass text-white font-black px-12 py-5 rounded-[2rem] border-white/10 hover:bg-white/10 text-lg">TRANG CHỦ</a>
                </div>
            </div>
        </template>

        <template x-if="!isSuccess && !isCancelled">
            <div>
                <div class="glass p-8 rounded-[2.5rem] border border-white/5 mb-10 relative overflow-hidden">
                    <div class="absolute -right-12 -top-12 w-48 h-48 bg-yellow-500/5 rounded-full blur-3xl"></div>
                    <div class="flex items-center gap-4 relative z-10">
                        <a href="dashboard.php" class="p-2.5 bg-slate-800/80 backdrop-blur-md rounded-xl text-slate-400 hover:bg-slate-700/80 hover:text-white transition-all border border-white/10 shadow-lg group">
                            <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        </a>
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-yellow-500/10 rounded-xl text-yellow-500"><?php echo getIcon('wallet', 'w-6 h-6'); ?></div>
                            <div>
                                <h2 class="text-2xl font-black">Nạp Tiền</h2>
                                <p class="text-xs text-slate-400 uppercase tracking-widest font-bold">Hệ thống nạp tự động 24/7</p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!$success): ?>
                    <div class="glass p-8 rounded-[2.5rem] border border-white/5">
                        <?php if ($error): ?>
                            <div class="bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-2xl mb-8 text-sm flex items-center gap-3"><?php echo getIcon('x', 'w-5 h-5'); ?><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="POST" class="space-y-10">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-[0.2em] text-slate-500 mb-6 ml-1">1. Chọn phương thức thanh toán</label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <?php if (empty($banks)): ?>
                                        <p class="col-span-full text-red-400 text-sm italic">Hệ thống chưa cấu hình ngân hàng. Vui lòng liên hệ Admin.</p>
                                    <?php else: ?>
                                        <?php foreach ($banks as $b): ?>
                                            <label class="cursor-pointer group">
                                                <input type="radio" name="bank_id" value="<?php echo $b['id']; ?>" required class="hidden peer">
                                                <div class="p-6 glass rounded-3xl border border-white/5 peer-checked:border-yellow-500 peer-checked:bg-yellow-500/10 group-hover:border-white/20 transition-all text-center bank-card">
                                                    <div class="h-12 flex items-center justify-center mb-4"><img src="../assets/images/<?php echo $b['logo']; ?>" alt="<?php echo $b['bank_name']; ?>" class="max-h-full max-w-full object-contain filter brightness-110"></div>
                                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 peer-checked:text-yellow-500"><?php echo $b['bank_name']; ?></span>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-[0.2em] text-slate-500 mb-6 ml-1">2. Nhập số tiền cần nạp</label>
                                <div class="relative group">
                                    <div class="absolute left-6 top-1/2 -translate-y-1/2 text-yellow-500"><?php echo getIcon('wallet', 'w-6 h-6'); ?></div>
                                    <input type="number" name="amount" min="10000" step="1000" required placeholder="Tối thiểu 10.000 VND" class="w-full bg-white/5 border border-white/10 rounded-[2rem] pl-16 pr-20 py-6 focus:outline-none focus:border-yellow-500/50 focus:bg-white/10 text-2xl font-black text-yellow-500 placeholder:text-slate-700 transition-all">
                                    <div class="absolute right-8 top-1/2 -translate-y-1/2 text-slate-500 font-black tracking-widest">VND</div>
                                </div>
                                <div class="flex flex-wrap gap-2 mt-4 ml-1">
                                    <?php foreach([20000, 50000, 100000, 200000, 500000] as $val): ?>
                                        <button type="button" onclick="document.getElementsByName('amount')[0].value='<?php echo $val; ?>'" class="px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-xs font-bold transition-all">+<?php echo number_format($val/1000); ?>K</button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <button type="submit" class="w-full btn-primary text-black font-black py-6 rounded-[2rem] hover:scale-[1.02] active:scale-[0.98] transition-all text-xl flex items-center justify-center gap-3">TIẾP TỤC THANH TOÁN <?php echo getIcon('rocket', 'w-6 h-6'); ?></button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        <div class="lg:col-span-7 space-y-6">
                            <div class="glass p-8 rounded-[3rem] border border-white/5 text-center relative overflow-hidden">
                                <div class="absolute -left-24 -top-24 w-64 h-64 bg-yellow-500/5 rounded-full blur-3xl"></div>
                                <div class="flex items-center justify-between mb-8">
                                    <div class="text-left">
                                        <p class="text-[10px] text-slate-500 font-black uppercase tracking-widest mb-1">Số dư hiện tại</p>
                                        <p class="text-2xl font-black text-gradient" id="current-balance"><?php echo formatMoney($currentUser['balance'] ?? 0); ?></p>
                                    </div>
                                    <div class="flex items-center gap-2 px-3 py-1 bg-yellow-500/10 rounded-full border border-yellow-500/20" id="status-badge">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></span>
                                        <span class="text-[10px] font-black text-yellow-500 uppercase tracking-widest">Đang chờ thanh toán</span>
                                    </div>
                                </div>
                                <div class="qr-container group relative mx-auto inline-block mb-6">
                                    <img src="https://img.vietqr.io/image/<?php echo "{$order['bank_name']}-{$order['account_no']}-qr_only.png?amount={$order['amount']}&addInfo={$order['description']}&accountName=" . urlencode($order['account_name']); ?>" alt="VietQR" class="w-64 h-64 rounded-xl relative z-10">
                                    <div class="absolute inset-0 border-4 border-yellow-500/20 rounded-[24px] pointer-events-none group-hover:border-yellow-500/50 transition-all z-20"></div>
                                    <div class="absolute inset-x-4 top-4 h-0.5 bg-yellow-500/50 shadow-[0_0_15px_rgba(234,179,8,0.5)] animate-scan z-30"></div>
                                </div>
                                <div class="space-y-2 mb-8">
                                    <h3 class="text-2xl font-black text-slate-100">Quét mã để nạp tiền</h3>
                                    <p class="text-sm text-slate-400">Hệ thống sẽ tự động cập nhật sau khi nhận được tiền</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="glass p-4 rounded-2xl border border-white/5">
                                        <p class="text-[10px] text-slate-500 font-black uppercase tracking-widest mb-1">Trạng thái</p>
                                        <p class="text-sm font-bold text-yellow-500 flex items-center justify-center gap-2" id="order-status">
                                            <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Kiểm tra...
                                        </p>
                                    </div>
                                    <div class="glass p-4 rounded-2xl border border-white/5">
                                        <p class="text-[10px] text-slate-500 font-black uppercase tracking-widest mb-1">Thời gian còn lại</p>
                                        <p class="text-sm font-bold text-slate-200" id="expiry-timer"><?php 
                                            $rem = strtotime($order['expires_at']) - time();
                                            echo $rem > 0 ? sprintf('%02d:%02d', floor($rem / 60), $rem % 60) : '00:00';
                                        ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-5 space-y-4">
                            <div class="glass p-6 rounded-[2.5rem] border border-white/5">
                                <h4 class="text-xs font-black text-slate-500 uppercase tracking-[0.2em] mb-6 ml-1">Thông tin chuyển khoản</h4>
                                <div class="space-y-3">
                                    <?php $rows = [['Mã giao dịch', $order['id']], ['Ngân hàng', $order['bank_name']], ['Số tài khoản', $order['account_no']], ['Chủ tài khoản', $order['account_name']], ['Nội dung', $order['description'], true], ['Số tiền', formatMoney($order['amount'])]]; ?>
                                    <?php foreach ($rows as $r): ?>
                                        <div class="glass p-4 rounded-2xl border border-white/10 flex justify-between items-center group hover:bg-white/5 transition-all <?php echo isset($r[2]) ? 'bg-yellow-500/5' : ''; ?>">
                                            <div class="text-left">
                                                <p class="text-[9px] <?php echo isset($r[2]) ? 'text-yellow-500' : 'text-slate-500'; ?> font-black uppercase tracking-widest mb-0.5"><?php echo $r[0]; ?></p>
                                                <p class="font-black <?php echo isset($r[2]) ? 'text-yellow-500 text-lg' : 'text-slate-200'; ?>"><?php echo $r[1]; ?></p>
                                            </div>
                                            <button onclick="copyText('<?php echo strip_tags($r[1]); ?>', this)" class="p-2 hover:text-yellow-500 transition-colors"><?php echo getIcon('copy', 'w-4 h-4'); ?></button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-6 p-4 glass rounded-2xl border border-yellow-500/20 flex gap-4">
                                    <div class="p-2 bg-yellow-500/10 rounded-xl text-yellow-500 self-start"><?php echo getIcon('info', 'w-4 h-4'); ?></div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase text-yellow-500 tracking-widest mb-1">Lưu ý:</p>
                                        <p class="text-[10px] text-slate-400 leading-relaxed">Chuyển đúng nội dung và số tiền. Tiền sẽ vào tài khoản sau 1-3 phút.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                    function copyText(t,b){navigator.clipboard.writeText(t);const o=b.innerHTML;b.innerHTML='<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';setTimeout(()=>b.innerHTML=o,2000)}
                    (function(){
                        const tD=document.getElementById('expiry-timer');
                        if(tD){
                            let pts=tD.innerText.split(':');
                            let s=parseInt(pts[0])*60+parseInt(pts[1]);
                            const i=setInterval(()=>{
                                if(--s<=0){clearInterval(i);tD.innerText='00:00';window.location.reload();return}
                                tD.innerText=(Math.floor(s/60)<10?'0':'')+Math.floor(s/60)+':'+(s%60<10?'0':'')+(s%60);
                            },1000);
                        }
                        const oId='<?php echo $order['id']; ?>',sE=document.getElementById('order-status'),bE=document.getElementById('current-balance');
                        const pI=setInterval(async()=>{
                            try{
                                const r=await fetch(`api/check-status.php?id=${oId}`);
                                const d=await r.json();
                                if(d.status==='completed'){
                                    clearInterval(pI);
                                    if(bE && d.new_balance) bE.innerText=d.new_balance;
                                    const app = document.querySelector('[x-data]');
                                    if(app && app.__x) {
                                        app.__x.$data.orderId = d.deposit_id || oId;
                                        app.__x.$data.amount = d.amount_formatted || '<?php echo formatMoney($order['amount'] ?? 0); ?>';
                                        app.__x.$data.isSuccess = true;
                                    } else if(document.body._x_dataStack){
                                        document.body._x_dataStack[0].orderId = d.deposit_id || oId;
                                        document.body._x_dataStack[0].amount = d.amount_formatted || '<?php echo formatMoney($order['amount'] ?? 0); ?>';
                                        document.body._x_dataStack[0].isSuccess = true;
                                    }
                                }else if(d.status==='cancelled'||d.status==='expired'){
                                    clearInterval(pI);
                                    const app = document.querySelector('[x-data]');
                                    const reason = d.status === 'expired' ? 'Giao dịch đã hết hạn' : 'Giao dịch đã bị quản trị viên hủy';
                                    if(app && app.__x) {
                                        app.__x.$data.orderId = d.deposit_id || oId;
                                        app.__x.$data.amount = d.amount_formatted || '<?php echo formatMoney($order['amount'] ?? 0); ?>';
                                        app.__x.$data.cancelReason = reason;
                                        app.__x.$data.isCancelled = true;
                                    } else if(document.body._x_dataStack){
                                        document.body._x_dataStack[0].orderId = d.deposit_id || oId;
                                        document.body._x_dataStack[0].amount = d.amount_formatted || '<?php echo formatMoney($order['amount'] ?? 0); ?>';
                                        document.body._x_dataStack[0].cancelReason = reason;
                                        document.body._x_dataStack[0].isCancelled = true;
                                    }
                                }
                            }catch(e){}
                        },1000);
                    })();
                    </script>
                <?php endif; ?>
            </div>
        </template>
    </main>
    <script src="../assets/js/security.js"></script>
</body>
</html>