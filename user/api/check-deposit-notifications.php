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

// Tìm tất cả thông báo của user này chưa được đánh dấu là "đã xử lý bởi Dashboard"
// Chúng ta sẽ dùng một cờ ảo hoặc so sánh với localStorage ở phía client
$userNotifs = array_filter($notifications, function($n) use ($user_id) {
    $isDepositNotif = (isset($n['type']) && ($n['type'] === 'deposit_approved' || $n['type'] === 'deposit_cancelled')) || 
                      (isset($n['title']) && (strpos($n['title'], 'Nạp tiền') !== false));
    return $n['user_id'] === $user_id && $isDepositNotif;
});

// Sắp xếp theo thời gian mới nhất
usort($userNotifs, function($a, $b) {
    return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
});

// Lấy thông báo mới nhất (không quan tâm is_read vì admin có thể chưa set is_read=true cho mọi thông báo)
$latest = !empty($userNotifs) ? reset($userNotifs) : null;

// Lấy số dư mới nhất
$freshBalance = 0;
foreach ($users as $u) {
    if ($u['id'] === $user_id) {
        $freshBalance = $u['balance'] ?? 0;
        break;
    }
}

echo json_encode([
    'success' => true,
    'notification' => $latest,
    'fresh_balance' => formatMoney($freshBalance),
    'balance_raw' => $freshBalance,
    'server_time' => time()
]);
?>