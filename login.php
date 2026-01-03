<?php
require_once 'core/functions.php';

if (!function_exists('generateSessionTokenLocal')) {
    function generateSessionTokenLocal() {
        return bin2hex(random_bytes(32));
    }
}

if (isLoggedIn()) {
    header('Location: user/dashboard.php');
    exit;
}

$status = ''; // loading, success, error
$message = '';

if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $status = 'success';
    $message = 'Đăng xuất thành công! Hẹn gặp lại bạn.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = 'loading';
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $status = 'error';
        $message = 'Lỗi xác thực CSRF. Vui lòng thử lại.';
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $users = readJSON('users');
        $foundUser = null;
        foreach ($users as $user) {
            if ($user['username'] === $username && password_verify($password, $user['password'])) {
                $foundUser = $user;
                break;
            }
        }

        if ($foundUser) {
            $sessionToken = generateSessionTokenLocal();
            // Update user session token in JSON
            $users = readJSON('users');
            foreach ($users as &$u) {
                if ($u['id'] === $foundUser['id']) {
                    $u['session_token'] = $sessionToken;
                    break;
                }
            }
            writeJSON('users', $users);

            $_SESSION['user_id'] = $foundUser['id'];
            $_SESSION['username'] = $foundUser['username'];
            $_SESSION['role'] = $foundUser['role'];
            $_SESSION['session_token'] = $sessionToken;
            
            $status = 'success';
            $message = 'Đăng nhập thành công! Đang chuyển hướng...';
        } else {
            $status = 'error';
            $message = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - TOOLTX2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/transitions.css">
    <style>
        html { zoom: 0.9; }
        body { 
            background-color: #1e293b; 
            color: #f8fafc; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: 
                radial-gradient(at 0% 0%, rgba(234, 179, 8, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(249, 115, 22, 0.15) 0px, transparent 50%);
        }
        .glass { 
            background: rgba(255, 255, 255, 0.08); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255, 255, 255, 0.15); 
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
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4" x-data="{ status: '<?php echo $status; ?>', message: '<?php echo $message; ?>' }" x-init="if(status === 'success') setTimeout(() => window.location.href='user/dashboard.php', 2000)">
    
    <!-- Status Modal -->
    <template x-if="status !== ''">
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
            <div class="glass p-8 rounded-[2.5rem] max-w-sm w-full border border-white/10 text-center relative overflow-hidden shadow-2xl">
                <div class="absolute -top-24 -left-24 w-48 h-48 rounded-full blur-3xl opacity-20" :class="status === 'success' ? 'bg-green-500' : (status === 'error' ? 'bg-red-500' : 'bg-yellow-500')"></div>
                
                <!-- Loading -->
                <template x-if="status === 'loading'">
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 border-4 border-yellow-500/20 border-t-yellow-500 rounded-full status-spinner mb-6"></div>
                        <h3 class="text-xl font-black text-white">ĐANG XỬ LÝ...</h3>
                    </div>
                </template>

                <!-- Success -->
                <template x-if="status === 'success'">
                    <div class="flex flex-col items-center py-6">
                        <div class="relative mb-6">
                            <div class="absolute inset-0 bg-green-500/20 rounded-full blur-xl animate-pulse"></div>
                            <div class="relative p-5 bg-green-500/10 rounded-full border border-green-500/20 text-green-500">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-2xl font-black text-white mb-2 tracking-tight uppercase">THÀNH CÔNG!</h3>
                        <p class="text-slate-400 text-sm font-medium" x-text="message"></p>
                        <div class="mt-8 flex gap-1 justify-center">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-bounce" style="animation-delay: 0.3s"></div>
                        </div>
                    </div>
                </template>

                <!-- Error -->
                <template x-if="status === 'error'">
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 mb-6 status-icon-box">
                            <svg class="w-full h-full" viewBox="0 0 52 52">
                                <circle class="cross-circle" cx="26" cy="26" r="25" fill="none"/>
                                <path class="cross-line" fill="none" d="M16 16 36 36 M36 16 16 36"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-red-500 mb-2 uppercase">THẤT BẠI</h3>
                        <p class="text-slate-300 font-semibold mb-6" x-text="message"></p>
                        <button @click="status = ''" class="w-full py-3 bg-white/5 border border-white/10 rounded-2xl text-white font-bold hover:bg-white/10 transition-all">THỬ LẠI</button>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <div class="glass p-8 rounded-[2.5rem] w-full max-w-md border border-white/10 relative overflow-hidden">
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-yellow-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-orange-500/10 rounded-full blur-3xl"></div>
        
        <div class="text-center mb-10 relative">
            <div class="inline-block p-1.5 bg-gradient-to-br from-yellow-400 to-orange-600 rounded-2xl shadow-lg shadow-orange-500/20 mb-4">
                <img src="assets/images/logo-vip.png" alt="Logo" class="h-16 w-16 rounded-xl bg-black">
            </div>
            <h2 class="text-4xl font-black tracking-tighter text-gradient">ĐĂNG NHẬP</h2>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mt-2">Hệ thống AI dự đoán 2026</p>
        </div>

        <form method="POST" class="space-y-6 relative">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2 ml-1">Tên đăng nhập</label>
                <div class="relative group">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-yellow-500 transition-colors">
                        <?php echo getIcon('user', 'w-5 h-5'); ?>
                    </div>
                    <input type="text" name="username" required placeholder="Nhập tên đăng nhập..." 
                        class="w-full bg-white/5 border border-white/10 rounded-2xl pl-12 pr-6 py-4 focus:outline-none focus:border-yellow-500/50 focus:bg-white/10 transition-all font-semibold">
                </div>
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2 ml-1">Mật khẩu</label>
                <div class="relative group" x-data="{ show: false }">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-yellow-500 transition-colors">
                        <?php echo getIcon('shield', 'w-5 h-5'); ?>
                    </div>
                    <input :type="show ? 'text' : 'password'" name="password" required placeholder="Nhập mật khẩu..." 
                        class="w-full bg-white/5 border border-white/10 rounded-2xl pl-12 pr-12 py-4 focus:outline-none focus:border-yellow-500/50 focus:bg-white/10 transition-all font-semibold">
                    <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors">
                        <template x-if="!show">
                            <?php echo getIcon('eye', 'w-5 h-5'); ?>
                        </template>
                        <template x-if="show">
                            <?php echo getIcon('eye-off', 'w-5 h-5'); ?>
                        </template>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="w-full btn-primary text-black font-black py-4 rounded-2xl hover:scale-[1.02] active:scale-[0.98] transition-all text-lg flex items-center justify-center gap-2">
                ĐĂNG NHẬP
                <?php echo getIcon('rocket', 'w-5 h-5'); ?>
            </button>
        </form>

        <p class="mt-8 text-center text-slate-500 text-sm font-semibold">
            Chưa có tài khoản? <a href="register.php" class="text-yellow-500 hover:text-yellow-400 transition-colors underline decoration-yellow-500/30 underline-offset-4">Đăng ký ngay</a>
        </p>
    </div>
    <script src="assets/js/transitions.js"></script>
</body>
</html>
