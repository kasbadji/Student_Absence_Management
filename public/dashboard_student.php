<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../public/login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="scripts/logout.js"></script>
</head>
<body>
    <h1>Welcome, Student!</h1>

    <button id="logoutBtn">Logout</button>

    <script>
        $("#logoutBtn").on("click", function(){
            $.ajax({
                url: "../api/logout.php",
                method: "POST",
                success: function(){
                    window.location.href = "../public/login.html";
                },
                error: function(){
                    alert("Logout failed. Please try again.");
                }
            });
        });
    </script>
</body>
</html>
