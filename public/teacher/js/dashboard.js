$(document).ready(function () {

  checkSession(loadDashboardStats);

  function loadDashboardStats() {
    $.ajax({
      url: API_BASE + '/teacher/dashboard_data.php',
      cache: false,
      method: 'GET',
      success: function (res) {
        if (!res.success) {
          return;
        }

        const s = res.stats || {};

        $('#studentCount').text(s.students ?? 0);
        $('#groupCount').text(s.groups ?? 0);
        $('#moduleCount').text(s.modules ?? 0);
        $('#sessionCount').text(s.sessions ?? 0);

        if (res.teacher_name) {
          $('#teacherName').text(res.teacher_name);
          $('#teacherRole').text('teacher');
        }
      },
    });
  }
});
