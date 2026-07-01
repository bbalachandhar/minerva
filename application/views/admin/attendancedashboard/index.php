<?php
$base       = base_url();
$is_period  = (bool) $is_period_wise;
$low_limit  = (int) $low_att_limit;
$mode_label = $is_period ? 'Period-wise Attendance' : 'Day-wise Attendance';
$mode_icon  = $is_period ? 'fa-clock-o' : 'fa-calendar';
$mode_color = $is_period ? '#5b73e8' : '#27ae60';
?>
<style>
/* ── Base layout fix ───────────────────────────────────────────── */
.attd-page { padding: 15px 20px 40px; }

/* ── Top bar ───────────────────────────────────────────────────── */
.attd-topbar {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    margin-bottom: 20px; padding-bottom: 14px;
    border-bottom: 1px solid #eef0f3;
}
.attd-mode-pill {
    display: inline-flex; align-items: center; gap: 6px;
    background: #f0f2ff; color: #5b73e8;
    border: 1.5px solid #c5cdf8; border-radius: 20px;
    padding: 5px 14px; font-size: 13px; font-weight: 700;
}
.attd-mode-pill.day { background: #edfaf4; color: #1a8a55; border-color: #b2eacc; }
.attd-thresh-pill {
    display: inline-flex; align-items: center; gap: 5px;
    background: #fff8e1; color: #7a5c00;
    border: 1.5px solid #f0c040; border-radius: 20px;
    padding: 5px 14px; font-size: 13px; font-weight: 600;
}
.attd-topbar-right { margin-left: auto; display: flex; gap: 8px; flex-wrap: wrap; }
.attd-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600;
    text-decoration: none; cursor: pointer; border: 1.5px solid #dde1e7;
    background: #fff; color: #495057; transition: all .15s;
}
.attd-btn:hover { border-color: #5b73e8; color: #5b73e8; text-decoration: none; }
.attd-btn-primary {
    background: linear-gradient(135deg,#5b73e8,#7c5ce7);
    color: #fff !important; border-color: transparent !important;
    box-shadow: 0 2px 8px rgba(91,115,232,.3);
}
.attd-btn-primary:hover { opacity: .9; }

/* ── Section headings ──────────────────────────────────────────── */
.attd-section-hdr {
    font-size: 11px; font-weight: 800; text-transform: uppercase;
    letter-spacing: .8px; color: #6b7280; margin: 22px 0 12px;
    display: flex; align-items: center; gap: 8px;
}
.attd-section-hdr i { font-size: 14px; }

/* ── Stat cards ────────────────────────────────────────────────── */
.attd-stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; margin-bottom: 6px; }
@media(max-width:900px){ .attd-stats { grid-template-columns: repeat(2,1fr); } }
@media(max-width:480px){ .attd-stats { grid-template-columns: 1fr; } }
.stat-card {
    background: #fff; border-radius: 12px; border: 1px solid #eef0f3;
    box-shadow: 0 2px 10px rgba(0,0,0,.06); padding: 18px 20px;
    display: flex; align-items: flex-start; gap: 14px;
}
.stat-icon {
    width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 20px;
}
.stat-val { font-size: 30px; font-weight: 800; line-height: 1; color: #111827; }
.stat-val span.denom { font-size: 18px; font-weight: 500; color: #9ca3af; }
.stat-label { font-size: 12px; font-weight: 600; color: #6b7280; margin-top: 4px; }
.stat-sub { font-size: 11px; color: #9ca3af; margin-top: 3px; }

/* ── Two-col layout ────────────────────────────────────────────── */
.attd-grid2 { display: grid; grid-template-columns: 1fr 420px; gap: 18px; }
@media(max-width:1100px){ .attd-grid2 { grid-template-columns: 1fr; } }

/* ── Box / card containers ─────────────────────────────────────── */
.attd-box {
    background: #fff; border-radius: 12px; border: 1px solid #eef0f3;
    box-shadow: 0 2px 10px rgba(0,0,0,.06); overflow: hidden;
}
.attd-box-hdr {
    padding: 13px 18px; border-bottom: 1px solid #eef0f3;
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 700; color: #374151;
}
.attd-box-hdr i { color: #5b73e8; font-size: 15px; }
.attd-box-body { padding: 16px 18px; }

/* ── Teacher rows ──────────────────────────────────────────────── */
.tchr-row {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 0; border-bottom: 1px solid #f3f4f6;
}
.tchr-row:last-child { border-bottom: none; }
.tchr-av {
    width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
    object-fit: cover; border: 2px solid #e5e7eb;
}
.tchr-av-init {
    width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg,#5b73e8,#7c5ce7);
    color: #fff; font-weight: 700; font-size: 14px;
    display: flex; align-items: center; justify-content: center;
}
.tchr-name { font-size: 13px; font-weight: 700; color: #1f2937; }
.tchr-sub  { font-size: 11px; color: #6b7280; margin-top: 2px; }
.tchr-meta { flex: 1; min-width: 0; }
.tchr-progress { display: flex; align-items: center; gap: 8px; margin-top: 5px; }
.prog-track { flex: 1; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; }
.prog-fill  { height: 100%; border-radius: 3px; }
.tchr-count { font-size: 11px; color: #6b7280; white-space: nowrap; }
.tchr-badge {
    font-size: 10px; font-weight: 700; padding: 2px 8px;
    border-radius: 12px; white-space: nowrap; flex-shrink: 0;
}
.badge-done  { background: #d1fae5; color: #065f46; }
.badge-part  { background: #fef3c7; color: #92400e; }
.badge-none  { background: #fee2e2; color: #991b1b; }
.tchr-pending { font-size: 10px; color: #ef4444; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ── Trend chart ───────────────────────────────────────────────── */
.trend-box { padding: 16px; }
#weeklyChart { max-height: 200px; }

/* ── Heatmap ───────────────────────────────────────────────────── */
.heatmap-scroll { overflow-x: auto; padding: 0 18px 18px; }
.hm-table { border-collapse: separate; border-spacing: 4px; font-size: 12px; white-space: nowrap; }
.hm-table th {
    font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase;
    padding: 4px 8px; text-align: center;
}
.hm-th-class { text-align: left !important; font-size: 12px; color: #374151; }
.hm-cell {
    border-radius: 8px; padding: 8px 12px; text-align: center;
    font-size: 12px; font-weight: 700; cursor: pointer; min-width: 90px;
    transition: opacity .15s;
}
.hm-cell:hover { opacity: .85; }
.hm-cell small { display: block; font-size: 10px; font-weight: 500; margin-top: 1px; }
.hm-full    { background: #d1fae5; color: #065f46; }
.hm-partial { background: #fef3c7; color: #78350f; }
.hm-none    { background: #fee2e2; color: #991b1b; }
.hm-empty   { background: #f3f4f6; color: #9ca3af; }
.hm-class   { font-size: 12px; font-weight: 600; color: #374151; padding: 8px 4px; }

/* ── Low attendance table ──────────────────────────────────────── */
.low-tbl { width: 100%; border-collapse: collapse; font-size: 12px; }
.low-tbl th { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #6b7280; padding: 6px 14px; border-bottom: 2px solid #f3f4f6; }
.low-tbl td { padding: 9px 14px; border-bottom: 1px solid #f9fafb; }
.low-tbl tr:last-child td { border-bottom: none; }
.pct-pill { display: inline-block; padding: 2px 10px; border-radius: 12px; font-weight: 700; font-size: 12px; }
.pct-crit { background: #fee2e2; color: #991b1b; }
.pct-warn { background: #fef3c7; color: #78350f; }

/* ── Skeleton ──────────────────────────────────────────────────── */
.skel {
    background: linear-gradient(90deg,#f0f0f0 25%,#e8e8e8 50%,#f0f0f0 75%);
    background-size: 400% 100%; animation: skel 1.2s infinite;
    border-radius: 6px;
}
@keyframes skel { 0%{background-position:100% 0} 100%{background-position:-100% 0} }

/* ── Legend ────────────────────────────────────────────────────── */
.hm-legend { display: flex; gap: 14px; padding: 10px 18px; border-top: 1px solid #f3f4f6; }
.hm-legend span { display: flex; align-items: center; gap: 5px; font-size: 11px; color: #6b7280; }
.hm-legend .dot { width: 12px; height: 12px; border-radius: 3px; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1 style="font-size:20px;font-weight:700;color:#1f2937;">
      <i class="fa fa-bar-chart" style="color:#5b73e8;margin-right:8px;"></i>
      Student Attendance Dashboard
      <small style="font-size:13px;font-weight:400;color:#6b7280;margin-left:8px;"><?php echo htmlspecialchars($today_label); ?></small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
      <li>Attendance</li>
      <li class="active">Student Dashboard</li>
    </ol>
  </section>

  <section class="content attd-page">

    <!-- Top bar: mode + threshold + action buttons -->
    <div class="attd-topbar">
      <span class="attd-mode-pill<?php echo $is_period ? '' : ' day'; ?>">
        <i class="fa <?php echo $mode_icon; ?>"></i> <?php echo $mode_label; ?>
      </span>
      <span class="attd-thresh-pill">
        <i class="fa fa-flag"></i> Threshold: <?php echo $low_limit; ?>%
      </span>
      <div class="attd-topbar-right">
        <?php if ($is_period): ?>
          <a href="<?php echo site_url('admin/subjectattendence/index'); ?>" class="attd-btn"><i class="fa fa-pencil-square-o"></i> Mark Attendance</a>
          <a href="<?php echo site_url('attendencereports/reportbymonth'); ?>" class="attd-btn"><i class="fa fa-th"></i> Class Matrix</a>
          <a href="<?php echo site_url('attendencereports/reportbymonthstudent'); ?>" class="attd-btn"><i class="fa fa-user-circle-o"></i> Student Report</a>
          <a href="<?php echo site_url('attendencereports/teachermarkingcoverage'); ?>" class="attd-btn"><i class="fa fa-id-card-o"></i> Teacher Coverage</a>
        <?php else: ?>
          <a href="<?php echo site_url('admin/stuattendence/index'); ?>" class="attd-btn"><i class="fa fa-pencil-square-o"></i> Mark Attendance</a>
          <a href="<?php echo site_url('attendencereports/classattendencereport'); ?>" class="attd-btn"><i class="fa fa-table"></i> Attendance Reports</a>
          <a href="<?php echo site_url('attendencereports/daywiseattendancereport'); ?>" class="attd-btn"><i class="fa fa-calendar"></i> Day-wise Report</a>
        <?php endif; ?>
        <button onclick="refreshAll()" class="attd-btn attd-btn-primary" id="refresh-btn">
          <i class="fa fa-refresh" id="refresh-icon"></i> Refresh
        </button>
      </div>
    </div>

    <!-- ── TODAY'S OVERVIEW ───────────────────────────────────── -->
    <div class="attd-section-hdr">
      <i class="fa fa-bolt" style="color:#f59e0b;"></i> Today's Overview
    </div>
    <div class="attd-stats" id="stat-cards">
      <!-- Card 1: Sections/Classes -->
      <div class="stat-card">
        <div class="stat-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fa fa-graduation-cap"></i></div>
        <div>
          <div class="stat-val" id="s-sections"><span class="skel" style="width:70px;height:30px;display:block;">&nbsp;</span></div>
          <div class="stat-label" id="s-sections-lbl"><?php echo $is_period ? 'Sections Marked' : 'Classes Marked'; ?></div>
          <div class="stat-sub" id="s-sections-sub"></div>
        </div>
      </div>
      <!-- Card 2: Periods (period-wise) or Student records (day-wise) -->
      <div class="stat-card">
        <div class="stat-icon" style="background:#d1fae5;color:#059669;"><i class="fa <?php echo $is_period ? 'fa-clock-o' : 'fa-users'; ?>"></i></div>
        <div>
          <div class="stat-val" id="s-periods"><span class="skel" style="width:70px;height:30px;display:block;">&nbsp;</span></div>
          <div class="stat-label" id="s-periods-lbl"><?php echo $is_period ? 'Periods Marked' : 'Students Marked'; ?></div>
          <div class="stat-sub" id="s-periods-sub"></div>
        </div>
      </div>
      <!-- Card 3: Present % -->
      <div class="stat-card">
        <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fa fa-check-circle-o"></i></div>
        <div>
          <div class="stat-val" id="s-present"><span class="skel" style="width:70px;height:30px;display:block;">&nbsp;</span></div>
          <div class="stat-label">Present Today</div>
          <div class="stat-sub" id="s-present-sub"></div>
        </div>
      </div>
      <!-- Card 4: Pending -->
      <div class="stat-card">
        <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="fa fa-exclamation-triangle"></i></div>
        <div>
          <div class="stat-val" id="s-pending"><span class="skel" style="width:70px;height:30px;display:block;">&nbsp;</span></div>
          <div class="stat-label" id="s-pending-lbl"><?php echo $is_period ? 'Periods Not Marked' : 'Classes Not Marked'; ?></div>
          <div class="stat-sub" id="s-pending-sub"></div>
        </div>
      </div>
    </div>

    <!-- ── TEACHER STATUS + TREND ─────────────────────────────── -->
    <div class="attd-grid2" style="margin-top:22px;">

      <!-- Left: Teacher status (period) / Section summary (day) -->
      <div>
        <div class="attd-section-hdr">
          <i class="fa fa-user-circle-o" style="color:#5b73e8;"></i>
          <?php echo $is_period ? "Teacher Marking Status — Today" : "Class / Section Summary — Today"; ?>
        </div>
        <div class="attd-box">
          <div id="teacher-status-wrap" style="max-height:380px;overflow-y:auto;">
            <div class="attd-box-body">
              <?php for($i=0;$i<5;$i++): ?>
              <div class="tchr-row">
                <div class="tchr-av-init"><span class="skel" style="width:100%;height:100%;border-radius:50%;display:block;"></span></div>
                <div class="tchr-meta">
                  <div class="skel" style="width:55%;height:12px;margin-bottom:6px;"></div>
                  <div class="skel" style="width:90%;height:6px;"></div>
                </div>
              </div>
              <?php endfor; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: 7-day trend chart -->
      <div>
        <div class="attd-section-hdr">
          <i class="fa fa-line-chart" style="color:#059669;"></i> 7-Day Attendance Trend
        </div>
        <div class="attd-box">
          <div class="trend-box">
            <canvas id="weeklyChart"></canvas>
            <div id="trend-empty" style="display:none;text-align:center;padding:40px 0;color:#9ca3af;">
              <i class="fa fa-bar-chart" style="font-size:32px;margin-bottom:8px;display:block;opacity:.4;"></i>
              No attendance data in past 7 days
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── HEATMAP ────────────────────────────────────────────── -->
    <div class="attd-section-hdr" style="margin-top:22px;">
      <i class="fa fa-th" style="color:#7c3aed;"></i>
      Today's Attendance Coverage — Class / Section Heatmap
    </div>
    <div class="attd-box">
      <div class="heatmap-scroll" id="heatmap-wrap">
        <div style="padding:40px;text-align:center;">
          <span class="skel" style="display:inline-block;width:80%;height:80px;"></span>
        </div>
      </div>
      <div class="hm-legend">
        <span><span class="dot" style="background:#d1fae5;border:1px solid #a7f3d0;"></span> Fully Marked</span>
        <span><span class="dot" style="background:#fef3c7;border:1px solid #fde68a;"></span> Partial</span>
        <span><span class="dot" style="background:#fee2e2;border:1px solid #fca5a5;"></span> Not Marked</span>
        <span><span class="dot" style="background:#f3f4f6;border:1px solid #e5e7eb;"></span> No Schedule</span>
      </div>
    </div>

    <!-- ── LOW ATTENDANCE ─────────────────────────────────────── -->
    <div class="attd-section-hdr" style="margin-top:22px;">
      <i class="fa fa-exclamation-circle" style="color:#dc2626;"></i>
      Low Attendance Alert — This Month (below <?php echo $low_limit; ?>%)
    </div>
    <div class="attd-box">
      <div id="low-att-wrap" style="padding:0 0 4px;">
        <table class="low-tbl">
          <thead>
            <tr><th>#</th><th>Student</th><th>Adm. No</th><th>Class</th><th>Section</th><th>Attendance %</th><th>Action</th></tr>
          </thead>
          <tbody id="low-att-tbody">
            <tr><td colspan="7" style="text-align:center;padding:24px;">
              <span class="skel" style="display:inline-block;width:60%;height:14px;"></span>
            </td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script>
var ATT_BASE   = '<?php echo site_url("admin/attendancedashboard"); ?>';
var IS_PERIOD  = <?php echo $is_period ? 'true' : 'false'; ?>;
var LOW_LIMIT  = <?php echo $low_limit; ?>;
var trendChart = null;

function refreshAll() {
    var icon = document.getElementById('refresh-icon');
    icon.classList.add('fa-spin');
    Promise.all([loadCoverage(), loadTeacherStatus(), loadTrend(), loadHeatmap(), loadLowAtt()])
        .then(function(){ icon.classList.remove('fa-spin'); })
        .catch(function(){ icon.classList.remove('fa-spin'); });
}

// ── Stat cards ──────────────────────────────────────────────────
function loadCoverage() {
    return fetch(ATT_BASE + '/ajax_today_coverage', {credentials:'same-origin'})
        .then(r => r.json())
        .then(function(d) {
            var covPct  = parseFloat(d.coverage_pct) || 0;
            var covColor = covPct >= 90 ? '#059669' : covPct >= 50 ? '#d97706' : '#dc2626';

            // Card 1: sections
            var secMark = IS_PERIOD ? d.marked_sections : d.marked_sections;
            var secTot  = IS_PERIOD ? d.total_sections  : d.total_sections;
            document.getElementById('s-sections').innerHTML =
                '<span style="color:' + covColor + ';">' + secMark + '</span>' +
                '<span class="denom"> / ' + secTot + '</span>';
            document.getElementById('s-sections-sub').textContent = covPct + '% coverage today';

            // Card 2: periods or students
            if (IS_PERIOD) {
                document.getElementById('s-periods').innerHTML =
                    '<span>' + d.marked_periods + '</span><span class="denom"> / ' + d.total_periods + '</span>';
                document.getElementById('s-periods-sub').textContent = d.pending_periods + ' periods still pending';
            } else {
                document.getElementById('s-periods').innerHTML = '<span>' + d.total_marked + '</span>';
                document.getElementById('s-periods-sub').textContent = 'student records saved today';
            }

            // Card 3: present %
            var presColor = d.pct_present >= 80 ? '#059669' : d.pct_present >= 60 ? '#d97706' : '#dc2626';
            document.getElementById('s-present').innerHTML =
                '<span style="color:' + presColor + ';">' + d.pct_present + '%</span>';
            document.getElementById('s-present-sub').textContent =
                d.present_count + ' of ' + d.total_marked + ' marked present';

            // Card 4: pending
            var pend = IS_PERIOD ? d.pending_periods : (d.total_sections - d.marked_sections);
            var pendColor = pend === 0 ? '#059669' : '#dc2626';
            document.getElementById('s-pending').innerHTML =
                '<span style="color:' + pendColor + ';">' + pend + '</span>';
        })
        .catch(function(e) {
            document.getElementById('s-sections').textContent = '—';
            document.getElementById('s-periods').textContent  = '—';
            document.getElementById('s-present').textContent  = '—';
            document.getElementById('s-pending').textContent  = '—';
        });
}

// ── Teacher status ───────────────────────────────────────────────
function loadTeacherStatus() {
    return fetch(ATT_BASE + '/ajax_teacher_status', {credentials:'same-origin'})
        .then(r => r.json())
        .then(function(d) {
            var wrap = document.getElementById('teacher-status-wrap');
            if (!IS_PERIOD) {
                wrap.innerHTML = '<div class="attd-box-body" style="color:#6b7280;font-size:13px;">' +
                    '<i class="fa fa-info-circle" style="margin-right:6px;"></i>' +
                    'Teacher-wise period status is available in period-wise attendance mode. See the class heatmap below.' +
                    '</div>';
                return;
            }
            if (!d.rows || d.rows.length === 0) {
                wrap.innerHTML = '<div class="attd-box-body" style="text-align:center;padding:40px;color:#9ca3af;">' +
                    '<i class="fa fa-calendar-times-o" style="font-size:28px;display:block;margin-bottom:8px;opacity:.4;"></i>' +
                    'No periods scheduled for today' + '</div>';
                return;
            }
            var html = '<div class="attd-box-body">';
            d.rows.forEach(function(r) {
                var tot  = parseInt(r.total_periods)  || 0;
                var mrk  = parseInt(r.marked_periods) || 0;
                var pend = tot - mrk;
                var pct  = tot > 0 ? Math.round(mrk * 100 / tot) : 0;
                var barCol  = mrk === tot ? '#059669' : mrk > 0 ? '#d97706' : '#dc2626';
                var badgeCls= mrk === tot ? 'badge-done' : mrk > 0 ? 'badge-part' : 'badge-none';
                var badgeTxt= mrk === tot ? 'All Done ✓' : pend + ' Pending';
                var init = (r.teacher_name || '?').trim().split(' ').map(function(w){return w[0]||'';}).slice(0,2).join('').toUpperCase();
                var avatar = r.image
                    ? '<img class="tchr-av" src="<?php echo $base; ?>uploads/staff/' + esc(r.image) + '" onerror="this.style.display=\'none\';this.nextSibling.style.display=\'flex\';">' +
                      '<div class="tchr-av-init" style="display:none;">' + init + '</div>'
                    : '<div class="tchr-av-init">' + init + '</div>';
                var pendTip = '';
                if (r.pending_detail) {
                    var items = r.pending_detail.split('|').filter(Boolean);
                    if (items.length) {
                        pendTip = '<div class="tchr-pending"><i class="fa fa-clock-o"></i> ' +
                            items.slice(0,2).map(esc).join(' &bull; ') +
                            (items.length > 2 ? ' +' + (items.length - 2) + ' more' : '') + '</div>';
                    }
                }
                html += '<div class="tchr-row">' + avatar +
                    '<div class="tchr-meta">' +
                      '<div class="tchr-name">' + esc(r.teacher_name) + '</div>' +
                      '<div class="tchr-progress">' +
                        '<div class="prog-track"><div class="prog-fill" style="width:' + pct + '%;background:' + barCol + ';"></div></div>' +
                        '<span class="tchr-count">' + mrk + '/' + tot + '</span>' +
                        '<span class="tchr-badge ' + badgeCls + '">' + badgeTxt + '</span>' +
                      '</div>' + pendTip +
                    '</div></div>';
            });
            html += '</div>';
            wrap.innerHTML = html;
        })
        .catch(function() { document.getElementById('teacher-status-wrap').innerHTML = '<div class="attd-box-body" style="color:#ef4444;">Error loading teacher status.</div>'; });
}

// ── Trend chart ──────────────────────────────────────────────────
function loadTrend() {
    return fetch(ATT_BASE + '/ajax_weekly_trend', {credentials:'same-origin'})
        .then(r => r.json())
        .then(function(d) {
            var emEl = document.getElementById('trend-empty');
            if (!d.rows || d.rows.length === 0) { emEl.style.display = 'block'; return; }
            emEl.style.display = 'none';
            var labels = d.rows.map(function(r) {
                var dt = new Date(r.date + 'T00:00:00');
                return dt.toLocaleDateString('en-IN', {weekday:'short', day:'numeric', month:'short'});
            });
            var pcts = d.rows.map(function(r) { return parseFloat(r.pct) || 0; });
            var ctx  = document.getElementById('weeklyChart').getContext('2d');
            if (trendChart) trendChart.destroy();
            trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Present %', data: pcts,
                        borderColor: '#5b73e8', backgroundColor: 'rgba(91,115,232,.08)',
                        borderWidth: 2.5, fill: true, tension: 0.35,
                        pointBackgroundColor: pcts.map(function(p) {
                            return p >= 80 ? '#059669' : p >= 60 ? '#d97706' : '#dc2626';
                        }),
                        pointRadius: 5, pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: true,
                    scales: {
                        y: { min: 0, max: 100, ticks: { callback: v => v + '%', font:{size:11} }, grid:{color:'#f3f4f6'} },
                        x: { ticks: { font:{size:10} }, grid:{display:false} }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ctx.parsed.y + '% present' } }
                    }
                }
            });
        })
        .catch(function() {});
}

// ── Heatmap ──────────────────────────────────────────────────────
function loadHeatmap() {
    return fetch(ATT_BASE + '/ajax_heatmap', {credentials:'same-origin'})
        .then(r => r.json())
        .then(function(d) {
            var wrap = document.getElementById('heatmap-wrap');
            if (!d.rows || d.rows.length === 0) {
                wrap.innerHTML = '<div style="text-align:center;padding:40px;color:#9ca3af;">' +
                    '<i class="fa fa-calendar-times-o" style="font-size:28px;display:block;margin-bottom:8px;opacity:.4;"></i>' +
                    'No timetable entries scheduled for today</div>';
                return;
            }
            var isPd   = (d.mode === 'period');
            var classes  = {}, sections = {}, lookup = {};
            d.rows.forEach(function(r) {
                if (!classes[r.class_id])   classes[r.class_id]   = r.class_name;
                if (!sections[r.section_id]) sections[r.section_id] = r.section_name;
                lookup[r.class_id + '_' + r.section_id] = r;
            });
            var cids = Object.keys(classes);
            var sids = Object.keys(sections);

            // Report links
            var link = isPd
                ? '<?php echo site_url("attendencereports/reportbymonth"); ?>'
                : '<?php echo site_url("attendencereports/classattendencereport"); ?>';

            var tbl = '<table class="hm-table"><thead><tr><th class="hm-th-class">Class</th>';
            sids.forEach(function(sid){ tbl += '<th>' + esc(sections[sid]) + '</th>'; });
            tbl += '</tr></thead><tbody>';
            cids.forEach(function(cid) {
                tbl += '<tr><td class="hm-class">' + esc(classes[cid]) + '</td>';
                sids.forEach(function(sid) {
                    var cell = lookup[cid + '_' + sid];
                    if (!cell) { tbl += '<td class="hm-cell hm-empty">—</td>'; return; }
                    var tot  = parseInt(isPd ? cell.total_periods  : cell.total_students)  || 0;
                    var mrk  = parseInt(isPd ? cell.marked_periods : cell.marked_students) || 0;
                    var pct  = tot > 0 ? Math.round(mrk * 100 / tot) : 0;
                    var cls  = mrk === 0 ? 'hm-none' : (mrk < tot ? 'hm-partial' : 'hm-full');
                    var icon = mrk === 0 ? '✕' : (mrk < tot ? '~' : '✓');
                    var tip  = isPd ? (mrk + '/' + tot + ' periods') : (mrk + '/' + tot + ' students');
                    tbl += '<td class="hm-cell ' + cls + '" title="' + tip + '" onclick="window.location=\'' + link + '\'" >' +
                           icon + ' ' + pct + '%<small>' + tip + '</small></td>';
                });
                tbl += '</tr>';
            });
            tbl += '</tbody></table>';
            wrap.innerHTML = '<div style="padding:16px 0 0;">' + tbl + '</div>';
        })
        .catch(function() {
            document.getElementById('heatmap-wrap').innerHTML = '<div style="padding:20px;color:#ef4444;">Error loading heatmap.</div>';
        });
}

// ── Low attendance ───────────────────────────────────────────────
function loadLowAtt() {
    return fetch(ATT_BASE + '/ajax_low_attendance', {credentials:'same-origin'})
        .then(r => r.json())
        .then(function(d) {
            var tbody = document.getElementById('low-att-tbody');
            if (!d.rows || d.rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:#059669;">' +
                    '<i class="fa fa-check-circle" style="font-size:20px;display:block;margin-bottom:6px;"></i>' +
                    'All students are above ' + LOW_LIMIT + '% this month</td></tr>';
                return;
            }
            var link = IS_PERIOD
                ? '<?php echo site_url("attendencereports/reportbymonthstudent"); ?>'
                : '<?php echo site_url("attendencereports/classattendencereport"); ?>';
            var html = '';
            d.rows.forEach(function(r, i) {
                var pct = parseFloat(r.pct) || 0;
                var cls = pct < 50 ? 'pct-crit' : 'pct-warn';
                var nm  = esc((r.firstname || '') + ' ' + (r.lastname || '')).trim();
                html += '<tr>' +
                    '<td style="color:#9ca3af;">' + (i+1) + '</td>' +
                    '<td><strong>' + nm + '</strong></td>' +
                    '<td style="color:#6b7280;">' + esc(r.admission_no) + '</td>' +
                    '<td>' + esc(r.class_name) + '</td>' +
                    '<td>' + esc(r.section_name) + '</td>' +
                    '<td><span class="pct-pill ' + cls + '">' + pct + '%</span>' +
                    '  <span style="font-size:11px;color:#9ca3af;">' + r.present_count + '/' + r.total_records + '</span></td>' +
                    '<td><a href="' + link + '" class="attd-btn" style="padding:4px 10px;font-size:11px;"><i class="fa fa-eye"></i> View</a></td>' +
                    '</tr>';
            });
            tbody.innerHTML = html;
        })
        .catch(function() { document.getElementById('low-att-tbody').innerHTML = '<tr><td colspan="7" style="color:#ef4444;padding:16px;">Error loading data.</td></tr>'; });
}

function esc(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.addEventListener('DOMContentLoaded', refreshAll);
</script>
