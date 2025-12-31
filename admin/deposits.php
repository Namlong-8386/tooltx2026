<?php
require_once '../core/functions.php';
require_once '../core/auth.php';
require_once '../core/icons.php';

requireAdmin();

$statusMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $deposits = readJSON('deposits');
    $users = readJSON('users');
    $id = $_POST['id'];
    $action = $_POST['action'];
    
    $found = false;
    foreach ($deposits as &$d) {
        $depositId = (string)($d['id'] ?? '');
        if ($depositId === (string)$id && isset($d['status']) && $d['status'] === 'pending') {
            if ($action === 'approve') {
                $d['status'] = 'completed';
                $d['updated_at'] = date('Y-m-d H:i:s');
                // Update user balance
                foreach ($users as &$u) {
                    if ($u['id'] == $d['user_id'] || $u['username'] == ($d['username'] ?? '')) {
                        $u['balance'] = ($u['balance'] ?? 0) + $d['amount'];
                        break;
                    }
                }
                writeJSON('users', $users);
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
    if ($found) {
        header('Location: deposits.php?msg=' . urlencode($statusMsg));
        exit;
    }
}

$deposits = readJSON('deposits');
$msg = $_GET['msg'] ?? '';

// Pagination and Filtering logic
$statusFilter = $_GET['status'] ?? 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;

$filteredDeposits = array_filter($deposits, function($d) use ($statusFilter) {
    if ($statusFilter === 'all') return true;
    return ($d['status'] ?? '') === $statusFilter;
});

// Sort by date descending
usort($filteredDeposits, function($a, $b) {
    return strtotime($b['created_at'] ?? '0') - strtotime($a['created_at'] ?? '0');
});

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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nạp tiền - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        html { zoom: 0.8; }
        body { background-color: #0f172a; color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .btn-approve { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); }
        .btn-reject { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .loading-spinner { animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-4xl font-black text-white tracking-tighter mb-2">QUẢN LÝ NẠP TIỀN</h1>
                <p class="text-slate-400 text-sm font-medium">Duyệt và theo dõi lịch sử nạp tiền hệ thống</p>
            </div>
            <div class="flex gap-3">
                <a href="dashboard.php" class="px-6 py-3 glass rounded-2xl hover:bg-white/10 transition-all flex items-center gap-2 font-bold text-sm">
                    <?php echo getIcon('home', 'w-4 h-4'); ?> Quay lại
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="glass p-6 rounded-3xl border-l-4 border-l-yellow-500">
                <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">Chờ duyệt</p>
                <p class="text-3xl font-black text-yellow-500"><?php echo $totalPending; ?> <span class="text-sm font-medium text-slate-400">Yêu cầu</span></p>
            </div>
            <div class="glass p-6 rounded-3xl border-l-4 border-l-green-500">
                <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">Tổng tiền đã duyệt</p>
                <p class="text-3xl font-black text-green-500"><?php echo formatMoney($totalApprovedAmount); ?></p>
            </div>
            <div class="glass p-6 rounded-3xl border-l-4 border-l-blue-500">
                <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">Tổng giao dịch</p>
                <p class="text-3xl font-black text-blue-500"><?php echo count($deposits); ?> <span class="text-sm font-medium text-slate-400">Tổng cộng</span></p>
            </div>
        </div>

        <?php if ($msg): ?>
        <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 text-green-500 rounded-2xl text-sm font-bold animate-bounce text-center">
            <?php echo htmlspecialchars($msg); ?>
        </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="flex flex-wrap gap-4 mb-6">
            <a href="?status=all" class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all <?php echo $statusFilter === 'all' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/20' : 'glass text-slate-400 hover:bg-white/10'; ?>">Tất cả</a>
            <a href="?status=pending" class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all <?php echo $statusFilter === 'pending' ? 'bg-yellow-500 text-black shadow-lg shadow-yellow-500/20' : 'glass text-slate-400 hover:bg-white/10'; ?>">Chờ duyệt</a>
            <a href="?status=completed" class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all <?php echo $statusFilter === 'completed' ? 'bg-green-500 text-white shadow-lg shadow-green-500/20' : 'glass text-slate-400 hover:bg-white/10'; ?>">Đã hoàn tất</a>
            <a href="?status=cancelled" class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all <?php echo $statusFilter === 'cancelled' ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'glass text-slate-400 hover:bg-white/10'; ?>">Đã hủy</a>
        </div>

        <!-- Main Table -->
        <div class="glass rounded-[2.5rem] overflow-hidden border border-white/5 mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-white/5 border-b border-white/5 text-slate-500 uppercase text-[10px] font-black tracking-widest">
                        <tr>
                            <th class="px-8 py-6">Mã Giao Dịch</th>
                            <th class="px-8 py-6">Khách hàng</th>
                            <th class="px-8 py-6">Số tiền nạp</th>
                            <th class="px-8 py-6">Thời gian</th>
                            <th class="px-8 py-6">Trạng thái</th>
                            <th class="px-8 py-6 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (empty($pagedDeposits)): ?>
                        <tr>
                            <td colspan="6" class="px-8 py-20 text-center text-slate-500 italic">
                                <div class="flex flex-col items-center gap-3">
                                    <?php echo getIcon('wallet', 'w-12 h-12 opacity-20'); ?>
                                    <span class="font-medium">Không tìm thấy giao dịch nào</span>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($pagedDeposits as $d): ?>
                            <tr class="hover:bg-white/5 transition-colors group">
                                <td class="px-8 py-6">
                                    <span class="font-mono text-[11px] bg-slate-800 px-3 py-1 rounded-full text-slate-300">
                                        #<?php echo htmlspecialchars($d['id'] ?? ''); ?>
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-500 font-black text-xs">
                                            <?php echo strtoupper(substr($d['username'] ?? $d['user_id'] ?? 'U', 0, 1)); ?>
                                        </div>
                                        <span class="font-bold text-sm text-slate-200"><?php echo htmlspecialchars($d['username'] ?? $d['user_id'] ?? 'N/A'); ?></span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="text-green-400 font-black text-lg"><?php echo formatMoney($d['amount'] ?? 0); ?></span>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="text-slate-400 text-xs font-medium"><?php echo htmlspecialchars($d['created_at'] ?? '---'); ?></span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-<?php echo (($d['status'] ?? '') === 'completed' ? 'green' : (($d['status'] ?? '') === 'pending' ? 'yellow' : 'red')); ?>-500/10 text-<?php echo (($d['status'] ?? '') === 'completed' ? 'green' : (($d['status'] ?? '') === 'pending' ? 'yellow' : 'red')); ?>-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current <?php echo (($d['status'] ?? '') === 'pending' ? 'animate-pulse' : ''); ?>"></span>
                                        <span class="text-[10px] font-black uppercase tracking-tighter"><?php echo $d['status'] ?? 'unknown'; ?></span>
                                    </div>
                                </td>
                            <td class="px-8 py-6">
                                <div class="flex justify-center gap-2" id="actions-<?php echo htmlspecialchars($d['id'] ?? ''); ?>">
                                    <?php if (isset($d['status']) && $d['status'] === 'pending'): ?>
                                    <button onclick="approveDeposit('<?php echo htmlspecialchars($d['id'] ?? ''); ?>', 'approve')" class="px-4 py-2 btn-approve text-white rounded-xl hover:scale-105 transition-all text-[11px] font-black uppercase tracking-tighter shadow-lg shadow-green-500/20 approve-btn-<?php echo htmlspecialchars($d['id'] ?? ''); ?>">Duyệt</button>
                                    <button onclick="approveDeposit('<?php echo htmlspecialchars($d['id'] ?? ''); ?>', 'reject')" class="px-4 py-2 btn-reject text-white rounded-xl hover:scale-105 transition-all text-[11px] font-black uppercase tracking-tighter shadow-lg shadow-red-500/20">Hủy</button>
                                    <?php else: ?>
                                    <div class="text-[10px] font-black text-slate-600 uppercase tracking-widest flex items-center gap-1">
                                        <?php echo getIcon('check', 'w-3 h-3'); ?> <?php echo ($d['status'] ?? '') === 'completed' ? 'Hoàn tất' : 'Đã hủy'; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center items-center gap-2 mb-12">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?status=<?php echo $statusFilter; ?>&page=<?php echo $i; ?>" 
                   class="w-10 h-10 flex items-center justify-center rounded-xl font-bold text-xs transition-all <?php echo $i === $page ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/20' : 'glass text-slate-400 hover:bg-white/10'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
    const currentStatus = new URLSearchParams(window.location.search).get('status') || 'all';
    let lastDepositIds = new Set();

    async function approveDeposit(depositId, action) {
        const btn = document.querySelector(`.approve-btn-${depositId}`);
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.innerHTML = '<span class="loading-spinner">⟳</span>';

        try {
            const response = await fetch('/admin/api/approve-deposit.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: depositId, action: action })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const text = await response.text();
            const data = JSON.parse(text);
            
            if (data.success) {
                showNotification(data.message, 'success');
                // Remove the row after 500ms
                setTimeout(() => {
                    const row = document.querySelector(`#actions-${depositId}`).closest('tr');
                    if (row) {
                        row.style.opacity = '0';
                        row.style.transition = 'opacity 0.3s ease';
                        setTimeout(() => row.remove(), 300);
                    }
                    // Refresh deposits list
                    setTimeout(refreshDepositsList, 300);
                }, 500);
            } else {
                showNotification(data.error || 'Lỗi', 'error');
                btn.textContent = originalText;
                btn.disabled = false;
            }
        } catch (error) {
            showNotification('Lỗi: ' + error.message, 'error');
            btn.textContent = originalText;
            btn.disabled = false;
            console.error('Approve error:', error);
        }
    }

    function showNotification(message, type) {
        const notif = document.createElement('div');
        notif.className = `fixed top-4 right-4 p-4 rounded-2xl text-sm font-bold animate-bounce z-50 ${
            type === 'success' ? 'bg-green-500/20 border border-green-500/20 text-green-500' : 'bg-red-500/20 border border-red-500/20 text-red-500'
        }`;
        notif.textContent = message;
        document.body.appendChild(notif);
        setTimeout(() => notif.remove(), 3000);
    }

    async function refreshDepositsList() {
        try {
            const response = await fetch(`/admin/api/get-deposits.php?status=${currentStatus}`);
            if (!response.ok) return;
            
            const data = await response.json();
            if (!data.success) return;

            const tbody = document.querySelector('table tbody');
            if (!tbody) return;

            // Check for new deposits
            const currentIds = new Set(data.deposits.map(d => d.id));
            let hasNewDeposits = false;

            // Remove rows that are no longer in the list (completed/cancelled)
            document.querySelectorAll('tbody tr').forEach(row => {
                const cell = row.querySelector('td span.font-mono');
                if (cell) {
                    const rowId = cell.textContent.replace('#', '').trim();
                    if (!currentIds.has(rowId)) {
                        row.style.opacity = '0.3';
                    }
                }
            });

            // Check for new deposits and show notification
            data.deposits.forEach(deposit => {
                if (!lastDepositIds.has(deposit.id)) {
                    hasNewDeposits = true;
                    showNotification(`📥 Nạp tiền mới: ${deposit.username} - ${deposit.amount.toLocaleString('vi-VN')} VND`, 'success');
                }
            });

            lastDepositIds = currentIds;
        } catch (error) {
            console.error('Refresh error:', error);
        }
    }

    // Initialize lastDepositIds with current deposits
    document.querySelectorAll('table tbody tr td span.font-mono').forEach(cell => {
        const id = cell.textContent.replace('#', '').trim();
        if (id) lastDepositIds.add(id);
    });

    // Auto-refresh deposits list every 3 seconds (NO page reload)
    setInterval(refreshDepositsList, 3000);
    </script>
</body>
</html>