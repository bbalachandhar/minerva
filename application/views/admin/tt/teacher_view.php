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
      <div class="col-md-3">
        <label>&nbsp;</label>
        <button class="btn btn-primary btn-block" id="btn-load-teacher-grid"><i class="fa fa-search"></i> Load</button>
      </div>
      <div class="col-md-5 text-right">
        <label>&nbsp;</label>
        <button class="btn btn-default btn-block" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
      </div>
    </div>
  </div>
</div>

<div id="teacher-grid-container">
  <div class="text-center text-muted p-5" id="tv-placeholder">
    <i class="fa fa-arrow-up fa-2x"></i><br>Select a teacher to view their timetable.
  </div>
</div>
</section>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';

  $('#btn-load-teacher-grid').on('click', function(){
    var staff_id = $('#tv_staff').val();
    if (!staff_id) { alert('Please select a teacher.'); return; }
    var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/load_teacher_grid'); ?>',
      {staff_id: staff_id, [csrf_name]: csrf_val}, function(res){
        $btn.prop('disabled',false).html('<i class="fa fa-search"></i> Load');
        if (res.status === '1') {
          $('#tv-placeholder').hide();
          $('#teacher-grid-container').html(res.html);
        }
      },'json');
  });
});
</script>
