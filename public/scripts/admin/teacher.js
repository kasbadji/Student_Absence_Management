$(document).ready(function () {

    loadTeachers();

    function loadTeachers() {
        $.ajax({
            url: "../../api/teachers/list.php",
            method: "GET",
            success: function (data) {
                let rows = "";
                data.forEach(t => {
                    rows += `
                        <tr>
                            <td>${t.id_teacher}</td>
                            <td>${t.first_name}</td>
                            <td>${t.last_name}</td>
                            <td>${t.email || ""}</td>
                            <td>
                                <button class="edit" data-id="${t.id_teacher}">Edit</button>
                                <button class="delete" data-id="${t.id_teacher}">Delete</button>
                            </td>
                        </tr>`;
                });
                $("#teacherTable tbody").html(rows);
            }
        });
    }

    // CREATE
    $("#createTeacher").on("click", function () {

        $.ajax({
            url: "../../api/teachers/create.php",
            method: "POST",
            data: {
                first_name: $("#t_first").val(),
                last_name: $("#t_last").val(),
                email: $("#t_email").val()
            },
            success: function (res) {
                if (res.error) { alert(res.error); return; }
                loadTeachers();
            }
        });

    });

    // DELETE
    $(document).on("click", ".delete", function () {
        let id = $(this).data("id");

        $.post("../../api/teachers/delete.php",
            { id_teacher: id },
            function (res) {
                if (res.error) { alert(res.error); return; }
                loadTeachers();
            }
        );
    });

    // EDIT
    $(document).on("click", ".edit", function () {
        let id = $(this).data("id");

        let first = prompt("New first name:");
        let last = prompt("New last name:");
        let email = prompt("New email:");

        $.post("../../api/teachers/update.php", {
            id_teacher: id,
            first_name: first,
            last_name: last,
            email: email
        }, function (res) {
            if (res.error) { alert(res.error); return; }
            loadTeachers();
        });
    });

});
