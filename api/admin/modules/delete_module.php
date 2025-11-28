<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

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
    $refs = [];

    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM teachers WHERE module_id = :id');
    $stmt->execute([':id' => $id]);
    $cnt = (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    if ($cnt > 0) {
        $refs['teachers'] = $cnt;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM sessions WHERE module_id = :id');
    $stmt->execute([':id' => $id]);
    $cnt = (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    if ($cnt > 0) {
        $refs['sessions'] = $cnt;
    }

    if (!empty($refs)) {
        $parts = [];
        foreach ($refs as $table => $count) {
            $parts[] = "$count reference(s) in $table";
        }
        $message = 'Cannot delete module — it is referenced by: ' . implode('; ', $parts) . '. Reassign or remove those references first.';
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM modules WHERE module_id = :id');
    $stmt->execute([':id' => $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Module deleted successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>

