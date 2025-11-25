<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? null;
$full_name = trim($data['full_name'] ?? null);
$email = trim($data['email'] ?? null);
$password = trim($data['password'] ?? null);

if (!$user_id || empty($full_name) || empty($email)) {
    echo json_encode([
        'success'=> false,
        'message'=> 'Missing fields'
]);
    exit;
}

try {
    $sql = 'UPDATE users SET full_name = :full_name, email = :email';
    $params = [
        'full_name' => $full_name,
        'email' => $email,
        'user_id' => $user_id
    ];
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $sql .= ", password_hash = :password_hash";
        $params['password_hash'] = $password_hash;
    }

    $sql .= " WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        'success'=> true,
        'message'=> 'User updated successfully'
    ]);
}
catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
