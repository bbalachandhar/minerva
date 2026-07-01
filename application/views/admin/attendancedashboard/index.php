<?php
$base      = base_url();
$is_period = (bool) $is_period_wise;
$low_limit = (int) $low_att_limit;
$today_fmt = date('d M Y');
$today_day = date('l');

// Dynamic labels based on mode
$mode_label   = $is_period ? 'Period-wise Attendance' : 'Day-wise Attendance';
$mode_icon    = $is_period ? 'fa-clock-o'             : 'fa-calendar';
$card2_label  = $is_period ? 'Periods Marked'          : 'Classes Marked';
$card4_label  = $is_period ? 'Periods Not Marked'      : 'Classes Not Marked';
?>
<!— Dashboard styles scoped to this page only -->
<style>
/* ── Page wrapper ───────────────────────────────────────────────── */
.ad-page { padding: 16px 20px 50px; }

/* ── Top bar ────────────────────────────────────────────────────── */
.ad-topbar { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid #e5e7eb; }
.ad-pill { display:inline-flex; align-items:center; gap:5px; border-radius:20px; padding:5px 13px; font-size:12px; font-weight:700; border:1.5px solid; }
.ad-pill-period { background:#eef2ff; color:#4338ca; border-color:#c7d2fe; }
.ad-pill-day    { background:#f0fdf4; color:#166534; border-color:#bbf7d0; }
.ad-pill-thresh { background:#fffbeb; color:#92400e; border-color:#fde68a; }
.ad-pill-date   { background:#f8fafc; color:#475569; border-color:#e2e8f0; }
.ad-topbar-right { margin-left:auto; display:flex; gap:6px; flex-wrap:wrap; }
.ad-btn { display:inline-flex; align-items:center; gap:5px; padding:6px 13px; border-radius:7px; font-size:12px; font-weight:600; text-decoration:none; border:1.5px solid #e5e7eb; background:#fff; color:#374151; cursor:pointer; transition:all .15s; white-space:nowrap; }
.ad-btn:hover { border-color:#6366f1; color:#6366f1; text-decoration:none; }
.ad-btn-primary { background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff!important; border-color:transparent!important; box-shadow:0 2px 8px rgba(99,102,241,.3); }
.ad-btn-primary:hover { opacity:.9; }
.ad-btn-sm { padding:4px 10px; font-size:11px; }

/* ── Section headers ────────────────────────────────────────────── */
.ad-sec-hdr { font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.7px; color:#6b7280; margin:22px 0 12px; display:flex; align-items:center; gap:8px; }
.ad-sec-hdr i { font-size:13px; }
.ad-sec-hdr .ad-sec-actions { margin-left:auto; display:flex; gap:6px; }

/* ── Stat cards ─────────────────────────────────────────────────── */
.ad-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
@media(max-width:900px){ .ad-stats{grid-template-columns:repeat(2,1fr);} }
@media(max-width:520px){ .ad-stats{grid-template-columns:1fr;} }
.ad-stat { border-radius:14px; border:1px solid; padding:18px 20px; display:flex; align-items:flex-start; gap:14px; box-shadow:0 1px 4px rgba(0,0,0,.06); }
.ad-stat-1 { background:linear-gradient(135deg,#f5f3ff,#ede9fe); border-color:#ddd6fe; }
.ad-stat-2 { background:linear-gradient(135deg,#f0fdf4,#dcfce7); border-color:#bbf7d0; }
.ad-stat-3 { background:linear-gradient(135deg,#fffbeb,#fef3c7); border-color:#fde68a; }
.ad-stat-4 { background:linear-gradient(135deg,#fff1f2,#fee2e2); border-color:#fecaca; }
.ad-stat-ico { width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:19px; flex-shrink:0; }
.ad-stat-ico-1 { background:rgba(109,40,217,.12); color:#6d28d9; }
.ad-stat-ico-2 { background:rgba(5,150,105,.12);  color:#059669; }
.ad-stat-ico-3 { background:rgba(217,119,6,.12);  color:#b45309; }
.ad-stat-ico-4 { background:rgba(220,38,38,.12);  color:#dc2626; }
.ad-stat-val { font-size:32px; font-weight:800; line-height:1; color:#111827; }
.ad-stat-val .denom { font-size:18px; font-weight:500; color:#9ca3af; }
.ad-stat-lbl { font-size:12px; font-weight:600; color:#4b5563; margin-top:5px; }
.ad-stat-sub { font-size:11px; color:#6b7280; margin-top:3px; }
.skel { display:inline-block; background:linear-gradient(90deg,#e5e7eb 25%,#d1d5db 50%,#e5e7eb 75%); background-size:400% 100%; animation:sk 1.2s infinite; border-radius:5px; }
@keyframes sk{0%{background-position:100% 0}100%{background-position:-100% 0}}

/* ── Two column layout ──────────────────────────────────────────── */
.ad-row2 { display:grid; grid-template-columns:1fr 380px; gap:18px; margin-top:22px; }
@media(max-width:1100px){ .ad-row2{grid-template-columns:1fr;} }

/* ── Box container ──────────────────────────────────────────────── */
.ad-box { background:#fff; border-radius:12px; border:1px solid #e5e7eb; box-shadow:0 1px 6px rgba(0,0,0,.06); display:flex; flex-direction:column; }
.ad-box-hdr { padding:12px 16px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:8px; font-size:13px; font-weight:700; color:#111827; flex-shrink:0; }
.ad-box-hdr i { color:#6366f1; }
.ad-box-hdr .hdr-actions { margin-left:auto; display:flex; gap:5px; }
.ad-box-body { padding:14px 16px; flex:1; overflow:hidden; }

/* ── Teacher rows ───────────────────────────────────────────────── */
.ad-tchr-scroll { overflow-y:auto; max-height:360px; flex:1; }
.ad-tchr-row { display:flex; align-items:center; gap:11px; padding:10px 16px; border-bottom:1px solid #f9fafb; }
.ad-tchr-row:last-child { border-bottom:none; }
.ad-av { width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid #e5e7eb; flex-shrink:0; }
.ad-av-init { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:800; color:#fff; background:linear-gradient(135deg,#6366f1,#8b5cf6); flex-shrink:0; }
.ad-tchr-name { font-size:13px; font-weight:700; color:#111827; }
.ad-tchr-sub  { font-size:10px; color:#6b7280; margin-top:1px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.ad-tchr-meta { flex:1; min-width:0; }
.ad-prog-wrap { display:flex; align-items:center; gap:7px; margin-top:5px; }
.ad-prog-track { flex:1; height:5px; background:#e5e7eb; border-radius:3px; overflow:hidden; }
.ad-prog-fill  { height:100%; border-radius:3px; }
.ad-tchr-cnt { font-size:10px; color:#6b7280; white-space:nowrap; }
.ad-badge { font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px; white-space:nowrap; flex-shrink:0; }
.ad-badge-done { background:#d1fae5; color:#065f46; }
.ad-badge-part { background:#fef3c7; color:#92400e; }
.ad-badge-none { background:#fee2e2; color:#991b1b; }
.ad-pend-tip   { font-size:10px; color:#ef4444; margin-top:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

/* ── Trend chart ────────────────────────────────────────────────── */
.ad-trend-body { padding:14px 16px 10px; }
#weeklyChart { max-height:200px; }

/* ── Heatmap ────────────────────────────────────────────────────── */
.ad-hm-scroll { overflow-x:auto; }
.ad-hm-tbl { width:100%; border-collapse:separate; border-spacing:5px; table-layout:fixed; font-size:12px; }
.ad-hm-tbl th { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:#6b7280; padding:5px 6px; text-align:center; }
.ad-hm-th-cls { text-align:left!important; width:22%; }
.ad-hm-cls { font-size:12px; font-weight:600; color:#374151; padding:8px 10px; vertical-align:middle; }
.ad-hm-cell { border-radius:9px; padding:10px 8px; text-align:center; font-size:13px; font-weight:700; cursor:pointer; transition:opacity .15s; vertical-align:middle; }
.ad-hm-cell:hover { opacity:.8; }
.ad-hm-cell small { display:block; font-size:10px; font-weight:500; margin-top:2px; opacity:.85; }
.hm-full    { background:#d1fae5; color:#065f46; }
.hm-partial { background:#fef3c7; color:#78350f; }
.hm-none    { background:#fee2e2; color:#991b1b; }
.hm-empty   { background:#f3f4f6; color:#9ca3af; font-size:16px; }
.ad-hm-legend { display:flex; gap:16px; padding:8px 14px; border-top:1px solid #f3f4f6; flex-wrap:wrap; }
.ad-hm-legend span { display:flex; align-items:center; gap:5px; font-size:11px; color:#6b7280; }
.hm-dot { width:11px; height:11px; border-radius:3px; display:inline-block; }

/* ── Low attendance ─────────────────────────────────────────────── */
.ad-low-tbl { width:100%; border-collapse:collapse; font-size:12px; }
.ad-low-tbl th { font-size:10px; font-weight:700; text-transform:uppercase; color:#6b7280; padding:8px 14px; border-bottom:2px solid #f3f4f6; white-space:nowrap; }
.ad-low-tbl td { padding:9px 14px; border-bottom:1px solid #f9fafb; vertical-align:middle; }
.ad-low-tbl tr:last-child td { border-bottom:none; }
.pct-pill { display:inline-block; padding:2px 9px; border-radius:10px; font-weight:700; font-size:12px; }
.pct-crit { background:#fee2e2; color:#991b1b; }
.pct-warn { background:#fef3c7; color:#78350f; }
/* Pagination */
.ad-pager { display:flex; align-items:center; justify-content:space-between; padding:10px 14px; border-top:1px solid #f3f4f6; }
.ad-pager-info { font-size:12px; color:#6b7280; }
.ad-pager-btns { display:flex; gap:4px; }
.ad-page-btn { padding:4px 10px; border-radius:6px; border:1px solid #e5e7eb; background:#fff; font-size:12px; color:#374151; cursor:pointer; }
.ad-page-btn:hover { border-color:#6366f1; color:#6366f1; }
.ad-page-btn.active { background:#6366f1; color:#fff; border-color:#6366f1; }
.ad-page-btn:disabled { opacity:.4; cursor:default; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 4px;">
      <i class="fa fa-bar-chart" style="color:#6366f1;margin-right:8px;"></i>
      Student Attendance Dashboard
    </h1>
    <ol class="breadcrumb" style="margin:0;">
      <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
      <li>Attendance</li><li class="active">Dashboard</li>
    </ol>
  </section>

  <section class="content ad-page">

    <!-- ── Top bar ─────────────────────────────────────────────── -->
    <div class="ad-topbar">
      <span class="ad-pill <?php echo $is_period ? 'ad-pill-period' : 'ad-pill-day'; ?>">
        <i class="fa <?php echo $mode_icon; ?>"></i> <?php echo $mode_label; ?>
      </span>
      <span class="ad-pill ad-pill-thresh">
        <i class="fa fa-flag"></i> Threshold: <?php echo $low_limit; ?>%
      </span>
      <span class="ad-pill ad-pill-date">
        <i class="fa fa-calendar-o"></i> <?php echo $today_day.', '.$today_fmt; ?>
      </span>
      <div class="ad-topbar-right">
        <?php if ($is_period): ?>
        <a href="<?php echo site_url('admin/subjectattendence/index'); ?>" class="ad-btn"><i class="fa fa-pencil-square-o"></i> Mark Attendance</a>
        <a href="<?php echo site_url('attendencereports/reportbymonth'); ?>" class="ad-btn"><i class="fa fa-th"></i> Class Matrix</a>
        <a href="<?php echo site_url('attendencereports/reportbymonthstudent'); ?>" class="ad-btn"><i class="fa fa-user-circle-o"></i> Student Report</a>
        <a href="<?php echo site_url('attendencereports/teachermarkingcoverage'); ?>" class="ad-btn"><i class="fa fa-id-card-o"></i> Teacher Coverage</a>
        <?php else: ?>
        <a href="<?php echo site_url('admin/stuattendence/index'); ?>" class="ad-btn"><i class="fa fa-pencil-square-o"></i> Mark Attendance</a>
        <a href="<?php echo site_url('attendencereports/classattendencereport'); ?>" class="ad-btn"><i class="fa fa-table"></i> Class Report</a>
        <a href="<?php echo site_url('attendencereports/daywiseattendancereport'); ?>" class="ad-btn"><i class="fa fa-calendar"></i> Day-wise Report</a>
        <?php endif; ?>
        <button onclick="refreshAll()" class="ad-btn ad-btn-primary" id="refresh-btn">
          <i class="fa fa-refresh" id="refresh-icon"></i> Refresh
        </button>
      </div>
    </div>

    <!-- ── STAT CARDS ──────────────────────────────────────────── -->
    <div class="ad-sec-hdr">
      <i class="fa fa-bolt" style="color:#f59e0b;"></i> Today's Overview
    </div>
    <div class="ad-stats">
      <div class="ad-stat ad-stat-1">
        <div class="ad-stat-ico ad-stat-ico-1"><i class="fa fa-graduation-cap"></i></div>
        <div>
          <div class="ad-stat-val" id="s-sec"><span class="skel" style="width:70px;height:28px;">&nbsp;</span></div>
          <div class="ad-stat-lbl" id="s-sec-lbl"><?php echo $is_period ? 'Sections Marked' : 'Classes Marked'; ?></div>
          <div class="ad-stat-sub" id="s-sec-sub"></div>
        </div>
      </div>
      <div class="ad-stat ad-stat-2">
        <div class="ad-stat-ico ad-stat-ico-2"><i class="fa <?php echo $is_period ? 'fa-clock-o' : 'fa-users'; ?>"></i></div>
        <div>
          <div class="ad-stat-val" id="s-per"><span class="skel" style="width:70px;height:28px;">&nbsp;</span></div>
          <div class="ad-stat-lbl" id="s-per-lbl"><?php echo $card2_label; ?></div>
          <div class="ad-stat-sub" id="s-per-sub"></div>
        </div>
      </div>
      <div class="ad-stat ad-stat-3">
        <div class="ad-stat-ico ad-stat-ico-3"><i class="fa fa-check-circle-o"></i></div>
        <div>
          <div class="ad-stat-val" id="s-pres"><span class="skel" style="width:70px;height:28px;">&nbsp;</span></div>
          <div class="ad-stat-lbl">Present Today</div>
          <div class="ad-stat-sub" id="s-pres-sub"></div>
        </div>
      </div>
      <div class="ad-stat ad-stat-4">
        <div class="ad-stat-ico ad-stat-ico-4"><i class="fa fa-exclamation-triangle"></i></div>
        <div>
          <div class="ad-stat-val" id="s-pend"><span class="skel" style="width:70px;height:28px;">&nbsp;</span></div>
          <div class="ad-stat-lbl" id="s-pend-lbl"><?php echo $card4_label; ?></div>
          <div class="ad-stat-sub" id="s-pend-sub"></div>
        </div>
      </div>
    </div>

    <!-- ── TEACHER STATUS + TREND ──────────────────────────────── -->
    <div class="ad-row2">

      <!-- Teacher status -->
      <div class="ad-box" style="min-height:200px;">
        <div class="ad-box-hdr">
          <i class="fa fa-user-circle-o"></i>
          <?php echo $is_period ? 'Teacher Marking Status — Today' : 'Class / Section Summary — Today'; ?>
          <div class="hdr-actions">
            <button onclick="exportTeacherCSV()" class="ad-btn ad-btn-sm" title="Download Excel/CSV"><i class="fa fa-file-excel-o" style="color:#217346;"></i> CSV</button>
            <button onclick="exportTeacherPDF()" class="ad-btn ad-btn-sm" title="Print / Save as PDF"><i class="fa fa-file-pdf-o" style="color:#e53e3e;"></i> PDF</button>
          </div>
        </div>
        <div class="ad-tchr-scroll" id="teacher-wrap">
          <?php for($i=0;$i<4;$i++): ?>
          <div class="ad-tchr-row">
            <div class="ad-av-init"><span class="skel" style="width:36px;height:36px;border-radius:50%;"></span></div>
            <div class="ad-tchr-meta">
              <div class="skel" style="width:55%;height:12px;margin-bottom:6px;"></div>
              <div class="skel" style="width:90%;height:5px;"></div>
            </div>
          </div>
          <?php endfor; ?>
        </div>
      </div>

      <!-- 7-day trend -->
      <div class="ad-box">
        <div class="ad-box-hdr">
          <i class="fa fa-line-chart" style="color:#059669;"></i> 7-Day Attendance Trend
          <div class="hdr-actions">
            <button onclick="exportTrendCSV()" class="ad-btn ad-btn-sm" title="Download CSV"><i class="fa fa-file-excel-o" style="color:#217346;"></i> CSV</button>
            <button onclick="exportTrendPDF()" class="ad-btn ad-btn-sm" title="Print"><i class="fa fa-print" style="color:#6366f1;"></i> PDF</button>
          </div>
        </div>
        <div class="ad-trend-body">
          <canvas id="weeklyChart"></canvas>
          <div id="trend-empty" style="display:none;text-align:center;padding:50px 0;color:#9ca3af;">
            <i class="fa fa-bar-chart" style="font-size:28px;display:block;margin-bottom:8px;opacity:.35;"></i>
            No attendance data in past 7 days
          </div>
        </div>
      </div>
    </div>

    <!-- ── HEATMAP ─────────────────────────────────────────────── -->
    <div class="ad-sec-hdr" style="margin-top:22px;">
      <i class="fa fa-th" style="color:#8b5cf6;"></i>
      Today's Attendance Coverage — Class / Section Heatmap
      <div class="ad-sec-actions">
        <button onclick="exportHeatmapCSV()" class="ad-btn ad-btn-sm"><i class="fa fa-file-excel-o" style="color:#217346;"></i> CSV</button>
        <button onclick="exportHeatmapPDF()" class="ad-btn ad-btn-sm"><i class="fa fa-file-pdf-o" style="color:#e53e3e;"></i> PDF</button>
      </div>
    </div>
    <div class="ad-box">
      <div class="ad-hm-scroll" id="heatmap-wrap">
        <div style="padding:40px;text-align:center;">
          <span class="skel" style="width:80%;height:80px;display:inline-block;"></span>
        </div>
      </div>
      <div class="ad-hm-legend">
        <span><span class="hm-dot" style="background:#d1fae5;border:1px solid #a7f3d0;"></span> Fully Marked</span>
        <span><span class="hm-dot" style="background:#fef3c7;border:1px solid #fde68a;"></span> Partial</span>
        <span><span class="hm-dot" style="background:#fee2e2;border:1px solid #fca5a5;"></span> Not Marked</span>
        <span><span class="hm-dot" style="background:#f3f4f6;border:1px solid #e5e7eb;"></span> No Schedule Today</span>
      </div>
    </div>

    <!-- ── LOW ATTENDANCE ──────────────────────────────────────── -->
    <div class="ad-sec-hdr" style="margin-top:22px;">
      <i class="fa fa-exclamation-circle" style="color:#dc2626;"></i>
      Low Attendance Alert — This Month (below <?php echo $low_limit; ?>%)
      <div class="ad-sec-actions">
        <button onclick="exportLowCSV()" class="ad-btn ad-btn-sm"><i class="fa fa-file-excel-o" style="color:#217346;"></i> CSV</button>
        <button onclick="exportLowPDF()" class="ad-btn ad-btn-sm"><i class="fa fa-file-pdf-o" style="color:#e53e3e;"></i> PDF</button>
      </div>
    </div>
    <div class="ad-box">
      <div id="low-att-wrap" style="overflow:hidden;">
        <table class="ad-low-tbl">
          <thead>
            <tr>
              <th style="width:40px;">#</th>
              <th>Student</th>
              <th>Adm. No</th>
              <th>Class</th>
              <th style="width:80px;">Section</th>
              <th style="width:110px;">Attendance %</th>
              <th style="width:80px;">Present</th>
              <th style="width:70px;">Action</th>
            </tr>
          </thead>
          <tbody id="low-att-tbody">
            <tr><td colspan="8" style="text-align:center;padding:24px;">
              <span class="skel" style="display:inline-block;width:60%;height:14px;"></span>
            </td></tr>
          </tbody>
        </table>
      </div>
      <div class="ad-pager" id="low-att-pager" style="display:none;">
        <span class="ad-pager-info" id="pager-info"></span>
        <div class="ad-pager-btns" id="pager-btns"></div>
      </div>
    </div>

  </section>
</div>

<!-- ── Hidden print template ─────────────────────────────────────── -->
<div id="print-template" style="display:none;"></div>

<script>
var ATT_BASE   = '<?php echo site_url("admin/attendancedashboard"); ?>';
var IS_PERIOD  = <?php echo $is_period ? 'true' : 'false'; ?>;
var LOW_LIMIT  = <?php echo $low_limit; ?>;
var SCHOOL     = '<?php echo addslashes(htmlspecialchars($sch_setting->name ?? 'School')); ?>';
var TODAY_LBL  = '<?php echo $today_day.', '.$today_fmt; ?>';

// Data stores for exports
var teacherData = [];
var trendData   = [];
var heatmapData = {};
var lowAttData  = [];

// Pagination
var LOW_PER_PAGE = 10;
var lowPage      = 1;
var trendChart   = null;

/* ── Refresh all ─────────────────────────────────────────────────── */
function refreshAll() {
    var icon = document.getElementById('refresh-icon');
    icon.classList.add('fa-spin');
    Promise.all([loadCoverage(), loadTeacher(), loadTrend(), loadHeatmap(), loadLowAtt()])
        .then(function(){ icon.classList.remove('fa-spin'); })
        .catch(function(){ icon.classList.remove('fa-spin'); });
}

/* ── Stat cards ──────────────────────────────────────────────────── */
function loadCoverage() {
    return fetch(ATT_BASE + '/ajax_today_coverage', {credentials:'same-origin'})
        .then(r => r.json()).then(function(d) {
            var covPct = parseFloat(d.coverage_pct)||0;
            var cc = covPct>=80?'#059669':covPct>=40?'#b45309':'#dc2626';

            // Card 1: sections
            document.getElementById('s-sec').innerHTML =
                '<span style="color:'+cc+';">'+(IS_PERIOD?d.marked_sections:d.marked_sections)+'</span>'+
                '<span class="denom"> / '+(IS_PERIOD?d.total_sections:d.total_sections)+'</span>';
            document.getElementById('s-sec-sub').textContent = covPct+'% coverage today';

            // Card 2: periods or classes
            if (IS_PERIOD) {
                document.getElementById('s-per').innerHTML =
                    '<span style="color:#059669;">'+d.marked_periods+'</span>'+
                    '<span class="denom"> / '+d.total_periods+'</span>';
                document.getElementById('s-per-sub').textContent = d.pending_periods+' periods still pending';
            } else {
                document.getElementById('s-per').innerHTML = '<span style="color:#059669;">'+d.total_marked+'</span>';
                document.getElementById('s-per-sub').textContent = 'student records saved today';
            }

            // Card 3: present %
            var pc = parseFloat(d.pct_present)||0;
            var pcol = pc>=80?'#059669':pc>=60?'#b45309':'#dc2626';
            document.getElementById('s-pres').innerHTML = '<span style="color:'+pcol+';">'+pc+'%</span>';
            document.getElementById('s-pres-sub').textContent = d.present_count+' of '+d.total_marked+' marked present';

            // Card 4: pending
            var pend = IS_PERIOD ? d.pending_periods : (d.total_sections - d.marked_sections);
            var pendcol = pend===0 ? '#059669' : '#dc2626';
            document.getElementById('s-pend').innerHTML = '<span style="color:'+pendcol+';">'+pend+'</span>';
        })
        .catch(function() {
            ['s-sec','s-per','s-pres','s-pend'].forEach(function(id){ document.getElementById(id).textContent='—'; });
        });
}

/* ── Teacher status ──────────────────────────────────────────────── */
function loadTeacher() {
    return fetch(ATT_BASE + '/ajax_teacher_status', {credentials:'same-origin'})
        .then(r => r.json()).then(function(d) {
            teacherData = d.rows || [];
            var wrap = document.getElementById('teacher-wrap');
            if (!IS_PERIOD) {
                wrap.innerHTML = '<div style="padding:20px 16px;color:#6b7280;font-size:13px;"><i class="fa fa-info-circle" style="margin-right:6px;"></i>Teacher period tracking is available in period-wise mode. See the heatmap below.</div>';
                return;
            }
            if (!teacherData.length) {
                wrap.innerHTML = '<div style="padding:40px;text-align:center;color:#9ca3af;"><i class="fa fa-calendar-times-o" style="font-size:28px;display:block;margin-bottom:8px;opacity:.4;"></i>No periods scheduled for today</div>';
                return;
            }
            var html = '';
            teacherData.forEach(function(r) {
                var tot=parseInt(r.total_periods)||0, mrk=parseInt(r.marked_periods)||0;
                var pct=tot>0?Math.round(mrk*100/tot):0;
                var bc=mrk===tot?'#059669':mrk>0?'#d97706':'#dc2626';
                var badge=mrk===tot?'ad-badge-done':mrk>0?'ad-badge-part':'ad-badge-none';
                var btxt=mrk===tot?'All Done ✓':(tot-mrk)+' Pending';
                var init=(r.teacher_name||'?').trim().split(' ').map(function(w){return(w[0]||'').toUpperCase();}).slice(0,2).join('');
                var av=r.image?'<img class="ad-av" src="<?php echo $base; ?>uploads/staff/'+esc(r.image)+'" onerror="this.style.display=\'none\';this.nextSibling.style.display=\'flex\';">'+'<div class="ad-av-init" style="display:none;">'+init+'</div>':'<div class="ad-av-init">'+init+'</div>';
                var tip='';
                if(r.pending_detail){
                    var items=r.pending_detail.split('|').filter(Boolean);
                    if(items.length) tip='<div class="ad-pend-tip"><i class="fa fa-clock-o"></i> '+items.slice(0,2).map(esc).join(' &bull; ')+(items.length>2?' +'+(items.length-2)+' more':'')+'</div>';
                }
                html+='<div class="ad-tchr-row">'+av+'<div class="ad-tchr-meta"><div class="ad-tchr-name">'+esc(r.teacher_name)+'</div>'+
                    '<div class="ad-prog-wrap"><div class="ad-prog-track"><div class="ad-prog-fill" style="width:'+pct+'%;background:'+bc+';"></div></div>'+
                    '<span class="ad-tchr-cnt">'+mrk+'/'+tot+'</span><span class="ad-badge '+badge+'">'+btxt+'</span></div>'+tip+'</div></div>';
            });
            wrap.innerHTML = html;
        })
        .catch(function(){ document.getElementById('teacher-wrap').innerHTML='<div style="padding:16px;color:#ef4444;">Error loading data.</div>'; });
}

/* ── 7-day trend ─────────────────────────────────────────────────── */
function loadTrend() {
    return fetch(ATT_BASE + '/ajax_weekly_trend', {credentials:'same-origin'})
        .then(r => r.json()).then(function(d) {
            trendData = d.rows || [];
            var emEl = document.getElementById('trend-empty');
            if (!trendData.length) { emEl.style.display='block'; return; }
            emEl.style.display='none';
            var labels = trendData.map(function(r){
                var dt=new Date(r.date+'T00:00:00');
                return dt.toLocaleDateString('en-IN',{weekday:'short',day:'numeric',month:'short'});
            });
            var pcts = trendData.map(function(r){return parseFloat(r.pct)||0;});
            var ctx  = document.getElementById('weeklyChart').getContext('2d');
            if(trendChart) trendChart.destroy();
            trendChart=new Chart(ctx,{
                type:'line',
                data:{labels:labels,datasets:[{
                    label:'Present %',data:pcts,
                    borderColor:'#6366f1',backgroundColor:'rgba(99,102,241,.08)',
                    borderWidth:2.5,fill:true,tension:0.35,
                    pointBackgroundColor:pcts.map(function(p){return p>=80?'#059669':p>=60?'#d97706':'#dc2626';}),
                    pointRadius:5,pointHoverRadius:7
                }]},
                options:{
                    responsive:true,maintainAspectRatio:true,
                    scales:{
                        y:{min:0,max:100,ticks:{callback:v=>v+'%',font:{size:11}},grid:{color:'#f3f4f6'}},
                        x:{ticks:{font:{size:10}},grid:{display:false}}
                    },
                    plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>ctx.parsed.y+'% present'}}}
                }
            });
        }).catch(function(){});
}

/* ── Heatmap ─────────────────────────────────────────────────────── */
function loadHeatmap() {
    return fetch(ATT_BASE + '/ajax_heatmap', {credentials:'same-origin'})
        .then(r => r.json()).then(function(d) {
            heatmapData = d;
            var wrap = document.getElementById('heatmap-wrap');
            if(!d.rows||!d.rows.length){
                wrap.innerHTML='<div style="padding:40px;text-align:center;color:#9ca3af;"><i class="fa fa-calendar-times-o" style="font-size:28px;display:block;margin-bottom:8px;opacity:.4;"></i>No timetable entries scheduled for today</div>';
                return;
            }
            var isPd=(d.mode==='period');
            var classes={},sections={},lookup={};
            d.rows.forEach(function(r){
                if(!classes[r.class_id])   classes[r.class_id]=r.class_name;
                if(!sections[r.section_id])sections[r.section_id]=r.section_name;
                lookup[r.class_id+'_'+r.section_id]=r;
            });
            var cids=Object.keys(classes);
            var sids=Object.keys(sections);
            var link=isPd?'<?php echo site_url("attendencereports/reportbymonth"); ?>':'<?php echo site_url("attendencereports/classattendencereport"); ?>';

            var tbl='<table class="ad-hm-tbl"><thead><tr><th class="ad-hm-th-cls">Class</th>';
            sids.forEach(function(sid){tbl+='<th>'+esc(sections[sid])+'</th>';});
            tbl+='</tr></thead><tbody>';
            cids.forEach(function(cid){
                tbl+='<tr><td class="ad-hm-cls">'+esc(classes[cid])+'</td>';
                sids.forEach(function(sid){
                    var cell=lookup[cid+'_'+sid];
                    if(!cell){tbl+='<td class="ad-hm-cell hm-empty">—</td>';return;}
                    var tot=parseInt(isPd?cell.total_periods:cell.total_students)||0;
                    var mrk=parseInt(isPd?cell.marked_periods:cell.marked_students)||0;
                    var pct=tot>0?Math.round(mrk*100/tot):0;
                    var cls=mrk===0?'hm-none':mrk<tot?'hm-partial':'hm-full';
                    var icon=mrk===0?'✕':mrk<tot?'~':'✓';
                    var tip=isPd?(mrk+'/'+tot+' periods'):(mrk+'/'+tot+' students');
                    tbl+='<td class="ad-hm-cell '+cls+'" title="'+tip+'" onclick="window.location=\''+link+'\'">'+icon+' '+pct+'%<small>'+tip+'</small></td>';
                });
                tbl+='</tr>';
            });
            tbl+='</tbody></table>';
            wrap.innerHTML='<div style="padding:14px 14px 4px;">'+tbl+'</div>';
        }).catch(function(){document.getElementById('heatmap-wrap').innerHTML='<div style="padding:16px;color:#ef4444;">Error loading heatmap.</div>';});
}

/* ── Low attendance with pagination ─────────────────────────────── */
function loadLowAtt() {
    return fetch(ATT_BASE + '/ajax_low_attendance', {credentials:'same-origin'})
        .then(r => r.json()).then(function(d) {
            lowAttData = d.rows || [];
            lowPage = 1;
            renderLowPage();
        }).catch(function(){document.getElementById('low-att-tbody').innerHTML='<tr><td colspan="8" style="color:#ef4444;padding:16px;">Error loading data.</td></tr>';});
}

function renderLowPage() {
    var tbody  = document.getElementById('low-att-tbody');
    var pager  = document.getElementById('low-att-pager');
    var info   = document.getElementById('pager-info');
    var btns   = document.getElementById('pager-btns');
    var link   = IS_PERIOD ? '<?php echo site_url("attendencereports/reportbymonthstudent"); ?>' : '<?php echo site_url("attendencereports/classattendencereport"); ?>';

    if (!lowAttData.length) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:30px;color:#059669;"><i class="fa fa-check-circle" style="font-size:20px;display:block;margin-bottom:6px;"></i>All students are above <?php echo $low_limit; ?>% this month</td></tr>';
        pager.style.display='none'; return;
    }
    var total  = lowAttData.length;
    var pages  = Math.ceil(total / LOW_PER_PAGE);
    var start  = (lowPage-1)*LOW_PER_PAGE;
    var slice  = lowAttData.slice(start, start+LOW_PER_PAGE);

    var html='';
    slice.forEach(function(r,i){
        var pct=parseFloat(r.pct)||0;
        var cls=pct<50?'pct-crit':'pct-warn';
        var nm=esc(((r.firstname||'')+' '+(r.lastname||'')).trim());
        html+='<tr>'+
            '<td style="color:#9ca3af;">'+(start+i+1)+'</td>'+
            '<td><strong>'+nm+'</strong></td>'+
            '<td style="color:#6b7280;font-family:monospace;">'+esc(r.admission_no)+'</td>'+
            '<td>'+esc(r.class_name)+'</td>'+
            '<td style="text-align:center;">'+esc(r.section_name)+'</td>'+
            '<td style="text-align:center;"><span class="pct-pill '+cls+'">'+pct+'%</span></td>'+
            '<td style="text-align:center;color:#6b7280;">'+r.present_count+'/'+r.total_records+'</td>'+
            '<td><a href="'+link+'" class="ad-btn ad-btn-sm"><i class="fa fa-eye"></i></a></td>'+
            '</tr>';
    });
    tbody.innerHTML=html;

    // Pagination controls
    info.textContent = 'Showing '+(start+1)+'–'+Math.min(start+LOW_PER_PAGE,total)+' of '+total+' students';
    var bhtml='<button class="ad-page-btn" onclick="changePage('+(lowPage-1)+')" '+(lowPage===1?'disabled':'')+'>‹ Prev</button>';
    var maxBtn=7, half=Math.floor(maxBtn/2);
    var p1=Math.max(1,lowPage-half), p2=Math.min(pages,p1+maxBtn-1);
    if(p2-p1<maxBtn-1) p1=Math.max(1,p2-maxBtn+1);
    if(p1>1) bhtml+='<button class="ad-page-btn" onclick="changePage(1)">1</button>'+(p1>2?'<span style="padding:4px 6px;color:#9ca3af;">…</span>':'');
    for(var p=p1;p<=p2;p++) bhtml+='<button class="ad-page-btn'+(p===lowPage?' active':'')+'" onclick="changePage('+p+')">'+p+'</button>';
    if(p2<pages) bhtml+=(p2<pages-1?'<span style="padding:4px 6px;color:#9ca3af;">…</span>':'')+'<button class="ad-page-btn" onclick="changePage('+pages+')">'+pages+'</button>';
    bhtml+='<button class="ad-page-btn" onclick="changePage('+(lowPage+1)+')" '+(lowPage===pages?'disabled':'')+'>Next ›</button>';
    btns.innerHTML=bhtml;
    pager.style.display='flex';
}

function changePage(p) {
    var pages=Math.ceil(lowAttData.length/LOW_PER_PAGE);
    if(p<1||p>pages) return;
    lowPage=p;
    renderLowPage();
    document.getElementById('low-att-wrap').scrollIntoView({behavior:'smooth',block:'nearest'});
}

/* ── EXPORTS ─────────────────────────────────────────────────────── */
// Utility: trigger CSV download
function dlCSV(filename, rows) {
    var csv=rows.map(function(row){return row.map(function(c){return '"'+String(c==null?'':c).replace(/"/g,'""')+'"';}).join(',');}).join('\r\n');
    var blob=new Blob(['﻿'+csv],{type:'text/csv;charset=utf-8;'});
    var a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download=filename; a.click();
}

// Utility: open print window
function openPrint(title, tableHtml) {
    var w=window.open('','_blank','width=900,height=700');
    w.document.write('<!DOCTYPE html><html><head><meta charset="utf-8"><title>'+title+'</title>'+
    '<style>body{font-family:Arial,sans-serif;font-size:12px;margin:20px;}h2{font-size:16px;margin-bottom:4px;}p{color:#6b7280;margin:0 0 16px;}'+
    'table{width:100%;border-collapse:collapse;}th,td{border:1px solid #e5e7eb;padding:7px 10px;text-align:left;}'+
    'th{background:#f3f4f6;font-weight:700;font-size:11px;text-transform:uppercase;}'+
    'tr:nth-child(even){background:#f9fafb;}@media print{button{display:none;}}</style></head><body>'+
    '<h2>'+SCHOOL+' — '+title+'</h2><p>'+TODAY_LBL+'</p>'+tableHtml+
    '<br><button onclick="window.print()" style="padding:8px 20px;background:#6366f1;color:#fff;border:none;border-radius:6px;cursor:pointer;">🖨 Print / Save PDF</button>'+
    '</body></html>');
    w.document.close();
}

// Teacher export
function exportTeacherCSV() {
    if(!teacherData.length){alert('No data loaded yet. Click Refresh.');return;}
    var rows=[['Teacher Name','Total Periods Today','Marked Periods','Coverage %','Pending Periods']];
    teacherData.forEach(function(r){
        var tot=parseInt(r.total_periods)||0, mrk=parseInt(r.marked_periods)||0;
        rows.push([r.teacher_name,tot,mrk,tot>0?Math.round(mrk*100/tot):0,tot-mrk]);
    });
    dlCSV('teacher_marking_status_'+TODAY_LBL.replace(/,? /g,'_')+'.csv',rows);
}
function exportTeacherPDF() {
    if(!teacherData.length){alert('No data loaded yet. Click Refresh.');return;}
    var tbl='<table><thead><tr><th>#</th><th>Teacher</th><th>Total</th><th>Marked</th><th>%</th><th>Pending Periods</th></tr></thead><tbody>';
    teacherData.forEach(function(r,i){
        var tot=parseInt(r.total_periods)||0,mrk=parseInt(r.marked_periods)||0,pct=tot>0?Math.round(mrk*100/tot):0;
        var pend=(r.pending_detail||'').split('|').filter(Boolean).join('; ');
        tbl+='<tr><td>'+(i+1)+'</td><td>'+esc(r.teacher_name)+'</td><td style="text-align:center;">'+tot+'</td><td style="text-align:center;">'+mrk+'</td><td style="text-align:center;">'+pct+'%</td><td style="font-size:10px;color:#6b7280;">'+esc(pend)+'</td></tr>';
    });
    tbl+='</tbody></table>';
    openPrint('Teacher Marking Status — Today',tbl);
}

// Trend export
function exportTrendCSV() {
    if(!trendData.length){alert('No trend data available.');return;}
    var rows=[['Date','Total Records','Present','Present %']];
    trendData.forEach(function(r){rows.push([r.date,r.total_marked,r.present_count,r.pct+'%']);});
    dlCSV('attendance_trend_7day.csv',rows);
}
function exportTrendPDF() {
    if(!trendData.length){alert('No trend data available.');return;}
    var tbl='<table><thead><tr><th>Date</th><th>Total Records</th><th>Present</th><th>Present %</th></tr></thead><tbody>';
    trendData.forEach(function(r){tbl+='<tr><td>'+r.date+'</td><td style="text-align:center;">'+r.total_marked+'</td><td style="text-align:center;">'+r.present_count+'</td><td style="text-align:center;font-weight:700;">'+r.pct+'%</td></tr>';});
    tbl+='</tbody></table>';
    openPrint('7-Day Attendance Trend',tbl);
}

// Heatmap export
function exportHeatmapCSV() {
    if(!heatmapData.rows||!heatmapData.rows.length){alert('No heatmap data loaded.');return;}
    var isPd=(heatmapData.mode==='period');
    var rows=[['Class','Section',isPd?'Periods Scheduled':'Students Enrolled',isPd?'Periods Marked':'Students Marked','Coverage %']];
    heatmapData.rows.forEach(function(r){
        var tot=parseInt(isPd?r.total_periods:r.total_students)||0;
        var mrk=parseInt(isPd?r.marked_periods:r.marked_students)||0;
        rows.push([r.class_name,r.section_name,tot,mrk,tot>0?Math.round(mrk*100/tot)+'%':'0%']);
    });
    dlCSV('heatmap_today_'+TODAY_LBL.replace(/,? /g,'_')+'.csv',rows);
}
function exportHeatmapPDF() {
    if(!heatmapData.rows||!heatmapData.rows.length){alert('No heatmap data loaded.');return;}
    var isPd=(heatmapData.mode==='period');
    var tbl='<table><thead><tr><th>Class</th><th>Section</th><th>'+(isPd?'Periods Scheduled':'Students')+'</th><th>'+(isPd?'Periods Marked':'Marked')+'</th><th>Coverage %</th></tr></thead><tbody>';
    heatmapData.rows.forEach(function(r){
        var tot=parseInt(isPd?r.total_periods:r.total_students)||0;
        var mrk=parseInt(isPd?r.marked_periods:r.marked_students)||0;
        var pct=tot>0?Math.round(mrk*100/tot):0;
        var bg=mrk===0?'#fee2e2':mrk<tot?'#fef3c7':'#d1fae5';
        tbl+='<tr><td>'+esc(r.class_name)+'</td><td style="text-align:center;">'+esc(r.section_name)+'</td><td style="text-align:center;">'+tot+'</td><td style="text-align:center;">'+mrk+'</td><td style="text-align:center;font-weight:700;background:'+bg+';">'+pct+'%</td></tr>';
    });
    tbl+='</tbody></table>';
    openPrint('Class / Section Heatmap — Today',tbl);
}

// Low attendance export
function exportLowCSV() {
    if(!lowAttData.length){alert('No low attendance data loaded.');return;}
    var rows=[['#','Student Name','Admission No','Class','Section','Attendance %','Present','Total']];
    lowAttData.forEach(function(r,i){
        rows.push([i+1,(r.firstname+' '+r.lastname).trim(),r.admission_no,r.class_name,r.section_name,r.pct+'%',r.present_count,r.total_records]);
    });
    dlCSV('low_attendance_'+TODAY_LBL.replace(/,? /g,'_')+'.csv',rows);
}
function exportLowPDF() {
    if(!lowAttData.length){alert('No low attendance data loaded.');return;}
    var tbl='<table><thead><tr><th>#</th><th>Student</th><th>Adm No</th><th>Class</th><th>Section</th><th>%</th><th>Present/Total</th></tr></thead><tbody>';
    lowAttData.forEach(function(r,i){
        var pct=parseFloat(r.pct)||0;
        var bg=pct<50?'#fee2e2':'#fef3c7';
        tbl+='<tr><td>'+(i+1)+'</td><td><strong>'+esc((r.firstname+' '+r.lastname).trim())+'</strong></td><td style="font-family:monospace;">'+esc(r.admission_no)+'</td><td>'+esc(r.class_name)+'</td><td style="text-align:center;">'+esc(r.section_name)+'</td><td style="text-align:center;font-weight:700;background:'+bg+';">'+pct+'%</td><td style="text-align:center;">'+r.present_count+'/'+r.total_records+'</td></tr>';
    });
    tbl+='</tbody></table><p style="margin-top:12px;font-size:11px;color:#6b7280;">Threshold: <?php echo $low_limit; ?>% &nbsp;|&nbsp; Total: '+lowAttData.length+' students</p>';
    openPrint('Low Attendance Alert — This Month',tbl);
}

function esc(s){if(!s)return'';return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

document.addEventListener('DOMContentLoaded', refreshAll);
</script>
