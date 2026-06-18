<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Subject Load <small>Configure weekly periods per subject per class — the scheduling "cards"</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Subject Load</li>
    </ol>
</section>
<section class="content">
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-filter"></i> Select Class</h3>
  </div>
  <div class="box-body">
    <div class="row">
      <div class="col-md-3">
        <label>Department</label>
        <select class="form-control" id="dept_filter">
          <option value="">-- All --</option>
          <?php foreach ($departments as $d): ?>
          <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label>Class <span class="text-danger">*</span></label>
        <select class="form-control" id="sl_class_id">
          <option value="">-- Select Class --</option>
          <?php foreach ($classlist as $cls): ?>
          <option value="<?php echo $cls['id']; ?>"><?php echo htmlspecialchars($cls['class']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label>Section <span class="text-danger">*</span></label>
        <select class="form-control" id="sl_section_id">
          <option value="">-- Select Section --</option>
        </select>
      </div>
      <div class="col-md-3">
        <label>&nbsp;</label>
        <button class="btn btn-primary btn-block" id="btn-load-subjects"><i class="fa fa-search"></i> Load Subjects</button>
      </div>
    </div>
  </div>
</div>

<div id="subject-load-container" style="display:none;">
  <div class="box box-default">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-table"></i> Subject Load Configuration <span id="sl-status-badge"></span></h3>
      <div class="box-tools">
        <button class="btn btn-default btn-sm" id="btn-toggle-copy-panel" title="Copy load settings from another section"><i class="fa fa-copy"></i> Copy from Section</button>
        &nbsp;
        <button class="btn btn-success btn-sm" id="btn-save-loads"><i class="fa fa-save"></i> Save All</button>
      </div>
    </div>
    <!-- Copy-from panel (hidden by default) -->
    <div id="copy-from-panel" style="display:none;background:#f9f9f9;border-bottom:1px solid #ddd;padding:12px 15px;">
      <div class="row" style="align-items:flex-end;">
        <div class="col-md-3">
          <label style="font-size:12px;">Source Class</label>
          <select class="form-control input-sm" id="cp_class_id">
            <option value="">-- Select Class --</option>
            <?php foreach ($classlist as $cls): ?>
            <option value="<?php echo $cls['id']; ?>"><?php echo htmlspecialchars($cls['class']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label style="font-size:12px;">Source Section</label>
          <select class="form-control input-sm" id="cp_section_id">
            <option value="">-- Select Section --</option>
          </select>
        </div>
        <div class="col-md-3">
          <button class="btn btn-primary btn-sm" id="btn-apply-copy" style="margin-top:20px;"><i class="fa fa-arrow-down"></i> Apply (Periods/Week only)</button>
        </div>
        <div class="col-md-3">
          <small class="text-muted" style="display:block;margin-top:22px;">Copies <em>Periods/Week</em> values for matching subjects. Teacher assignments are not copied.</small>
        </div>
      </div>
    </div>
    <div class="box-body p-0">
      <form id="subject-load-form">
        <input type="hidden" name="class_id" id="sl_class_id_hidden">
        <input type="hidden" name="section_id" id="sl_section_id_hidden">
        <div id="subject-load-rows"></div>
      </form>
    </div>
    <div id="sl-teacher-warnings" style="display:none;padding:8px 15px;">
    </div>
    <div class="box-footer">
      <button class="btn btn-success" id="btn-save-loads-bottom"><i class="fa fa-save"></i> Save All Changes</button>
      <small class="text-muted ml-3"><i class="fa fa-info-circle"></i> Changes take effect on the next Auto Generate run.</small>
    </div>
  </div>
</div>

<div id="subject-load-empty" class="text-center text-muted p-5" style="display:none;">
  <i class="fa fa-exclamation-circle fa-3x"></i><br>
  <strong>Could not load subjects.</strong><br>
  Please try again.
</div>
</section>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';

  $('#dept_filter').select2({ placeholder: '-- All --', allowClear: true, width: '100%' });
  $('#sl_class_id').select2({ placeholder: '-- Select Class --', allowClear: true, width: '100%' });
  $('#sl_section_id').select2({ placeholder: '-- Select Section --', allowClear: true, width: '100%' });

  $('#sl_class_id').on('change', function(){
    var id = $(this).val();
    $('#sl_section_id').html('<option value="">Loading...</option>');
    if (!id) return;
    $.post('<?php echo site_url('admin/tt/get_sections_by_class'); ?>',
      {class_id: id, [csrf_name]: csrf_val}, function(res){
        var opts = '<option value="">-- Select Section --</option>';
        $.each(res, function(i,s){ opts += '<option value="'+s.section_id+'">'+s.section+'</option>'; });
        $('#sl_section_id').html(opts);
      },'json');
  });

  function updateStatusBadge() {
    var $pools = $('#subject-load-rows .sl-teacher-pool');
    var total = $pools.length;
    if (!total) { $('#sl-status-badge').html(''); return; }
    var configured = $pools.filter(function(){ return $(this).val() && $(this).val().length > 0; }).length;
    var color = (configured === total) ? 'success' : (configured > 0 ? 'warning' : 'danger');
    $('#sl-status-badge').html('<span class="label label-'+color+'" style="font-size:11px;vertical-align:middle;margin-left:6px;">'+configured+'/'+total+' teacher pools assigned</span>');
  }

  function loadSubjects() {
    var class_id   = $('#sl_class_id').val();
    var section_id = $('#sl_section_id').val();
    if (!class_id || !section_id) { alert('Please select Class and Section.'); return; }

    $('#subject-load-container, #subject-load-empty').hide();
    $('#copy-from-panel').hide();
    var $btn = $('#btn-load-subjects').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');

    $.post('<?php echo site_url('admin/tt/get_subject_load_data'); ?>',
      {class_id: class_id, section_id: section_id, [csrf_name]: csrf_val},
      function(res){
        $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Load Subjects');
        if (res.status === '1') {
          $('#sl_class_id_hidden').val(class_id);
          $('#sl_section_id_hidden').val(section_id);
          $('#subject-load-rows').html(res.html);
          $('#subject-load-rows select:not(#sl-add-subject-picker)').select2({ width: 'resolve', placeholder: '-- Select --', allowClear: true });
          $('#sl-add-subject-picker').select2({ width: '100%', placeholder: 'Select subjects to add...', allowClear: true });
          $('#subject-load-container').show();
          updateStatusBadge();
        } else {
          $('#subject-load-empty').show();
        }
      },'json');
  }

  $('#btn-load-subjects').on('click', loadSubjects);

  // Remove subject row
  $(document).on('click', '.btn-remove-sl-row', function(){
    var $row = $(this).closest('tr');
    var load_id = $(this).data('load-id');
    if (!confirm('Remove this subject from the class?')) return;
    if (load_id > 0) {
      $.post('<?php echo site_url('admin/tt/delete_subject_load_row'); ?>',
        {id: load_id, [csrf_name]: csrf_val},
        function(res){ if (res.status === '1') { $row.fadeOut(300, function(){ $(this).remove(); updateStatusBadge(); }); }
          else { alert('Error removing subject.'); } }, 'json');
    } else {
      $row.fadeOut(300, function(){ $(this).remove(); updateStatusBadge(); });
    }
  });

  // Add subjects
  $(document).on('click', '#btn-add-subjects', function(){
    var class_id   = $('#sl_class_id_hidden').val();
    var section_id = $('#sl_section_id_hidden').val();
    var subject_ids = $('#sl-add-subject-picker').val();
    if (!subject_ids || subject_ids.length === 0) { alert('Please select at least one subject.'); return; }
    var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/add_subjects_to_load'); ?>',
      {class_id: class_id, section_id: section_id, subject_ids: subject_ids, [csrf_name]: csrf_val},
      function(res){
        $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Add Selected');
        if (res.status === '1') {
          loadSubjects(); // reload the table to show new rows
        } else { alert('Error adding subjects: ' + (res.error || 'Unknown error')); }
      }, 'json');
  });

  // Copy-from panel toggle
  $('#btn-toggle-copy-panel').on('click', function(){
    $('#copy-from-panel').slideToggle(200);
  });

  $('#cp_class_id').on('change', function(){
    var id = $(this).val();
    $('#cp_section_id').html('<option value="">Loading...</option>');
    if (!id) return;
    $.post('<?php echo site_url('admin/tt/get_sections_by_class'); ?>',
      {class_id: id, [csrf_name]: csrf_val}, function(res){
        var opts = '<option value="">-- Select Section --</option>';
        $.each(res, function(i,s){ opts += '<option value="'+s.section_id+'">'+s.section+'</option>'; });
        $('#cp_section_id').html(opts);
      },'json');
  });

  $('#btn-apply-copy').on('click', function(){
    var class_id   = $('#cp_class_id').val();
    var section_id = $('#cp_section_id').val();
    if (!class_id || !section_id) { alert('Select source class and section first.'); return; }
    var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/get_subject_load_raw'); ?>',
      {class_id: class_id, section_id: section_id, [csrf_name]: csrf_val},
      function(res){
        $btn.prop('disabled',false).html('<i class="fa fa-arrow-down"></i> Apply (Periods/Week only)');
        if (res.status === '1') {
          var applied = 0;
          $.each(res.data, function(sgs_id, row){
            var $ppw = $('[name="rows['+sgs_id+'][periods_per_week]"]');
            if ($ppw.length && row.periods_per_week > 0) {
              $ppw.val(row.periods_per_week);
              applied++;
            }
          });
          updateStatusBadge();
          alert('Applied periods/week for '+applied+' subject(s). Review and save.');
          $('#copy-from-panel').slideUp(200);
        } else {
          alert('No load data found for selected section.');
        }
      },'json');
  });

  function saveLoads() {
    var $btn = $('#btn-save-loads, #btn-save-loads-bottom').prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    var formData = $('#subject-load-form').serialize() + '&' + csrf_name + '=' + csrf_val;
    $.post('<?php echo site_url('admin/tt/save_subject_load'); ?>', formData, function(res){
      $btn.prop('disabled',false);
      if (res.status === '1') {
        $('#btn-save-loads, #btn-save-loads-bottom').html('<i class="fa fa-check"></i> Saved!').addClass('btn-success');
        setTimeout(function(){ $('#btn-save-loads, #btn-save-loads-bottom').html('<i class="fa fa-save"></i> Save All').removeClass('btn-success'); }, 2000);
        updateStatusBadge();
      } else {
        alert('Error saving. Please try again.');
        $btn.html('<i class="fa fa-save"></i> Save All');
      }
    },'json');
  }

  $('#btn-save-loads, #btn-save-loads-bottom').on('click', saveLoads);

  // ---- Teacher capacity validation ----
  var teacherCap = {}; // {tid: {name, total_ppw, max_week, max_day, unavail, avail_slots, slot_count, day_count}}

  function loadTeacherCapacity() {
    $.post('<?php echo site_url("admin/tt/get_teacher_capacity_data"); ?>', {[csrf_name]: csrf_val}, function(res){
      if (res.status === '1') teacherCap = res.data;
      validateTeacherLoads();
    }, 'json');
  }

  function validateTeacherLoads() {
    if (!Object.keys(teacherCap).length) return;
    var classId = $('#sl_class_id_hidden').val();
    var sectionId = $('#sl_section_id_hidden').val();
    if (!classId) return;

    // Collect this form's teacher→ppw assignments
    var formLoads = {}; // {tid: total_ppw_in_this_form}
    $('#sl-rows-table tbody tr').each(function(){
      var $row = $(this);
      var ppw = parseInt($row.find('[name$="[periods_per_week]"]').val()) || 0;
      var $pool = $row.find('.sl-teacher-pool');
      if (!$pool.length) return;
      var tids = $pool.val() || [];
      $.each(tids, function(i, tid) {
        formLoads[tid] = (formLoads[tid] || 0) + ppw;
      });
    });

    var warnings = [];
    var $rows = $('#sl-rows-table tbody tr');
    $rows.find('.sl-cap-warn').remove();

    $.each(formLoads, function(tid, formPpw) {
      var cap = teacherCap[tid];
      if (!cap) return;
      // Total across all classes = cap.total_ppw for OTHER classes + formPpw for THIS class
      // But cap.total_ppw includes this class's saved data. We need: other_classes + current_form
      // Approximation: use cap.total_ppw as the saved total. If they changed values, it'll be
      // slightly off until they save — but close enough for real-time warnings.
      var totalOther = cap.total_ppw; // includes this class's saved values
      var effective = totalOther; // we'll refine below if we can

      var overWeek = effective > cap.max_week;
      var overSlots = effective > cap.avail_slots && cap.unavail > 0;
      var dayCapacity = cap.max_day * cap.day_count;
      var overDay = effective > dayCapacity;
      var highUse = effective >= cap.max_week * 0.85;

      if (overWeek || overSlots || overDay) {
        var msg = '<b>' + cap.name + '</b>: ' + effective + '/' + cap.max_week + ' periods/week';
        if (overWeek) msg += ' — <span class="text-danger">OVER weekly cap by ' + (effective - cap.max_week) + '</span>';
        if (overSlots) msg += ' — <span class="text-danger">' + cap.unavail + ' unavailable slots, only ' + cap.avail_slots + ' free</span>';
        if (overDay) msg += ' — <span class="text-danger">exceeds ' + cap.max_day + '/day × ' + cap.day_count + ' days = ' + dayCapacity + '</span>';
        warnings.push({tid: tid, msg: msg, level: 'danger'});
      } else if (highUse) {
        var pct = Math.round(effective / cap.max_week * 100);
        warnings.push({tid: tid, msg: '<b>' + cap.name + '</b>: ' + effective + '/' + cap.max_week + ' periods/week (' + pct + '% capacity)', level: 'warning'});
      }

      // Inline indicator on rows with this teacher
      $rows.each(function(){
        var $pool = $(this).find('.sl-teacher-pool');
        if (!$pool.length) return;
        var tids = $pool.val() || [];
        if (tids.indexOf(tid.toString()) === -1 && tids.indexOf(parseInt(tid)) === -1) return;
        var $td = $pool.closest('td');
        if (overWeek || overSlots) {
          $td.append('<div class="sl-cap-warn text-danger" style="font-size:10px;margin-top:2px;"><i class="fa fa-exclamation-triangle"></i> ' + cap.name + ': ' + effective + '/' + cap.max_week + '</div>');
        } else if (highUse) {
          $td.append('<div class="sl-cap-warn text-warning" style="font-size:10px;margin-top:2px;"><i class="fa fa-info-circle"></i> ' + cap.name + ': ' + effective + '/' + cap.max_week + '</div>');
        }
      });
    });

    var $panel = $('#sl-teacher-warnings');
    if (warnings.length > 0) {
      var html = '<div class="alert alert-warning" style="margin-bottom:0;font-size:12px;padding:8px 12px;">'
        + '<strong><i class="fa fa-users"></i> Teacher Capacity Summary</strong><br>';
      $.each(warnings, function(i, w) {
        html += '<div style="margin-top:4px;"><i class="fa fa-' + (w.level === 'danger' ? 'times-circle text-danger' : 'info-circle text-warning') + '"></i> ' + w.msg + '</div>';
      });
      html += '</div>';
      $panel.html(html).show();
    } else {
      $panel.hide();
    }
  }

  // Re-validate when teacher pool or periods/week changes
  $(document).on('change', '.sl-teacher-pool, [name$="[periods_per_week]"]', function(){
    setTimeout(validateTeacherLoads, 100);
  });

  // Load teacher capacity data once at init, re-validate when subjects are fetched
  loadTeacherCapacity();
  $(document).ajaxComplete(function(e, xhr, settings) {
    if (settings.url && settings.url.indexOf('get_subject_load_data') !== -1) {
      setTimeout(function(){ loadTeacherCapacity(); }, 300);
    }
  });
});
</script>
</div>
