<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Teacher') {
    header('Location: ../login.html');
    exit;
}

if (!isset($_GET['id_session'])) {
    die("id_session required");
}

$id_session = intval($_GET['id_session']);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="assets/teacher.css">
    <script src="../scripts/jquery.min.js"></script>
    <script>
        const ID_SESSION = <?php echo $id_session; ?>;
    </script>
    <script src="../scripts/teacher/session.js"></script>
</head>

<body>

    <h1>Mark Attendance – Session <?php echo $id_session; ?></h1>

    <table id="studentsTable">
        <thead>
            <tr>
                <th>Student</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <button id="saveBtn">Save Attendance</button>

</body>

</html>
