<?php
header('Content-Type: application/json');
require_once '../../core/functions.php';
require_once '../../core/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$notifications = readJSON('notifications');

// Find unread deposit-related notifications
$unread = array_filter($notifications, function($n) use ($user_id) {
    return $n['user_id'] === $user_id && (str_contains($n['title'], 'Nạp tiền') || str_contains($n['title'], 'Giao dịch'));
});

// Sort by date desc
usort($unread, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

$response = [
    'success' => true,
    'notification' => null
];

if (!empty($unread)) {
    $response['notification'] = reset($unread);
}

echo json_encode($response);
?>