<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Timetable Reports</h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Reports</li>
    </ol>
</section>
<section class="content">

<div class="nav-tabs-custom">
  <ul class="nav nav-tabs">
    <li class="active"><a href="#tab-master" data-toggle="tab"><i class="fa fa-table"></i> Master Timetable</a></li>
    <li><a href="#tab-rooms" data-toggle="tab"><i class="fa fa-building"></i> Room Utilization</a></li>
    <li><a href="#tab-workload" data-toggle="tab"><i class="fa fa-bar-chart"></i> Teacher Workload</a></li>
    <li><a href="#tab-substitution" data-toggle="tab"><i class="fa fa-exchange"></i> Substitution Report</a></li>
  </ul>
  <div class="tab-content">

    <!-- MASTER TIMETABLE -->
    <div class="tab-pane active" id="tab-master">
      <div class="row" style="padding:10px 0;">
        <div class="col-md-4">
          <label>Filter by Department</label>
          <select class="form-control" id="report_dept">
            <option value="">All Departments</option>
            <?php foreach ($departments as $d): ?><option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['department_name']); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label>&nbsp;</label>
          <button class="btn btn-primary btn-block" id="btn-load-master"><i class="fa fa-search"></i> Load</button>
        </div>
        <div class="col-md-3">
          <label>&nbsp;</label>
          <button class="btn btn-default btn-block" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
      </div>
      <div id="master-report-container">
        <div class="text-center text-muted p-4"><i class="fa fa-arrow-up"></i> Click Load to view master timetable.</div>
      </div>
    </div>

    <!-- ROOM UTILIZATION -->
    <div class="tab-pane" id="tab-rooms">
      <div style="padding:10px 0;">
        <button class="btn btn-primary" id="btn-load-rooms"><i class="fa fa-search"></i> Load Room Utilization</button>
        <button class="btn btn-default" onclick="window.print()" style="margin-left:8px;"><i class="fa fa-print"></i> Print</button>
      </div>
      <div id="room-report-container">
        <div class="text-center text-muted p-4"><i class="fa fa-arrow-up"></i> Click Load to view room utilization.</div>
      </div>
    </div>

    <!-- TEACHER WORKLOAD -->
    <div class="tab-pane" id="tab-workload">
      <div style="padding:10px 0;">
        <button class="btn btn-primary" id="btn-load-workload"><i class="fa fa-bar-chart"></i> Load Teacher Workload</button>
      </div>
      <div id="workload-container">
        <div class="text-center text-muted p-4"><i class="fa fa-arrow-up"></i> Click Load to view teacher workload.</div>
      </div>
    </div>

    <!-- SUBSTITUTION REPORT -->
    <div class="tab-pane" id="tab-substitution">
      <div class="row" style="padding:10px 0;">
        <div class="col-md-3">
          <label>From</label>
          <div class="input-group date" id="sub_from_pick">
            <input type="text" class="form-control" id="sub_from" value="<?php echo date('Y-m-01'); ?>" placeholder="YYYY-MM-DD" readonly style="background:#fff;cursor:pointer;">
            <span class="input-group-addon" style="cursor:pointer;background:#f4f4f4;border-left:0;"><i class="fa fa-calendar" style="color:#3c8dbc;"></i></span>
          </div>
        </div>
        <div class="col-md-3">
          <label>To</label>
          <div class="input-group date" id="sub_to_pick">
            <input type="text" class="form-control" id="sub_to" value="<?php echo date('Y-m-d'); ?>" placeholder="YYYY-MM-DD" readonly style="background:#fff;cursor:pointer;">
            <span class="input-group-addon" style="cursor:pointer;background:#f4f4f4;border-left:0;"><i class="fa fa-calendar" style="color:#3c8dbc;"></i></span>
          </div>
        </div>
        <div class="col-md-3">
          <label>Teacher (optional)</label>
          <select class="form-control" id="sub_staff" style="width:100%;">
            <option value="">All Teachers</option>
            <?php foreach ($staff_list as $st): ?><option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['name'].' '.$st['surname']); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label>&nbsp;</label>
          <button class="btn btn-primary btn-block" id="btn-load-sub-report"><i class="fa fa-search"></i> Load</button>
        </div>
      </div>
      <div id="sub-report-container">
        <div class="text-center text-muted p-4">Select date range and click Load.</div>
      </div>
    </div>

  </div>
