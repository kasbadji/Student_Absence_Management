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

if (!$user_id) {
    echo json_encode([
        'success'=> false,
        'message'=> 'Missing user_id'
    ]);
    exit;
}

try {
    //! remove teachers or students
    $pdo ->prepare('
         DELETE FROM teachers WHERE user_id = :uid
    ') -> execute(array('uid'=> $user_id));

    $pdo ->prepare('
         DELETE FROM students WHERE user_id = :uid
    ') -> execute(array('uid'=> $user_id));

    //! remove user from users table
    $stmt = $pdo->prepare('
        DELETE FROM users WHERE user_id = :uid
    ') -> execute(array('uid'=> $user_id));

    echo json_encode([
        'success'=> true,
        'message'=> 'User deleted successfully'
    ]);
}
catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
