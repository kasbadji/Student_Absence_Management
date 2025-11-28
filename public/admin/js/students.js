$(document).ready(function () {
let ALL_STUDENTS = [];

checkSession(loadStudents);
checkSession(loadGroups);
function loadGroups() {
    $.ajax({
      url: '/api/admin/groups/get_all_groups.php',
      method: 'GET',
      cache: false,
      success(res) {
        if (typeof res === 'string') res = JSON.parse(res);

        if (res.success && Array.isArray(res.groups)) {
              $('#studentGroup').empty().append('<option value="">Select Group</option>');

              for (const g of res.groups) {
                $('#studentGroup').append(
                  `<option value="${g.group_id}">${g.name}</option>`
                );
              }
        }
      },
      error(xhr) {
        alert('Could not load groups from server.');
      }
    });
  }

function loadStudents() {
  $.ajax({
    url: API_BASE + '/admin/users/get_all_students.php',
    method: 'GET',
    cache: false,
    success: function (res) {
      if (!res.success) {
        $('#studentRows').html('<tr><td colspan="6">Error loading students.</td></tr>');
        return;
      }
      ALL_STUDENTS = Array.isArray(res.students) ? res.students : [];
      renderStudents(ALL_STUDENTS);
    },
    error: function () {
      $('#studentRows').html('<tr><td colspan="6">Server error.</td></tr>');
    }
  });
}

function renderStudents(list) {
  const rows = (list || []).map(s => `
    <tr>
      <td>${s.user_id}</td>
      <td>${s.full_name}</td>
      <td>${s.email || '-'}</td>
      <td>${s.group_name || '-'}</td>
      <td>${s.matricule || '-'}</td>
      <td>
        <div class="actions">
          <button type="button" class="action-btn edit-student-btn"
            data-id="${s.user_id}"
            data-name="${s.full_name}"
            data-email="${s.email || ''}"
            data-group-id="${s.group_id || ''}"
            title="Edit">
            <i class="fas fa-pen-to-square"></i>
          </button>
          <button type="button" class="action-btn delete-student-btn"
            data-id="${s.user_id}" title="Delete">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </td>
    </tr>`).join('');

  $('#studentRows').html(rows || '<tr><td colspan="6">No students found.</td></tr>');
}

function debounce(fn, ms) {
  let t; return function() { clearTimeout(t); const args = arguments; const ctx = this; t = setTimeout(() => fn.apply(ctx, args), ms); };
}

$('#searchStudents').on('input', debounce(function() {
  const q = ($(this).val() || '').toString().toLowerCase();
  if (!q) { renderStudents(ALL_STUDENTS); return; }
  const filtered = ALL_STUDENTS.filter(s => {
    const fields = [
      s.full_name,
      s.email,
      s.group_name,
      s.matricule,
      s.user_id && String(s.user_id)
    ];
    return fields.some(v => (v || '').toString().toLowerCase().includes(q));
  });
  renderStudents(filtered);
}, 150));

$(document).on('click', '#createStudentBtn', function () {
  const payload = {
    full_name: $('#studentFullName').val().trim(),
    password: $('#studentPassword').val().trim(),
    group_id: $('#studentGroup').val() || null
  };

  if (!payload.full_name || !payload.password ) {
    $('#studentMsg').addClass('error').text('All fields are required.');
    return;
  }

  $.ajax({
    url: API_BASE + '/admin/users/create_student.php',
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify(payload),
    success: function (res) {
      $('#studentMsg').removeClass().addClass(res.success ? 'status' : 'error')
        .text(res.success ? `${res.message} Matricule: ${res.student.matricule}` : `${res.message}`);
      if (res.success) {
        $('#studentFullName, #studentPassword').val('');
        loadStudents();
      }
    },
    error: function () {
      $('#studentMsg').removeClass().addClass('error').text('Server connection failed.');
    }
  });
});

$(document).on('click', '#addStudentBtn', function () {
  $.get(API_BASE + '/admin/groups/get_all_groups.php', function (response) {
    if (typeof response === 'string') response = JSON.parse(response);
    if (!response.success) return alert('Could not load groups list.');

    const groupOptions = `<option value="">-- No group --</option>` + response.groups.map(g =>
      `<option value="${g.group_id}">${g.name}</option>`
    ).join('');

    openEditModal({
      title: 'Add Student',
      fields: [
        { name: 'full_name', label: 'Full Name', type: 'text', value: '', required: true },
        { name: 'email', label: 'Email', type: 'email', value: '' },
        { name: 'password', label: 'Password', type: 'password', value: '', required: true },
        { name: 'group_id', label: 'Group', type: 'select', value: '', optionsHtml: groupOptions }
      ],
      onSubmit(values) {
        if (!values.full_name || !values.password) return alert('Name and password are required.');

        const payload = {
          full_name: values.full_name.trim(),
          email: values.email?.trim() || null,
          password: values.password.trim(),
          group_id: values.group_id || null
        };

        $.ajax({
          url: API_BASE + '/admin/users/create_student.php',
          method: 'POST',
          contentType: 'application/json',
          data: JSON.stringify(payload),
          success: function (res) {
            if (typeof res === 'string') res = JSON.parse(res);
            alert(res.message || (res.success ? 'Student created' : 'Create failed'));
            if (res.success) loadStudents();
          },
          error: function () { alert('Server connection failed.'); }
        });
      }
    });
  });
});

$(document).on('click', '.edit-student-btn', function () {

  const id         = $(this).data('id');
  const oldName    = $(this).data('name');
  const oldEmail   = $(this).data('email') || '';
  const oldGroupId = $(this).data('group-id') || '';

  $.get(API_BASE + '/admin/groups/get_all_groups.php', function (response) {
    if (typeof response === 'string') response = JSON.parse(response);
    if (!response.success) {
      alert('Could not load groups list.');
      return;
    }

    const groupOptions = `<option value="" ${oldGroupId === '' ? 'selected' : ''}>-- No group --</option>` + response.groups.map(g =>
      `<option value="${g.group_id}" ${g.group_id == oldGroupId ? 'selected' : ''}>${g.name}</option>`
    ).join('');

    openEditModal({
      title: 'Edit Student',
      fields: [

        { name: 'full_name', label: 'Full Name', type: 'text', value: oldName, required: true },
        { name: 'email', label: 'Email (leave blank to keep current)', type: 'email', value: oldEmail, placeholder: '' },
        { name: 'password',  label: 'New Password (leave blank to keep current)', type: 'password', value: '' },
        { name: 'group_id',  label: 'Group', type: 'select', value: oldGroupId, optionsHtml: groupOptions }
      ],

      onSubmit(values) {
        if (!values.full_name?.trim()) return alert('Name is required.');

        const payload = {
          user_id:   id,
          full_name: values.full_name.trim(),
          email:     (values.email && values.email.trim()) ? values.email.trim() : oldEmail,
          group_id:  values.group_id || null
        };

        if (values.password?.trim()) payload.password = values.password.trim();

        $.ajax({
          url: API_BASE + '/admin/users/update_student.php',
          method: 'POST',
          contentType: 'application/json',
          xhrFields: { withCredentials: true },
          data: JSON.stringify(payload),
          success(result) {
            if (typeof result === 'string') result = JSON.parse(result);
            if (result.success) {
              alert(result.message);
              loadStudents();
            } else {
              alert('❌ ' + (result.message || 'Update failed'));
            }
          },
          error(xhr, status, err) {
            alert('Server connection failed.');
          }
        });
      }
    });
  });
});

async function handleDeleteEvent(e) {
  e.preventDefault();

  let $btn = $(e.target).closest('.delete-student-btn');
  if (!$btn || $btn.length === 0) $btn = $(this);

  const idRaw = $btn.data('id');
  const id = (typeof idRaw === 'number' || (typeof idRaw === 'string' && idRaw.trim() !== '')) ? String(idRaw) : null;

  if (!id) {
    return;
  }

  window.__deletingUsers = window.__deletingUsers || new Set();
  if (window.__deletingUsers.has(id)) return;

  const urlParams = new URLSearchParams(window.location.search);
  const autoConfirm = urlParams.get('auto_confirm_delete') === '1';
  let confirmed = false;
  if (e.shiftKey || autoConfirm) {
    confirmed = true;
  } else {
    confirmed = await showConfirm('Are you sure you want to delete this user?');
  }
  if (!confirmed) return;

  window.__deletingUsers.add(id);
  $btn.prop('disabled', true).addClass('deleting');

  try {
    $.ajax({
      url: API_BASE + '/admin/users/delete_user.php',
      method: 'POST',
      contentType: 'application/json',
      dataType: 'json',
      xhrFields: { withCredentials: true },
      timeout: 10000,
      data: JSON.stringify({ user_id: id }),
      beforeSend(xhr) {},
      success: function (res, textStatus, xhr) {
        if (res && res.success) {
          alert(res.message || 'User deleted.');
          loadStudents();
        } else {
          const msg = res && res.message ? res.message : 'Delete failed (no message)';
          alert('Delete failed: ' + msg);
        }
      },
      error: function (xhr, status, err) {
        let body = xhr && xhr.responseText ? xhr.responseText : 'No response body';
        alert('Server connection failed. Response: ' + body);
      },
      complete: function () {
        window.__deletingUsers.delete(id);
        $btn.prop('disabled', false).removeClass('deleting');
      }
    });
  } catch (ex) {
    window.__deletingUsers.delete(id);
    $btn.prop('disabled', false).removeClass('deleting');
    alert('Unexpected error when attempting delete. See console.');
  }
}

$(document).off('click.deleteStudent', '.delete-student-btn').on('click.deleteStudent', '.delete-student-btn', handleDeleteEvent);
$('#studentRows').off('click.deleteStudent').on('click.deleteStudent', '.delete-student-btn', handleDeleteEvent);
});
