<?php
$low_limit = isset($low_att_limit) ? (int)$low_att_limit : 75;

// Helper: extract clean letter from key_value (which may contain HTML like <b class="...">P</b>)
function _att_clean_key($attendencetypeslist, $id) {
    if (!$id && $id !== 0) return null;
    foreach ((array)$attendencetypeslist as $t) {
        if ($t['id'] == $id) return strip_tags($t['key_value'] ?? '');
    }
    return null;
}

// Helper: get the attendence type name (Present, Absent, etc.)
function _att_type_name($attendencetypeslist, $id) {
    if (!$id) return null;
    foreach ((array)$attendencetypeslist as $t) {
        if ($t['id'] == $id) return $t['type'] ?? strip_tags($t['key_value'] ?? '');
    }
    return null;
}

// Pre-compute summary stats
$total_held = 0; $total_present = 0; $total_absent = 0; $total_na = 0;
$days_with_class = 0;

if (!empty($resultlist['students_attendances'])) {
    foreach ($resultlist['students_attendances'] as $day_data) {
        if (empty($day_data['subjects'])) continue;
        $days_with_class++;
        foreach ($day_data['subjects'] as $idx => $subj) {
            $count  = $idx + 1;
            $att_id = isset($day_data['attendances']) ? ($day_data['attendances']->{"attendence_type_id_".$count} ?? '') : '';
            $total_held++;
            if ($att_id == 1)  $total_present++;
            elseif ($att_id == 4) $total_absent++;
            elseif ($att_id === '') $total_na++;
        }
    }
}
$pct       = $total_held > 0 ? round($total_present * 100 / $total_held, 1) : null;
$pct_color = is_null($pct) ? '#6b7280' : ($pct >= 80 ? '#059669' : ($pct >= $low_limit ? '#d97706' : '#dc2626'));
$pct_bg    = is_null($pct) ? '#f3f4f6' : ($pct >= 80 ? '#d1fae5' : ($pct >= $low_limit ? '#fef3c7' : '#fee2e2'));
?>
<style>
/* ── Filter form ────────────────────────────────────────────────── */
.rfb { background:#fff; border-radius:12px; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,.06); padding:18px 20px 12px; margin-bottom:18px; }
.rfb .form-group label { font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; margin-bottom:4px; display:block; }
.rfb .form-control { border-radius:8px; border-color:#e5e7eb; font-size:13px; height:36px; }
.rpt-search-btn { background:linear-gradient(135deg,#6366f1,#8b5cf6); border:none; border-radius:8px; color:#fff; padding:7px 20px; font-size:13px; font-weight:600; }
/* ── Student banner ─────────────────────────────────────────────── */
.stu-banner { background:linear-gradient(135deg,#6366f1,#8b5cf6); border-radius:14px; padding:20px 24px; color:#fff; display:flex; align-items:center; gap:16px; margin-bottom:16px; }
.stu-banner-av { width:52px; height:52px; border-radius:50%; background:rgba(255,255,255,.2); display:flex; align-items:center; justify-content:center; font-size:20px; font-weight:800; flex-shrink:0; border:2px solid rgba(255,255,255,.4); }
.stu-banner-name { font-size:18px; font-weight:800; letter-spacing:-.2px; }
.stu-banner-meta { font-size:12px; opacity:.8; margin-top:3px; }
.stu-banner-pct { margin-left:auto; text-align:center; background:rgba(255,255,255,.15); border-radius:12px; padding:10px 18px; }
.stu-banner-pct-val { font-size:34px; font-weight:800; line-height:1; }
.stu-banner-pct-lbl { font-size:11px; opacity:.8; margin-top:2px; }
.stu-banner-actions { display:flex; flex-direction:column; gap:6px; }
.stu-banner-btn { display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:7px; font-size:11px; font-weight:700; text-decoration:none; border:1.5px solid rgba(255,255,255,.5); background:rgba(255,255,255,.12); color:#fff; cursor:pointer; white-space:nowrap; }
.stu-banner-btn:hover { background:rgba(255,255,255,.25); text-decoration:none; color:#fff; }
/* ── Stat cards ─────────────────────────────────────────────────── */
.stu-stats { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
.stu-stat { flex:1; min-width:100px; background:#fff; border-radius:10px; border:1px solid #e5e7eb; box-shadow:0 1px 4px rgba(0,0,0,.05); padding:12px 14px; text-align:center; }
.stu-stat-val { font-size:24px; font-weight:800; line-height:1; }
.stu-stat-lbl { font-size:11px; color:#6b7280; margin-top:3px; font-weight:600; }
/* ── Main content columns ───────────────────────────────────────── */
.stu-body { display:grid; grid-template-columns:1fr 320px; gap:18px; }
@media(max-width:1100px){ .stu-body{grid-template-columns:1fr;} }
/* ── Period details ─────────────────────────────────────────────── */
.stu-detail-box { background:#fff; border-radius:12px; border:1px solid #e5e7eb; box-shadow:0 1px 6px rgba(0,0,0,.05); display:flex; flex-direction:column; }
.stu-detail-hdr { padding:12px 16px; border-bottom:1px solid #f3f4f6; font-size:13px; font-weight:700; color:#111827; display:flex; align-items:center; gap:8px; flex-shrink:0; }
.stu-detail-hdr i { color:#6366f1; }
.stu-detail-scroll { overflow-y:auto; max-height:65vh; flex:1; }
.day-block { border-bottom:1px solid #f3f4f6; }
.day-block:last-child { border-bottom:none; }
.day-hdr { padding:10px 16px; background:#f8f9fb; display:flex; align-items:center; gap:10px; position:sticky; top:0; z-index:1; border-bottom:1px solid #f3f4f6; }
.day-date  { font-size:13px; font-weight:700; color:#1f2937; }
.day-name  { font-size:11px; color:#6b7280; background:#e5e7eb; border-radius:10px; padding:1px 8px; }
.day-stat  { margin-left:auto; font-size:12px; font-weight:700; }
.period-row { display:flex; align-items:center; gap:10px; padding:9px 16px; border-bottom:1px solid #f9fafb; }
.period-row:last-child { border-bottom:none; }
.period-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.period-subj { flex:1; min-width:0; }
.period-subj-name { font-size:12px; font-weight:600; color:#1f2937; }
.period-subj-code { font-size:10px; color:#9ca3af; }
.period-time { font-size:11px; color:#6b7280; white-space:nowrap; padding-right:8px; }
/* Attendance pills — clean, no HTML from DB */
.att-pill { display:inline-flex; align-items:center; justify-content:center; width:32px; height:28px; border-radius:7px; font-size:12px; font-weight:800; flex-shrink:0; }
.att-P, .att-E { background:#d1fae5; color:#065f46; }
.att-A         { background:#fee2e2; color:#991b1b; }
.att-L         { background:#fef3c7; color:#78350f; }
.att-H         { background:#dbeafe; color:#1e40af; }
.att-F, .att-HD { background:#f3e8ff; color:#6b21a8; }
.att-NA        { background:#f3f4f6; color:#9ca3af; }
/* ── Right sidebar ──────────────────────────────────────────────── */
.stu-sidebar { display:flex; flex-direction:column; gap:16px; }
.stu-box { background:#fff; border-radius:12px; border:1px solid #e5e7eb; box-shadow:0 1px 6px rgba(0,0,0,.05); overflow:hidden; }
.stu-box-hdr { padding:11px 14px; border-bottom:1px solid #f3f4f6; font-size:12px; font-weight:700; color:#374151; display:flex; align-items:center; gap:6px; }
.stu-box-hdr i { color:#6366f1; font-size:13px; }
/* ── Calendar ───────────────────────────────────────────────────── */
.cal-wrap { padding:12px 14px; }
.cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:3px; }
.cal-hdr  { font-size:9px; font-weight:700; color:#9ca3af; text-align:center; padding:2px 0; }
.cal-day  { border-radius:6px; padding:4px 2px; text-align:center; font-size:11px; font-weight:700; min-height:34px; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.cal-day .dn { font-size:12px; font-weight:700; }
.cal-day .dp { font-size:9px; margin-top:1px; }
.cal-present { background:#d1fae5; color:#065f46; }
.cal-absent  { background:#fee2e2; color:#991b1b; }
.cal-mixed   { background:#fef3c7; color:#78350f; }
.cal-noclass { background:#f9fafb; color:#d1d5db; }
.cal-empty   { }
.cal-legend  { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
.cal-legend span { font-size:10px; color:#6b7280; display:flex; align-items:center; gap:4px; }
.cal-legend .dot { width:10px; height:10px; border-radius:3px; }
/* ── Donut chart ────────────────────────────────────────────────── */
#breakdownChart { height:180px; }
</style>

<div class="content-wrapper">
<section class="content-header">
  <h1 style="font-size:20px;font-weight:700;color:#111827;">
    <i class="fa fa-user-circle-o" style="color:#6366f1;margin-right:8px;"></i>
    Student Period Attendance
    <small style="font-size:13px;font-weight:400;color:#6b7280;margin-left:6px;">Monthly timeline</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
    <li><a href="<?php echo site_url('admin/attendancedashboard/index'); ?>">Attendance Dashboard</a></li>
    <li class="active">Student Report</li>
  </ol>
</section>

<section class="content" style="padding:10px 15px 40px;">
  <?php $this->load->view('attendencereports/_attendance'); ?>

  <!-- ── Filter form ─────────────────────────────────────────── -->
  <div class="rfb">
    <form method="post" action="<?php echo site_url('attendencereports/reportbymonthstudent'); ?>">
      <?php echo $this->customlib->getCSRF(); ?>
      <div class="row">
        <?php if ($sch_setting->institution_type == 'college'): ?>
        <div class="col-md-2">
          <div class="form-group">
            <label>Department</label>
            <select id="department_id" name="department_id" class="form-control">
              <option value="">All Departments</option>
              <?php foreach ($department_list as $d): ?>
              <option value="<?php echo $d['id']; ?>" <?php if (set_value('department_id')==$d['id']) echo 'selected'; ?>><?php echo htmlspecialchars($d['department_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <?php endif; ?>
        <div class="col-md-2">
          <div class="form-group">
            <label>Class <small class="req">*</small></label>
            <select id="class_id" name="class_id" class="form-control">
              <option value="">Select Class</option>
              <?php foreach ($classlist as $c): ?>
              <option value="<?php echo $c['id']; ?>" <?php if (set_value('class_id')==$c['id']) echo 'selected'; ?>><?php echo htmlspecialchars($c['class']); ?></option>
              <?php endforeach; ?>
            </select>
            <span class="text-danger"><?php echo form_error('class_id'); ?></span>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>Section <small class="req">*</small></label>
            <select id="section_id" name="section_id" class="form-control"><option value="">Select Section</option></select>
            <span class="text-danger"><?php echo form_error('section_id'); ?></span>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>Student <small class="req">*</small></label>
            <select id="student_id" name="student_id" class="form-control"><option value="">Select Student</option></select>
            <span class="text-danger"><?php echo form_error('student_id'); ?></span>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>Month <small class="req">*</small></label>
            <select id="month" name="month" class="form-control">
              <option value="">Select Month</option>
              <?php foreach ($monthlist as $mk => $mv): ?>
              <option value="<?php echo $mk; ?>" <?php echo set_select('month', $mk); ?>><?php echo $mv; ?></option>
              <?php endforeach; ?>
            </select>
            <span class="text-danger"><?php echo form_error('month'); ?></span>
          </div>
        </div>
        <div class="col-md-2" style="padding-top:22px;">
          <button type="submit" class="btn rpt-search-btn btn-block"><i class="fa fa-search"></i> View Report</button>
        </div>
      </div>
    </form>
  </div>

  <?php if (isset($resultlist)): ?>
  <?php if (empty($resultlist) || empty($resultlist['students_attendances'])): ?>
    <div class="rfb" style="text-align:center;padding:40px;color:#6b7280;">
      <i class="fa fa-calendar-times-o" style="font-size:36px;color:#dde1e7;display:block;margin-bottom:10px;"></i>
      No attendance data found for this student in the selected month.
    </div>
  <?php else: ?>

  <!-- ── Student banner ─────────────────────────────────────── -->
  <div class="stu-banner">
    <div class="stu-banner-av"><i class="fa fa-user"></i></div>
    <div>
      <div class="stu-banner-name">
        <?php if (!empty($resultlist['student_info'])): ?>
        <?php echo htmlspecialchars($this->customlib->getFullName(
            $resultlist['student_info']->firstname ?? '',
            $resultlist['student_info']->middlename ?? '',
            $resultlist['student_info']->lastname ?? '',
            $sch_setting->middlename, $sch_setting->lastname
        )); ?>
        <?php else: ?>Student Report<?php endif; ?>
      </div>
      <div class="stu-banner-meta">
        <?php if (!empty($resultlist['student_info'])): ?>
        Adm: <?php echo htmlspecialchars($resultlist['student_info']->admission_no ?? ''); ?>
        &nbsp;&bull;&nbsp;
        <?php echo htmlspecialchars($resultlist['student_info']->class ?? ''); ?>
        <?php echo htmlspecialchars($resultlist['student_info']->section ?? ''); ?>
        <?php endif; ?>
      </div>
    </div>
    <div class="stu-banner-pct">
      <div class="stu-banner-pct-val"><?php echo is_null($pct) ? '—' : $pct.'%'; ?></div>
      <div class="stu-banner-pct-lbl"><?php echo $total_present; ?> / <?php echo $total_held; ?> periods</div>
    </div>
    <div class="stu-banner-actions">
      <button onclick="exportStudentPDF()" class="stu-banner-btn"><i class="fa fa-file-pdf-o"></i> PDF</button>
      <button onclick="exportStudentWord()" class="stu-banner-btn"><i class="fa fa-file-word-o"></i> Word</button>
    </div>
  </div>

  <!-- ── Summary stat cards ─────────────────────────────────── -->
  <div class="stu-stats">
    <div class="stu-stat"><div class="stu-stat-val" style="color:#6366f1;"><?php echo $days_with_class; ?></div><div class="stu-stat-lbl">Days with Class</div></div>
    <div class="stu-stat"><div class="stu-stat-val" style="color:#374151;"><?php echo $total_held; ?></div><div class="stu-stat-lbl">Total Periods</div></div>
    <div class="stu-stat"><div class="stu-stat-val" style="color:#059669;"><?php echo $total_present; ?></div><div class="stu-stat-lbl">Present</div></div>
    <div class="stu-stat"><div class="stu-stat-val" style="color:#dc2626;"><?php echo $total_absent; ?></div><div class="stu-stat-lbl">Absent</div></div>
    <div class="stu-stat"><div class="stu-stat-val" style="color:#9ca3af;"><?php echo $total_na; ?></div><div class="stu-stat-lbl">Not Marked</div></div>
    <div class="stu-stat" style="background:<?php echo $pct_bg; ?>;border-color:<?php echo $pct_bg; ?>;">
      <div class="stu-stat-val" style="color:<?php echo $pct_color; ?>;"><?php echo is_null($pct) ? '—' : $pct.'%'; ?></div>
      <div class="stu-stat-lbl">Attendance %</div>
    </div>
  </div>

  <!-- ── Two-column body ────────────────────────────────────── -->
  <div class="stu-body">

    <!-- Period-by-period details (scrollable fixed height) -->
    <div class="stu-detail-box">
      <div class="stu-detail-hdr">
        <i class="fa fa-list"></i> Period-by-Period Details
        <span style="margin-left:auto;font-size:11px;color:#9ca3af;font-weight:400;">Scroll to see all days</span>
      </div>
      <div class="stu-detail-scroll" id="period-detail-scroll">
        <?php foreach ($resultlist['students_attendances'] as $day_data):
          if (empty($day_data['subjects'])) continue;
          $dp = 0; $dt = count($day_data['subjects']);
          foreach ($day_data['subjects'] as $idx => $s) {
              $c = $idx + 1;
              $aid = isset($day_data['attendances']) ? ($day_data['attendances']->{"attendence_type_id_".$c} ?? '') : '';
              if ($aid == 1) $dp++;
          }
          $day_pct   = $dt > 0 ? round($dp * 100 / $dt) : 0;
          $day_color = $dp === $dt ? '#059669' : ($dp > 0 ? '#d97706' : '#dc2626');
        ?>
        <div class="day-block">
          <div class="day-hdr">
            <span class="day-date"><?php echo htmlspecialchars($day_data['date']); ?></span>
            <span class="day-name"><?php echo htmlspecialchars($this->lang->line(strtolower($day_data['day'])) ?: $day_data['day']); ?></span>
            <span class="day-stat" style="color:<?php echo $day_color; ?>;"><?php echo $dp; ?>/<?php echo $dt; ?> present</span>
          </div>
          <?php foreach ($day_data['subjects'] as $idx => $subj):
            $c      = $idx + 1;
            $att_id = isset($day_data['attendances']) ? ($day_data['attendances']->{"attendence_type_id_".$c} ?? '') : '';
            $key    = _att_clean_key($attendencetypeslist ?? [], $att_id);
            if ($att_id === '') { $pill_key = 'NA'; $dot_col = '#d1d5db'; }
            else {
                $pill_key = strtoupper($key ?: 'NA');
                $dot_col  = $pill_key === 'P' ? '#059669' : ($pill_key === 'A' ? '#dc2626' : ($pill_key === 'H' ? '#3b82f6' : '#d97706'));
            }
          ?>
          <div class="period-row">
            <span class="period-dot" style="background:<?php echo $dot_col; ?>;"></span>
            <span class="period-subj">
              <span class="period-subj-name"><?php echo htmlspecialchars($subj->name); ?></span>
              <?php if ($subj->code): ?><span class="period-subj-code"> (<?php echo htmlspecialchars($subj->code); ?>)</span><?php endif; ?>
            </span>
            <span class="period-time"><i class="fa fa-clock-o" style="opacity:.4;margin-right:3px;"></i><?php echo htmlspecialchars($subj->time_from); ?>–<?php echo htmlspecialchars($subj->time_to); ?></span>
            <span class="att-pill att-<?php echo $pill_key; ?>"><?php echo $pill_key; ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Right sidebar: calendar + breakdown -->
    <div class="stu-sidebar">

      <!-- Mini calendar -->
      <div class="stu-box">
        <div class="stu-box-hdr"><i class="fa fa-calendar"></i> Month at a Glance</div>
        <div class="cal-wrap">
          <?php
          $sel_month = set_value('month');
          if ($sel_month && !empty($monthlist[$sel_month])) {
              $year_parts = explode('-', $sch_setting->session);
              $cal_year   = (int)($sel_month >= $sch_setting->start_month ? $year_parts[0] : $year_parts[1]);
          } else {
              $cal_year  = (int)date('Y');
              $sel_month = (int)date('n');
          }
          $cal_start  = $cal_year . '-' . sprintf('%02d', $sel_month) . '-01';
          $first_dow  = (int)date('w', strtotime($cal_start));
          $total_days = (int)date('t', strtotime($cal_start));

          // Build day-level stats
          $cal_data = [];
          foreach ($resultlist['students_attendances'] as $dd) {
              if (empty($dd['subjects'])) continue;
              $dp2 = 0; $dt2 = count($dd['subjects']);
              foreach ($dd['subjects'] as $idx2 => $s2) {
                  $c2 = $idx2 + 1;
                  $aid2 = isset($dd['attendances']) ? ($dd['attendances']->{"attendence_type_id_".$c2} ?? '') : '';
                  if ($aid2 == 1) $dp2++;
              }
              // Map formatted date back to day number
              for ($d = 1; $d <= $total_days; $d++) {
                  $ymd = $cal_year . '-' . sprintf('%02d', $sel_month) . '-' . sprintf('%02d', $d);
                  if ($this->customlib->dateformat($ymd) === $dd['date']) {
                      $cal_data[$d] = ['present' => $dp2, 'total' => $dt2];
                      break;
                  }
              }
          }
          ?>
          <div class="cal-grid">
            <?php foreach (['Su','Mo','Tu','We','Th','Fr','Sa'] as $h): ?>
            <div class="cal-hdr"><?php echo $h; ?></div>
            <?php endforeach; ?>
            <?php for ($b = 0; $b < $first_dow; $b++): ?>
            <div class="cal-day cal-empty"></div>
            <?php endfor; ?>
            <?php for ($d = 1; $d <= $total_days; $d++):
              if (isset($cal_data[$d])) {
                  $s = $cal_data[$d];
                  if ($s['present'] === $s['total'])      $dc = 'cal-present';
                  elseif ($s['present'] === 0)            $dc = 'cal-absent';
                  else                                    $dc = 'cal-mixed';
                  $dpct = $s['total'] > 0 ? round($s['present']*100/$s['total']).'%' : '';
              } else {
                  $dc = 'cal-noclass'; $dpct = '';
              }
            ?>
            <div class="cal-day <?php echo $dc; ?>" title="<?php echo $d.($dpct?' — '.$dpct:''); ?>">
              <span class="dn"><?php echo $d; ?></span>
              <?php if ($dpct): ?><span class="dp"><?php echo $dpct; ?></span><?php endif; ?>
            </div>
            <?php endfor; ?>
          </div>
          <div class="cal-legend">
            <span><span class="dot" style="background:#d1fae5;"></span>All Present</span>
            <span><span class="dot" style="background:#fef3c7;"></span>Partial</span>
            <span><span class="dot" style="background:#fee2e2;"></span>All Absent</span>
            <span><span class="dot" style="background:#f9fafb;"></span>No Class</span>
          </div>
        </div>
      </div>

      <!-- Breakdown donut -->
      <div class="stu-box">
        <div class="stu-box-hdr"><i class="fa fa-pie-chart"></i> Attendance Breakdown</div>
        <div style="padding:14px 16px 10px;">
          <?php if ($total_held > 0): ?>
          <canvas id="breakdownChart"></canvas>
          <?php else: ?>
          <div style="text-align:center;padding:30px;color:#9ca3af;">
            <i class="fa fa-pie-chart" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>
            No data to chart
          </div>
          <?php endif; ?>
        </div>
      </div>

    </div><!-- /.stu-sidebar -->
  </div><!-- /.stu-body -->

  <?php endif; ?>
  <?php endif; ?>

</section>
</div>

<?php
/* Hidden data for exports — always available when result exists */
if (isset($resultlist) && !empty($resultlist['students_attendances'])):
    $student_name_export = '';
    $student_adm_export  = '';
    if (!empty($resultlist['student_info'])) {
        $student_name_export = $this->customlib->getFullName(
            $resultlist['student_info']->firstname ?? '',
            $resultlist['student_info']->middlename ?? '',
            $resultlist['student_info']->lastname ?? '',
            $sch_setting->middlename, $sch_setting->lastname
        );
        $student_adm_export = $resultlist['student_info']->admission_no ?? '';
    }
endif;
?>

<script>
$(window).on('load', function() {
    var savedCls  = '<?php echo addslashes(set_value('class_id')); ?>';
    var savedSec  = '<?php echo addslashes(set_value('section_id')); ?>';
    var savedStd  = '<?php echo addslashes(set_value('student_id')); ?>';
    var savedSubj = '<?php echo addslashes(set_value('subject_id')); ?>';
    var isCollege = <?php echo ($sch_setting->institution_type == 'college') ? 'true' : 'false'; ?>;

    // ── Select2 ───────────────────────────────────────────────
    var s2 = {width:'100%', allowClear:true};
    $('#department_id').select2($.extend({},s2,{placeholder:'— All Departments —'}));
    $('#class_id').select2($.extend({},s2,{placeholder:'— Select Class —'}));
    $('#section_id').select2($.extend({},s2,{placeholder:'— Select Section —'}));
    $('#student_id').select2($.extend({},s2,{placeholder:'— Select Student —'}));
    $('#month').select2($.extend({},s2,{placeholder:'— Select Month —',allowClear:false}));

    // ── Restore cascade on reload ─────────────────────────────
    if (savedCls) {
        loadSections(savedCls, savedSec, function() {
            if (savedSec) { loadStudents(savedCls, savedSec, savedStd); }
        });
    }

    // ── Event chain ───────────────────────────────────────────
    $('#department_id').on('change', function() {
        var deptId = $(this).val();
        reset3(); // clear class/section/student
        if (!deptId || !isCollege) return;
        $.getJSON(baseurl+'classes/getClassesByDepartment',{department_id:deptId},function(data){
            var h='<option value="">— Select Class —</option>';
            $.each(data,function(i,o){h+='<option value="'+o.id+'">'+esc(o.class)+'</option>';});
            $('#class_id').html(h).trigger('change.select2');
        });
    });
    $('#class_id').on('change', function() {
        var cid=$(this).val();
        reset2(); // clear section/student
        if (cid) loadSections(cid,'',null);
    });
    $('#section_id').on('change', function() {
        var sid=$(this).val(), cid=$('#class_id').val();
        resetDrop('#student_id','— Select Student —');
        if (sid && cid) loadStudents(cid,sid,'');
    });

    // ── AJAX helpers ──────────────────────────────────────────
    function loadSections(cid, sel, cb) {
        $.getJSON(baseurl+'sections/getByClass',{class_id:cid,department_id:$('#department_id').val()},function(data){
            var h='<option value="">— Select Section —</option>';
            $.each(data,function(i,o){h+='<option value="'+o.section_id+'"'+(sel&&sel==o.section_id?' selected':'')+'>'+esc(o.section)+'</option>';});
            $('#section_id').html(h).trigger('change.select2');
            if (cb) cb();
        });
    }
    function loadStudents(cid, sid, sel) {
        $.getJSON(baseurl+'student/getByClassAndSection',{class_id:cid,section_id:sid,department_id:$('#department_id').val()},function(data){
            var h='<option value="">— Select Student —</option>';
            $.each(data,function(i,o){h+='<option value="'+o.id+'"'+(sel&&sel==o.id?' selected':'')+'>'+esc(o.full_name)+' ('+esc(o.admission_no)+')</option>';});
            $('#student_id').html(h).trigger('change.select2');
        });
    }
    function reset3(){ resetDrop('#class_id','— Select Class —'); reset2(); }
    function reset2(){ resetDrop('#section_id','— Select Section —'); resetDrop('#student_id','— Select Student —'); }
    function resetDrop(sel,ph){ $(sel).html('<option value="">'+ph+'</option>').trigger('change.select2'); }
    function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

    <?php if (isset($resultlist) && !empty($resultlist['students_attendances']) && $total_held > 0): ?>
    // ── Breakdown donut — explicit height fixes invisible chart ─
    new Chart(document.getElementById('breakdownChart'), {
        type:'doughnut',
        data:{
            labels:['Present','Absent','Not Marked'],
            datasets:[{
                data:[<?php echo $total_present; ?>,<?php echo $total_absent; ?>,<?php echo max(0,$total_held-$total_present-$total_absent); ?>],
                backgroundColor:['#059669','#dc2626','#d1d5db'],
                borderWidth:0, hoverOffset:4
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            cutout:'62%',
            plugins:{
                legend:{position:'bottom',labels:{font:{size:11},padding:12,usePointStyle:true}},
                tooltip:{callbacks:{label:function(c){
                    var pct=<?php echo $total_held; ?>>0?Math.round(c.parsed*100/<?php echo $total_held; ?>):0;
                    return c.label+': '+c.parsed+' ('+pct+'%)';
                }}}
            }
        }
    });
    <?php endif; ?>
});

<?php if (isset($resultlist) && !empty($resultlist['students_attendances'])): ?>
// ── Export helpers ──────────────────────────────────────────────
var SCHOOL_NAME   = '<?php echo addslashes(htmlspecialchars($sch_setting->name ?? '')); ?>';
var STUDENT_NAME  = '<?php echo addslashes(htmlspecialchars($student_name_export ?? '')); ?>';
var STUDENT_ADM   = '<?php echo addslashes(htmlspecialchars($student_adm_export ?? '')); ?>';
var MONTH_LABEL   = '<?php echo isset($month_label) ? addslashes(htmlspecialchars($month_label)) : ''; ?>';
var TOTAL_PCT     = '<?php echo is_null($pct) ? "—" : $pct."%"; ?>';
var TOTAL_PRESENT = <?php echo $total_present; ?>;
var TOTAL_HELD    = <?php echo $total_held; ?>;

function buildExportHTML() {
    var rows = '';
    document.querySelectorAll('.day-block').forEach(function(block) {
        var hdr = block.querySelector('.day-hdr');
        var date = hdr.querySelector('.day-date')?.textContent || '';
        var day  = hdr.querySelector('.day-name')?.textContent || '';
        var stat = hdr.querySelector('.day-stat')?.textContent || '';
        rows += '<tr style="background:#f8f9fb;"><td colspan="4" style="font-weight:700;padding:8px 12px;border:1px solid #e5e7eb;">'+
                date+' <span style="color:#6b7280;font-weight:400;">'+day+'</span>'+
                '<span style="float:right;color:#374151;">'+stat+'</span></td></tr>';
        block.querySelectorAll('.period-row').forEach(function(pr) {
            var subj  = pr.querySelector('.period-subj-name')?.textContent || '';
            var code  = pr.querySelector('.period-subj-code')?.textContent || '';
            var time  = pr.querySelector('.period-time')?.textContent.trim() || '';
            var pill  = pr.querySelector('.att-pill')?.textContent.trim() || 'N/A';
            var pillBg= pill==='P'?'#d1fae5':pill==='A'?'#fee2e2':pill==='L'?'#fef3c7':pill==='H'?'#dbeafe':'#f3f4f6';
            var pillFg= pill==='P'?'#065f46':pill==='A'?'#991b1b':pill==='L'?'#78350f':pill==='H'?'#1e40af':'#6b7280';
            rows += '<tr><td style="padding:6px 12px;border:1px solid #f3f4f6;font-weight:600;">'+subj+' '+code+'</td>'+
                    '<td style="padding:6px 12px;border:1px solid #f3f4f6;color:#6b7280;">'+time+'</td>'+
                    '<td style="padding:6px 12px;border:1px solid #f3f4f6;text-align:center;">'+
                    '<span style="background:'+pillBg+';color:'+pillFg+';font-weight:700;padding:2px 10px;border-radius:5px;">'+pill+'</span></td></tr>';
        });
    });
    return rows;
}

function getExportHeader() {
    return '<h2 style="margin:0 0 4px;font-size:18px;">'+SCHOOL_NAME+'</h2>'+
           '<h3 style="margin:0 0 2px;font-size:15px;color:#6366f1;">Student Period Attendance Report</h3>'+
           '<p style="margin:0 0 16px;color:#6b7280;font-size:12px;">'+
           STUDENT_NAME+' &bull; Adm: '+STUDENT_ADM+' &bull; '+MONTH_LABEL+
           ' &bull; Overall: '+TOTAL_PCT+' ('+TOTAL_PRESENT+'/'+TOTAL_HELD+' periods)</p>'+
           '<table style="width:100%;border-collapse:collapse;font-family:Arial,sans-serif;font-size:12px;">'+
           '<thead><tr style="background:#6366f1;color:#fff;">'+
           '<th style="padding:8px 12px;text-align:left;border:1px solid #4f46e5;">Subject</th>'+
           '<th style="padding:8px 12px;text-align:left;border:1px solid #4f46e5;">Time</th>'+
           '<th style="padding:8px 12px;text-align:center;border:1px solid #4f46e5;width:80px;">Status</th>'+
           '</tr></thead><tbody>';
}

function exportStudentPDF() {
    var w = window.open('','_blank','width=850,height=700');
    w.document.write('<!DOCTYPE html><html><head><meta charset="utf-8"><title>Attendance Report</title>'+
        '<style>body{font-family:Arial,sans-serif;margin:24px;font-size:12px;}@media print{button{display:none;}}</style>'+
        '</head><body>'+getExportHeader()+buildExportHTML()+'</tbody></table>'+
        '<p style="margin-top:16px;font-size:11px;color:#9ca3af;">Generated on '+new Date().toLocaleDateString('en-IN')+'</p>'+
        '<br><button onclick="window.print()" style="padding:8px 20px;background:#6366f1;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;">🖨 Print / Save PDF</button>'+
        '</body></html>');
    w.document.close();
}

function exportStudentWord() {
    // Word-compatible HTML export (Office XML)
    var html = '<!DOCTYPE html><html xmlns:o="urn:schemas-microsoft-com:office:office" '+
        'xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">'+
        '<head><meta charset="utf-8"><title>Attendance Report</title>'+
        '<!--[if gte mso 9]><xml><w:WordDocument><w:View>Print</w:View></w:WordDocument></xml><![endif]-->'+
        '<style>@page{size:A4 portrait;margin:2cm;} body{font-family:Arial,sans-serif;font-size:12pt;}</style>'+
        '</head><body>'+getExportHeader()+buildExportHTML()+'</tbody></table>'+
        '<p style="margin-top:20px;color:#9ca3af;font-size:10pt;">Generated on '+new Date().toLocaleDateString('en-IN')+'</p>'+
        '</body></html>';
    var blob = new Blob(['﻿'+html], {type:'application/msword;charset=utf-8'});
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'attendance_'+STUDENT_ADM+'_'+MONTH_LABEL.replace(/\s/g,'_')+'.doc';
    a.click();
}
<?php endif; ?>
</script>
