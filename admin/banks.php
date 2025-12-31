<?php
require_once '../core/functions.php';
require_once '../core/auth.php';

requireAdmin();

// Ghi log hành động
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_bank'])) {
        logAdminAction('ADD_BANK', 'Thêm ngân hàng: ' . $_POST['bank_name']);
    } elseif (isset($_POST['delete_bank'])) {
        logAdminAction('DELETE_BANK', 'Xóa ngân hàng ID: ' . $_POST['bank_id']);
    }
}

$banks_list = [
    'MBBANK' => 'mbbank.png',
    'VietcomBank' => 'vcb.png',
    'TechComBank' => 'tcb.png',
    'ACB' => 'acb.png',
    'BIDV' => 'bidv.png',
    'VietinBank' => 'vietinbank.png',
    'Agribank' => 'agribank.png',
    'VPBank' => 'vpbank.png'
];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_bank'])) {
        $bank_name = $_POST['bank_name'];
        $account_no = trim($_POST['account_no']);
        $account_name = trim($_POST['account_name']);
        
        if (empty($account_no) || empty($account_name)) {
            $error = 'Vui lòng điền đầy đủ thông tin.';
        } else {
            $banks = readJSON('banks');
            $banks[] = [
                'id' => time(),
                'bank_name' => $bank_name,
                'logo' => $banks_list[$bank_name],
                'account_no' => $account_no,
                'account_name' => $account_name
            ];
            writeJSON('banks', $banks);
            $success = 'Thêm ngân hàng thành công.';
        }
    } elseif (isset($_POST['delete_bank'])) {
        $bank_id = $_POST['bank_id'];
        $banks = readJSON('banks');
        $banks = array_filter($banks, function($b) use ($bank_id) {
            return $b['id'] != $bank_id;
        });
        writeJSON('banks', array_values($banks));
        $success = 'Xóa ngân hàng thành công.';
    }
}

$currentBanks = readJSON('banks');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Ngân hàng - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/transitions.css">
    <style>
        body { background-color: #0f172a; color: white; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <nav class="p-4 glass flex justify-between items-center">
        <div class="flex items-center gap-2">
            <span class="text-xl font-bold text-red-500">QUẢN LÝ NGÂN HÀNG</span>
        </div>
        <a href="index.php" class="text-sm text-gray-400">Quay lại Admin</a>
    </nav>

    <main class="p-6 max-w-6xl mx-auto w-full">
        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-200 p-3 rounded mb-4 text-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-green-500/20 border border-green-500 text-green-200 p-3 rounded mb-4 text-sm">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form thêm ngân hàng -->
            <div class="glass p-6 rounded-xl h-fit">
                <h3 class="text-lg font-bold mb-4">Thêm Ngân Hàng Mới</h3>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Chọn Ngân hàng</label>
                        <select name="bank_name" class="w-full bg-white/10 border border-white/20 rounded px-4 py-2 focus:outline-none focus:border-yellow-500">
                            <?php foreach ($banks_list as $name => $logo): ?>
                                <option value="<?php echo $name; ?>" class="bg-[#0f172a]"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Số tài khoản</label>
                        <input type="text" name="account_no" required class="w-full bg-white/10 border border-white/20 rounded px-4 py-2 focus:outline-none focus:border-yellow-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Tên chủ tài khoản</label>
                        <input type="text" name="account_name" required class="w-full bg-white/10 border border-white/20 rounded px-4 py-2 focus:outline-none focus:border-yellow-500">
                    </div>
                    <button type="submit" name="add_bank" class="w-full bg-yellow-500 text-black font-bold py-2 rounded hover:bg-yellow-400 transition-colors">THÊM NGÂN HÀNG</button>
                </form>
            </div>

            <!-- Danh sách ngân hàng hiện có -->
            <div class="lg:col-span-2 glass p-6 rounded-xl">
                <h3 class="text-lg font-bold mb-4">Danh Sách Ngân Hàng Nhận Tiền</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if (empty($currentBanks)): ?>
                        <p class="col-span-full text-center text-gray-500 py-8">Chưa có ngân hàng nào được thêm.</p>
                    <?php else: ?>
                        <?php foreach ($currentBanks as $b): ?>
                            <div class="bg-white/5 p-4 rounded-lg border border-white/10 flex justify-between items-start">
                                <div class="flex gap-3">
                                    <img src="../assets/images/<?php echo $b['logo']; ?>" alt="logo" class="h-10 w-10 object-contain bg-white rounded p-1">
                                    <div>
                                        <div class="font-bold text-yellow-500"><?php echo $b['bank_name']; ?></div>
                                        <div class="text-sm"><?php echo $b['account_no']; ?></div>
                                        <div class="text-xs text-gray-400"><?php echo $b['account_name']; ?></div>
                                    </div>
                                </div>
                                <form method="POST" onsubmit="return confirm('Xóa ngân hàng này?')">
                                    <input type="hidden" name="bank_id" value="<?php echo $b['id']; ?>">
                                    <button type="submit" name="delete_bank" class="text-red-500 hover:text-red-400 text-sm">Xóa</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <script src="../assets/js/transitions.js"></script>
</body>
</html>
