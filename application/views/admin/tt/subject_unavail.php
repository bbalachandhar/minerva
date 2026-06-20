<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Subject Time Off <small>Block time slots where a subject must never be scheduled</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Subject Time Off</li>
    </ol>
</section>
<section class="content">
<div class="row">
  <div class="col-md-3">
    <div class="box box-primary">
      <div class="box-header"><h3 class="box-title"><i class="fa fa-book"></i> Select Subject</h3></div>
      <div class="box-body">
        <div class="form-group">
          <label>Subject</label>
          <select class="form-control" id="su_subject_id" style="width:100%;">
            <option value="">-- Select Subject --</option>
            <?php
            $type_badge = ['theory'=>'label-primary','practical'=>'label-danger','project'=>'label-warning','other'=>'label-default'];
            foreach ($subjects as $sub): ?>
            <option value="<?php echo $sub->id; ?>">[<?php echo htmlspecialchars($sub->code); ?>] <?php echo htmlspecialchars($sub->name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button class="btn btn-primary btn-block" id="btn-load-su"><i class="fa fa-search"></i> Load</button>
      </div>
    </div>
    <div class="callout callout-warning" style="font-size:12px;">
      <i class="fa fa-info-circle"></i> Blocked slots mean this subject can <strong>never</strong> be scheduled in those periods — for any class. Example: "No Math in the last period of Friday."
    </div>
  </div>

  <div class="col-md-9">
    <div class="box box-default" id="su-box" style="display:none;">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-calendar"></i> Time Off Grid — <span id="su-subject-name"></span></h3>
        <div class="box-tools">
          <button class="btn btn-success btn-sm" id="btn-save-su"><i class="fa fa-save"></i> Save</button>
          <button class="btn btn-default btn-sm" id="btn-clear-su"><i class="fa fa-times"></i> Clear All</button>
        </div>
      </div>
      <div class="box-body p-2">
        <p class="text-muted" style="font-size:12px;"><i class="fa fa-check-square-o text-success"></i> = Allowed &nbsp;&nbsp; <i class="fa fa-times-circle text-danger"></i> = <strong>Blocked</strong> — Click to toggle</p>
        <div class="table-responsive">
          <table class="table table-bordered text-center" id="su-grid" style="font-size:13px;">
            <thead>
              <tr style="background:#3c8dbc;color:#fff;">
                <th width="120">Period</th>
                <?php foreach ($days as $day_key => $day_val): ?>
                <th><?php echo $day_key; ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($periods as $period): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($period->name); ?></strong><br>
                  <small><?php echo date('h:i A', strtotime($period->start_time)); ?></small></td>
                <?php foreach ($days as $day_key => $day_val): ?>
                <td class="su-cell" data-day="<?php echo $day_key; ?>" data-period="<?php echo $period->id; ?>" data-blocked="0"
                    style="cursor:pointer;vertical-align:middle;" title="Click to toggle">
                  <i class="fa fa-check-square-o text-success fa-lg"></i>
                </td>
                <?php endforeach; ?>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div id="su-placeholder" class="text-center text-muted p-5">
      <i class="fa fa-arrow-left fa-2x"></i><br>Select a subject to configure its time-off slots.
    </div>
  </div>
</div>
</section>

<style>
.su-cell.blocked { background: #ffdddd !important; }
.su-cell:hover   { background: #f0f8ff; }
</style>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';
  var current_subject_id = null;

  $('#su_subject_id').select2({ placeholder: '-- Select Subject --', allowClear: true });

  $('#btn-load-su').on('click', function(){
    var subject_id = $('#su_subject_id').val();
    if (!subject_id) { swal({title:'Alert',text:'Please select a subject.',type:'warning'}); return; }
    current_subject_id = subject_id;
    $('#su-subject-name').text($('#su_subject_id option:selected').text());

    $.post('<?php echo site_url('admin/tt/get_subject_unavail'); ?>',
      {subject_id: subject_id, [csrf_name]: csrf_val},
      function(res){
        $('.su-cell').each(function(){
          $(this).data('blocked',0).removeClass('blocked').html('<i class="fa fa-check-square-o text-success fa-lg"></i>');
        });
        if (res.status === '1') {
          toastr.success('Unavailability loaded');
          $.each(res.map, function(key){
            var parts = key.split('_');
            var day = parts.slice(0, -1).join('_'), period = parts[parts.length-1];
            var $c = $('.su-cell[data-day="'+day+'"][data-period="'+period+'"]');
            $c.data('blocked',1).addClass('blocked').html('<i class="fa fa-times-circle text-danger fa-lg"></i>');
          });
        }
        $('#su-placeholder').hide();
        $('#su-box').show();
      },'json');
  });

  $(document).on('click', '.su-cell', function(){
    var blocked = $(this).data('blocked');
    if (blocked) {
      $(this).data('blocked',0).removeClass('blocked').html('<i class="fa fa-check-square-o text-success fa-lg"></i>');
    } else {
      $(this).data('blocked',1).addClass('blocked').html('<i class="fa fa-times-circle text-danger fa-lg"></i>');
    }
  });

  $('#btn-clear-su').on('click', function(){
    $('.su-cell').data('blocked',0).removeClass('blocked').html('<i class="fa fa-check-square-o text-success fa-lg"></i>');
  });

  $('#btn-save-su').on('click', function(){
    if (!current_subject_id) return;
    var slots = [];
    $('.su-cell').filter(function(){ return $(this).data('blocked') == 1; }).each(function(){
      slots.push({day: $(this).data('day'), period_id: $(this).data('period'), reason: ''});
    });
    var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/save_subject_unavail'); ?>',
      {subject_id: current_subject_id, slots: slots, [csrf_name]: csrf_val},
      function(res){
        $btn.prop('disabled',false).html('<i class="fa fa-save"></i> Save');
        if (res.status === '1') {
          toastr.success('Unavailability saved');
          $btn.html('<i class="fa fa-check"></i> Saved!').addClass('btn-success');
          setTimeout(function(){ $btn.html('<i class="fa fa-save"></i> Save').removeClass('btn-success'); }, 2000);
        } else {
          swal({title:'Alert',text:'Error saving. Please try again.',type:'warning'});
        }
      },'json');
  });
});
</script>
</div>
