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
$id = $data['module_id'] ?? null;
$title = trim($data['title'] ?? '');
$code = trim($data['code'] ?? '');

if (!$id || !$title || !$code) {
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
         UPDATE modules SET title = :title, code = :code WHERE module_id = :id
    ");

    $stmt->execute([
        ':title' => $title,
        ':code' => $code,
        ':id' => $id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Module updated successfully']);
}
catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()]);
}
?>
