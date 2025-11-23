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
    <title>Manage Courses</title>
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
    <h2>Manage Courses</h2>

    <div>
        <h3>Add New Course</h3>

        <input type="text" id="c_name" placeholder="Course Name">
        <input type="text" id="c_code" placeholder="Course Code">

        <select id="c_class"></select>
        <select id="c_teacher"></select>

        <button id="createCourse" class="btn">Add Course</button>
    </div>

    <h3>Existing Courses</h3>

    <table id="courseTable" border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Code</th>
                <th>Class</th>
                <th>Teacher</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <script src="../scripts/admin/course.js"></script>
</div>
</body>

</html>
