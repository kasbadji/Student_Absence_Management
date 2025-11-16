<?php
header("Content-Type: application/json");
$path = __DIR__ . '/../../config/db.php';
require_once $path;

$id   = $_POST["id_class"] ?? "";
$name = $_POST["class_name"] ?? "";
$level = $_POST["level"] ?? "";
$year = $_POST["academic_year"] ?? "";

if (!$id || !$name || !$level || !$year) {
    echo json_encode(["error" => "All fields are required"]);
    exit;
}

$stmt = $pdo->prepare("UPDATE class
                       SET class_name = ?, level = ?, academic_year = ?
                       WHERE id_class = ?");
$stmt->execute([$name, $level, $year, $id]);

echo json_encode(["success" => true]);
