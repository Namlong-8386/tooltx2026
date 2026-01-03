<?php
require_once '../../core/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
    echo json_encode(['status' => 'expired']);
    exit;
}

$users = readJSON('users');
$isValid = false;

foreach ($users as $user) {
    if ($user['id'] === $_SESSION['user_id']) {
        if ($user['session_token'] === $_SESSION['session_token']) {
            $isValid = true;
        }
        break;
    }
}

if (!$isValid) {
    session_destroy();
    echo json_encode(['status' => 'expired']);
} else {
    echo json_encode(['status' => 'active']);
}
