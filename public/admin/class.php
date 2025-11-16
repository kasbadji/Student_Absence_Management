<?php
session_start();

//! only admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../public/login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Classes</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <h1>Manage Classes</h1>

    <h3>Create New Class</h3>
    <form id="classForm">
        <input type="text" name="class_name" placeholder="Class Name" required><br><br>
        <input type="text" name="level" placeholder="Level" required><br><br>
        <input type="text" name="academic_year" placeholder="Academic Year" required><br><br>
        <button type="submit">Create Class</button>
    </form>

    <div id="message"></div>
    <hr>

    <h3>Existing Classes</h3>
    <table border="1" id="classTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Level</th>
                <th>Academic Year</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <script src="../scripts/admin/class.js"></script>
</body>
</html>
