<?php
header("Content-Type: application/json");
$path = __DIR__ . '/../../config/db.php';
require_once $path;

$stmt = $pdo->query("SELECT * FROM class ORDER BY id_class DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rows);
