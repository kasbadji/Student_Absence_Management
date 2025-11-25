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
$code = trim($data['code'] ?? '');

if (empty($title) || empty($code)) {
    echo json_encode([
        'success' => false,
        'message' => 'Title and code are required']);
    exit;
}

try {
    $stmt = $pdo->prepare('
        INSERT INTO modules (title, code) VALUES (:title, :code)
    ');
    $stmt->execute(['title' => $title, 'code' => $code]);

    echo json_encode([
        'success' => true,
        'message' => 'Module created successfully']);
}
catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
