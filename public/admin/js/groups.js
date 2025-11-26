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
                <button type="button" class="edit-group-btn"
                  data-id="${g.group_id}"
                  data-name="${g.name}">Edit</button>
                <button type="button" class="delete-group-btn"
                  data-id="${g.group_id}">Delete</button>
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

// ---------------- Edit Group ----------------
$(document).on('click', '.edit-group-btn', function () {
  console.log('groups.js: edit-group-btn clicked', this, $(this).data());
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
      console.log('groups.js: sending update_group payload', updatePayload);

      $.ajax({
        url: API_BASE + '/admin/groups/update_group.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(updatePayload),
        success: function (res) {
          console.log('update_group.php response:', res);
          alert(res.message);
          if (res.success) loadGroups();
        },
        error: function (xhr, status, err) {
          console.error('update_group.php error:', status, err, xhr && xhr.responseText);
          alert('Server connection failed.');
        }
      });
    }
  });
});

// ---------------- Delete Group ----------------
$(document).on('click', '.delete-group-btn', function () {

  const id = $(this).data('id');

  $.ajax({
    url: API_BASE + '/admin/groups/delete_group.php',
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ group_id: id }),
    success: function (res) {
      alert(res.message);
      if (res.success) loadGroups();
    },
    error: function (xhr, status, err) {
      console.error('delete_group.php error:', status, err, xhr && xhr.responseText);
      alert('Server connection failed.');
    }
  });
});
});
