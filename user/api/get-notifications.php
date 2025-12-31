<?php
header('Content-Type: application/json');
require_once '../../core/functions.php';
require_once '../../core/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$notifications = readJSON('notifications');
$user_id = $_SESSION['user_id'];

// Get unread notifications for current user
$userNotifications = array_filter($notifications, function($n) use ($user_id) {
    return $n['user_id'] === $user_id;
});

// Sort by date descending
usort($userNotifications, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

echo json_encode([
    'success' => true,
    'notifications' => $userNotifications,
    'unread_count' => count(array_filter($userNotifications, function($n) { return !$n['is_read']; }))
]);
?>
