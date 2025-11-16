<?php
header("Content-Type: application/json");
$path = __DIR__ . '/../../config/db.php';
require_once $path;

$id = $_POST["id_class"] ?? "";

if (!$id) {
    echo json_encode(["error" => "Missing class ID"]);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM class WHERE id_class = ?");
$stmt->execute([$id]);

echo json_encode(["success" => true]);
