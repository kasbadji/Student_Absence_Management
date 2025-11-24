<?php
require_once '../../config/db.php';
header('Content-Type: application/json');

if (!isset($_GET['id_teacher'])) {
    echo json_encode(['error' => 'id_teacher required']);
    exit;
}

$id_teacher = intval($_GET['id_teacher']);

$sql = "
SELECT c.course_name, cl.class_name,
       COUNT(a.id_absence) AS total_records,
       SUM(CASE WHEN a.status='absent' THEN 1 ELSE 0 END) AS absents
FROM session s
JOIN course c ON s.id_course = c.id_course
JOIN class cl ON c.id_class = cl.id_class
LEFT JOIN absence a ON a.id_session = s.id_session
WHERE s.id_teacher = ?
GROUP BY c.course_name, cl.class_name
ORDER BY c.course_name, cl.class_name
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_teacher]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
