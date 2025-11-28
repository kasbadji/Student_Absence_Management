<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$group_id = isset($_GET['group_id']) ? $_GET['group_id'] : null;
$module_id = isset($_GET['module_id']) ? $_GET['module_id'] : null;
if (!$group_id) {
    echo json_encode(['success' => false, 'message' => 'group_id is required']);
    exit;
}

try {
    $teacher_id = null;
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT teacher_id FROM teachers WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $_SESSION['user_id']]);
        $tr = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($tr && isset($tr['teacher_id']))
            $teacher_id = (int) $tr['teacher_id'];
    }

    $session_id = null;
    if ($module_id && $teacher_id) {
        $sstmt = $pdo->prepare("SELECT session_id FROM sessions WHERE module_id = :mid AND group_id = :gid AND teacher_id = :tid AND session_date = CURRENT_DATE LIMIT 1");
        $sstmt->execute(['mid' => $module_id, 'gid' => $group_id, 'tid' => $teacher_id]);
        $sr = $sstmt->fetch(PDO::FETCH_ASSOC);
        if ($sr && isset($sr['session_id']))
            $session_id = $sr['session_id'];
    }

    if ($session_id) {
        $sql = "SELECT s.student_id, u.user_id, u.full_name, u.email, a.status
                FROM students s
                JOIN users u ON s.user_id = u.user_id
                LEFT JOIN attendance a ON a.student_id = s.student_id AND a.session_id = :sid
                WHERE s.group_id = :gid
                ORDER BY u.full_name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['gid' => $group_id, 'sid' => $session_id]);
    } else {
        $sql = "SELECT s.student_id, u.user_id, u.full_name, u.email, NULL AS status
                FROM students s
                JOIN users u ON s.user_id = u.user_id
                WHERE s.group_id = :gid
                ORDER BY u.full_name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['gid' => $group_id]);
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'students' => $rows]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>

