<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

// allow only teacher access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

try {
    $stats = ['modules' => 0, 'groups' => 0, 'students' => 0, 'sessions' => 0];

    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT module_id) AS count FROM teachers WHERE user_id = :uid AND module_id IS NOT NULL");
    $stmt->execute(['uid' => $user_id]);
    $stats['modules'] = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT group_id) AS count FROM teachers WHERE user_id = :uid AND group_id IS NOT NULL");
    $stmt->execute(['uid' => $user_id]);
    $stats['groups'] = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM students WHERE group_id IN (
        SELECT DISTINCT group_id FROM teachers WHERE user_id = :uid AND group_id IS NOT NULL
    )");
    $stmt->execute(['uid' => $user_id]);
    $stats['students'] = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

    $stmt = $pdo->prepare("SELECT teacher_id FROM teachers WHERE user_id = :uid LIMIT 1");
    $stmt->execute(['uid' => $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && isset($row['teacher_id'])) {
        $teacher_id = (int) $row['teacher_id'];
        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM sessions WHERE teacher_id = :tid");
        $stmt->execute(['tid' => $teacher_id]);
        $stats['sessions'] = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM sessions WHERE module_id IN (
            SELECT DISTINCT module_id FROM teachers WHERE user_id = :uid AND module_id IS NOT NULL
        )");
        $stmt->execute(['uid' => $user_id]);
        $stats['sessions'] = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
    }

    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'teacher_name' => $_SESSION['full_name'] ?? null
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>

