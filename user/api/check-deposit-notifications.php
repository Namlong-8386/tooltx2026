<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
require_once '../../core/functions.php';
require_once '../../core/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$notifications = readJSON('notifications');
$users = readJSON('users');

// 1. Tìm thông báo nạp tiền mới nhất của user này
$userNotifs = array_filter($notifications, function($n) use ($user_id) {
    $isDepositNotif = (isset($n['type']) && ($n['type'] === 'deposit_approved' || $n['type'] === 'deposit_cancelled')) || 
                      (isset($n['title']) && (stripos($n['title'], 'Nạp tiền') !== false)) ||
                      (isset($n['message']) && (stripos($n['message'], 'Nạp tiền') !== false));
    return (string)$n['user_id'] === (string)$user_id && $isDepositNotif;
});

// Sắp xếp theo thời gian mới nhất (id giảm dần cũng được nếu id có timestamp hoặc random nhưng được push vào cuối)
usort($userNotifs, function($a, $b) {
    $timeA = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
    $timeB = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
    return $timeB - $timeA;
});

$latest = !empty($userNotifs) ? array_values($userNotifs)[0] : null;

// 2. Lấy số dư mới nhất
$freshBalance = 0;
foreach ($users as $u) {
    if ((string)$u['id'] === (string)$user_id) {
        $freshBalance = $u['balance'] ?? 0;
        break;
    }
}

// 3. Trả về kết quả
echo json_encode([
    'success' => true,
    'notification' => $latest,
    'fresh_balance' => formatMoney($freshBalance),
    'balance_raw' => $freshBalance,
    'debug_user_id' => $user_id
]);
exit;
