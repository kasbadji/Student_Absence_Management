<?php
header("Content-Type: application/json");
$path = __DIR__ . '/../../config/db.php';
require_once $path;

$stmt = $pdo->query("SELECT * FROM teacher ORDER BY id_teacher DESC");
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($teachers);
