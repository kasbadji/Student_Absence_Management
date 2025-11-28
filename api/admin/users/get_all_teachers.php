<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../config/db.php';
if (!isset($pdo))
    die(json_encode(['success' => false, 'message' => 'PDO not connected']));
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

try {
    $query = "
        SELECT
            t.teacher_id,
            t.matricule,
            t.group_id,
            t.module_id,
            t.session_type AS session_type,
            u.user_id,
            u.full_name,
            u.email,
            u.created_at,
            g.name AS group_name,
            m.title AS title
        FROM teachers t
        JOIN users u ON t.user_id = u.user_id
        LEFT JOIN groups g ON t.group_id = g.group_id
        LEFT JOIN modules m ON t.module_id = m.module_id
        ORDER BY u.created_at DESC;
    ";
    $stmt = $pdo->query($query);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $map = ['C' => 'Cours', 'cours' => 'Cours', 'TD' => 'TD', 'TP' => 'TP', 'TD/TP' => 'TD/TP', 'all' => 'All', 'ALL' => 'All'];
    foreach ($teachers as $i => $t) {
        $st = $t['session_type'] ?? '';
        $parts = preg_split('/[,\/]/', $st);
        $labels = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p === '')
                continue;
            $labels[] = $map[$p] ?? $p;
        }
        $teachers[$i]['session_type_label'] = implode(' / ', $labels) ?: '-';
    }

    echo json_encode([
        "success" => true,
        "teachers" => $teachers
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

