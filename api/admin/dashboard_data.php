<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

try {
    $stats = [];

    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM students");
    $stats['students'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM teachers");
    $stats['teachers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM modules");
    $stats['modules'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM groups");
    $stats['groups'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM sessions");
    $stats['sessions'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $present = 0;
    $total = 0;
    $stmt = $pdo->query("SELECT status FROM attendance");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $total++;
        $raw = strtolower(trim((string) ($row['status'] ?? '')));
        if (in_array($raw, ['present', 'p', '1', 'true'], true)) {
            $present++;
        }
    }
    $absent = max($total - $present, 0);
    $rate = $total ? round(($present / $total) * 100, 1) : 0;
    $stats['attendance'] = [
        'total' => $total,
        'present' => $present,
        'absent' => $absent,
        'rate' => $rate
    ];

    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'admin_name' => $_SESSION['full_name']
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

