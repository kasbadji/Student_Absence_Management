// js/admin/teachers.js
function loadTeachers() {
  $.ajax({
    url: '/api/admin/users/get_all_teachers.php',
    method: 'GET',
    cache: false,
    success: function (res) {
      if (!res.success) {
        $('#teacherRows').html('<tr><td colspan="5">Error loading teachers.</td></tr>');
        return;
      }

      const rows = res.teachers.map(t => `
        <tr>
          <td>${t.teacher_id}</td>
          <td>${t.full_name}</td>
          <td>${t.email || '-'}</td>
          <td>${t.matricule}</td>
          <td>
            <button type="button" class="edit-teacher-btn" data-id="${t.user_id}" data-name="${t.full_name}" data-email="${t.email || ''}">Edit</button>
            <button type="button" class="delete-teacher-btn" data-id="${t.user_id}">Delete</button>
          </td>
        </tr>`).join('');

      $('#teacherRows').html(rows);
    },
    error: function () {
      $('#teacherRows').html('<tr><td colspan="5">Could not connect to server.</td></tr>');
    }
  });
}

// ---------------- Create Teacher ----------------
$(document).on('click', '#createTeacherBtn', function () {
  const payload = {
    full_name: $('#teacherFullName').val().trim(),
    email: $('#teacherEmail').val().trim(),
    password: $('#teacherPassword').val().trim()
  };

  if (!payload.full_name || !payload.email || !payload.password) {
    $('#teacherMsg').addClass('error').text('All fields are required.');
    return;
  }

  $.ajax({
    url: '/api/admin/users/create_teacher.php',
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify(payload),
    success: function (res) {
      $('#teacherMsg').removeClass().addClass(res.success ? 'status' : 'error')
        .text(res.success ? `✅ ${res.message} Matricule: ${res.teacher.matricule}` : `❌ ${res.message}`);
      if (res.success) {
        $('#teacherFullName, #teacherEmail, #teacherPassword').val('');
        loadTeachers();
      }
    },
    error: function () {
      $('#teacherMsg').removeClass().addClass('error').text('Server connection failed.');
    }
  });
});

// ---------------- Edit Teacher ----------------
$(document).on('click', '.edit-teacher-btn', function () {
  const id        = $(this).data('id');
  const oldName   = $(this).data('name');
  const oldEmail  = $(this).data('email');


  openEditModal({
    title: 'Edit Teacher',
    fields: [
      { name: 'full_name', label: 'Full Name', type: 'text', value: oldName, required: true },
      { name: 'email', label: 'Email', type: 'email', value: oldEmail, required: true },
      { name: 'password', label: 'New Password (leave blank to keep current)', type: 'password', value: '' }
    ],
    onSubmit(values) {
      if (!values.full_name || values.full_name.trim() === '') return alert('Name is required.');
      if (!values.email || values.email.trim() === '') return alert('Email is required.');

      const payload = {
        user_id: id,
        full_name: values.full_name.trim(),
        email: values.email.trim()
      };
      if (values.password && values.password.trim() !== '') payload.password = values.password.trim();

      console.log('teachers.js → sending update_user payload:', payload);

      $.ajax({
        url: '/api/admin/users/update_user.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success(res) {
          console.log('update_user.php response:', res);
          if (res.success) {
            alert('✅ ' + res.message);
            loadTeachers();
          } else {
            alert('❌ ' + res.message);
          }
        },
        error(xhr, status, err) {
          console.error('update_user.php error:', status, err, xhr && xhr.responseText);
          alert('Server connection failed.');
        }
      });
    }
  });
});

// ---------------- Delete Teacher ----------------
$(document).on('click', '.delete-teacher-btn', function () {

  const id = $(this).data('id');

  $.ajax({
    url: '/api/admin/users/delete_user.php',
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ user_id: id }),
    success: function (res) {
      console.log('delete_user.php response:', res);
      alert(res.message);
      loadTeachers();
    },
    error: function (xhr, status, err) {
      console.error('delete_user.php error:', status, err, xhr && xhr.responseText);
      alert('Server connection failed.');
    }
  });
});
