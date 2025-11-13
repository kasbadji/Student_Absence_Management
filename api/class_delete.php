<?php
header("Content-Type: application/json");
require_once("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST["id_class"] ?? 0);

    if ($id <= 0) {
        echo json_encode(["error" => "Invalid class ID"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM class WHERE id_class = :id");
        $stmt->execute([":id" => $id]);

        echo json_encode(["success" => "Class deleted successfully"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
