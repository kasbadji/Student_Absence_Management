<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";

$sql = "
    SELECT s.*, c.class_name
    FROM student s
    LEFT JOIN class c ON s.id_class = c.id_class
    ORDER BY s.id_student DESC
";
$stmt = $pdo->query($sql);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
