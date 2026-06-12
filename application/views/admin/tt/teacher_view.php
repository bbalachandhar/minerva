<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Teacher Timetable <small>View teacher-wise weekly schedule</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Teacher Timetable</li>
    </ol>
</section>
<section class="content">
<div class="box box-primary">
  <div class="box-body">
    <div class="row">
      <div class="col-md-4">
        <label>Teacher</label>
        <select class="form-control" id="tv_staff">
          <option value="">-- Select Teacher --</option>
          <?php foreach ($staff_list as $st): ?>
          <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['name'].' '.$st['surname']); ?> (<?php echo $st['employee_id']; ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label>&nbsp;</label>
        <div class="input-group">
          <span class="input-group-btn"><button class="btn btn-default" id="btn-prev-teacher" title="Previous Teacher" disabled><i class="fa fa-chevron-left"></i></button></span>
          <button class="btn btn-primary btn-block" id="btn-load-teacher-grid" style="border-radius:0;"><i class="fa fa-search"></i> Load</button>
          <span class="input-group-btn"><button class="btn btn-default" id="btn-next-teacher" title="Next Teacher" disabled><i class="fa fa-chevron-right"></i></button></span>
        </div>
      </div>
      <div class="col-md-4 text-right">
        <label>&nbsp;</label>
        <button class="btn btn-default btn-block" id="btn-print-teacher"><i class="fa fa-print"></i> Print</button>
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
  <span class="label label-default" style="font-size:12px;padding:5px 10px;">Other</span>
</div>

<div id="teacher-grid-container">
  <div class="text-center text-muted p-5" id="tv-placeholder">
    <i class="fa fa-arrow-up fa-2x"></i><br>Select a teacher to view their timetable.
  </div>
</div>
</section>

<style>
.tt-grid th { text-align:center; background:#3c8dbc; color:#fff; white-space:nowrap; padding:8px 4px; }
.tt-grid .time-col { width:80px; min-width:80px; background:#f4f4f4; font-size:11px; text-align:center; vertical-align:middle; }
.tt-cell { min-height:60px; vertical-align:middle; text-align:center; padding:4px; }
.tt-cell.filled { background:#eaf7ea; }
.tt-cell.break-row { background:#fffde7 !important; }
.slot-tag { display:inline-block; border-radius:4px; padding:2px 6px; font-size:11px; font-weight:600; color:#fff; margin:1px; }
.slot-theory    { background:#3498db; }
.slot-practical { background:#e74c3c; }
.slot-project   { background:#f39c12; }
.slot-free      { background:#27ae60; }
.slot-other     { background:#7f8c8d; }
</style>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';
  var lastStaffName = '';

  $('#tv_staff').select2({ placeholder: '-- Select Teacher --', allowClear: true, width: '100%' });

  function updateNavButtons(){
    var $opts = $('#tv_staff option[value!=""]');
    var idx   = $opts.index($('#tv_staff option:selected'));
    $('#btn-prev-teacher').prop('disabled', idx <= 0);
    $('#btn-next-teacher').prop('disabled', idx < 0 || idx >= $opts.length - 1);
  }

  function loadTeacher(){
    var staff_id = $('#tv_staff').val();
    if (!staff_id) { alert('Please select a teacher.'); return; }
    lastStaffName = $('#tv_staff option:selected').text().trim();
    var $btn = $('#btn-load-teacher-grid').prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/load_teacher_grid'); ?>',
      {staff_id: staff_id, [csrf_name]: csrf_val}, function(res){
        $btn.prop('disabled',false).html('<i class="fa fa-search"></i> Load');
        if (res.status === '1') {
          $('#tv-placeholder').hide();
          $('#teacher-grid-container').html(res.html);
          updateNavButtons();
        }
      },'json');
  }

  $('#btn-load-teacher-grid').on('click', loadTeacher);

  $('#btn-prev-teacher').on('click', function(){
    var $opts = $('#tv_staff option[value!=""]');
    var idx   = $opts.index($('#tv_staff option:selected'));
    if (idx > 0) { $('#tv_staff').val($opts.eq(idx-1).val()).trigger('change'); loadTeacher(); }
  });

  $('#btn-next-teacher').on('click', function(){
    var $opts = $('#tv_staff option[value!=""]');
    var idx   = $opts.index($('#tv_staff option:selected'));
    if (idx < $opts.length - 1) { $('#tv_staff').val($opts.eq(idx+1).val()).trigger('change'); loadTeacher(); }
  });

  $('#btn-print-teacher').on('click', function(){
    var html = $('#teacher-grid-container').html();
    if (!html || !html.trim() || html.indexOf('fa-arrow-up') !== -1) { alert('Please load a timetable first.'); return; }
    var w = window.open('','_blank');
    w.document.write('<html><head><title>'+lastStaffName+' — Timetable</title>'
      +'<link rel="stylesheet" href="<?php echo base_url('assets/bower_components/bootstrap/dist/css/bootstrap.min.css'); ?>">'
      +'<style>body{padding:20px;font-size:12px;}'
      +'.tt-grid th,.tt-grid td{padding:4px 6px;border:1px solid #ccc;white-space:nowrap;}'
      +'.tt-grid th{background:#3c8dbc;color:#fff;text-align:center;}'
      +'.time-col{width:80px;background:#f4f4f4;text-align:center;font-size:11px;}'
      +'.slot-tag{display:inline-block;border-radius:3px;padding:1px 5px;font-size:11px;font-weight:600;color:#fff;margin:1px;}'
      +'.slot-theory{background:#3498db;}.slot-practical{background:#e74c3c;}.slot-project{background:#f39c12;}.slot-free{background:#27ae60;}.slot-other{background:#7f8c8d;}'
      +'.break-row{background:#fffde7 !important;}.box-tools,.box-header .btn{display:none}'
      +'</style></head><body>'
      +'<h4>'+lastStaffName+' — Weekly Timetable</h4>'
      +html+'</body></html>');
    w.document.close();
    w.focus();
    w.print();
  });
});
</script>
</div>
