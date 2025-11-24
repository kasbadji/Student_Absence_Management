<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Teacher') {
    header('Location: ../login.html');
    exit;
}

$id_teacher = $_SESSION['id_teacher'];
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="assets/teacher.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        const ID_TEACHER = <?php echo intval($id_teacher); ?>;
    </script>
    <script src="../scripts/teacher/dashboard.js"></script>
</head>

<body>

    <h1>Teacher Dashboard</h1>

    <table id="sessionsTable">
        <thead>
            <tr>
                <th>Course</th>
                <th>Class</th>
                <th>Date</th>
                <th>Time</th>
                <th>Open</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

</body>

</html>
