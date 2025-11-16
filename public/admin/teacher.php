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
    <h2>Manage Teachers</h2>

<div>
    <h3>Add Teacher</h3>
    <input type="text" id="t_first" placeholder="First name">
    <input type="text" id="t_last" placeholder="Last name">
    <input type="text" id="t_email" placeholder="Email (optional)">
    <button id="createTeacher">Add Teacher</button>
</div>

<h3>Existing Teachers</h3>
<table id="teacherTable" border="1">
    <thead>
        <tr>
            <th>ID</th><th>First</th><th>Last</th><th>Email</th><th>Actions</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script src="../scripts/admin/teacher.js"></script>

</body>
</html>
