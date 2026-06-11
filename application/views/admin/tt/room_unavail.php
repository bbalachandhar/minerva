<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Room Availability <small>Mark periods when a room is unavailable for scheduling</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Room Availability</li>
    </ol>
</section>
<section class="content">
<div class="row">
  <div class="col-md-3">
    <div class="box box-primary">
      <div class="box-header"><h3 class="box-title"><i class="fa fa-building-o"></i> Select Room</h3></div>
      <div class="box-body">
        <div class="form-group">
          <label>Room</label>
          <select class="form-control select2-room" id="ru_room_id" style="width:100%;">
            <option value="">-- Select Room --</option>
            <?php foreach ($rooms as $rm): ?>
            <option value="<?php echo $rm->id; ?>"><?php echo htmlspecialchars($rm->name); ?> (<?php echo $rm->room_number ?? $rm->room_type; ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <button class="btn btn-primary btn-block" id="btn-load-ru"><i class="fa fa-search"></i> Load</button>
      </div>
    </div>
    <div class="callout callout-info" style="font-size:12px;">
      <i class="fa fa-info-circle"></i> Blocked slots mean the room is permanently unavailable at those times (e.g., gym closed on Monday mornings, lab maintenance).
    </div>
  </div>

  <div class="col-md-9">
    <div class="box box-default" id="ru-box" style="display:none;">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-calendar"></i> Availability Grid — <span id="ru-room-name"></span></h3>
        <div class="box-tools">
          <button class="btn btn-success btn-sm" id="btn-save-ru"><i class="fa fa-save"></i> Save</button>
          <button class="btn btn-default btn-sm" id="btn-clear-ru"><i class="fa fa-times"></i> Clear All</button>
        </div>
      </div>
      <div class="box-body p-2">
        <p class="text-muted" style="font-size:12px;"><i class="fa fa-check-square-o text-success"></i> = Available &nbsp;&nbsp; <i class="fa fa-times-circle text-danger"></i> = <strong>Unavailable (blocked)</strong> — Click to toggle</p>
        <div class="table-responsive">
          <table class="table table-bordered text-center" id="ru-grid" style="font-size:13px;">
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
                <td class="ru-cell" data-day="<?php echo $day_key; ?>" data-period="<?php echo $period->id; ?>" data-blocked="0"
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
    <div id="ru-placeholder" class="text-center text-muted p-5">
      <i class="fa fa-arrow-left fa-2x"></i><br>Select a room to configure its availability.
    </div>
  </div>
</div>
</section>

<style>
.ru-cell.blocked { background: #ffdddd !important; }
.ru-cell:hover   { background: #f0f8ff; }
</style>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';
  var current_room_id = null;

  $('#ru_room_id').select2({ placeholder: '-- Select Room --', allowClear: true });

  $('#btn-load-ru').on('click', function(){
    var room_id = $('#ru_room_id').val();
    if (!room_id) { alert('Please select a room.'); return; }
    current_room_id = room_id;
    $('#ru-room-name').text($('#ru_room_id option:selected').text());

    $.post('<?php echo site_url('admin/tt/get_room_unavail'); ?>',
      {room_id: room_id, [csrf_name]: csrf_val},
      function(res){
        $('.ru-cell').each(function(){
          $(this).data('blocked', 0).removeClass('blocked').html('<i class="fa fa-check-square-o text-success fa-lg"></i>');
        });
        if (res.status === '1') {
          $.each(res.map, function(key){
            var parts = key.split('_');
            var day = parts.slice(0, -1).join('_'), period = parts[parts.length-1];
            var $c = $('.ru-cell[data-day="'+day+'"][data-period="'+period+'"]');
            $c.data('blocked',1).addClass('blocked').html('<i class="fa fa-times-circle text-danger fa-lg"></i>');
          });
        }
        $('#ru-placeholder').hide();
        $('#ru-box').show();
      },'json');
  });

  $(document).on('click', '.ru-cell', function(){
    var blocked = $(this).data('blocked');
    if (blocked) {
      $(this).data('blocked',0).removeClass('blocked').html('<i class="fa fa-check-square-o text-success fa-lg"></i>');
    } else {
      $(this).data('blocked',1).addClass('blocked').html('<i class="fa fa-times-circle text-danger fa-lg"></i>');
    }
  });

  $('#btn-clear-ru').on('click', function(){
    $('.ru-cell').data('blocked',0).removeClass('blocked').html('<i class="fa fa-check-square-o text-success fa-lg"></i>');
  });

  $('#btn-save-ru').on('click', function(){
    if (!current_room_id) return;
    var slots = [];
    $('.ru-cell[data-blocked="1"]').each(function(){
      slots.push({day: $(this).data('day'), period_id: $(this).data('period'), reason: ''});
    });
    var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/save_room_unavail'); ?>',
      {room_id: current_room_id, slots: slots, [csrf_name]: csrf_val},
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
