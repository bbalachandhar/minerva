<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
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
<?php
$failures  = array_values(array_filter($conflicts, fn($c) => ($c['type'] ?? ($c['placement'] !== 'On1' ? 'no_slot' : 'on1')) === 'no_slot'));
$warnings  = array_values(array_filter($conflicts, fn($c) => ($c['type'] ?? ($c['placement'] === 'On1'  ? 'on1' : 'no_slot')) === 'on1'));
$td        = $teacher_diagnostics ?? [];
?>

<?php if (!empty($failures)): ?>
<div class="box box-danger">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-times-circle text-danger"></i>
      <?php echo count($failures); ?> Subject(s) could NOT be placed — actual missing period(s) in the grid</h3>
    <div class="box-tools"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button></div>
  </div>
  <div class="box-body">
    <table class="table table-bordered table-condensed" style="font-size:12px;">
      <thead class="bg-danger"><tr><th>Subject</th><th>Teacher</th><th>Placement</th><th>Specific Reason</th></tr></thead>
      <tbody>
        <?php foreach ($failures as $c): ?>
        <tr class="danger">
          <td><?php echo htmlspecialchars($c['subject']); ?></td>
          <td><?php echo htmlspecialchars($c['staff']); ?></td>
          <td><?php echo htmlspecialchars($c['placement']); ?></td>
          <td><?php echo htmlspecialchars($c['reason']); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php if (!empty($td)): ?>
    <h4 style="margin-top:16px;margin-bottom:8px;"><i class="fa fa-user-times text-danger"></i> Teacher Booking Details</h4>
    <?php foreach ($td as $tid => $info): ?>
    <div class="panel panel-default" style="margin-bottom:10px;">
      <div class="panel-heading" style="padding:8px 12px;">
        <strong><?php echo htmlspecialchars($info['name'] ?? 'Unknown'); ?></strong>
        &nbsp;—&nbsp;
        Total assigned: <strong><?php echo $info['total_ppw'] ?? 0; ?> periods/week</strong>
        <?php if (!empty($info['max_ppw'])): ?>
          &nbsp;|&nbsp; Weekly cap: <strong><?php echo $info['max_ppw']; ?></strong>
        <?php else: ?>
          &nbsp;|&nbsp; <span class="text-muted">No weekly cap set</span>
        <?php endif; ?>
      </div>
      <div class="panel-body" style="padding:0;">
        <table class="table table-condensed table-bordered" style="margin:0;font-size:12px;">
          <thead><tr><th>Class</th><th>Section</th><th>Subject</th><th>Periods/Week</th></tr></thead>
          <tbody>
            <?php foreach (($info['assignments'] ?? []) as $a): ?>
            <tr>
              <td><?php echo htmlspecialchars($a->class_name); ?></td>
              <td><?php echo htmlspecialchars($a->section_name); ?></td>
              <td><?php echo htmlspecialchars($a->subject_name); ?></td>
              <td><strong><?php echo $a->periods_per_week; ?></strong></td>
            </tr>
            <?php endforeach; ?>
            <tr class="active">
              <td colspan="3"><strong>Total</strong></td>
              <td><strong><?php echo $info['total_ppw'] ?? 0; ?></strong></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <?php endforeach; ?>
    <div class="alert alert-danger" style="font-size:12px;margin-top:8px;">
      <i class="fa fa-lightbulb-o"></i>
      <strong>How to fix:</strong> If the teacher teaches many classes, generate <strong>all classes together</strong> in one run — the generator will distribute their periods optimally. Alternatively, set a Teacher Constraint (max periods/week) and reduce their load.
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($warnings)): ?>
<?php
// Group On1 warnings by subject to show a clean summary
$on1_summary = [];
foreach ($warnings as $w) {
    $key = $w['subject'] . '||' . $w['staff'];
    if (!isset($on1_summary[$key])) {
        $on1_summary[$key] = ['subject' => $w['subject'], 'staff' => $w['staff'], 'days' => [], 'reason' => $w['reason']];
    }
    // Extract day name from reason
    preg_match('/(?:On1 (?:warning|violation)[^:]*: [^\s]+ [^\s]+ [^\s]+ )(\w+)/', $w['reason'], $m);
    if (!empty($m[1])) $on1_summary[$key]['days'][] = $m[1];
}
?>
<div class="box box-warning collapsed-box">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-info-circle text-warning"></i>
      <?php echo count($warnings); ?> Distribution Warning(s) — subjects ARE placed, just not on every day</h3>
    <div class="box-tools"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button></div>
  </div>
  <div class="box-body" style="display:none;">
    <div class="alert alert-warning" style="font-size:12px;margin-bottom:10px;">
      <i class="fa fa-check-circle"></i>
      <strong>These are NOT missing periods.</strong> The subjects below have the <em>Min 1/day</em> (On1) setting enabled, meaning they should appear on every working day. But their period count is too low to cover all days — so some days are skipped. The grid is still correctly filled.
    </div>
    <table class="table table-bordered table-condensed" style="font-size:12px;">
      <thead><tr><th>Subject</th><th>Teacher</th><th>What to do</th></tr></thead>
      <tbody>
        <?php foreach ($on1_summary as $row): ?>
        <tr class="warning">
          <td><?php echo htmlspecialchars($row['subject']); ?></td>
          <td><?php echo htmlspecialchars($row['staff']); ?></td>
          <td><?php
            // Detect impossible On1 from reason text
            if (strpos($row['reason'], 'cannot appear on every day') !== false || strpos($row['reason'], 'Disable') !== false) {
                echo '<span class="text-danger"><i class="fa fa-times"></i> <strong>Disable Min 1/day</strong> — not enough periods/week to appear every day</span>';
            } else {
                echo '<span class="text-warning"><i class="fa fa-exclamation-triangle"></i> Teacher/class unavailable on some days — regenerating all classes together may help</span>';
            }
          ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="alert alert-info" style="font-size:12px;margin-top:6px;">
      <i class="fa fa-wrench"></i>
      Go to <strong>Subject Load setup</strong> and uncheck <em>Min 1/day</em> for subjects with fewer periods/week than working days (Library=2, Hindi=3, Computer Science=4, Physical Education=4). This will eliminate all these warnings.
    </div>
  </div>
</div>
<?php endif; ?>

<?php if (empty($conflicts)): ?>
<div class="alert alert-success"><i class="fa fa-check-circle"></i> All subjects placed successfully — no conflicts.</div>
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
              <?php if (!empty($e->is_free_period)): ?>
              <span class="slot-tag" style="background:#27ae60;color:#fff;"><?php echo htmlspecialchars($e->free_period_label ?: 'Free'); ?></span>
              <?php else: ?>
              <?php $tc = $type_class[strtolower($e->subject_type ?? 'other')] ?? 'slot-other'; ?>
              <span class="slot-tag <?php echo $tc; ?>"><?php echo htmlspecialchars($e->subject_code ?: $e->subject_name); ?></span><br>
              <small style="font-size:10px;"><?php echo htmlspecialchars($e->staff_name.' '.$e->staff_surname); ?></small>
              <?php endif; ?>
              <?php if ($e->room_name): ?><br><small style="font-size:10px;color:#888;"><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($e->room_name); ?></small><?php endif; ?>
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
</div>
