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
    <title>Manage Students</title>
    <link rel="stylesheet" href="assets/admin.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>



    <div class="sidebar">
        <h2>Admin Panel</h2>

        <a href="dashboard.php">📊 Dashboard</a>
        <a href="student.php">👨‍🎓 Manage Students</a>
        <a href="teacher.php">👨‍🏫 Manage Teachers</a>
        <a href="class.php">🏫 Manage Classes</a>
        <a href="course.php">📚 Manage Courses</a>
    </div>

    <!-- Main Content -->
    <div class="content">
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

            <button id="createStudent" class="btn">Add Student</button>
        </div>

        <h3>Existing Students</h3>

        <table id="studentTable" border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First</th>
                    <th>Last</th>
                    <th>Matricule</th>
                    <th>DOB</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Class</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <script src="../scripts/admin/student.js"></script>

</body>

</html>
