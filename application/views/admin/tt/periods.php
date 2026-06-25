<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1><?php echo $this->lang->line('tt_period_setup') ?: 'Period Setup'; ?> <small>Define daily time slots and breaks</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Period Setup</li>
    </ol>
</section>
<section class="content">
<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-list"></i> Period Slots <small class="text-muted ml-2">Drag to reorder</small></h3>
        <div class="box-tools">
          <span class="badge bg-green" id="period-count"><?php echo count($periods); ?> slots</span>
          <button class="btn btn-sm btn-primary" id="btn-add-period" style="margin-left:5px;"><i class="fa fa-plus"></i> Add Period / Break</button>
        </div>
      </div>
      <div class="box-body">
        <div class="callout callout-info" style="font-size:12px;">
          <ul class="pl-3" style="margin-bottom:0;">
            <li>Add all teaching periods first, then add breaks.</li>
            <li>Breaks are skipped during auto-generation.</li>
            <li>Drag rows in the table to reorder.</li>
            <li>Sort order determines the grid column order.</li>
          </ul>
        </div>
        <div class="table-responsive">
        <table class="table table-hover table-bordered" id="periods-table">
          <thead>
            <tr style="background:#3c8dbc;color:#fff;">
              <th width="30">#</th>
              <th>Name</th>
              <th>Start</th>
              <th>End</th>
              <th>Duration</th>
              <th>Type</th>
              <th width="100">Actions</th>
            </tr>
          </thead>
          <tbody id="periods-tbody">
            <?php foreach ($periods as $p): ?>
            <tr data-id="<?php echo $p->id; ?>" style="cursor:move;">
              <td><i class="fa fa-bars text-muted"></i></td>
              <td><strong><?php echo htmlspecialchars($p->name); ?></strong>
                <?php if ($p->is_break && $p->break_label): ?>
                  <br><small class="text-warning"><?php echo htmlspecialchars($p->break_label); ?></small>
                <?php endif; ?>
              </td>
              <td><?php echo date('h:i A', strtotime($p->start_time)); ?></td>
              <td><?php echo date('h:i A', strtotime($p->end_time)); ?></td>
              <td><?php
                $diff = (strtotime($p->end_time) - strtotime($p->start_time)) / 60;
                echo $diff . ' min';
              ?></td>
              <td><?php echo $p->is_break
                  ? '<span class="label label-warning">Break</span>'
                  : '<span class="label label-primary">Period</span>'; ?></td>
              <td>
                <button class="btn btn-xs btn-info btn-edit-period"
                  data-id="<?php echo $p->id; ?>"
                  data-name="<?php echo htmlspecialchars($p->name); ?>"
                  data-start="<?php echo $p->start_time; ?>"
                  data-end="<?php echo $p->end_time; ?>"
                  data-isbreak="<?php echo $p->is_break; ?>"
                  data-breaklabel="<?php echo htmlspecialchars($p->break_label ?? ''); ?>"
                  data-sortorder="<?php echo $p->sort_order; ?>">
                  <i class="fa fa-edit"></i>
                </button>
                <a href="<?php echo site_url('admin/tt/delete_period/'.$p->id); ?>"
                   class="btn btn-xs btn-danger btn-delete"
                   data-confirm="Delete this period?">
                  <i class="fa fa-trash"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (empty($periods)): ?>
        <div class="text-center text-muted p-4"><i class="fa fa-clock-o fa-2x"></i><br>No periods added yet. Add your first period.</div>
        <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
</section>

