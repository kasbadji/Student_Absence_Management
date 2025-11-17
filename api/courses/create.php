<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";

$name = $_POST["course_name"] ?? "";
$code = $_POST["course_code"] ?? "";
$id_class = $_POST["id_class"] ?? "";
$id_teacher = $_POST["id_teacher"] ?? "";

if (!$name || !$code || !$id_class || !$id_teacher) {
    echo json_encode(["error" => "All fields are required"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO course (course_name, course_code, id_class, id_teacher)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([$name, $code, $id_class, $id_teacher]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
