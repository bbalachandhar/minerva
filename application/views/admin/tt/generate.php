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
  <div class="col-md-8">
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
                  <?php foreach ($departments as $d): ?><option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name']); ?></option><?php endforeach; ?>
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
              <div class="class-scope-item" style="padding:6px 0;border-bottom:1px solid #f4f4f4;">
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

          <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i>
            <strong>Important:</strong> Auto-generation will <strong>replace</strong> existing non-locked entries for selected classes.
            Any manually placed or locked entries will be preserved.
            You will see a <strong>preview</strong> before anything is saved.
          </div>

          <button type="submit" class="btn btn-success btn-lg btn-block" id="btn-generate">
            <i class="fa fa-magic"></i> Generate Timetable
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <!-- Generation progress -->
    <div class="box box-info" id="progress-box" style="display:none;">
      <div class="box-header"><h3 class="box-title"><i class="fa fa-cog fa-spin"></i> Generating...</h3></div>
      <div class="box-body text-center">
        <div class="progress progress-striped active" style="margin:20px 0;">
          <div class="progress-bar progress-bar-info" style="width:100%"></div>
        </div>
        <p>Scheduling subjects across the week.<br>This may take a few seconds...</p>
      </div>
    </div>

    <!-- Result card -->
    <div class="box box-default" id="result-box" style="display:none;">
      <div class="box-header" id="result-header"><h3 class="box-title">Result</h3></div>
      <div class="box-body" id="result-body"></div>
    </div>

    <!-- Recent runs -->
    <?php if (!empty($gen_logs)): ?>
    <div class="box box-default">
      <div class="box-header"><h3 class="box-title"><i class="fa fa-history"></i> Recent Runs</h3></div>
      <div class="box-body p-0">
        <table class="table table-sm table-hover" style="font-size:12px;">
          <tbody>
            <?php foreach ($gen_logs as $log): ?>
            <tr>
              <td>
                <?php echo date('d M Y h:i A', strtotime($log->generated_at)); ?><br>
                <small>By <?php echo htmlspecialchars($log->generated_by_name.' '.($log->generated_by_surname??'')); ?></small>
              </td>
              <td class="text-center">
                <?php
                $color = $log->quality_score >= 90 ? 'success' : ($log->quality_score >= 70 ? 'warning' : 'danger');
                echo '<span class="label label-'.$color.'">'.$log->quality_score.'%</span>';
                ?>
              </td>
              <td>
                <?php if ($log->confirmed_at): ?>
                  <span class="label label-success">Confirmed</span>
                <?php elseif ($log->status === 'completed'): ?>
                  <a href="<?php echo site_url('admin/tt/preview/'.$log->id); ?>" class="btn btn-xs btn-primary">Preview</a>
                <?php else: ?>
                  <span class="label label-default"><?php echo $log->status; ?></span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
</section>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';

  // Load sections for each class
  <?php foreach ($classlist as $cls): ?>
  $.post('<?php echo site_url('admin/tt/get_sections_by_class'); ?>',
    {class_id: <?php echo $cls['id']; ?>, [csrf_name]: csrf_val},
    function(res){
      var html = '';
      $.each(res, function(i, s){
        html += '<label style="margin-right:12px;font-weight:normal;">'
          + '<input type="checkbox" class="scope-chk" name="class_scope[]" value="<?php echo $cls['id']; ?>_'+s.section_id+'" '
          + 'data-class="<?php echo $cls['id']; ?>" data-section="'+s.section_id+'"> '
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

    $('#progress-box').show();
    $('#result-box').hide();
    $('#generate-form').find('button[type=submit]').prop('disabled', true);

    var formData = $(this).serialize() + '&' + csrf_name + '=' + csrf_val;
    // Add scope as JSON
    formData += '&class_scope=' + encodeURIComponent(JSON.stringify(scope));

    $.post('<?php echo site_url('admin/tt/run_generate'); ?>', formData, function(res){
      $('#progress-box').hide();
      $('#generate-form').find('button[type=submit]').prop('disabled', false);

      if (res.status === '1') {
        var color = res.quality_score >= 90 ? 'success' : (res.quality_score >= 70 ? 'warning' : 'danger');
        var html = '<div class="text-center" style="padding:10px;">'
          + '<div style="font-size:48px;font-weight:700;" class="text-'+color+'">' + res.quality_score + '%</div>'
          + '<div>Quality Score</div>'
          + '<hr>'
          + '<table class="table table-sm" style="font-size:13px;">'
          + '<tr><td>Required</td><td><strong>' + res.total_required + '</strong></td></tr>'
          + '<tr><td>Placed</td><td><strong class="text-success">' + res.total_placed + '</strong></td></tr>'
          + '<tr><td>Conflicts</td><td><strong class="text-danger">' + res.total_conflicts + '</strong></td></tr>'
          + '</table>';

        if (res.total_conflicts > 0) {
          html += '<div class="alert alert-warning text-left" style="font-size:12px;max-height:150px;overflow-y:auto;">'
            + '<strong>Unplaced subjects:</strong><ul>';
          $.each(res.conflicts, function(i, c){
            html += '<li>' + c.subject + ' — ' + c['class_id'] + '/' + c['section_id'] + ' ('+c.reason+')</li>';
          });
          html += '</ul></div>';
        }

        html += '<a href="<?php echo site_url('admin/tt/preview/'); ?>' + res.log_id + '" class="btn btn-primary btn-block btn-lg">'
          + '<i class="fa fa-eye"></i> Review & Confirm</a></div>';

        $('#result-header').find('.box-title').html('<i class="fa fa-check text-success"></i> Generation Complete');
        $('#result-body').html(html);
        $('#result-box').show();
      } else {
        $('#result-header').find('.box-title').html('<i class="fa fa-times text-danger"></i> Failed');
        $('#result-body').html('<div class="alert alert-danger">' + (res.message || 'Generation failed.') + '</div>');
        $('#result-box').show();
      }
    },'json').fail(function(){
      $('#progress-box').hide();
      alert('Server error. Please try again.');
    });
  });
});
</script>
