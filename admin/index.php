<?php
require_once '../core/functions.php';
require_once '../core/auth.php';

// Nếu admin đã đăng nhập, chuyển hướng đến dashboard
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}
?>