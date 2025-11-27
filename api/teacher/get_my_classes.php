<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT t.module_id, m.title AS module_title, m.code AS module_code,
                   t.group_id, g.name AS group_name,
                   (SELECT COUNT(*) FROM students s WHERE s.group_id = t.group_id) AS student_count
            FROM teachers t
            LEFT JOIN modules m ON m.module_id = t.module_id
            LEFT JOIN groups g ON g.group_id = t.group_id
            WHERE t.user_id = :uid
            ORDER BY m.title NULLS LAST, g.name NULLS LAST";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $classes = [];
    foreach ($rows as $r) {
        $classes[] = [
            'module_id' => $r['module_id'],
            'module_title' => $r['module_title'],
            'module_code' => $r['module_code'],
            'group_id' => $r['group_id'],
            'group_name' => $r['group_name'],
            'student_count' => (int) ($r['student_count'] ?? 0)
        ];
    }

    echo json_encode(['success' => true, 'classes' => $classes, 'teacher_name' => $_SESSION['full_name'] ?? null]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>

