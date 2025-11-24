$(document).ready(function () {

    $.getJSON("../../api/sessions/session_students.php", { id_session: ID_SESSION }, function (data) {

        const tbody = $("#studentsTable tbody");
        tbody.empty();

        data.forEach(st => {
            const currentStatus = st.absence_status ? st.absence_status : "present";

            const tr = `
                <tr>
                    <td>${st.first_name} ${st.last_name}</td>
                    <td>
                        <select class="status" data-id="${st.id_student}">
                            <option value="present" ${currentStatus === "present" ? "selected" : ""}>Present</option>
                            <option value="absent" ${currentStatus === "absent" ? "selected" : ""}>Absent</option>
                        </select>
                    </td>
                </tr>
            `;
            tbody.append(tr);
        });

    });

    $("#saveBtn").click(function () {

        let attendance = [];

        $(".status").each(function () {
            attendance.push({
                id_student: $(this).data("id"),
                status: $(this).val()
            });
        });

        $.ajax({
            url: "../../api/sessions/mark_attendance.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                id_session: ID_SESSION,
                attendance: attendance
            }),
            success: function () {
                location.reload();
            }
        });

    });

});
