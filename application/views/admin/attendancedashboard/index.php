<?php
$base = base_url();
$is_period = (bool) $is_period_wise;
$mode_label = $is_period ? 'Period-wise' : 'Day-wise';
$low_limit  = (int) $low_att_limit;
?>
<style>
/* ── Layout ───────────────────────────────────────────────── */
.attd-wrap { padding: 0 15px 30px; }
.attd-section-title {
    font-size: 13px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .6px; color: #7f8c8d; margin: 24px 0 10px;
    display: flex; align-items: center; gap: 8px;
}
.attd-section-title i { font-size: 15px; }

/* ── Stat cards ───────────────────────────────────────────── */
.attd-stat-card {
    background: #fff; border-radius: 12px;
    border: 1px solid #eef0f3;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
    padding: 18px 20px; display: flex; align-items: center; gap: 16px;
    margin-bottom: 16px;
}
.attd-stat-icon {
    width: 52px; height: 52px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; flex-shrink: 0;
}
.attd-stat-val { font-size: 28px; font-weight: 700; line-height: 1; color: #2c3e50; }
.attd-stat-lbl { font-size: 12px; color: #7f8c8d; margin-top: 3px; font-weight: 500; }
.attd-stat-sub { font-size: 12px; color: #95a5a6; margin-top: 4px; }

/* ── Donut wrapper ────────────────────────────────────────── */
.attd-donut-wrap {
    background: #fff; border-radius: 12px;
    border: 1px solid #eef0f3; box-shadow: 0 2px 8px rgba(0,0,0,.05);
    padding: 18px; text-align: center; margin-bottom: 16px;
}
.attd-donut-title { font-size: 13px; color: #7f8c8d; font-weight: 600; margin-bottom: 10px; }
#donutChart { max-width: 200px; margin: 0 auto; }

/* ── Teacher status ───────────────────────────────────────── */
.teacher-row {
    background: #fff; border-radius: 10px; border: 1px solid #eef0f3;
    padding: 12px 16px; margin-bottom: 8px;
    display: flex; align-items: center; gap: 14px;
}
.teacher-avatar {
    width: 40px; height: 40px; border-radius: 50%; object-fit: cover;
    border: 2px solid #eee; flex-shrink: 0;
}
.teacher-avatar-init {
    width: 40px; height: 40px; border-radius: 50%;
    background: linear-gradient(135deg,#5b73e8,#7c5ce7);
    color: #fff; font-weight: 700; font-size: 15px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.teacher-name { font-size: 13px; font-weight: 600; color: #2c3e50; }
.teacher-sub  { font-size: 11px; color: #7f8c8d; margin-top: 2px; }
.teacher-meta { flex: 1; min-width: 0; }
.teacher-bar-wrap { display: flex; align-items: center; gap: 8px; margin-top: 6px; }
.teacher-bar-bg {
    flex: 1; height: 7px; border-radius: 4px; background: #eef0f3; overflow: hidden;
}
.teacher-bar-fill { height: 100%; border-radius: 4px; transition: width .4s; }
.teacher-badge {
    font-size: 11px; font-weight: 600; padding: 2px 8px;
    border-radius: 20px; white-space: nowrap;
}
.badge-done   { background: #d4edda; color: #155724; }
.badge-pend   { background: #fff3cd; color: #856404; }
.badge-none   { background: #f8d7da; color: #721c24; }
.pending-tip  { font-size: 11px; color: #e74c3c; margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── Heatmap ──────────────────────────────────────────────── */
.heatmap-table { width: 100%; border-collapse: separate; border-spacing: 4px; }
.heatmap-table th {
    font-size: 11px; font-weight: 600; color: #7f8c8d;
    padding: 4px 6px; text-align: center; white-space: nowrap;
}
.heatmap-cell {
    border-radius: 8px; padding: 8px 10px; text-align: center;
    font-size: 12px; font-weight: 600; cursor: default;
    min-width: 70px; white-space: nowrap;
}
.hm-done   { background: #d4f5e4; color: #1a6b3c; }
.hm-partial{ background: #fff8e1; color: #7a5c00; }
.hm-none   { background: #fdecea; color: #c0392b; }
.hm-empty  { background: #f4f5f7; color: #bbb; }
.hm-class-label { font-size: 12px; font-weight: 700; color: #34495e; padding: 6px 8px; white-space: nowrap; }

/* ── Weekly trend chart ───────────────────────────────────── */
.attd-chart-box {
    background: #fff; border-radius: 12px; border: 1px solid #eef0f3;
    box-shadow: 0 2px 8px rgba(0,0,0,.05); padding: 18px; margin-bottom: 16px;
}
.attd-chart-box canvas { max-height: 220px; }

/* ── Low attendance table ─────────────────────────────────── */
.low-att-table { width: 100%; }
.low-att-table th { font-size: 11px; color: #7f8c8d; text-transform: uppercase; font-weight: 600; padding: 6px 10px; border-bottom: 2px solid #eef0f3; }
.low-att-table td { padding: 8px 10px; font-size: 13px; border-bottom: 1px solid #f4f5f7; vertical-align: middle; }
.low-att-table tr:last-child td { border-bottom: none; }
.pct-badge {
    display: inline-block; padding: 2px 9px; border-radius: 20px;
    font-weight: 700; font-size: 12px;
}
.pct-critical { background: #fdecea; color: #c0392b; }
.pct-warn     { background: #fff3cd; color: #856404; }

/* ── Loading skeleton ─────────────────────────────────────── */
.skeleton {
    background: linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);
    background-size: 400% 100%; animation: sk 1.2s infinite;
    border-radius: 6px; height: 18px;
}
@keyframes sk { 0%{background-position:100% 0} 100%{background-position:-100% 0} }
.skeleton-val { height: 32px; width: 80px; }
.skeleton-bar { height: 7px; width: 100%; margin-top: 6px; }

/* ── Mode badge ───────────────────────────────────────────── */
.mode-badge {
    display: inline-flex; align-items: center; gap: 5px;
    background: <?php echo $is_period ? '#e8f4fd' : '#e8fdf4'; ?>;
    color: <?php echo $is_period ? '#1a6b9a' : '#1a6b3c'; ?>;
    border: 1px solid <?php echo $is_period ? '#bee3f8' : '#b8f0d8'; ?>;
    border-radius: 20px; padding: 3px 10px; font-size: 12px; font-weight: 600;
}
</style>

<div class="content-wrapper">
<section class="content-header">
    <h1>
        <i class="fa fa-bar-chart" style="color:#3c8dbc;margin-right:8px;"></i>
        Student Attendance Dashboard
        <small>Live overview — <?php echo htmlspecialchars($today_label); ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Attendance</li>
        <li class="active">Student Dashboard</li>
    </ol>
</section>

<section class="content attd-wrap">

    <!-- Mode indicator + quick actions row -->
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;flex-wrap:wrap;">
        <span class="mode-badge">
            <i class="fa <?php echo $is_period ? 'fa-clock-o' : 'fa-calendar'; ?>"></i>
            <?php echo $mode_label; ?> Attendance
        </span>
        <span style="font-size:12px;color:#95a5a6;">Threshold: <strong><?php echo $low_limit; ?>%</strong></span>
        <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;">
            <?php if ($is_period): ?>
            <a href="<?php echo site_url('admin/subjectattendence/index'); ?>" class="btn btn-sm btn-default">
                <i class="fa fa-pencil-square-o"></i> Mark Attendance
            </a>
            <a href="<?php echo site_url('attendencereports/reportbymonth'); ?>" class="btn btn-sm btn-default">
                <i class="fa fa-table"></i> Period Reports
            </a>
            <?php else: ?>
            <a href="<?php echo site_url('admin/stuattendence/index'); ?>" class="btn btn-sm btn-default">
                <i class="fa fa-pencil-square-o"></i> Mark Attendance
            </a>
            <a href="<?php echo site_url('attendencereports/classattendencereport'); ?>" class="btn btn-sm btn-default">
                <i class="fa fa-table"></i> Attendance Reports
            </a>
            <?php endif; ?>
            <button onclick="refreshAll()" class="btn btn-sm btn-primary">
                <i class="fa fa-refresh" id="refresh-icon"></i> Refresh
            </button>
        </div>
    </div>

    <!-- ── TODAY'S OVERVIEW ─────────────────────────────────────── -->
    <div class="attd-section-title">
        <i class="fa fa-bolt" style="color:#f39c12;"></i> Today's Overview
    </div>

    <div class="row" id="stat-cards">
        <!-- Card 1: Classes Marked -->
        <div class="col-xs-6 col-sm-3">
            <div class="attd-stat-card">
                <div class="attd-stat-icon" style="background:#edf2ff;color:#5b73e8;">
                    <i class="fa fa-graduation-cap"></i>
                </div>
                <div>
                    <div class="attd-stat-val" id="stat-sections">
                        <span class="skeleton skeleton-val">&nbsp;</span>
                    </div>
                    <div class="attd-stat-lbl">Classes Marked</div>
                    <div class="attd-stat-sub" id="stat-sections-sub">&nbsp;</div>
                </div>
            </div>
        </div>
        <!-- Card 2: Periods Marked (period-wise) / Students Marked (day-wise) -->
        <div class="col-xs-6 col-sm-3" id="card-periods-wrap">
            <div class="attd-stat-card">
                <div class="attd-stat-icon" style="background:#e8fdf4;color:#27ae60;">
                    <i class="fa fa-clock-o"></i>
                </div>
                <div>
                    <div class="attd-stat-val" id="stat-periods">
                        <span class="skeleton skeleton-val">&nbsp;</span>
                    </div>
                    <div class="attd-stat-lbl" id="stat-periods-lbl"><?php echo $is_period ? 'Periods Marked' : 'Students Marked'; ?></div>
                    <div class="attd-stat-sub" id="stat-periods-sub">&nbsp;</div>
                </div>
            </div>
        </div>
        <!-- Card 3: Present % today -->
        <div class="col-xs-6 col-sm-3">
            <div class="attd-stat-card">
                <div class="attd-stat-icon" style="background:#fef9e7;color:#f39c12;">
                    <i class="fa fa-check-circle-o"></i>
                </div>
                <div>
                    <div class="attd-stat-val" id="stat-present">
                        <span class="skeleton skeleton-val">&nbsp;</span>
                    </div>
                    <div class="attd-stat-lbl">Present Today</div>
                    <div class="attd-stat-sub" id="stat-present-sub">&nbsp;</div>
                </div>
            </div>
        </div>
        <!-- Card 4: Pending / Not Marked -->
        <div class="col-xs-6 col-sm-3">
            <div class="attd-stat-card">
                <div class="attd-stat-icon" style="background:#fdecea;color:#e74c3c;">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>
                <div>
                    <div class="attd-stat-val" id="stat-pending">
                        <span class="skeleton skeleton-val">&nbsp;</span>
                    </div>
                    <div class="attd-stat-lbl" id="stat-pending-lbl">Not Marked Yet</div>
                    <div class="attd-stat-sub" id="stat-pending-sub">&nbsp;</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── MAIN BODY: Teacher Status + Trend Chart ─────────────── -->
    <div class="row">

        <!-- Left: Teacher Marking Status (period-wise) OR Class Summary (day-wise) -->
        <div class="col-md-7">
            <div class="attd-section-title">
                <i class="fa fa-user-circle-o" style="color:#5b73e8;"></i>
                <?php echo $is_period ? 'Teacher Marking Status — Today' : "Class / Section Summary — Today"; ?>
            </div>
            <div id="teacher-status-wrap">
                <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="teacher-row">
                    <div class="teacher-avatar-init"><span class="skeleton" style="width:100%;height:100%;border-radius:50%;"></span></div>
                    <div class="teacher-meta">
                        <div class="skeleton" style="width:60%;height:14px;margin-bottom:6px;"></div>
                        <div class="skeleton skeleton-bar"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Right: Weekly Trend Chart -->
        <div class="col-md-5">
            <div class="attd-section-title">
                <i class="fa fa-line-chart" style="color:#27ae60;"></i> 7-Day Attendance Trend
            </div>
            <div class="attd-chart-box">
                <canvas id="weeklyChart"></canvas>
                <div id="trend-empty" style="display:none;text-align:center;color:#bbb;padding:40px 0;font-size:13px;">
                    <i class="fa fa-bar-chart" style="font-size:32px;margin-bottom:8px;display:block;"></i>
                    No attendance data in past 7 days
                </div>
            </div>
        </div>
    </div>

    <!-- ── CLASS / SECTION HEATMAP ──────────────────────────────── -->
    <div class="attd-section-title">
        <i class="fa fa-th" style="color:#9b59b6;"></i> Today's Attendance Coverage — Class / Section Heatmap
        <span style="margin-left:auto;display:flex;gap:8px;font-size:11px;font-weight:400;text-transform:none;letter-spacing:0;">
            <span style="display:inline-flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#d4f5e4;display:inline-block;"></span> Fully marked</span>
            <span style="display:inline-flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#fff8e1;display:inline-block;"></span> Partial</span>
            <span style="display:inline-flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#fdecea;display:inline-block;"></span> Not marked</span>
        </span>
    </div>
    <div class="attd-chart-box" style="overflow-x:auto;">
        <div id="heatmap-wrap">
            <table class="heatmap-table"><tr><td colspan="10"><span class="skeleton" style="display:block;height:80px;"></span></td></tr></table>
        </div>
    </div>

    <!-- ── LOW ATTENDANCE STUDENTS ──────────────────────────────── -->
    <div class="attd-section-title">
        <i class="fa fa-exclamation-circle" style="color:#e74c3c;"></i>
        Low Attendance Alert — This Month (below <?php echo $low_limit; ?>%)
    </div>
    <div class="attd-chart-box">
        <div id="low-att-wrap">
            <table class="low-att-table">
                <thead><tr><th>#</th><th>Student</th><th>Adm. No</th><th>Class</th><th>Section</th><th>Attendance %</th><th>Action</th></tr></thead>
                <tbody id="low-att-tbody">
                    <tr><td colspan="7" style="text-align:center;padding:24px;"><span class="skeleton" style="display:inline-block;width:60%;height:16px;"></span></td></tr>
                </tbody>
            </table>
        </div>
    </div>

</section>
</div>

<script>
var ATT_BASE    = '<?php echo site_url("admin/attendancedashboard"); ?>';
var IS_PERIOD   = <?php echo $is_period ? 'true' : 'false'; ?>;
var LOW_LIMIT   = <?php echo (int) $low_att_limit; ?>;
var weeklyChart = null;

function refreshAll() {
    var icon = document.getElementById('refresh-icon');
    icon.classList.add('fa-spin');
    Promise.all([
        loadCoverage(),
        loadTeacherStatus(),
        loadHeatmap(),
        loadWeeklyTrend(),
        loadLowAttendance()
    ]).then(function() {
        icon.classList.remove('fa-spin');
    }).catch(function() {
        icon.classList.remove('fa-spin');
    });
}

// ── Coverage stat cards ──────────────────────────────────────────
function loadCoverage() {
    return fetch(ATT_BASE + '/ajax_today_coverage').then(r => r.json()).then(function(d) {
        var secEl   = document.getElementById('stat-sections');
        var secSub  = document.getElementById('stat-sections-sub');
        var perEl   = document.getElementById('stat-periods');
        var perSub  = document.getElementById('stat-periods-sub');
        var presEl  = document.getElementById('stat-present');
        var presSub = document.getElementById('stat-present-sub');
        var pendEl  = document.getElementById('stat-pending');
        var pendSub = document.getElementById('stat-pending-sub');

        var covPct = d.coverage_pct || 0;
        var covColor = covPct >= 90 ? '#27ae60' : covPct >= 60 ? '#f39c12' : '#e74c3c';

        secEl.innerHTML = '<span style="color:' + covColor + ';">' + d.marked_sections + '</span><span style="font-size:16px;color:#bbb;"> / ' + d.total_sections + '</span>';
        secSub.textContent = covPct + '% coverage';

        if (IS_PERIOD) {
            perEl.innerHTML = '<span>' + d.marked_periods + '</span><span style="font-size:16px;color:#bbb;"> / ' + d.total_periods + '</span>';
            perSub.textContent = (d.total_periods - d.marked_periods) + ' periods pending';
        } else {
            perEl.innerHTML = '<span>' + d.total_marked + '</span>';
            perSub.textContent = 'student records saved';
        }

        var presColor = d.pct_present >= 80 ? '#27ae60' : d.pct_present >= 60 ? '#f39c12' : '#e74c3c';
        presEl.innerHTML = '<span style="color:' + presColor + ';">' + d.pct_present + '%</span>';
        presSub.textContent = d.present_count + ' of ' + d.total_marked + ' marked present';

        var pend = IS_PERIOD ? (d.total_periods - d.marked_periods) : (d.total_sections - d.marked_sections);
        var pendColor = pend === 0 ? '#27ae60' : '#e74c3c';
        pendEl.innerHTML = '<span style="color:' + pendColor + ';">' + pend + '</span>';
        pendSub.textContent = IS_PERIOD ? 'periods not yet marked' : 'classes not yet marked';
    });
}

// ── Teacher status ───────────────────────────────────────────────
function loadTeacherStatus() {
    return fetch(ATT_BASE + '/ajax_teacher_status').then(r => r.json()).then(function(d) {
        var wrap = document.getElementById('teacher-status-wrap');
        if (!IS_PERIOD) {
            wrap.innerHTML = '<p style="color:#7f8c8d;font-size:13px;padding:12px 0;"><i class="fa fa-info-circle"></i> Teacher-wise period status is available in period-wise attendance mode. See the class heatmap below.</p>';
            return;
        }
        if (!d.rows || d.rows.length === 0) {
            wrap.innerHTML = '<div style="text-align:center;color:#bbb;padding:30px;"><i class="fa fa-calendar-times-o" style="font-size:28px;margin-bottom:8px;display:block;"></i>No periods scheduled today</div>';
            return;
        }
        var html = '';
        d.rows.forEach(function(r) {
            var total   = parseInt(r.total_periods) || 0;
            var marked  = parseInt(r.marked_periods) || 0;
            var pending = total - marked;
            var pct     = total > 0 ? Math.round(marked * 100 / total) : 0;
            var barColor = marked === total ? '#27ae60' : marked > 0 ? '#f39c12' : '#e74c3c';
            var badgeCls = marked === total ? 'badge-done' : marked > 0 ? 'badge-pend' : 'badge-none';
            var badgeTxt = marked === total ? 'All Done' : (pending + ' Pending');

            var initials = (r.teacher_name || '?').trim().split(' ').map(function(w){return w[0];}).slice(0,2).join('').toUpperCase();

            var avatarHtml = r.image
                ? '<img class="teacher-avatar" src="<?php echo $base; ?>uploads/staff/' + r.image + '" onerror="this.style.display=\'none\';this.nextSibling.style.display=\'flex\';">'
                  + '<div class="teacher-avatar-init" style="display:none;">' + initials + '</div>'
                : '<div class="teacher-avatar-init">' + initials + '</div>';

            var pendingDetail = '';
            if (r.pending_detail) {
                var items = r.pending_detail.split('|').filter(Boolean);
                if (items.length > 0) {
                    pendingDetail = '<div class="pending-tip"><i class="fa fa-clock-o"></i> ' + items.slice(0,2).join(' &bull; ') + (items.length > 2 ? ' +' + (items.length-2) + ' more' : '') + '</div>';
                }
            }

            html += '<div class="teacher-row">'
                + avatarHtml
                + '<div class="teacher-meta">'
                +   '<div class="teacher-name">' + escHtml(r.teacher_name) + '</div>'
                +   '<div class="teacher-bar-wrap">'
                +     '<div class="teacher-bar-bg"><div class="teacher-bar-fill" style="width:' + pct + '%;background:' + barColor + ';"></div></div>'
                +     '<span style="font-size:11px;color:#7f8c8d;white-space:nowrap;">' + marked + '/' + total + '</span>'
                +     '<span class="teacher-badge ' + badgeCls + '">' + badgeTxt + '</span>'
                +   '</div>'
                +   pendingDetail
                + '</div>'
                + '</div>';
        });
        wrap.innerHTML = html;
    });
}

// ── Heatmap ──────────────────────────────────────────────────────
function loadHeatmap() {
    return fetch(ATT_BASE + '/ajax_heatmap').then(r => r.json()).then(function(d) {
        var wrap = document.getElementById('heatmap-wrap');
        if (!d.rows || d.rows.length === 0) {
            wrap.innerHTML = '<div style="text-align:center;color:#bbb;padding:40px;"><i class="fa fa-calendar-times-o" style="font-size:28px;margin-bottom:8px;display:block;"></i>No timetable scheduled for today</div>';
            return;
        }

        // Organise by class → section
        var classes = {};
        var sections = {};
        d.rows.forEach(function(r) {
            if (!classes[r.class_id]) classes[r.class_id] = r.class_name;
            if (!sections[r.section_id]) sections[r.section_id] = r.section_name;
        });
        var classIds   = Object.keys(classes);
        var sectionIds = Object.keys(sections);

        // Build lookup
        var lookup = {};
        d.rows.forEach(function(r) {
            lookup[r.class_id + '_' + r.section_id] = r;
        });

        var isPeriod = d.mode === 'period';

        var html = '<table class="heatmap-table"><thead><tr><th></th>';
        sectionIds.forEach(function(sid) {
            html += '<th>' + escHtml(sections[sid]) + '</th>';
        });
        html += '</tr></thead><tbody>';

        classIds.forEach(function(cid) {
            html += '<tr><td class="hm-class-label">' + escHtml(classes[cid]) + '</td>';
            sectionIds.forEach(function(sid) {
                var cell = lookup[cid + '_' + sid];
                if (!cell) {
                    html += '<td class="heatmap-cell hm-empty">—</td>';
                    return;
                }
                var total  = parseInt(isPeriod ? cell.total_periods  : cell.total_students)  || 0;
                var marked = parseInt(isPeriod ? cell.marked_periods : cell.marked_students) || 0;
                var pct    = total > 0 ? Math.round(marked * 100 / total) : 0;
                var cls, icon;
                if (marked === 0)        { cls = 'hm-none';    icon = '✕'; }
                else if (marked < total) { cls = 'hm-partial'; icon = '~'; }
                else                     { cls = 'hm-done';    icon = '✓'; }
                var tip  = isPeriod ? (marked + '/' + total + ' periods') : (marked + '/' + total + ' students');
                var link = isPeriod
                    ? '<?php echo site_url("attendencereports/reportbymonth"); ?>'
                    : '<?php echo site_url("attendencereports/classattendencereport"); ?>';
                html += '<td class="heatmap-cell ' + cls + '" title="' + tip + '" onclick="window.location=\'' + link + '\'" style="cursor:pointer;">'
                    + icon + ' ' + pct + '%'
                    + '<div style="font-size:10px;opacity:.75;">' + tip + '</div>'
                    + '</td>';
            });
            html += '</tr>';
        });

        html += '</tbody></table>';
        wrap.innerHTML = html;
    });
}

// ── Weekly trend chart ───────────────────────────────────────────
function loadWeeklyTrend() {
    return fetch(ATT_BASE + '/ajax_weekly_trend').then(r => r.json()).then(function(d) {
        var emptyEl = document.getElementById('trend-empty');
        if (!d.rows || d.rows.length === 0) {
            emptyEl.style.display = 'block';
            return;
        }
        emptyEl.style.display = 'none';

        var labels = d.rows.map(function(r) {
            var dt = new Date(r.date);
            return dt.toLocaleDateString('en-IN', {weekday:'short', month:'short', day:'numeric'});
        });
        var pcts = d.rows.map(function(r) { return parseFloat(r.pct) || 0; });

        var ctx = document.getElementById('weeklyChart').getContext('2d');
        if (weeklyChart) weeklyChart.destroy();

        weeklyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Present %',
                    data: pcts,
                    borderColor: '#5b73e8',
                    backgroundColor: 'rgba(91,115,232,.10)',
                    borderWidth: 2.5,
                    pointBackgroundColor: pcts.map(function(p) {
                        return p >= 80 ? '#27ae60' : p >= 60 ? '#f39c12' : '#e74c3c';
                    }),
                    pointRadius: 5,
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: { min: 0, max: 100, ticks: { callback: v => v + '%', font: {size:11} }, grid: { color: '#f0f0f0' } },
                    x: { ticks: { font: {size:10} }, grid: { display: false } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ctx.parsed.y + '% present' } }
                }
            }
        });
    });
}

// ── Low attendance students ──────────────────────────────────────
function loadLowAttendance() {
    return fetch(ATT_BASE + '/ajax_low_attendance').then(r => r.json()).then(function(d) {
        var tbody = document.getElementById('low-att-tbody');
        if (!d.rows || d.rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#7f8c8d;padding:30px;"><i class="fa fa-check-circle" style="color:#27ae60;font-size:20px;margin-bottom:6px;display:block;"></i>All students are above ' + LOW_LIMIT + '% attendance this month</td></tr>';
            return;
        }
        var html = '';
        d.rows.forEach(function(r, i) {
            var pct  = parseFloat(r.pct) || 0;
            var cls  = pct < 50 ? 'pct-critical' : 'pct-warn';
            var name = escHtml((r.firstname || '') + ' ' + (r.lastname || ''));
            var link = IS_PERIOD
                ? '<?php echo site_url("attendencereports/reportbymonthstudent"); ?>'
                : '<?php echo site_url("attendencereports/classattendencereport"); ?>';
            html += '<tr>'
                + '<td style="color:#bbb;">' + (i+1) + '</td>'
                + '<td><strong>' + name + '</strong></td>'
                + '<td style="color:#7f8c8d;">' + escHtml(r.admission_no) + '</td>'
                + '<td>' + escHtml(r.class_name) + '</td>'
                + '<td>' + escHtml(r.section_name) + '</td>'
                + '<td><span class="pct-badge ' + cls + '">' + pct + '%</span>'
                +   '<span style="font-size:11px;color:#bbb;margin-left:6px;">' + r.present_count + '/' + r.total_records + '</span></td>'
                + '<td><a href="' + link + '" class="btn btn-xs btn-default"><i class="fa fa-eye"></i> View</a></td>'
                + '</tr>';
        });
        tbody.innerHTML = html;
    });
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Auto-load on page ready
document.addEventListener('DOMContentLoaded', refreshAll);
</script>
