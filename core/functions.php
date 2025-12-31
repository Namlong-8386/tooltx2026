<?php
session_start();

define('DATA_PATH', dirname(__DIR__) . '/data/');
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/auth.php';

function readJSON($filename) {
    $path = DATA_PATH . $filename . '.json';
    if (!file_exists($path)) {
        return [];
    }
    $content = file_get_contents($path);
    return json_decode($content, true) ?: [];
}

function writeJSON($filename, $data) {
    $path = DATA_PATH . $filename . '.json';
    $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($path, $content);
}

function generateID($prefix = 'USER') {
    return $prefix . rand(100000, 999999);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin';
}

function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . ' VND';
}

function generateRandomString($length = 6) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>
