<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT teacher_id FROM teachers WHERE user_id = :uid LIMIT 1");
$stmt->execute(['uid' => $user_id]);
$teacherRow = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$teacherRow || !isset($teacherRow['teacher_id'])) {
    echo json_encode(['success' => false, 'message' => 'Teacher record not found for this user']);
    exit;
}
$teacher_id = (int) $teacherRow['teacher_id'];

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid payload']);
    exit;
}

$module_id = isset($data['module_id']) ? intval($data['module_id']) : null;
$group_id = isset($data['group_id']) ? intval($data['group_id']) : null;
$students = isset($data['students']) && is_array($data['students']) ? $data['students'] : [];

if (!$module_id || !$group_id || count($students) === 0) {
    echo json_encode(['success' => false, 'message' => 'module_id, group_id and students are required']);
    exit;
}

try {
    $pdo->beginTransaction();

    $sql = "SELECT session_id FROM sessions WHERE module_id = :mid AND group_id = :gid AND teacher_id = :tid AND session_date = CURRENT_DATE LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['mid' => $module_id, 'gid' => $group_id, 'tid' => $teacher_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && isset($row['session_id'])) {
        $session_id = $row['session_id'];
    } else {
        $ins = "INSERT INTO sessions (module_id, group_id, teacher_id, session_date) VALUES (:mid, :gid, :tid, CURRENT_DATE) RETURNING session_id";
        $stmt = $pdo->prepare($ins);
        $stmt->execute(['mid' => $module_id, 'gid' => $group_id, 'tid' => $teacher_id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$res || !isset($res['session_id'])) {
            throw new Exception('Failed to create session');
        }
        $session_id = $res['session_id'];
    }

    $del = "DELETE FROM attendance WHERE session_id = :sid";
    $stmt = $pdo->prepare($del);
    $stmt->execute(['sid' => $session_id]);

    $ins = $pdo->prepare("INSERT INTO attendance (session_id, student_id, status) VALUES (:sid, :stid, :status)");
    foreach ($students as $s) {
        $stid = isset($s['student_id']) ? intval($s['student_id']) : null;
        $present = isset($s['present']) ? (bool) $s['present'] : false;
        $status = $present ? 'present' : 'absent';
        if ($stid) {
            $ins->execute(['sid' => $session_id, 'stid' => $stid, 'status' => $status]);
        }
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'session_id' => $session_id]);

} catch (PDOException $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>

