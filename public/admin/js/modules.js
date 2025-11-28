$(document).ready(function () {
let ALL_MODULES = [];
checkSession(loadModules);

function loadModules() {
  $.ajax({
    url: API_BASE + '/admin/modules/get_all_modules.php',
      method: 'GET',
      cache: false,
      dataType: 'json',
      xhrFields: { withCredentials: true },
    success: function (res) {
      if (!res.success) {
        $('#moduleRows').html('<tr><td colspan="4">Error loading modules.</td></tr>');
        return;
      }
      ALL_MODULES = Array.isArray(res.modules) ? res.modules : [];
      renderModules(ALL_MODULES);
    },
    error: function () {
      $('#moduleRows').html('<tr><td colspan="4">Server connection failed.</td></tr>');
    }
  });
}

function renderModules(list) {
  const rows = (list || []).map(m => {
    const flags = [];
    if (m.has_td && +m.has_td) flags.push('TD');
    if (m.has_tp && +m.has_tp) flags.push('TP');
    const sess = flags.length ? flags.join(' / ') : '-';
    return `
      <tr>
        <td>${m.module_id}</td>
        <td>${m.title}</td>
        <td>${sess}</td>
        <td>
          <button type="button" class="action-btn edit-student-btn edit-module-btn"
            title="Edit"
            data-id="${m.module_id}"
            data-title="${m.title}"
            data-has_td="${m.has_td}"
            data-has_tp="${m.has_tp}"><i class="fas fa-edit"></i></button>
          <button type="button" class="action-btn delete-student-btn delete-module-btn"
            title="Delete" data-id="${m.module_id}"><i class="fas fa-trash"></i></button>
        </td>
      </tr>`
  }).join('');

  $('#moduleRows').html(rows || '<tr><td colspan="4">No modules found.</td></tr>');
}

function debounce(fn, ms){ let t; return function(){ clearTimeout(t); const a=arguments, c=this; t=setTimeout(()=>fn.apply(c,a), ms); }; }

$('#searchModules').on('input keyup change', debounce(function(){
  const q = ($(this).val()||'').toString().toLowerCase().trim();
  if (!q) { renderModules(ALL_MODULES); return; }
  const filtered = ALL_MODULES.filter(m => {
    const flags = [];
    if (m.has_td && +m.has_td) flags.push('td');
    if (m.has_tp && +m.has_tp) flags.push('tp');
    const fields = [m.title, m.module_id && String(m.module_id), flags.join('/')];
    return fields.some(v => (v||'').toString().toLowerCase().includes(q));
  });
  renderModules(filtered);
}, 150));

$(document).on('click', '#addModuleBtn', function () {
  openEditModal({
    title: 'Add Module',
    fields: [
      { name: 'title', label: 'Title', type: 'text', value: '', required: true },
      { name: 'has_td', label: 'Has TD', type: 'checkbox', value: false },
      { name: 'has_tp', label: 'Has TP', type: 'checkbox', value: false }
    ],
    onSubmit(values) {
      if (!values.title) return alert('All fields are required.');
      $.ajax({
        url: API_BASE + '/admin/modules/create_module.php',
          method: 'POST',
          contentType: 'application/json',
          dataType: 'json',
          xhrFields: { withCredentials: true },
        data: JSON.stringify({ title: values.title.trim(), has_td: !!values.has_td, has_tp: !!values.has_tp }),
        success: function (res) { alert(res.message); if (res.success) loadModules(); },
        error: function () { alert('Server connection failed.'); }
      });
    }
  });
});

$(document).on('click', '.edit-module-btn', function () {

  const id = $(this).data('id');
  const oldTitle = $(this).data('title');

  openEditModal({
    title: 'Edit Module',
    fields: [
      { name: 'title', label: 'Title', type: 'text', value: oldTitle, required: true },
      { name: 'has_td', label: 'Has TD', type: 'checkbox', value: ($(this).data('has_td') && +$(this).data('has_td')) ? true : false },
      { name: 'has_tp', label: 'Has TP', type: 'checkbox', value: ($(this).data('has_tp') && +$(this).data('has_tp')) ? true : false }
    ],

    onSubmit(values) {
      if (!values.title) return alert('All fields are required.');

      const updatePayload = { module_id: id, title: values.title.trim(), has_td: !!values.has_td, has_tp: !!values.has_tp };

      $.ajax({
        url: API_BASE + '/admin/modules/update_module.php',
        method: 'POST',
        contentType: 'application/json',
        dataType: 'json',
        xhrFields: { withCredentials: true },
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
        dataType: 'json',
        xhrFields: { withCredentials: true },
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
