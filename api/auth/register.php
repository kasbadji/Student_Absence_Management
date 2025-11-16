<?php
header("Content-Type: application/json"); //! sending to browser or ajax response as json data
$path = __DIR__ . '/../../config/db.php';

if (!file_exists($path)) {
    echo json_encode([
        "error" => "DB file not found",
        "checked" => $path
    ]);
    exit;
}

require_once $path;

$username = $_POST["username"] ?? '';
$password = $_POST['password'] ??'';
$role = $_POST['role'] ??'';

//! if empty fields send error response
if (empty($username) || empty($password) || empty($role)) {
    echo json_encode(["error" => "All fields are required."]);
    exit;
}

//! check if username already exists
$stmt = $pdo->prepare("SELECT id_user FROM users WHERE username = :username");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    echo json_encode(["error" => "Username already exists."]);
    exit;
}

//! hash the password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, :role)");

    $stmt->execute([$username, $passwordHash, $role]);

    echo json_encode(["success" => "User registered successfully."]);
}
catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}


//! User fills form → AJAX sends to register.php
//? → PHP validates & hashes password
//? → INSERT INTO users
//? → Returns JSON { success: "User registered" }
