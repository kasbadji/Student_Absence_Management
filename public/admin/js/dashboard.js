$(document).ready(function () {

checkSession(loadDashboardStats);

  function loadDashboardStats() {
    $.ajax({
      url: API_BASE + '/admin/dashboard_data.php',
      cache: false,
      method: 'GET',
      success: function (res) {
        if (!res.success) return console.error('Stats error:', res.message);
        $('#studentCount').text(res.stats.students);
        $('#teacherCount').text(res.stats.teachers);
        $('#moduleCount').text(res.stats.modules);
        $('#sessionCount').text(res.stats.sessions);
      },
      error: function () {
        console.error('Failed to load dashboard stats.');
      }
    });
  }
});
