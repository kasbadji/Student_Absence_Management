// js/admin/students.js
$(document).ready(function () {

checkSession(loadStudents);
function loadStudents() {
  $.ajax({
    url: API_BASE + '/admin/users/get_all_students.php',
    method: 'GET',
    cache: false,
    success: function (res) {
      if (!res.success) {
        $('#studentRows').html('<tr><td colspan="4">Error loading students.</td></tr>');
        return;
      }

      const rows = res.students.map(s => `
        <tr>
          <td>${s.student_id}</td>
          <td>${s.full_name}</td>
          <td>${s.matricule}</td>
          <td>
            <button type="button" class="edit-student-btn" data-id="${s.user_id}" data-name="${s.full_name}">Edit</button>
            <button type="button" class="delete-student-btn" data-id="${s.user_id}">Delete</button>
          </td>
        </tr>`).join('');

      $('#studentRows').html(rows);
    },
    error: function () {
      $('#studentRows').html('<tr><td colspan="4">Server error.</td></tr>');
    }
  });
}

// ---------------- Create Student ----------------
$(document).on('click', '#createStudentBtn', function () {
  const payload = {
    full_name: $('#studentFullName').val().trim(),
    password: $('#studentPassword').val().trim()
  };

  if (!payload.full_name || !payload.password) {
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
        .text(res.success ? `✅ ${res.message} Matricule: ${res.student.matricule}` : `❌ ${res.message}`);
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

// ---------------- Edit Student ----------------
$(document).on('click', '.edit-student-btn', function () {


  const id = $(this).data('id');
  const oldName = $(this).data('name');
  const oldEmail = $(this).data('email') || '';

  openEditModal({
    title: 'Edit Student',
    fields: [
      { name: 'full_name', label: 'Full Name', type: 'text', value: oldName, required: true },
      { name: 'password', label: 'New Password (leave blank to keep current)', type: 'password', value: '' }
    ],
    onSubmit(values) {
      if (!values.full_name || values.full_name.trim() === '') {
        alert('Name is required.');
        return;
      }

      const payload = { user_id: id, full_name: values.full_name.trim(), email: oldEmail };
      if (values.password && values.password.trim() !== '') payload.password = values.password.trim();


      $.ajax({
        url: API_BASE + '/admin/users/update_user.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function (res) {
          console.log('students.js: update_user.php response', res);
          alert(res.message);
          loadStudents();
        },
        error: function (xhr, status, err) {
          console.error('update_user.php error:', status, err, xhr && xhr.responseText);
          alert('Server connection failed.');
        }
      });
    }
  });
});

// ---------------- Delete Student ----------------
$(document).on('click', '.delete-student-btn', function () {
  const $btn = $(this);
  const id = $btn.data('id');

  window.__deletingUsers = window.__deletingUsers || new Set();
  if (window.__deletingUsers.has(id)) return console.warn('Delete in progress for', id);

  if (!confirm('Are you sure you want to delete this user?')) return;

  window.__deletingUsers.add(id);
  $btn.prop('disabled', true);

  $.ajax({
    url: API_BASE + '/admin/users/delete_user.php',
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ user_id: id }),
    success: function (res) {
      console.log('delete_user.php response:', res);
      alert(res.message);
      loadStudents();
    },
    error: function (xhr, status, err) {
      console.error('delete_user.php error:', status, err, xhr && xhr.responseText);
      alert('Server connection failed.');
    },
    complete: function () {
      window.__deletingUsers.delete(id);
      $btn.prop('disabled', false);
    }
  });
});
});