<!-- Period Modal -->
<div class="modal fade" id="periodModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-clock-o"></i> <span id="periodModalLabel">Add Period / Break</span></h4>
      </div>
      <form id="period-form">
        <div class="modal-body">
          <input type="hidden" id="period_id" name="id" value="0">
          <div class="form-group">
            <label>Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="period_name" name="name" placeholder="e.g. Period 1, Break, Lunch" required>
          </div>
          <div class="form-group">
            <label>Start Time <span class="text-danger">*</span></label>
            <div class="input-group" id="period_start_pick">
              <input type="text" class="form-control" id="period_start" name="start_time" placeholder="HH:MM:SS" autocomplete="off" required>
              <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
            </div>
          </div>
          <div class="form-group">
            <label>End Time <span class="text-danger">*</span></label>
            <div class="input-group" id="period_end_pick">
              <input type="text" class="form-control" id="period_end" name="end_time" placeholder="HH:MM:SS" autocomplete="off" required>
              <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
            </div>
          </div>
          <div class="form-group">
            <label><input type="checkbox" id="is_break" name="is_break" value="1"> This is a Break / Interval</label>
          </div>
          <div id="break_label_row" class="form-group" style="display:none;">
            <label>Break Label</label>
            <input type="text" class="form-control" id="break_label" name="break_label" placeholder="e.g. Short Break, Lunch Break">
          </div>
          <div class="form-group">
            <label>Sort Order</label>
            <input type="number" class="form-control" id="sort_order" name="sort_order" value="0" min="0">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" id="btn-reset" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Period</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';

  // Init timepickers on modal shown (inputs are hidden until modal opens)
  var tpInitialized = false;
  $('#periodModal').on('shown.bs.modal', function(){
    if (!tpInitialized) {
      $('#period_start').datetimepicker({ format: 'HH:mm:ss' });
      $('#period_end').datetimepicker({ format: 'HH:mm:ss' });
      tpInitialized = true;
    }
  });

  // Toggle break label field
  $('#is_break').on('change', function(){
    $('#break_label_row').toggle(this.checked);
  });

  // Drag to reorder
  if ($.fn.sortable) {
    $('#periods-tbody').sortable({
      handle: 'td:first-child',
      update: function(){
        var order = [];
        $('#periods-tbody tr').each(function(i){
          order.push($(this).data('id'));
        });
        $.post('<?php echo site_url('admin/tt/reorder_periods'); ?>', {order: order,
          [csrf_name]: csrf_val});
      }
    });
  }

  // Add button
  $('#btn-add-period').on('click', function(){
    $('#period-form')[0].reset();
    $('#period_id').val(0);
    $('#break_label_row').hide();
    $('#periodModalLabel').text('Add Period / Break');
    if (tpInitialized) {
      $('#period_start').data('DateTimePicker').clear();
      $('#period_end').data('DateTimePicker').clear();
    }
    $('#periodModal').modal('show');
  });

  // Edit
  $(document).on('click', '.btn-edit-period', function(){
    var d = $(this).data();
    $('#period_id').val(d.id);
    $('#period_name').val(d.name);
    $('#sort_order').val(d.sortorder);
    if (d.isbreak == 1) {
      $('#is_break').prop('checked', true);
      $('#break_label_row').show();
      $('#break_label').val(d.breaklabel);
    } else {
      $('#is_break').prop('checked', false);
      $('#break_label_row').hide();
    }
    $('#periodModalLabel').text('Edit Period / Break');
    $('#periodModal').modal('show');
    // Set timepicker values after modal is shown so DateTimePicker is initialized
    $('#periodModal').one('shown.bs.modal', function(){
      $('#period_start').data('DateTimePicker').date(moment(d.start, 'HH:mm:ss'));
      $('#period_end').data('DateTimePicker').date(moment(d.end, 'HH:mm:ss'));
    });
  });

  // Reset
  $('#btn-reset').on('click', function(){
    $('#period-form')[0].reset();
    $('#period_id').val(0);
    $('#break_label_row').hide();
    if (tpInitialized) {
      $('#period_start').data('DateTimePicker').clear();
      $('#period_end').data('DateTimePicker').clear();
    }
  });

  // Save
  $('#period-form').on('submit', function(e){
    e.preventDefault();
    var $btn = $(this).find('[type=submit]').prop('disabled', true).text('Saving...');
    $.post('<?php echo site_url('admin/tt/save_period'); ?>', $(this).serialize() + '&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>', function(res){
      if (res.status === '1') {
        toastr.success('Period saved successfully');
        $('#periodModal').modal('hide');
        location.reload();
      } else {
        swal({title:'Alert',text:'Error saving period. Please try again.',type:'warning'});
        $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Period');
      }
    }, 'json');
  });

  // Delete confirm
  $(document).on('click', '.btn-delete', function(e){
    e.preventDefault(); var _href=$(this).attr('href'); var _msg=$(this).data('confirm')||'Are you sure?'; swal({title:'Confirm',text:_msg,type:'warning',showCancelButton:true,confirmButtonColor:'#dd4b39',confirmButtonText:'Yes'},function(ok){if(ok)window.location.href=_href;});
  });
});
</script>
</div>
