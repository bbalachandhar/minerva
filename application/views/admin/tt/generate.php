<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Auto Generate Timetable <small>Let the system build the timetable automatically</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Auto Generate</li>
    </ol>
</section>
<section class="content">
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-cog"></i> Generation Settings</h3>
      </div>
      <div class="box-body">
        <form id="generate-form">

          <!-- Class Scope -->
          <div class="form-group">
            <label><strong>Scope — Which Classes to Generate?</strong></label>
            <div class="row" style="margin-bottom:8px;">
              <div class="col-md-4">
                <select class="form-control" id="scope_dept">
                  <option value="">All Departments</option>
                  <?php foreach ($departments as $d): ?><option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['department_name']); ?></option><?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <button type="button" class="btn btn-default btn-block" id="btn-select-all"><i class="fa fa-check-square-o"></i> Select All</button>
              </div>
              <div class="col-md-3">
                <button type="button" class="btn btn-default btn-block" id="btn-deselect-all"><i class="fa fa-square-o"></i> Deselect All</button>
              </div>
            </div>

            <div id="class-scope-list" style="max-height:300px;overflow-y:auto;border:1px solid #ddd;border-radius:4px;padding:10px;">
              <?php foreach ($classlist as $cls): ?>
              <div class="class-scope-item" data-dept="<?php echo $cls['department_id']; ?>" style="padding:6px 0;border-bottom:1px solid #f4f4f4;">
                <strong><?php echo htmlspecialchars($cls['class']); ?></strong>
                <div id="sections-<?php echo $cls['id']; ?>" style="padding-left:20px;margin-top:4px;">
                  <small class="text-muted"><i class="fa fa-spinner fa-spin"></i> Loading sections...</small>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <small class="text-muted">Select the class-sections to include. Locked entries in selected classes will be preserved.</small>
          </div>

          <hr>
          <!-- Generation Options -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><i class="fa fa-calendar"></i> Working Days</label>
                <div>
                  <?php
                  $day_list = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                  foreach ($day_list as $day):
                  ?>
                  <label style="margin-right:15px;">
                    <input type="checkbox" name="allow_days[]" value="<?php echo $day; ?>" <?php echo ($day !== 'Saturday') ? 'checked' : ''; ?>> <?php echo $day; ?>
                  </label>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Max Same Subject Per Day</label>
                <select class="form-control" name="max_same_subject_day">
                  <option value="1">1 — Never repeat same subject on same day (recommended)</option>
                  <option value="2">2 — Allow at most 2 same subject per day</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>
                  <input type="checkbox" name="spread_evenly" value="1" checked>
                  Spread subjects evenly across the week
                </label><br>
                <small class="text-muted">Tries to distribute each subject across different days.</small>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>
                  <input type="checkbox" name="allow_saturday" value="1">
                  Include Saturday
                </label><br>
                <small class="text-muted">Check if Saturday is a working day.</small>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>
                  <input type="checkbox" name="fill_free_periods" value="1" checked>
                  Fill Empty Cells
                </label><br>
                <small class="text-muted">Backfill any empty slots with an available subject or Free Period.</small>
              </div>
            </div>
          </div>

          <hr>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><i class="fa fa-clock-o"></i> Solver Time Limit</label>
                <select class="form-control" name="time_limit">
                  <?php for ($m = 1; $m <= 10; $m++): ?>
                  <option value="<?php echo $m * 60; ?>" <?php echo $m == 3 ? 'selected' : ''; ?>><?php echo $m; ?> min<?php echo $m == 3 ? ' — Recommended' : ''; ?></option>
                  <?php endfor; ?>
                </select>
                <small class="text-muted">More time = better quality. Schools with zero free periods may need 5-10 min.</small>
              </div>
            </div>
          </div>

          <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i>
            <strong>Important:</strong> Auto-generation will <strong>replace</strong> existing non-locked entries for selected classes.
            Any manually placed or locked entries will be preserved.
            You will see a <strong>preview</strong> before anything is saved.
          </div>

          <div class="row" style="margin-bottom:10px;">
            <div class="col-md-12">
              <button type="button" class="btn btn-info btn-block" id="btn-verify">
                <i class="fa fa-check-circle"></i> Verify Constraints &amp; Readiness
              </button>
              <small class="text-muted text-center" style="display:block;margin-top:4px;">Checks loads, teacher capacity and slot availability before generating</small>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <button type="button" class="btn btn-default btn-lg btn-block" id="btn-test-generate">
                <i class="fa fa-flask"></i> Test (Dry Run)
              </button>
              <small class="text-muted text-center" style="display:block;margin-top:4px;">See stats without saving anything</small>
            </div>
            <div class="col-md-6">
              <button type="submit" class="btn btn-success btn-lg btn-block" id="btn-generate">
                <i class="fa fa-magic"></i> Generate Timetable
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Progress and result appear below the form (full width) -->
<div class="row" id="progress-result-row" style="display:none;">
  <div class="col-md-12">
    <div class="box box-info" id="progress-box" style="display:none;">
      <div class="box-header"><h3 class="box-title"><i class="fa fa-cog fa-spin"></i> Generating...</h3></div>
      <div class="box-body text-center">
        <div class="progress progress-striped active" style="margin:20px 0;">
          <div class="progress-bar progress-bar-info" style="width:100%"></div>
        </div>
        <p>Scheduling subjects across the week.<br>This may take a few seconds...</p>
      </div>
    </div>
    <div class="box box-default" id="result-box" style="display:none;">
      <div class="box-header" id="result-header"><h3 class="box-title">Result</h3></div>
      <div class="box-body" id="result-body"></div>
    </div>
  </div>
