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
    <h2>Manage Students</h2>

<div>
    <h3>Add Student</h3>

    <input type="text" id="s_first" placeholder="First Name">
    <input type="text" id="s_last" placeholder="Last Name">
    <input type="text" id="s_matricule" placeholder="Matricule">
    <input type="date" id="s_dob">
    <input type="text" id="s_email" placeholder="Email">
    <input type="text" id="s_phone" placeholder="Phone">

    <select id="s_class"></select>

    <button id="createStudent">Add</button>
</div>

<h3>Existing Students</h3>

<table id="studentTable" border="1">
    <thead>
        <tr>
            <th>ID</th><th>First</th><th>Last</th><th>Matricule</th>
            <th>DOB</th><th>Email</th><th>Phone</th><th>Class</th><th>Actions</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script src="../scripts/admin/student.js"></script>

</body>
</html>
