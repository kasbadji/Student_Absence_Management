$(document).ready(function () {
    // Fetch and show classes
    function loadClasses() {
        $.getJSON("../../api/classes/list.php", function (data) {
            let rows = "";

            if (data.length > 0) {
                data.forEach(function (c) {
                    rows += `
                        <tr>
                            <td>${c.id_class}</td>
                            <td><input type="text" class="edit-name" value="${c.class_name}" data-id="${c.id_class}"></td>
                            <td><input type="text" class="edit-level" value="${c.level}" data-id="${c.id_class}"></td>
                            <td><input type="text" class="edit-year" value="${c.academic_year}" data-id="${c.id_class}"></td>
                            <td>
                                <button class="updateBtn" data-id="${c.id_class}">💾 Save</button>
                                <button class="deleteBtn" data-id="${c.id_class}">🗑️ Delete</button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                rows = `<tr><td colspan='5'>No classes found</td></tr>`;
            }

            $("#classTable tbody").html(rows);
        });
    }

    loadClasses();

    // Create new class
    $("#classForm").on("submit", function (e) {
        e.preventDefault();

        $.post("../../api/classes/create.php", $(this).serialize(), function (response) {
            if (response.success) {
                $("#message").html(`<p style='color:green'>${response.success}</p>`);
                $("#classForm")[0].reset();
                loadClasses();
            } else {
                $("#message").html(`<p style='color:red'>${response.error}</p>`);
            }
        }, "json");
    });

    // Delete class
    $(document).on("click", ".deleteBtn", function () {
        if (!confirm("Are you sure you want to delete this class?")) return;

        const id = $(this).data("id");
        $.post("../../api/classes/delete.php", { id_class: id }, function (response) {
            if (response.success) {
                $("#message").html(`<p style='color:green'>${response.success}</p>`);
                loadClasses();
            } else {
                $("#message").html(`<p style='color:red'>${response.error}</p>`);
            }
        }, "json");
    });

    // Update class
    $(document).on("click", ".updateBtn", function () {
        const id = $(this).data("id");
        const name = $(`.edit-name[data-id='${id}']`).val();
        const level = $(`.edit-level[data-id='${id}']`).val();
        const year = $(`.edit-year[data-id='${id}']`).val();

        $.post("../../api/classes/update.php", {
            id_class: id,
            class_name: name,
            level: level,
            academic_year: year
        }, function (response) {
            if (response.success) {
                $("#message").html(`<p style='color:green'>${response.success}</p>`);
                loadClasses();
            } else {
                $("#message").html(`<p style='color:red'>${response.error}</p>`);
            }
        }, "json");
    });
});
