<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";

$id = $_POST["id_student"] ?? "";
$first = $_POST["first_name"] ?? "";
$last = $_POST["last_name"] ?? "";
$dob = $_POST["date_of_birth"] ?? "";
$email = $_POST["email"] ?? "";
$phone = $_POST["phone"] ?? "";
$matricule = $_POST["matricule"] ?? "";
$id_class = $_POST["id_class"] ?? "";

if (!$id) {
    echo json_encode(["error" => "Student ID missing"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE student
        SET first_name=?, last_name=?, date_of_birth=?, email=?, phone=?, matricule=?, id_class=?
        WHERE id_student=?
    ");

    $stmt->execute([$first, $last, $dob, $email, $phone, $matricule, $id_class, $id]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
