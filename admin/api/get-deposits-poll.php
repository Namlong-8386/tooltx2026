<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once '../../core/functions.php';

if (!isAdmin()) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$deposits = readJSON('deposits');
$pending_count = 0;
foreach ($deposits as $d) {
    if (($d['status'] ?? '') === 'pending') {
        $pending_count++;
    }
}

$last_id = $_GET['last_id'] ?? '';
$has_new = false;
if ($last_id && !empty($deposits)) {
    $latest = end($deposits);
    if ((string)($latest['id'] ?? '') !== (string)$last_id && ($latest['status'] ?? '') === 'pending') {
        $has_new = true;
    }
}

ob_end_clean();
echo json_encode([
    'status' => 'success',
    'pending_count' => $pending_count,
    'has_new' => $has_new,
    'deposits' => $deposits // Return all for simplicity in refresh
]);
?>