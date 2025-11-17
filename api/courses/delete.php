<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";

$id = $_POST["id_course"] ?? "";

if (!$id) {
    echo json_encode(["error" => "No course ID"]);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM course WHERE id_course=?");
$stmt->execute([$id]);

echo json_encode(["success" => true]);
