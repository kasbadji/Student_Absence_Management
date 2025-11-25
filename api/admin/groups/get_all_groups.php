<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

//! Only admin can create students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->query('
        SELECT group_id, name FROM groups ORDER BY group_id DESC
    ');
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'=> true,
        'groups'=> $groups
    ]);
}
catch (PDOException $e) {
    echo json_encode([
        'success'=> false,
        'message'=> $e->getMessage()
    ]);
}
?>
