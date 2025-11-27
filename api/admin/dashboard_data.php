<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

//! Allow only admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

try {
    //! statistics Table
    $stats = [];

    //! count students
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM students");
    $stats['students'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    //! count teachers
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM teachers");
    $stats['teachers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    //! count modules
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM modules");
    $stats['modules'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    //! count groups
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM groups");
    $stats['groups'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    //! count sessions
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM sessions");
    $stats['sessions'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'admin_name' => $_SESSION['full_name']
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

