<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";

$id = $_POST["id_course"] ?? "";
$name = $_POST["course_name"] ?? "";
$code = $_POST["course_code"] ?? "";
$id_class = $_POST["id_class"] ?? "";
$id_teacher = $_POST["id_teacher"] ?? "";

if (!$id) {
    echo json_encode(["error" => "Course ID missing"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE course
        SET course_name=?, course_code=?, id_class=?, id_teacher=?
        WHERE id_course=?
    ");

    $stmt->execute([$name, $code, $id_class, $id_teacher, $id]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
