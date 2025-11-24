<?php
require_once '../../config/db.php';
header('Content-Type: application/json');

if (!isset($_GET['id_teacher'])) {
    echo json_encode(['error' => 'id_teacher required']);
    exit;
}

$id_teacher = intval($_GET['id_teacher']);

$sql = "
    SELECT s.id_session, s.session_date, s.start_time, s.end_time,
           c.course_name, cl.class_name
    FROM session s
    JOIN course c ON s.id_course = c.id_course
    JOIN class cl ON c.id_class = cl.id_class
    WHERE s.id_teacher = ?
    ORDER BY s.session_date DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_teacher]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
