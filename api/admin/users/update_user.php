<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Accept JSON payload and allow optional fields (email, password).
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? null;
$full_name = isset($data['full_name']) ? trim($data['full_name']) : null;
$email = isset($data['email']) ? trim($data['email']) : null;
$password = isset($data['password']) ? trim($data['password']) : null;

if (!$user_id || empty($full_name)) {
    echo json_encode([
        'success'=> false,
        'message'=> 'user_id and full_name are required'
    ]);
    exit;
}

try {
    // Build dynamic SQL so we only update fields provided by the client.
    $setParts = ['full_name = :full_name'];
    $params = [':full_name' => $full_name, ':user_id' => $user_id];

    if ($email !== null && $email !== '') {
        $setParts[] = 'email = :email';
        $params[':email'] = $email;
    }

    if ($password !== null && $password !== '') {
        $setParts[] = 'password_hash = :password_hash';
        $params[':password_hash'] = password_hash($password, PASSWORD_BCRYPT);
    }

    $sql = 'UPDATE users SET ' . implode(', ', $setParts) . ' WHERE user_id = :user_id';
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
