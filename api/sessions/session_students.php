<?php
require_once '../../config/db.php';
header('Content-Type: application/json');

if (!isset($_GET['id_session'])) {
    echo json_encode(['error' => 'id_session required']);
    exit;
}

$id_session = intval($_GET['id_session']);

$sql = "
SELECT st.id_student, st.first_name, st.last_name,
       a.id_absence, a.status AS absence_status
FROM student st
LEFT JOIN absence a
    ON a.id_session = ? AND a.id_student = st.id_student
WHERE st.id_class = (
    SELECT c.id_class
    FROM session s
    JOIN course c ON s.id_course = c.id_course
    WHERE s.id_session = ?
)
ORDER BY st.last_name, st.first_name
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_session, $id_session]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
