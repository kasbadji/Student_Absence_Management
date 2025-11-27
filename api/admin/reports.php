<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

// Only admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

try {
    // Optional filters
    $date_from = $_GET['date_from'] ?? null;
    $date_to = $_GET['date_to'] ?? null;
    $group_id = isset($_GET['group_id']) && $_GET['group_id'] !== '' ? (int) $_GET['group_id'] : null;
    $student_id = isset($_GET['student_id']) && $_GET['student_id'] !== '' ? (int) $_GET['student_id'] : null;

    // Build base query. We try to join with sessions to get a session date if available.
    $sql = "
        SELECT
            a.attendance_id,
            a.session_id,
            a.student_id,
            a.status,
            COALESCE(s.session_date, NULL) AS session_date,
            st.matricule,
            u.full_name,
            g.name AS group_name
        FROM attendance a
        LEFT JOIN sessions s ON a.session_id = s.session_id
        LEFT JOIN students st ON a.student_id = st.student_id
        LEFT JOIN users u ON st.user_id = u.user_id
        LEFT JOIN groups g ON st.group_id = g.group_id
        WHERE 1=1
    ";

    $params = [];
    if ($date_from) {
        $sql .= " AND s.session_date >= :date_from ";
        $params['date_from'] = $date_from;
    }
    if ($date_to) {
        $sql .= " AND s.session_date <= :date_to ";
        $params['date_to'] = $date_to;
    }
    if ($group_id) {
        $sql .= " AND st.group_id = :group_id ";
        $params['group_id'] = $group_id;
    }
    if ($student_id) {
        $sql .= " AND a.student_id = :student_id ";
        $params['student_id'] = $student_id;
    }

    $sql .= " ORDER BY s.session_date DESC NULLS LAST, a.attendance_id DESC ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Compute simple stats
    $total = count($records);
    $present = 0;
    $absent = 0;
    foreach ($records as $r) {
        $st = strtolower($r['status'] ?? '');
        if ($st === 'present' || $st === 'p' || $st === '1')
            $present++;
        else
            $absent++;
    }

    echo json_encode([
        'success' => true,
        'stats' => ['total' => $total, 'present' => $present, 'absent' => $absent, 'rate' => $total ? round(($present / $total) * 100, 1) : 0],
        'records' => $records
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>

