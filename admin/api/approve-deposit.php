<?php
// Ensure no output before JSON
ob_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

require_once '../../core/functions.php';
require_once '../../core/auth.php';

// Check admin without redirect
if (!isAdminLoggedIn()) {
    http_response_code(401);
    ob_end_clean();
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    ob_end_clean();
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;
$action = $input['action'] ?? null;

if (!$id || !$action) {
    http_response_code(400);
    ob_end_clean();
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$deposits = readJSON('deposits');
$users = readJSON('users');
$notifications = readJSON('notifications');

$found = false;
$approvedUser = null;

foreach ($deposits as &$d) {
    if ((string)$d['id'] === (string)$id && isset($d['status']) && $d['status'] === 'pending') {
        if ($action === 'approve') {
            $d['status'] = 'completed';
            $d['updated_at'] = date('Y-m-d H:i:s');
            
            // Update user balance
            foreach ($users as &$u) {
                if ($u['id'] == $d['user_id'] || $u['username'] == ($d['username'] ?? '')) {
                    $u['balance'] = ($u['balance'] ?? 0) + $d['amount'];
                    $approvedUser = $u;
                    break;
                }
            }
            writeJSON('users', $users);
            
            // Send notification to user
            if ($approvedUser) {
                $notification = [
                    'id' => 'NOTIF' . generateRandomString(8),
                    'user_id' => $approvedUser['id'],
                    'type' => 'deposit_approved',
                    'title' => 'Nạp tiền thành công',
                    'message' => 'Yêu cầu nạp tiền ' . formatMoney($d['amount']) . ' đã được duyệt. Số dư của bạn: ' . formatMoney($approvedUser['balance']),
                    'amount' => $d['amount'],
                    'deposit_id' => $d['id'],
                    'is_read' => false,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $notifications[] = $notification;
                writeJSON('notifications', $notifications);
            }
            
            $statusMsg = 'Đã duyệt nạp tiền thành công!';
        } else {
            $d['status'] = 'cancelled';
            $d['updated_at'] = date('Y-m-d H:i:s');
            $statusMsg = 'Đã hủy yêu cầu nạp tiền.';
        }
        $found = true;
        break;
    }
}

writeJSON('deposits', $deposits);

// Clean any buffered output
ob_end_clean();

if ($found) {
    echo json_encode([
        'success' => true,
        'message' => $statusMsg,
        'deposit_id' => $id,
        'action' => $action
    ]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Deposit not found']);
}
exit;
