<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Joint / Cross-Class Lessons <small>Schedule multiple teachers with multiple classes at the same slot</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Joint Lessons</li>
    </ol>
</section>
<section class="content">

<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-object-group"></i> Joint Lessons</h3>
    <div class="box-tools pull-right">
      <button class="btn btn-success btn-sm" id="btn-add-joint"><i class="fa fa-plus"></i> Add Joint Lesson</button>
    </div>
  </div>
  <div class="box-body p-0">
    <?php if (empty($joint_lessons)): ?>
    <div class="text-center text-muted p-5">
      <i class="fa fa-object-group fa-3x"></i><br><br>
      <strong>No joint lessons defined yet.</strong><br>
      Joint lessons let you schedule multiple class-sections to attend the same lesson simultaneously.<br>
      <em>Examples: Combined PT, Silambam, Assembly, Library period, cross-section lab.</em><br><br>
      <button class="btn btn-success" id="btn-add-joint2"><i class="fa fa-plus"></i> Add Joint Lesson</button>
    </div>
    <?php else: ?>
    <table class="table table-bordered table-hover" style="font-size:13px;">
      <thead>
        <tr style="background:#3c8dbc;color:#fff;">
          <th>Name</th>
          <th>Subject</th>
          <th>Teachers</th>
          <th>Room</th>
          <th>P/W</th>
          <th>Consec.</th>
          <th>Classes</th>
          <th>Priority</th>
          <th style="width:90px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($joint_lessons as $jl): ?>
        <tr>
          <td><strong><?php echo htmlspecialchars($jl->name); ?></strong>
              <?php if ($jl->notes): ?><br><small class="text-muted"><?php echo htmlspecialchars($jl->notes); ?></small><?php endif; ?></td>
          <td>
            <?php if ($jl->tt_color): ?>
            <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:<?php echo $jl->tt_color; ?>;margin-right:4px;"></span>
            <?php endif; ?>
            <?php echo htmlspecialchars($jl->subject_name ?? ''); ?>
            <?php if ($jl->subject_code): ?><small class="text-muted">(<?php echo $jl->subject_code; ?>)</small><?php endif; ?>
          </td>
          <td>
            <?php if (empty($jl->teachers)): ?>
              <span class="text-muted">—</span>
            <?php else: ?>
              <?php foreach ($jl->teachers as $i => $t): ?>
                <?php if ($i === 0): ?>
                  <span class="label label-primary" style="margin:1px;display:inline-block;"><?php echo htmlspecialchars($t->name.' '.$t->surname); ?></span>
                <?php else: ?>
                  <span class="label label-default" style="margin:1px;display:inline-block;"><?php echo htmlspecialchars($t->name.' '.$t->surname); ?></span>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endif; ?>
          </td>
          <td><?php echo $jl->room_name ? htmlspecialchars($jl->room_name) : '<span class="text-muted">—</span>'; ?></td>
          <td><strong><?php echo $jl->periods_per_week; ?></strong></td>
          <td><?php echo $jl->consecutive_periods; ?></td>
          <td>
            <?php foreach ($jl->classes as $cs): ?>
            <span class="label label-default" style="margin:1px;display:inline-block;"><?php echo htmlspecialchars($cs->class_name.' '.$cs->section_name); ?></span>
            <?php endforeach; ?>
            <?php if (empty($jl->classes)): ?><span class="text-danger">No classes!</span><?php endif; ?>
          </td>
          <td><?php
            $pLabel = [10=>'Highest',9=>'Very High',8=>'High',7=>'Above Normal',6=>'Normal',5=>'Below Normal',4=>'Low',3=>'Very Low',2=>'Very Low',1=>'Lowest'];
            $pColor = $jl->priority>=8?'danger':($jl->priority>=6?'warning':($jl->priority>=4?'default':'info'));
            echo '<span class="label label-'.$pColor.'" title="'.($pLabel[$jl->priority]??'').'">'.$jl->priority.'</span>';
          ?></td>
          <td>
            <button class="btn btn-xs btn-primary btn-edit-joint" data-id="<?php echo $jl->id; ?>"><i class="fa fa-edit"></i></button>
            <button class="btn btn-xs btn-danger btn-delete-joint" data-id="<?php echo $jl->id; ?>" data-name="<?php echo htmlspecialchars($jl->name); ?>"><i class="fa fa-trash"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<div class="callout callout-info" style="font-size:12px;">
  <h4><i class="fa fa-info-circle"></i> How joint lessons work</h4>
  <ul style="margin:6px 0 0 0;padding-left:20px;">
    <li>During Auto Generate, joint lessons are placed <strong>first</strong> (harder constraint: all classes must be free simultaneously).</li>
    <li>The generator picks the <strong>first available teacher</strong> from the pool for each placement (primary teacher preferred).</li>
    <li>After generation, each class-section's timetable will show the lesson independently.</li>
    <li>The subject must ideally exist in each class's Subject Group for correct display in the grid.</li>
    <li>Example: Silambam with 7 teachers (3 external + 4 internal) — assign all 7 as the teacher pool.</li>
  </ul>
