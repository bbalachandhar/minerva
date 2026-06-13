<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Teacher Constraints <small>Set max load, preferred hours and scheduling preferences per teacher</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Teacher Constraints</li>
    </ol>
</section>
<section class="content">
<div class="row">
  <div class="col-md-4">
    <div class="box box-primary">
      <div class="box-header"><h3 class="box-title"><i class="fa fa-plus"></i> Add / Update Constraint</h3></div>
      <div class="box-body">
        <form id="constraint-form">
          <input type="hidden" id="tc_id" name="id" value="0">
          <div class="form-group">
            <label>Teacher <span class="text-danger">*</span></label>
            <select class="form-control" name="staff_id" id="tc_staff" required>
              <option value="">-- Select Teacher --</option>
              <?php foreach ($staff_list as $st): ?>
              <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['name'].' '.$st['surname']); ?> (<?php echo $st['employee_id']; ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label>Max Periods / Day</label>
                <input type="number" class="form-control" name="max_periods_per_day" id="tc_max_day" value="6" min="1" max="12">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Max Periods / Week</label>
                <input type="number" class="form-control" name="max_periods_per_week" id="tc_max_week" value="30" min="1" max="60">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label>Min Free Periods / Day</label>
                <input type="number" class="form-control" name="min_free_per_day" id="tc_min_free" value="0" min="0" max="4">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Max Gap / Day</label>
                <input type="number" class="form-control" name="max_gap_per_day" id="tc_max_gap" placeholder="No limit" min="0" max="8">
                <small class="text-muted">Max consecutive free slots between lessons</small>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label>Preferred Start (not before)</label>
                <input type="time" class="form-control" name="preferred_start_time" id="tc_pref_start">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Preferred End (not after)</label>
                <input type="time" class="form-control" name="preferred_end_time" id="tc_pref_end">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Preferred / Home Room (optional)</label>
            <select class="form-control" name="preferred_room_id" id="tc_pref_room">
              <option value="">-- None --</option>
              <?php foreach ($rooms as $rm): ?>
              <option value="<?php echo $rm->id; ?>"><?php echo htmlspecialchars($rm->name); ?> (<?php echo $rm->room_type; ?>)</option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">Scheduler will prefer this room for this teacher's lessons</small>
          </div>
          <div class="form-group">
            <label><input type="checkbox" name="avoid_first_period" id="tc_avoid_first" value="1"> Avoid First Period of Day</label><br>
            <label><input type="checkbox" name="avoid_last_period"  id="tc_avoid_last"  value="1"> Avoid Last Period of Day</label><br>
            <label><input type="checkbox" name="exclude_from_substitution" id="tc_excl_subst" value="1"> Do not include in substitutions</label>
          </div>
          <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i> Save Constraint</button>
          <button type="button" class="btn btn-default btn-block" id="tc-reset">Reset</button>
        </form>
      </div>
    </div>
    <div class="callout callout-info" style="font-size:12px;">
      <h4><i class="fa fa-info-circle"></i> Soft vs Hard Constraints</h4>
      Max periods are <strong>hard constraints</strong> — strictly enforced. Preferred time and first/last period are <strong>soft constraints</strong> — the scheduler tries to respect them but may skip if no other slot is available.
    </div>
  </div>

  <div class="col-md-8">
    <div class="box box-default">
      <div class="box-header"><h3 class="box-title"><i class="fa fa-list"></i> Configured Constraints</h3></div>
      <div class="box-body table-responsive p-0">
        <table class="table table-bordered table-hover table-striped" style="font-size:13px;">
          <thead>
            <tr style="background:#3c8dbc;color:#fff;">
              <th>Teacher</th>
              <th>Max/Day</th>
              <th>Max/Week</th>
              <th>Min Free</th>
              <th>Max Gap</th>
              <th>Pref. Room</th>
              <th>Pref. Start</th>
              <th>Pref. End</th>
              <th>Avoid 1st</th>
              <th>Avoid Last</th>
              <th>Excl. Subst.</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($constraints as $c): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($c->name.' '.$c->surname); ?></strong><br><small><?php echo $c->employee_id; ?></small></td>
              <td><?php echo $c->max_periods_per_day; ?></td>
              <td><?php echo $c->max_periods_per_week; ?></td>
              <td><?php echo $c->min_free_per_day; ?></td>
              <td><?php echo isset($c->max_gap_per_day) && $c->max_gap_per_day !== null ? $c->max_gap_per_day : '-'; ?></td>
              <td><?php echo !empty($c->preferred_room_name) ? htmlspecialchars($c->preferred_room_name) : '-'; ?></td>
              <td><?php echo $c->preferred_start_time ? date('h:i A', strtotime($c->preferred_start_time)) : '-'; ?></td>
              <td><?php echo $c->preferred_end_time ? date('h:i A', strtotime($c->preferred_end_time)) : '-'; ?></td>
              <td><?php echo $c->avoid_first_period ? '<i class="fa fa-check text-success"></i>' : '-'; ?></td>
              <td><?php echo $c->avoid_last_period ? '<i class="fa fa-check text-success"></i>' : '-'; ?></td>
              <td><?php echo !empty($c->exclude_from_substitution) ? '<i class="fa fa-check text-danger"></i>' : '-'; ?></td>
              <td>
                <button class="btn btn-xs btn-info btn-edit-tc"
                  data-id="<?php echo $c->id; ?>"
                  data-staff="<?php echo $c->staff_id; ?>"
                  data-maxday="<?php echo $c->max_periods_per_day; ?>"
                  data-maxweek="<?php echo $c->max_periods_per_week; ?>"
                  data-minfree="<?php echo $c->min_free_per_day; ?>"
                  data-maxgap="<?php echo $c->max_gap_per_day ?? ''; ?>"
                  data-prefroom="<?php echo $c->preferred_room_id ?? ''; ?>"
                  data-prefstart="<?php echo $c->preferred_start_time ?? ''; ?>"
                  data-prefend="<?php echo $c->preferred_end_time ?? ''; ?>"
                  data-avoidfirst="<?php echo $c->avoid_first_period; ?>"
                  data-avoidlast="<?php echo $c->avoid_last_period; ?>"
                  data-exclsubst="<?php echo $c->exclude_from_substitution ?? 0; ?>">
                  <i class="fa fa-edit"></i>
                </button>
                <a href="<?php echo site_url('admin/tt/delete_teacher_constraint/'.$c->id); ?>"
                   class="btn btn-xs btn-danger btn-delete" data-confirm="Delete constraint for this teacher?">
                  <i class="fa fa-trash"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($constraints)): ?>
            <tr><td colspan="12" class="text-center text-muted p-4">No constraints configured yet. Default max is used during auto-generation.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</section>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';

  $('#tc_staff').select2({ placeholder: '-- Select Teacher --', allowClear: true, width: '100%' });
  $('#tc_pref_room').select2({ placeholder: '-- No Preference --', allowClear: true, width: '100%' });

  $(document).on('click', '.btn-edit-tc', function(){
    var d = $(this).data();
    $('#tc_id').val(d.id);
    $('#tc_staff').val(d.staff);
    $('#tc_max_day').val(d.maxday);
    $('#tc_max_week').val(d.maxweek);
    $('#tc_min_free').val(d.minfree);
    $('#tc_max_gap').val(d.maxgap || '');
    $('#tc_pref_room').val(d.prefroom || '');
    $('#tc_pref_start').val(d.prefstart);
    $('#tc_pref_end').val(d.prefend);
    $('#tc_avoid_first').prop('checked', d.avoidfirst == 1);
    $('#tc_avoid_last').prop('checked', d.avoidlast == 1);
    $('#tc_excl_subst').prop('checked', d.exclsubst == 1);
    $('html,body').animate({scrollTop:0}, 400);
  });

  $('#tc-reset').on('click', function(){ $('#constraint-form')[0].reset(); $('#tc_id').val(0); });

  $('#constraint-form').on('submit', function(e){
    e.preventDefault();
    var $btn = $(this).find('[type=submit]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/save_teacher_constraint'); ?>',
      $(this).serialize() + '&' + csrf_name + '=' + csrf_val,
      function(res){
        if (res.status === '1') { location.reload(); }
        else { alert('Error saving.'); $btn.prop('disabled',false).html('<i class="fa fa-save"></i> Save Constraint'); }
      },'json');
  });

  $(document).on('click','.btn-delete', function(e){
    if(!confirm($(this).data('confirm')||'Are you sure?')) e.preventDefault();
  });
});
</script>
</div>
