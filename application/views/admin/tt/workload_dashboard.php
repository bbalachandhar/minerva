<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
  <h1>Teacher Workload Dashboard <small>Pre-generation load analysis and reassignment</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li>Auto Timetable</li>
    <li class="active">Workload Dashboard</li>
  </ol>
</section>
<section class="content">

<!-- Summary boxes (filled by JS) -->
<div class="row" id="summary-row">
  <div class="col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Teachers</span>
        <span class="info-box-number" id="stat-total">—</span>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-red"><i class="fa fa-exclamation-triangle"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Overloaded</span>
        <span class="info-box-number" id="stat-over">—</span>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-yellow"><i class="fa fa-warning"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Near Limit (≥80%)</span>
        <span class="info-box-number" id="stat-near">—</span>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Within Limit</span>
        <span class="info-box-number" id="stat-ok">—</span>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-bar-chart"></i> Teacher Load Summary</h3>
        <div class="box-tools pull-right">
          <div class="btn-group">
            <button class="btn btn-sm btn-default active" id="filter-all">All Teachers</button>
            <button class="btn btn-sm btn-danger"         id="filter-over">Overloaded Only</button>
            <button class="btn btn-sm btn-warning"        id="filter-near">Near Limit</button>
          </div>
          <button class="btn btn-sm btn-default" id="btn-refresh" style="margin-left:6px;">
            <i class="fa fa-refresh"></i> Refresh
          </button>
          <a href="<?php echo site_url('admin/tt/subject_load'); ?>" class="btn btn-sm btn-info" style="margin-left:6px;">
            <i class="fa fa-edit"></i> Manage Subject Loads
          </a>
        </div>
      </div>
      <div class="box-body p-0">
        <div id="workload-loading" class="text-center p-4" style="display:none;">
          <i class="fa fa-spinner fa-spin fa-2x"></i><br><small>Loading workload data…</small>
        </div>
        <table class="table table-bordered table-hover" id="workload-table" style="font-size:13px;margin-bottom:0;">
          <thead>
            <tr style="background:#3c8dbc;color:#fff;">
              <th style="width:30px;"></th>
              <th>Teacher</th>
              <th style="width:80px;">Assigned</th>
              <th style="width:80px;">Cap</th>
              <th style="width:60px;">Status</th>
              <th>Load Bar</th>
            </tr>
          </thead>
          <tbody id="workload-tbody">
            <tr><td colspan="6" class="text-center text-muted p-4">Loading…</td></tr>
          </tbody>
        </table>
      </div>
      <div class="box-footer">
        <small class="text-muted">
          <i class="fa fa-info-circle"></i>
          Default cap is <strong>36 periods/week</strong> for teachers without a configured constraint row.
          Click the <i class="fa fa-chevron-right"></i> button to see class-subject breakdown and reassign subjects.
        </small>
      </div>
    </div>
  </div>
</div>

</section>
</div>

<!-- Reassign Modal -->
<div class="modal fade" id="reassign-modal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-exchange"></i> Reassign Subject</h4>
      </div>
      <div class="modal-body">
        <p id="reassign-desc" style="font-size:13px;margin-bottom:10px;"></p>
        <div class="form-group">
          <label>Assign To</label>
          <select class="form-control" id="reassign-new-teacher">
            <option value="">-- Select Teacher --</option>
            <?php foreach ($staff_list as $st): ?>
            <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['name'].' '.$st['surname']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <input type="hidden" id="reassign-load-id">
        <input type="hidden" id="reassign-old-teacher">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-warning" id="btn-do-reassign"><i class="fa fa-save"></i> Reassign</button>
      </div>
    </div>
  </div>
