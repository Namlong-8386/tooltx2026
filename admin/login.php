<?php
require_once '../core/functions.php';
require_once '../core/auth.php';

// Nếu admin đã đăng nhập, chuyển hướng đến dashboard
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Lỗi xác thực CSRF. Vui lòng thử lại.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    } elseif (loginAdmin($username, $password)) {
        header('Location: dashboard.php');
        exit;
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu admin không chính xác.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin - TOOLTX2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/transitions.css">
    <style>
        body { 
            background-color: #020617; 
            color: #f8fafc; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: 
                radial-gradient(at 0% 0%, rgba(234, 179, 8, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(249, 115, 22, 0.1) 0px, transparent 50%);
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
        .btn-primary {
            background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%);
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3);
        }
        .admin-badge {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="glass p-8 rounded-[2.5rem] w-full max-w-md border border-white/10 relative overflow-hidden">
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-red-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-orange-500/10 rounded-full blur-3xl"></div>
        
        <div class="text-center mb-10 relative">
            <div class="inline-block p-1.5 bg-gradient-to-br from-red-500 to-orange-600 rounded-2xl shadow-lg shadow-red-500/20 mb-4">
                <div class="h-16 w-16 rounded-xl bg-black flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <h2 class="text-4xl font-black tracking-tighter mb-2">ADMIN PANEL</h2>
            <p class="text-red-400 text-sm font-bold uppercase tracking-widest">Khu vực quản trị hệ thống</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-2xl mb-6 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6 relative">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2 ml-1">Tên đăng nhập Admin</label>
                <div class="relative group">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
                        </svg>
                    </div>
                    <input type="text" name="username" required placeholder="Nhập tên đăng nhập admin..." 
                        class="w-full bg-white/5 border border-white/10 rounded-2xl pl-12 pr-6 py-4 focus:outline-none focus:border-red-500/50 focus:bg-white/10 transition-all font-semibold">
                </div>
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2 ml-1">Mật khẩu Admin</label>
                <div class="relative group">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="password" name="password" required placeholder="Nhập mật khẩu admin..." 
                        class="w-full bg-white/5 border border-white/10 rounded-2xl pl-12 pr-6 py-4 focus:outline-none focus:border-red-500/50 focus:bg-white/10 transition-all font-semibold">
                </div>
            </div>
            
            <button type="submit" class="w-full bg-gradient-to-r from-red-500 to-orange-600 text-white font-black py-4 rounded-2xl hover:scale-[1.02] active:scale-[0.98] transition-all text-lg flex items-center justify-center gap-2 shadow-lg shadow-red-500/30">
                ĐĂNG NHẬP ADMIN
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 1a1 1 0 011-1h12a1 1 0 011 1H3zm0 4a1 1 0 011-1h12a1 1 0 011 1H3zm0 4a1 1 0 011-1h12a1 1 0 011 1H3zm0 4a1 1 0 011-1h12a1 1 0 011 1H3z" clip-rule="evenodd" />
                </svg>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-white/10">
            <p class="text-center text-slate-500 text-xs font-semibold">
                <span class="block mb-2">Đây là khu vực dành cho quản trị viên hệ thống</span>
                <a href="../index.php" class="text-yellow-500 hover:text-yellow-400 transition-colors underline decoration-yellow-500/30 underline-offset-4">Quay lại trang chủ</a>
            </p>
        </div>
    </div>
    <script src="../assets/js/transitions.js"></script>
</body>
</html>
