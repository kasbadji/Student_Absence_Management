<?php
include "../config/db.php";

$stmt = $conn->query("SELECT student_id, first_name, last_name, message FROM students ORDER BY student_id ASC"); //! Get all students from DB
$students = $stmt->fetchAll(PDO::FETCH_ASSOC); //! fetch all data from DB

$attendance = []; //! Table to store presence and participation

$res = $conn->query("SELECT student_id, session, status FROM attendance"); //! Select the student id , session and the value of P and Pa (status)

foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $row) { //! loop to read the value of p and pa
    $attendance[$row["student_id"]][$row["session"]][] = $row["status"];
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance List</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>
<h2>Attendance List</h2>

<form id="attendanceForm">
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th rowspan="2">ID</th>
                <th rowspan="2">Last Name</th>
                <th rowspan="2">First Name</th>

                <!--loop to create S1,S2,S3 etc... (php s'exécute befor jquery) -->
                <?php for ($s = 1; $s <= 6; $s++): ?>
                    <th colspan="2">S<?= $s ?></th> <!--"S" + $s = S1,S2.. || = php echo-->
                <?php endfor; ?>

                <th rowspan="2">Absences</th>
                <th rowspan="2">Participation</th>
                <th rowspan="2">Message</th>
            </tr>

            <tr>
               <!-- display P and Pa cells-->
                <?php for ($s = 1; $s <= 6; $s++): ?>
                    <th>P</th>
                    <th>Pa</th>
                <?php endfor; ?>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($students as $st):
                //! select the student id
                $sid = $st["student_id"];

                //! calculate Absences and Participation
                $abs = 6;
                $par = 0;

                for ($s = 1; $s <= 6; $s++) {
                    $val = $attendance[$sid][$s] ?? [];

                    //! we select the checked box and stock it in the table val
                    if (is_array($val)) { //! is array function is to verify if $val is a table
                        foreach ($val as $status) {
                            if ($status === "Pa") $par++;
                            elseif ($status === "P") $abs--;
                        }
                    }
                    //! and here we calculat the p and pa that are checked once
                    else {
                        if ($val === "Pa") $par++;
                        elseif ($val === "P") $abs--;
                    }
                }
            ?>

           <!--store student id , only jquery can read it-->
            <tr data-sid="<?= htmlspecialchars($sid) ?>">

                <td class="sid"><?= htmlspecialchars($sid) ?></td> <!--display id-->
                <td><?= htmlspecialchars($st["last_name"]) ?></td> <!--last name-->
                <td><?= htmlspecialchars($st["first_name"]) ?></td> <!--first name-->

               <!--read and display all box with previous information (if it was chaked or not)-->
                <?php for ($s = 1; $s <= 6; $s++):
                    $val = $attendance[$sid][$s] ?? [];

                    $checkedP = (is_array($val) && in_array("P", $val)) ? 'checked' : '';

                    $checkedPa = (is_array($val) && in_array("Pa", $val)) ? 'checked' : '';
                ?>

                    <td>
                        <input type="checkbox"
                            class="att-checkbox"
                            data-session="<?= $s ?>"
                            data-status="P"
                            name="<?= $sid ?>[att][<?= $s ?>][]"
                            value="P"
                            <?= $checkedP ?>>
                    </td>

                    <td>
                        <input type="checkbox"
                            class="att-checkbox"
                            data-session="<?= $s ?>"
                            data-status="Pa"
                            name="<?= $sid ?>[att][<?= $s ?>][]"
                            value="Pa"
                            <?= $checkedPa ?>>
                    </td>

                <?php endfor; ?>

                <td class="absences"><?= $abs ?></td>
                <td class="participation"><?= $par ?></td>

                <td>
                    <span class="msg-text"><?= htmlspecialchars($st["message"] ?? '') ?></span>
                </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
    </table>
</form>

<div id="statusMsg" class="msg"></div>

<script>
$(function() {
    function recalcRow($tr) {
        var abs = 6;
        var par = 0;

        //! re calculate the abs and presence evry refresh not like php who do it once
        for (var s = 1; s <= 6; s++) {
            //! serach inside tr , for matching class att-checkbox where the status is P and it is checked
            var pChecked = $tr.find('input.att-checkbox[data-session="'+s+'"][data-status="P"]').is(':checked');

            //! serach inside tr , for matching class att-checkbox where the status is Pa and it is checked
            var paChecked = $tr.find('input.att-checkbox[data-session="'+s+'"][data-status="Pa"]').is(':checked');

            if (paChecked) par++;
            if (pChecked) abs--;
        }

        //! update numbers
        $tr.find('.absences').text(abs);
        $tr.find('.participation').text(par);

        //! apply color + auto-message
        var $msg = $tr.find('.msg-text');
        if (abs < 3) {
            $tr.css('background-color', 'lightgreen');
            $msg.text("Good attendance – Excellent participation");
        }
        else if (abs <= 4) {
            $tr.css('background-color', 'khaki');
            $msg.text("Warning – attendance low – You need to participate more");
        }
        else {
            $tr.css('background-color', 'lightcoral');
            $msg.text("Excluded – too many absences – You need to participate more");
        }
    }

    //! on change of any checkbox
    $(document).on('change', 'input.att-checkbox', function() {
        var $tr = $(this).closest('tr');
        recalcRow($tr);
    });

    //! initial recalculation for all rows
    $('tbody tr').each(function() {
        recalcRow($(this));
    });

    //! AJAX form submit
    $('#attendanceForm').on('submit', function(e) {
        e.preventDefault();
        $('#statusMsg').text('Saving...');

        $.ajax({
            url: 'attendance_update.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
                $('#statusMsg').text(resp);
            },
            error: function(xhr) {
                $('#statusMsg').text('Error: ' + (xhr.responseText || xhr.statusText));
            }
        });
    });
});

</script>
</body>
</html>
