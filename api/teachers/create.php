<?php
header("Content-Type: application/json");
$path = __DIR__ . '/../../config/db.php';
require_once $path;

$first = $_POST["first_name"] ?? "";
$last = $_POST["last_name"] ?? "";
$email = $_POST["email"] ?? "";

if (!$first || !$last) {
    echo json_encode(["error" => "First and last name are required"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO teacher (first_name, last_name, email)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$first, $last, $email]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
