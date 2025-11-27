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
                <button type="button" class="action-btn edit-student-btn edit-module-btn"
                  title="Edit"
                  data-id="${m.module_id}"
                  data-title="${m.title}"
                  data-code="${m.code}"><i class="fas fa-edit"></i></button>
                <button type="button" class="action-btn delete-student-btn delete-module-btn"
                  title="Delete"
                  data-id="${m.module_id}"><i class="fas fa-trash"></i></button>
          </td>
        </tr>`).join('');

      $('#moduleRows').html(rows);
    },
    error: function () {
      $('#moduleRows').html('<tr><td colspan="4">Server connection failed.</td></tr>');
    }
  });
}

// ---------------- Create Module (modal)
$(document).on('click', '#addModuleBtn', function () {
  openEditModal({
    title: 'Add Module',
    fields: [
      { name: 'title', label: 'Title', type: 'text', value: '', required: true },
      { name: 'code', label: 'Code', type: 'text', value: '', required: true }
    ],
    onSubmit(values) {
      if (!values.title || !values.code) return alert('All fields are required.');
      $.ajax({
        url: API_BASE + '/admin/modules/create_module.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ title: values.title.trim(), code: values.code.trim() }),
        success: function (res) { alert(res.message); if (res.success) loadModules(); },
        error: function () { alert('Server connection failed.'); }
      });
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
          alert(res.message);
          if (res.success) loadModules();
        },
        error: function (xhr, status, err) {
          alert('Server connection failed.');
        }
      });
    }
  });
});

// ---------------- Delete Module ----------------
async function handleModuleDelete(e) {
  e.preventDefault();
  const $btn = $(e.target).closest('.delete-module-btn');
  if (!$btn || $btn.length === 0) return;
  const id = $btn.data('id');
  if (!id) return;
  window.__deletingModules = window.__deletingModules || new Set();
  if (window.__deletingModules.has(id)) return;
  const autoConfirm = new URLSearchParams(window.location.search).get('auto_confirm_delete') === '1';
  let confirmed = false;
  if (e.shiftKey || autoConfirm) confirmed = true;
  else confirmed = await window.showConfirm('Are you sure you want to delete this module?');
  if (!confirmed) return;
  window.__deletingModules.add(id);
  $btn.prop('disabled', true).addClass('deleting');
  try {
    $.ajax({
      url: API_BASE + '/admin/modules/delete_module.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ module_id: id }),
      success(res) { alert(res.message); if (res.success) loadModules(); },
      error() { alert('Server connection failed.'); },
      complete() { window.__deletingModules.delete(id); $btn.prop('disabled', false).removeClass('deleting'); }
    });
  } catch (ex) { window.__deletingModules.delete(id); $btn.prop('disabled', false).removeClass('deleting'); alert('Unexpected error'); }
}

$(document).off('click.deleteModule', '.delete-module-btn').on('click.deleteModule', '.delete-module-btn', handleModuleDelete);
$('#moduleRows').off('click.deleteModule').on('click.deleteModule', '.delete-module-btn', handleModuleDelete);
});
