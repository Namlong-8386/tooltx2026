<?php
require_once '../core/functions.php';
require_once '../core/auth.php';
require_once '../core/icons.php';

requireAdmin();

$users = readJSON('users');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Người dùng - TOOLTX2026</title>
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
            <h1 class="text-3xl font-black text-red-500">QUẢN LÝ NGƯỜI DÙNG</h1>
            <a href="dashboard.php" class="px-4 py-2 glass rounded-lg hover:bg-white/10 transition-all">Quay lại</a>
        </div>
        
        <div class="glass rounded-2xl overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-white/5 text-slate-400 uppercase text-xs font-black">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Username</th>
                        <th class="px-6 py-4">Số dư</th>
                        <th class="px-6 py-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4 font-mono text-slate-500"><?php echo htmlspecialchars($u['id'] ?? ''); ?></td>
                        <td class="px-6 py-4 font-bold"><?php echo htmlspecialchars($u['username']); ?></td>
                        <td class="px-6 py-4 text-orange-400 font-bold"><?php echo formatMoney($u['balance'] ?? 0); ?></td>
                        <td class="px-6 py-4">
                            <button class="text-blue-400 hover:text-blue-300 mr-3">Sửa</button>
                            <button class="text-red-400 hover:text-red-300">Khóa</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>