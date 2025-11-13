<?php
header("Content-Type: application/json");
require_once("../config/db.php");

try {
    $stmt = $pdo->query("SELECT * FROM class ORDER BY id_class DESC");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($classes);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>
