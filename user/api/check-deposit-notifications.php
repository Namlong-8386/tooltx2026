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

// Find unread deposit-related notifications
$unread = array_filter($notifications, function($n) use ($user_id) {
    return $n['user_id'] === $user_id && !($n['is_read'] ?? false);
});

// Sort by date desc
usort($unread, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

$latest = !empty($unread) ? reset($unread) : null;

// Get fresh balance
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