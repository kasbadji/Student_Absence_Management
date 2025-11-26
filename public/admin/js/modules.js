// js/admin/modules.js
$(document).ready(function () {
checkSession(loadModules);

function loadModules() {
  $.ajax({
    url: API_BASE + '/admin/modules/get_all_modules.php',
    method: 'GET',
    cache: false,
    success: function (res) {
      if (!res.success) {
        $('#moduleRows').html('<tr><td colspan="4">Error loading modules.</td></tr>');
        return;
      }

      const rows = res.modules.map(m => `
        <tr>
          <td>${m.module_id}</td>
          <td>${m.title}</td>
          <td>${m.code}</td>
          <td>
                <button type="button" class="edit-module-btn"
                  data-id="${m.module_id}"
                  data-title="${m.title}"
                  data-code="${m.code}">Edit</button>
                <button type="button" class="delete-module-btn"
                  data-id="${m.module_id}">Delete</button>
          </td>
        </tr>`).join('');

      $('#moduleRows').html(rows);
    },
    error: function () {
      $('#moduleRows').html('<tr><td colspan="4">Server connection failed.</td></tr>');
    }
  });
}

// ---------------- Create Module ----------------
$(document).on('click', '#createModuleBtn', function () {
  const title = $('#moduleTitle').val().trim();
  const code = $('#moduleCode').val().trim();

  if (!title || !code) {
    alert('All fields are required.');
    return;
  }

  $.ajax({
    url: API_BASE + '/admin/modules/create_module.php',
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ title, code }),
    success: function (res) {
      alert(res.message);
      if (res.success) {
        $('#moduleTitle, #moduleCode').val('');
        loadModules();
      }
    },
    error: function () {
      alert('Server connection failed.');
    }
  });
});

// ---------------- Edit Module ----------------
$(document).on('click', '.edit-module-btn', function () {

  const id = $(this).data('id');
  const oldTitle = $(this).data('title');
  const oldCode = $(this).data('code');

  openEditModal({
    title: 'Edit Module',
    fields: [
      { name: 'title', label: 'Title', type: 'text', value: oldTitle, required: true },
      { name: 'code', label: 'Code', type: 'text', value: oldCode, required: true }
    ],

    onSubmit(values) {
      if (!values.title || !values.code) return alert('All fields are required.');

      const updatePayload = { module_id: id, title: values.title.trim(), code: values.code.trim() };

      $.ajax({
        url: API_BASE + '/admin/modules/update_module.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(updatePayload),
        success: function (res) {
          console.log('update_module.php response:', res);
          alert(res.message);
          if (res.success) loadModules();
        },
        error: function (xhr, status, err) {
          console.error('update_module.php error:', status, err, xhr && xhr.responseText);
          alert('Server connection failed.');
        }
      });
    }
  });
});

// ---------------- Delete Module ----------------
$(document).on('click', '.delete-module-btn', function () {
  console.log('modules.js: delete-module-btn clicked', this, $(this).data());
  const id = $(this).data('id');

  console.log('modules.js: sending delete_module id', id);

  $.ajax({
    url: API_BASE + '/admin/modules/delete_module.php',
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ module_id: id }),
    success: function (res) {
      alert(res.message);
      if (res.success) loadModules();
    },
    error: function (xhr, status, err) {
      console.error('delete_module.php error:', status, err, xhr && xhr.responseText);
      alert('Server connection failed.');
    }
  });
});
});
