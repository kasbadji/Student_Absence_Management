<?php
header("Content-Type: application/json");
$path = __DIR__ . '/../../config/db.php';
require_once $path;

$id = $_POST["id_teacher"] ?? "";

if (!$id) {
    echo json_encode(["error" => "No teacher ID provided"]);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM teacher WHERE id_teacher=?");
$stmt->execute([$id]);

echo json_encode(["success" => true]);
