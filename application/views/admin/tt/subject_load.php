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

<!-- Select Class Card -->
<div class="box box-primary" style="border-radius:8px;overflow:hidden;margin-bottom:16px;">
  <div class="box-header" style="background:linear-gradient(135deg,#3c8dbc,#357ca5);padding:12px 16px;">
    <h3 class="box-title" style="color:#fff;font-size:15px;">
      <i class="fa fa-book"></i>&nbsp; Subject Load Configuration
    </h3>
    <small style="color:rgba(255,255,255,.7);display:block;margin-top:2px;font-size:11px;">
      Configure how many periods/week each subject has and which teachers to assign
    </small>
  </div>
  <div class="box-body" style="background:#f9fbfd;padding:16px;">
    <div class="row" style="align-items:flex-end;">
      <div class="col-md-3 col-sm-6">
        <label style="font-size:12px;font-weight:600;color:#555;margin-bottom:4px;">Department</label>
        <select class="form-control input-sm" id="dept_filter" style="border-radius:6px;">
          <option value="">— All —</option>
          <?php foreach ($departments as $d): ?>
          <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['department_name'] ?? $d['name'] ?? ''); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 col-sm-6">
        <label style="font-size:12px;font-weight:600;color:#555;margin-bottom:4px;">Class <span class="text-danger">*</span></label>
        <select class="form-control input-sm" id="sl_class_id" style="border-radius:6px;">
          <option value="">— Select Class —</option>
          <?php foreach ($classlist as $cls): ?>
          <option value="<?php echo $cls['id']; ?>"><?php echo htmlspecialchars($cls['class']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 col-sm-4">
        <label style="font-size:12px;font-weight:600;color:#555;margin-bottom:4px;">Section <span class="text-danger">*</span></label>
        <select class="form-control input-sm" id="sl_section_id" style="border-radius:6px;">
          <option value="">— Select —</option>
        </select>
      </div>
      <div class="col-md-4 col-sm-8" style="padding-top:20px;">
        <div style="display:flex;gap:8px;align-items:center;">
          <button class="btn btn-primary" id="btn-load-subjects" style="border-radius:6px;font-weight:600;white-space:nowrap;">
            <i class="fa fa-search"></i>&nbsp; Load Subjects
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Subject Load Container (shown after load) -->
<div id="subject-load-container" style="display:none;">

  <!-- Toolbar -->
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
    <div style="display:flex;align-items:center;gap:8px;">
      <h4 style="margin:0;font-size:15px;font-weight:700;color:#2c3e50;">
        <i class="fa fa-list" style="color:#3498db;"></i>&nbsp; Subjects
      </h4>
      <span id="sl-status-badge"></span>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
      <button class="btn btn-sm" id="btn-toggle-copy-panel"
        style="border-radius:6px;background:#f0f4f8;border:1px solid #dce0e8;color:#555;">
        <i class="fa fa-copy"></i>&nbsp; Copy from Section
      </button>
      <button class="btn btn-success btn-sm" id="btn-save-loads"
        style="border-radius:6px;font-weight:600;padding:6px 18px;">
        <i class="fa fa-save"></i>&nbsp; Save All
      </button>
    </div>
  </div>

  <!-- Copy-from panel -->
  <div id="copy-from-panel" style="display:none;background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:14px 16px;margin-bottom:12px;">
    <div class="row" style="align-items:flex-end;">
      <div class="col-md-3">
        <label style="font-size:12px;font-weight:600;">Source Class</label>
        <select class="form-control input-sm" id="cp_class_id">
          <option value="">— Select Class —</option>
          <?php foreach ($classlist as $cls): ?>
          <option value="<?php echo $cls['id']; ?>"><?php echo htmlspecialchars($cls['class']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label style="font-size:12px;font-weight:600;">Source Section</label>
        <select class="form-control input-sm" id="cp_section_id">
          <option value="">— Select Section —</option>
        </select>
      </div>
      <div class="col-md-3">
        <button class="btn btn-warning btn-sm" id="btn-apply-copy" style="margin-top:20px;border-radius:6px;">
          <i class="fa fa-arrow-down"></i> Apply P/Week Only
        </button>
      </div>
      <div class="col-md-3">
        <small class="text-muted" style="display:block;margin-top:22px;">
          Copies <em>Periods/Week</em> for matching subjects only. Teacher assignments not copied.
        </small>
      </div>
    </div>
  </div>

  <!-- Teacher workload warnings -->
  <div id="sl-teacher-warnings" style="display:none;margin-bottom:12px;"></div>

  <!-- Subject cards -->
  <form id="subject-load-form">
    <input type="hidden" name="class_id"   id="sl_class_id_hidden">
    <input type="hidden" name="section_id" id="sl_section_id_hidden">
    <div id="subject-load-rows" style="padding:0;"></div>
  </form>

  <!-- Save footer -->
  <div style="margin-top:16px;padding:14px 16px;background:#f8fdf8;border:1px solid #c8e6c9;border-radius:8px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
    <small class="text-muted">
      <i class="fa fa-info-circle text-success"></i>
      Changes take effect on the next Auto Generate run.
    </small>
    <button class="btn btn-success" id="btn-save-loads-bottom" style="border-radius:6px;font-weight:600;">
      <i class="fa fa-save"></i>&nbsp; Save All Changes
    </button>
  </div>

</div>

<div id="subject-load-empty" class="text-center" style="display:none;padding:50px;color:#ccc;">
  <i class="fa fa-exclamation-circle fa-3x" style="display:block;margin-bottom:10px;"></i>
  <strong style="color:#999;">Could not load subjects.</strong><br>
  <small>Please check the class/section selection and try again.</small>
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
    var total  = $pools.length;
    if (!total) { $('#sl-status-badge').html(''); return; }
    var configured = $pools.filter(function(){ return $(this).val() && $(this).val().length > 0; }).length;
    var pct   = Math.round(configured / total * 100);
    var color = (configured === total) ? '#27ae60' : (configured > 0 ? '#f39c12' : '#e74c3c');
    var bg    = (configured === total) ? '#eafaf1' : (configured > 0 ? '#fef9e7' : '#fdf2f2');
    $('#sl-status-badge').html(
      '<span style="display:inline-flex;align-items:center;gap:6px;background:'+bg+';color:'+color+';border:1px solid '+color+';border-radius:20px;padding:2px 10px;font-size:11px;font-weight:700;">'
      + '<i class="fa fa-users"></i> '+configured+'/'+total+' teacher pools ('+pct+'%)</span>'
    );
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
          $('#subject-load-rows .sl-teacher-pool').select2({ width: '100%', placeholder: '-- Select Teachers --', allowClear: true });
          $('#sl-add-subject-picker').select2({ width: '100%', placeholder: 'Select subjects to add...', allowClear: true });
          $('#subject-load-container').show();
          updateStatusBadge();
        } else {
          $('#subject-load-empty').show();
        }
      },'json');
  }

  $('#btn-load-subjects').on('click', loadSubjects);

  // Remove subject card
  $(document).on('click', '.btn-remove-sl-row', function(){
    var $card    = $(this).closest('.sl-card');
    var load_id  = $(this).data('load-id');
    var subName  = $card.find('.sl-card-subject strong').text().trim() || 'this subject';

    swal({
      title: 'Remove Subject?',
      text:  'Remove "' + subName + '" from this class? This cannot be undone.',
      type:  'warning',
      showCancelButton:   true,
      confirmButtonColor: '#e74c3c',
      cancelButtonColor:  '#6c757d',
      confirmButtonText:  '<i class="fa fa-trash"></i>&nbsp; Yes, Remove',
      cancelButtonText:   'Cancel',
      closeOnConfirm: false,
      closeOnCancel:  true,
      animation: 'slide-from-top'
    }, function(isConfirm){
      if (!isConfirm) return;
      swal.close();
      if (load_id > 0) {
        $card.css('opacity', '0.5');
        $.post('<?php echo site_url('admin/tt/delete_subject_load_row'); ?>',
          {id: load_id, [csrf_name]: csrf_val},
          function(res){
            if (res.status === '1') {
              $card.slideUp(300, function(){ $(this).remove(); updateStatusBadge(); });
              toastr.success('"' + subName + '" removed successfully.');
            } else {
              $card.css('opacity','1');
              swal({title:'Error', text:'Could not remove subject. Please try again.', type:'error'});
            }
          }, 'json');
      } else {
        $card.slideUp(300, function(){ $(this).remove(); updateStatusBadge(); });
        toastr.success('"' + subName + '" removed.');
      }
    });
  });

  // Toggle config (card design)
  $(document).on('click', '.btn-sl-toggle', function(){
    var sgs = $(this).data('sgs');
    var $cfg = $('.sl-card-config[data-sgs="'+sgs+'"]');
    var $icon = $(this).find('i');
    $cfg.slideToggle(180);
    $icon.toggleClass('fa-cog fa-times');
    $(this).closest('.sl-card').toggleClass('sl-expanded');
  });

  // Legend toggle
  $(document).on('click', '#btn-toggle-legend', function(){
    var $legend = $('#sl-legend');
    $legend.toggleClass('open');
    $(this).find('.fa-chevron-down, .fa-chevron-up').toggleClass('fa-chevron-down fa-chevron-up');
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
    $('.sl-main-row').each(function(){
      var sgs = $(this).data('sgs');
      var ppw = parseInt($(this).find('[name$="[periods_per_week]"]').val()) || 0;
      var $cfg = $('.sl-card-config[data-sgs="'+sgs+'"]');
      var $chk = $cfg.find('[name$="[min_per_day]"]');
      if (!$chk.length) return;
      var $item = $chk.closest('.sl-cfg-item');
      $item.find('.min-day-warn').remove();
      if (ppw < workingDays) {
        if ($chk.is(':checked')) $chk.prop('checked', false);
        $chk.prop('disabled', true);
        $item.append('<div class="min-day-warn text-muted" style="font-size:9px;margin-top:1px;" title="' + ppw + ' p/week < ' + workingDays + ' days"><i class="fa fa-ban"></i> Needs ≥' + workingDays + ' P/W</div>');
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

    // Clear existing inline workload bars
    $('.sl-workload-bar').empty();

    var formLoads = {};
    // card-based: iterate sl-main-row divs
    $('.sl-main-row').each(function(){
      var ppw  = parseInt($(this).find('[name$="[periods_per_week]"]').val()) || 0;
      var $pool = $(this).find('.sl-teacher-pool');
      if (!$pool.length) return;
      var tids = $pool.val() || [];
      $.each(tids, function(i, tid) { formLoads[tid] = (formLoads[tid] || 0) + ppw; });
    });

    if (!Object.keys(formLoads).length) { $('#sl-teacher-warnings').hide(); return; }

    var summaryRows = [];
    $.each(formLoads, function(tid) {
      var cap = teacherCap[tid]; if (!cap) return;
      var total = cap.total_ppw;
      var pct = cap.max_week > 0 ? Math.round(total / cap.max_week * 100) : 0;
      var overWeek = total > cap.max_week;
      var dayCapacity = cap.max_day * cap.day_count;
      var overDay    = total > dayCapacity;
      var overSlots  = total > cap.avail_slots && cap.unavail > 0;
      var color = (overWeek || overSlots || overDay) ? 'danger' : (pct >= 85 ? 'warning' : (pct >= 60 ? 'info' : 'success'));
      var barColor = color === 'danger' ? '#e74c3c' : color === 'warning' ? '#f39c12' : color === 'info' ? '#3498db' : '#27ae60';
      var issues = [];
      if (overWeek)  issues.push('OVER weekly cap by ' + (total - cap.max_week));
      if (overSlots) issues.push(cap.unavail + ' unavail slots');
      if (overDay)   issues.push('exceeds ' + dayCapacity + ' day-cap');
      summaryRows.push({ tid: tid, name: cap.name, total: total, max: cap.max_week, pct: pct, color: color, barColor: barColor, barWidth: Math.min(pct,100), issues: issues });

      // Render inline workload bar inside each card that uses this teacher
      $('.sl-main-row').each(function(){
        var $pool2 = $(this).find('.sl-teacher-pool');
        if (!$pool2.length) return;
        var match = false;
        $.each($pool2.val() || [], function(i,v){ if (v == tid) match = true; });
        if (!match) return;
        var sgs = $(this).data('sgs');
        var $wb = $('#sl-wb-' + sgs);
        $wb.append(
          '<div class="sl-workload-row">'
          + '<span class="sl-workload-name" title="' + cap.name + '">' + cap.name + '</span>'
          + '<div class="sl-workload-track"><div class="sl-workload-fill" style="width:' + Math.min(pct,100) + '%;background:' + barColor + ';"></div></div>'
          + '<span class="sl-workload-pct" style="color:' + barColor + ';">' + total + '/' + cap.max_week + ' <small style="font-weight:400;color:#aaa;">(' + pct + '%)</small></span>'
          + (issues.length ? '<span style="color:#e74c3c;font-size:9px;margin-left:4px;" title="' + issues.join(', ') + '"><i class="fa fa-exclamation-triangle"></i></span>' : '')
          + '</div>'
        );
      });
    });

    summaryRows.sort(function(a,b){ return (b.color==='danger'?1:0)-(a.color==='danger'?1:0) || b.pct-a.pct; });
    var hasDanger = summaryRows.some(function(r){ return r.color === 'danger'; });
    var html = '<div class="alert alert-' + (hasDanger?'danger':'info') + '" style="padding:10px 14px;font-size:12px;margin-bottom:0;">'
      + '<strong><i class="fa fa-users"></i> Overall Teacher Workload</strong><div style="margin-top:6px;">';
    $.each(summaryRows, function(i,r){
      html += '<div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">'
        + '<span style="min-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;" title="'+r.name+'">'+r.name+'</span>'
        + '<div style="flex:1;background:#eee;border-radius:3px;height:10px;"><div style="width:'+r.barWidth+'%;height:100%;border-radius:3px;background:'+r.barColor+';"></div></div>'
        + '<span style="min-width:80px;text-align:right;font-size:11px;color:'+r.barColor+';">'+r.total+'/'+r.max+' ('+r.pct+'%)</span></div>';
      if (r.issues.length) html += '<div style="font-size:10px;color:#e74c3c;margin:-2px 0 4px 158px;"><i class="fa fa-exclamation-triangle"></i> '+r.issues.join(' | ')+'</div>';
    });
    html += '</div></div>';
    $('#sl-teacher-warnings').html(html).show();
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
