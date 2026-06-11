<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>
        <i class="fa fa-calendar-check-o" style="color:#3c8dbc;margin-right:8px;"></i>
        Timetable Setup
        <small>Status overview &amp; configuration guide</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Dashboard</li>
    </ol>
</section>

<section class="content">
<?php
/* ── Color palette per step (bg, border/circle, text) ─────────── */
$palettes = [
  ['bg'=>'#EBF4FB','border'=>'#2980B9','circle'=>'#2980B9','text'=>'#1A5276','light'=>'#D6EAF8'],
  ['bg'=>'#F5EEF8','border'=>'#7D3C98','circle'=>'#7D3C98','text'=>'#6C3483','light'=>'#E8DAEF'],
  ['bg'=>'#E8F8F5','border'=>'#17A589','circle'=>'#17A589','text'=>'#0E6655','light'=>'#D1F2EB'],
  ['bg'=>'#FEF9E7','border'=>'#D4AC0D','circle'=>'#D4AC0D','text'=>'#9A7D0A','light'=>'#FDEBD0'],
  ['bg'=>'#FDEDEC','border'=>'#CB4335','circle'=>'#CB4335','text'=>'#922B21','light'=>'#FADBD8'],
  ['bg'=>'#EAFAF1','border'=>'#1E8449','circle'=>'#1E8449','text'=>'#196F3D','light'=>'#D5F5E3'],
  ['bg'=>'#EAF2FF','border'=>'#1F618D','circle'=>'#1F618D','text'=>'#154360','light'=>'#D6EAF8'],
];

/* ── Status badge config ───────────────────────────────────────── */
$status_badge = [
  'done'     => ['bg'=>'#d4edda','fg'=>'#155724','icon'=>'fa-check-circle'],
  'warn'     => ['bg'=>'#fff3cd','fg'=>'#856404','icon'=>'fa-exclamation-circle'],
  'required' => ['bg'=>'#f8d7da','fg'=>'#721c24','icon'=>'fa-times-circle'],
  'optional' => ['bg'=>'#e2e6ea','fg'=>'#495057','icon'=>'fa-minus-circle'],
];

/* ── Steps data ────────────────────────────────────────────────── */
$steps = [
  [
    'icon'  => 'fa-clock-o',
    'title' => 'Period Setup',
    'sub'   => 'Teaching slots &amp; breaks',
    'url'   => site_url('admin/tt/periods'),
    'status'=> $period_count > 0 ? 'done' : 'required',
    'badge' => $period_count > 0 ? 'Done' : 'Required',
    'desc'  => $period_count > 0
      ? "<strong>{$period_count}</strong> teaching + <strong>{$break_count}</strong> break period(s) configured"
      : "Define the daily schedule — morning to afternoon teaching slots",
    'action'=> $period_count > 0 ? 'Edit Periods' : 'Set Up Periods',
  ],
  [
    'icon'  => 'fa-building',
    'title' => 'Rooms',
    'sub'   => 'Classrooms, labs &amp; halls',
    'url'   => site_url('admin/tt/rooms'),
    'status'=> $room_count > 0 ? 'done' : 'warn',
    'badge' => $room_count > 0 ? 'Done' : 'Recommended',
    'desc'  => $room_count > 0
      ? "<strong>{$room_count}</strong> room(s) available for scheduling"
      : "Add rooms so the generator can assign venues to lessons",
    'action'=> $room_count > 0 ? 'Manage Rooms' : 'Add Rooms',
  ],
  [
    'icon'  => 'fa-users',
    'title' => 'Batches',
    'sub'   => 'Split groups for labs',
    'url'   => site_url('admin/tt/batches'),
    'status'=> 'optional',
    'badge' => $batch_count > 0 ? "{$batch_count} Batch(es)" : 'Optional',
    'desc'  => $batch_count > 0
      ? "<strong>{$batch_count}</strong> batch(es) — parallel lab sessions supported"
      : "Only needed if classes split into sub-groups (e.g. Batch A / B for labs)",
    'action'=> 'Manage Batches',
  ],
  [
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
    'icon'  => 'fa-table',
    'title' => 'Subject Load',
    'sub'   => 'Teachers &amp; periods per class',
    'url'   => site_url('admin/tt/subject_load'),
    'status'=> ($load_class_count > 0 && $missing_teacher === 0) ? 'done' : ($load_class_count > 0 ? 'warn' : 'required'),
    'badge' => ($load_class_count > 0 && $missing_teacher === 0) ? 'Done' : ($load_class_count > 0 ? 'Incomplete' : 'Required'),
    'desc'  => $load_class_count > 0
      ? "<strong>{$load_class_count}</strong> class-sections &middot; <strong>{$total_load_rows}</strong> subjects" . ($missing_teacher > 0 ? " &mdash; <span style='color:#C0392B;font-weight:600;'><i class='fa fa-exclamation-triangle'></i> {$missing_teacher} missing teacher</span>" : "")
      : "Assign subjects, teachers and periods/week to every class-section",
    'action'=> $load_class_count > 0 ? 'Edit Subject Load' : 'Configure Load',
  ],
  [
    'icon'  => 'fa-user-times',
    'title' => 'Teacher Constraints',
    'sub'   => 'Limits &amp; unavailable slots',
    'url'   => site_url('admin/tt/teacher_constraints'),
    'status'=> 'optional',
    'badge' => $teacher_const_ct > 0 ? "{$teacher_const_ct} Set" : 'Optional',
    'desc'  => $teacher_const_ct > 0
      ? "<strong>{$teacher_const_ct}</strong> teacher(s) have custom constraints set"
      : "Set max periods/day and mark unavailable time slots per teacher",
    'action'=> 'Manage Constraints',
  ],
  [
    'icon'  => 'fa-magic',
    'title' => 'Auto Generate',
    'sub'   => 'Run the scheduler',
    'url'   => site_url('admin/tt/generate'),
    'status'=> !empty($last_gen) ? ($last_gen->quality_score >= 90 ? 'done' : 'warn') : 'required',
    'badge' => !empty($last_gen) ? "Quality {$last_gen->quality_score}%" : 'Not Run',
    'desc'  => !empty($last_gen)
      ? "Last run " . date('d M Y, h:i A', strtotime($last_gen->generated_at)) . " &mdash; Placed <strong>{$last_gen->total_placed}</strong>&thinsp;/&thinsp;{$last_gen->total_required} lessons"
      : "Generate the timetable once periods + subject load are configured",
    'action'=> !empty($last_gen) ? 'Re-Generate' : 'Generate Now',
  ],
];

