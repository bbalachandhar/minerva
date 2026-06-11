<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Auto Timetable <small>Setup checklist and status overview</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Dashboard</li>
    </ol>
</section>
<section class="content">

<?php
$steps = [
  [
    'icon'  => 'fa-clock-o',
    'title' => 'Period Setup',
    'url'   => site_url('admin/tt/periods'),
    'ok'    => $period_count > 0,
    'desc'  => $period_count > 0 ? "{$period_count} teaching + {$break_count} break periods configured" : "No periods set up yet",
    'color' => $period_count > 0 ? 'green' : 'red',
  ],
  [
    'icon'  => 'fa-building',
    'title' => 'Rooms',
    'url'   => site_url('admin/tt/rooms'),
    'ok'    => $room_count > 0,
    'desc'  => $room_count > 0 ? "{$room_count} room(s) configured" : "No rooms configured",
    'color' => $room_count > 0 ? 'green' : 'yellow',
  ],
  [
    'icon'  => 'fa-users',
    'title' => 'Batches',
    'url'   => site_url('admin/tt/batches'),
    'ok'    => true,
    'desc'  => $batch_count > 0 ? "{$batch_count} batch(es) configured" : "No batches — only needed for split groups",
    'color' => 'green',
  ],
  [
    'icon'  => 'fa-paint-brush',
    'title' => 'Subject Colours',
    'url'   => site_url('admin/tt/subject_colors'),
    'ok'    => $colored_subjects > 0,
    'desc'  => $colored_subjects > 0 ? "{$colored_subjects} subject(s) have custom colours" : "No custom colours set (optional)",
    'color' => 'green',
  ],
  [
    'icon'  => 'fa-table',
    'title' => 'Subject Load',
    'url'   => site_url('admin/tt/subject_load'),
    'ok'    => $load_class_count > 0 && $missing_teacher === 0,
    'desc'  => $load_class_count > 0
      ? "{$load_class_count} class-section(s) configured, {$total_load_rows} total rows" . ($missing_teacher > 0 ? " — ⚠ {$missing_teacher} rows missing teacher" : " ✓")
      : "Subject load not configured yet",
    'color' => ($load_class_count > 0 && $missing_teacher === 0) ? 'green' : ($load_class_count > 0 ? 'yellow' : 'red'),
  ],
  [
    'icon'  => 'fa-user',
    'title' => 'Teacher Constraints',
    'url'   => site_url('admin/tt/teacher_constraints'),
    'ok'    => $teacher_const_ct > 0,
    'desc'  => $teacher_const_ct > 0 ? "{$teacher_const_ct} teacher constraint(s) set" : "No constraints set (optional)",
    'color' => 'green',
  ],
  [
    'icon'  => 'fa-magic',
    'title' => 'Auto Generate',
    'url'   => site_url('admin/tt/generate'),
    'ok'    => !empty($last_gen),
    'desc'  => !empty($last_gen)
      ? "Last run: " . date('d M Y h:i A', strtotime($last_gen->generated_at)) . " — Quality: {$last_gen->quality_score}%"
      : "Not generated yet",
    'color' => !empty($last_gen) ? ($last_gen->quality_score >= 90 ? 'green' : 'yellow') : 'red',
  ],
];

$color_map = [
  'green'  => ['box' => 'box-success', 'icon_bg' => '#00a65a'],
  'yellow' => ['box' => 'box-warning', 'icon_bg' => '#f39c12'],
  'red'    => ['box' => 'box-danger',  'icon_bg' => '#dd4b39'],
];
?>

<div class="row">
<?php foreach ($steps as $i => $step):
  $cm = $color_map[$step['color']];
?>
<div class="col-md-4 col-sm-6">
  <a href="<?php echo $step['url']; ?>" style="text-decoration:none;">
    <div class="box <?php echo $cm['box']; ?>" style="cursor:pointer;">
      <div class="box-header with-border" style="padding:10px 15px;">
        <h3 class="box-title" style="font-size:15px;">
          <span style="display:inline-block;width:28px;height:28px;border-radius:50%;background:<?php echo $cm['icon_bg']; ?>;text-align:center;line-height:28px;margin-right:8px;">
            <i class="fa <?php echo $step['icon']; ?>" style="color:#fff;font-size:13px;"></i>
          </span>
          <?php echo $i+1; ?>. <?php echo htmlspecialchars($step['title']); ?>
        </h3>
        <div class="box-tools pull-right">
          <i class="fa <?php echo $step['ok'] ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-warning'; ?> fa-lg"></i>
        </div>
      </div>
      <div class="box-body" style="padding:8px 15px 10px;">
        <small class="text-muted"><?php echo htmlspecialchars($step['desc']); ?></small>
      </div>
    </div>
  </a>
</div>
<?php endforeach; ?>
</div>

<?php if (!empty($last_confirmed)): ?>
<div class="row">
  <div class="col-md-12">
    <div class="callout callout-success">
      <h4><i class="fa fa-calendar-check-o"></i> Active Timetable</h4>
      <p>Confirmed on <?php echo date('d M Y h:i A', strtotime($last_confirmed->confirmed_at)); ?>
        — Quality score: <strong><?php echo $last_confirmed->quality_score; ?>%</strong>
        | Placed: <?php echo $last_confirmed->total_placed; ?> / <?php echo $last_confirmed->total_required; ?> cards
      </p>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-road"></i> Workflow Guide</h3></div>
      <div class="box-body">
        <ol style="font-size:13px;line-height:2;">
          <li>Set up <a href="<?php echo site_url('admin/tt/periods'); ?>">Periods</a> (teaching slots + breaks for the day)</li>
          <li>Add <a href="<?php echo site_url('admin/tt/rooms'); ?>">Rooms</a> (classrooms, labs) and optional <a href="<?php echo site_url('admin/tt/batches'); ?>">Batches</a></li>
          <li>Configure <a href="<?php echo site_url('admin/tt/subject_load'); ?>">Subject Loads</a> — assign teachers + periods/week for every class</li>
          <li>Set <a href="<?php echo site_url('admin/tt/teacher_constraints'); ?>">Teacher Constraints</a> and unavailability if needed</li>
          <li>Run <a href="<?php echo site_url('admin/tt/generate'); ?>">Auto Generate</a> → review → confirm</li>
          <li>View results in <a href="<?php echo site_url('admin/tt/class_grid'); ?>">Class Grid</a> or <a href="<?php echo site_url('admin/tt/teacher_view'); ?>">Teacher View</a></li>
        </ol>
      </div>
    </div>
  </div>
</div>

</section>
</div>
