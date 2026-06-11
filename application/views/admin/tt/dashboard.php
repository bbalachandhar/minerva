<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1><i class="fa fa-calendar-check-o" style="color:#3c8dbc;margin-right:8px;"></i>Timetable Setup <small>Status overview &amp; configuration guide</small></h1>
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
    'num'   => 1,
    'icon'  => 'fa-clock-o',
    'title' => 'Period Setup',
    'sub'   => 'Teaching slots & breaks',
    'url'   => site_url('admin/tt/periods'),
    'status'=> $period_count > 0 ? 'done' : 'required',
    'badge' => $period_count > 0 ? 'Done' : 'Required',
    'desc'  => $period_count > 0
      ? "<strong>{$period_count}</strong> teaching + <strong>{$break_count}</strong> break period(s) configured"
      : "Define the daily schedule — morning to afternoon teaching slots",
    'action'=> $period_count > 0 ? 'Edit Periods' : 'Set Up Periods',
  ],
  [
    'num'   => 2,
    'icon'  => 'fa-building',
    'title' => 'Rooms',
    'sub'   => 'Classrooms, labs & halls',
    'url'   => site_url('admin/tt/rooms'),
    'status'=> $room_count > 0 ? 'done' : 'warn',
    'badge' => $room_count > 0 ? 'Done' : 'Recommended',
    'desc'  => $room_count > 0
      ? "<strong>{$room_count}</strong> room(s) available for scheduling"
      : "Add rooms so the generator can assign venues to lessons",
    'action'=> $room_count > 0 ? 'Manage Rooms' : 'Add Rooms',
  ],
  [
    'num'   => 3,
    'icon'  => 'fa-users',
    'title' => 'Batches',
    'sub'   => 'Split groups for labs etc.',
    'url'   => site_url('admin/tt/batches'),
    'status'=> 'optional',
    'badge' => $batch_count > 0 ? "{$batch_count} Batch(es)" : 'Optional',
    'desc'  => $batch_count > 0
      ? "<strong>{$batch_count}</strong> batch(es) — parallel lab sessions supported"
      : "Only needed if classes split into sub-groups for labs",
    'action'=> 'Manage Batches',
  ],
  [
    'num'   => 4,
    'icon'  => 'fa-paint-brush',
    'title' => 'Subject Colours',
    'sub'   => 'Visual grid cell colours',
    'url'   => site_url('admin/tt/subject_colors'),
    'status'=> 'optional',
    'badge' => $colored_subjects > 0 ? "{$colored_subjects} Coloured" : 'Optional',
    'desc'  => $colored_subjects > 0
      ? "<strong>{$colored_subjects}</strong> subject(s) have custom colours set"
      : "Colour-code subjects for easier timetable grid reading",
    'action'=> 'Set Colours',
  ],
  [
    'num'   => 5,
    'icon'  => 'fa-table',
    'title' => 'Subject Load',
    'sub'   => 'Teachers & periods per class',
    'url'   => site_url('admin/tt/subject_load'),
    'status'=> ($load_class_count > 0 && $missing_teacher === 0) ? 'done' : ($load_class_count > 0 ? 'warn' : 'required'),
    'badge' => ($load_class_count > 0 && $missing_teacher === 0) ? 'Done' : ($load_class_count > 0 ? 'Incomplete' : 'Required'),
    'desc'  => $load_class_count > 0
      ? "<strong>{$load_class_count}</strong> class-section(s) &middot; <strong>{$total_load_rows}</strong> subjects" . ($missing_teacher > 0 ? " &mdash; <span style='color:#c0392b;'><i class='fa fa-exclamation-triangle'></i> {$missing_teacher} missing teacher</span>" : "")
      : "Assign subjects, teachers and periods/week to every class-section",
    'action'=> $load_class_count > 0 ? 'Edit Subject Load' : 'Configure Load',
  ],
  [
    'num'   => 6,
    'icon'  => 'fa-user-times',
    'title' => 'Teacher Constraints',
    'sub'   => 'Limits & unavailable slots',
    'url'   => site_url('admin/tt/teacher_constraints'),
    'status'=> 'optional',
    'badge' => $teacher_const_ct > 0 ? "{$teacher_const_ct} Set" : 'Optional',
    'desc'  => $teacher_const_ct > 0
      ? "<strong>{$teacher_const_ct}</strong> teacher(s) have max-period/unavailability rules"
      : "Set max periods per day and mark unavailable slots per teacher",
    'action'=> 'Manage Constraints',
  ],
  [
    'num'   => 7,
    'icon'  => 'fa-magic',
    'title' => 'Auto Generate',
    'sub'   => 'Run the scheduler',
    'url'   => site_url('admin/tt/generate'),
    'status'=> !empty($last_gen) ? ($last_gen->quality_score >= 90 ? 'done' : 'warn') : 'required',
    'badge' => !empty($last_gen) ? "Quality {$last_gen->quality_score}%" : 'Not Run',
    'desc'  => !empty($last_gen)
      ? "Last run " . date('d M Y, h:i A', strtotime($last_gen->generated_at)) . " &mdash; Placed <strong>{$last_gen->total_placed}</strong>&thinsp;/&thinsp;{$last_gen->total_required} lessons"
      : "Generate the timetable once periods + subject load are set up",
    'action'=> !empty($last_gen) ? 'Re-Generate' : 'Generate Now',
  ],
];

