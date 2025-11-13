$(document).ready(function () {
    // 🟢 Load existing classes on page load
    fetchClasses();

    // 🟢 Handle form submission
    $("#classForm").submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: "../api/class_create.php",
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $("#message").html(`<p style='color:green;'>${response.success}</p>`);
                    $("#classForm")[0].reset();
                    fetchClasses(); // reload table
                } else {
                    $("#message").html(`<p style='color:red;'>${response.error}</p>`);
                }
            },
            error: function () {
                $("#message").html("<p style='color:red;'>Server error</p>");
            }
        });
    });

    // 🟢 Function to load classes
    function fetchClasses() {
        $.ajax({
            url: "../api/class_list.php",
            method: "GET",
            dataType: "json",
            success: function (data) {
                let tbody = $("#classTable tbody");
                tbody.empty();

                if (data.length === 0) {
                    tbody.append("<tr><td colspan='4'>No classes found</td></tr>");
                    return;
                }

                // ✅ Loop through all classes
                data.forEach((cls) => {
                    tbody.append(`
                        <tr>
                            <td>${cls.id_class}</td>
                            <td>${cls.class_name}</td>
                            <td>${cls.level}</td>
                            <td>${cls.academic_year}</td>
                        </tr>
                    `);
                });
            },
            error: function (xhr, status, error) {
                console.error("Error loading classes:", error);
            }
        });
    }
});
