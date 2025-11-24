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
    <title>Attendance Summary</title>
    <link rel="stylesheet" href="assets/teacher.css">
    <script src="../scripts/jquery.min.js"></script>
    <script>
        const ID_TEACHER = <?php echo intval($id_teacher); ?>;
    </script>
    <script src="../scripts/teacher/summary.js"></script>
</head>

<body>

    <h1>Attendance Summary</h1>

    <table id="summaryTable">
        <thead>
            <tr>
                <th>Course</th>
                <th>Class</th>
                <th>Total Records</th>
                <th>Absents</th>
                <th>Absence Rate (%)</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

</body>

</html>
