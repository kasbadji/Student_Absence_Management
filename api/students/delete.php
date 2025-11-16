<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";

$id = $_POST["id_student"] ?? "";

if (!$id) {
    echo json_encode(["error" => "No student ID"]);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM student WHERE id_student=?");
$stmt->execute([$id]);

echo json_encode(["success" => true]);