$steps_complete = count(array_filter($steps, function($s){ return $s['status'] === 'done'; }));
$pct            = round(($steps_complete / count($steps)) * 100);
$bar_color      = $pct >= 71 ? '#27AE60' : ($pct >= 43 ? '#F39C12' : '#E74C3C');
?>

<!-- ══ Active Timetable Banner ══════════════════════════════════ -->
<?php if (!empty($last_confirmed)): ?>
<div class="row">
  <div class="col-md-12">
    <div style="background:linear-gradient(135deg,#0e7a45 0%,#1ABC9C 100%);border-radius:10px;padding:18px 24px;margin-bottom:22px;color:#fff;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
      <div style="display:flex;align-items:center;gap:16px;">
        <div style="background:rgba(255,255,255,.18);border-radius:50%;width:52px;height:52px;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
          <i class="fa fa-calendar-check-o" style="font-size:22px;"></i>
        </div>
        <div>
          <div style="font-size:15px;font-weight:700;">Active Timetable Confirmed</div>
          <div style="font-size:12px;opacity:.9;margin-top:3px;">
            Confirmed <?php echo date('d M Y, h:i A', strtotime($last_confirmed->confirmed_at)); ?>
            &ensp;&bull;&ensp; Quality <strong><?php echo $last_confirmed->quality_score; ?>%</strong>
            &ensp;&bull;&ensp; <?php echo $last_confirmed->total_placed; ?>&thinsp;/&thinsp;<?php echo $last_confirmed->total_required; ?> lessons placed
          </div>
        </div>
      </div>
      <div style="display:flex;gap:8px;">
        <a href="<?php echo site_url('admin/tt/class_grid'); ?>" style="background:#fff;color:#0e7a45;border-radius:6px;padding:7px 16px;font-size:12px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
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