$sc_map = [
  'done'     => ['border'=>'#00a65a','circle'=>'#00a65a','badge_bg'=>'#d4edda','badge_fg'=>'#155724','tick'=>'fa-check',   'bg'=>'#f6fffb'],
  'warn'     => ['border'=>'#f39c12','circle'=>'#f39c12','badge_bg'=>'#fff3cd','badge_fg'=>'#7d5a00','tick'=>'fa-exclamation','bg'=>'#fffdf5'],
  'required' => ['border'=>'#e74c3c','circle'=>'#e74c3c','badge_bg'=>'#fde8e8','badge_fg'=>'#8b1a1a','tick'=>'fa-times',  'bg'=>'#fff8f8'],
  'optional' => ['border'=>'#bdc3c7','circle'=>'#95a5a6','badge_bg'=>'#eaedef','badge_fg'=>'#5d6d74','tick'=>'fa-minus',  'bg'=>'#fafbfc'],
];

$steps_complete = count(array_filter($steps, function($s){ return $s['status'] === 'done'; }));
$pct            = round(($steps_complete / count($steps)) * 100);
$bar_color      = $pct >= 71 ? '#00a65a' : ($pct >= 43 ? '#f39c12' : '#e74c3c');
?>

<!-- ══ Active Timetable Banner ══════════════════════════════════════════ -->
<?php if (!empty($last_confirmed)): ?>
<div class="row">
  <div class="col-md-12">
    <div style="background:linear-gradient(135deg,#0e7a45 0%,#00a65a 100%);border-radius:10px;padding:18px 24px;margin-bottom:22px;color:#fff;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
      <div style="display:flex;align-items:center;gap:16px;">
        <div style="background:rgba(255,255,255,.18);border-radius:50%;width:52px;height:52px;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
          <i class="fa fa-calendar-check-o" style="font-size:22px;"></i>
        </div>
        <div>
          <div style="font-size:15px;font-weight:700;letter-spacing:.2px;">Active Timetable Confirmed</div>
          <div style="font-size:12px;opacity:.88;margin-top:3px;">
            Confirmed <?php echo date('d M Y, h:i A', strtotime($last_confirmed->confirmed_at)); ?>
            &ensp;&bull;&ensp; Quality <strong><?php echo $last_confirmed->quality_score; ?>%</strong>
            &ensp;&bull;&ensp; <?php echo $last_confirmed->total_placed; ?>&thinsp;/&thinsp;<?php echo $last_confirmed->total_required; ?> lessons placed
          </div>
        </div>
      </div>
      <div style="display:flex;gap:8px;flex-shrink:0;">
        <a href="<?php echo site_url('admin/tt/class_grid'); ?>" style="background:#fff;color:#00a65a;border:none;border-radius:6px;padding:7px 16px;font-size:12px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
          <i class="fa fa-th-large"></i> Class Grid
        </a>
        <a href="<?php echo site_url('admin/tt/teacher_view'); ?>" style="background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.4);border-radius:6px;padding:7px 16px;font-size:12px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
          <i class="fa fa-user"></i> Teacher View
        </a>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ══ Quick Stats ═══════════════════════════════════════════════════════ -->
