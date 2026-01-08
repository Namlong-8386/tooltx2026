<?php
require_once __DIR__ . '/../core/functions.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$users = readJSON('users');
$currentUser = null;
foreach ($users as $user) {
    if ($user['id'] === $_SESSION['user_id']) {
        $currentUser = $user;
        break;
    }
}

// Lấy thông tin Key của người dùng
$keys = readJSON('keys');
$activeKey = null;
foreach ($keys as $key) {
    if ($key['user_id'] === $currentUser['id']) {
        // Ưu tiên key còn hạn
        $created_at = strtotime($key['created_at']);
        $package = $key['package_name'];
        $duration = 0;
        if (strpos($package, 'Giờ') !== false) {
            $hours = (int)filter_var($package, FILTER_SANITIZE_NUMBER_INT);
            $duration = $hours * 3600;
        } elseif (strpos($package, 'Ngày') !== false) {
            $days = (int)filter_var($package, FILTER_SANITIZE_NUMBER_INT);
            $duration = $days * 86400;
        }
        $expiry_time = $created_at + $duration;
        if (time() < $expiry_time) {
            $activeKey = $key;
            $activeKey['expiry_at'] = $expiry_time;
            break;
        }
    }
}

$games = [
    ['name' => 'Go88', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Go88', 'url' => 'https://play.go88.vin/', 'status' => 'Hoạt động'],
    ['name' => 'Sunwin', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Sunwin', 'url' => 'https://web.sun.me/?affId=Sunwin', 'status' => 'Hoạt động'],
    ['name' => '789Club', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=789Club', 'url' => 'https://789.club/', 'status' => 'Hoạt động'],
    ['name' => 'B52', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=B52', 'url' => 'https://b52.club/', 'status' => 'Hoạt động'],
    ['name' => 'Rikvip', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Rikvip', 'url' => 'https://rik.vip/', 'status' => 'Hoạt động'],
    ['name' => 'Manclub', 'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Manclub', 'url' => 'https://man.club/', 'status' => 'Hoạt động'],
];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn Game Tool Tài Xỉu - TOOLTX2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/transitions.css">
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
            min-height: screen;
        }
        .glass { 
            background: rgba(255, 255, 255, 0.08); 
            backdrop-filter: blur(16px); 
            border: 1px solid rgba(255, 255, 255, 0.15); 
        }
        .text-gradient {
            background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Robot Prediction Styles */
        #robotContainer {
            position: fixed;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            display: none;
            z-index: 9999;
            cursor: move;
        }

        #robotInner {
            display: flex;
            align-items: center;
            transform: rotate(90deg);
            transform-origin: left center;
        }

        #robotIcon {
            width: 95px;
            height: 95px;
            margin-right: 8px;
            pointer-events: none;
        }

        #robotText {
            background: rgba(50, 50, 50, 0.7);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: #fff;
            padding: 6px 10px;
            border-radius: 10px;
            font-size: 12px;
            line-height: 1.4;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.3);
            white-space: nowrap;
            max-width: 200px;
            position: relative;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        #robotText #line1 {
            font-size: 17px;
            color: #fff;
        }

        #robotText #line2 {
            font-size: 17px;
            color: #fff;
            font-weight: 500;
        }

        /* Iframe Styles */
        #iframeGame {
            display: none; 
            width: 100vw; 
            height: 100vh; 
            border: none;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            background: #000;
        }

        /* Logout Button in Game */
        .game-logout-btn {
            position: fixed;
            top: 12px;
            right: 12px;
            background: rgba(239, 68, 68, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 16px;
            cursor: pointer;
            z-index: 1002;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 8px rgba(239, 68, 68, 0.3);
            transition: all 0.3s ease;
        }

        .game-logout-btn:hover {
            background: rgba(239, 68, 68, 1);
            transform: translateY(-2px);
        }
    </style>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
</head>
<body class="min-h-screen">
    <div class="max-w-7xl mx-auto px-6 md:px-12 py-8">
        <div class="flex items-center justify-between mb-12">
            <div class="flex items-center gap-6">
                <div class="relative">
                    <a href="dashboard.php" class="p-3 bg-white/5 border border-white/10 rounded-2xl text-slate-400 hover:text-white hover:bg-white/10 transition-all shadow-lg block">
                        <?php echo getIcon('arrow-left', 'w-6 h-6'); ?>
                    </a>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_10px_rgba(34,197,94,1)] animate-pulse"></span>
                        <p class="text-white text-[10px] font-black uppercase tracking-[0.3em]">Hệ thống Tool AI</p>
                    </div>
                    <h1 class="text-3xl font-black text-gradient uppercase tracking-tight">Chọn Game Tool Tài Xỉu</h1>
                </div>
            </div>
            <div class="glass px-6 py-3 rounded-2xl flex items-center gap-4">
                <div class="p-2 bg-green-500 rounded-xl text-white shadow-[0_0_15px_rgba(34,197,94,0.5)]">
                    <?php echo getIcon('clock', 'w-6 h-6'); ?>
                </div>
                <div>
                    <p class="text-[10px] text-gradient font-black uppercase tracking-widest">Thời gian Key</p>
                    <span class="font-bold text-white">
                        <?php 
                        if ($activeKey) {
                            $remaining = $activeKey['expiry_at'] - time();
                            if ($remaining > 86400) {
                                echo ceil($remaining / 86400) . ' Ngày';
                            } elseif ($remaining > 3600) {
                                echo ceil($remaining / 3600) . ' Giờ';
                            } else {
                                echo ceil($remaining / 60) . ' Phút';
                            }
                        } else {
                            echo 'Chưa có Key';
                        }
                        ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            <?php foreach ($games as $game): ?>
            <div class="group relative glass rounded-[2.5rem] p-8 hover:border-yellow-500/30 transition-all duration-500 hover:shadow-2xl hover:shadow-yellow-500/10 flex flex-col items-center">
                <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity rounded-[2.5rem]"></div>
                
                <div class="relative flex flex-col items-center w-full">
                    <div class="w-24 h-24 mb-6 p-1 bg-gradient-to-br from-yellow-400 to-orange-600 rounded-3xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-500 shadow-xl shadow-orange-500/20">
                        <div class="w-full h-full rounded-2xl bg-slate-900/90 p-2 flex items-center justify-center overflow-hidden">
                            <img src="<?php echo $game['image']; ?>" alt="<?php echo $game['name']; ?>" class="w-full h-full object-contain rounded-xl">
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-black text-slate-100 mb-1 tracking-tight"><?php echo $game['name']; ?></h3>
                    <div class="flex items-center gap-2 mb-8">
                        <span class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.8)] animate-pulse"></span>
                        <span class="text-[10px] font-black text-green-500 uppercase tracking-widest drop-shadow-[0_0_5px_rgba(34,197,94,0.5)]"><?php echo $game['status']; ?></span>
                    </div>

                    <button onclick="startGame('<?php echo $game['name']; ?>', '<?php echo $game['url']; ?>')" class="w-full py-4 glass rounded-2xl text-xs font-black hover:bg-yellow-500 hover:text-black transition-all border border-white/5 shadow-lg active:scale-95 uppercase tracking-widest">
                        VÀO GAME
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Robot Prediction with Lottie Animation -->
    <div id="robotContainer">
        <div id="robotInner">
            <dotlottie-player 
                id="robotIcon" 
                src="https://lottie.host/55ab9688-9a63-4f35-93f8-b40bd7fb8058/4n00MLJLZk.lottie" 
                background="transparent" 
                speed="1" 
                loop 
                autoplay>
            </dotlottie-player>
            <div id="robotText">
                <div id="line1"><strong>Đang tải...</strong></div>
                <div id="line2"></div>
            </div>
        </div>
    </div>

    <!-- Logout Button in Game -->
    <button class="game-logout-btn" id="gameLogoutBtn" onclick="backToMenu()" style="display: none;">
        <?php echo getIcon('logout', 'w-6 h-6'); ?>
    </button>

    <!-- Game Iframe -->
    <iframe id="iframeGame" src=""></iframe>

    <script>
        let predictionInterval = null;

        function startGame(gameName, gameUrl) {
            const iframe = document.getElementById('iframeGame');
            const robotContainer = document.getElementById('robotContainer');
            const logoutBtn = document.getElementById('gameLogoutBtn');
            
            iframe.src = gameUrl;
            iframe.style.display = 'block';
            robotContainer.style.display = 'block';
            logoutBtn.style.display = 'flex';
            
            // Start mock predictions
            startPredictions(gameName);
        }

        function backToMenu() {
            const iframe = document.getElementById('iframeGame');
            const robotContainer = document.getElementById('robotContainer');
            const logoutBtn = document.getElementById('gameLogoutBtn');
            
            iframe.src = '';
            iframe.style.display = 'none';
            robotContainer.style.display = 'none';
            logoutBtn.style.display = 'none';
            
            if (predictionInterval) {
                clearInterval(predictionInterval);
            }
        }

        function startPredictions(gameName) {
            if (predictionInterval) clearInterval(predictionInterval);
            
            const outcomes = ['TÀI', 'XỈU'];
            const robotText = document.getElementById('line1');
            const robotSubText = document.getElementById('line2');

            predictionInterval = setInterval(() => {
                const outcome = outcomes[Math.floor(Math.random() * outcomes.length)];
                const percentage = Math.floor(Math.random() * 20) + 75; // 75-95%
                
                robotText.innerHTML = `<strong>DỰ ĐOÁN: ${outcome}</strong>`;
                robotSubText.innerHTML = `Độ chính xác: ${percentage}%`;
            }, 5000);
        }

        // Draggable Robot
        const robot = document.getElementById('robotContainer');
        let isDragging = false;
        let currentX;
        let currentY;
        let initialX;
        let initialY;
        let xOffset = 0;
        let yOffset = 0;

        function dragStart(e) {
            if (e.type === 'touchstart') {
                initialX = e.touches[0].clientX - xOffset;
                initialY = e.touches[0].clientY - yOffset;
            } else {
                initialX = e.clientX - xOffset;
                initialY = e.clientY - yOffset;
            }
            if (e.target === robot || robot.contains(e.target)) {
                isDragging = true;
            }
        }

        function dragEnd(e) {
            initialX = currentX;
            initialY = currentY;
            isDragging = false;
        }

        function drag(e) {
            if (isDragging) {
                e.preventDefault();
                if (e.type === 'touchmove') {
                    currentX = e.touches[0].clientX - initialX;
                    currentY = e.touches[0].clientY - initialY;
                } else {
                    currentX = e.clientX - initialX;
                    currentY = e.clientY - initialY;
                }
                xOffset = currentX;
                yOffset = currentY;
                setTranslate(currentX, currentY, robot);
            }
        }

        function setTranslate(xPos, yPos, el) {
            el.style.transform = `translate3d(${xPos}px, ${yPos}px, 0) translateY(-50%)`;
        }

        robot.addEventListener('touchstart', dragStart, false);
        robot.addEventListener('touchend', dragEnd, false);
        robot.addEventListener('touchmove', drag, false);
        robot.addEventListener('mousedown', dragStart, false);
        robot.addEventListener('mouseup', dragEnd, false);
        robot.addEventListener('mousemove', drag, false);
    </script>
    <script src="../assets/js/transitions.js"></script>
</body>
</html>