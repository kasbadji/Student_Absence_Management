$(function () {
  const modalHtml = `
  <div id="editModal" style="display:none; position:fixed; left:0; top:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999;">
    <div style="background:#fff; width:400px; max-width:95%; margin:80px auto; padding:20px; border-radius:6px; box-shadow:0 6px 24px rgba(0,0,0,0.3);">
      <h3 id="editModalTitle">Edit</h3>
      <form id="editModalForm">
        <div id="editModalFields"></div>
        <div style="text-align:right; margin-top:12px;">
          <button type="button" id="editModalCancel">Cancel</button>
          <button type="submit" id="editModalSave">Save</button>
        </div>
      </form>
    </div>
  </div>`;

  $('body').append(modalHtml);

  function openEditModal(opts) {
    const $modal = $('#editModal');
    const $fields = $('#editModalFields').empty();
    $('#editModalTitle').text(opts.title || 'Edit');

    // build each field
    opts.fields.forEach(f => {
      const required = f.required ? 'required' : '';
      const val       = f.value !== undefined ? f.value : '';
      const type      = f.type || 'text';
      let el;

      if (type === 'select') {
        // 👇 create a dropdown element
        const optionsHtml = f.optionsHtml || '';
        el = $(`
          <div style="margin-bottom:8px;">
            <label style="display:block;margin-bottom:4px;">${f.label}</label>
            <select name="${f.name}" ${required} style="width:100%; padding:6px;">
              ${optionsHtml}
            </select>
          </div>
        `);
      } else {
        // normal input
        el = $(`
          <div style="margin-bottom:8px;">
            <label style="display:block;margin-bottom:4px;">${f.label}</label>
            <input name="${f.name}" type="${type}" value="${val}" ${required} style="width:100%; padding:6px;" />
          </div>
        `);
      }

      $fields.append(el);
    });

    console.log('edit_modal: openEditModal called', opts && opts.title);
    $modal.show();
    $modal.find('input, select').first().focus();

    // cleanup + event bindings
    function cleanup() {
      $modal.hide();
      $('#editModalForm').off('submit');
      $('#editModalCancel').off('click');
    }

    $('#editModalCancel').on('click', cleanup);

    $('#editModalForm').on('submit', function (e) {
      e.preventDefault();
      const values = {};
      // collect both inputs and selects 👇
      $modal.find('input, select').each(function () {
        values[$(this).attr('name')] = $(this).val();
      });
      console.log('edit_modal: form submit', values);
      cleanup();
      if (typeof opts.onSubmit === 'function') opts.onSubmit(values);
    });
  }

  // expose globally
  window.openEditModal = openEditModal;
});
