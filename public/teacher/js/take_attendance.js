$(document).ready(function () {

  checkSession(initPage);

  function initPage() {
    const params = new URLSearchParams(location.search);
    const moduleId = params.get('module_id');
    const groupId = params.get('group_id');

    if (!moduleId || !groupId) {
      $('#classInfo').text('No class selected.');
      return;
    }

    $('#classInfo').text('Loading students...');

    $.ajax({
      url: API_BASE + `/teacher/get_my_classes.php`,
      method: 'GET',
      dataType: 'json',
      success(res) {
        if (res.success) {
          const cls = (res.classes || []).find(c => String(c.module_id) === String(moduleId) && String(c.group_id) === String(groupId));
          if (cls) {
            $('#classInfo').text(cls.module_title + ' — ' + cls.group_name);
          }
        }
      }
    });

    loadStudents(moduleId, groupId);

    $(document).on('click', '.mark-present', function () {
      const id = $(this).data('student');
      $(`#student-${id}`).data('present', true);
      $(`#student-${id} .present-btn`).addClass('active');
      $(`#student-${id} .absent-btn`).removeClass('active');
      updateCounts();
    });

    $(document).on('click', '.mark-absent', function () {
      const id = $(this).data('student');
      $(`#student-${id}`).data('present', false);
      $(`#student-${id} .absent-btn`).addClass('active');
      $(`#student-${id} .present-btn`).removeClass('active');
      updateCounts();
    });

    $('#saveAttendance').on('click', function () {
      const students = [];
      $('#studentsList .student-row').each(function () {
        const sid = $(this).data('sid');
        let present = $(this).data('present') === true;
        if (typeof present !== 'boolean') {
          present = $(this).find('.present-btn').hasClass('active');
        }
        students.push({ student_id: sid, present });
      });

      const params = new URLSearchParams(location.search);
      const moduleId = params.get('module_id');
      const groupId = params.get('group_id');

      $.ajax({
        url: API_BASE + '/teacher/save_attendance.php',
        method: 'POST',
        data: JSON.stringify({ module_id: moduleId, group_id: groupId, students }),
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        xhrFields: { withCredentials: true },
        success(res) {
          if (res.success) {
            alert('Attendance saved (session: ' + (res.session_id || '') + ')');
          } else {
            alert('Save failed: ' + (res.message || 'unknown'));
          }
        },
        error(xhr, status, err) {
          alert('Save failed: ' + status);
        }
      });
    });
  }

  function loadStudents(moduleId, groupId) {
    $.ajax({
      url: API_BASE + '/teacher/get_students.php',
      method: 'GET',
      data: { module_id: moduleId, group_id: groupId },
      dataType: 'json',
      success(res) {
        if (!res.success) {
          $('#studentsList').html('<div style="padding:1rem;">Unable to load students.</div>');
          return;
        }

        const list = res.students || [];
        $('#totalStudents').text(list.length);
        let present = 0;

        const container = $('#studentsList');
        container.empty();

        list.forEach(s => {
          const sid = s.student_id || s.user_id || s.id;
          const name = escapeHtml(s.full_name || s.name || s.user_name || 'Student');
          const email = escapeHtml(s.email || '');
          const initial = name ? name.trim().charAt(0).toUpperCase() : 'S';

          const isPresent = (typeof s.status === 'string') ? (s.status.toLowerCase() === 'present') : true;

          const row = $(
            `<div class="student-row" id="student-${sid}" data-sid="${sid}" data-present="${isPresent}">
              <div class="student-left">
                <div class="avatar">${initial}</div>
                <div class="student-meta">
                  <div class="student-name">${name}</div>
                  <div class="student-email">${email}</div>
                </div>
              </div>
              <div class="student-actions">
                <button class="present-btn mark-present" data-student="${sid}">Present</button>
                <button class="absent-btn mark-absent" data-student="${sid}">Absent</button>
              </div>
            </div>`
          );

          if (isPresent) {
            row.find('.present-btn').addClass('active');
            present++;
          } else {
            row.find('.absent-btn').addClass('active');
          }

          container.append(row);
          row.data('present', !!isPresent);
        });

        $('#presentCount').text(present);
        $('#absentCount').text(list.length - present);
      },
      error() {
        $('#studentsList').html('<div style="padding:1rem;">Failed to load students.</div>');
      }
    });
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>'"`]/g, function (s) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;","`":"&#96;"})[s]; });
  }

  function updateCounts() {
    const total = $('#studentsList .student-row').length;
    const present = $('#studentsList .student-row').filter(function () { return $(this).data('present') === true; }).length;
    $('#totalStudents').text(total);
    $('#presentCount').text(present);
    $('#absentCount').text(total - present);
  }

});
