$(document).ready(function () {

  checkSession(loadDashboardStats);

  function loadDashboardStats() {
    $.ajax({
      url: API_BASE + '/admin/dashboard_data.php',
      cache: false,
      method: 'GET',
      success: function (res) {
        if (!res.success) { return; }

        const s = res.stats || {};

      $('#studentCount').text(s.students ?? 0);
      $('#teacherCount').text(s.teachers ?? 0);
      $('#sessionCount').text(s.sessions ?? 0);

      $('#groupModuleDisplay').text(`${s.groups ?? 0}-${s.modules ?? 0}`);
      const a = s.attendance || {};
      $('#attendanceRateDashboard').text(((a.rate ?? 0)) + '%');
      },
    });
  }
});
