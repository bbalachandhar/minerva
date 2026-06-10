<?php if (isset($msg)) { echo $msg; } ?>
<section class="content-header">
    <h1>Preview Draft Timetable <small>Review before confirming</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li><a href="<?php echo site_url('admin/tt/generate'); ?>">Auto Generate</a></li>
        <li class="active">Preview</li>
    </ol>
</section>
<section class="content">

<!-- Quality summary bar -->
<?php
$score = $log->quality_score;
$color = $score >= 90 ? 'success' : ($score >= 70 ? 'warning' : 'danger');
?>
<div class="row">
  <div class="col-md-3">
    <div class="info-box bg-<?php echo $color; ?>">
      <span class="info-box-icon"><i class="fa fa-percent"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Quality Score</span>
        <span class="info-box-number"><?php echo $score; ?>%</span>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="info-box bg-aqua">
      <span class="info-box-icon"><i class="fa fa-check"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Placed</span>
        <span class="info-box-number"><?php echo $log->total_placed; ?> / <?php echo $log->total_required; ?></span>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="info-box bg-<?php echo $log->total_conflicts > 0 ? 'red' : 'green'; ?>">
      <span class="info-box-icon"><i class="fa fa-exclamation-triangle"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Conflicts</span>
        <span class="info-box-number"><?php echo $log->total_conflicts; ?></span>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Generated</span>
        <span class="info-box-number" style="font-size:14px;"><?php echo date('d M h:i A', strtotime($log->generated_at)); ?></span>
      </div>
    </div>
  </div>
</div>

<!-- Action buttons -->
<div class="row" style="margin-bottom:15px;">
  <div class="col-md-12">
    <a href="<?php echo site_url('admin/tt/confirm_draft/'.$log->id); ?>"
       onclick="return confirm('Confirm and save this timetable? Existing non-locked entries will be replaced.')"
       class="btn btn-success btn-lg">
      <i class="fa fa-check-circle"></i> Confirm & Save Timetable
    </a>
    <a href="<?php echo site_url('admin/tt/discard_draft/'.$log->id); ?>"
       onclick="return confirm('Discard this draft?')"
       class="btn btn-danger btn-lg" style="margin-left:10px;">
      <i class="fa fa-trash"></i> Discard Draft
    </a>
    <a href="<?php echo site_url('admin/tt/generate'); ?>" class="btn btn-default btn-lg" style="margin-left:10px;">
      <i class="fa fa-arrow-left"></i> Back to Generate
    </a>
  </div>
</div>

<!-- Conflicts panel -->
<?php if (!empty($conflicts)): ?>
<div class="box box-warning collapsed-box">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-exclamation-triangle text-warning"></i>
      <?php echo count($conflicts); ?> Unplaced Subjects — these could NOT be scheduled automatically</h3>
    <div class="box-tools"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button></div>
  </div>
  <div class="box-body" style="display:none;">
    <table class="table table-sm table-bordered" style="font-size:12px;">
      <thead><tr><th>Subject</th><th>Teacher</th><th>Placement</th><th>Reason</th></tr></thead>
      <tbody>
        <?php foreach ($conflicts as $c): ?>
        <tr class="warning">
          <td><?php echo htmlspecialchars($c['subject']); ?></td>
          <td><?php echo htmlspecialchars($c['staff']); ?></td>
          <td><?php echo htmlspecialchars($c['placement']); ?></td>
          <td><?php echo htmlspecialchars($c['reason']); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="alert alert-info" style="font-size:12px;margin-top:10px;">
      <i class="fa fa-info-circle"></i>
      To resolve conflicts: <strong>(1)</strong> Check teacher workload/unavailability, <strong>(2)</strong> Add more period slots, or <strong>(3)</strong> Reduce periods_per_week for affected subjects. Then re-generate.
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Draft preview per class -->
<?php $type_class = ['theory'=>'slot-theory','practical'=>'slot-practical','project'=>'slot-project','other'=>'slot-other']; ?>

<?php foreach ($draft as $key => $class_data): ?>
<div class="box box-default">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-graduation-cap"></i>
      <?php echo htmlspecialchars($class_data['class'].' — '.$class_data['section']); ?>
    </h3>
    <div class="box-tools"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button></div>
  </div>
  <div class="box-body table-responsive p-0">
    <?php
    // Build entry map for this class
    $day_map = [];
    $period_ids_used = [];
    foreach ($class_data['entries'] as $e) {
      $day_map[$e->day][$e->period_id][] = $e;
      $period_ids_used[$e->period_id] = $e->sort_order;
    }
    asort($period_ids_used);
    ?>
    <table class="table table-bordered tt-grid" style="font-size:12px;">
      <thead>
        <tr>
          <th class="time-col">Period</th>
          <?php foreach ($days as $dk => $dv): ?><th><?php echo $dk; ?></th><?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($periods as $period): ?>
        <?php if ($period->is_break): ?>
        <tr><td class="time-col"><small><?php echo $period->name; ?></small></td>
          <td colspan="<?php echo count($days); ?>" class="text-center text-muted" style="background:#fffde7;"><i class="fa fa-coffee"></i> <?php echo htmlspecialchars($period->break_label ?: $period->name); ?></td>
        </tr>
        <?php else: ?>
        <tr>
          <td class="time-col"><strong><?php echo htmlspecialchars($period->name); ?></strong><br><small><?php echo date('h:i', strtotime($period->start_time)); ?></small></td>
          <?php foreach ($days as $dk => $dv): ?>
          <td style="text-align:center;vertical-align:middle;min-height:50px;">
            <?php $entries_here = $day_map[$dk][$period->id] ?? []; ?>
            <?php foreach ($entries_here as $e): ?>
              <?php $tc = $type_class[strtolower($e->subject_type ?? 'other')] ?? 'slot-other'; ?>
              <span class="slot-tag <?php echo $tc; ?>"><?php echo htmlspecialchars($e->subject_code ?: $e->subject_name); ?></span><br>
              <small style="font-size:10px;"><?php echo htmlspecialchars($e->staff_name.' '.$e->staff_surname); ?></small>
              <?php if ($e->room_name): ?><br><small style="font-size:10px;color:#888;"><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($e->room_name); ?></small><?php endif; ?>
              <?php if ($e->batch_name): ?><br><span class="label label-info" style="font-size:9px;">Batch <?php echo $e->batch_name; ?></span><?php endif; ?>
            <?php endforeach; ?>
            <?php if (empty($entries_here)): ?><span class="text-muted">—</span><?php endif; ?>
          </td>
          <?php endforeach; ?>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endforeach; ?>

<?php if (empty($draft)): ?>
<div class="alert alert-warning text-center"><i class="fa fa-exclamation-triangle"></i> No draft entries were generated. Please check your subject load configuration and try again.</div>
<?php endif; ?>

<!-- Confirm button at bottom too -->
<div class="text-center" style="margin:20px 0;">
  <a href="<?php echo site_url('admin/tt/confirm_draft/'.$log->id); ?>"
     onclick="return confirm('Confirm and save this timetable?')"
     class="btn btn-success btn-lg">
    <i class="fa fa-check-circle"></i> Confirm & Save Timetable
  </a>
</div>

</section>
