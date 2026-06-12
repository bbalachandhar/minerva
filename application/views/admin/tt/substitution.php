<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Substitution Manager <small>Handle teacher absences and assign substitutes</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Substitution</li>
    </ol>
</section>
<section class="content">

<!-- ── Search bar ────────────────────────────────────────────── -->
<div class="box" style="border-top:3px solid #3c8dbc;margin-bottom:16px;">
  <div class="box-body" style="padding:14px 20px;">
    <div class="row" style="align-items:flex-end;">
      <div class="col-md-4">
        <label style="font-size:12px;font-weight:600;color:#555;margin-bottom:4px;">
          <i class="fa fa-user-times text-danger"></i> Absent Teacher <span class="text-danger">*</span>
        </label>
        <select class="form-control" id="absent_staff">
          <option value="">-- Select Teacher --</option>
          <?php foreach ($staff_list as $st): ?>
          <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['name'].' '.$st['surname']); ?> (<?php echo $st['employee_id']; ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label style="font-size:12px;font-weight:600;color:#555;margin-bottom:4px;">
          <i class="fa fa-calendar text-primary"></i> Date <span class="text-danger">*</span>
        </label>
        <div class="input-group date" id="absence_date_pick">
          <input type="text" class="form-control" id="absence_date" value="<?php echo date('Y-m-d'); ?>" placeholder="YYYY-MM-DD" readonly style="background:#fff;cursor:pointer;">
          <span class="input-group-addon" style="cursor:pointer;background:#f4f4f4;border-left:0;"><i class="fa fa-calendar" style="color:#3c8dbc;"></i></span>
        </div>
      </div>
      <div class="col-md-3">
        <label style="font-size:12px;margin-bottom:4px;">&nbsp;</label>
        <button class="btn btn-primary btn-block" id="btn-load-slots">
          <i class="fa fa-search"></i> Find Affected Slots
        </button>
      </div>
      <div class="col-md-2">
        <label style="font-size:12px;margin-bottom:4px;">&nbsp;</label>
        <button class="btn btn-success btn-block" id="btn-auto-assign-all" style="display:none;">
          <i class="fa fa-magic"></i> Auto-Assign All
        </button>
        <div id="slots-summary" style="font-size:12px;color:#888;padding-top:7px;text-align:center;"></div>
      </div>
    </div>
  </div>
</div>

<!-- ── Affected slots ─────────────────────────────────────────── -->
<div id="slots-container">
  <div id="slots-placeholder" style="text-align:center;padding:28px 0;color:#aaa;">
    <i class="fa fa-exchange fa-2x" style="opacity:.4;"></i>
    <p style="margin-top:10px;font-size:13px;">Select a teacher and date, then click <strong>Find Affected Slots</strong>.</p>
  </div>
</div>

<!-- ── Recent substitutions ───────────────────────────────────── -->
<div class="box box-default" style="margin-top:20px;">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-history"></i> Recent Substitutions <small class="text-muted">(Last 30 days)</small></h3>
    <div class="box-tools"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button></div>
  </div>
  <div class="box-body table-responsive p-0">
    <table class="table table-bordered table-hover" style="font-size:12px;">
      <thead>
        <tr style="background:#3c8dbc;color:#fff;">
          <th>Date</th><th>Absent Teacher</th><th>Period</th><th>Class</th><th>Subject</th><th>Substitute</th><th>Type</th><th>Status</th><th style="width:50px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent as $r): ?>
        <tr class="<?php echo $r->status === 'cancelled' ? 'text-muted' : ''; ?>">
          <td><?php echo date('d M', strtotime($r->date)); ?><br><small class="text-muted"><?php echo $r->day; ?></small></td>
          <td><?php echo htmlspecialchars($r->absent_name.' '.$r->absent_surname); ?></td>
          <td><?php echo htmlspecialchars($r->period_name ?? ''); ?><br><small><?php echo $r->start_time ? date('h:i A', strtotime($r->start_time)) : ''; ?></small></td>
          <td><?php echo htmlspecialchars(($r->class ?? '').' '.($r->section ?? '')); ?></td>
          <td><?php echo htmlspecialchars($r->subject_name ?? 'N/A'); ?></td>
          <td><?php echo $r->sub_name ? htmlspecialchars($r->sub_name.' '.$r->sub_surname) : '<span class="text-danger"><i class="fa fa-times-circle"></i> Unassigned</span>'; ?></td>
          <td><span class="label <?php echo $r->substitution_type==='auto_suggested'?'label-info':'label-primary'; ?>"><?php echo $r->substitution_type==='auto_suggested'?'Auto':'Manual'; ?></span></td>
          <td><span class="label label-<?php echo $r->status==='confirmed'?'success':($r->status==='cancelled'?'default':'warning'); ?>"><?php echo ucfirst($r->status); ?></span></td>
          <td><?php if ($r->status !== 'cancelled'): ?>
            <button class="btn btn-xs btn-danger btn-cancel-sub" data-id="<?php echo $r->id; ?>" title="Cancel"><i class="fa fa-times"></i></button>
          <?php endif; ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recent)): ?>
        <tr><td colspan="9" class="text-center text-muted" style="padding:20px;"><i class="fa fa-inbox"></i> No substitutions in the last 30 days.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</section>

