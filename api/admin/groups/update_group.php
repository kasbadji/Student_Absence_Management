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
$id = $data['group_id'] ?? null;
$name = trim($data['name'] ?? '');

if (!$id || empty($name)) {
    echo json_encode([
        'success'=> false,
        'message'=> 'Group ID and name are required'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE groups SET name = :name WHERE group_id = :id
    ");

    $stmt->execute(['name' => $name, 'id' => $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Group updated successfully.']);
}
catch (PDOException $e) {
    echo json_encode([
        'success'=> false,
        'message'=> $e->getMessage()
    ]);
}
