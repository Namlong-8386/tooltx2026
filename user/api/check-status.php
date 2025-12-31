<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once '../../core/functions.php';

if (!isLoggedIn()) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$id = $_GET['id'] ?? '';
if (!$id) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID is required']);
    exit;
}

$deposits = readJSON('deposits');
$users = readJSON('users');
$found = false;

foreach ($deposits as $d) {
    if ((string)($d['id'] ?? '') === (string)$id) {
        $found = true;
        $status = $d['status'] ?? 'pending';
        
        // Check if expired
        $expires_at = $d['expires_at'] ?? '';
        if ($expires_at && strtotime($expires_at) < time() && $status === 'pending') {
            $status = 'expired';
        }
        
        $new_balance = formatMoney(0);
        foreach ($users as $u) {
            if ($u['id'] === $_SESSION['user_id']) {
                $new_balance = formatMoney($u['balance'] ?? 0);
                break;
            }
        }
        
        ob_end_clean();
        echo json_encode([
            'status' => $status,
            'new_balance' => $new_balance,
            'deposit_id' => $id,
            'amount_formatted' => formatMoney($d['amount'] ?? 0)
        ]);
        exit;
    }
}

if (!$found) {
    ob_end_clean();
    http_response_code(404);
    echo json_encode(['status' => 'not_found', 'deposit_id' => $id]);
    exit;
}
?>