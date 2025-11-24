<?php
require_once '../../config/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id_session']) || !isset($data['attendance'])) {
    echo json_encode(['error' => 'missing fields']);
    exit;
}

$id_session = intval($data['id_session']);
$attendance = $data['attendance'];

$sql_upsert = "
INSERT INTO absence (status, id_student, id_session)
VALUES (?, ?, ?)
ON CONFLICT (id_student, id_session) DO UPDATE SET status = EXCLUDED.status
";

foreach ($attendance as $row) {
    $stmt = $pdo->prepare($sql_upsert);
    $stmt->execute([
        $row['status'],
        $row['id_student'],
        $id_session
    ]);
}

echo json_encode(['success' => true]);
