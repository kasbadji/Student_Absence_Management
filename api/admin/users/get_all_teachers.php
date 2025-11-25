<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

try {
    $query = "
        SELECT
            t.teacher_id,
            t.matricule,
            u.user_id,
            u.full_name,
            u.email,
            u.created_at
        FROM teachers t
        JOIN users u ON t.user_id = u.user_id
        ORDER BY u.created_at DESC;
    ";
    $stmt = $pdo->query($query);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success"=> true,
        "teachers"=> $teachers
    ]);
}
catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
