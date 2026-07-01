<style>
.rpt-filter-box { background:#fff; border-radius:10px; border:1px solid #eef0f3; box-shadow:0 2px 8px rgba(0,0,0,.05); padding:18px 20px 10px; margin-bottom:18px; }
.rpt-filter-box .form-group label { font-size:12px; font-weight:600; color:#7f8c8d; text-transform:uppercase; letter-spacing:.5px; }
.rpt-filter-box .form-control { border-radius:7px; border-color:#dde1e7; font-size:13px; height:36px; }
.rpt-search-btn { background:linear-gradient(135deg,#5b73e8,#7c5ce7); border:none; border-radius:8px; color:#fff; padding:7px 22px; font-size:13px; font-weight:600; }
/* Teacher blocks */
.tchr-block { background:#fff; border-radius:12px; border:1px solid #eef0f3; box-shadow:0 2px 8px rgba(0,0,0,.05); margin-bottom:14px; overflow:hidden; }
.tchr-header { padding:14px 18px; display:flex; align-items:center; gap:14px; border-bottom:1px solid #eef0f3; cursor:pointer; }
.tchr-avatar { width:44px; height:44px; border-radius:50%; object-fit:cover; border:2px solid #eee; flex-shrink:0; }
.tchr-avatar-init { width:44px; height:44px; border-radius:50%; background:linear-gradient(135deg,#5b73e8,#7c5ce7); color:#fff; font-weight:700; font-size:16px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.tchr-name { font-size:14px; font-weight:700; color:#2c3e50; }
.tchr-empid { font-size:11px; color:#7f8c8d; margin-top:2px; }
.tchr-bar-wrap { flex:1; display:flex; align-items:center; gap:10px; }
.tchr-bar-bg { flex:1; height:8px; background:#eef0f3; border-radius:4px; overflow:hidden; }
.tchr-bar-fill { height:100%; border-radius:4px; transition:width .5s; }
.tchr-pct-badge { font-size:13px; font-weight:700; min-width:42px; text-align:right; }
.tchr-status-badge { font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; white-space:nowrap; }
.badge-excellent { background:#d4f5e4; color:#1a6b3c; }
.badge-good      { background:#e8f4fd; color:#1a6b9a; }
.badge-fair      { background:#fff3cd; color:#856404; }
.badge-poor      { background:#fdecea; color:#c0392b; }
/* Subject rows */
.subj-tbl { width:100%; border-collapse:collapse; font-size:12px; }
.subj-tbl th { background:#f8f9fb; color:#7f8c8d; font-weight:700; font-size:11px; text-transform:uppercase; padding:7px 14px; border-bottom:1px solid #eef0f3; }
.subj-tbl td { padding:8px 14px; border-bottom:1px solid #f4f5f7; vertical-align:middle; }
.subj-tbl tr:last-child td { border-bottom:none; }
.mini-bar-wrap { display:flex; align-items:center; gap:8px; }
.mini-bar-bg { flex:1; height:6px; background:#eef0f3; border-radius:3px; overflow:hidden; max-width:120px; }
.mini-bar-fill { height:100%; border-radius:3px; }
/* Summary cards */
.cov-summary { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
.cov-card { flex:1; min-width:130px; background:#fff; border-radius:10px; border:1px solid #eef0f3; box-shadow:0 1px 5px rgba(0,0,0,.05); padding:14px 16px; text-align:center; }
.cov-val { font-size:26px; font-weight:700; line-height:1; }
.cov-lbl { font-size:11px; color:#7f8c8d; margin-top:4px; font-weight:500; }
</style>

<div class="content-wrapper">
<section class="content-header">
    <h1>
        <i class="fa fa-user-circle-o" style="color:#5b73e8;margin-right:8px;"></i>
        Teacher Marking Coverage
        <small>Period attendance compliance by teacher</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo site_url('admin/attendancedashboard/index'); ?>">Attendance Dashboard</a></li>
        <li class="active">Teacher Coverage</li>
    </ol>
</section>

<section class="content">
    <?php $this->load->view('attendencereports/_attendance'); ?>

    <!-- Flatpickr — loaded inline so it never depends on footer load order -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
    .flatpickr-input { cursor: pointer !important; background:#fff !important; }
    .fp-wrap { position:relative; }
    .fp-wrap .fp-icon { position:absolute; right:10px; top:50%; transform:translateY(-50%); color:#5b73e8; pointer-events:none; font-size:14px; }
    </style>

    <!-- Filter form -->
    <div class="rpt-filter-box">
        <form method="post" action="<?php echo site_url('attendencereports/teachermarkingcoverage'); ?>">
            <?php echo $this->customlib->getCSRF(); ?>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>From Date <small class="req">*</small></label>
                        <div class="fp-wrap">
                            <input type="text" name="from_date" id="from_date" class="form-control"
                                   value="<?php echo htmlspecialchars($from_date); ?>"
                                   autocomplete="off" placeholder="Select from date" readonly>
                            <i class="fa fa-calendar fp-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>To Date <small class="req">*</small></label>
                        <div class="fp-wrap">
                            <input type="text" name="to_date" id="to_date" class="form-control"
                                   value="<?php echo htmlspecialchars($to_date); ?>"
                                   autocomplete="off" placeholder="Select to date" readonly>
                            <i class="fa fa-calendar fp-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3" style="padding-top:22px;">
                    <button type="submit" class="btn rpt-search-btn">
                        <i class="fa fa-search"></i> Generate Report
                    </button>
                    <?php if (!empty($from_date)): ?>
                    <a href="<?php echo site_url('attendencereports/teachermarkingcoverage'); ?>" class="btn btn-default btn-sm" style="margin-left:6px;">Reset</a>
                    <?php endif; ?>
                </div>
                <div class="col-md-3" style="padding-top:26px;">
                    <div class="btn-group pull-right">
                        <a href="<?php echo site_url('admin/attendancedashboard/index'); ?>" class="btn btn-sm btn-default">
                            <i class="fa fa-bar-chart"></i> Live Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php if (isset($rows) && $rows !== null): ?>
    <?php if (empty($rows)): ?>
        <div class="rpt-filter-box" style="text-align:center;padding:40px;color:#7f8c8d;">
            <i class="fa fa-calendar-times-o" style="font-size:36px;color:#dde1e7;display:block;margin-bottom:10px;"></i>
            No period attendance scheduled in this date range.
        </div>
    <?php else:
        // Group rows by teacher
        $teachers = [];
        foreach ($rows as $r) {
            $tid = $r['staff_id'];
            if (!isset($teachers[$tid])) {
                $teachers[$tid] = [
                    'staff_id'     => $tid,
                    'name'         => $r['teacher_name'],
                    'employee_id'  => $r['employee_id'],
                    'image'        => $r['image'],
                    'scheduled'    => 0,
                    'marked'       => 0,
                    'subjects'     => [],
                ];
            }
            $teachers[$tid]['scheduled'] += (int)$r['scheduled_periods'];
            $teachers[$tid]['marked']    += (int)$r['marked_periods'];
            $teachers[$tid]['subjects'][] = $r;
        }

        // Summary
        $total_sch = 0; $total_mrk = 0; $never_marked = 0; $full_marked = 0;
        foreach ($teachers as $t) {
            $total_sch += $t['scheduled']; $total_mrk += $t['marked'];
            if ($t['marked'] === 0) $never_marked++;
            if ($t['marked'] === $t['scheduled'] && $t['scheduled'] > 0) $full_marked++;
        }
        $overall_cov = $total_sch > 0 ? round($total_mrk * 100 / $total_sch, 1) : 0;

        // Sort: worst first
        uasort($teachers, function($a, $b) {
            $pa = $a['scheduled'] > 0 ? $a['marked']/$a['scheduled'] : 0;
            $pb = $b['scheduled'] > 0 ? $b['marked']/$b['scheduled'] : 0;
            return $pa <=> $pb;
        });
    ?>

    <!-- Summary cards -->
    <div class="cov-summary">
        <div class="cov-card">
            <div class="cov-val" style="color:#5b73e8;"><?php echo count($teachers); ?></div>
            <div class="cov-lbl">Teachers</div>
        </div>
        <div class="cov-card">
            <div class="cov-val" style="color:#7f8c8d;"><?php echo number_format($total_sch); ?></div>
            <div class="cov-lbl">Periods Scheduled</div>
        </div>
        <div class="cov-card">
            <div class="cov-val" style="color:#27ae60;"><?php echo $full_marked; ?></div>
            <div class="cov-lbl">100% Compliant</div>
        </div>
        <div class="cov-card">
            <div class="cov-val" style="color:#e74c3c;"><?php echo $never_marked; ?></div>
            <div class="cov-lbl">Never Marked</div>
        </div>
        <div class="cov-card">
            <?php $oc = $overall_cov >= 90 ? '#27ae60' : ($overall_cov >= 70 ? '#f39c12' : '#e74c3c'); ?>
            <div class="cov-val" style="color:<?php echo $oc; ?>;"><?php echo $overall_cov; ?>%</div>
            <div class="cov-lbl">Overall Coverage</div>
        </div>
    </div>

    <!-- Teacher blocks -->
    <?php foreach ($teachers as $t):
        $pct = $t['scheduled'] > 0 ? round($t['marked'] * 100 / $t['scheduled'], 1) : 0;
        $bar_color = $pct >= 90 ? '#27ae60' : ($pct >= 70 ? '#3498db' : ($pct >= 50 ? '#f39c12' : '#e74c3c'));
        $badge_cls = $pct >= 90 ? 'badge-excellent' : ($pct >= 70 ? 'badge-good' : ($pct >= 50 ? 'badge-fair' : 'badge-poor'));
        $badge_txt = $pct >= 90 ? 'Excellent' : ($pct >= 70 ? 'Good' : ($pct >= 50 ? 'Fair' : 'Poor'));
        $initials   = collect_initials($t['name']);
        $block_id   = 'tchr-'.htmlspecialchars($t['staff_id']);
    ?>
    <div class="tchr-block">
        <div class="tchr-header" onclick="toggleBlock('<?php echo $block_id; ?>')">
            <?php if ($t['image']): ?>
            <img class="tchr-avatar" src="<?php echo base_url(); ?>uploads/staff/<?php echo htmlspecialchars($t['image']); ?>"
                 onerror="this.style.display='none';this.nextSibling.style.display='flex';">
            <div class="tchr-avatar-init" style="display:none;"><?php echo $initials; ?></div>
            <?php else: ?>
            <div class="tchr-avatar-init"><?php echo $initials; ?></div>
            <?php endif; ?>
            <div>
                <div class="tchr-name"><?php echo htmlspecialchars($t['name']); ?></div>
                <div class="tchr-empid"><?php echo htmlspecialchars($t['employee_id'] ?? ''); ?></div>
            </div>
            <div class="tchr-bar-wrap">
                <div class="tchr-bar-bg">
                    <div class="tchr-bar-fill" style="width:<?php echo $pct; ?>%;background:<?php echo $bar_color; ?>;"></div>
                </div>
                <span class="tchr-pct-badge" style="color:<?php echo $bar_color; ?>;"><?php echo $pct; ?>%</span>
                <span class="tchr-status-badge <?php echo $badge_cls; ?>"><?php echo $badge_txt; ?></span>
                <span style="color:#bbb;font-size:12px;margin-left:6px;"><?php echo $t['marked']; ?>/<?php echo $t['scheduled']; ?></span>
            </div>
            <i class="fa fa-angle-down" style="color:#bbb;margin-left:10px;" id="<?php echo $block_id; ?>-icon"></i>
        </div>
        <div id="<?php echo $block_id; ?>" style="display:none;">
            <table class="subj-tbl">
                <thead>
                    <tr>
                        <th>Subject</th><th>Class / Section</th>
                        <th>Scheduled</th><th>Marked</th><th>Coverage</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($t['subjects'] as $subj):
                    $sp = (int)$subj['scheduled_periods'];
                    $mp = (int)$subj['marked_periods'];
                    $spct = $sp > 0 ? round($mp*100/$sp, 1) : 0;
                    $sc = $spct >= 90 ? '#27ae60' : ($spct >= 70 ? '#3498db' : ($spct >= 50 ? '#f39c12' : '#e74c3c'));
                ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($subj['subject_name']); ?></strong>
                        <?php if ($subj['subject_code']): ?>
                        <small style="color:#bbb;"> (<?php echo htmlspecialchars($subj['subject_code']); ?>)</small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($subj['class_name']); ?> &mdash; <?php echo htmlspecialchars($subj['section_name']); ?></td>
                    <td><?php echo $sp; ?></td>
                    <td><?php echo $mp; ?></td>
                    <td>
                        <div class="mini-bar-wrap">
                            <div class="mini-bar-bg">
                                <div class="mini-bar-fill" style="width:<?php echo $spct; ?>%;background:<?php echo $sc; ?>;"></div>
                            </div>
                            <span style="font-size:12px;font-weight:700;color:<?php echo $sc; ?>;"><?php echo $spct; ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>

    <?php endif; // empty rows
    endif; // isset rows ?>

</section>
</div>

<?php
function collect_initials($name) {
    $parts = array_filter(explode(' ', trim($name)));
    $init = '';
    foreach (array_slice($parts, 0, 2) as $p) { $init .= strtoupper($p[0]); }
    return $init ?: '?';
}
?>
<script>
function toggleBlock(id) {
    var el = document.getElementById(id);
    var icon = document.getElementById(id+'-icon');
    if (!el) return;
    var open = el.style.display !== 'none';
    el.style.display = open ? 'none' : 'block';
    if (icon) { icon.className = open ? 'fa fa-angle-down' : 'fa fa-angle-up'; icon.style.color = open ? '#bbb' : '#5b73e8'; }
}
$(document).ready(function() {
    // Open the first (worst) teacher by default
    var first = document.querySelector('.tchr-block .tchr-header');
    if (first) first.click();
});

// Flatpickr — runs immediately since the script is embedded above
(function initFlatpickr() {
    // dateFormat Y-m-d: Flatpickr submits YYYY-MM-DD, customlib->datetostrtotime() handles it fine.
    // altInput + altFormat gives the user a readable display like "Wed 01 Jul 2026".
    var fromPicker = flatpickr('#from_date', {
        dateFormat:  'Y-m-d',
        altInput:    true,
        altFormat:   'D, d M Y',
        allowInput:  false,
        maxDate:     'today',
        disableMobile: false,
        onChange: function(dates) {
            if (dates.length) toPicker.set('minDate', dates[0]);
        }
    });
    var toPicker = flatpickr('#to_date', {
        dateFormat:  'Y-m-d',
        altInput:    true,
        altFormat:   'D, d M Y',
        allowInput:  false,
        maxDate:     'today',
        disableMobile: false,
        onChange: function(dates) {
            if (dates.length) fromPicker.set('maxDate', dates[0]);
        }
    });
    // If pre-filled values exist (page reloaded after submit), parse them
    var fv = document.getElementById('from_date').value;
    var tv = document.getElementById('to_date').value;
    if (fv) fromPicker.setDate(fv, false);
    if (tv) toPicker.setDate(tv, false);
})();
</script>
