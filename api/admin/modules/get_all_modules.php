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
    $modules = $pdo->query('
        SELECT module_id, title, code FROM modules ORDER BY module_id DESC
   ')->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'modules' => $modules
    ]);
}
catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
