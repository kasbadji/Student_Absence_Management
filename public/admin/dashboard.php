<?php
session_start();
//! only admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/admin.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="/Student_Absence_Management/public/scripts/admin/dashboard.js"></script>
</head>

<body>

    <!-- Sidebar -->
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

        <h1>Dashboard Overview</h1>
        <hr><br>

        <div class="cards">

            <div class="card">
                <h2 id="studentCount">0</h2>
                <p>Students</p>
            </div>

            <div class="card">
                <h2 id="teacherCount">0</h2>
                <p>Teachers</p>
            </div>

            <div class="card">
                <h2 id="classCount">0</h2>
                <p>Classes</p>
            </div>

            <div class="card">
                <h2 id="courseCount">0</h2>
                <p>Courses</p>
            </div>

        </div>
    </div>

</body>

</html>
