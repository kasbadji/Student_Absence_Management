<?php

header("Content-Type: application/json");
require_once("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $class_name = trim($_POST["class_name"] ?? "");
    $level = trim($_POST["level"] ?? "");
    $academic_year = trim($_POST["academic_year"] ?? "");

    if (empty($class_name)) {
        echo json_encode(["error" => "Class name is required"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO class (class_name, level, academic_year) VALUES (:name, :level, :year)");
        $stmt->execute([
            ":name" => $class_name,
            ":level" => $level,
            ":year" => $academic_year
        ]);

        echo json_encode(["success" => "Class added successfully"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
