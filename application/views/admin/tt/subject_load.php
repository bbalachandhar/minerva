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
    if (!class_id || !section_id) { swal({title:'Alert',text:'Please select Class and Section.',type:'warning'}); return; }

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
    swal({title:'Remove Subject?',text:'Remove this subject from the class?',type:'warning',showCancelButton:true,confirmButtonColor:'#dd4b39',confirmButtonText:'Yes, remove'},function(isConfirm){
      if (!isConfirm) return;
      if (load_id > 0) {
        $.post('<?php echo site_url('admin/tt/delete_subject_load_row'); ?>',
          {id: load_id, [csrf_name]: csrf_val},
          function(res){ if (res.status === '1') { $row.fadeOut(300, function(){ $(this).remove(); updateStatusBadge(); }); }
            else { swal({title:'Error',text:'Error removing subject.',type:'error'}); } }, 'json');
      } else {
        $row.fadeOut(300, function(){ $(this).remove(); updateStatusBadge(); });
      }
    });
  });

  // Add subjects
  $(document).on('click', '#btn-add-subjects', function(){
    var class_id   = $('#sl_class_id_hidden').val();
    var section_id = $('#sl_section_id_hidden').val();
    var subject_ids = $('#sl-add-subject-picker').val();
    if (!subject_ids || subject_ids.length === 0) { swal({title:'Alert',text:'Please select at least one subject.',type:'warning'}); return; }
    var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/add_subjects_to_load'); ?>',
      {class_id: class_id, section_id: section_id, subject_ids: subject_ids, [csrf_name]: csrf_val},
      function(res){
        $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Add Selected');
        if (res.status === '1') {
          toastr.success('Subjects added');
          loadSubjects(); // reload the table to show new rows
        } else { swal({title:'Error',text:'Error adding subjects: ' + (res.error || 'Unknown error'),type:'error'}); }
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
    if (!class_id || !section_id) { swal({title:'Alert',text:'Select source class and section first.',type:'warning'}); return; }
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
          swal({title:'Alert',text:'Applied periods/week for '+applied+' subject(s). Review and save.',type:'warning'});
          $('#copy-from-panel').slideUp(200);
        } else {
          swal({title:'Alert',text:'No load data found for selected section.',type:'warning'});
        }
      },'json');
  });

  function saveLoads() {
    var $btn = $('#btn-save-loads, #btn-save-loads-bottom').prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    var formData = $('#subject-load-form').serialize() + '&' + csrf_name + '=' + csrf_val;
    $.post('<?php echo site_url('admin/tt/save_subject_load'); ?>', formData, function(res){
      $btn.prop('disabled',false);
      if (res.status === '1') {
        toastr.success('Subject loads saved');
        $('#btn-save-loads, #btn-save-loads-bottom').html('<i class="fa fa-check"></i> Saved!').addClass('btn-success');
        setTimeout(function(){ $('#btn-save-loads, #btn-save-loads-bottom').html('<i class="fa fa-save"></i> Save All').removeClass('btn-success'); }, 5000);
        updateStatusBadge();
        if (res.warning) {
          showSlAlert('warning', 'Saved with Warnings', res.warning);
        } else {
          toastr.success('Subject loads saved successfully.', '', {timeOut:3000, positionClass:'toast-top-right'});
        }
      } else {
        showSlAlert('error', 'Cannot Save', res.message || 'Error saving. Please try again.');
        $btn.html('<i class="fa fa-save"></i> Save All');
      }
    },'json').fail(function(xhr, status, err){
      $btn.prop('disabled',false).html('<i class="fa fa-save"></i> Save All');
      toastr.error('Network error: ' + (err || status), '', {timeOut:5000, positionClass:'toast-top-right'});
    });
  }

  $('#btn-save-loads, #btn-save-loads-bottom').on('click', saveLoads);

  // ---- Teacher capacity validation ----
  var teacherCap = {};
  var workingDays = 6; // updated from server data

  function loadTeacherCapacity() {
    $.post('<?php echo site_url("admin/tt/get_teacher_capacity_data"); ?>', {[csrf_name]: csrf_val}, function(res){
      if (res.status === '1') {
        teacherCap = res.data;
        // Extract working days count from any teacher's data
        $.each(res.data, function(tid, d) { workingDays = d.day_count || 6; return false; });
      }
      validateTeacherLoads();
      enforceMinPerDay();
    }, 'json').fail(function(){ console.warn('Teacher capacity data load failed'); });
  }

  function enforceMinPerDay() {
    $('#sl-rows-table tbody tr').each(function(){
      var $row = $(this);
      var ppw = parseInt($row.find('[name$="[periods_per_week]"]').val()) || 0;
      var $chk = $row.find('[name$="[min_per_day]"]');
      if (!$chk.length) return;
      var $td = $chk.closest('td');
      $td.find('.min-day-warn').remove();
      if (ppw < workingDays) {
        if ($chk.is(':checked')) $chk.prop('checked', false);
        $chk.prop('disabled', true);
        $td.append('<div class="min-day-warn text-muted" style="font-size:9px;margin-top:1px;" title="' + ppw + ' periods/week < ' + workingDays + ' working days — impossible to have 1 per day"><i class="fa fa-ban"></i> ' + ppw + '<' + workingDays + '</div>');
      } else {
        $chk.prop('disabled', false);
      }
    });
  }

  $(document).on('change', '[name$="[periods_per_week]"]', function(){
    setTimeout(enforceMinPerDay, 50);
  });

  function validateTeacherLoads() {
    var classId = $('#sl_class_id_hidden').val();
    if (!classId || !Object.keys(teacherCap).length) return;

    var formLoads = {};
    $('#sl-rows-table tbody tr').each(function(){
      var ppw = parseInt($(this).find('[name$="[periods_per_week]"]').val()) || 0;
      var $pool = $(this).find('.sl-teacher-pool');
      if (!$pool.length) return;
      var tids = $pool.val() || [];
      $.each(tids, function(i, tid) {
        formLoads[tid] = (formLoads[tid] || 0) + ppw;
      });
    });

    if (!Object.keys(formLoads).length) { $('#sl-teacher-warnings').hide(); return; }

    var $rows = $('#sl-rows-table tbody tr');
    $rows.find('.sl-cap-warn').remove();

    var summaryRows = [];
    $.each(formLoads, function(tid, formPpw) {
      var cap = teacherCap[tid];
      if (!cap) return;
      var total = cap.total_ppw;
      var pct = cap.max_week > 0 ? Math.round(total / cap.max_week * 100) : 0;
      var overWeek = total > cap.max_week;
      var overSlots = total > cap.avail_slots && cap.unavail > 0;
      var dayCapacity = cap.max_day * cap.day_count;
      var overDay = total > dayCapacity;

      var color = overWeek || overSlots || overDay ? 'danger' : (pct >= 85 ? 'warning' : (pct >= 60 ? 'info' : 'success'));
      var barWidth = Math.min(pct, 100);
      var issues = [];
      if (overWeek) issues.push('OVER weekly cap by ' + (total - cap.max_week));
      if (overSlots) issues.push(cap.unavail + ' unavailable slots, only ' + cap.avail_slots + ' free');
      if (overDay) issues.push('exceeds ' + cap.max_day + '/day × ' + cap.day_count + ' days = ' + dayCapacity);

      summaryRows.push({
        tid: tid, name: cap.name, total: total, max: cap.max_week, pct: pct,
        color: color, barWidth: barWidth, issues: issues, thisClass: formPpw,
        overWeek: overWeek, overSlots: overSlots, overDay: overDay
      });

      // Inline indicator on each row that uses this teacher
      $rows.each(function(){
        var $pool = $(this).find('.sl-teacher-pool');
        if (!$pool.length) return;
        var poolVals = $pool.val() || [];
        var match = false;
        $.each(poolVals, function(i, v) { if (v == tid) match = true; });
        if (!match) return;
        var $td = $pool.closest('td');
        var labelClass = color === 'danger' ? 'label-danger' : (color === 'warning' ? 'label-warning' : 'label-info');
        $td.append('<div class="sl-cap-warn" style="font-size:10px;margin-top:2px;">'
          + '<span class="label ' + labelClass + '" style="font-weight:normal;">'
          + cap.name + ': ' + total + '/' + cap.max_week + ' (' + pct + '%)'
          + '</span></div>');
      });
    });

    // Sort: problems first, then by utilization descending
    summaryRows.sort(function(a, b) {
      if (a.color === 'danger' && b.color !== 'danger') return -1;
      if (b.color === 'danger' && a.color !== 'danger') return 1;
      return b.pct - a.pct;
    });

    var $panel = $('#sl-teacher-warnings');
    var hasDanger = summaryRows.some(function(r){ return r.color === 'danger'; });
    var alertClass = hasDanger ? 'alert-danger' : 'alert-info';
    var html = '<div class="alert ' + alertClass + '" style="margin-bottom:0;font-size:12px;padding:10px 12px;">'
      + '<strong><i class="fa fa-users"></i> Teacher Workload — This Class</strong>'
      + '<div style="margin-top:6px;">';

    $.each(summaryRows, function(i, r) {
      html += '<div style="display:flex;align-items:center;margin-bottom:4px;">'
        + '<span style="min-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="' + r.name + '">' + r.name + '</span>'
        + '<div style="flex:1;margin:0 8px;background:#eee;border-radius:3px;height:14px;position:relative;">'
        + '<div style="width:' + r.barWidth + '%;height:100%;border-radius:3px;background:' + (r.color==='danger'?'#dd4b39':r.color==='warning'?'#f39c12':r.color==='info'?'#00c0ef':'#00a65a') + ';"></div>'
        + '</div>'
        + '<span style="min-width:90px;text-align:right;white-space:nowrap;" class="text-' + r.color + '">'
        + '<b>' + r.total + '</b>/' + r.max + ' <small>(' + r.pct + '%)</small>'
        + '</span></div>';
      if (r.issues.length) {
        html += '<div style="margin:-2px 0 4px 168px;font-size:11px;" class="text-danger"><i class="fa fa-exclamation-triangle"></i> ' + r.issues.join(' | ') + '</div>';
      }
    });

    html += '</div></div>';
    $panel.html(html).show();
  }

  $(document).on('change', '.sl-teacher-pool, [name$="[periods_per_week]"]', function(){
    setTimeout(validateTeacherLoads, 100);
  });

  // Load capacity data once and refresh after subjects are fetched
  loadTeacherCapacity();
  $(document).ajaxComplete(function(e, xhr, settings) {
    if (settings.url && settings.url.indexOf('get_subject_load_data') !== -1) {
      setTimeout(loadTeacherCapacity, 300);
    }
  });
});

