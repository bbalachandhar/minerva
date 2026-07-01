<?php
$low_limit = isset($low_att_limit) ? (int)$low_att_limit : 75;
$warn_limit = $low_limit + 10;
?>
<style>
/* ── Filter form ─────────────────────────────── */
.rpt-filter-box { background:#fff; border-radius:10px; border:1px solid #eef0f3; box-shadow:0 2px 8px rgba(0,0,0,.05); padding:18px 20px 10px; margin-bottom:18px; }
.rpt-filter-box .form-group label { font-size:12px; font-weight:600; color:#7f8c8d; text-transform:uppercase; letter-spacing:.5px; }
.rpt-filter-box .form-control { border-radius:7px; border-color:#dde1e7; font-size:13px; height:36px; }
.rpt-search-btn { background:linear-gradient(135deg,#5b73e8,#7c5ce7); border:none; border-radius:8px; color:#fff; padding:7px 22px; font-size:13px; font-weight:600; }
/* ── Summary bar ─────────────────────────────── */
.rpt-summary { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
.rpt-sum-card { background:#fff; border-radius:10px; border:1px solid #eef0f3; box-shadow:0 1px 5px rgba(0,0,0,.05); padding:12px 18px; display:flex; align-items:center; gap:10px; flex:1; min-width:130px; }
.rpt-sum-icon { width:38px; height:38px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; }
.rpt-sum-val { font-size:22px; font-weight:700; color:#2c3e50; line-height:1; }
.rpt-sum-lbl { font-size:11px; color:#7f8c8d; margin-top:2px; font-weight:500; }
/* ── Matrix table ────────────────────────────── */
.matrix-wrap { overflow-x:auto; }
.matrix-tbl { border-collapse:separate; border-spacing:0; font-size:12px; white-space:nowrap; width:100%; }
.matrix-tbl th { background:#f8f9fb; color:#7f8c8d; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:.4px; padding:8px 10px; border-bottom:2px solid #eef0f3; position:sticky; top:0; z-index:2; }
.matrix-tbl th.subj-th { text-align:center; min-width:90px; max-width:120px; overflow:hidden; white-space:normal; }
.matrix-tbl th.subj-th small { display:block; font-weight:400; color:#bbb; font-size:10px; }
.matrix-tbl th.sticky-col { position:sticky; left:0; z-index:3; background:#f8f9fb; min-width:160px; }
.matrix-tbl td { padding:7px 10px; border-bottom:1px solid #f4f5f7; vertical-align:middle; }
.matrix-tbl td.sticky-col { position:sticky; left:0; z-index:1; background:#fff; border-right:2px solid #eef0f3; }
.matrix-tbl tr:hover td { background:#fafbff; }
.matrix-tbl tr:hover td.sticky-col { background:#fafbff; }
.matrix-tbl .foot-row td { background:#f8f9fb; font-weight:700; font-size:11px; border-top:2px solid #eef0f3; }
/* ── Attendance cells ────────────────────────── */
.att-cell { border-radius:8px; padding:5px 7px; text-align:center; display:inline-block; min-width:56px; font-weight:700; font-size:12px; line-height:1.3; }
.att-cell small { display:block; font-size:10px; font-weight:400; opacity:.85; }
.att-good    { background:#d4f5e4; color:#1a6b3c; }
.att-warn    { background:#fff8e1; color:#7a5c00; }
.att-low     { background:#fdecea; color:#c0392b; }
.att-empty   { background:#f4f5f7; color:#bbb; }
.student-name { font-size:13px; font-weight:600; color:#2c3e50; }
.student-adm  { font-size:11px; color:#7f8c8d; }
.no-data-block { text-align:center; padding:50px 20px; color:#7f8c8d; }
.no-data-block i { font-size:40px; margin-bottom:10px; display:block; color:#dde1e7; }
</style>

<div class="content-wrapper">
<section class="content-header">
    <h1>
        <i class="fa fa-th" style="color:#5b73e8;margin-right:8px;"></i>
        Period Attendance — Class Matrix
        <small><?php echo isset($month_label) ? htmlspecialchars($month_label) : 'Select filters below'; ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo site_url('admin/attendancedashboard/index'); ?>">Attendance Dashboard</a></li>
        <li class="active">Class Matrix Report</li>
    </ol>
</section>

<section class="content">
    <?php $this->load->view('attendencereports/_attendance'); ?>

    <!-- Filter form -->
    <div class="rpt-filter-box">
        <form method="post" action="<?php echo site_url('attendencereports/reportbymonth'); ?>">
            <?php echo $this->customlib->getCSRF(); ?>
            <div class="row">
                <?php if ($sch_setting->institution_type == 'college'): ?>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Department</label>
                        <select id="department_id" name="department_id" class="form-control">
                            <option value="">All</option>
                            <?php foreach ($department_list as $d): ?>
                            <option value="<?php echo $d['id']; ?>" <?php if (set_value('department_id') == $d['id']) echo 'selected'; ?>><?php echo htmlspecialchars($d['department_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Class <small class="req">*</small></label>
                        <select id="class_id" name="class_id" class="form-control">
                            <option value="">— Select Class —</option>
                        </select>
                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Section <small class="req">*</small></label>
                        <select id="section_id" name="section_id" class="form-control">
                            <option value="">Select</option>
                        </select>
                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Month <small class="req">*</small></label>
                        <select id="month" name="month" class="form-control">
                            <option value="">Select</option>
                            <?php foreach ($monthlist as $mk => $mv): ?>
                            <option value="<?php echo $mk; ?>" <?php echo set_select('month', $mk); ?>><?php echo $mv; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="text-danger"><?php echo form_error('month'); ?></span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Subject <small style="font-weight:400;color:#bbb;">(optional)</small></label>
                        <select id="subject_id" name="subject_id" class="form-control">
                            <option value="">All Subjects</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2" style="padding-top:22px;">
                    <button type="submit" class="btn rpt-search-btn btn-block">
                        <i class="fa fa-search"></i> Generate
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php if (isset($matrix) && $matrix !== null): ?>

    <?php if (empty($matrix['students'])): ?>
        <div class="rpt-filter-box no-data-block">
            <i class="fa fa-calendar-times-o"></i>
            No attendance records found for the selected criteria.
        </div>
    <?php else:
        $students      = $matrix['students'];
        $subjects      = $matrix['subjects'];
        $mat           = $matrix['matrix'];
        $subj_totals   = $matrix['subject_totals'];

        // Summary stats
        $total_records = 0; $total_present = 0;
        foreach ($mat as $sid => $subjs) {
            foreach ($subjs as $xid => $cell) {
                $total_records += $cell['total'];
                $total_present += $cell['present'];
            }
        }
        $overall_pct = $total_records > 0 ? round($total_present * 100 / $total_records, 1) : 0;
        $low_students = 0;
        foreach ($students as $sid => $st) {
            $sp = 0; $st_total = 0;
            if (!empty($mat[$sid])) {
                foreach ($mat[$sid] as $c) { $sp += $c['present']; $st_total += $c['total']; }
            }
            if ($st_total > 0 && round($sp * 100 / $st_total, 1) < $low_limit) $low_students++;
        }
    ?>

    <!-- Summary bar -->
    <div class="rpt-summary">
        <div class="rpt-sum-card">
            <div class="rpt-sum-icon" style="background:#edf2ff;color:#5b73e8;"><i class="fa fa-users"></i></div>
            <div><div class="rpt-sum-val"><?php echo count($students); ?></div><div class="rpt-sum-lbl">Students</div></div>
        </div>
        <div class="rpt-sum-card">
            <div class="rpt-sum-icon" style="background:#e8fdf4;color:#27ae60;"><i class="fa fa-book"></i></div>
            <div><div class="rpt-sum-val"><?php echo count($subjects); ?></div><div class="rpt-sum-lbl">Subjects</div></div>
        </div>
        <div class="rpt-sum-card">
            <div class="rpt-sum-icon" style="background:#fef9e7;color:#f39c12;"><i class="fa fa-clock-o"></i></div>
            <div><div class="rpt-sum-val"><?php echo number_format($total_records); ?></div><div class="rpt-sum-lbl">Total Periods</div></div>
        </div>
        <div class="rpt-sum-card">
            <div class="rpt-sum-icon" style="background:<?php echo $overall_pct >= 80 ? '#e8fdf4' : ($overall_pct >= $low_limit ? '#fff8e1' : '#fdecea'); ?>;color:<?php echo $overall_pct >= 80 ? '#27ae60' : ($overall_pct >= $low_limit ? '#f39c12' : '#e74c3c'); ?>;"><i class="fa fa-percent"></i></div>
            <div><div class="rpt-sum-val" style="color:<?php echo $overall_pct >= 80 ? '#27ae60' : ($overall_pct >= $low_limit ? '#f39c12' : '#e74c3c'); ?>;"><?php echo $overall_pct; ?>%</div><div class="rpt-sum-lbl">Overall Attendance</div></div>
        </div>
        <?php if ($low_students > 0): ?>
        <div class="rpt-sum-card">
            <div class="rpt-sum-icon" style="background:#fdecea;color:#e74c3c;"><i class="fa fa-exclamation-triangle"></i></div>
            <div><div class="rpt-sum-val" style="color:#e74c3c;"><?php echo $low_students; ?></div><div class="rpt-sum-lbl">Below <?php echo $low_limit; ?>%</div></div>
        </div>
        <?php endif; ?>
        <div style="margin-left:auto;display:flex;align-items:center;gap:8px;">
            <span style="font-size:11px;color:#7f8c8d;">Legend:</span>
            <span class="att-cell att-good" style="font-size:11px;">≥ <?php echo $warn_limit; ?>%</span>
            <span class="att-cell att-warn" style="font-size:11px;">≥ <?php echo $low_limit; ?>%</span>
            <span class="att-cell att-low" style="font-size:11px;">< <?php echo $low_limit; ?>%</span>
        </div>
    </div>

    <!-- Matrix table -->
    <div class="rpt-filter-box" style="padding:0;">
        <div class="matrix-wrap">
            <table class="matrix-tbl">
                <thead>
                    <tr>
                        <th class="sticky-col" style="min-width:180px;">#&nbsp; Student</th>
                        <?php foreach ($subjects as $xid => $subj): ?>
                        <th class="subj-th">
                            <?php echo htmlspecialchars($subj['name']); ?>
                            <?php if ($subj['code']): ?><small><?php echo htmlspecialchars($subj['code']); ?></small><?php endif; ?>
                        </th>
                        <?php endforeach; ?>
                        <th class="subj-th" style="background:#f0f2ff;">Overall</th>
                    </tr>
                </thead>
                <tbody>
                <?php $i = 1; foreach ($students as $sid => $st):
                    $st_present = 0; $st_total = 0;
                    if (!empty($mat[$sid])) { foreach ($mat[$sid] as $c) { $st_present += $c['present']; $st_total += $c['total']; } }
                    $st_pct = $st_total > 0 ? round($st_present * 100 / $st_total, 1) : null;
                    $st_cls = is_null($st_pct) ? 'att-empty' : ($st_pct >= $warn_limit ? 'att-good' : ($st_pct >= $low_limit ? 'att-warn' : 'att-low'));
                ?>
                <tr>
                    <td class="sticky-col">
                        <span style="color:#bbb;margin-right:6px;font-size:11px;"><?php echo $i++; ?></span>
                        <span class="student-name"><?php echo htmlspecialchars($this->customlib->getFullName($st['firstname'], $st['middlename'], $st['lastname'], $sch_setting->middlename, $sch_setting->lastname)); ?></span>
                        <br><span class="student-adm"><?php echo htmlspecialchars($st['admission_no']); ?></span>
                    </td>
                    <?php foreach ($subjects as $xid => $subj):
                        $cell = isset($mat[$sid][$xid]) ? $mat[$sid][$xid] : null;
                        if ($cell) {
                            $cls = $cell['pct'] >= $warn_limit ? 'att-good' : ($cell['pct'] >= $low_limit ? 'att-warn' : 'att-low');
                        }
                    ?>
                    <td style="text-align:center;">
                        <?php if ($cell): ?>
                        <span class="att-cell <?php echo $cls; ?>">
                            <?php echo $cell['pct']; ?>%
                            <small><?php echo $cell['present']; ?>/<?php echo $cell['total']; ?></small>
                        </span>
                        <?php else: ?>
                        <span class="att-cell att-empty">—</span>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                    <td style="text-align:center;">
                        <?php if (!is_null($st_pct)): ?>
                        <span class="att-cell <?php echo $st_cls; ?>" style="font-size:13px;">
                            <strong><?php echo $st_pct; ?>%</strong>
                            <small><?php echo $st_present; ?>/<?php echo $st_total; ?></small>
                        </span>
                        <?php else: ?>
                        <span class="att-cell att-empty">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="foot-row">
                        <td class="sticky-col" style="font-size:12px;color:#5b73e8;font-weight:700;">
                            <i class="fa fa-bar-chart"></i> Subject Average
                        </td>
                        <?php foreach ($subjects as $xid => $subj):
                            $t = isset($subj_totals[$xid]) ? $subj_totals[$xid] : null;
                            $tcls = $t ? ($t['pct'] >= $warn_limit ? 'att-good' : ($t['pct'] >= $low_limit ? 'att-warn' : 'att-low')) : 'att-empty';
                        ?>
                        <td style="text-align:center;">
                            <?php if ($t): ?>
                            <span class="att-cell <?php echo $tcls; ?>">
                                <?php echo $t['pct']; ?>%
                                <small><?php echo $t['present']; ?>/<?php echo $t['total']; ?></small>
                            </span>
                            <?php else: ?><span class="att-cell att-empty">—</span><?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                        <td style="text-align:center;">
                            <?php $ocls = $overall_pct >= $warn_limit ? 'att-good' : ($overall_pct >= $low_limit ? 'att-warn' : 'att-low'); ?>
                            <span class="att-cell <?php echo $ocls; ?>" style="font-size:13px;"><strong><?php echo $overall_pct; ?>%</strong></span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <?php endif; // empty check
    endif; // matrix isset ?>

</section>
</div>

<script>
$(window).on('load', function() {
    var savedDept = '<?php echo addslashes(set_value('department_id')); ?>';
    var savedCls  = '<?php echo addslashes(set_value('class_id')); ?>';
    var savedSec  = '<?php echo addslashes(set_value('section_id')); ?>';
    var savedSubj = '<?php echo addslashes(set_value('subject_id')); ?>';
    var allClasses = []; // cached once, filtered client-side per department

    // ── Select2 ──────────────────────────────────────────────
    var s2 = { width: '100%', allowClear: true };
    $('#department_id').select2($.extend({}, s2, {placeholder: '— All Departments (optional) —'}));
    $('#class_id').select2($.extend({}, s2, {placeholder: '— Select Class —'}));
    $('#section_id').select2($.extend({}, s2, {placeholder: '— Select Section —'}));
    $('#month').select2($.extend({}, s2, {placeholder: '— Select Month —', allowClear: false}));
    $('#subject_id').select2($.extend({}, s2, {placeholder: '— All Subjects —'}));

    // ── Load ALL classes once on page load (no PHP pre-population) ─
    $.getJSON(baseurl + 'attendencereports/getAllAcademicClasses', function(data) {
        allClasses = data;
        renderClasses(allClasses, savedCls, function() {
            if (savedCls) {
                loadSections(savedCls, savedSec, function() {
                    if (savedSec) loadSubjects(savedCls, savedSec, savedSubj);
                });
            }
        });
    });

    // ── Department → filter class list CLIENT-SIDE (instant, no AJAX) ─
    $('#department_id').on('select2:select select2:unselect select2:clear change', function() {
        var deptId = $(this).val();
        var filtered = deptId
            ? allClasses.filter(function(c) { return String(c.department_id) === String(deptId); })
            : allClasses;
        renderClasses(filtered, '', null);
        resetDrop('#section_id', '— Select Section —');
        resetDrop('#subject_id', '— All Subjects —');
    });

    // ── Class → load sections ─────────────────────────────────
    $(document).on('change', '#class_id', function() {
        var cid = $(this).val();
        resetDrop('#section_id', '— Select Section —');
        resetDrop('#subject_id', '— All Subjects —');
        if (cid) loadSections(cid, '', null);
    });
    $('#class_id').on('select2:select', function() {
        var cid = $(this).val();
        resetDrop('#section_id', '— Select Section —');
        resetDrop('#subject_id', '— All Subjects —');
        if (cid) loadSections(cid, '', null);
    });

    // ── Section → load subjects ───────────────────────────────
    $(document).on('change', '#section_id', function() {
        var sid = $(this).val();
        resetDrop('#subject_id', '— All Subjects —');
        if (sid) loadSubjects($('#class_id').val(), sid, '');
    });
    $('#section_id').on('select2:select', function() {
        var sid = $(this).val();
        resetDrop('#subject_id', '— All Subjects —');
        if (sid) loadSubjects($('#class_id').val(), sid, '');
    });

    // ── Helpers ───────────────────────────────────────────────
    function renderClasses(classes, sel, callback) {
        var html = '<option value="">— Select Class —</option>';
        $.each(classes, function(i, c) {
            html += '<option value="' + c.id + '" data-dept="' + c.department_id + '"'
                 + (sel && sel == c.id ? ' selected' : '') + '>' + esc(c['class']) + '</option>';
        });
        $('#class_id').html(html).trigger('change.select2');
        if (callback) callback();
    }

    function loadSections(cid, sel, callback) {
        if (!cid) return;
        $.getJSON(baseurl + 'sections/getByClass', {class_id: cid, department_id: $('#department_id').val()}, function(data) {
            var html = '<option value="">— Select Section —</option>';
            $.each(data, function(i, o) {
                html += '<option value="' + o.section_id + '"' + (sel && sel == o.section_id ? ' selected' : '') + '>' + esc(o.section) + '</option>';
            });
            $('#section_id').html(html).trigger('change.select2');
            if (callback) callback();
        });
    }

    function loadSubjects(cid, sid, sel) {
        if (!cid || !sid) return;
        $.ajax({
            type: 'POST',
            url:  baseurl + 'attendencereports/getSubjectsByClassSection',
            data: {class_id: cid, section_id: sid},
            dataType: 'json',
            success: function(data) {
                var html = '<option value="">— All Subjects —</option>';
                if (data && data.length) {
                    $.each(data, function(i, o) {
                        var lbl = esc(o.subject_name) + (o.subject_code ? ' (' + esc(o.subject_code) + ')' : '');
                        html += '<option value="' + o.subject_id + '"' + (sel && sel == o.subject_id ? ' selected' : '') + '>' + lbl + '</option>';
                    });
                }
                $('#subject_id').html(html).trigger('change.select2');
            },
            error: function() {
                $('#subject_id').html('<option value="">— All Subjects —</option>').trigger('change.select2');
            }
        });
    }

    function resetDrop(sel, ph) { $(sel).html('<option value="">' + ph + '</option>').trigger('change.select2'); }
    function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
});
</script>
