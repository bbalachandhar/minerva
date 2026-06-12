<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Teacher Availability <small>Mark slots where a teacher is permanently unavailable</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Teacher Availability</li>
    </ol>
</section>
<section class="content">
<div class="row">
  <div class="col-md-3">
    <div class="box box-primary">
      <div class="box-header"><h3 class="box-title"><i class="fa fa-user"></i> Select Teacher</h3></div>
      <div class="box-body">
        <div class="form-group">
          <label>Teacher</label>
          <select class="form-control" id="avail_staff_id">
            <option value="">-- Select Teacher --</option>
            <?php foreach ($staff_list as $st): ?>
            <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['name'].' '.$st['surname']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button class="btn btn-primary btn-block" id="btn-load-avail"><i class="fa fa-search"></i> Load</button>
      </div>
    </div>
    <div class="callout callout-warning" style="font-size:12px;">
      <strong><i class="fa fa-info-circle"></i> Note:</strong> Marked slots mean the teacher is <strong>always unavailable</strong> for those periods (e.g., external commitments, PhD class). For day-specific absence, use the <a href="<?php echo site_url('admin/tt/substitution'); ?>">Substitution</a> screen.
    </div>
  </div>

  <div class="col-md-9">
    <div class="box box-default" id="avail-box" style="display:none;">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-calendar"></i> Availability Grid — <span id="avail-teacher-name"></span></h3>
        <div class="box-tools">
          <button class="btn btn-success btn-sm" id="btn-save-avail"><i class="fa fa-save"></i> Save</button>
        </div>
      </div>
      <div class="box-body p-2">
        <p class="text-muted" style="font-size:12px;"><i class="fa fa-check-square-o text-success"></i> = Available &nbsp;&nbsp; <i class="fa fa-times-circle text-danger"></i> = <strong>Unavailable (blocked)</strong> — Click to toggle</p>
        <div class="table-responsive">
          <table class="table table-bordered text-center" id="avail-grid" style="font-size:13px;">
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
                  <small><?php echo date('h:i A', strtotime($period->start_time)); ?></small>
                </td>
                <?php foreach ($days as $day_key => $day_val): ?>
                <td class="avail-cell" data-day="<?php echo $day_key; ?>" data-period="<?php echo $period->id; ?>" data-blocked="0"
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
    <div id="avail-placeholder" class="text-center text-muted p-5">
      <i class="fa fa-arrow-left fa-2x"></i><br>Select a teacher to configure availability.
    </div>
  </div>
</div>
</section>

<style>
.avail-cell.blocked { background: #ffdddd !important; }
.avail-cell:hover   { background: #f0f8ff; }
</style>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';
  var current_staff_id = null;

  $('#avail_staff_id').select2({ placeholder: '-- Select Teacher --', allowClear: true, width: '100%' });

  $('#btn-load-avail').on('click', function(){
    var staff_id = $('#avail_staff_id').val();
    if (!staff_id) { alert('Please select a teacher.'); return; }
    current_staff_id = staff_id;
    var name = $('#avail_staff_id option:selected').text();
    $('#avail-teacher-name').text(name);

    $.post('<?php echo site_url('admin/tt/get_teacher_unavail'); ?>',
      {staff_id: staff_id, [csrf_name]: csrf_val},
      function(res){
        // Reset all cells
        $('.avail-cell').each(function(){
          $(this).data('blocked', 0).removeClass('blocked')
            .html('<i class="fa fa-check-square-o text-success fa-lg"></i>');
        });
        // Mark blocked
        if (res.status === '1') {
          $.each(res.data, function(i, u){
            var $cell = $('.avail-cell[data-day="'+u.day+'"][data-period="'+u.period_id+'"]');
            $cell.data('blocked', 1).addClass('blocked')
              .html('<i class="fa fa-times-circle text-danger fa-lg"></i>');
          });
        }
        $('#avail-placeholder').hide();
        $('#avail-box').show();
      },'json');
  });

  // Toggle cell
  $(document).on('click', '.avail-cell', function(){
    var blocked = $(this).data('blocked');
    if (blocked) {
      $(this).data('blocked', 0).removeClass('blocked')
        .html('<i class="fa fa-check-square-o text-success fa-lg"></i>');
    } else {
      $(this).data('blocked', 1).addClass('blocked')
        .html('<i class="fa fa-times-circle text-danger fa-lg"></i>');
    }
  });

  // Save
  $('#btn-save-avail').on('click', function(){
    if (!current_staff_id) return;
    var slots = [];
    $('.avail-cell').filter(function(){ return $(this).data('blocked') == 1; }).each(function(){
      slots.push({day: $(this).data('day'), period_id: $(this).data('period'), reason: ''});
    });
    var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/save_teacher_unavail'); ?>',
      {staff_id: current_staff_id, slots: slots, [csrf_name]: csrf_val},
      function(res){
        $btn.prop('disabled',false).html('<i class="fa fa-save"></i> Save');
        if (res.status === '1') {
          $btn.html('<i class="fa fa-check"></i> Saved!').addClass('btn-success');
          setTimeout(function(){ $btn.html('<i class="fa fa-save"></i> Save').removeClass('btn-success'); }, 2000);
        } else {
          alert('Error saving. Please try again.');
        }
      },'json');
  });
});
</script>
</div>
