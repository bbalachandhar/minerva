<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Class Timetable <small>View, edit and manage class timetable manually</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Class Timetable</li>
    </ol>
</section>
<section class="content">

<!-- Filter bar -->
<div class="box box-primary">
  <div class="box-body">
    <div class="row" style="align-items:flex-end;">
      <div class="col-md-3">
        <label>Department</label>
        <select class="form-control" id="cg_dept">
          <option value="">-- All --</option>
          <?php foreach ($departments as $d): ?><option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['department_name']); ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label>Class <span class="text-danger">*</span></label>
        <select class="form-control" id="cg_class">
          <option value="">-- Select Class --</option>
          <?php foreach ($classlist as $c): ?><option value="<?php echo $c['id']; ?>" data-dept="<?php echo $c['department_id']; ?>"><?php echo htmlspecialchars($c['class']); ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label>Section <span class="text-danger">*</span></label>
        <select class="form-control" id="cg_section"><option value="">-- Select Section --</option></select>
      </div>
      <div class="col-md-3">
        <div class="row" style="margin-top:25px;">
          <div class="col-xs-5 pr-0">
            <button class="btn btn-primary btn-block" id="btn-load-grid"><i class="fa fa-table"></i> Load</button>
          </div>
          <div class="col-xs-7 pl-1">
            <div class="btn-group" id="export-btns" style="display:none;">
              <button class="btn btn-default" id="btn-print-grid"  title="Print"><i class="fa fa-print"></i></button>
              <button class="btn btn-danger"  id="btn-pdf-grid"    title="Export PDF"><i class="fa fa-file-pdf-o"></i></button>
              <button class="btn btn-success" id="btn-excel-grid"  title="Export Excel"><i class="fa fa-file-excel-o"></i></button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Week navigation (shown after first load) -->
    <div class="row" id="week-nav-row" style="display:none;margin-top:8px;">
      <div class="col-md-12 text-center">
        <div class="btn-group">
          <button class="btn btn-sm btn-default" id="btn-week-prev"><i class="fa fa-chevron-left"></i> Prev Week</button>
          <button class="btn btn-sm btn-default" id="btn-week-cur"><i class="fa fa-calendar"></i> Current Week</button>
          <button class="btn btn-sm btn-default" id="btn-week-next">Next Week <i class="fa fa-chevron-right"></i></button>
        </div>
        <span id="week-label" class="text-muted" style="margin-left:10px;font-size:12px;"></span>
      </div>
    </div>
  </div>
</div>

