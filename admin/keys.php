<?php
require_once '../core/functions.php';
require_once '../core/auth.php';
require_once '../core/icons.php';

requireAdmin();

$keys = readJSON('keys');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Key - TOOLTX2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f172a; color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-black text-red-500">QUẢN LÝ KEY</h1>
            <a href="dashboard.php" class="px-4 py-2 glass rounded-lg hover:bg-white/10 transition-all">Quay lại</a>
        </div>
        
        <div class="glass rounded-2xl overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-white/5 text-slate-400 uppercase text-xs font-black">
                    <tr>
                        <th class="px-6 py-4">Key</th>
                        <th class="px-6 py-4">Loại</th>
                        <th class="px-6 py-4">Người mua</th>
                        <th class="px-6 py-4">Hết hạn</th>
                        <th class="px-6 py-4">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php foreach ($keys as $k): ?>
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4 font-mono font-bold text-blue-400"><?php echo htmlspecialchars($k['key_code'] ?? ''); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($k['type'] ?? ''); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($k['username'] ?? 'Chưa bán'); ?></td>
                        <td class="px-6 py-4 text-slate-400"><?php echo htmlspecialchars($k['expiry'] ?? ''); ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded bg-green-500/10 text-green-500 text-[10px] font-black uppercase">Active</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>