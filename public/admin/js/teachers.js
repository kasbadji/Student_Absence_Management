$(document).ready(function () {
  checkSession(init);

  function init() {
    loadGroups();
    loadModules();
    loadTeachers();
  }

  // ---------------- Load Groups ----------------
  function loadGroups() {
    $.ajax({
      url: API_BASE + '/admin/groups/get_all_groups.php',
      method: 'GET',
      xhrFields: { withCredentials: true },
      success(res) {
        if (!res.success) return;
        // include an explicit "no group" option so admin can clear assignment
        const options = res.groups.map(g =>
          `<option value="${g.group_id}">${g.name}</option>`
        );
        const optionsHtml = `<option value="">-- No group --</option>` + options.join('');
        $('#teacherGroup').append(optionsHtml);
      }
    });
  }

  // ---------------- Load Modules ----------------
  function loadModules() {
    $.ajax({
      url: API_BASE + '/admin/modules/get_all_modules.php',
      method: 'GET',
      xhrFields: { withCredentials: true },
      success(res) {
        if (!res.success) return;
        // include an explicit "no module" option so admin can clear assignment
        const options = res.modules.map(m =>
          `<option value="${m.module_id}">${m.title}</option>`
        );
        const optionsHtml = `<option value="">-- No module --</option>` + options.join('');
        $('#teacherModule').append(optionsHtml);
      }
    });
  }

  // ---------------- Load Teachers ----------------
  function loadTeachers() {
  $.ajax({
    url: API_BASE + '/admin/users/get_all_teachers.php',
    method: 'GET',
    xhrFields: { withCredentials: true },
    cache: false,
    success: function (res) {
      if (!res.success) {
        $('#teacherRows').html('<tr><td colspan="7">Error loading teachers.</td></tr>');
        return;
      }

      const rows = res.teachers.map(t => `
        <tr>
          <td>${t.teacher_id}</td>
          <td>${t.full_name}</td>
          <td>${t.email || '-'}</td>
          <td>${t.group_name || '-'} ${t.title || '-'}</td>
          <td>
            <button
              type="button"
              class="action-btn edit-student-btn edit-teacher-btn"
              title="Edit"
              data-id="${t.user_id}"
              data-name="${t.full_name}"
              data-email="${t.email || ''}"
              data-group_id="${t.group_id || ''}"
              data-module_id="${t.module_id || ''}"
            ><i class="fas fa-edit"></i></button>
            <button type="button" class="action-btn delete-student-btn delete-teacher-btn" title="Delete" data-id="${t.user_id}"><i class="fas fa-trash"></i></button>
          </td>
        </tr>`).join('');

      $('#teacherRows').html(rows);
    },
    error: function () {
      $('#teacherRows').html('<tr><td colspan="7">Could not connect to server.</td></tr>');
    }
  });
}

  // ---------------- Create Teacher ----------------
  // Add Teacher (open modal)
  $(document).on('click', '#addTeacherBtn', function () {
    // load groups/modules first
    Promise.all([
      $.get(API_BASE + '/admin/groups/get_all_groups.php'),
      $.get(API_BASE + '/admin/modules/get_all_modules.php')
    ]).then(([gRes, mRes]) => {
      if (typeof gRes === 'string') gRes = JSON.parse(gRes);
      if (typeof mRes === 'string') mRes = JSON.parse(mRes);
      // prepend an explicit empty option to allow clearing assignment
      const groupOptions = `<option value="">-- No group --</option>` + (gRes.groups || []).map(g => `<option value="${g.group_id}">${g.name}</option>`).join('');
      const moduleOptions = `<option value="">-- No module --</option>` + (mRes.modules || []).map(m => `<option value="${m.module_id}">${m.title}</option>`).join('');

      openEditModal({
        title: 'Add Teacher',
        fields: [
          { name: 'full_name', label: 'Full Name', type: 'text', value: '', required: true },
          { name: 'email', label: 'Email', type: 'email', value: '', required: true },
          { name: 'password', label: 'Password', type: 'password', value: '', required: true },
          { name: 'group_id', label: 'Group', type: 'select', value: '', optionsHtml: groupOptions },
          { name: 'module_id', label: 'Module', type: 'select', value: '', optionsHtml: moduleOptions }
        ],
        onSubmit(values) {
          if (!values.full_name || !values.email || !values.password) return alert('All fields are required.');
          const payload = {
            full_name: values.full_name.trim(),
            email: values.email.trim(),
            password: values.password.trim(),
            group_id: values.group_id || null,
            module_id: values.module_id || null
          };
          $.ajax({
            url: API_BASE + '/admin/users/create_teacher.php',
            method: 'POST',
            contentType: 'application/json',
            xhrFields: { withCredentials: true },
            data: JSON.stringify(payload),
            success(res) {
              alert(res.message);
              if (res.success) loadTeachers();
            },
            error() { alert('Server connection failed.'); }
          });
        }
      });
    }).catch(() => alert('Could not load groups/modules'));
  });

// ---------------- Edit Teacher ----------------
// ---------------- Edit Teacher ----------------
$(document).on('click', '.edit-teacher-btn', function () {
  const id        = $(this).data('id');
  const oldName   = $(this).data('name');
  const oldEmail  = $(this).data('email');
  const oldGroup  = $(this).data('group_id');
  const oldModule = $(this).data('module_id');

  // Load groups and modules first
  Promise.all([
    $.ajax({
      url: API_BASE + '/admin/groups/get_all_groups.php',
      method: 'GET',
      xhrFields: { withCredentials: true }
    }),
    $.ajax({
      url: API_BASE + '/admin/modules/get_all_modules.php',
      method: 'GET',
      xhrFields: { withCredentials: true }
    })
  ])
  .then(([groupRes, moduleRes]) => {
    // include a blank option first so admin can unset the assignment
    const groupOptions = [ { label: '-- No group --', value: '' } ].concat((groupRes.groups || []).map(g => ({
      label: g.name,
      value: g.group_id
    })));

    const moduleOptions = [ { label: '-- No module --', value: '' } ].concat((moduleRes.modules || []).map(m => ({
      label: m.title,
      value: m.module_id
    })));

    openEditModal({
      title: 'Edit Teacher',
      fields: [
        { name: 'full_name', label: 'Full Name', type: 'text', value: oldName, required: true },
        { name: 'email', label: 'Email', type: 'email', value: oldEmail, required: true },
        { name: 'password', label: 'New Password (leave blank to keep current)', type: 'password', value: '' },
        { name: 'group_id', label: 'Assigned Group', type: 'select', options: groupOptions, value: oldGroup || '' },
        { name: 'module_id', label: 'Assigned Module', type: 'select', options: moduleOptions, value: oldModule || '' }
      ],

      onSubmit(values) {
        if (!values.full_name || values.full_name.trim() === '') return alert('Name is required.');
        if (!values.email || values.email.trim() === '') return alert('Email is required.');

        const payload = {
          user_id: id,
          full_name: values.full_name.trim(),
          email: values.email.trim(),
          group_id: values.group_id || null,
          module_id: values.module_id || null
        };
        if (values.password && values.password.trim() !== '')
          payload.password = values.password.trim();

        $.ajax({
          url: API_BASE + '/admin/users/update_user.php',
          method: 'POST',
          contentType: 'application/json',
          xhrFields: { withCredentials: true },
          data: JSON.stringify(payload),
          success(res) {
            if (res.success) {
              alert('✅ ' + res.message);
              loadTeachers();
            } else {
              alert('❌ ' + res.message);
            }
          },
          error(xhr, status, err) {
            alert('Server connection failed.');
          }
        });
      }
    });
  })
  .catch(err => {
    // failed to load group/module lists
    alert('Failed to load list of groups/modules.');
  });
});

// ---------------- Delete Teacher ----------------
async function handleTeacherDelete(e) {
  e.preventDefault();
  const $btn = $(e.target).closest('.delete-teacher-btn');
  if (!$btn || $btn.length === 0) return;
  const id = $btn.data('id');
  if (!id) return;
  window.__deletingUsers = window.__deletingUsers || new Set();
  if (window.__deletingUsers.has(id)) return;
  const autoConfirm = new URLSearchParams(window.location.search).get('auto_confirm_delete') === '1';
  let confirmed = false;
  if (e.shiftKey || autoConfirm) confirmed = true;
  else confirmed = await window.showConfirm('Are you sure you want to delete this user?');
  if (!confirmed) return;
  window.__deletingUsers.add(id);
  $btn.prop('disabled', true).addClass('deleting');
  try {
    $.ajax({
      url: API_BASE + '/admin/users/delete_user.php',
      method: 'POST',
      contentType: 'application/json',
      xhrFields: { withCredentials: true },
      data: JSON.stringify({ user_id: id }),
      success(res) { alert(res.message); if (res.success) loadTeachers(); },
      error() { alert('Server connection failed.'); },
      complete() { window.__deletingUsers.delete(id); $btn.prop('disabled', false).removeClass('deleting'); }
    });
  } catch (ex) { window.__deletingUsers.delete(id); $btn.prop('disabled', false).removeClass('deleting'); alert('Unexpected error'); }
}

// delegated binding
$(document).off('click.deleteTeacher', '.delete-teacher-btn').on('click.deleteTeacher', '.delete-teacher-btn', handleTeacherDelete);
$('#teacherRows').off('click.deleteTeacher').on('click.deleteTeacher', '.delete-teacher-btn', handleTeacherDelete);
});
