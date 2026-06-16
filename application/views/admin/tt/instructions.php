<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
  <h1><i class="fa fa-book" style="color:#2980B9;margin-right:8px;"></i>Auto Timetable — User Guide <small>Complete step-by-step reference</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="<?php echo site_url('admin/tt/dashboard'); ?>">Auto Timetable</a></li>
    <li class="active">User Guide</li>
  </ol>
</section>

<section class="content">
<style>
.guide-section { scroll-margin-top: 70px; }
.guide-h2 { font-size:18px;font-weight:800;color:#1A2533;margin:0 0 6px;display:flex;align-items:center;gap:10px; }
.guide-h2 .step-chip { width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:800;flex-shrink:0; }
.guide-p { font-size:13px;color:#4a5568;line-height:1.7;margin-bottom:10px; }
.guide-steps { padding-left:0;list-style:none;counter-reset:step-counter;margin-bottom:0; }
.guide-steps li { counter-increment:step-counter;padding:8px 0 8px 44px;position:relative;font-size:13px;color:#374151;line-height:1.6;border-bottom:1px dashed #e5e7eb; }
.guide-steps li:last-child { border-bottom:none; }
.guide-steps li::before { content:counter(step-counter);position:absolute;left:0;top:8px;width:26px;height:26px;border-radius:50%;background:#2980B9;color:#fff;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center;line-height:1; }
.tip-box   { background:#EBF4FB;border-left:4px solid #2980B9;border-radius:0 8px 8px 0;padding:12px 16px;margin:12px 0;font-size:12px;color:#1A5276; }
.warn-box  { background:#FEF9E7;border-left:4px solid #D4AC0D;border-radius:0 8px 8px 0;padding:12px 16px;margin:12px 0;font-size:12px;color:#7D6608; }
.danger-box{ background:#FDEDEC;border-left:4px solid #CB4335;border-radius:0 8px 8px 0;padding:12px 16px;margin:12px 0;font-size:12px;color:#7B241C; }
.new-box   { background:#E9F7EF;border-left:4px solid #1E8449;border-radius:0 8px 8px 0;padding:12px 16px;margin:12px 0;font-size:12px;color:#196F3D; }
.screen-mock { background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:8px;padding:0;overflow:hidden;margin:14px 0;font-size:11px; }
.screen-mock .mock-header { background:#3c8dbc;color:#fff;padding:8px 14px;font-size:12px;font-weight:700;display:flex;align-items:center;gap:8px; }
.mock-table { width:100%;border-collapse:collapse; }
.mock-table th { background:#f1f5f9;color:#64748b;font-size:10px;font-weight:700;padding:6px 10px;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid #e2e8f0; }
.mock-table td { padding:7px 10px;border-bottom:1px solid #f1f5f9;color:#374151;font-size:11px; }
.mock-table tr:last-child td { border-bottom:none; }
.mock-badge { display:inline-block;padding:2px 7px;border-radius:100px;font-size:9px;font-weight:700; }
.toc-link { display:block;padding:6px 10px;font-size:12px;color:#4a5568;text-decoration:none;border-radius:6px;margin-bottom:2px;transition:background .15s; }
.toc-link:hover,.toc-link.active { background:#EBF4FB;color:#2980B9;text-decoration:none; }
.toc-link i { width:18px;color:#94a3b8; }
.section-card { background:#fff;border-radius:10px;border:1px solid #e8ecf0;padding:22px 24px;margin-bottom:18px; }
.constraint-table td,.constraint-table th { padding:8px 10px;border:1px solid #e2e8f0;font-size:12px; }
.constraint-table th { background:#f1f5f9;font-weight:700;color:#374151; }
.badge-hard   { background:#FDEDEC;color:#922B21;padding:2px 7px;border-radius:4px;font-size:10px;font-weight:700; }
.badge-soft   { background:#EBF4FB;color:#1A5276;padding:2px 7px;border-radius:4px;font-size:10px;font-weight:700; }
.badge-new    { background:#E9F7EF;color:#1E8449;padding:2px 7px;border-radius:4px;font-size:10px;font-weight:700; }
</style>

<div class="row">

<!-- ── TOC Sidebar ─────────────────────────────────────────────── -->
<div class="col-md-3 hidden-xs hidden-sm">
  <div style="position:sticky;top:70px;">
    <div style="background:#fff;border-radius:10px;border:1px solid #e8ecf0;padding:16px;">
      <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.7px;margin-bottom:12px;">Contents</div>
      <a href="#sec-overview"     class="toc-link"><i class="fa fa-info-circle"></i> Overview</a>
      <a href="#sec-periods"      class="toc-link"><i class="fa fa-clock-o"></i> 1. Period Setup</a>
      <a href="#sec-rooms"        class="toc-link"><i class="fa fa-building"></i> 2. Rooms</a>
      <a href="#sec-batches"      class="toc-link"><i class="fa fa-users"></i> 3. Batches</a>
      <a href="#sec-colors"       class="toc-link"><i class="fa fa-paint-brush"></i> 4. Subject Colours</a>
      <a href="#sec-avail"        class="toc-link"><i class="fa fa-ban"></i> 5. Availability</a>
      <a href="#sec-load"         class="toc-link"><i class="fa fa-table"></i> 6. Subject Load</a>
      <a href="#sec-constraints"  class="toc-link"><i class="fa fa-user-times"></i> 7. Teacher Constraints</a>
      <a href="#sec-workload"     class="toc-link"><i class="fa fa-bar-chart"></i> 8. Workload Dashboard <span style="font-size:9px;background:#1E8449;color:#fff;padding:1px 5px;border-radius:3px;margin-left:2px;">NEW</span></a>
      <a href="#sec-joint"        class="toc-link"><i class="fa fa-link"></i> 9. Joint Lessons</a>
      <a href="#sec-generate"     class="toc-link"><i class="fa fa-magic"></i> 10. Auto Generate</a>
      <a href="#sec-classgrid"    class="toc-link"><i class="fa fa-th-large"></i> 11. Class Grid</a>
      <a href="#sec-teacherview"  class="toc-link"><i class="fa fa-user"></i> 12. Teacher View</a>
      <a href="#sec-substitution" class="toc-link"><i class="fa fa-exchange"></i> 13. Substitution</a>
      <a href="#sec-browser"      class="toc-link"><i class="fa fa-search"></i> 14. Lesson Browser</a>
      <div style="margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9;">
        <a href="<?php echo site_url('admin/tt/dashboard'); ?>" class="btn btn-default btn-xs btn-block"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
      </div>
    </div>
  </div>
</div>

<!-- ── Main Content ────────────────────────────────────────────── -->
<div class="col-md-9 col-xs-12">

<!-- OVERVIEW -->
<div id="sec-overview" class="section-card guide-section">
  <div class="guide-h2"><i class="fa fa-calendar-check-o" style="color:#2980B9;font-size:20px;"></i> Auto Timetable — Overview</div>
  <p class="guide-p">The Auto Timetable module automatically generates a conflict-free weekly timetable for all your classes. It respects teacher availability, room capacity, consecutive-period requirements (e.g. 2-hour labs), per-day and per-week load caps, idle-gap limits, preferred teaching windows, and optional joint lessons shared across multiple classes.</p>
  <div style="display:flex;flex-wrap:wrap;gap:10px;margin:14px 0;">
    <?php
    $flow = [
      ['1','Periods','#2980B9'],['2','Rooms','#7D3C98'],['3','Batches','#17A589'],
      ['4','Colours','#D4AC0D'],['5','Subject Load','#CB4335'],
      ['6','Constraints','#1E8449'],['7','Workload Check','#E67E22'],['8','Generate!','#1F618D'],
    ];
    foreach($flow as $i=>$f): ?>
    <div style="display:flex;align-items:center;gap:6px;">
      <div style="background:<?php echo $f[2]; ?>;color:#fff;border-radius:50%;width:26px;height:26px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0;"><?php echo $f[0]; ?></div>
      <span style="font-size:12px;font-weight:600;color:#374151;"><?php echo $f[1]; ?></span>
      <?php if($i < count($flow)-1): ?><i class="fa fa-chevron-right" style="font-size:9px;color:#94a3b8;"></i><?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="tip-box"><i class="fa fa-lightbulb-o"></i> <strong>Minimum to generate:</strong> You only need <strong>Period Setup + Subject Load</strong>. All other steps improve quality but are optional. The generator enforces a <strong>default cap of 6 periods/day and 36 periods/week</strong> for every teacher even without a constraint row.</div>
</div>

<!-- 1. PERIOD SETUP -->
<div id="sec-periods" class="section-card guide-section">
  <div class="guide-h2"><div class="step-chip" style="background:#2980B9;">1</div> Period Setup</div>
  <p class="guide-p">Periods define the daily time slots. The generator uses these as the rows of the timetable grid. Configure periods <strong>per academic session</strong>.</p>
  <ol class="guide-steps">
    <li>Go to <strong>Auto Timetable → Period Setup</strong></li>
    <li>Click <strong>Add Period</strong> and enter a name (e.g. "Period 1"), start time, and end time</li>
    <li>For lunch or tea breaks, tick <strong>Is Break</strong> and enter a label — breaks appear in the grid but cannot have lessons assigned</li>
    <li>Drag rows to reorder — the generator uses the order you set here</li>
    <li>Typical setup: 6–8 teaching periods + 1–2 breaks per day</li>
  </ol>
  <div class="screen-mock">
    <div class="mock-header"><i class="fa fa-clock-o"></i> Period Setup — Example Configuration</div>
    <table class="mock-table">
      <tr><th>#</th><th>Name</th><th>Start</th><th>End</th><th>Type</th></tr>
      <tr><td>1</td><td>Period 1</td><td>08:30</td><td>09:20</td><td><span class="mock-badge" style="background:#D1F2EB;color:#0E6655;">Teaching</span></td></tr>
      <tr><td>2</td><td>Period 2</td><td>09:20</td><td>10:10</td><td><span class="mock-badge" style="background:#D1F2EB;color:#0E6655;">Teaching</span></td></tr>
      <tr><td>3</td><td>Tea Break</td><td>10:10</td><td>10:25</td><td><span class="mock-badge" style="background:#FDEBD0;color:#784212;">Break</span></td></tr>
      <tr><td>4</td><td>Period 3</td><td>10:25</td><td>11:15</td><td><span class="mock-badge" style="background:#D1F2EB;color:#0E6655;">Teaching</span></td></tr>
      <tr><td>5</td><td>Period 4</td><td>11:15</td><td>12:05</td><td><span class="mock-badge" style="background:#D1F2EB;color:#0E6655;">Teaching</span></td></tr>
      <tr><td>6</td><td>Lunch</td><td>12:05</td><td>13:00</td><td><span class="mock-badge" style="background:#FDEBD0;color:#784212;">Break</span></td></tr>
      <tr><td>7</td><td>Period 5</td><td>13:00</td><td>13:50</td><td><span class="mock-badge" style="background:#D1F2EB;color:#0E6655;">Teaching</span></td></tr>
      <tr><td>8</td><td>Period 6</td><td>13:50</td><td>14:40</td><td><span class="mock-badge" style="background:#D1F2EB;color:#0E6655;">Teaching</span></td></tr>
    </table>
  </div>
  <div class="warn-box"><i class="fa fa-exclamation-triangle"></i> Periods are <strong>session-specific</strong>. If you create a new academic session, add periods again for that session before generating.</div>
</div>

<!-- 2. ROOMS -->
<div id="sec-rooms" class="section-card guide-section">
  <div class="guide-h2"><div class="step-chip" style="background:#7D3C98;">2</div> Rooms</div>
  <p class="guide-p">Rooms are venues that the generator assigns to each lesson. Adding rooms is optional — if no rooms are configured the generator still works but won't assign venues.</p>
  <ol class="guide-steps">
    <li>Go to <strong>Auto Timetable → Rooms</strong></li>
    <li>Click <strong>Add Room</strong> and enter Name, Room Number, Capacity, and Room Type (Classroom / Lab / Seminar / Hall)</li>
    <li>Tick <strong>Shared Room</strong> if the room can be used by multiple classes simultaneously</li>
    <li>On <strong>Room Availability</strong>, mark any slots when a room is unavailable (maintenance, exam use, etc.)</li>
  </ol>
  <div class="tip-box"><i class="fa fa-lightbulb-o"></i> Set the correct <strong>Room Type</strong>. In Subject Load you specify <em>preferred room type</em> per subject — the generator will try to assign a matching room (e.g. Lab subjects → Lab rooms).</div>
</div>

<!-- 3. BATCHES -->
<div id="sec-batches" class="section-card guide-section">
  <div class="guide-h2"><div class="step-chip" style="background:#17A589;">3</div> Batches</div>
  <p class="guide-p">Batches split a class-section into parallel sub-groups that can run different subjects at the same time. Most common use: half the class does Lab A while the other half does Lab B simultaneously.</p>
  <ol class="guide-steps">
    <li>Go to <strong>Auto Timetable → Batches</strong> and select session, class, and section</li>
    <li>Add batch names — typically "Batch A" and "Batch B" (or "Odd Roll" / "Even Roll")</li>
    <li>Enter the student count for each batch</li>
    <li>In <strong>Subject Load</strong>, assign lab subjects to a specific batch — the generator will place them in parallel</li>
  </ol>
  <div class="tip-box"><i class="fa fa-lightbulb-o"></i> If your institution does not split classes for labs, <strong>skip this step entirely</strong>. Batches are only needed for parallel scheduling.</div>
</div>

<!-- 4. SUBJECT COLOURS -->
<div id="sec-colors" class="section-card guide-section">
  <div class="guide-h2"><div class="step-chip" style="background:#D4AC0D;">4</div> Subject Colours &amp; Abbreviations</div>
  <p class="guide-p">Assign each subject a distinct background colour and a short abbreviation (≤ 8 chars). These appear on the timetable grid cells making it easy to identify subjects at a glance.</p>
  <ol class="guide-steps">
    <li>Go to <strong>Auto Timetable → Subject Colors</strong></li>
    <li>Each row shows a subject — click the colour swatch to pick a colour</li>
    <li>Enter a short <strong>Abbreviation</strong> (e.g. "MATHS", "PHY", "CS-LAB")</li>
    <li>Click <strong>Save All Colors</strong> when done</li>
  </ol>
</div>

<!-- 5. AVAILABILITY -->
<div id="sec-avail" class="section-card guide-section">
  <div class="guide-h2"><div class="step-chip" style="background:#E67E22;">5</div> Availability (Class / Room / Teacher / Subject)</div>
  <p class="guide-p">Before running the generator, mark any time slots that must be kept free. The generator will never place a lesson in a blocked slot.</p>
  <ol class="guide-steps">
    <li><strong>Class Availability</strong> — block a class-section from a slot (e.g. every Friday P6 is reserved for Sports)</li>
    <li><strong>Room Availability</strong> — block a room (e.g. Lab 1 unavailable Monday P1–P2 for equipment calibration)</li>
    <li><strong>Teacher Availability</strong> — mark slots where a teacher is unavailable (PhD class, admin duty, off-campus). The generator will never assign them during blocked slots</li>
    <li><strong>Subject Time Off</strong> — prevent a subject from being scheduled in certain slots (e.g. no Maths on Friday afternoon). This is respected for both regular and joint lessons</li>
  </ol>
  <div class="warn-box"><i class="fa fa-exclamation-triangle"></i> The generator respects all blocks strictly. Over-blocking can reduce quality or cause unplaced lessons — block only genuinely unavailable slots.</div>
</div>

<!-- 6. SUBJECT LOAD -->
<div id="sec-load" class="section-card guide-section">
  <div class="guide-h2"><div class="step-chip" style="background:#CB4335;">6</div> Subject Load <span style="font-size:12px;font-weight:500;color:#CB4335;margin-left:6px;">— Most Important Step</span></div>
  <p class="guide-p">Subject Load is the core configuration. For every class-section, you define <em>which subjects are taught, by which teacher, how many times per week</em>, and per-subject scheduling preferences.</p>

  <div class="screen-mock">
    <div class="mock-header"><i class="fa fa-table"></i> Subject Load — Class: II CSE, Section: A</div>
    <table class="mock-table">
      <tr><th>Subject</th><th>Teacher</th><th>P/Week</th><th>Consec.</th><th>Max/Day</th><th>On1</th><th>Room Pref.</th></tr>
      <tr><td>Mathematics</td><td>Mr. Kumar</td><td style="text-align:center;">5</td><td style="text-align:center;">1</td><td style="text-align:center;">2</td><td style="text-align:center;">☐</td><td>Classroom</td></tr>
      <tr><td>Physics</td><td>Ms. Ravi</td><td style="text-align:center;">4</td><td style="text-align:center;">1</td><td style="text-align:center;">2</td><td style="text-align:center;">☐</td><td>Classroom</td></tr>
      <tr><td>CS Lab</td><td>Mr. Ali</td><td style="text-align:center;">2</td><td style="text-align:center;font-weight:700;color:#CB4335;">2</td><td style="text-align:center;">1</td><td style="text-align:center;">☐</td><td>Lab</td></tr>
      <tr><td>English</td><td>Ms. Priya</td><td style="text-align:center;">3</td><td style="text-align:center;">1</td><td style="text-align:center;">1</td><td style="text-align:center;font-weight:700;color:#1E8449;">☑</td><td>Any</td></tr>
    </table>
  </div>

  <p class="guide-p" style="margin-top:12px;"><strong>Field explanations:</strong></p>
  <table class="constraint-table" style="width:100%;">
    <tr><th style="width:130px;">Periods/Week</th><td>Total times this subject appears per week. The generator spreads these evenly across working days.</td></tr>
    <tr><th>Consecutive</th><td><strong>1</strong> = single periods (default). <strong>2</strong> = pairs of back-to-back periods (ideal for labs). <strong>3</strong> = three consecutive periods. The generator always places them together.</td></tr>
    <tr><th>Max / Day</th><td>Maximum times this subject can appear on a single day. Set <strong>1</strong> to force a different day each occurrence; <strong>2</strong> to allow doubles on the same day.</td></tr>
    <tr><th>On1 (Min 1/day)</th><td>Guarantees the subject appears <em>at least once every working day</em>. Use for core daily subjects like Maths or English.</td></tr>
    <tr><th>Batch</th><td>Assign to a specific batch (A / B) for parallel lab scheduling. Leave blank for whole-class subjects.</td></tr>
  </table>

  <div class="new-box" style="margin-top:12px;">
    <strong><i class="fa fa-star"></i> Overflow Handling (PPW &gt; Working Days)</strong><br>
    If a subject's <em>Periods per Week exceeds the number of working days</em> (e.g. 7 PPW on a 6-day week), the generator automatically enters <strong>Overflow Mode</strong> for that subject: one double period is placed consecutively on the overflow day, and the remaining periods are spread across the other days. No manual intervention needed.
  </div>

  <div class="danger-box"><i class="fa fa-exclamation-circle"></i> <strong>Every subject row must have a teacher assigned.</strong> Rows without a teacher are skipped by the generator and flagged as warnings in the pre-flight check.</div>
</div>

<!-- 7. TEACHER CONSTRAINTS -->
<div id="sec-constraints" class="section-card guide-section">
  <div class="guide-h2"><div class="step-chip" style="background:#1E8449;">7</div> Teacher Constraints &amp; Availability</div>
  <p class="guide-p">Constraints control how the generator schedules each teacher's periods. <strong>Default limits apply to ALL teachers</strong> even without a configured constraint row — you only need to create a row to override these defaults or add extra rules.</p>

  <div class="new-box">
    <strong><i class="fa fa-shield"></i> Default Limits (apply to every teacher automatically)</strong><br>
    Every teacher is limited to <strong>6 periods per day</strong> and <strong>36 periods per week</strong> by default. No configuration needed — these are enforced by the generator even if a teacher has no constraint row. Create a constraint row only when you need to change these values or add additional rules.
  </div>

  <p class="guide-p" style="margin-top:14px;"><strong>Available Constraints (all configurable per teacher):</strong></p>
  <table class="constraint-table" style="width:100%;margin-bottom:14px;">
    <tr>
      <th style="width:180px;">Constraint</th>
      <th style="width:80px;">Type</th>
      <th>What it does</th>
    </tr>
    <tr>
      <td><strong>Max Periods / Day</strong></td>
      <td><span class="badge-hard">Hard</span></td>
      <td>Maximum teaching periods in a single day. Default: 6. The generator will never exceed this.</td>
    </tr>
    <tr>
      <td><strong>Max Periods / Week</strong></td>
      <td><span class="badge-hard">Hard</span></td>
      <td>Total teaching periods across the entire week. Default: 36. The generator stops assigning once this is reached.</td>
    </tr>
    <tr>
      <td><strong>Min Free Periods / Day</strong><br><span class="badge-new">New</span></td>
      <td><span class="badge-hard">Hard</span></td>
      <td>Teacher must have at least this many free periods each day. This <em>tightens the effective daily cap</em> — e.g. 6 max/day with 2 min-free means effectively 4 teaching periods/day. Default: 0.</td>
    </tr>
    <tr>
      <td><strong>Max Idle Gap / Day</strong><br><span class="badge-new">New</span></td>
      <td><span class="badge-hard">Hard</span></td>
      <td>Maximum number of <em>consecutive free periods</em> allowed between two teaching blocks. Prevents situations like teaching P1, free P2–P5, teaching P6 — a 4-period idle gap. Set this to 1 or 2 to keep teachers busy without long waits.</td>
    </tr>
    <tr>
      <td><strong>Max Consecutive Periods</strong><br><span class="badge-new">New</span></td>
      <td><span class="badge-hard">Hard</span></td>
      <td>Maximum number of back-to-back teaching periods before a break is required. E.g. set 3 → teacher cannot teach 4 or more periods in a row without a free period. Set 0 to disable.</td>
    </tr>
    <tr>
      <td><strong>Min Break After Max Consec</strong><br><span class="badge-new">New</span></td>
      <td><span class="badge-hard">Hard</span></td>
      <td>How many <em>free periods</em> are required after hitting the consecutive limit before the teacher can teach again. Default: 1. Works together with Max Consecutive — e.g. 3 consecutive + 1 break + next block of 3.</td>
    </tr>
    <tr>
      <td><strong>Teaching Window — Not Before</strong></td>
      <td><span class="badge-hard">Hard</span></td>
      <td>Generator will not assign periods that <em>start before</em> this time. E.g. 09:00 → teacher will never be assigned to an 08:30 period.</td>
    </tr>
    <tr>
      <td><strong>Teaching Window — Not After</strong></td>
      <td><span class="badge-hard">Hard</span></td>
      <td>Generator will not assign periods that <em>end after</em> this time. E.g. 14:00 → teacher will never be assigned to a period that ends at 14:40.</td>
    </tr>
    <tr>
      <td><strong>Avoid First Period</strong></td>
      <td><span class="badge-hard">Hard</span></td>
      <td>Prevents the teacher from being assigned to the first period of any day.</td>
    </tr>
    <tr>
      <td><strong>Avoid Last Period</strong></td>
      <td><span class="badge-hard">Hard</span></td>
      <td>Prevents the teacher from being assigned to the last period of any day.</td>
    </tr>
    <tr>
      <td><strong>Preferred Room</strong></td>
      <td><span class="badge-soft">Soft</span></td>
      <td>The generator will try to assign this room to this teacher's lessons (scoring preference). May be overridden when no better slot exists.</td>
    </tr>
    <tr>
      <td><strong>Day-Spread Penalty</strong></td>
      <td><span class="badge-soft">Soft</span></td>
      <td>The generator automatically prefers spreading a teacher's load evenly across all working days (scoring penalty of −0.8× per existing period on the day). No configuration needed — always active.</td>
    </tr>
    <tr>
      <td><strong>Exclude from Substitution</strong></td>
      <td><span class="badge-soft">Soft</span></td>
      <td>This teacher will not appear in the substitute dropdown when another teacher is absent. Useful for department heads or admin staff who should not cover classes.</td>
    </tr>
  </table>

  <div class="screen-mock">
    <div class="mock-header"><i class="fa fa-sliders"></i> Constraint Example — Managing a teacher's consecutive teaching</div>
    <div style="padding:14px 16px;background:#fff;font-size:12px;">
      <p style="margin:0 0 8px;color:#4a5568;">A teacher takes 3 continuous hours of classes with a 1-period break before the next block can start:</p>
      <div style="display:flex;flex-wrap:wrap;gap:10px;">
        <div style="flex:1;min-width:160px;background:#EBF4FB;padding:10px;border-radius:6px;">
          <div style="font-size:10px;font-weight:700;color:#1A5276;margin-bottom:4px;">MAX CONSECUTIVE</div>
          <div style="font-size:22px;font-weight:800;color:#2980B9;">3</div>
          <div style="font-size:10px;color:#7f8c8d;">periods in a row</div>
        </div>
        <div style="flex:1;min-width:160px;background:#E9F7EF;padding:10px;border-radius:6px;">
          <div style="font-size:10px;font-weight:700;color:#1E8449;margin-bottom:4px;">MIN BREAK AFTER</div>
          <div style="font-size:22px;font-weight:800;color:#1E8449;">1</div>
          <div style="font-size:10px;color:#7f8c8d;">free period required</div>
        </div>
        <div style="flex:2;min-width:220px;background:#f8fafc;padding:10px;border-radius:6px;border:1px solid #e2e8f0;">
          <div style="font-size:10px;font-weight:700;color:#64748b;margin-bottom:6px;">RESULTING DAILY PATTERN</div>
          <div style="display:flex;gap:4px;flex-wrap:wrap;">
            <?php
            $slots = [
              ['P1','teach','#3498db'],['P2','teach','#3498db'],['P3','teach','#3498db'],
              ['P4','FREE','#ecf0f1'],
              ['P5','teach','#3498db'],['P6','teach','#3498db'],['P7','teach','#3498db'],
            ];
            foreach($slots as $s):
              $bg = $s[1]==='FREE' ? '#ecf0f1' : $s[2];
              $tc = $s[1]==='FREE' ? '#7f8c8d' : '#fff';
            ?>
            <div style="background:<?php echo $bg; ?>;color:<?php echo $tc; ?>;border-radius:4px;padding:4px 8px;font-size:10px;font-weight:700;text-align:center;">
              <?php echo $s[0]; ?><br><?php echo $s[1]; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <p class="guide-p" style="margin-top:14px;"><strong>Teacher Availability</strong> (mark specific unavailable slots):</p>
  <ol class="guide-steps">
    <li>Go to <strong>Teacher Availability</strong> and select a teacher</li>
    <li>Click any grid cell to toggle it as <em>Unavailable</em> (shown in red)</li>
    <li>Add a reason (e.g. "PhD classes Wednesdays", "Administrative duty Fri P1")</li>
    <li>The generator will never schedule this teacher in a blocked slot — this is a hard block</li>
  </ol>
</div>

<!-- 8. WORKLOAD DASHBOARD (NEW) -->
<div id="sec-workload" class="section-card guide-section" style="border-color:#A9DFBF;">
  <div class="guide-h2"><div class="step-chip" style="background:#E67E22;">8</div> Teacher Workload Dashboard <span style="font-size:11px;background:#1E8449;color:#fff;padding:2px 8px;border-radius:4px;margin-left:6px;">NEW</span></div>
  <p class="guide-p">The Workload Dashboard gives you a <strong>bird's-eye view of all teachers' assigned loads before generation</strong>. It reads directly from the Subject Load configuration — not from any generated timetable — so you can catch and fix overloads proactively.</p>

  <div class="screen-mock">
    <div class="mock-header"><i class="fa fa-bar-chart"></i> Workload Dashboard — Pre-generation Load Analysis</div>
    <table class="mock-table">
      <tr><th></th><th>Teacher</th><th>Assigned PPW</th><th>Cap</th><th>Status</th><th>Load</th></tr>
      <tr style="background:#fff5f5;">
        <td style="text-align:center;"><i class="fa fa-chevron-right" style="color:#aaa;"></i></td>
        <td><strong>M. SHANTHI</strong><br><small style="color:#aaa;">MCE001</small></td>
        <td style="text-align:center;font-weight:700;color:#c0392b;">56</td>
        <td style="text-align:center;">36</td>
        <td><span class="mock-badge" style="background:#FADBD8;color:#922B21;">OVER +20</span></td>
        <td><div style="background:#e74c3c;height:8px;border-radius:4px;width:100%;"></div><small style="font-size:9px;color:#999;">155%</small></td>
      </tr>
      <tr style="background:#fffdf0;">
        <td style="text-align:center;"><i class="fa fa-chevron-right" style="color:#aaa;"></i></td>
        <td><strong>SHARON SHAJI</strong><br><small style="color:#aaa;">MCE002</small></td>
        <td style="text-align:center;font-weight:700;color:#D4AC0D;">30</td>
        <td style="text-align:center;">36</td>
        <td><span class="mock-badge" style="background:#FEF9E7;color:#7D6608;">NEAR 83%</span></td>
        <td><div style="background:#f39c12;height:8px;border-radius:4px;width:83%;"></div><small style="font-size:9px;color:#999;">83%</small></td>
      </tr>
      <tr>
        <td style="text-align:center;"><i class="fa fa-chevron-right" style="color:#aaa;"></i></td>
        <td><strong>R. KUMAR</strong><br><small style="color:#aaa;">MCE003</small></td>
        <td style="text-align:center;">22</td>
        <td style="text-align:center;">36</td>
        <td><span class="mock-badge" style="background:#D5F5E3;color:#1E8449;">OK</span></td>
        <td><div style="background:#27ae60;height:8px;border-radius:4px;width:61%;"></div><small style="font-size:9px;color:#999;">61%</small></td>
      </tr>
    </table>
  </div>

  <ol class="guide-steps">
    <li>Go to <strong>Auto Timetable → Workload Dashboard</strong> (or follow the link from Verify Constraints when an overload is detected)</li>
    <li>The dashboard loads automatically — teachers sorted by total PPW descending, with a colour-coded status badge: <span style="background:#FADBD8;color:#922B21;padding:1px 6px;border-radius:3px;font-size:11px;font-weight:700;">OVER</span> <span style="background:#FEF9E7;color:#7D6608;padding:1px 6px;border-radius:3px;font-size:11px;font-weight:700;">NEAR</span> <span style="background:#D5F5E3;color:#1E8449;padding:1px 6px;border-radius:3px;font-size:11px;font-weight:700;">OK</span></li>
    <li>Use the filter buttons at the top to show <strong>All / Overloaded Only / Near Limit</strong></li>
    <li>Click the <strong>expand arrow</strong> (<i class="fa fa-chevron-right"></i>) on any teacher row to see the full class-subject breakdown — which classes are contributing to their load</li>
    <li>Click <strong>Reassign</strong> next to any assignment to move that subject to a different teacher — a modal opens with a staff dropdown</li>
    <li>After reassigning, the dashboard refreshes automatically to show the updated totals</li>
    <li>Repeat until all teachers are within their weekly cap, then go to <strong>Auto Generate</strong></li>
  </ol>

  <div class="tip-box"><i class="fa fa-lightbulb-o"></i> <strong>Joint lesson rows</strong> are shown as read-only with a <span style="background:#e2e6ea;color:#495057;padding:1px 6px;border-radius:3px;font-size:11px;font-weight:700;">Joint</span> tag. To change teachers in a joint lesson, use the <strong>Joint Lessons</strong> screen instead.</div>
  <div class="new-box"><i class="fa fa-lightbulb-o"></i> The cap shown is each teacher's configured <em>Max Periods/Week</em> constraint, or the default <strong>36</strong> for unconfigured teachers. Reducing a teacher's load here (via Reassign) immediately affects what the generator will attempt.</div>
</div>

<!-- 9. JOINT LESSONS -->
<div id="sec-joint" class="section-card guide-section">
  <div class="guide-h2"><div class="step-chip" style="background:#E67E22;">9</div> Joint Lessons <span style="font-size:12px;font-weight:500;color:#999;margin-left:6px;">(Cross-Class)</span></div>
  <p class="guide-p">Joint Lessons allow <strong>two or more classes to share the same period together</strong> — for example, a combined PE session, a visiting faculty lecture for all first-year sections, or a shared assembly.</p>
  <ol class="guide-steps">
    <li>Go to <strong>Auto Timetable → Joint Lessons</strong> and click <strong>Add Joint Lesson</strong></li>
    <li>Enter a Name (e.g. "PE Combined I CSE All"), select the <strong>Subject</strong> and <strong>Teacher(s)</strong></li>
    <li>Set <strong>Periods / Week</strong> and <strong>Consecutive</strong> (e.g. 2 for a double period)</li>
    <li>Under <strong>Participating Classes</strong>, tick all class-sections that share this lesson</li>
    <li>Set a <strong>Priority</strong> (1–10) — higher priority joint lessons are placed first in the generator's pre-pass</li>
    <li>Optionally pin one or more placements to an exact day/period using <strong>Fixed Slot(s)</strong> (see below)</li>
    <li>Save. The generator finds a slot where ALL selected classes and ALL assigned teachers are simultaneously free</li>
  </ol>

  <div class="new-box">
    <strong><i class="fa fa-users"></i> All Teachers Shown on Class Timetable</strong><br>
    When a joint lesson is assigned to multiple teachers, the <strong>Class Timetable Grid, Print view, PDF, and Excel export</strong> all show every teacher's name (e.g. "SHAN. + ALI. + RAVI."). In the Teacher Timetable view, each teacher sees only their own name — only the classes they are personally involved in appear on their individual timetable.
  </div>

  <div class="new-box" style="margin-top:8px;">
    <strong><i class="fa fa-thumb-tack"></i> Fixed Slot(s) — pin a joint lesson to an exact day/period <span style="background:#28a745;color:#fff;padding:1px 6px;border-radius:3px;font-size:10px;font-weight:700;margin-left:4px;">New</span></strong><br>
    Need a joint lesson to land on one specific slot every time (e.g. a combined assembly that must always be Tuesday Period 3–4)? Open the lesson's edit form and set <strong>Fixed Slot(s)</strong> for that placement — choose the Day and the Period(s). The generator will try <em>only</em> that exact slot for that placement instead of searching the whole week. Placements left on "Auto" are still placed by the normal full-week search.
    <ul style="margin:6px 0 0 18px;">
      <li>If the pinned slot can't actually be used (a class or teacher is already busy there, or a hard constraint blocks it), generation reports a specific conflict explaining <em>why</em> — it will not silently place the lesson somewhere else.</li>
      <li>Make sure the slot you pin is <strong>not</strong> also blocked for that subject in <strong>Subject Time-Off</strong> (see the warning below) — a slot that's both "fixed" and "blocked" can never be used.</li>
    </ul>
  </div>

  <div class="warn-box" style="margin-top:8px;">
    <strong><i class="fa fa-exclamation-triangle"></i> Subject Time-Off blocks a slot — it does not reserve one</strong><br>
    The <strong>Subject Time-Off</strong> grid (Auto Timetable → Subject Time-Off) is a pure <em>blocking</em> tool: every cell defaults to Allowed (green check), and clicking a cell marks it Blocked (red X) so that subject can <strong>never</strong> be scheduled there. Clicking a cell does <strong>not</strong> restrict the subject to only that slot — it does the opposite. If you want a lesson to land on one particular slot, use <strong>Fixed Slot(s)</strong> on the Joint Lesson itself (above), not Subject Time-Off.
  </div>

  <div class="new-box" style="margin-top:8px;">
    <strong><i class="fa fa-ban"></i> Subject Time-Off is Respected for Joint Lessons</strong><br>
    If a subject has a time-off block configured in <strong>Subject Unavailability</strong>, the joint lesson generator correctly skips those slots, just like regular lessons do.
  </div>

  <div class="warn-box"><i class="fa fa-exclamation-triangle"></i> Joint lessons are the hardest constraint to satisfy — the more classes and teachers involved, the fewer valid slots exist. Keep joint lessons minimal and set priority 8–10 so they are placed before regular subjects.</div>
</div>

<!-- 10. AUTO GENERATE -->
<div id="sec-generate" class="section-card guide-section">
  <div class="guide-h2"><div class="step-chip" style="background:#1F618D;">10</div> Auto Generate</div>
  <p class="guide-p">Once all setup is complete, run the generator. It uses a greedy constraint-satisfaction algorithm with soft-constraint scoring to place all lessons while respecting every rule configured.</p>

  <p class="guide-p"><strong>Generator Modes:</strong></p>
  <table class="constraint-table" style="width:100%;">
    <tr><th style="width:100px;">Normal</th><td>1 pass. Fast. Good for daily iteration and testing.</td></tr>
    <tr><th>Large</th><td>3 passes — keeps the best result. Better quality, takes ~3× longer.</td></tr>
    <tr><th>Huge</th><td>10 passes. Best quality for complex schedules. Use for your final confirmed generation.</td></tr>
  </table>

  <p class="guide-p" style="margin-top:12px;"><strong>Strictness:</strong></p>
  <table class="constraint-table" style="width:100%;">
    <tr><th style="width:100px;">Relaxed</th><td>Allows up to 3 periods/day regardless of Max/Day setting. Packs the schedule tighter — useful when many subjects need to fit.</td></tr>
    <tr><th>Normal</th><td>Respects Max/Day exactly as configured in Subject Load.</td></tr>
    <tr><th>Strict</th><td>Limits all subjects to max 1 period/day regardless of setting. Maximum spread across the week.</td></tr>
  </table>

  <p class="guide-p" style="margin-top:14px;"><strong>Recommended workflow:</strong></p>
  <ol class="guide-steps">
    <li>Click <strong>Verify Constraints &amp; Readiness</strong> first — it checks for missing periods, overloaded teachers, insufficient slots, and missing teacher assignments</li>
    <li>If the pre-flight shows <em>Teacher overload</em> warnings, click the <strong>Workload Dashboard</strong> link in the result and reassign subjects until all teachers are within cap</li>
    <li>Select which <strong>class-sections</strong> to include in this run (tick all or a subset)</li>
    <li>Start with <strong>Normal mode</strong> for a quick preview — upgrade to <strong>Huge</strong> for the final timetable</li>
    <li>Review the <strong>Quality Score</strong> and per-class breakdown — 90%+ is excellent, 75%+ is acceptable</li>
    <li>Click <strong>Preview Draft</strong> to inspect the proposed grid before committing</li>
    <li>Click <strong>Confirm &amp; Publish</strong> to make it the live timetable — or <strong>Discard</strong> to try again with different settings</li>
  </ol>

  <div class="new-box">
    <strong><i class="fa fa-info-circle"></i> What the Generator Enforces (Hard Constraints)</strong><br>
    All of the following are enforced as hard limits — a slot is rejected outright if any of these would be violated:
    <ul style="margin:6px 0 0 16px;font-size:12px;line-height:1.8;">
      <li>Teacher max periods per day and per week (default 6/day, 36/week)</li>
      <li>Teacher availability blocks</li>
      <li>Subject time-off blocks</li>
      <li>Class and room availability blocks</li>
      <li>Teaching window (not before / not after times)</li>
      <li>Max consecutive periods + required break after</li>
      <li>Max idle gap between teaching blocks</li>
      <li>Min free periods per day (tightens effective daily cap)</li>
      <li>Avoid first / avoid last period flags</li>
    </ul>
  </div>

  <div class="tip-box" style="margin-top:10px;"><i class="fa fa-lightbulb-o"></i> If quality is below 75%: try <strong>Large/Huge</strong> mode first; then check the <strong>Workload Dashboard</strong> for overloaded teachers; or relax some Max/Day constraints in Subject Load. Each unplaced lesson gets a diagnosis message explaining why it couldn't be placed.</div>
</div>

<!-- 11. CLASS GRID -->
<div id="sec-classgrid" class="section-card guide-section">
  <div class="guide-h2"><div class="step-chip" style="background:#2980B9;">11</div> Class Timetable Grid</div>
  <p class="guide-p">The Class Grid is the primary view for viewing and manually editing a class's timetable after generation. Joint lessons show all participating teachers in the cell.</p>

  <div class="screen-mock">
    <div class="mock-header"><i class="fa fa-th-large"></i> Class Grid — II CSE Section A</div>
    <table class="mock-table">
      <tr style="background:#3c8dbc;color:#fff;">
        <th style="background:#2c6e9e;color:#fff;width:80px;">Period</th>
        <th style="background:#3c8dbc;color:#fff;">Mon</th><th style="background:#3c8dbc;color:#fff;">Tue</th>
        <th style="background:#3c8dbc;color:#fff;">Wed</th><th style="background:#3c8dbc;color:#fff;">Thu</th>
        <th style="background:#3c8dbc;color:#fff;">Fri</th>
      </tr>
      <tr>
        <td style="background:#f1f5f9;font-size:10px;font-weight:600;text-align:center;">P1<br>08:30</td>
        <td style="background:#FADBD8;text-align:center;cursor:pointer;"><small style="font-weight:700;color:#7B241C;">MATHS</small><br><small style="color:#888;font-size:9px;">KUM..</small></td>
        <td style="background:#D1F2EB;text-align:center;cursor:pointer;"><small style="font-weight:700;color:#0E6655;">PHY</small><br><small style="color:#888;font-size:9px;">RAV..</small></td>
        <td style="background:#E8DAEF;text-align:center;cursor:pointer;"><small style="font-weight:700;color:#6C3483;">PE (Joint)</small><br><small style="color:#888;font-size:9px;">SHA.+ALI.</small></td>
        <td style="background:#D6EAF8;text-align:center;cursor:pointer;"><small style="font-weight:700;color:#1A5276;">CS</small><br><small style="color:#888;font-size:9px;">ALI..</small></td>
        <td style="background:#FADBD8;text-align:center;cursor:pointer;"><span style="font-size:9px;color:#CB4335;">🔒</span><small style="font-weight:700;color:#7B241C;"> MATHS</small><br><small style="color:#888;font-size:9px;">KUM..</small></td>
      </tr>
    </table>
    <div style="padding:8px 12px;background:#fffdf5;font-size:10px;color:#7D6608;border-top:1px solid #fdebd0;"><i class="fa fa-info-circle"></i> &nbsp; Click any cell to edit · 🔒 = Locked · Joint lessons show all teachers (hover to see full names)</div>
  </div>

  <ol class="guide-steps">
    <li>Select <strong>Class</strong> and <strong>Section</strong>, then click <strong>Load Schedule</strong></li>
    <li>Click any filled cell to <strong>edit</strong> — change teacher, subject, or room</li>
    <li>Click an empty cell to <strong>add a manual entry</strong></li>
    <li>Click the <strong>Lock</strong> icon on a cell to protect it — locked cells are preserved when you re-generate</li>
    <li>Joint lesson cells show abbreviated names of all teachers (e.g. SHA.+ALI.) — hover to see full names</li>
    <li>Use the <strong>Print / PDF / Excel</strong> buttons to export the timetable with full teacher names</li>
  </ol>
  <div class="tip-box"><i class="fa fa-lightbulb-o"></i> After confirming a generated timetable, <strong>lock critical slots</strong> (labs, joint lessons) before re-running the generator for adjustments or additional classes.</div>
</div>

<!-- 12. TEACHER VIEW -->
<div id="sec-teacherview" class="section-card guide-section">
  <div class="guide-h2"><div class="step-chip" style="background:#7D3C98;">12</div> Teacher Timetable View</div>
  <p class="guide-p">Shows the timetable from a teacher's perspective — all their classes across the week in a single grid. For joint lessons, each teacher sees only their own entry on their individual timetable.</p>
  <ol class="guide-steps">
    <li>Go to <strong>Auto Timetable → Teacher Timetable</strong></li>
    <li>Select a teacher from the dropdown — their full weekly schedule appears</li>
    <li>Use <strong>← Previous / Next Teacher →</strong> arrows to browse teachers without going back to the dropdown</li>
    <li>Click <strong>Print</strong> to open a clean print-ready version in a new window</li>
    <li>The grid shows class name, section, and subject for each slot — the teacher's name is <em>not</em> shown (it's their own timetable)</li>
  </ol>
  <div class="tip-box"><i class="fa fa-lightbulb-o"></i> Use Teacher View to verify that no teacher has too many periods bunched on one day. If the spread looks uneven, go to <strong>Teacher Constraints</strong> and set <em>Max Idle Gap</em> or <em>Min Free / Day</em>, then re-generate.</div>
</div>

<!-- 13. SUBSTITUTION -->
<div id="sec-substitution" class="section-card guide-section">
  <div class="guide-h2"><div class="step-chip" style="background:#CB4335;">13</div> Substitution</div>
  <p class="guide-p">When a teacher is absent, the Substitution module finds and records a replacement teacher for each of their affected periods that day.</p>
  <ol class="guide-steps">
    <li>Go to <strong>Auto Timetable → Substitution</strong></li>
    <li>Select the <strong>absent teacher</strong> and the <strong>date</strong> of absence</li>
    <li>Click <strong>Load Affected Periods</strong> — the system shows all classes that teacher has that day</li>
    <li>For each period, select a <strong>substitute teacher</strong> from the dropdown — only free teachers for that slot are shown</li>
    <li>Click <strong>Confirm</strong> for each assignment — status changes from Pending → Confirmed</li>
    <li>Confirmed substitutions appear in reports and can be viewed per class or per teacher</li>
    <li>To cancel a substitution, click the entry and select <strong>Cancel Substitution</strong></li>
  </ol>
  <div class="tip-box"><i class="fa fa-lightbulb-o"></i> The substitute dropdown shows only teachers who are <strong>free at that specific slot</strong> — no manual conflict checking needed.</div>
  <div class="warn-box"><i class="fa fa-exclamation-triangle"></i> Substitution records are linked to the <strong>confirmed timetable</strong>. If you re-generate and re-confirm a different timetable, archive substitution data first before major timetable revisions.</div>
</div>

<!-- 14. LESSON BROWSER -->
<div id="sec-browser" class="section-card guide-section">
  <div class="guide-h2"><div class="step-chip" style="background:#1F618D;">14</div> Lesson Browser</div>
  <p class="guide-p">The Lesson Browser gives a global, filterable view of every lesson configured in Subject Load — across all classes, sections, teachers, and subjects in one table. Use it as an audit tool before generating.</p>
  <ol class="guide-steps">
    <li>Go to <strong>Auto Timetable → Lesson Browser</strong></li>
    <li>Filter by <strong>Department</strong>, <strong>Teacher</strong>, or <strong>Subject</strong> — or leave all blank to see everything</li>
    <li>The table shows: Class, Section, Subject, Code, Type, Teacher, Employee ID, Periods/Week, Consecutive, Max/Day, Batch</li>
    <li>Filter by teacher to see their total PPW across all classes — compare against their constraint cap</li>
    <li>Use the <strong>Workload Dashboard</strong> for a more visual, aggregated view of the same information</li>
  </ol>
</div>

<!-- PRO TIPS -->
<div class="section-card guide-section" style="background:linear-gradient(135deg,#EBF4FB,#F5EEF8);">
  <div class="guide-h2"><i class="fa fa-star" style="color:#D4AC0D;"></i> Pro Tips &amp; Best Practices</div>
  <div style="columns:2;column-gap:20px;margin-top:10px;" class="guide-p">
    <div style="break-inside:avoid;background:#fff;border-radius:8px;padding:12px;margin-bottom:10px;border-left:3px solid #2980B9;">
      <strong style="color:#1A5276;">Check Workload Dashboard First</strong><br>
      Before generating, always open the Workload Dashboard. Any teacher over 36 PPW will cause the generator to report conflicts. Fix overloads by reassigning subjects — you cannot generate your way out of an over-allocated teacher.
    </div>
    <div style="break-inside:avoid;background:#fff;border-radius:8px;padding:12px;margin-bottom:10px;border-left:3px solid #CB4335;">
      <strong style="color:#922B21;">Set Consecutive Limits for Teachers</strong><br>
      Use Max Consecutive Periods + Min Break to prevent teachers from teaching 5+ hours straight. A realistic setting for most teachers: 3 consecutive, 1 break. This avoids burnout patterns the generator would otherwise create.
    </div>
    <div style="break-inside:avoid;background:#fff;border-radius:8px;padding:12px;margin-bottom:10px;border-left:3px solid #1E8449;">
      <strong style="color:#196F3D;">Lock After Confirming</strong><br>
      After confirming, lock important cells (labs, joint lessons) in the Class Grid before re-generating for adjustments. Locked cells are always preserved through subsequent generations.
    </div>
    <div style="break-inside:avoid;background:#fff;border-radius:8px;padding:12px;margin-bottom:10px;border-left:3px solid #7D3C98;">
      <strong style="color:#6C3483;">Iterative Generation</strong><br>
      Start with Normal mode for speed. If quality &lt; 80%, try Large. Reserve Huge for the final confirmed timetable. Each pass is independent — Huge mode picks the best of 10 runs.
    </div>
    <div style="break-inside:avoid;background:#fff;border-radius:8px;padding:12px;margin-bottom:10px;border-left:3px solid #17A589;">
      <strong style="color:#0E6655;">Overflow Subjects (PPW &gt; Days)</strong><br>
      If a subject needs more periods than working days (e.g. 7 PPW, 6-day week), the generator automatically handles the overflow with a double period. No manual setup needed.
    </div>
    <div style="break-inside:avoid;background:#fff;border-radius:8px;padding:12px;margin-bottom:10px;border-left:3px solid #D4AC0D;">
      <strong style="color:#9A7D0A;">Quality Score Guide</strong><br>
      90–100% = Excellent. 75–89% = Good — review any unplaced lessons. 60–74% = Check teacher overloads and blocked slots. Below 60% = Likely an infeasible constraint combination — use the diagnosis messages.
    </div>
    <div style="break-inside:avoid;background:#fff;border-radius:8px;padding:12px;margin-bottom:10px;border-left:3px solid #E67E22;">
      <strong style="color:#784212;">Joint Lessons First</strong><br>
      Joint lessons are placed in a pre-pass before regular subjects. Set them to Priority 8–10. If a joint lesson keeps failing (all classes share a common block somewhere), check teacher availability and subject time-off blocks for conflicts.
    </div>
    <div style="break-inside:avoid;background:#fff;border-radius:8px;padding:12px;margin-bottom:10px;border-left:3px solid #1F618D;">
      <strong style="color:#1A5276;">Session Management</strong><br>
      Each academic session needs its own Period Setup and Subject Load. Rooms, teacher constraints, and staff data carry across sessions. Run the Workload Dashboard each new session — teacher assignments often change.
    </div>
  </div>
</div>

<div style="text-align:center;padding:20px 0 10px;">
  <a href="<?php echo site_url('admin/tt/dashboard'); ?>" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
  <a href="<?php echo site_url('admin/tt/periods'); ?>"    class="btn btn-success"  style="margin-left:10px;"><i class="fa fa-play"></i> Start Setup</a>
  <a href="<?php echo site_url('admin/tt/teacher_workload_dashboard'); ?>" class="btn btn-warning" style="margin-left:10px;"><i class="fa fa-bar-chart"></i> Workload Dashboard</a>
</div>

</div><!-- col-md-9 -->
</div><!-- row -->

<script>
(function(){
  var sections = document.querySelectorAll('.guide-section');
  var tocLinks = document.querySelectorAll('.toc-link[href^="#"]');
  window.addEventListener('scroll', function(){
    var scrollY = window.scrollY + 90;
    sections.forEach(function(sec){
      var top = sec.offsetTop, bot = top + sec.offsetHeight;
      if(scrollY >= top && scrollY < bot){
        tocLinks.forEach(function(l){ l.classList.remove('active'); });
        var link = document.querySelector('.toc-link[href="#'+sec.id+'"]');
        if(link) link.classList.add('active');
      }
    });
  });
})();
</script>

</section>
</div>
