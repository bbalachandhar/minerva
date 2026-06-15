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
            <div class="col-md-6">
              <div class="form-group">
                <label>
                  <input type="checkbox" name="allow_saturday" value="1">
                  Include Saturday
                </label><br>
                <small class="text-muted">Check if Saturday is a working day for this institution.</small>
              </div>
            </div>
          </div>

          <hr>
          <!-- Complexity & Strictness -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><i class="fa fa-tachometer"></i> Generation Size</label>
                <div class="btn-group btn-group-justified gen-radio-group" data-toggle="buttons">
                  <label class="btn btn-default active"><input type="radio" name="gen_size" value="normal" checked> Normal</label>
                  <label class="btn btn-default"><input type="radio" name="gen_size" value="large"> Large</label>
                  <label class="btn btn-default"><input type="radio" name="gen_size" value="huge"> Huge</label>
                </div>
                <small class="text-muted" id="gen-size-hint">Quick single pass — good for most timetables.</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label><i class="fa fa-sliders"></i> Constraint Strictness</label>
                <div class="btn-group btn-group-justified gen-radio-group" data-toggle="buttons">
                  <label class="btn btn-default"><input type="radio" name="gen_strictness" value="relaxed"> Relaxed</label>
                  <label class="btn btn-default active"><input type="radio" name="gen_strictness" value="normal" checked> Normal</label>
                  <label class="btn btn-default"><input type="radio" name="gen_strictness" value="strict"> Strict</label>
                </div>
                <small class="text-muted" id="gen-strict-hint">Balanced constraint enforcement.</small>
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

  var sizeHints = {
    normal: 'Quick single pass — good for most timetables.',
    large:  '3 retry passes with reordering — better quality for complex setups.',
    huge:   '10 passes exhaustive search — best quality, slower (may take 10-30s).'
  };
  var strictHints = {
    relaxed: 'Fewer constraints enforced — maximises slot fill-rate.',
    normal:  'Balanced constraint enforcement.',
    strict:  'All constraints hard-enforced — may leave more slots empty but no violations.'
  };
  $('input[name=gen_size]').on('change', function(){
    $('#gen-size-hint').text(sizeHints[$(this).val()] || '');
  });
  $('input[name=gen_strictness]').on('change', function(){
    $('#gen-strict-hint').text(strictHints[$(this).val()] || '');
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

  function showResult(res, isDryRun) {
    if (res.status === '1') {
      var color = res.quality_score >= 90 ? 'success' : (res.quality_score >= 70 ? 'warning' : 'danger');
      var cardsLeft = res.cards_left || (res.total_required - res.total_placed);

      var html = '<div style="padding:10px;">';

      if (isDryRun) {
        html += '<div class="alert alert-info"><i class="fa fa-flask"></i> <strong>Dry Run — nothing was saved</strong></div>';
      }

      html += '<div class="row text-center" style="margin-bottom:10px;">'
        + '<div class="col-xs-4"><div style="font-size:32px;font-weight:700;" class="text-success">' + res.total_placed + '</div><small>Cards Placed</small></div>'
        + '<div class="col-xs-4"><div style="font-size:32px;font-weight:700;" class="text-' + (cardsLeft > 0 ? 'danger' : 'success') + '">' + cardsLeft + '</div><small>Cards Left</small></div>'
        + '<div class="col-xs-4"><div style="font-size:32px;font-weight:700;" class="text-' + color + '">' + res.quality_score + '%</div><small>Quality</small></div>'
        + '</div>';

      html += '<table class="table table-condensed table-bordered" style="font-size:12px;">'
        + '<tr><td>Total Required</td><td><strong>' + res.total_required + '</strong></td></tr>'
        + '<tr><td>Conditions Broken</td><td><strong class="text-' + (res.total_conflicts > 0 ? 'danger' : 'success') + '">' + res.total_conflicts + '</strong></td></tr>'
        + '</table>';

      // Per-class breakdown
      if (res.class_stats && res.class_stats.length > 0) {
        var classMap = {};
        $('.scope-chk').each(function(){
          var k = $(this).data('class')+'_'+$(this).data('section');
          classMap[k] = ($(this).data('class-name')||'Class '+$(this).data('class'))+' '+($(this).data('section-name')||'Sec '+$(this).data('section'));
        });
        html += '<div style="margin-bottom:8px;"><strong style="font-size:12px;"><i class="fa fa-list"></i> Class-by-Class</strong>'
          +'<div style="max-height:130px;overflow-y:auto;margin-top:4px;">';
        $.each(res.class_stats, function(i, cs){
          var pct   = cs.required > 0 ? Math.round(cs.placed/cs.required*100) : 100;
          var cl    = pct === 100 ? 'success' : (pct >= 70 ? 'warning' : 'danger');
          var label = classMap[cs.class_id+'_'+cs.section_id] || ('Class '+cs.class_id+' Sec '+cs.section_id);
          html += '<div style="font-size:11px;display:flex;justify-content:space-between;padding:1px 0;">'
            + '<span>'+label+'</span>'
            + '<span><span class="label label-'+cl+'">'+cs.placed+'/'+cs.required+' ('+pct+'%)</span></span></div>';
        });
        html += '</div></div>';
      }

      if (res.total_conflicts > 0) {
        html += '<div class="alert alert-warning text-left" style="font-size:11px;max-height:160px;overflow-y:auto;">'
          + '<strong><i class="fa fa-exclamation-triangle"></i> Unplaced (' + res.total_conflicts + '):</strong><ul style="margin-top:5px;">';
        $.each(res.conflicts, function(i, c){
          html += '<li><strong>' + c.subject + '</strong> — ' + c.staff + '<br><small class="text-muted">' + c.reason + '</small></li>';
        });
        html += '</ul></div>';
      }

      if (!isDryRun) {
        html += '<a href="<?php echo site_url('admin/tt/preview/'); ?>' + res.log_id + '" class="btn btn-primary btn-block btn-lg">'
          + '<i class="fa fa-eye"></i> Review & Confirm</a>';
      }

      html += '</div>';

      var icon = isDryRun ? '<i class="fa fa-flask text-info"></i> Test Result'
                           : '<i class="fa fa-check text-success"></i> Generation Complete';
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
