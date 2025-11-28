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

if (!$id) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing group ID'
    ]);
    exit;
}

try {
    //! Check for referencing rows before deleting to avoid FK violations
    $refs = [];

    //! students
    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM students WHERE group_id = :id');
    $stmt->execute(['id' => $id]);
    $cnt = (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    if ($cnt > 0)
        $refs['students'] = $cnt;

    //! teachers
    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM teachers WHERE group_id = :id');
    $stmt->execute(['id' => $id]);
    $cnt = (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    if ($cnt > 0)
        $refs['teachers'] = $cnt;

    //! sessions
    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM sessions WHERE group_id = :id');
    $stmt->execute(['id' => $id]);
    $cnt = (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    if ($cnt > 0)
        $refs['sessions'] = $cnt;

    if (!empty($refs)) {
        $parts = [];
        foreach ($refs as $table => $count) {
            $parts[] = "$count reference(s) in $table";
        }
        $message = 'Cannot delete group — it is referenced by: ' . implode('; ', $parts) . '. Reassign or remove those references first.';
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }

    //! Safe to delete
    $stmt = $pdo->prepare('DELETE FROM groups WHERE group_id = :id');
    $stmt->execute(['id' => $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Group deleted successfully.'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