</div>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';
  var allData   = [];
  var filterMode = 'all';

  $('#reassign-new-teacher').select2({ placeholder: '-- Select Teacher --', allowClear: true, width: '100%', dropdownParent: $('#reassign-modal') });

  function loadData() {
    $('#workload-loading').show();
    $('#workload-tbody').html('<tr><td colspan="6" class="text-center text-muted p-3"><i class="fa fa-spinner fa-spin"></i></td></tr>');
    $.post('<?php echo site_url('admin/tt/get_pregeneration_workload'); ?>', {}, function(res) {
      $('#workload-loading').hide();
      if (res.status !== '1') { alert('Failed to load workload data.'); return; }
      allData = res.data;
      // refresh CSRF from response headers or assume same
      renderTable();
      renderStats();
    }, 'json');
  }

  function renderStats() {
    var over = 0, near = 0, ok = 0;
    $.each(allData, function(_, t) {
      var pct = t.cap > 0 ? (t.total_ppw / t.cap * 100) : 100;
      if (t.total_ppw > t.cap)   over++;
      else if (pct >= 80)        near++;
      else                       ok++;
    });
    $('#stat-total').text(allData.length);
    $('#stat-over').text(over);
    $('#stat-near').text(near);
    $('#stat-ok').text(ok);
  }

  function renderTable() {
    var filtered = allData.filter(function(t) {
      if (filterMode === 'over') return t.total_ppw > t.cap;
      if (filterMode === 'near') { var p = t.cap > 0 ? t.total_ppw / t.cap * 100 : 100; return p >= 80 && t.total_ppw <= t.cap; }
      return true;
    });
    if (filtered.length === 0) {
      $('#workload-tbody').html('<tr><td colspan="6" class="text-center text-muted p-4">No teachers match the current filter.</td></tr>');
      return;
    }
    var html = '';
    $.each(filtered, function(_, t) {
      var pct  = t.cap > 0 ? Math.round(t.total_ppw / t.cap * 100) : 100;
      var over = t.total_ppw > t.cap;
      var near = !over && pct >= 80;
      var barCls = over ? 'danger' : (near ? 'warning' : 'success');
      var badge  = over ? '<span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> OVER +'+(t.total_ppw - t.cap)+'</span>'
                 : (near ? '<span class="label label-warning">NEAR '+pct+'%</span>'
                 : '<span class="label label-success">OK</span>');
      var rowStyle = over ? 'background:#fff5f5;' : '';

      html += '<tr class="teacher-row" data-id="'+t.staff_id+'" style="'+rowStyle+'">';
      html += '<td style="text-align:center;vertical-align:middle;"><button class="btn btn-xs btn-default btn-expand" data-id="'+t.staff_id+'"><i class="fa fa-chevron-right"></i></button></td>';
      html += '<td><strong>'+escHtml(t.name)+'</strong><br><small class="text-muted">'+escHtml(t.employee_id || '')+'</small></td>';
      html += '<td style="text-align:center;font-weight:bold;color:'+(over?'#c0392b':'inherit')+'">'+t.total_ppw+'</td>';
      html += '<td style="text-align:center;">'+t.cap+'</td>';
      html += '<td style="text-align:center;">'+badge+'</td>';
      html += '<td style="vertical-align:middle;min-width:120px;"><div class="progress progress-sm" style="margin:0;"><div class="progress-bar progress-bar-'+barCls+'" style="width:'+Math.min(pct,100)+'%;"></div></div><small style="font-size:10px;">'+pct+'%</small></td>';
      html += '</tr>';

      // Detail sub-row (hidden by default)
      html += '<tr class="detail-row" data-parent="'+t.staff_id+'" style="display:none;">';
      html += '<td></td>';
      html += '<td colspan="5" style="padding:0;">';
      if (t.assignments.length === 0) {
        html += '<div class="p-2 text-muted"><small>No subject load entries found.</small></div>';
      } else {
        html += '<table class="table table-condensed" style="margin:0;font-size:12px;background:#fafafa;">';
        html += '<thead><tr style="background:#ecf0f1;"><th>Class / Section</th><th>Subject</th><th style="text-align:center;">PPW</th><th style="width:130px;">Action</th></tr></thead><tbody>';
        $.each(t.assignments, function(_, a) {
          html += '<tr>';
          html += '<td>'+escHtml(a.class)+'</td>';
          html += '<td>'+escHtml(a.subject)+'</td>';
          html += '<td style="text-align:center;"><strong>'+a.ppw+'</strong></td>';
          html += '<td>';
          if (a.is_joint) {
            html += '<span class="label label-default" title="Joint lessons are managed in the Joint Lessons screen"><i class="fa fa-link"></i> Joint</span>';
          } else {
            html += '<button class="btn btn-xs btn-warning btn-reassign" '
                  + 'data-load="'+a.load_id+'" '
                  + 'data-old="'+t.staff_id+'" '
                  + 'data-teacher="'+escHtml(t.name)+'" '
                  + 'data-class="'+escHtml(a.class)+'" '
                  + 'data-subject="'+escHtml(a.subject)+'">'
                  + '<i class="fa fa-exchange"></i> Reassign</button>';
          }
          html += '</td></tr>';
        });
        html += '</tbody></table>';
      }
      html += '</td></tr>';
    });
    $('#workload-tbody').html(html);
  }

  function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  // Expand/collapse
  $(document).on('click', '.btn-expand', function(){
    var id = $(this).data('id');
    var $detail = $('tr.detail-row[data-parent="'+id+'"]');
    var $icon   = $(this).find('i');
    if ($detail.is(':visible')) {
      $detail.hide();
      $icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
    } else {
      $detail.show();
      $icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }
  });

  // Reassign button
  $(document).on('click', '.btn-reassign', function(){
    var d = $(this).data();
    $('#reassign-load-id').val(d.load);
    $('#reassign-old-teacher').val(d.old);
    $('#reassign-desc').html(
      'Reassigning <strong>'+escHtml(d.subject)+'</strong> for <strong>'+escHtml(d['class'])+'</strong><br>'
      + 'Currently: <span class="label label-default">'+escHtml(d.teacher)+'</span>'
    );
    $('#reassign-new-teacher').val('').trigger('change');
    $('#reassign-modal').modal('show');
  });

  // Do reassign
  $('#btn-do-reassign').on('click', function(){
    var newT = $('#reassign-new-teacher').val();
    if (!newT) { alert('Please select a teacher.'); return; }
    var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/reassign_subject_teacher'); ?>', {
      load_id:       $('#reassign-load-id').val(),
      old_teacher_id:$('#reassign-old-teacher').val(),
      new_teacher_id: newT,
      [csrf_name]:   csrf_val
    }, function(res){
      $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Reassign');
      if (res.status === '1') {
        $('#reassign-modal').modal('hide');
        loadData();
      } else {
        alert('Failed to reassign: ' + (res.message || 'Unknown error'));
      }
    }, 'json');
  });

  // Filters
  $('#filter-all').on('click', function(){ filterMode='all'; $('.btn-group .btn').removeClass('active'); $(this).addClass('active'); renderTable(); });
  $('#filter-over').on('click', function(){ filterMode='over'; $('.btn-group .btn').removeClass('active'); $(this).addClass('active'); renderTable(); });
  $('#filter-near').on('click', function(){ filterMode='near'; $('.btn-group .btn').removeClass('active'); $(this).addClass('active'); renderTable(); });
  $('#btn-refresh').on('click', loadData);

  loadData();
});
</script>
</div>
