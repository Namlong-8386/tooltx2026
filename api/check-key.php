<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/functions.php';

$key_code = $_GET['key'] ?? '';

if (empty($key_code)) {
    echo json_encode(['status' => 'error', 'message' => 'Key is required']);
    exit;
}

$keys = readJSON('keys');
$found_key = null;

foreach ($keys as $key) {
    if ($key['key_code'] === $key_code) {
        $found_key = $key;
        break;
    }
}

if (!$found_key) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid key']);
    exit;
}

// Logic to check expiration based on package_name
// Assuming packages like "1 Giờ", "3 Ngày", "999999 Ngày"
$created_at = strtotime($found_key['created_at']);
$package = $found_key['package_name'];

$duration = 0;
if (strpos($package, 'Giờ') !== false) {
    $hours = (int)filter_var($package, FILTER_SANITIZE_NUMBER_INT);
    $duration = $hours * 3600;
} elseif (strpos($package, 'Ngày') !== false) {
    $days = (int)filter_var($package, FILTER_SANITIZE_NUMBER_INT);
    $duration = $days * 86400;
}

$expiry_time = $created_at + $duration;
$current_time = time();
$is_expired = ($current_time > $expiry_time);

$response = [
    'status' => 'success',
    'key' => $found_key['key_code'],
    'package' => $found_key['package_name'],
    'created_at' => $found_key['created_at'],
    'expiry_at' => date('Y-m-d H:i:s', $expiry_time),
    'is_expired' => $is_expired,
    'linked_account' => $found_key['linked_account'] ?? null
];

echo json_encode($response);
?>