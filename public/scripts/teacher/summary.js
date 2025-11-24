$(document).ready(function () {

    $.getJSON("../../api/sessions/summary.php", { id_teacher: ID_TEACHER }, function (data) {

        const tbody = $("#summaryTable tbody");
        tbody.empty();

        data.forEach(row => {
            const total = row.total_records;
            const absents = row.absents;
            const rate = total > 0 ? ((absents / total) * 100).toFixed(1) : "0";

            const tr = `
                <tr>
                    <td>${row.course_name}</td>
                    <td>${row.class_name}</td>
                    <td>${total}</td>
                    <td>${absents}</td>
                    <td>${rate}</td>
                </tr>
            `;

            tbody.append(tr);
        });

    });

});
