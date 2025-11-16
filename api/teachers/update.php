<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";

$id = $_POST["id_teacher"] ?? "";
$first = $_POST["first_name"] ?? "";
$last = $_POST["last_name"] ?? "";
$email = $_POST["email"] ?? "";

if (!$id || !$first || !$last) {
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE teacher
        SET first_name=?, last_name=?, email=?
        WHERE id_teacher=?
    ");

    $stmt->execute([$first, $last, $email, $id]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
