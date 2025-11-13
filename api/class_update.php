<?php
header("Content-Type: application/json");
require_once("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST["id_class"] ?? 0);
    $class_name = trim($_POST["class_name"] ?? "");
    $level = trim($_POST["level"] ?? "");
    $academic_year = trim($_POST["academic_year"] ?? "");

    if ($id <= 0 || empty($class_name)) {
        echo json_encode(["error" => "Invalid data"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE class SET class_name = :name, level = :level, academic_year = :year WHERE id_class = :id");
        $stmt->execute([
            ":name" => $class_name,
            ":level" => $level,
            ":year" => $academic_year,
            ":id" => $id
        ]);

        echo json_encode(["success" => "Class updated successfully"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
