<?php
require_once 'core/functions.php';

// Clear session token in database if logged in
if (isset($_SESSION['user_id'])) {
    $users = readJSON('users');
    foreach ($users as &$u) {
        if ($u['id'] === $_SESSION['user_id']) {
            $u['session_token'] = '';
            break;
        }
    }
    writeJSON('users', $users);
}

session_destroy();
header('Location: login.php?logout=success');
exit;
