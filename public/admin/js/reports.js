$(document).ready(function () {
  checkSession(init);

  function init() {
    loadGroups();
    loadStudents();
    loadRecords();
  }

  function loadGroups() {
    $.ajax({ url: API_BASE + '/admin/groups/get_all_groups.php', method: 'GET', xhrFields: { withCredentials: true }, success(res) {
      if (!res.success) return;
      const opts = res.groups.map(g => `<option value="${g.group_id}">${g.name}</option>`).join('');
      $('#filterGroup').append(opts);
    }});
  }

  function loadStudents() {
    $.ajax({ url: API_BASE + '/admin/users/get_all_students.php', method: 'GET', xhrFields: { withCredentials: true }, success(res) {
      if (!res.success) return;
      const opts = res.students.map(s => `<option value="${s.student_id}">${s.full_name}</option>`).join('');
      $('#filterStudent').append(opts);
    }});
  }

  function loadRecords() {
    const params = {};
    const df = $('#dateFrom').val();
    const dt = $('#dateTo').val();
    const gid = $('#filterGroup').val();
    const sid = $('#filterStudent').val();
    if (df) params.date_from = df;
    if (dt) params.date_to = dt;
    if (gid) params.group_id = gid;
    if (sid) params.student_id = sid;

    $.ajax({ url: API_BASE + '/admin/reports.php', method: 'GET', data: params, xhrFields: { withCredentials: true }, success(res) {
      if (!res.success) return alert('Failed to load records');
      const s = res.stats || { total:0, present:0, absent:0, rate:0 };
      $('#totalRecords').text(s.total);
      $('#presentCount').text(s.present);
      $('#absentCount').text(s.absent);
      $('#attendanceRate').text((s.rate||0) + '%');

      const rows = (res.records || []).map(r => `
        <tr>
          <td>${r.session_date || ''}</td>
          <td>${r.full_name || ''}</td>
          <td>${r.group_name || ''}</td>
          <td><span class="status ${r.status.toLowerCase()}">${r.status}</span></td>
        </tr>`).join('');
      $('#recordsBody').html(rows);
    }, error() { alert('Server connection failed.'); }});
  }

  $('#applyFilters').on('click', function () { loadRecords(); });

  $('#exportCsv').on('click', function () {
    // Simple CSV export of current table
    const rows = [['Date','Student','Group','Status']];
    $('#recordsBody tr').each(function () {
      const cols = $(this).find('td').map(function(){return $(this).text().trim();}).get();
      rows.push(cols);
    });
    const csv = rows.map(r => r.map(c => '"'+ (String(c).replace(/"/g,'""')) +'"').join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = 'attendance_report.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
  });

});
