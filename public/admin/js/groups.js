// js/admin/groups.js
$(document).ready(function () {

checkSession(loadGroups);
// ---------------- Load all groups ----------------
function loadGroups() {
  $.ajax({
    url: API_BASE + '/admin/groups/get_all_groups.php',
    method: 'GET',
    cache: false,
    success: function (res) {
      if (!res.success) {
        $('#groupRows').html('<tr><td colspan="3">Error loading groups.</td></tr>');
        return;
      }

      const rows = res.groups.map(g => `
        <tr>
          <td>${g.group_id}</td>
          <td>${g.name}</td>
          <td>
                <button type="button" class="action-btn edit-student-btn edit-group-btn"
                  title="Edit"
                  data-id="${g.group_id}"
                  data-name="${g.name}"><i class="fas fa-edit"></i></button>
                <button type="button" class="action-btn delete-student-btn delete-group-btn"
                  title="Delete"
                  data-id="${g.group_id}"><i class="fas fa-trash"></i></button>
          </td>
        </tr>`).join('');

      $('#groupRows').html(rows);
    },
    error: function () {
      $('#groupRows').html('<tr><td colspan="3">Server connection failed.</td></tr>');
    }
  });
}

// ---------------- Create Group ----------------
$(document).on('click', '#createGroupBtn', function () {
  const name = $('#groupName').val().trim();
  if (!name) {
    alert('Group name is required.');
    return;
  }

  $.ajax({
    url: API_BASE + '/admin/groups/create_group.php',
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ name }),
    success: function (res) {
      alert(res.message);
      if (res.success) {
        $('#groupName').val('');
        loadGroups();
      }
    },
    error: function () {
      alert('Server connection failed.');
    }
  });
});
// Remove inline create handler; use modal add instead
$(document).on('click', '#addGroupBtn', function () {
  openEditModal({
    title: 'Add Group',
    fields: [ { name: 'name', label: 'Group Name', type: 'text', value: '', required: true } ],
    onSubmit(values) {
      if (!values.name || values.name.trim() === '') return alert('Group name is required.');
      $.ajax({
        url: API_BASE + '/admin/groups/create_group.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ name: values.name.trim() }),
        success(res) { alert(res.message); if (res.success) loadGroups(); },
        error() { alert('Server connection failed.'); }
      });
    }
  });
});
// ---------------- Edit Group ----------------
$(document).on('click', '.edit-group-btn', function () {
  const id = $(this).data('id');
  const oldName = $(this).data('name');
  openEditModal({
    title: 'Edit Group',
    fields: [
      { name: 'name', label: 'Group Name', type: 'text', value: oldName, required: true }
    ],
    onSubmit(values) {
      if (!values.name || values.name.trim() === '') return alert('Group name is required.');
      const updatePayload = { group_id: id, name: values.name.trim() };
      // sending update_group payload

      $.ajax({
        url: API_BASE + '/admin/groups/update_group.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(updatePayload),
        success: function (res) {
          alert(res.message);
          if (res.success) loadGroups();
        },
        error: function (xhr, status, err) {
          alert('Server connection failed.');
        }
      });
    }
  });
});

// ---------------- Delete Group ----------------
$(document).on('click', '.delete-group-btn', function () {
  // delegated delete handler using in-page confirm
});

async function handleGroupDelete(e) {
  e.preventDefault();
  const $btn = $(e.target).closest('.delete-group-btn');
  if (!$btn || $btn.length === 0) return;
  const id = $btn.data('id');
  if (!id) return;
  window.__deletingGroups = window.__deletingGroups || new Set();
  if (window.__deletingGroups.has(id)) return;
  const autoConfirm = new URLSearchParams(window.location.search).get('auto_confirm_delete') === '1';
  let confirmed = false;
  if (e.shiftKey || autoConfirm) confirmed = true;
  else confirmed = await window.showConfirm('Are you sure you want to delete this group?');
  if (!confirmed) return;
  window.__deletingGroups.add(id);
  $btn.prop('disabled', true).addClass('deleting');
  try {
    $.ajax({
      url: API_BASE + '/admin/groups/delete_group.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ group_id: id }),
      success(res) { alert(res.message); if (res.success) loadGroups(); },
      error() { alert('Server connection failed.'); },
      complete() { window.__deletingGroups.delete(id); $btn.prop('disabled', false).removeClass('deleting'); }
    });
  } catch (ex) { window.__deletingGroups.delete(id); $btn.prop('disabled', false).removeClass('deleting'); alert('Unexpected error'); }
}

// delegated binding
$(document).off('click.deleteGroup', '.delete-group-btn').on('click.deleteGroup', '.delete-group-btn', handleGroupDelete);
$('#groupRows').off('click.deleteGroup').on('click.deleteGroup', '.delete-group-btn', handleGroupDelete);
});
