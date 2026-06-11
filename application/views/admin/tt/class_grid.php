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
          <?php foreach ($classlist as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['class']); ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label>Section <span class="text-danger">*</span></label>
        <select class="form-control" id="cg_section"><option value="">-- Select Section --</option></select>
      </div>
      <div class="col-md-3">
        <div class="row" style="margin-top:25px;">
          <div class="col-xs-8 pr-0"><button class="btn btn-primary btn-block" id="btn-load-grid"><i class="fa fa-table"></i> Load Timetable</button></div>
          <div class="col-xs-4 pl-1"><button class="btn btn-default btn-block" id="btn-print-grid" title="Print" disabled><i class="fa fa-print"></i></button></div>
        </div>
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

  $('#cg_dept').select2({ placeholder: '-- All --', allowClear: true, width: '100%' });
  $('#cg_class').select2({ placeholder: '-- Select Class --', allowClear: true, width: '100%' });
  $('#cg_section').select2({ placeholder: '-- Select Section --', allowClear: true, width: '100%' });

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

  // Load grid
  $('#btn-load-grid').on('click', function(){
    var class_id = $('#cg_class').val(), section_id = $('#cg_section').val();
    if (!class_id || !section_id) { alert('Please select Class and Section.'); return; }
    var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
    $.post('<?php echo site_url('admin/tt/load_class_grid'); ?>',
      {class_id: class_id, section_id: section_id, [csrf_name]: csrf_val}, function(res){
        $btn.prop('disabled',false).html('<i class="fa fa-table"></i> Load Timetable');
        if (res.status === '1') {
          $('#grid-placeholder').hide();
          $('#timetable-grid-container').html(res.html);
          current_subjects = res.subjects || [];
          current_staff    = res.staff    || [];
          current_rooms    = res.rooms    || [];
          current_batches  = res.batches  || [];
          $('#btn-print-grid').prop('disabled', false);
        }
      },'json');
  });

  // Print timetable
  $('#btn-print-grid').on('click', function(){
    var printContent = '<html><head><title>Class Timetable</title>'
      + '<link rel="stylesheet" href="<?php echo base_url('assets/bower_components/bootstrap/dist/css/bootstrap.min.css'); ?>">'
      + '<style>body{padding:20px;font-size:12px;}.tt-grid th,.tt-grid td{padding:4px 6px;border:1px solid #ccc;white-space:nowrap;}'
      + '.slot-tag{display:inline-block;border-radius:3px;padding:1px 5px;font-size:11px;font-weight:600;color:#fff;margin:1px;}'
      + '.slot-theory{background:#3498db;}.slot-practical{background:#e74c3c;}.slot-project{background:#f39c12;}.slot-free{background:#27ae60;}.slot-other{background:#7f8c8d;}'
      + '.break-row{background:#fffde7 !important;}'
      + '@media print{.no-print{display:none}}</style></head><body>'
      + $('#timetable-grid-container').html()
      + '</body></html>';
    var w = window.open('','_blank');
    w.document.write(printContent);
    w.document.close();
    w.focus();
    w.print();
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
          $('#btn-load-grid').trigger('click');
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
      if (res.status === '1') { $('#cell-modal').modal('hide'); $('#btn-load-grid').trigger('click'); }
    },'json');
  });

  // Lock/unlock
  $('#btn-toggle-lock').on('click', function(){
    var $btn = $(this);
    var id = $btn.data('entry-id');
    var locked = $btn.data('locked') == 1 ? 0 : 1;
    $.post('<?php echo site_url('admin/tt/toggle_lock'); ?>',
      {id: id, locked: locked, [csrf_name]: csrf_val}, function(res){
        if (res.status === '1') { $('#cell-modal').modal('hide'); $('#btn-load-grid').trigger('click'); }
      },'json');
  });
});
</script>
</div>