</div>

</section>

<!-- Add / Edit Modal -->
<div class="modal fade" id="joint-modal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-object-group"></i> <span id="jl-modal-title">Add Joint Lesson</span></h4>
      </div>
      <div class="modal-body">
        <form id="joint-form">
          <input type="hidden" id="jl_id" name="id" value="0">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label>Lesson Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" id="jl_name" placeholder="e.g. Silambam All Sections, PE Combined 3A+3B" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Priority</label>
                <select class="form-control" name="priority" id="jl_priority">
                  <option value="10">10 — Highest (first to schedule)</option>
                  <option value="9">9 — Very High</option>
                  <option value="8">8 — High</option>
                  <option value="7" selected>7 — Above Normal (default)</option>
                  <option value="6">6 — Normal</option>
                  <option value="5">5 — Below Normal</option>
                  <option value="4">4 — Low</option>
                  <option value="3">3 — Very Low</option>
                  <option value="2">2 — Very Low</option>
                  <option value="1">1 — Lowest (last to schedule)</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Subject <span class="text-danger">*</span></label>
                <select class="form-control" name="subject_id" id="jl_subject" required>
                  <option value="">-- Select Subject --</option>
                  <?php foreach ($subjects as $s): ?>
                  <option value="<?php echo $s->id; ?>"><?php echo htmlspecialchars($s->name); ?> (<?php echo $s->code; ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Room (optional)</label>
                <select class="form-control" name="room_id" id="jl_room">
                  <option value="">-- Any Room --</option>
                  <?php foreach ($rooms as $rm): ?>
                  <option value="<?php echo $rm->id; ?>"><?php echo htmlspecialchars($rm->name); ?><?php echo $rm->room_type !== 'any' ? ' ('.$rm->room_type.')' : ''; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <!-- Multi-teacher picker -->
          <div class="form-group">
            <label><strong>Teacher Pool</strong>
              <small class="text-muted ml-2">Select one or more teachers. The generator picks the first available for each slot. The first selected is treated as primary (preferred).</small>
            </label>
            <select class="form-control" name="teacher_ids[]" id="jl_teachers" multiple style="width:100%;">
              <?php foreach ($staff_list as $st): ?>
              <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['name'].' '.$st['surname']); ?> (<?php echo $st['employee_id']; ?>)</option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted"><i class="fa fa-info-circle"></i> Leave empty if no specific teacher is required.</small>
          </div>

          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label>Periods / Week <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="periods_per_week" id="jl_ppw" value="1" min="1" max="20" required>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Consecutive</label>
                <select class="form-control" name="consecutive_periods" id="jl_consec">
                  <option value="1">1 (single)</option>
                  <option value="2">2 (double)</option>
                  <option value="3">3 (triple)</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Max Same Day</label>
                <input type="number" class="form-control" name="max_per_day" id="jl_mpd" value="1" min="1" max="4">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group" style="padding-top:26px;">
                <label><input type="checkbox" name="distribute_evenly" id="jl_spread" value="1" checked> Spread across days</label>
              </div>
            </div>
          </div>

          <!-- Participating Classes -->
          <div class="form-group">
            <label><strong>Participating Class-Sections <span class="text-danger">*</span></strong>
              <small class="text-muted ml-2">Select all classes that attend this lesson together</small>
            </label>
            <div id="jl-class-picker" style="border:1px solid #ddd;border-radius:4px;padding:10px;max-height:260px;overflow-y:auto;">
              <?php foreach ($classlist as $cls): ?>
              <div class="jl-class-group" style="margin-bottom:8px;">
                <strong style="font-size:12px;color:#555;"><?php echo htmlspecialchars($cls['class']); ?></strong>
                <div id="jl-sections-<?php echo $cls['id']; ?>" style="padding-left:18px;margin-top:3px;display:flex;flex-wrap:wrap;gap:8px;">
                  <?php foreach ($cls['sections'] as $sec): ?>
                  <label style="font-weight:normal;margin:0;font-size:12px;">
                    <input type="checkbox" class="jl-class-chk"
                      name="classes[]"
                      value="<?php echo $cls['id']; ?>_<?php echo $sec['section_id']; ?>"
                      data-class="<?php echo $cls['id']; ?>"
                      data-section="<?php echo $sec['section_id']; ?>">
                    <?php echo htmlspecialchars($sec['section']); ?>
                  </label>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <small id="jl-class-error" class="text-danger" style="display:none;">Please select at least 1 class-section.</small>
          </div>

          <div class="form-group">
            <label>Notes (optional)</label>
            <input type="text" class="form-control" name="notes" id="jl_notes" placeholder="Any additional details">
          </div>

          <div id="jl-conflict-msg" class="alert alert-danger" style="display:none;"></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="btn-save-joint"><i class="fa fa-save"></i> Save Joint Lesson</button>
      </div>
    </div>
  </div>
