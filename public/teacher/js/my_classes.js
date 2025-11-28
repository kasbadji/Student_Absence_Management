$(document).ready(function () {

  checkSession(loadClasses);

  function loadClasses() {
    $.ajax({
      url: API_BASE + '/teacher/get_my_classes.php',
      method: 'GET',
      dataType: 'json',
      success(res) {
        if (!res.success) {
          $('#classesContainer').html('<div class="stat-card" style="padding:1rem;">Unable to load classes.</div>');
          return;
        }

        const list = res.classes || [];
        if (list.length === 0) {
          $('#classesContainer').html('<div class="stat-card" style="padding:1rem;">No classes assigned yet.</div>');
          return;
        }

        const container = $('#classesContainer');
        container.empty();

        list.forEach(item => {
          const studentsText = (item.student_count || 0) + ' students';
          const title = item.module_title || 'Untitled Module';
          const group = item.group_name || 'Unspecified Group';

          const card = $(
            `<div class="stat-card" style="flex-direction:column; align-items:flex-start;">
                <div style="display:flex; gap:12px; align-items:center; width:100%;">
                  <div style="background:#f4f4ff; border-radius:8px; padding:10px; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-book-open" style="color:#4b3aff;"></i>
                  </div>
                  <div style="flex:1">
                    <h4 style="margin:0 0 .25rem 0;">${escapeHtml(title)}</h4>
                    <p style="margin:0; color:#666;">${escapeHtml(group)}</p>
                  </div>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; width:100%; margin-top:12px;">
                  <div style="color:#666; font-size:.95rem;"> <i class="fas fa-user-friends"></i> ${escapeHtml(studentsText)}</div>
                  <div>
                    <a class="action-btn" href="take_attendance.html?module_id=${encodeURIComponent(item.module_id)}&group_id=${encodeURIComponent(item.group_id)}">Take Attendance &rarr;</a>
                  </div>
                </div>
            </div>`
          );

          container.append(card);
        });
      },
      error() {
        $('#classesContainer').html('<div class="stat-card" style="padding:1rem;">Failed to load classes.</div>');
      }
    });
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"'`]/g, function (s) {
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;","`":"&#96;"})[s];
    });
  }
});
