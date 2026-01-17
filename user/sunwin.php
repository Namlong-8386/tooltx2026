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

$keys = readJSON('keys');
$activeKey = null;
foreach ($keys as $key) {
    if ($key['user_id'] === $currentUser['id']) {
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

$gameName = "Sunwin";
$gameUrl = "https://web.sun.me/?affId=Sunwin";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dự Đoán Sunwin - TOOLTX2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/transitions.css">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #000;
        }
        #robotContainer {
            position: fixed;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            z-index: 9999;
            cursor: move;
            will-change: transform;
            transition: transform 0.05s linear;
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
        #robotText #line1 { font-size: 17px; color: #fff; }
        #robotText #line2 { font-size: 17px; color: #fff; font-weight: 500; }
        #iframeGame {
            width: 100vw; 
            height: 100vh; 
            border: none;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }
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
        .game-logout-btn:hover { background: rgba(239, 68, 68, 1); transform: translateY(-2px); }
        #rotateRobotBtn {
            position: fixed;
            bottom: 15px;
            right: 65px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        #rotateRobotBtn:hover { background: rgba(255, 255, 255, 0.2); transform: scale(1.1); }
    </style>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
</head>
<body>
    <div id="robotContainer">
        <div id="robotInner">
            <dotlottie-player id="robotIcon" src="https://lottie.host/55ab9688-9a63-4f35-93f8-b40bd7fb8058/4n00MLJLZk.lottie" background="transparent" speed="1" loop autoplay></dotlottie-player>
            <div id="robotText">
                <div id="line1"><strong>Đang tải...</strong></div>
                <div id="line2"></div>
            </div>
        </div>
    </div>
    <a href="taixiu-select.php" class="game-logout-btn">
        <?php echo getIcon('logout', 'w-6 h-6'); ?>
    </a>
    <iframe id="iframeGame" src="<?php echo $gameUrl; ?>"></iframe>
    <button id="rotateRobotBtn" onclick="rotateRobot()" title="Xoay Robot">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
    </button>
    <script>
        let robotRotation = 90;
        function rotateRobot() {
            const robotInner = document.getElementById('robotInner');
            robotRotation = (robotRotation + 90) % 360;
            robotInner.style.transform = `rotate(${robotRotation}deg)`;
        }
        function startPredictions() {
            const outcomes = ['TÀI', 'XỈU'];
            const robotText = document.getElementById('line1');
            const robotSubText = document.getElementById('line2');
            setInterval(() => {
                const outcome = outcomes[Math.floor(Math.random() * outcomes.length)];
                const percentage = Math.floor(Math.random() * 20) + 75;
                robotText.innerHTML = `<strong>DỰ ĐOÁN: ${outcome}</strong>`;
                robotSubText.innerHTML = `Độ chính xác: ${percentage}%`;
            }, 5000);
        }
        startPredictions();
        const robot = document.getElementById('robotContainer');
        let isDragging = false, currentX, currentY, initialX, initialY, xOffset = 0, yOffset = 0;
        function dragStart(e) {
            if (e.type === 'touchstart') {
                initialX = e.touches[0].clientX - xOffset;
                initialY = e.touches[0].clientY - yOffset;
            } else {
                initialX = e.clientX - xOffset;
                initialY = e.clientY - yOffset;
            }
            if (e.target === robot || robot.contains(e.target)) isDragging = true;
        }
        function dragEnd(e) { initialX = currentX; initialY = currentY; isDragging = false; }
        function drag(e) {
            if (isDragging) {
                e.preventDefault();
                let clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
                let clientY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY;
                currentX = clientX - initialX;
                currentY = clientY - initialY;
                xOffset = currentX; yOffset = currentY;
                requestAnimationFrame(() => { robot.style.transform = `translate3d(${currentX}px, ${currentY}px, 0) translateY(-50%)`; });
            }
        }
        robot.addEventListener('touchstart', dragStart, false);
        robot.addEventListener('touchend', dragEnd, false);
        robot.addEventListener('touchmove', drag, false);
        robot.addEventListener('mousedown', dragStart, false);
        robot.addEventListener('mouseup', dragEnd, false);
        robot.addEventListener('mousemove', drag, false);
    </script>
</body>
</html>