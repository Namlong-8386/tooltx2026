<?php
require_once '../../core/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện thao tác này.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
    exit;
}

$keyCode = trim($_POST['key_code'] ?? '');
$account = $_SESSION['username'];

if (empty($keyCode)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã key.']);
    exit;
}

$keys = readJSON('keys');
$keyIndex = -1;

foreach ($keys as $index => $k) {
    if ($k['key_code'] === $keyCode) {
        $keyIndex = $index;
        break;
    }
}

if ($keyIndex === -1) {
    echo json_encode(['success' => false, 'message' => 'Mã key không tồn tại.']);
    exit;
}

if ($keys[$keyIndex]['user_id'] !== $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Key này không thuộc về bạn.']);
    exit;
}

if (!empty($keys[$keyIndex]['linked_account'])) {
    echo json_encode(['success' => false, 'message' => 'Mã key này đã được liên kết với tài khoản: ' . $keys[$keyIndex]['linked_account']]);
    exit;
}

// Update the key with the linked account
$keys[$keyIndex]['linked_account'] = $account;
$keys[$keyIndex]['linked_at'] = date('Y-m-d H:i:s');

if (writeJSON('keys', $keys)) {
    echo json_encode(['success' => true, 'message' => 'Liên kết key thành công!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại sau.']);
}
