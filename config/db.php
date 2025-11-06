<?php
$host = "localhost";
$db = "AbsenceManagement";
$user = "postgres";
$pass = "HaliM232023";

try {
    $conn = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}
?>
