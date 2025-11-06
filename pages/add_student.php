<?php
include "../config/db.php";

$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $studentid = trim($_POST["student_id"]);
    $last_name = trim($_POST["last_name"]);
    $first_name = trim($_POST["first_name"]);
    $email = trim($_POST["email"]);

    if ($studentid === "" || $last_name === "" || $first_name === "" || $email === "") {
        $error = "All fields are required";
    } else {
        try {
            $sql = "INSERT INTO students (student_id, last_name, first_name, email) VALUES (:student_id, :last_name, :first_name, :email)";

            $stmt = $conn->prepare($sql);

            $stmt->execute([
                ':student_id' => $studentid,
                ':last_name' => $last_name,
                ':first_name' => $first_name,
                ':email' => $email
            ]);
            $success = "Student added successfully";

        } catch (PDOException $e) {
            $error = "DB error: " . $e->getMessage();
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <title>Add Student</title>
</head>

<body>
    <h2>Add Student</h2>

    <?php if ($error !== "")
        echo "<p style='color:red'>$error</p>"; ?>
    <?php if ($success !== "")
        echo "<p style='color:green'>$success</p>"; ?>

    <form method="POST" id="studentForm">
        <label>Student ID</label>
        <input type="text" name="student_id" required> <br>

        <label>Last Name</label>
        <input type="text" name="last_name" required> <br>

        <label>First Name</label>
        <input type="text" name="first_name" required><br>

        <label>Email</label>
        <input type="email" name="email" required><br>

        <button type="submit">Submit</button>
    </form>

    <script>
        $("#studentForm").on("submit", function (e) {
            let valid = true;
            $(this).find("input").each(function () {
                if ($(this).val().trim() === "") {
                    valid = false;
                    alert("All fields must be filled");
                    return false:
                }
            });
            if (!valid) e.preventDefault();
            //! stop the form from submitting
        });
    </script>
</body>

</html>
