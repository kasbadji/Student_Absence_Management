<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

// Only admin can delete modules
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['module_id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing id']);
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM modules WHERE module_id = :id');
    $stmt->execute([':id' => $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Module deleted successfully'
    ]);
}
catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>
