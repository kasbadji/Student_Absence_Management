$(document).ready(function () {

    loadStudents();
    loadClassOptions();

    function loadStudents() {
        $.getJSON("../../api/students/list.php", function (data) {
            let rows = "";
            data.forEach(s => {
                rows += `
                    <tr>
                        <td>${s.id_student}</td>
                        <td>${s.first_name}</td>
                        <td>${s.last_name}</td>
                        <td>${s.matricule}</td>
                        <td>${s.date_of_birth || ""}</td>
                        <td>${s.email || ""}</td>
                        <td>${s.phone || ""}</td>
                        <td>${s.class_name || "—"}</td>

                        <td>
                            <button class="edit" data-id="${s.id_student}">Edit</button>
                            <button class="delete" data-id="${s.id_student}">Delete</button>
                        </td>
                    </tr>
                `;
            });
            $("#studentTable tbody").html(rows);
        });
    }

    function loadClassOptions() {
        $.getJSON("../../api/classes/list.php", function (classes) {
            let options = "<option value=''>Select class</option>";
            classes.forEach(c => {
                options += `<option value="${c.id_class}">${c.class_name}</option>`;
            });
            $("#s_class").html(options);
        });
    }

    // CREATE
    $("#createStudent").on("click", function () {
        $.post("../../api/students/create.php", {
            first_name: $("#s_first").val(),
            last_name: $("#s_last").val(),
            matricule: $("#s_matricule").val(),
            date_of_birth: $("#s_dob").val(),
            email: $("#s_email").val(),
            phone: $("#s_phone").val(),
            id_class: $("#s_class").val()
        }, function (res) {
            if (res.error) alert(res.error);
            else loadStudents();
        }, "json");
    });

    // DELETE
    $(document).on("click", ".delete", function () {
        let id = $(this).data("id");

        $.post("../../api/students/delete.php", { id_student: id }, function (res) {
            if (res.error) alert(res.error);
            else loadStudents();
        }, "json");
    });

    // EDIT
    $(document).on("click", ".edit", function () {
        let id = $(this).data("id");

        let first = prompt("New first name:");
        let last = prompt("New last name:");
        let matricule = prompt("New matricule:");
        let email = prompt("New email:");
        let phone = prompt("New phone:");
        let dob = prompt("New date of birth yyyy-mm-dd:");
        let id_class = prompt("New class ID:");

        $.post("../../api/students/update.php", {
            id_student: id,
            first_name: first,
            last_name: last,
            matricule: matricule,
            email: email,
            phone: phone,
            date_of_birth: dob,
            id_class: id_class
        }, function (res) {
            if (res.error) alert(res.error);
            else loadStudents();
        }, "json");
    });

});
