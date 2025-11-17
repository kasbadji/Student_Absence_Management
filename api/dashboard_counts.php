<?php
require_once __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

$response = [
    "students" => 0,
    "teachers" => 0,
    "classes" => 0,
    "courses" => 0
];

$stmt = $pdo->query("SELECT COUNT(*) FROM student");
$response["students"] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM teacher");
$response["teachers"] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM class");
$response["classes"] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM course");
$response["courses"] = $stmt->fetchColumn();

echo json_encode($response);
