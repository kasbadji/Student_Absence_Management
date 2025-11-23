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
    <link rel="stylesheet" href="assets/admin.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>
<div class="sidebar">
    <h2>Admin Panel</h2>

    <a href="dashboard.php">Dashboard</a>
    <a href="student.php">Manage Students</a>
    <a href="teacher.php">Manage Teachers</a>
    <a href="class.php">Manage Classes</a>
    <a href="course.php">Manage Courses</a>
</div>

<!-- Main Content -->
<div class="content">
    <h1>Manage Classes</h1>

    <h3>Create New Class</h3>
    <form id="classForm">
        <input type="text" name="class_name" placeholder="Class Name" required><br><br>
        <input type="text" name="level" placeholder="Level" required><br><br>
        <input type="text" name="academic_year" placeholder="Academic Year" required><br><br>
        <button type="submit" class="btn">Create Class</button>
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
</div>
    <script src="../scripts/admin/class.js"></script>
</body>

</html>
