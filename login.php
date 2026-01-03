<?php
require_once 'core/functions.php';

function generateSessionTokenLocal() {
    return bin2hex(random_bytes(32));
}

if (isLoggedIn()) {
    header('Location: user/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Lỗi xác thực CSRF. Vui lòng thử lại.';
    } else {
        $username = trim($_POST['username']);
    $password = $_POST['password'];

    $users = readJSON('users');
    $found = false;
    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            $sessionToken = generateSessionTokenLocal();
            
            // Update user session token in JSON
            foreach ($users as &$u) {
                if ($u['id'] === $user['id']) {
                    $u['session_token'] = $sessionToken;
                    break;
                }
            }
            writeJSON('users', $users);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['session_token'] = $sessionToken;
            $found = true;
            header('Location: user/dashboard.php');
            exit;
        }
    }
        if (!$found) {
            $error = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
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
        html {
            zoom: 0.9;
        }
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
<body class="min-h-screen flex items-center justify-center p-4">
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

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-2xl mb-6 text-sm flex items-center gap-3">
                <?php echo getIcon('x', 'w-5 h-5'); ?>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

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