function showSlAlert(type, title, message) {
  var icon, color, bgColor, borderColor;
  if (type === 'error') {
    icon = 'fa-times-circle'; color = '#c0392b'; bgColor = '#fdf2f2'; borderColor = '#e74c3c';
  } else if (type === 'warning') {
    icon = 'fa-exclamation-triangle'; color = '#e67e22'; bgColor = '#fef9e7'; borderColor = '#f0ad4e';
  } else if (type === 'success') {
    icon = 'fa-check-circle'; color = '#27ae60'; bgColor = '#eafaf1'; borderColor = '#2ecc71';
  } else {
    icon = 'fa-info-circle'; color = '#2980b9'; bgColor = '#ebf5fb'; borderColor = '#3498db';
  }
  var lines = (message || '').split('\n\n');
  var bodyHtml = '';
  for (var i = 0; i < lines.length; i++) {
    bodyHtml += '<div style="margin-bottom:8px;padding:10px 14px;background:#fff;border-left:4px solid '
      + borderColor + ';border-radius:3px;font-size:13px;line-height:1.5;">'
      + lines[i].replace(/\n/g, '<br>') + '</div>';
  }
  var html = '<div class="modal fade" id="sl-alert-modal" tabindex="-1">'
    + '<div class="modal-dialog"><div class="modal-content">'
    + '<div class="modal-header" style="background:' + bgColor + ';border-bottom:2px solid ' + borderColor + ';">'
    + '<button type="button" class="close" data-dismiss="modal">&times;</button>'
    + '<h4 class="modal-title" style="color:' + color + ';"><i class="fa ' + icon + '"></i> ' + title + '</h4>'
    + '</div>'
    + '<div class="modal-body" style="padding:15px;">' + bodyHtml + '</div>'
    + '<div class="modal-footer">'
    + '<button type="button" class="btn btn-default" data-dismiss="modal">OK</button>'
    + '</div></div></div></div>';
  $('#sl-alert-modal').remove();
  $('body').append(html);
  $('#sl-alert-modal').modal('show');
}
</script>
</div>
