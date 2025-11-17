$(document).ready(function () {

    loadCourses();
    loadClassOptions();
    loadTeacherOptions();

    function loadCourses() {
        $.getJSON("/Student_Absence_Management/api/courses/list.php", function (data) {
            let rows = "";
            data.forEach(c => {
                rows += `
                    <tr>
                        <td>${c.id_course}</td>
                        <td>${c.course_name}</td>
                        <td>${c.course_code}</td>
                        <td>${c.class_name || "—"}</td>
                        <td>${c.teacher_first} ${c.teacher_last}</td>

                        <td>
                            <button class="edit" data-id="${c.id_course}">Edit</button>
                            <button class="delete" data-id="${c.id_course}">Delete</button>
                        </td>
                    </tr>
                `;
            });
            $("#courseTable tbody").html(rows);
        });
    }

    function loadClassOptions() {
        $.getJSON("/Student_Absence_Management/api/classes/list.php", function (data) {
            let options = "<option value=''>Select class</option>";
            data.forEach(c => {
                options += `<option value="${c.id_class}">${c.class_name}</option>`;
            });
            $("#c_class").html(options);
        });
    }

    function loadTeacherOptions() {
        $.getJSON("/Student_Absence_Management/api/teachers/list.php", function (data) {
            let options = "<option value=''>Select teacher</option>";
            data.forEach(t => {
                options += `<option value="${t.id_teacher}">${t.first_name} ${t.last_name}</option>`;
            });
            $("#c_teacher").html(options);
        });
    }

    // CREATE COURSE
    $("#createCourse").on("click", function () {
        $.post("/Student_Absence_Management/api/courses/create.php", {
            course_name: $("#c_name").val(),
            course_code: $("#c_code").val(),
            id_class: $("#c_class").val(),
            id_teacher: $("#c_teacher").val()
        }, function (res) {
            if (res.error) alert(res.error);
            else loadCourses();
        }, "json");
    });

    // DELETE COURSE
    $(document).on("click", ".delete", function () {
        let id = $(this).data("id");

        $.post("/Student_Absence_Management/api/courses/delete.php", { id_course: id }, function (res) {
            if (res.error) alert(res.error);
            else loadCourses();
        }, "json");
    });

    // EDIT COURSE
    $(document).on("click", ".edit", function () {
        let id = $(this).data("id");

        let name = prompt("New course name:");
        let code = prompt("New course code:");
        let id_class = prompt("New class ID:");
        let id_teacher = prompt("New teacher ID:");

        $.post("/Student_Absence_Management/api/courses/update.php", {
            id_course: id,
            course_name: name,
            course_code: code,
            id_class: id_class,
            id_teacher: id_teacher
        }, function (res) {
            if (res.error) alert(res.error);
            else loadCourses();
        }, "json");
    });

});
