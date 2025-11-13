<?php
session_start();
header("Content-Type: application/json");
require_once __DIR__ ."/../config/db.php";

$username = $_POST["username"] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(["error" => "Username and password are required."]);
    exit;
}

//! find user
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user["password_hash"])) {
    echo json_encode(["error" => "Invalid username or password."]);
    exit;
}

if ($user && password_verify($password, $user['password_hash'])) {
    
    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['role'] = $user['role'];

    echo json_encode(["success" => "Login successful", "role" => $user['role']]);
} else {
    echo json_encode(["error" => "Invalid username or password"]);
}


//! User fills form → AJAX sends to login.php
//? → PHP fetches user → password_verify()
//? → If OK, PHP sets $_SESSION variables
//? → Returns JSON { success: "Login successful" }
