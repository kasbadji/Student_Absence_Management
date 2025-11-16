<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";

$first = $_POST["first_name"] ?? "";
$last = $_POST["last_name"] ?? "";
$dob = $_POST["date_of_birth"] ?? "";
$email = $_POST["email"] ?? "";
$phone = $_POST["phone"] ?? "";
$matricule = $_POST["matricule"] ?? "";
$id_class = $_POST["id_class"] ?? "";

if (!$first || !$last || !$matricule || !$id_class) {
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO student (first_name, last_name, date_of_birth, email, phone, matricule, id_class)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([$first, $last, $dob, $email, $phone, $matricule, $id_class]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
