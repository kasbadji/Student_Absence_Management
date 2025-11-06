<?php
include "../config/db.php";

// Récupération étudiants
$stmt = $conn->query("SELECT student_id, first_name, last_name, message FROM students ORDER BY student_id ASC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sauvegarde formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    foreach ($_POST as $sid => $data) {
        if (!is_numeric($sid)) continue;

        // Supprimer ancienne attendance
        $conn->prepare("DELETE FROM attendance WHERE student_id=?")->execute([$sid]);

        // Sauvegarder nouvelle attendance
        if (isset($data['att'])) {
            foreach ($data['att'] as $s => $status) {
                if (!empty($data['att'][$s])) {
                    foreach ($data['att'][$s] as $status) {
                        $conn->prepare("INSERT INTO attendance (student_id, session, status)
                                        VALUES (?, ?, ?)")
                            ->execute([$sid, $s, $status]);
                    }
                }
            }
        }

        // Message
        if (isset($data['msg'])) {
            $msg = trim($data['msg']);
            $conn->prepare("UPDATE students SET message=? WHERE student_id=?")
                 ->execute([$msg, $sid]);
        }
    }

    echo "<p style='color:green;'>Updated.</p>";
}

// Charger attendance existante
$attendance = [];
$res = $conn->query("SELECT student_id, session, status FROM attendance");
foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $attendance[$row["student_id"]][$row["session"]][] = $row["status"];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance List</title>
</head>
<body>

<h2>Attendance List</h2>

<form method="POST">
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th rowspan="2">ID</th>
            <th rowspan="2">Last Name</th>
            <th rowspan="2">First Name</th>

            <?php for ($s = 1; $s <= 6; $s++): ?>
                <th colspan="2">S<?= $s ?></th>
            <?php endfor; ?>

            <th rowspan="2">Absences</th>
            <th rowspan="2">Participation</th>
            <th rowspan="2">Message</th>
        </tr>

        <tr>
            <?php for ($s = 1; $s <= 6; $s++): ?>
                <th>P</th>
                <th>Pa</th>
            <?php endfor; ?>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($students as $st):
        $sid = $st["student_id"];
        $abs = 6;
        $par = 0;
    ?>
        <tr>
            <td><?= $sid ?></td>
            <td><?= $st["last_name"] ?></td>
            <td><?= $st["first_name"] ?></td>

            <?php for ($s = 1; $s <= 6; $s++):
                $val = $attendance[$sid][$s] ?? [];

                if (is_array($val)) {
                    foreach ($val as $status) {
                        if ($status === "Pa") {
                            $par++;
                        } elseif ($status === "P") {
                            $abs--;
                        }
                    }
                } else {
                    if ($val === "Pa") {
                        $par++;
                    } elseif ($val === "P") {
                        $abs--;
                    }
                }
            ?>
        <td><input type="checkbox"
                name="<?= $sid ?>[att][<?= $s ?>][]"
                value="P"
                <?= (is_array($val) && in_array("P", $val)) ? "checked" : "" ?>></td>

        <td><input type="checkbox"
                name="<?= $sid ?>[att][<?= $s ?>][]"
                value="Pa"
                <?= (is_array($val) && in_array("Pa", $val)) ? "checked" : "" ?>></td>


            <?php endfor; ?>

            <td><?= $abs ?></td>
            <td><?= $par ?></td>
            <td><input type="text" name="<?= $sid ?>[msg]" value="<?= htmlspecialchars($st["message"]) ?>"></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<br>
<button type="submit">Save</button>
</form>

</body>
</html>
