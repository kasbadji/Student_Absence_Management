<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";

$sql = "
    SELECT c.*,
           cl.class_name,
           t.first_name AS teacher_first,
           t.last_name AS teacher_last
    FROM course c
    LEFT JOIN class cl ON c.id_class = cl.id_class
    LEFT JOIN teacher t ON c.id_teacher = t.id_teacher
    ORDER BY c.id_course DESC
";

$stmt = $pdo->query($sql);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
