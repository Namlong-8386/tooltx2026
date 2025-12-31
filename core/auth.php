<?php
/**
 * Authentication Module
 * Xử lý xác thực người dùng và admin riêng biệt
 */

// Lấy thông tin người dùng hiện tại
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $users = readJSON('users');
    foreach ($users as $user) {
        if ($user['id'] === $_SESSION['user_id']) {
            return $user;
        }
    }
    return null;
}

// Lấy thông tin admin hiện tại
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    $admins = readJSON('admins');
    foreach ($admins as $admin) {
        if ($admin['id'] === $_SESSION['admin_id']) {
            return $admin;
        }
    }
    return null;
}

// Đăng nhập người dùng
function loginUser($username, $password) {
    $users = readJSON('users');
    
    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            // Xóa session admin nếu có
            if (isset($_SESSION['admin_id'])) {
                unset($_SESSION['admin_id']);
                unset($_SESSION['admin_username']);
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = 'user';
            
            return true;
        }
    }
    
    return false;
}

// Đăng nhập admin
function loginAdmin($username, $password) {
    $admins = readJSON('admins');
    
    foreach ($admins as $admin) {
        if ($admin['username'] === $username && password_verify($password, $admin['password'])) {
            // Xóa session người dùng nếu có
            if (isset($_SESSION['user_id'])) {
                unset($_SESSION['user_id']);
                unset($_SESSION['username']);
            }
            
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = 'admin';
            
            // Ghi log đăng nhập admin
            logAdminAction('LOGIN', 'Admin đã đăng nhập');
            
            return true;
        }
    }
    
    return false;
}

// Đăng xuất
function logout() {
    session_destroy();
    // session_start(); // Remove or keep session_start? session_start() is usually called at top of core/functions.php
    return true;
}

// Ghi log hành động của admin
function logAdminAction($action, $details = '') {
    $logFile = DATA_PATH . 'admin_logs.json';
    $logs = [];
    
    if (file_exists($logFile)) {
        $logs = json_decode(file_get_contents($logFile), true) ?: [];
    }
    
    $logs[] = [
        'timestamp' => date('Y-m-d H:i:s'),
        'admin_id' => $_SESSION['admin_id'] ?? 'unknown',
        'admin_username' => $_SESSION['admin_username'] ?? 'unknown',
        'action' => $action,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Kiểm tra quyền truy cập admin
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ../admin/login.php');
        exit;
    }
}

// Kiểm tra quyền truy cập người dùng
function requireUser() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
}

// Xác thực CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate password strength
function isStrongPassword($password) {
    // Ít nhất 8 ký tự, có chữ hoa, chữ thường, số
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
}
?>
