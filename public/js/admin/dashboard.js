$(document).ready(function () {

  // ---------------- Session Check ----------------
  $.get('/api/auth/check_session.php', function (res) {
    if (!res.logged_in || res.role !== 'admin') {
      window.location.href = 'login.html';
    } else {
      $('#adminName').text(res.full_name);
      loadDashboardStats();

      loadTeachers();
      loadStudents();
      loadModules();
      loadGroups();
    }
  });

  // ---------------- Logout ----------------
  $('.logout-btn').on('click', function () {
    $.get('/api/auth/logout.php', function () {
      window.location.href = 'login.html';
    });
  });

  // ---------------- Dashboard Stats ----------------
  function loadDashboardStats() {
    $.ajax({
      url: '/api/admin/dashboard_data.php',
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