<!-- ══ Quick Stats ═══════════════════════════════════════════════ -->
<div class="row" style="margin-bottom:4px;">
  <?php
  $stats = [
    ['val'=> $period_count,     'lbl'=> 'Periods / Day', 'icon'=> 'fa-clock-o',  'col'=>'#2980B9','bg'=>'#EBF4FB'],
    ['val'=> $room_count,       'lbl'=> 'Rooms',          'icon'=> 'fa-building', 'col'=>'#7D3C98','bg'=>'#F5EEF8'],
    ['val'=> $load_class_count, 'lbl'=> 'Classes Loaded', 'icon'=> 'fa-table',    'col'=>'#CB4335','bg'=>'#FDEDEC'],
    ['val'=> !empty($last_gen) ? $last_gen->quality_score.'%' : '—',
                                'lbl'=> 'Last Quality',   'icon'=> 'fa-star',     'col'=> !empty($last_gen) ? ($last_gen->quality_score >= 90 ? '#1E8449' : '#D68910') : '#95A5A6','bg'=> !empty($last_gen) ? ($last_gen->quality_score >= 90 ? '#EAFAF1' : '#FEF9E7') : '#F2F3F4'],
  ];
  foreach ($stats as $st): ?>
  <div class="col-xs-6 col-sm-3">
    <div style="background:<?php echo $st['bg']; ?>;border-radius:10px;padding:16px;border:1px solid <?php echo $st['col']; ?>22;margin-bottom:18px;display:flex;align-items:center;gap:14px;">
      <div style="background:<?php echo $st['col']; ?>22;border-radius:10px;width:44px;height:44px;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
        <i class="fa <?php echo $st['icon']; ?>" style="color:<?php echo $st['col']; ?>;font-size:18px;"></i>
      </div>
      <div>
        <div style="font-size:22px;font-weight:800;color:<?php echo $st['col']; ?>;line-height:1.1;"><?php echo $st['val']; ?></div>
        <div style="font-size:11px;color:<?php echo $st['col']; ?>bb;margin-top:2px;font-weight:600;"><?php echo $st['lbl']; ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ══ Progress Bar ═════════════════════════════════════════════ -->
<div style="background:#fff;border-radius:10px;padding:18px 22px;border:1px solid #e8ecf0;margin-bottom:22px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
    <span style="font-size:13px;font-weight:700;color:#333;">Setup Progress</span>
    <div style="display:flex;align-items:center;gap:12px;">
      <span style="font-size:12px;color:#777;"><strong style="color:#333;"><?php echo $steps_complete; ?></strong> of <?php echo count($steps); ?> steps complete</span>
      <a href="<?php echo site_url('admin/tt/instructions'); ?>" style="font-size:11px;font-weight:700;color:#2980B9;text-decoration:none;background:#EBF4FB;border:1px solid #AED6F1;border-radius:20px;padding:4px 12px;display:inline-flex;align-items:center;gap:5px;">
        <i class="fa fa-question-circle"></i> How to Use
      </a>
    </div>
  </div>
  <div style="background:#edf0f3;border-radius:100px;height:10px;overflow:hidden;">
    <div style="background:linear-gradient(90deg,<?php echo $bar_color; ?>99,<?php echo $bar_color; ?>);height:100%;border-radius:100px;width:<?php echo $pct; ?>%;transition:width .5s ease;"></div>
  </div>
  <div style="display:flex;margin-top:10px;">
    <?php foreach ($steps as $idx => $s):
      $p = $palettes[$idx];
      $is_done = $s['status'] === 'done';
    ?>
    <div style="flex:1;text-align:center;" title="<?php echo htmlspecialchars($s['title']); ?>">
      <div style="width:12px;height:12px;border-radius:50%;background:<?php echo $is_done ? $p['circle'] : '#dde1e7'; ?>;margin:0 auto;border:2px solid <?php echo $is_done ? $p['border'] : '#c8cdd5'; ?>;"></div>
      <div style="font-size:9px;color:#aaa;margin-top:4px;"><?php echo $idx+1; ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ══ Step Cards ════════════════════════════════════════════════ -->
<div class="row">
<?php foreach ($steps as $idx => $step):
  $p  = $palettes[$idx];
  $sb = $status_badge[$step['status']];
