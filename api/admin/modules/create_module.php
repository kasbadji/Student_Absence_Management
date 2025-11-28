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
$title = trim($data['title'] ?? '');
$has_td = !empty($data['has_td']) ? 1 : 0;
$has_tp = !empty($data['has_tp']) ? 1 : 0;

if (empty($title)) {
    echo json_encode([
        'success' => false,
        'message' => 'Title is required'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare('
            INSERT INTO modules (title, has_td, has_tp) VALUES (:title, :has_td, :has_tp)
        ');
    $stmt->execute(['title' => $title, 'has_td' => $has_td, 'has_tp' => $has_tp]);

    echo json_encode([
        'success' => true,
        'message' => 'Module created successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

