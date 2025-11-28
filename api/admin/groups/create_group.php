<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

//! Only admin can create students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['name'] ?? '');

if (empty($name)) {
    echo json_encode([
        'success' => false,
        'message' => 'Group name is required'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare('
         INSERT INTO groups (name) VALUES (:name)
    ');
    $stmt->execute([':name' => $name]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

//! success response
echo json_encode([
    'success' => true,
    'message' => 'Group created successfully',
    'group_id' => $pdo->lastInsertId()
]);
exit;
?>