</div>

<?php if (!empty($gen_logs)): ?>
<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-header"><h3 class="box-title"><i class="fa fa-history"></i> Recent Runs</h3></div>
      <div class="box-body p-0">
        <table class="table table-sm table-hover" style="font-size:12px;">
          <thead><tr style="background:#f4f4f4;">
            <th>Date / By</th><th style="width:80px;text-align:center;">Quality</th><th style="width:120px;">Status</th>
          </tr></thead>
          <tbody>
            <?php foreach ($gen_logs as $log): ?>
            <tr>
              <td><?php echo date('d M Y h:i A', strtotime($log->generated_at)); ?><br>
                  <small class="text-muted">By <?php echo htmlspecialchars($log->generated_by_name.' '.($log->generated_by_surname??'')); ?></small></td>
              <td class="text-center"><?php
                $color = $log->quality_score >= 90 ? 'success' : ($log->quality_score >= 70 ? 'warning' : 'danger');
                echo '<span class="label label-'.$color.'">'.$log->quality_score.'%</span>';
              ?></td>
              <td><?php if ($log->confirmed_at): ?>
                  <span class="label label-success">Confirmed</span>
                <?php elseif ($log->status === 'completed'): ?>
                  <a href="<?php echo site_url('admin/tt/preview/'.$log->id); ?>" class="btn btn-xs btn-primary">Preview</a>
                <?php else: ?>
                  <span class="label label-default"><?php echo $log->status; ?></span>
                <?php endif; ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
