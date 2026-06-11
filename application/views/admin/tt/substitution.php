<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Substitution / Rescheduling <small>Manage teacher absences and assign substitutes</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Substitution</li>
    </ol>
</section>
<section class="content">

<div class="row">
  <div class="col-md-4">
    <div class="box box-primary">
      <div class="box-header"><h3 class="box-title"><i class="fa fa-user-times"></i> Mark Absence</h3></div>
      <div class="box-body">
        <div class="form-group">
          <label>Absent Teacher <span class="text-danger">*</span></label>
          <select class="form-control" id="absent_staff">
            <option value="">-- Select Teacher --</option>
            <?php foreach ($staff_list as $st): ?>
            <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['name'].' '.$st['surname']); ?> (<?php echo $st['employee_id']; ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Date <span class="text-danger">*</span></label>
          <input type="date" class="form-control" id="absence_date" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <button class="btn btn-warning btn-block" id="btn-load-slots">
          <i class="fa fa-search"></i> Find Affected Slots
        </button>
      </div>
    </div>

    <!-- Auto-substitute all button -->
    <div class="box box-info" id="auto-assign-box" style="display:none;">
      <div class="box-body text-center">
        <p><strong>All affected slots found.</strong></p>
        <button class="btn btn-success btn-block" id="btn-auto-assign-all">
          <i class="fa fa-magic"></i> Auto-Assign All Substitutes
        </button>
        <small class="text-muted">System picks best available teacher for each slot.</small>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <div id="slots-container">
      <div class="text-center text-muted p-5" id="slots-placeholder">
        <i class="fa fa-arrow-left fa-2x"></i><br>Select a teacher and date to find affected slots.
      </div>
    </div>
  </div>
</div>

<!-- Recent substitutions -->
<div class="box box-default">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-history"></i> Recent Substitutions (Last 30 days)</h3>
    <div class="box-tools"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button></div>
  </div>
  <div class="box-body table-responsive p-0">
    <table class="table table-bordered table-hover table-sm" style="font-size:12px;">
      <thead>
        <tr style="background:#3c8dbc;color:#fff;">
          <th>Date</th><th>Absent Teacher</th><th>Period</th><th>Class</th><th>Subject</th><th>Substitute</th><th>Type</th><th>Status</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent as $r): ?>
        <tr class="<?php echo $r->status === 'cancelled' ? 'text-muted' : ''; ?>">
          <td><?php echo date('d M Y', strtotime($r->date)); ?><br><small><?php echo $r->day; ?></small></td>
          <td><?php echo htmlspecialchars($r->absent_name.' '.$r->absent_surname); ?></td>
          <td><?php echo htmlspecialchars($r->period_name ?? ''); ?><br><small><?php echo $r->start_time ? date('h:i A', strtotime($r->start_time)) : ''; ?></small></td>
          <td><?php echo htmlspecialchars(($r->class ?? '').' '.($r->section ?? '')); ?></td>
          <td><?php echo htmlspecialchars($r->subject_name ?? 'N/A'); ?></td>
          <td><?php echo $r->sub_name ? htmlspecialchars($r->sub_name.' '.$r->sub_surname) : '<span class="text-danger">Unassigned</span>'; ?></td>
          <td><span class="label <?php echo $r->substitution_type==='auto_suggested'?'label-info':'label-primary'; ?>"><?php echo $r->substitution_type==='auto_suggested'?'Auto':'Manual'; ?></span></td>
          <td><span class="label label-<?php echo $r->status==='confirmed'?'success':($r->status==='cancelled'?'default':'warning'); ?>"><?php echo ucfirst($r->status); ?></span></td>
          <td>
            <?php if ($r->status !== 'cancelled'): ?>
            <button class="btn btn-xs btn-danger btn-cancel-sub" data-id="<?php echo $r->id; ?>"><i class="fa fa-times"></i></button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recent)): ?>
        <tr><td colspan="9" class="text-center text-muted p-3">No substitutions in the last 30 days.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</section>