<!-- Legend -->
<div class="row" style="margin-bottom:8px;padding-left:15px;">
  <span class="label label-primary" style="font-size:12px;padding:5px 10px;">Theory</span>&nbsp;
  <span class="label label-danger"  style="font-size:12px;padding:5px 10px;">Practical / Lab</span>&nbsp;
  <span class="label label-warning" style="font-size:12px;padding:5px 10px;">Project</span>&nbsp;
  <span class="label label-success" style="font-size:12px;padding:5px 10px;">Free / PT</span>&nbsp;
  <span style="font-size:12px;"><i class="fa fa-lock text-danger"></i> = Locked (won't be moved by auto-gen)</span>
</div>

<!-- Hidden flat table for DataTables export (no colspan) -->
<div style="display:none;"><table id="export-flat-table" class="display"></table></div>

<!-- Grid container -->
<div id="timetable-grid-container">
  <div class="text-center text-muted p-5" id="grid-placeholder">
    <i class="fa fa-arrow-up fa-2x"></i><br>Select Class and Section to load the timetable.
  </div>
</div>

<!-- Cell edit modal -->
<div class="modal fade" id="cell-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-edit"></i> <span id="modal-title-text">Edit Slot</span></h4>
      </div>
      <div class="modal-body">
        <form id="cell-form">
          <input type="hidden" id="cell_id" name="cell_id" value="0">
          <input type="hidden" id="cell_class_id" name="class_id">
          <input type="hidden" id="cell_section_id" name="section_id">
          <input type="hidden" id="cell_day" name="day">
          <input type="hidden" id="cell_period_id" name="period_id">

          <div id="regular-fields">
            <div class="form-group">
              <label>Subject <span class="text-danger">*</span></label>
              <select class="form-control" name="subject_group_subject_id" id="cell_subject">
                <option value="">-- Select Subject --</option>
              </select>
              <input type="hidden" name="subject_group_id" id="cell_sgroup_id">
            </div>
            <div class="form-group">
              <label>Teacher</label>
              <select class="form-control" name="staff_id" id="cell_staff">
                <option value="">-- Select Teacher --</option>
              </select>
            </div>
            <div class="form-group">
              <label>Room</label>
              <select class="form-control" name="room_id" id="cell_room">
                <option value="">-- No Room --</option>
              </select>
            </div>
            <div class="form-group">
              <label>Batch</label>
              <select class="form-control" name="batch_id" id="cell_batch">
                <option value="">Full Class</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label><input type="checkbox" id="chk_free" name="is_free_period" value="1"> Free Period (PT / Library / Assembly)</label>
          </div>
          <div id="free-period-fields" style="display:none;">
            <div class="form-group">
              <label>Label</label>
              <input type="text" class="form-control" name="free_period_label" id="cell_free_label" placeholder="PT, Library, Free, Assembly">
            </div>
          </div>

          <div id="conflict-msg" class="alert alert-danger" style="display:none;"></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="btn-delete-cell" style="display:none;"><i class="fa fa-trash"></i> Remove</button>
        <button type="button" class="btn btn-warning" id="btn-toggle-lock" style="display:none;"></button>
        <button type="button" class="btn btn-primary" id="btn-save-cell"><i class="fa fa-save"></i> Save</button>
      </div>
    </div>
  </div>
</div>

</section>

<style>
.tt-grid th { text-align:center; background:#3c8dbc; color:#fff; white-space:nowrap; padding:8px 4px; }
.tt-grid .time-col { width:80px; min-width:80px; background:#f4f4f4; font-size:11px; text-align:center; vertical-align:middle; }
.tt-cell { min-height:60px; vertical-align:middle; text-align:center; cursor:pointer; padding:4px; transition:background .2s; }
.tt-cell:hover { background:#e8f4fd !important; }
.tt-cell.filled { background:#eaf7ea; }
.tt-cell.break-row { background:#fffde7 !important; cursor:default; }
.tt-cell.locked-cell { border-left: 3px solid #e74c3c !important; }
.slot-tag { display:inline-block; border-radius:4px; padding:2px 6px; font-size:11px; font-weight:600; color:#fff; margin:1px; }
.slot-theory   { background:#3498db; }
.slot-practical{ background:#e74c3c; }
.slot-project  { background:#f39c12; }
.slot-free     { background:#27ae60; }
.slot-other    { background:#7f8c8d; }
</style>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';
  var current_subjects = [], current_staff = [], current_rooms = [], current_batches = [];

  $('#cg_dept').select2({ placeholder: '-- All --', allowClear: true, width: '100%', minimumResultsForSearch: 1 });
  $('#cg_class').select2({ placeholder: '-- Select Class --', allowClear: true, width: '100%', minimumResultsForSearch: 1 });
  $('#cg_section').select2({ placeholder: '-- Select Section --', allowClear: true, width: '100%' });

  // Store original class options for dept filtering
  var allCgClassOpts = [];
  $('#cg_class option').each(function(){
    if ($(this).val()) allCgClassOpts.push({val:$(this).val(), text:$(this).text(), dept:$(this).data('dept')});
  });

  $('#cg_dept').on('change', function(){
    var dept = $(this).val();
    var opts = '<option value="">-- Select Class --</option>';
    $.each(allCgClassOpts, function(i,o){
      if (!dept || o.dept == dept) opts += '<option value="'+o.val+'" data-dept="'+o.dept+'">'+o.text+'</option>';
    });
    $('#cg_class').html(opts).trigger('change.select2');
    $('#cg_section').html('<option value="">-- Select Section --</option>').trigger('change.select2');
  });

  // Load sections
  $('#cg_class').on('change', function(){
    var id = $(this).val();
    $('#cg_section').html('<option value="">Loading...</option>');
    if (!id) return;
    $.post('<?php echo site_url('admin/tt/get_sections_by_class'); ?>',
      {class_id: id, [csrf_name]: csrf_val}, function(res){
        var opts = '<option value="">-- Select Section --</option>';
        $.each(res, function(i,s){ opts += '<option value="'+s.section_id+'">'+s.section+'</option>'; });
        $('#cg_section').html(opts);
      },'json');
  });

  var loaded_class_id = 0, loaded_section_id = 0;
  var schoolName = '<?php echo addslashes($this->sch_setting_detail->name ?? ''); ?>';
  var exportDT = null;
  var weekOffset = 0;

  function weekLabel(offset) {
    if (offset === 0) return 'Current Week';
    return offset > 0 ? ('Next Week +' + offset) : ('Prev Week ' + offset);
  }

  function initExportDataTable(flat_rows, flat_cols, cls_label) {
    // Destroy previous instance if any
    if (exportDT) { exportDT.destroy(); exportDT = null; }

    // Build hidden flat table HTML
    var thead = '<thead><tr>' + $.map(flat_cols, function(c){ return '<th>' + c + '</th>'; }).join('') + '</tr></thead>';
    var tbody = '<tbody>' + $.map(flat_rows, function(row){
      return '<tr>' + $.map(row, function(c){ return '<td>' + c + '</td>'; }).join('') + '</tr>';
    }).join('') + '</tbody>';
    $('#export-flat-table').html(thead + tbody);

    var reportTitle = schoolName + (schoolName ? ' | ' : '') + cls_label + ' — Class Timetable';

    exportDT = $('#export-flat-table').DataTable({
      dom: 'B',
      paging: false, searching: false, ordering: false, info: false,
      buttons: [
        {
          extend: 'excelHtml5',
          title: reportTitle,
          exportOptions: {
            format: { body: function(data){ return data.replace(/(<([^>]+)>)/ig,'').trim(); } },
            stripHtml: true
          }
        },
        {
          extend: 'pdfHtml5',
          orientation: 'landscape',
          title: '',
          customize: function(doc) {
            doc.content.splice(0, 0,
              { text: schoolName,  style: 'dtHeader' },
              { text: cls_label + ' — Class Timetable', style: 'dtSubHeader' },
              { text: ' ' }
            );
            doc.styles.dtHeader    = { fontSize: 14, bold: true, alignment: 'center' };
            doc.styles.dtSubHeader = { fontSize: 11, alignment: 'center' };
          },
          exportOptions: {
            format: { body: function(data){ return data.replace(/(<([^>]+)>)/ig,'').trim(); } },
            stripHtml: true
          }
        }
      ]
    });
  }

  function loadGrid(resetWeek) {
    var class_id = $('#cg_class').val(), section_id = $('#cg_section').val();
    if (!class_id || !section_id) { alert('Please select Class and Section.'); return; }
    if (resetWeek) weekOffset = 0;
    var $btn = $('#btn-load-grid').prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
    $.post('<?php echo site_url('admin/tt/load_class_grid'); ?>',
      {class_id: class_id, section_id: section_id, week_offset: weekOffset, [csrf_name]: csrf_val}, function(res){
        $btn.prop('disabled',false).html('<i class="fa fa-table"></i> Load');
        if (res.status === '1') {
          $('#grid-placeholder').hide();
          $('#timetable-grid-container').html(res.html);
          current_subjects  = res.subjects || [];
          current_staff     = res.staff    || [];
          current_rooms     = res.rooms    || [];
          current_batches   = res.batches  || [];
          loaded_class_id   = class_id;
          loaded_section_id = section_id;
          initExportDataTable(res.flat_rows || [], res.flat_cols || [], res.cls_label || '');
          $('#export-btns').show();
          $('#week-nav-row').show();
          $('#week-label').text(weekLabel(weekOffset));
          $('#timetable-grid-container [data-toggle="tooltip"]').tooltip();
        }
      },'json');
  }

  // Load grid
  $('#btn-load-grid').on('click', function(){ loadGrid(true); });

  $('#btn-week-prev').on('click', function(){ weekOffset--; loadGrid(false); });
  $('#btn-week-cur').on('click',  function(){ weekOffset = 0; loadGrid(false); });
  $('#btn-week-next').on('click', function(){ weekOffset++; loadGrid(false); });

  // Print: server-side page with header, auto-triggers window.print()
  $('#btn-print-grid').on('click', function(){
    if (!loaded_class_id) return;
    window.open('<?php echo site_url('admin/tt/print_class_grid'); ?>?class_id=' + loaded_class_id + '&section_id=' + loaded_section_id, '_blank');
  });

  // Excel: trigger DataTables excelHtml5 button
  $('#btn-excel-grid').on('click', function(){
    if (exportDT) exportDT.button(0).trigger();
  });

  // PDF: trigger DataTables pdfHtml5 button
  $('#btn-pdf-grid').on('click', function(){
    if (exportDT) exportDT.button(1).trigger();
  });

  // Open cell modal
  $(document).on('click', '.tt-cell:not(.break-row)', function(){
    var $cell = $(this);
    var day      = $cell.data('day');
    var period   = $cell.data('period');
    var class_id = $cell.closest('[data-class]').data('class');
    var section_id=$cell.closest('[data-section]').data('section');
    var entry_id = $cell.data('entry-id') || 0;
    var locked   = $cell.data('locked') || 0;

    $('#cell_id').val(entry_id);
    $('#cell_class_id').val(class_id);
    $('#cell_section_id').val(section_id);
    $('#cell_day').val(day);
    $('#cell_period_id').val(period);
    $('#conflict-msg').hide();
    $('#chk_free').prop('checked', false);
    $('#free-period-fields').hide();
    $('#regular-fields').show();
    $('#modal-title-text').text(day + ' — ' + $cell.data('period-name'));

    // Populate dropdowns
    var subOpts = '<option value="">-- Select Subject --</option>';
    $.each(current_subjects, function(i,s){
      subOpts += '<option value="'+s.subject_group_subject_id+'" data-sgid="'+s.subject_group_id+'">'+s.subject_name+' ('+s.subject_code+')</option>';
    });
    $('#cell_subject').html(subOpts);

    var staffOpts = '<option value="">-- No Teacher --</option>';
    $.each(current_staff, function(i,s){ staffOpts += '<option value="'+s.id+'">'+s.name+' '+s.surname+'</option>'; });
    $('#cell_staff').html(staffOpts);

    var roomOpts = '<option value="">-- No Room --</option>';
    $.each(current_rooms, function(i,r){ roomOpts += '<option value="'+r.id+'">'+r.name+' ('+r.room_type+')</option>'; });
    $('#cell_room').html(roomOpts);

    var batchOpts = '<option value="">Full Class</option>';
    $.each(current_batches, function(i,b){ batchOpts += '<option value="'+b.id+'">Batch '+b.batch_name+'</option>'; });
    $('#cell_batch').html(batchOpts);

    // Pre-fill if existing entry
    if (entry_id > 0) {
      var e_data = $cell.data('entry') || {};
      $('#cell_subject').val(e_data.sgs_id || '');
      $('#cell_sgroup_id').val(e_data.sg_id || '');
      $('#cell_staff').val(e_data.staff_id || '');
      $('#cell_room').val(e_data.room_id || '');
      $('#cell_batch').val(e_data.batch_id || '');
      if (e_data.is_free) {
        $('#chk_free').prop('checked', true);
        $('#free-period-fields').show();
        $('#regular-fields').hide();
        $('#cell_free_label').val(e_data.free_label || '');
      }
      $('#btn-delete-cell').show();
      $('#btn-toggle-lock').show().removeClass('btn-warning btn-default')
        .addClass(locked ? 'btn-warning' : 'btn-default')
        .html(locked ? '<i class="fa fa-unlock"></i> Unlock' : '<i class="fa fa-lock"></i> Lock');
      $('#btn-toggle-lock').data('locked', locked).data('entry-id', entry_id);
    } else {
      $('#btn-delete-cell, #btn-toggle-lock').hide();
    }

    $('#cell-modal').modal('show');
  });

  // Subject change → auto-fill subject_group_id
  $('#cell_subject').on('change', function(){
    $('#cell_sgroup_id').val($(this).find(':selected').data('sgid') || '');
  });

  // Free period toggle
  $('#chk_free').on('change', function(){
    if (this.checked) {
      $('#regular-fields').hide();
      $('#free-period-fields').show();
    } else {
      $('#regular-fields').show();
      $('#free-period-fields').hide();
    }
  });

  // Save cell
  $('#btn-save-cell').on('click', function(){
    $('#conflict-msg').hide();
    var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/save_cell'); ?>',
      $('#cell-form').serialize() + '&' + csrf_name + '=' + csrf_val,
      function(res){
        $btn.prop('disabled',false).html('<i class="fa fa-save"></i> Save');
        if (res.status === '1') {
          $('#cell-modal').modal('hide');
          loadGrid(false);
        } else {
          $('#conflict-msg').text(res.message || 'Error saving.').show();
        }
      },'json');
  });

  // Delete cell
  $('#btn-delete-cell').on('click', function(){
    if (!confirm('Remove this slot?')) return;
    var id = $('#cell_id').val();
    $.post('<?php echo site_url('admin/tt/delete_cell/'); ?>'+id, {[csrf_name]: csrf_val}, function(res){
      if (res.status === '1') { $('#cell-modal').modal('hide'); loadGrid(false); }
    },'json');
  });

  // Lock/unlock
  $('#btn-toggle-lock').on('click', function(){
    var $btn = $(this);
    var id = $btn.data('entry-id');
    var locked = $btn.data('locked') == 1 ? 0 : 1;
    $.post('<?php echo site_url('admin/tt/toggle_lock'); ?>',
      {id: id, locked: locked, [csrf_name]: csrf_val}, function(res){
        if (res.status === '1') { $('#cell-modal').modal('hide'); loadGrid(false); }
      },'json');
  });
});
</script>
</div>
