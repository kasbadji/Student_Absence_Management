$(document).ready(function () {

    $.getJSON("/Student_Absence_Management/api/dashboard_counts.php", function (data) {

        $("#studentCount").text(data.students || 0);
        $("#teacherCount").text(data.teachers || 0);
        $("#classCount").text(data.classes || 0);
        $("#courseCount").text(data.courses || 0);

    });

});
