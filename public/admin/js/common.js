// ===================== COMMON ADMIN SCRIPT =====================

$(document).ready(function () {
  // --------------------------------------------------------------
  // 1️⃣  Dynamically detect API base path
  // --------------------------------------------------------------
  (function detectApiBase() {
    const path = location.pathname;
    const idx  = path.indexOf('/public/');
    window.API_BASE = idx !== -1 ? path.slice(0, idx) + '/api' : '/api';
  })();

  // --------------------------------------------------------------
  // 2️⃣  Helper - check session, then run a callback
  //     Call this from each page:  checkSession(loadTeachers);
  // --------------------------------------------------------------
  window.checkSession = function (callback) {
    $.ajax({
      url: API_BASE + '/auth/check_session.php',
      method: 'GET',
      dataType: 'json',
      xhrFields: { withCredentials: true },
      success(res) {
        if (!res.logged_in || res.role !== 'admin') {
          // Not authenticated or not admin → redirect
          window.location.href = '/login.html';
        } else {
          // Authenticated: greet user and run page logic
          console.log('Welcome ' + res.full_name);
          $('#adminName').text(res.full_name);
          if (typeof callback === 'function') callback(res);
        }
      },
      error(xhr, status, err) {
        console.error('Session check failed:', status, err);
        alert('⚠ Unable to verify session. Please log in again.');
        window.location.href = '/login.html';
      }
    });
  };

  // --------------------------------------------------------------
  // 3️⃣  Handle logout button click
  // --------------------------------------------------------------
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
