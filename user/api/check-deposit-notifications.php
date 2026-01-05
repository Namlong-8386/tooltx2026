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

// Tìm thông báo chưa đọc liên quan đến nạp tiền
$unread = array_filter($notifications, function($n) use ($user_id) {
    // Lọc các thông báo nạp tiền dựa trên type hoặc nội dung title
    $isDepositNotif = (isset($n['type']) && ($n['type'] === 'deposit_approved' || $n['type'] === 'deposit_cancelled')) || 
                      (isset($n['title']) && (strpos($n['title'], 'Nạp tiền') !== false));
    return $n['user_id'] === $user_id && !($n['is_read'] ?? false) && $isDepositNotif;
});

// Sắp xếp theo thời gian mới nhất
usort($unread, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

$latest = !empty($unread) ? reset($unread) : null;

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
    'balance_raw' => $freshBalance
]);
?>