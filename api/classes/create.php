<?php
header("Content-Type: application/json");
$path = __DIR__ . '/../../config/db.php';

if (!file_exists($path)) {
    echo json_encode([
        "error" => "DB file not found",
        "checked" => $path
    ]);
    exit;
}

require_once $path;

$class_name = $_POST["class_name"] ?? "";
$level = $_POST["level"] ?? "";
$academic_year = $_POST["academic_year"] ?? "";

if (!$class_name || !$level || !$academic_year) {
    echo json_encode(["error" => "All fields are required"]);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO class (class_name, level, academic_year) VALUES (?, ?, ?)");
$stmt->execute([$class_name, $level, $academic_year]);

echo json_encode(["success" => true]);
