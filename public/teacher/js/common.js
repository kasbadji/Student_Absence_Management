$(document).ready(function () {

  (function detectApiBase() {
    const path = location.pathname;
    const idx  = path.indexOf('/public/');
    window.API_BASE = idx !== -1 ? path.slice(0, idx) + '/api' : '/api';
  })();

  window.checkSession = function (callback) {
    $.ajax({
      url: API_BASE + '/auth/check_session.php',
      method: 'GET',
      dataType: 'json',
      xhrFields: { withCredentials: true },
      success(res) {
        if (!res.logged_in || res.role !== 'teacher') {
          window.location.href = '/login.html';
        } else {
          $('#teacherName').text(res.full_name);
          $('#teacherRole').text(res.role);

          if (typeof callback === 'function') callback(res);
        }
      },
      error(xhr, status, err) {
        alert('Unable to verify session. Please log in again.');
        window.location.href = '/login.html';
      }
    });
  };

  $(document).on('click', '.logout-btn', function () {
    $.ajax({
      url: API_BASE + '/auth/logout.php',
      method: 'GET',
      xhrFields: { withCredentials: true },
      success() {
        window.location.href = '/login.html';
      },
      error() {
        alert('Logout failed – please try again.');
      }
    });
  });
});
