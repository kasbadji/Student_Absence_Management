$(document).ready(function () {

  checkSession(loadDashboardStats);

  function loadDashboardStats() {
    $.ajax({
      url: API_BASE + '/admin/dashboard_data.php',
      cache: false,
      method: 'GET',
      success: function (res) {
        if (!res.success) {
          return;
        }

        const s = res.stats || {};

      $('#studentCount').text(s.students ?? 0);
      $('#teacherCount').text(s.teachers ?? 0);
      // Dashboard shows groups count in the UI (id="groupCount"); populate it from stats.groups
      $('#groupCount').text(s.groups ?? 0);
      // keep moduleCount update if a module element exists elsewhere
      $('#moduleCount').text(s.modules ?? 0);
      $('#sessionCount').text(s.sessions ?? 0);


      },
      error: function (xhr, status, err) {
        // failed to load dashboard stats
      }
    });
  }
});