<style>
.sub-day-header {
  background: linear-gradient(135deg,#2980b9,#3498db);
  color:#fff;
  padding:10px 16px;
  border-radius:4px 4px 0 0;
  font-size:14px;
  font-weight:600;
  margin-bottom:0;
}
.slot-card {
  border:1px solid #dce4ec;
  border-radius:4px;
  margin-bottom:14px;
  background:#fff;
  box-shadow:0 1px 3px rgba(0,0,0,.06);
  overflow:hidden;
  transition:box-shadow .2s;
}
.slot-card:hover { box-shadow:0 3px 10px rgba(0,0,0,.1); }
.slot-card.assigned { border-left:4px solid #27ae60; }
.slot-card.pending  { border-left:4px solid #e67e22; }
.slot-card-head {
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:8px 14px;
  background:#f8f9fa;
  border-bottom:1px solid #eee;
}
.slot-card-body { padding:10px 14px; }
.slot-period-badge {
  background:#3c8dbc;
  color:#fff;
  border-radius:3px;
  padding:3px 10px;
  font-size:12px;
  font-weight:700;
  margin-right:8px;
}
.slot-info-row {
  display:flex;
  flex-wrap:wrap;
  gap:6px 20px;
  font-size:12px;
  color:#555;
  margin-bottom:10px;
}
.slot-info-row i { color:#3c8dbc; width:14px; }
.slot-assign-row {
  display:flex;
  align-items:center;
  gap:8px;
  flex-wrap:wrap;
}
.slot-assign-row .form-control { font-size:12px; }
</style>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';
  var current_slots = [];

  $('#absent_staff').select2({ placeholder: '-- Select Teacher --', allowClear: true, width: '100%' });
  $('#absence_date_pick').datetimepicker({
    format: 'YYYY-MM-DD',
    maxDate: moment(),
    useCurrent: false,
    ignoreReadonly: true,
    icons: { time:'fa fa-clock-o', date:'fa fa-calendar', up:'fa fa-chevron-up', down:'fa fa-chevron-down', previous:'fa fa-chevron-left', next:'fa fa-chevron-right', today:'fa fa-crosshairs', clear:'fa fa-trash', close:'fa fa-times' }
  });
  $('#absence_date_pick').on('dp.show', function(){
    var dtp = $(this).data('DateTimePicker');
    if (!dtp.date()) dtp.date(moment());
  });

  $('#btn-load-slots').on('click', function(){
    var staff_id = $('#absent_staff').val();
    var date     = $('#absence_date').val();
    if (!staff_id || !date) { alert('Please select teacher and date.'); return; }

    var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Searching...');
    $.post('<?php echo site_url('admin/tt/get_absent_slots'); ?>',
      {absent_staff_id: staff_id, date: date, [csrf_name]: csrf_val},
      function(res){
        $btn.prop('disabled',false).html('<i class="fa fa-search"></i> Find Affected Slots');
        $('#slots-placeholder').hide();
        $('#btn-auto-assign-all').hide();
        $('#slots-summary').text('');

        if (res.status !== '1' || !res.slots || res.slots.length === 0) {
          $('#slots-container').html(
            '<div class="alert alert-info" style="margin:0;">'
            + '<i class="fa fa-info-circle"></i> No periods scheduled for this teacher on <strong>'+date+'</strong>'
            + (res.day ? ' ('+res.day+')' : '')+'.</div>');
          return;
        }

        current_slots = res.slots;
        var pendingCount = res.slots.filter(function(s){ return !s.substitution; }).length;
        $('#slots-summary').html('<i class="fa fa-clock-o text-warning"></i> <strong>'+pendingCount+'</strong> pending');
        $('#btn-auto-assign-all').show();

        var html = '<div class="sub-day-header"><i class="fa fa-calendar-check-o"></i> &nbsp;'
          + date + ' &mdash; ' + res.day + ' &nbsp; <span style="font-weight:400;font-size:12px;">('+res.slots.length+' period'+(res.slots.length>1?'s':'')+' affected)</span></div>'
          + '<div class="row" style="margin:12px 0 0 0;">';

        $.each(res.slots, function(i, slot){
          var isAssigned = !!slot.substitution;
          var curSubId = isAssigned ? slot.substitution.substitute_staff_id : '';
          var subOpts = '<option value="">-- Unassigned --</option>';
          $.each(slot.available_teachers, function(j,t){
            subOpts += '<option value="'+t.id+'"'+(curSubId==t.id?' selected':'')+'>'+t.name+' '+t.surname+'</option>';
          });
          var statusBadge = isAssigned
            ? '<span class="label label-success"><i class="fa fa-check"></i> Assigned</span>'
            : '<span class="label label-warning"><i class="fa fa-clock-o"></i> Pending</span>';

          html += '<div class="col-md-6 col-lg-4">'
            + '<div class="slot-card '+(isAssigned?'assigned':'pending')+'" data-entry-id="'+slot.id+'" data-period-id="'+slot.period_id+'" data-sub-id="'+(slot.substitution?slot.substitution.id:0)+'">'
            + '<div class="slot-card-head">'
            + '<span><span class="slot-period-badge">'+slot.period_name+'</span><span style="font-size:12px;color:#666;">'+slot.start_time+'</span></span>'
            + statusBadge
            + '</div>'
            + '<div class="slot-card-body">'
            + '<div class="slot-info-row">'
            + '<span><i class="fa fa-book"></i> <strong>'+(slot.subject_name||'N/A')+'</strong></span>'
            + '<span><i class="fa fa-users"></i> '+(slot.class_name||'')+' '+(slot.section_name||'')+'</span>'
            + (slot.room_name ? '<span><i class="fa fa-map-marker"></i> '+slot.room_name+'</span>' : '')
            + '</div>'
            + '<div class="slot-assign-row">'
            + '<select class="form-control input-sm slot-sub-select" style="flex:1;min-width:120px;">'+subOpts+'</select>'
            + '<input type="text" class="form-control input-sm slot-note" placeholder="Note" style="flex:0 0 90px;" value="'+(slot.substitution&&slot.substitution.note?slot.substitution.note:'')+'">'
            + '<button class="btn btn-sm btn-success btn-assign-sub" title="Save"><i class="fa fa-save"></i></button>'
            + '<button class="btn btn-sm btn-info btn-auto-this" title="Best available"><i class="fa fa-magic"></i></button>'
            + '</div>'
            + '</div>'
            + '</div></div>';
        });

        html += '</div>';
        $('#slots-container').html(html);
        $('.slot-sub-select').select2({ placeholder: '-- Unassigned --', allowClear: true, width: '100%' });
      },'json');
  });

  // Assign substitute for one slot
  $(document).on('click', '.btn-assign-sub', function(){
    var $card    = $(this).closest('.slot-card');
    var entry_id = $card.data('entry-id');
    var sub_id   = $card.find('.slot-sub-select').val();
    var note     = $card.find('.slot-note').val();
    var sub_row_id = $card.data('sub-id') || 0;
    _saveSubstitution(entry_id, sub_id, note, sub_row_id, $card);
  });

  // Auto-pick best available for one slot
  $(document).on('click', '.btn-auto-this', function(){
    var $card   = $(this).closest('.slot-card');
    var $select = $card.find('.slot-sub-select');
    if ($select.find('option').length > 1) {
      $select.val($select.find('option:eq(1)').val()).trigger('change');
    }
    $card.find('.btn-assign-sub').trigger('click');
  });

  // Auto-assign all
  $('#btn-auto-assign-all').on('click', function(){
    $('.slot-card').each(function(){
      var $card = $(this);
      var $select = $card.find('.slot-sub-select');
      if ($select.find('option').length > 1) {
        $select.val($select.find('option:eq(1)').val()).trigger('change');
        $card.find('.btn-assign-sub').trigger('click');
      }
    });
  });

  function _saveSubstitution(entry_id, sub_id, note, existing_id, $card) {
    var $btn = $card.find('.btn-assign-sub').prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/save_substitution'); ?>', {
      absent_staff_id: $('#absent_staff').val(),
      substitute_staff_id: sub_id,
      tt_entry_id: entry_id,
      date: $('#absence_date').val(),
      substitution_id: existing_id,
      note: note,
      [csrf_name]: csrf_val
    }, function(res){
      $btn.prop('disabled',false).html('<i class="fa fa-save"></i>');
      if (res.status === '1') {
        $card.removeClass('pending').addClass('assigned');
        $card.find('.slot-card-head .label').removeClass('label-warning').addClass('label-success').html('<i class="fa fa-check"></i> Assigned');
        $card.data('sub-id', res.substitute_id || existing_id || 1);
      } else {
        alert('Error saving substitution. Please try again.');
      }
    },'json');
  }

  // Cancel substitution
  $(document).on('click', '.btn-cancel-sub', function(){
    if (!confirm('Cancel this substitution?')) return;
    var id   = $(this).data('id');
    var $row = $(this).closest('tr');
    $.post('<?php echo site_url('admin/tt/cancel_substitution/'); ?>'+id,
      {[csrf_name]: csrf_val}, function(res){
        if (res.status === '1') { location.reload(); }
      },'json');
  });
});
</script>
</div>