</section>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';

  $('#scope_dept').select2({ placeholder: '-- All Departments --', allowClear: true, width: '100%', minimumResultsForSearch: 1 });

  // Filter class scope list by department
  $('#scope_dept').on('change', function(){
    var dept = $(this).val();
    $('.class-scope-item').each(function(){
      $(this).toggle(!dept || $(this).data('dept') == dept);
    });
  });

  // Load sections for each class
  <?php foreach ($classlist as $cls): ?>
  $.post('<?php echo site_url('admin/tt/get_sections_by_class'); ?>',
    {class_id: <?php echo $cls['id']; ?>, [csrf_name]: csrf_val},
    function(res){
      var html = '';
      $.each(res, function(i, s){
        html += '<label style="margin-right:12px;font-weight:normal;">'
          + '<input type="checkbox" class="scope-chk" name="class_scope[]" value="<?php echo $cls['id']; ?>_'+s.section_id+'" '
          + 'data-class="<?php echo $cls['id']; ?>" data-section="'+s.section_id+'"'
          + ' data-class-name="<?php echo addslashes(htmlspecialchars($cls['class'])); ?>" data-section-name="'+s.section+'"'
          + '> '
          + s.section + '</label>';
      });
      $('#sections-<?php echo $cls['id']; ?>').html(html || '<small class="text-muted">No sections</small>');
    },'json');
  <?php endforeach; ?>

  $('#btn-select-all').on('click', function(){
    $('.scope-chk').prop('checked', true);
  });
  $('#btn-deselect-all').on('click', function(){
    $('.scope-chk').prop('checked', false);
  });

  $('#generate-form').on('submit', function(e){
    e.preventDefault();
    var scope = [];
    $('.scope-chk:checked').each(function(){
      scope.push({class_id: $(this).data('class'), section_id: $(this).data('section')});
    });
    if (scope.length === 0) {
      alert('Please select at least one class-section.'); return;
    }

    $('#progress-result-row').show();
    $('#progress-box').show();
    $('#result-box').hide();
    $('#generate-form').find('button[type=submit]').prop('disabled', true);

    var formData = $(this).serialize() + '&' + csrf_name + '=' + csrf_val;
    formData += '&class_scope=' + encodeURIComponent(JSON.stringify(scope));

    $.post('<?php echo site_url('admin/tt/run_generate'); ?>', formData, function(res){
      $('#progress-box').hide();
      $('#generate-form').find('button[type=submit]').prop('disabled', false);
      showResult(res, false);
    },'json').fail(function(){
      $('#progress-box').hide();
      alert('Server error. Please try again.');
    });
  });

  $('#btn-test-generate').on('click', function(){
    var scope = [];
    $('.scope-chk:checked').each(function(){
      scope.push({class_id: $(this).data('class'), section_id: $(this).data('section')});
    });
    if (scope.length === 0) { alert('Please select at least one class-section.'); return; }

    $('#progress-result-row').show();
    $('#progress-box').show();
    $('#result-box').hide();
    $('#btn-test-generate').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Testing...');

    var formData = $('#generate-form').serialize() + '&' + csrf_name + '=' + csrf_val;
    formData += '&class_scope=' + encodeURIComponent(JSON.stringify(scope));

    $.post('<?php echo site_url('admin/tt/test_generate'); ?>', formData, function(res){
      $('#progress-box').hide();
      $('#btn-test-generate').prop('disabled', false).html('<i class="fa fa-flask"></i> Test (Dry Run)');
      showResult(res, true);
    },'json').fail(function(){
      $('#progress-box').hide();
      alert('Server error. Please try again.');
    });
  });

  $('#btn-verify').on('click', function(){
    var scope = [];
    $('.scope-chk:checked').each(function(){
      scope.push({class_id: $(this).data('class'), section_id: $(this).data('section')});
    });
    if (scope.length === 0) { alert('Please select at least one class-section.'); return; }
    var $btn = $(this).prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Checking...');
    var formData = $('#generate-form').serialize() + '&' + csrf_name + '=' + csrf_val;
    formData += '&class_scope=' + encodeURIComponent(JSON.stringify(scope));
    $.post('<?php echo site_url('admin/tt/verify_constraints'); ?>', formData, function(res){
      $btn.prop('disabled',false).html('<i class="fa fa-check-circle"></i> Verify Constraints &amp; Readiness');
      var html = '<div style="padding:10px;">';
      var icon  = res.ok ? '<i class="fa fa-check-circle text-success"></i> Ready to Generate' : '<i class="fa fa-exclamation-triangle text-warning"></i> Warnings Found';
      html += '<div class="alert alert-'+(res.ok?'success':'warning')+' text-left" style="font-size:12px;">';
      var hasTeacherOverload = false;
      $.each(res.items, function(i, item){
        html += '<div style="margin-bottom:4px;"><i class="fa '+(item.ok?'fa-check text-success':'fa-times text-danger')+'" style="width:16px;"></i> '+item.msg+'</div>';
        if (!item.ok && item.msg.indexOf('periods/week but max') !== -1) hasTeacherOverload = true;
      });
      if (hasTeacherOverload) {
        html += '<div style="margin-top:8px;padding:8px;background:#fff3cd;border-radius:4px;border-left:4px solid #f39c12;">'
              + '<i class="fa fa-info-circle text-warning"></i> '
              + 'One or more teachers are over their weekly cap. Use the '
              + '<a href="<?php echo site_url('admin/tt/teacher_workload_dashboard'); ?>" target="_blank"><strong>Workload Dashboard</strong></a>'
              + ' to see the full breakdown and reassign subjects before generating.'
              + '</div>';
      }
      html += '</div></div>';
      $('#result-header').find('.box-title').html(icon);
      $('#result-body').html(html);
      $('#result-box').show();
    },'json').fail(function(){
      $btn.prop('disabled',false).html('<i class="fa fa-check-circle"></i> Verify Constraints &amp; Readiness');
      alert('Server error during verification.');
    });
  });

  function nl2br(str) {
    return (str||'').replace(/\n/g, '<br>');
  }

  function showResult(res, isDryRun) {
    if (res.status === '1') {
      var color = res.quality_score >= 100 ? 'success' : (res.quality_score >= 90 ? 'warning' : 'danger');
      var cardsLeft = res.cards_left || (res.total_required - res.total_placed);
      var is100 = (cardsLeft === 0);

      var html = '<div style="padding:10px;">';

      if (isDryRun) {
        html += '<div class="alert alert-info" style="margin-bottom:10px;"><i class="fa fa-flask"></i> <strong>Dry Run</strong> — nothing was saved. Fix any issues below, then Generate.</div>';
      }

      // ── Summary banner ──
      if (is100) {
        html += '<div class="alert alert-success text-center" style="font-size:16px;padding:15px;">'
          + '<i class="fa fa-check-circle fa-2x"></i><br>'
          + '<strong style="font-size:24px;">100% — Perfect Timetable!</strong><br>'
          + 'All ' + res.total_required + ' periods placed successfully in ' + (res.solve_time_seconds||'?') + ' seconds.'
          + '</div>';
      } else {
        html += '<div class="alert alert-' + color + ' text-center" style="padding:15px;">'
          + '<div style="font-size:36px;font-weight:700;">' + res.quality_score + '%</div>'
          + '<div style="font-size:14px;">' + res.total_placed + ' of ' + res.total_required + ' periods placed &mdash; '
          + '<strong>' + cardsLeft + ' could not be placed</strong></div>'
          + '<div style="font-size:12px;margin-top:5px;color:#666;">Solve time: ' + (res.solve_time_seconds||'?') + ' seconds</div>'
          + '</div>';
      }

      // ── ISSUES section (the main focus) ──
      var errors = [], warnings = [];
      $.each(res.issues || [], function(i, issue) {
        if (issue.severity === 'warning') warnings.push(issue); else errors.push(issue);
      });

      if (errors.length > 0) {
        html += '<div style="margin-bottom:15px;">'
          + '<h4 style="color:#c0392b;margin-bottom:10px;"><i class="fa fa-exclamation-triangle"></i> '
          + errors.length + ' Issue' + (errors.length > 1 ? 's' : '') + ' Blocking 100% Placement</h4>'
          + '<p style="font-size:13px;color:#666;margin-bottom:10px;">Fix these issues one by one, then run Test again. Each fix will improve the placement rate.</p>';
        $.each(errors, function(i, issue) {
          html += '<div style="border:1px solid #e74c3c;border-left:4px solid #e74c3c;border-radius:4px;padding:12px;margin-bottom:10px;background:#fdf2f2;">'
            + '<div style="font-size:14px;font-weight:700;color:#c0392b;margin-bottom:6px;">'
            + '<i class="fa fa-times-circle"></i> ' + (issue.title||'Issue') + '</div>'
            + '<div style="font-size:12px;color:#555;margin-bottom:8px;">' + nl2br(issue.detail||'') + '</div>'
            + '<div style="background:#fff;border:1px solid #ddd;border-radius:3px;padding:10px;font-size:12px;">'
            + '<strong style="color:#27ae60;"><i class="fa fa-wrench"></i> How to Fix:</strong><br>'
            + '<div style="margin-top:4px;color:#333;">' + nl2br(issue.fix||'') + '</div>'
            + '</div></div>';
        });
        html += '</div>';
      }

      if (warnings.length > 0) {
        html += '<div style="margin-bottom:15px;">'
          + '<h4 style="color:#f39c12;margin-bottom:10px;"><i class="fa fa-info-circle"></i> '
          + warnings.length + ' Warning' + (warnings.length > 1 ? 's' : '') + '</h4>';
        $.each(warnings, function(i, issue) {
          html += '<div style="border:1px solid #f0ad4e;border-left:4px solid #f0ad4e;border-radius:4px;padding:12px;margin-bottom:10px;background:#fef9e7;">'
            + '<div style="font-size:13px;font-weight:700;color:#e67e22;margin-bottom:4px;">'
            + '<i class="fa fa-exclamation-circle"></i> ' + (issue.title||'Warning') + '</div>'
            + '<div style="font-size:12px;color:#555;margin-bottom:6px;">' + nl2br(issue.detail||'') + '</div>'
            + '<div style="font-size:11px;color:#888;"><i class="fa fa-wrench"></i> ' + nl2br(issue.fix||'') + '</div>'
            + '</div>';
        });
        html += '</div>';
      }

      // ── Unplaced entries ──
      var unplaced = res.unplaced || [];
      if (unplaced.length > 0 && errors.length === 0) {
        html += '<div style="margin-bottom:12px;">'
          + '<h4 style="color:#c0392b;"><i class="fa fa-times-circle"></i> ' + unplaced.length + ' Unplaced Subject' + (unplaced.length > 1 ? 's' : '') + '</h4>';
        $.each(unplaced, function(i, u) {
          html += '<div style="font-size:12px;padding:6px 10px;border-left:3px solid #e74c3c;margin-bottom:4px;background:#fdf2f2;">'
            + '<strong>' + (u.subject||'?') + '</strong>'
            + (u.staff ? ' (Teacher: ' + u.staff + ')' : '')
            + '<br><span style="color:#888;">' + (u.reason||'') + '</span></div>';
        });
        html += '</div>';
      }

      // ── Class-by-class breakdown (collapsed) ──
      if (res.class_stats && res.class_stats.length > 0) {
        var classMap = {};
        $('.scope-chk').each(function(){
          var k = $(this).data('class')+'_'+$(this).data('section');
          classMap[k] = ($(this).data('class-name')||'Class '+$(this).data('class'))+' '+($(this).data('section-name')||'Sec '+$(this).data('section'));
        });
        var hasIncomplete = false;
        $.each(res.class_stats, function(i, cs) {
          if (cs.placed < cs.required) hasIncomplete = true;
        });
        html += '<div style="margin-bottom:12px;">'
          + '<a data-toggle="collapse" href="#class-breakdown" style="font-size:13px;font-weight:600;cursor:pointer;">'
          + '<i class="fa fa-list"></i> Class-by-Class Breakdown '
          + (hasIncomplete ? '<span class="label label-warning">some incomplete</span>' : '<span class="label label-success">all complete</span>')
          + ' <i class="fa fa-chevron-down" style="font-size:10px;"></i></a>'
          + '<div class="collapse" id="class-breakdown" style="margin-top:6px;max-height:200px;overflow-y:auto;">';
        $.each(res.class_stats, function(i, cs){
          var pct   = cs.required > 0 ? Math.round(cs.placed/cs.required*100) : 100;
          var cl    = pct === 100 ? 'success' : (pct >= 70 ? 'warning' : 'danger');
          var label = classMap[cs.class_id+'_'+cs.section_id] || ('Class '+cs.class_id+' Sec '+cs.section_id);
          html += '<div style="font-size:11px;display:flex;justify-content:space-between;padding:2px 0;border-bottom:1px solid #f4f4f4;">'
            + '<span>'+label+'</span>'
            + '<span><span class="label label-'+cl+'">'+cs.placed+'/'+cs.required+' ('+pct+'%)</span></span></div>';
        });
        html += '</div></div>';
      }

      // ── Action button ──
      if (!isDryRun) {
        if (is100) {
          html += '<a href="<?php echo site_url('admin/tt/preview/'); ?>' + res.log_id + '" class="btn btn-success btn-block btn-lg" style="margin-top:10px;">'
            + '<i class="fa fa-eye"></i> Review & Confirm Timetable</a>';
        } else {
          html += '<a href="<?php echo site_url('admin/tt/preview/'); ?>' + res.log_id + '" class="btn btn-primary btn-block" style="margin-top:10px;">'
            + '<i class="fa fa-eye"></i> Review Draft (' + res.quality_score + '% placed)</a>'
            + '<p class="text-center text-muted" style="font-size:11px;margin-top:5px;">Fix the issues above and regenerate for a better result.</p>';
        }
      }

      html += '</div>';

      var icon = isDryRun
        ? '<i class="fa fa-flask text-info"></i> Test Result'
        : (is100 ? '<i class="fa fa-check-circle text-success"></i> Generation Complete'
                  : '<i class="fa fa-exclamation-triangle text-warning"></i> Generation Complete — Issues Found');
      $('#result-header').find('.box-title').html(icon);
      $('#result-body').html(html);
      $('#result-box').show();
    } else {
      $('#result-header').find('.box-title').html('<i class="fa fa-times text-danger"></i> Failed');
      $('#result-body').html('<div class="alert alert-danger">' + (res.message || 'Generation failed.') + '</div>');
      $('#result-box').show();
    }
  }
});
</script>
</div>