<div class="row" style="margin-bottom:4px;">
  <?php
  $stats = [
    ['val'=> $period_count,     'lbl'=> 'Periods / Day', 'icon'=> 'fa-clock-o',    'color'=> '#3c8dbc'],
    ['val'=> $room_count,       'lbl'=> 'Rooms',          'icon'=> 'fa-building',   'color'=> '#605ca8'],
    ['val'=> $load_class_count, 'lbl'=> 'Classes Loaded', 'icon'=> 'fa-table',      'color'=> '#00a65a'],
    ['val'=> !empty($last_gen) ? $last_gen->quality_score.'%' : '—',
                                'lbl'=> 'Last Quality',   'icon'=> 'fa-star',       'color'=> !empty($last_gen) ? ($last_gen->quality_score >= 90 ? '#00a65a' : '#f39c12') : '#bdc3c7'],
  ];
  foreach ($stats as $st): ?>
  <div class="col-xs-6 col-sm-3">
    <div style="background:#fff;border-radius:10px;padding:16px;border:1px solid #e8ecf0;margin-bottom:18px;display:flex;align-items:center;gap:14px;">
      <div style="background:<?php echo $st['color']; ?>18;border-radius:10px;width:44px;height:44px;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
        <i class="fa <?php echo $st['icon']; ?>" style="color:<?php echo $st['color']; ?>;font-size:18px;"></i>
      </div>
      <div>
        <div style="font-size:22px;font-weight:700;color:#222;line-height:1.1;"><?php echo $st['val']; ?></div>
        <div style="font-size:11px;color:#8a94a0;margin-top:2px;"><?php echo $st['lbl']; ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ══ Progress Bar ══════════════════════════════════════════════════════ -->
<div style="background:#fff;border-radius:10px;padding:18px 22px;border:1px solid #e8ecf0;margin-bottom:22px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
    <span style="font-size:13px;font-weight:700;color:#444;">Setup Progress</span>
    <span style="font-size:12px;color:#888;"><strong style="color:#444;"><?php echo $steps_complete; ?></strong> of <?php echo count($steps); ?> steps complete</span>
  </div>
  <div style="background:#edf0f3;border-radius:100px;height:8px;overflow:hidden;">
    <div style="background:<?php echo $bar_color; ?>;height:100%;border-radius:100px;width:<?php echo $pct; ?>%;transition:width .5s ease;"></div>
  </div>
  <div style="display:flex;margin-top:10px;gap:2px;">
    <?php foreach ($steps as $s):
      $c = $sc_map[$s['status']];
    ?>
    <div style="flex:1;text-align:center;" title="<?php echo htmlspecialchars($s['title']); ?>">
      <div style="width:10px;height:10px;border-radius:50%;background:<?php echo $c['circle']; ?>;margin:0 auto;"></div>
      <div style="font-size:9px;color:#aaa;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo $s['num']; ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ══ Step Cards ════════════════════════════════════════════════════════ -->
<div class="row">
<?php foreach ($steps as $step):
  $c = $sc_map[$step['status']];
