$(document).ready(function () {

    $.getJSON("../../api/sessions/list_by_teacher.php", { id_teacher: ID_TEACHER }, function (data) {

        const tbody = $("#sessionsTable tbody");
        tbody.empty();

        data.forEach(item => {
            const tr = `
                <tr>
                    <td>${item.course_name}</td>
                    <td>${item.class_name}</td>
                    <td>${item.session_date}</td>
                    <td>${item.start_time} - ${item.end_time}</td>
                    <td><a href="session.php?id_session=${item.id_session}">Open</a></td>
                </tr>`;
            tbody.append(tr);
        });

    });

});
