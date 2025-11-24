<?php
require_once '../../config/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data ||
    !isset($data['session_date']) ||
    !isset($data['start_time']) ||
    !isset($data['end_time']) ||
    !isset($data['id_course']) ||
    !isset($data['id_teacher'])) {

    echo json_encode(['error' => 'missing fields']);
    exit;
}

$sql = "
INSERT INTO session (session_date, start_time, end_time, id_course, id_teacher)
VALUES (?, ?, ?, ?, ?)
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $data['session_date'],
    $data['start_time'],
    $data['end_time'],
    $data['id_course'],
    $data['id_teacher']
]);

echo json_encode(['success' => true, 'id_session' => $pdo->lastInsertId()]);