<!-- Slot cards template area (populated by JS) -->
<template id="slot-card-template">
  <div class="box box-default slot-card" data-entry-id="" data-period-id="" data-sub-id="0">
    <div class="box-header" style="padding:8px 12px;">
      <h4 class="box-title slot-title" style="font-size:14px;"></h4>
      <div class="box-tools pull-right"><span class="slot-status-badge"></span></div>
    </div>
    <div class="box-body" style="padding:10px;">
      <div class="row">
        <div class="col-md-8">
          <strong>Subject:</strong> <span class="slot-subject"></span><br>
          <strong>Class:</strong> <span class="slot-class"></span><br>
          <strong>Room:</strong> <span class="slot-room"></span>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Substitute:</label>
            <select class="form-control input-sm slot-sub-select"></select>
          </div>
          <div class="form-group">
            <label>Note:</label>
            <input type="text" class="form-control input-sm slot-note" placeholder="Optional note">
          </div>
          <button class="btn btn-success btn-sm btn-assign-sub"><i class="fa fa-save"></i> Assign</button>
          <button class="btn btn-info btn-sm btn-auto-this" style="margin-left:4px;"><i class="fa fa-magic"></i> Auto</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';
  var current_slots = [];

  $('#absent_staff').select2({ placeholder: '-- Select Teacher --', allowClear: true, width: '100%' });

  $('#btn-load-slots').on('click', function(){
    var staff_id = $('#absent_staff').val();
    var date     = $('#absence_date').val();
    if (!staff_id || !date) { alert('Please select teacher and date.'); return; }

    var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/get_absent_slots'); ?>',
      {absent_staff_id: staff_id, date: date, [csrf_name]: csrf_val},
      function(res){
        $btn.prop('disabled',false).html('<i class="fa fa-search"></i> Find Affected Slots');
        $('#slots-placeholder').hide();

        if (res.status !== '1' || !res.slots || res.slots.length === 0) {
          $('#slots-container').html('<div class="alert alert-info text-center"><i class="fa fa-info-circle"></i> No periods scheduled for this teacher on '+date+' ('+res.day+').</div>');
          $('#auto-assign-box').hide();
          return;
        }

        current_slots = res.slots;
        var html = '<h4><i class="fa fa-calendar"></i> '+date+' ('+res.day+') — '+res.slots.length+' period(s) affected</h4>';

        $.each(res.slots, function(i, slot){
          var subOpts = '<option value="">-- Unassigned --</option>';
          var cur_sub = slot.substitution ? slot.substitution.substitute_staff_id : null;
          $.each(slot.available_teachers, function(j, t){
            subOpts += '<option value="'+t.id+'" '+(cur_sub==t.id?'selected':'')+'>'+t.name+' '+t.surname+'</option>';
          });

          var statusBadge = slot.substitution
            ? '<span class="label label-success">Assigned</span>'
            : '<span class="label label-warning">Pending</span>';

          html += '<div class="box box-default slot-card" data-entry-id="'+slot.id+'" data-period-id="'+slot.period_id+'" data-sub-id="'+(slot.substitution ? slot.substitution.id : 0)+'">'
            + '<div class="box-header" style="padding:8px 12px;">'
            + '<h4 class="box-title" style="font-size:14px;"><i class="fa fa-clock-o"></i> '+slot.period_name+' &nbsp; '+slot.start_time+'</h4>'
            + '<div class="box-tools pull-right">'+statusBadge+'</div></div>'
            + '<div class="box-body" style="padding:10px;"><div class="row">'
            + '<div class="col-md-5"><strong>Subject:</strong> '+(slot.subject_name||'N/A')+'<br>'
            + '<strong>Class:</strong> '+(slot.class_name||'')+' '+(slot.section_name||'')+'<br>'
            + '<strong>Room:</strong> '+(slot.room_name||'—')+'</div>'
            + '<div class="col-md-7"><div class="form-group" style="margin-bottom:5px;">'
            + '<label>Substitute Teacher:</label>'
            + '<select class="form-control input-sm slot-sub-select">'+subOpts+'</select></div>'
            + '<div class="form-group" style="margin-bottom:5px;">'
            + '<input type="text" class="form-control input-sm slot-note" placeholder="Note (optional)" value="'+(slot.substitution && slot.substitution.note ? slot.substitution.note : '')+'">'
            + '</div>'
            + '<button class="btn btn-success btn-sm btn-assign-sub"><i class="fa fa-save"></i> Save</button>'
            + ' <button class="btn btn-info btn-sm btn-auto-this"><i class="fa fa-magic"></i> Best Available</button>'
            + '</div></div></div></div>';
        });

        $('#slots-container').html(html);
        $('#auto-assign-box').show();
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
    var $card    = $(this).closest('.slot-card');
    var $select  = $card.find('.slot-sub-select');
    // Pick first available
    if ($select.find('option').length > 1) {
      $select.find('option:eq(1)').prop('selected', true);
    }
    $card.find('.btn-assign-sub').trigger('click');
  });

  // Auto-assign all
  $('#btn-auto-assign-all').on('click', function(){
    $('.slot-card').each(function(){
      var $card = $(this);
      var $select = $card.find('.slot-sub-select');
      if ($select.find('option').length > 1) {
        $select.find('option:eq(1)').prop('selected', true);
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
      $btn.prop('disabled',false).html('<i class="fa fa-save"></i> Save');
      if (res.status === '1') {
        $card.find('.box-tools .label').removeClass('label-warning').addClass('label-success').text('Assigned');
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