?>
<div class="col-md-6" style="margin-bottom:14px;">
  <div style="background:<?php echo $c['bg']; ?>;border-radius:10px;border:1px solid #e8ecf0;border-left:4px solid <?php echo $c['border']; ?>;padding:16px 18px;display:flex;align-items:flex-start;gap:16px;height:100%;box-sizing:border-box;transition:box-shadow .2s,transform .15s;"
       onmouseover="this.style.boxShadow='0 6px 20px rgba(0,0,0,.09)';this.style.transform='translateY(-1px)'"
       onmouseout="this.style.boxShadow='none';this.style.transform='none'">

    <!-- Number circle -->
    <div style="flex-shrink:0;position:relative;">
      <div style="width:46px;height:46px;border-radius:50%;background:<?php echo $c['circle']; ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;font-weight:800;box-shadow:0 2px 8px <?php echo $c['circle']; ?>44;">
        <?php echo $step['num']; ?>
      </div>
      <div style="position:absolute;bottom:-1px;right:-1px;width:17px;height:17px;border-radius:50%;background:#fff;border:1.5px solid <?php echo $c['circle']; ?>;display:flex;align-items:center;justify-content:center;">
        <i class="fa <?php echo $c['tick']; ?>" style="font-size:8px;color:<?php echo $c['circle']; ?>;"></i>
      </div>
    </div>

    <!-- Content -->
    <div style="flex:1;min-width:0;">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;flex-wrap:wrap;margin-bottom:5px;">
        <div>
          <span style="font-size:14px;font-weight:700;color:#1a1e2b;"><?php echo htmlspecialchars($step['title']); ?></span>
          <div style="font-size:11px;color:#8a94a0;margin-top:1px;"><?php echo htmlspecialchars($step['sub']); ?></div>
        </div>
        <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:100px;background:<?php echo $c['badge_bg']; ?>;color:<?php echo $c['badge_fg']; ?>;white-space:nowrap;letter-spacing:.3px;flex-shrink:0;">
          <?php echo htmlspecialchars($step['badge']); ?>
        </span>
      </div>

      <p style="font-size:12px;color:#5d6d74;margin:0 0 12px;line-height:1.5;"><?php echo $step['desc']; ?></p>

      <a href="<?php echo $step['url']; ?>" style="font-size:12px;font-weight:700;color:<?php echo $c['circle']; ?>;text-decoration:none;display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border:1.5px solid <?php echo $c['border']; ?>33;border-radius:6px;background:#fff;transition:background .15s;"
         onmouseover="this.style.background='<?php echo $c['circle']; ?>12'"
         onmouseout="this.style.background='#fff'">
        <i class="fa fa-arrow-right" style="font-size:10px;"></i>
        <?php echo htmlspecialchars($step['action']); ?>
      </a>
    </div>

  </div>
</div>
<?php endforeach; ?>
</div>

<!-- ══ Quick Actions ═════════════════════════════════════════════════════ -->
<div style="background:#fff;border-radius:10px;border:1px solid #e8ecf0;padding:18px 22px;margin-top:4px;">
  <div style="font-size:11px;font-weight:700;color:#8a94a0;text-transform:uppercase;letter-spacing:.8px;margin-bottom:14px;">Quick Actions</div>
  <div style="display:flex;flex-wrap:wrap;gap:10px;">
    <a href="<?php echo site_url('admin/tt/generate'); ?>"     class="btn btn-primary  btn-sm"><i class="fa fa-magic"></i> Auto Generate</a>
    <a href="<?php echo site_url('admin/tt/class_grid'); ?>"   class="btn btn-default  btn-sm"><i class="fa fa-th-large"></i> Class Grid</a>
    <a href="<?php echo site_url('admin/tt/teacher_view'); ?>" class="btn btn-default  btn-sm"><i class="fa fa-user"></i> Teacher View</a>
    <a href="<?php echo site_url('admin/tt/lesson_browser'); ?>" class="btn btn-default btn-sm"><i class="fa fa-search"></i> Lesson Browser</a>
    <a href="<?php echo site_url('admin/tt/joint_lessons'); ?>" class="btn btn-default btn-sm"><i class="fa fa-link"></i> Joint Lessons</a>
    <a href="<?php echo site_url('admin/tt/substitution'); ?>" class="btn btn-default  btn-sm"><i class="fa fa-exchange"></i> Substitution</a>
  </div>
</div>

</section>
</div>
