<?php
header('Content-Type: application/json');
session_set_cookie_params(0, '/');
session_start();

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'logged_in' => true,
        'user_id' => $_SESSION['user_id'],
        'role' => $_SESSION['role'],
        'full_name' => $_SESSION['full_name']
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}
?>