?>
<div class="col-md-6" style="margin-bottom:14px;">
  <div style="background:<?php echo $p['bg']; ?>;border-radius:10px;border:1px solid <?php echo $p['border']; ?>33;border-left:4px solid <?php echo $p['border']; ?>;padding:18px 18px 14px;display:flex;align-items:flex-start;gap:16px;height:100%;box-sizing:border-box;transition:box-shadow .2s,transform .15s;"
       onmouseover="this.style.boxShadow='0 6px 22px <?php echo $p['border']; ?>33';this.style.transform='translateY(-2px)'"
       onmouseout="this.style.boxShadow='none';this.style.transform='none'">

    <!-- Numbered circle -->
    <div style="flex-shrink:0;position:relative;">
      <div style="width:48px;height:48px;border-radius:50%;background:<?php echo $p['circle']; ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;font-weight:800;box-shadow:0 3px 10px <?php echo $p['circle']; ?>55;">
        <?php echo $idx+1; ?>
      </div>
      <div style="position:absolute;bottom:-2px;right:-2px;width:18px;height:18px;border-radius:50%;background:<?php echo $p['light']; ?>;border:2px solid <?php echo $p['border']; ?>;display:flex;align-items:center;justify-content:center;">
        <i class="fa <?php echo $step['status']==='done' ? 'fa-check' : ($step['status']==='required' ? 'fa-times' : ($step['status']==='warn' ? 'fa-exclamation' : 'fa-minus')); ?>" style="font-size:8px;color:<?php echo $p['border']; ?>;"></i>
      </div>
    </div>

    <!-- Content -->
    <div style="flex:1;min-width:0;">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:4px;flex-wrap:wrap;">
        <div>
          <span style="font-size:14px;font-weight:800;color:<?php echo $p['text']; ?>;"><?php echo htmlspecialchars($step['title']); ?></span>
          <div style="font-size:11px;color:<?php echo $p['circle']; ?>99;margin-top:1px;font-weight:500;"><?php echo $step['sub']; ?></div>
        </div>
        <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:100px;background:<?php echo $sb['bg']; ?>;color:<?php echo $sb['fg']; ?>;white-space:nowrap;flex-shrink:0;border:1px solid <?php echo $sb['fg']; ?>33;">
          <i class="fa <?php echo $sb['icon']; ?>" style="font-size:9px;"></i>
          <?php echo htmlspecialchars($step['badge']); ?>
        </span>
      </div>

      <p style="font-size:12px;color:<?php echo $p['text']; ?>cc;margin:0 0 12px;line-height:1.55;"><?php echo $step['desc']; ?></p>

      <a href="<?php echo $step['url']; ?>" style="font-size:12px;font-weight:700;color:<?php echo $p['circle']; ?>;text-decoration:none;display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border:1.5px solid <?php echo $p['border']; ?>66;border-radius:6px;background:#fff;transition:all .15s;"
         onmouseover="this.style.background='<?php echo $p['bg']; ?>';this.style.borderColor='<?php echo $p['border']; ?>'"
         onmouseout="this.style.background='#fff';this.style.borderColor='<?php echo $p['border']; ?>66'">
        <i class="fa fa-arrow-right" style="font-size:10px;"></i>
        <?php echo htmlspecialchars($step['action']); ?>
      </a>
    </div>

  </div>
</div>
<?php endforeach; ?>
</div>

<!-- ══ Quick Actions ════════════════════════════════════════════ -->
<div style="background:#fff;border-radius:10px;border:1px solid #e8ecf0;padding:18px 22px;margin-top:4px;">
  <div style="font-size:11px;font-weight:700;color:#8a94a0;text-transform:uppercase;letter-spacing:.8px;margin-bottom:14px;">Quick Actions</div>
  <div style="display:flex;flex-wrap:wrap;gap:10px;">
    <a href="<?php echo site_url('admin/tt/generate'); ?>"       class="btn btn-primary  btn-sm"><i class="fa fa-magic"></i> Auto Generate</a>
    <a href="<?php echo site_url('admin/tt/class_grid'); ?>"     class="btn btn-default  btn-sm"><i class="fa fa-th-large"></i> Class Grid</a>
    <a href="<?php echo site_url('admin/tt/teacher_view'); ?>"   class="btn btn-default  btn-sm"><i class="fa fa-user"></i> Teacher View</a>
    <a href="<?php echo site_url('admin/tt/lesson_browser'); ?>" class="btn btn-default  btn-sm"><i class="fa fa-search"></i> Lesson Browser</a>
    <a href="<?php echo site_url('admin/tt/joint_lessons'); ?>"  class="btn btn-default  btn-sm"><i class="fa fa-link"></i> Joint Lessons</a>
    <a href="<?php echo site_url('admin/tt/substitution'); ?>"   class="btn btn-default  btn-sm"><i class="fa fa-exchange"></i> Substitution</a>
    <a href="<?php echo site_url('admin/tt/instructions'); ?>"   class="btn btn-info     btn-sm"><i class="fa fa-book"></i> User Guide</a>
  </div>
</div>

</section>
</div>