</div>
</section>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';

  $('#sub_staff').select2({ placeholder: 'All Teachers', allowClear: true, width: '100%' });

  var dpIcons = { time:'fa fa-clock-o', date:'fa fa-calendar', up:'fa fa-chevron-up', down:'fa fa-chevron-down', previous:'fa fa-chevron-left', next:'fa fa-chevron-right', today:'fa fa-crosshairs', clear:'fa fa-trash', close:'fa fa-times' };
  $('#sub_from_pick').datetimepicker({ format:'YYYY-MM-DD', icons: dpIcons });
  $('#sub_to_pick').datetimepicker({ format:'YYYY-MM-DD', icons: dpIcons });
  $('#sub_from_pick').on('dp.change', function(e){ $('#sub_to_pick').data('DateTimePicker').minDate(e.date); });
  $('#sub_to_pick').on('dp.change',   function(e){ $('#sub_from_pick').data('DateTimePicker').maxDate(e.date); });

  $('#btn-load-master').on('click', function(){
    var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/get_master_report'); ?>',
      {[csrf_name]: csrf_val, dept_id: $('#report_dept').val()}, function(res){
        $btn.prop('disabled',false).html('<i class="fa fa-search"></i> Load');
        $('#master-report-container').html(res.status==='1' ? res.html : '<div class="alert alert-warning">No data found.</div>');
      },'json');
  });

  $('#btn-load-rooms').on('click', function(){
    var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/get_room_utilization'); ?>',
      {[csrf_name]: csrf_val}, function(res){
        $btn.prop('disabled',false).html('<i class="fa fa-search"></i> Load Room Utilization');
        $('#room-report-container').html(res.status==='1' ? res.html : '<div class="alert alert-warning">No data found.</div>');
      },'json');
  });

  $('#btn-load-workload').on('click', function(){
    var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/get_teacher_workload'); ?>',
      {[csrf_name]: csrf_val}, function(res){
        $btn.prop('disabled',false).html('<i class="fa fa-bar-chart"></i> Load Teacher Workload');
        $('#workload-container').html(res.status==='1' ? res.html : '<div class="alert alert-warning">No data found.</div>');
      },'json');
  });

  $('#btn-load-sub-report').on('click', function(){
    var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/get_substitution_report'); ?>',
      {from_date: $('#sub_from').val(), to_date: $('#sub_to').val(), staff_id: $('#sub_staff').val(), [csrf_name]: csrf_val},
      function(res){
        $btn.prop('disabled',false).html('<i class="fa fa-search"></i> Load');
        if (res.status === '1' && res.data.length > 0) {
          var html = '<table class="table table-bordered table-hover table-sm" style="font-size:12px;"><thead><tr style="background:#3c8dbc;color:#fff;"><th>Date</th><th>Absent</th><th>Period</th><th>Class</th><th>Subject</th><th>Substitute</th><th>Type</th></tr></thead><tbody>';
          $.each(res.data, function(i, r){
            html += '<tr><td>'+r.date+'</td><td>'+r.absent_name+' '+r.absent_surname+'</td><td>'+(r.period_name||'')+'</td><td>'+(r['class']||'')+' '+(r.section||'')+'</td><td>'+(r.subject_name||'')+'</td><td>'+(r.sub_name ? r.sub_name+' '+r.sub_surname : '<em>Unassigned</em>')+'</td><td>'+r.substitution_type+'</td></tr>';
          });
          html += '</tbody></table>';
          $('#sub-report-container').html(html);
        } else {
          $('#sub-report-container').html('<div class="alert alert-warning">No substitutions found for selected criteria.</div>');
        }
      },'json');
  });
});
</script>
</div>