</div>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';

  $('#jl_subject').select2({ placeholder: '-- Select Subject --', allowClear: true, width: '100%' });
  $('#jl_room').select2({ placeholder: '-- Any Room --', allowClear: true, width: '100%' });
  $('#jl_teachers').select2({
    placeholder: '-- No specific teacher (any available) --',
    allowClear: true,
    width: '100%',
    tags: false
  });

  function openAddModal() {
    $('#jl-modal-title').text('Add Joint Lesson');
    $('#joint-form')[0].reset();
    $('#jl_id').val(0);
    $('.jl-class-chk').prop('checked', false);
    $('#jl-conflict-msg, #jl-class-error').hide();
    $('#jl_subject, #jl_room').val('').trigger('change.select2');
    $('#jl_teachers').val([]).trigger('change.select2');
    $('#jl_priority').val(7);
    $('#jl_spread').prop('checked', true);
    $('#joint-modal').modal('show');
  }

  $('#btn-add-joint, #btn-add-joint2').on('click', openAddModal);

  $('.btn-edit-joint').on('click', function(){
    var id = $(this).data('id');
    $.post('<?php echo site_url('admin/tt/get_joint_lesson'); ?>',
      {id: id, [csrf_name]: csrf_val}, function(res){
        if (res.status !== '1') { alert('Error loading lesson.'); return; }
        var l = res.lesson;
        $('#jl-modal-title').text('Edit Joint Lesson');
        $('#jl_id').val(l.id);
        $('#jl_name').val(l.name);
        $('#jl_subject').val(l.subject_id).trigger('change.select2');
        $('#jl_room').val(l.room_id || '').trigger('change.select2');
        $('#jl_ppw').val(l.periods_per_week);
        $('#jl_consec').val(l.consecutive_periods);
        $('#jl_mpd').val(l.max_per_day);
        $('#jl_priority').val(l.priority);
        $('#jl_spread').prop('checked', l.distribute_evenly == 1);
        $('#jl_notes').val(l.notes || '');

        // Populate teacher multi-select
        var teacher_ids = [];
        if (l.teachers && l.teachers.length) {
          $.each(l.teachers, function(i, t){ teacher_ids.push(String(t.staff_id)); });
        }
        $('#jl_teachers').val(teacher_ids).trigger('change.select2');

        // Set class checkboxes
        $('.jl-class-chk').prop('checked', false);
        $.each(l.classes, function(i, cs){
          $('.jl-class-chk[data-class="'+cs.class_id+'"][data-section="'+cs.section_id+'"]').prop('checked', true);
        });
        $('#jl-conflict-msg, #jl-class-error').hide();
        $('#joint-modal').modal('show');
      },'json');
  });

  $('#btn-save-joint').on('click', function(){
    $('#jl-conflict-msg, #jl-class-error').hide();
    var classes = [];
    $('.jl-class-chk:checked').each(function(){
      classes.push({class_id: $(this).data('class'), section_id: $(this).data('section')});
    });
    if (classes.length < 1) {
      $('#jl-class-error').text('Please select at least 1 class-section.').show();
      return;
    }

    // Collect teacher IDs in selection order
    var teacher_ids = $('#jl_teachers').val() || [];

    var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');

    // Build POST data manually to send teacher_ids[] as array
    var postData = {
      id:                  $('#jl_id').val(),
      name:                $('#jl_name').val(),
      subject_id:          $('#jl_subject').val(),
      room_id:             $('#jl_room').val(),
      periods_per_week:    $('#jl_ppw').val(),
      consecutive_periods: $('#jl_consec').val(),
      max_per_day:         $('#jl_mpd').val(),
      priority:            $('#jl_priority').val(),
      distribute_evenly:   $('#jl_spread').is(':checked') ? 1 : 0,
      notes:               $('#jl_notes').val(),
      classes_json:        JSON.stringify(classes),
      'teacher_ids[]':     teacher_ids
    };
    postData[csrf_name] = csrf_val;

    $.post('<?php echo site_url('admin/tt/save_joint_lesson'); ?>', postData, function(res){
      $btn.prop('disabled',false).html('<i class="fa fa-save"></i> Save Joint Lesson');
      if (res.status === '1') {
        $('#joint-modal').modal('hide');
        location.reload();
      } else {
        $('#jl-conflict-msg').text(res.message || 'Error saving.').show();
      }
    },'json').fail(function(){ $btn.prop('disabled',false).html('<i class="fa fa-save"></i> Save Joint Lesson'); alert('Server error.'); });
  });

  $('.btn-delete-joint').on('click', function(){
    var id   = $(this).data('id');
    var name = $(this).data('name');
    if (!confirm('Delete joint lesson "' + name + '"? This cannot be undone.')) return;
    $.post('<?php echo site_url('admin/tt/delete_joint_lesson/'); ?>'+id, {[csrf_name]: csrf_val}, function(res){
      if (res.status === '1') { location.reload(); }
      else { alert('Error deleting.'); }
    },'json');
  });
});
</script>
</div>
