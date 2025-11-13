<?php
session_start();

if (isset($_SESSION["user_id"])) {
    echo json_encode(["logged_in" => true, "user_id" => $_SESSION["user_id"], "role" => $_SESSION["role"]]);
} else {
    echo json_encode(["logged_in" => false]);
}
?>
