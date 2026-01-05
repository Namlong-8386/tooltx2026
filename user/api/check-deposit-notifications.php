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

// 1. Lọc tất cả thông báo của user này liên quan đến nạp tiền
$userNotifs = [];
foreach ($notifications as $n) {
    if ((string)$n['user_id'] === (string)$user_id) {
        $type = $n['type'] ?? '';
        $title = $n['title'] ?? '';
        $message = $n['message'] ?? '';
        
        $isDeposit = ($type === 'deposit_approved' || $type === 'deposit_cancelled') || 
                     (stripos($title, 'Nạp tiền') !== false) ||
                     (stripos($message, 'Nạp tiền') !== false);
                     
        if ($isDeposit) {
            $userNotifs[] = $n;
        }
    }
}

// Sắp xếp theo thời gian mới nhất
usort($userNotifs, function($a, $b) {
    $timeA = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
    $timeB = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
    return $timeB <=> $timeA;
});

$latest = !empty($userNotifs) ? $userNotifs[0] : null;

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
    'debug' => [
        'id' => $user_id,
        'count' => count($userNotifs),
        'latest_id' => $latest ? $latest['id'] : null,
        'ts' => time()
    ]
]);
exit;
