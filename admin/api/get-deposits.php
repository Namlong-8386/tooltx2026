<?php
header('Content-Type: application/json');
require_once '../../core/functions.php';
require_once '../../core/auth.php';

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$deposits = readJSON('deposits');
$statusFilter = $_GET['status'] ?? 'all';

$filteredDeposits = array_filter($deposits, function($d) use ($statusFilter) {
    if ($statusFilter === 'all') return true;
    return ($d['status'] ?? '') === $statusFilter;
});

// Sort by date descending
usort($filteredDeposits, function($a, $b) {
    return strtotime($b['created_at'] ?? '0') - strtotime($a['created_at'] ?? '0');
});

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$totalFiltered = count($filteredDeposits);
$totalPages = ceil($totalFiltered / $perPage);
$page = max(1, min($page, $totalPages));
$offset = ($page - 1) * $perPage;
$pagedDeposits = array_slice($filteredDeposits, $offset, $perPage);

// Statistics
$totalPending = 0;
$totalApprovedAmount = 0;
foreach ($deposits as $d) {
    if (($d['status'] ?? '') === 'pending') $totalPending++;
    if (($d['status'] ?? '') === 'completed') $totalApprovedAmount += ($d['amount'] ?? 0);
}

echo json_encode([
    'success' => true,
    'deposits' => $pagedDeposits,
    'totalPending' => $totalPending,
    'totalApprovedAmount' => $totalApprovedAmount,
    'totalDeposits' => count($deposits),
    'page' => $page,
    'totalPages' => $totalPages
]);
?>
