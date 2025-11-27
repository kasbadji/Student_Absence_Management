$(function () {
  const modalHtml = `
  <div id="editModal" class="modal-overlay" style="display:none;">
    <div class="modal-dialog" role="dialog" aria-modal="true">
      <div class="modal-header">
        <h3 id="editModalTitle">Edit</h3>
        <button type="button" class="modal-close" id="editModalClose" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
        <form id="editModalForm">
          <div id="editModalMessage" style="margin-bottom:8px;color:#333"></div>
          <div id="editModalFields"></div>
          <div class="modal-footer">
            <button type="button" id="editModalCancel" class="btn btn-secondary">Cancel</button>
            <button type="submit" id="editModalSave" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>`;

  $('body').append(modalHtml);

  function openEditModal(opts) {
    const $modal = $('#editModal');
    const $fields = $('#editModalFields').empty();
    $('#editModalMessage').text(opts.message || '');
    $('#editModalTitle').text(opts.title || 'Edit');

    const submitText = opts.submitText || 'Save';
    const cancelText = opts.cancelText || 'Cancel';
    $('#editModalSave').text(submitText);
    $('#editModalCancel').text(cancelText);

    // build each field
    opts.fields.forEach(f => {
      const required = f.required ? 'required' : '';
      const val = f.value !== undefined ? f.value : '';
      const type = f.type || 'text';
      let $el;

      if (type === 'select') {
        // support either pre-rendered HTML (`optionsHtml`) or an `options` array of {label,value}
        let optionsHtml = '';
        if (f.optionsHtml) {
          optionsHtml = f.optionsHtml;
        } else if (Array.isArray(f.options)) {
          const val = f.value !== undefined ? String(f.value) : '';
          optionsHtml = f.options.map(opt => {
            const optVal = opt.value !== undefined ? String(opt.value) : '';
            const sel = (optVal === val) ? ' selected' : '';
            return `<option value="${opt.value}"${sel}>${opt.label}</option>`;
          }).join('');
        }

        $el = $(
          `<div class="form-row">
            <label>${f.label}</label>
            <select name="${f.name}" ${required}>
              ${optionsHtml}
            </select>
          </div>`
        );
      } else {
        // support optional placeholder property
        const placeholderAttr = f.placeholder ? `placeholder="${f.placeholder}"` : '';
        $el = $(
          `<div class="form-row">
            <label>${f.label}</label>
            <input name="${f.name}" type="${type}" value="${val}" ${placeholderAttr} ${required} />
          </div>`
        );
      }

      $fields.append($el);
    });

    // show modal
    $modal.fadeIn(120);
    $modal.find('input, select').first().focus();

    // cleanup + event bindings
    function cleanup() {
      $modal.fadeOut(120);
      $('#editModalForm').off('submit');
      $('#editModalCancel').off('click');
      $('#editModalClose').off('click');
      $modal.off('click', overlayClick);
      $(document).off('keydown', onKeyDown);
    }
    $('#editModalCancel').on('click', function () { if (typeof opts.onCancel === 'function') opts.onCancel(); cleanup(); });
    $('#editModalClose').on('click', function () { if (typeof opts.onCancel === 'function') opts.onCancel(); cleanup(); });

    function overlayClick(e) {
      if (e.target === $modal[0]) { if (typeof opts.onCancel === 'function') opts.onCancel(); cleanup(); }
    }

    $modal.on('click', overlayClick);

    function onKeyDown(e) {
      if (e.key === 'Escape') { if (typeof opts.onCancel === 'function') opts.onCancel(); cleanup(); }
    }
    $(document).on('keydown', onKeyDown);

    $('#editModalForm').on('submit', function (e) {
      e.preventDefault();
      const values = {};
      $modal.find('input, select').each(function () {
        values[$(this).attr('name')] = $(this).val();
      });
      cleanup();
      if (typeof opts.onSubmit === 'function') opts.onSubmit(values);
    });
  }

  // expose globally
  window.openEditModal = openEditModal;

  // showConfirm uses openEditModal for consistent styling and behavior
  window.showConfirm = function (message) {
    return new Promise((resolve) => {
      openEditModal({
        title: 'Confirm',
        message: message || 'Are you sure?',
        fields: [],
        submitText: 'Yes',
        cancelText: 'No',
        onSubmit() { resolve(true); },
        onCancel() { resolve(false); }
      });
    });
  };
});
