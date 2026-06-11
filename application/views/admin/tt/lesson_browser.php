<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Lesson Browser <small>All subject loads — filter by department, teacher, or subject</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Lesson Browser</li>
    </ol>
</section>
<section class="content">

<div class="box box-primary">
  <div class="box-body">
    <div class="row" style="align-items:flex-end;">
      <div class="col-md-3">
        <label>Department</label>
        <select class="form-control" id="lb_dept">
          <option value="">-- All Departments --</option>
          <?php foreach ($departments as $d): ?><option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['department_name']); ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label>Teacher</label>
        <select class="form-control" id="lb_staff">
          <option value="">-- All Teachers --</option>
          <?php foreach ($staff_list as $st): ?><option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['name'].' '.$st['surname']); ?> (<?php echo $st['employee_id']; ?>)</option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label>Subject</label>
        <select class="form-control" id="lb_subject">
          <option value="">-- All Subjects --</option>
        </select>
      </div>
      <div class="col-md-3">
        <label>&nbsp;</label>
        <button class="btn btn-primary btn-block" id="btn-lb-load"><i class="fa fa-search"></i> Load</button>
      </div>
    </div>
  </div>
</div>

<div class="box box-default" id="lb-result-box" style="display:none;">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-list"></i> Lessons <span id="lb-count-badge" class="label label-default" style="font-size:12px;margin-left:6px;vertical-align:middle;"></span></h3>
    <div class="box-tools pull-right">
      <button class="btn btn-xs btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </div>
  </div>
  <div class="box-body p-0">
    <div id="lb-rows"></div>
  </div>
</div>

</section>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';

  $('#lb_dept').select2({ placeholder: '-- All Departments --', allowClear: true, width: '100%', minimumResultsForSearch: 1 });
  $('#lb_staff').select2({ placeholder: '-- All Teachers --', allowClear: true, width: '100%', minimumResultsForSearch: 1 });
  $('#lb_subject').select2({ placeholder: '-- All Subjects --', allowClear: true, width: '100%', minimumResultsForSearch: 1 });

  // Load subjects list
  $.get('<?php echo site_url('admin/tt/get_all_subjects'); ?>', function(res){
    var opts = '<option value="">-- All Subjects --</option>';
    $.each(res, function(i,s){ opts += '<option value="'+s.id+'">'+s.name+' ('+s.code+')</option>'; });
    $('#lb_subject').html(opts).trigger('change.select2');
  },'json');

  $('#btn-lb-load').on('click', function(){
    var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/get_lesson_browser_data'); ?>',
      {dept_id: $('#lb_dept').val(), staff_id: $('#lb_staff').val(), subject_id: $('#lb_subject').val(),
       [csrf_name]: csrf_val},
      function(res){
        $btn.prop('disabled',false).html('<i class="fa fa-search"></i> Load');
        if (res.status === '1') {
          $('#lb-rows').html(res.html);
          if (res.count > 0) {
            $('#lb-count-badge').text(res.count + ' lessons').removeClass('label-warning').addClass('label-primary');
          } else {
            $('#lb-count-badge').text('No data').removeClass('label-primary').addClass('label-warning');
          }
          $('#lb-result-box').show();
        }
      },'json');
  });

  // Auto-load on page open
  $('#btn-lb-load').trigger('click');
});
</script>
</div>
