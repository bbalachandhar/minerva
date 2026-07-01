<?php
$low_limit = isset($low_att_limit) ? (int)$low_att_limit : 75;

// Pre-compute summary stats from resultlist
$total_held = 0; $total_present = 0; $total_absent = 0; $total_na = 0;
$days_with_class = 0;

function _att_type_key($attendencetypeslist, $id) {
    if (!$id) return null;
    foreach ((array)$attendencetypeslist as $t) {
        if ($t['id'] == $id) return $t['key_value'];
    }
    return null;
}

if (!empty($resultlist['students_attendances'])) {
    foreach ($resultlist['students_attendances'] as $day_data) {
        if (empty($day_data['subjects'])) continue;
        $days_with_class++;
        foreach ($day_data['subjects'] as $idx => $subj) {
            $count = $idx + 1;
            $att_id = isset($day_data['attendances']) ? ($day_data['attendances']->{"attendence_type_id_".$count} ?? '') : '';
            $total_held++;
            if ($att_id == 1) $total_present++;
            elseif ($att_id == 4) $total_absent++;
            elseif ($att_id == '') $total_na++;
        }
    }
}
$pct = $total_held > 0 ? round($total_present * 100 / $total_held, 1) : null;
$pct_color = is_null($pct) ? '#bbb' : ($pct >= 80 ? '#27ae60' : ($pct >= $low_limit ? '#f39c12' : '#e74c3c'));
?>
<style>
.rpt-filter-box { background:#fff; border-radius:10px; border:1px solid #eef0f3; box-shadow:0 2px 8px rgba(0,0,0,.05); padding:18px 20px 10px; margin-bottom:18px; }
.rpt-filter-box .form-group label { font-size:12px; font-weight:600; color:#7f8c8d; text-transform:uppercase; letter-spacing:.5px; }
.rpt-filter-box .form-control { border-radius:7px; border-color:#dde1e7; font-size:13px; height:36px; }
.rpt-search-btn { background:linear-gradient(135deg,#5b73e8,#7c5ce7); border:none; border-radius:8px; color:#fff; padding:7px 22px; font-size:13px; font-weight:600; }
/* Summary cards */
.stu-summary { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
.stu-sum-card { flex:1; min-width:120px; background:#fff; border-radius:10px; border:1px solid #eef0f3; box-shadow:0 1px 5px rgba(0,0,0,.05); padding:14px 16px; text-align:center; }
.stu-sum-val { font-size:26px; font-weight:700; line-height:1; }
.stu-sum-lbl { font-size:11px; color:#7f8c8d; margin-top:4px; font-weight:500; }
/* Student info card */
.stu-info-card { background:linear-gradient(135deg,#5b73e8,#7c5ce7); border-radius:12px; padding:18px 22px; color:#fff; display:flex; align-items:center; gap:18px; margin-bottom:16px; }
.stu-info-card .stu-avatar { width:54px; height:54px; border-radius:50%; background:rgba(255,255,255,.25); display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:700; flex-shrink:0; }
.stu-info-card .stu-name { font-size:18px; font-weight:700; }
.stu-info-card .stu-meta { font-size:12px; opacity:.8; margin-top:3px; }
.stu-info-card .stu-pct { margin-left:auto; text-align:center; }
.stu-info-card .stu-pct-val { font-size:36px; font-weight:800; line-height:1; }
.stu-info-card .stu-pct-lbl { font-size:11px; opacity:.8; }
/* Calendar heatmap */
.cal-wrap { background:#fff; border-radius:10px; border:1px solid #eef0f3; padding:16px; margin-bottom:16px; }
.cal-title { font-size:12px; font-weight:700; color:#7f8c8d; text-transform:uppercase; letter-spacing:.5px; margin-bottom:12px; }
.cal-grid { display:grid; grid-template-columns: repeat(7,1fr); gap:5px; }
.cal-day-hdr { font-size:10px; font-weight:700; color:#bbb; text-align:center; padding:2px 0; }
.cal-day { border-radius:8px; padding:6px 3px; text-align:center; font-size:11px; font-weight:600; min-height:36px; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.cal-day .day-num { font-size:13px; font-weight:700; }
.cal-day .day-pct { font-size:9px; margin-top:1px; }
.cal-present { background:#d4f5e4; color:#1a6b3c; }
.cal-absent  { background:#fdecea; color:#c0392b; }
.cal-mixed   { background:#fff8e1; color:#7a5c00; }
.cal-noclass { background:#f4f5f7; color:#bbb; }
.cal-empty   { background:transparent; }
/* Day-by-day table */
.day-tbl { width:100%; border-collapse:separate; border-spacing:0 5px; }
.day-tbl th { font-size:11px; color:#7f8c8d; text-transform:uppercase; font-weight:700; padding:6px 12px; }
.day-row td { background:#fff; padding:10px 12px; border-top:1px solid #f4f5f7; border-bottom:1px solid #f4f5f7; vertical-align:middle; }
.day-row td:first-child { border-left:1px solid #f4f5f7; border-radius:8px 0 0 8px; }
.day-row td:last-child { border-right:1px solid #f4f5f7; border-radius:0 8px 8px 0; }
.day-date { font-size:13px; font-weight:700; color:#2c3e50; }
.day-dayname { font-size:11px; color:#7f8c8d; }
.period-row { display:flex; align-items:center; gap:8px; padding:3px 0; border-bottom:1px solid #f8f9fb; }
.period-row:last-child { border-bottom:none; }
.period-subj { font-size:12px; font-weight:600; color:#34495e; min-width:140px; }
.period-time { font-size:11px; color:#7f8c8d; min-width:110px; }
.att-pill { display:inline-block; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.pill-P, .pill-p  { background:#d4f5e4; color:#1a6b3c; }
.pill-A, .pill-a  { background:#fdecea; color:#c0392b; }
.pill-L, .pill-l  { background:#fff3cd; color:#856404; }
.pill-HD { background:#e8f4fd; color:#1a6b9a; }
.pill-NA { background:#f4f5f7; color:#bbb; }
</style>

<div class="content-wrapper">
<section class="content-header">
    <h1>
        <i class="fa fa-user-circle-o" style="color:#5b73e8;margin-right:8px;"></i>
        Student Period Attendance
        <small>Monthly timeline view</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo site_url('admin/attendancedashboard/index'); ?>">Attendance Dashboard</a></li>
        <li class="active">Student Timeline</li>
    </ol>
</section>

<section class="content">
    <?php $this->load->view('attendencereports/_attendance'); ?>

    <!-- Filter form -->
    <div class="rpt-filter-box">
        <form method="post" action="<?php echo site_url('attendencereports/reportbymonthstudent'); ?>">
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
                            <option value="">Select</option>
                            <?php foreach ($classlist as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php if (set_value('class_id') == $c['id']) echo 'selected'; ?>><?php echo htmlspecialchars($c['class']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Section <small class="req">*</small></label>
                        <select id="section_id" name="section_id" class="form-control"><option value="">Select</option></select>
                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Student <small class="req">*</small></label>
                        <select id="student_id" name="student_id" class="form-control"><option value="">Select</option></select>
                        <span class="text-danger"><?php echo form_error('student_id'); ?></span>
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
                <div class="col-md-2" style="padding-top:22px;">
                    <button type="submit" class="btn rpt-search-btn btn-block">
                        <i class="fa fa-search"></i> View Report
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php if (isset($resultlist)): ?>
    <?php if (empty($resultlist) || empty($resultlist['students_attendances'])): ?>
        <div class="rpt-filter-box" style="text-align:center;padding:40px;color:#7f8c8d;">
            <i class="fa fa-calendar-times-o" style="font-size:36px;color:#dde1e7;display:block;margin-bottom:10px;"></i>
            No attendance data found for this student in the selected month.
        </div>
    <?php else:
        // Get student name from first record if available
        $student_name  = set_value('student_id') ? '' : '';
        $student_adm   = set_value('student_id', '');
    ?>

    <!-- Student info + overall % banner -->
    <div class="stu-info-card">
        <div class="stu-avatar"><i class="fa fa-user"></i></div>
        <div>
            <div class="stu-name">
                <?php
                // Try to get student name from the attendance data
                if (!empty($resultlist['student_info'])) {
                    echo htmlspecialchars($this->customlib->getFullName(
                        $resultlist['student_info']->firstname ?? '',
                        $resultlist['student_info']->middlename ?? '',
                        $resultlist['student_info']->lastname ?? '',
                        $sch_setting->middlename, $sch_setting->lastname
                    ));
                } else {
                    echo 'Student Report';
                }
                ?>
            </div>
            <div class="stu-meta">
                <?php if (!empty($resultlist['student_info'])): ?>
                Adm: <?php echo htmlspecialchars($resultlist['student_info']->admission_no ?? ''); ?>
                &nbsp;|&nbsp;
                <?php echo htmlspecialchars($resultlist['student_info']->class ?? ''); ?>
                <?php echo htmlspecialchars($resultlist['student_info']->section ?? ''); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="stu-pct">
            <div class="stu-pct-val"><?php echo is_null($pct) ? '—' : $pct.'%'; ?></div>
            <div class="stu-pct-lbl"><?php echo $total_present; ?> of <?php echo $total_held; ?> periods</div>
        </div>
    </div>

    <!-- Summary cards -->
    <div class="stu-summary">
        <div class="stu-sum-card">
            <div class="stu-sum-val" style="color:#5b73e8;"><?php echo $days_with_class; ?></div>
            <div class="stu-sum-lbl">Days with Class</div>
        </div>
        <div class="stu-sum-card">
            <div class="stu-sum-val" style="color:#7f8c8d;"><?php echo $total_held; ?></div>
            <div class="stu-sum-lbl">Total Periods</div>
        </div>
        <div class="stu-sum-card">
            <div class="stu-sum-val" style="color:#27ae60;"><?php echo $total_present; ?></div>
            <div class="stu-sum-lbl">Present</div>
        </div>
        <div class="stu-sum-card">
            <div class="stu-sum-val" style="color:#e74c3c;"><?php echo $total_absent; ?></div>
            <div class="stu-sum-lbl">Absent</div>
        </div>
        <div class="stu-sum-card">
            <div class="stu-sum-val" style="color:#bbb;"><?php echo $total_na; ?></div>
            <div class="stu-sum-lbl">Not Marked</div>
        </div>
        <div class="stu-sum-card">
            <div class="stu-sum-val" style="color:<?php echo $pct_color; ?>;"><?php echo is_null($pct) ? '—' : $pct.'%'; ?></div>
            <div class="stu-sum-lbl">Attendance %</div>
        </div>
    </div>

    <?php
    // Build calendar heatmap data
    // Collect per-day stats
    $day_stats = []; // date => ['present'=>n,'total'=>n]
    foreach ($resultlist['students_attendances'] as $day_data) {
        if (empty($day_data['subjects'])) continue;
        $date_str = $day_data['date']; // formatted date — convert to Y-m-d for sorting
        $dp = 0; $dt = 0;
        foreach ($day_data['subjects'] as $idx => $subj) {
            $c = $idx + 1;
            $aid = isset($day_data['attendances']) ? ($day_data['attendances']->{"attendence_type_id_".$c} ?? '') : '';
            $dt++;
            if ($aid == 1) $dp++;
        }
        $day_stats[$date_str] = ['present' => $dp, 'total' => $dt];
    }
    ?>

    <div class="row">
        <div class="col-md-8">
            <!-- Day-by-day detail table -->
            <div class="rpt-filter-box" style="padding:16px;">
                <div style="font-size:12px;font-weight:700;color:#7f8c8d;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">
                    <i class="fa fa-list" style="color:#5b73e8;"></i> Period-by-Period Details
                </div>
                <?php foreach ($resultlist['students_attendances'] as $day_data):
                    if (empty($day_data['subjects'])) continue;
                    $day_p = 0; $day_t = count($day_data['subjects']);
                    foreach ($day_data['subjects'] as $idx => $s) {
                        $c = $idx + 1;
                        $aid = isset($day_data['attendances']) ? ($day_data['attendances']->{"attendence_type_id_".$c} ?? '') : '';
                        if ($aid == 1) $day_p++;
                    }
                    $day_pct = $day_t > 0 ? round($day_p * 100 / $day_t) : 0;
                    $day_color = $day_p === $day_t ? '#27ae60' : ($day_p > 0 ? '#f39c12' : '#e74c3c');
                ?>
                <div style="margin-bottom:10px;background:#f8f9fb;border-radius:10px;padding:10px 14px;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                        <span class="day-date"><?php echo htmlspecialchars($day_data['date']); ?></span>
                        <span class="day-dayname"><?php echo htmlspecialchars($this->lang->line(strtolower($day_data['day'])) ?: $day_data['day']); ?></span>
                        <span style="margin-left:auto;font-size:12px;font-weight:700;color:<?php echo $day_color; ?>;"><?php echo $day_p; ?>/<?php echo $day_t; ?> periods</span>
                    </div>
                    <?php foreach ($day_data['subjects'] as $idx => $subj):
                        $c = $idx + 1;
                        $att_id = isset($day_data['attendances']) ? ($day_data['attendances']->{"attendence_type_id_".$c} ?? '') : '';
                        $key = _att_type_key($attendencetypeslist ?? [], $att_id);
                        if ($att_id === '') { $pill_cls = 'pill-NA'; $pill_txt = 'N/A'; }
                        else { $pill_cls = 'pill-'.strtoupper($key ?: 'NA'); $pill_txt = $key ?: 'N/A'; }
                    ?>
                    <div class="period-row">
                        <span class="period-subj"><?php echo htmlspecialchars($subj->name); ?><?php if ($subj->code): ?> <small style="color:#bbb;">(<?php echo htmlspecialchars($subj->code); ?>)</small><?php endif; ?></span>
                        <span class="period-time"><i class="fa fa-clock-o" style="color:#bbb;margin-right:3px;"></i><?php echo htmlspecialchars($subj->time_from); ?> – <?php echo htmlspecialchars($subj->time_to); ?></span>
                        <span class="att-pill <?php echo $pill_cls; ?>"><?php echo htmlspecialchars(strtoupper($pill_txt)); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-md-4">
            <!-- Mini calendar heatmap -->
            <div class="cal-wrap">
                <div class="cal-title"><i class="fa fa-calendar" style="color:#5b73e8;"></i> Month at a Glance</div>
                <?php
                // Build a full-month calendar for the selected month
                // We need the month start/end — derive from the data
                if (!empty($resultlist['students_attendances'])) {
                    // Get first date string to determine year-month
                    // Date is in formatted form — use sch_setting date_format to parse
                    // Fallback: current month
                }
                // Use session month data from POST
                $sel_month = set_value('month');
                if ($sel_month && !empty($monthlist[$sel_month])) {
                    $cal_start = date('Y-m-01', strtotime($sch_setting->session . '-' . sprintf('%02d', $sel_month) . '-01'));
                    // Handle academic year crossing: if month < start_month, year+1
                    $year_parts = explode('-', $sch_setting->session);
                    $cal_year   = (int)($sel_month >= $sch_setting->start_month ? $year_parts[0] : $year_parts[1]);
                    $cal_start  = $cal_year . '-' . sprintf('%02d', $sel_month) . '-01';
                } else {
                    $cal_start = date('Y-m-01');
                }
                $cal_end    = date('Y-m-t', strtotime($cal_start));
                $first_dow  = (int)date('w', strtotime($cal_start)); // 0=Sun
                $total_days = (int)date('t', strtotime($cal_start));

                // Map our day_stats (formatted date) to Y-m-d for lookup
                // Since we don't know exact date format, mark days that have data
                $cal_data = []; // day_num => stats
                foreach ($resultlist['students_attendances'] as $dd) {
                    if (empty($dd['subjects'])) continue;
                    // Try to extract day number from the formatted date
                    // The date field is already formatted by customlib->dateformat
                    // Let's use the raw date from the model if available
                }
                // Better: iterate from cal_start to cal_end and match against sch date format
                for ($d = 1; $d <= $total_days; $d++) {
                    $ymd = $cal_year . '-' . sprintf('%02d', $sel_month) . '-' . sprintf('%02d', $d);
                    $formatted = $this->customlib->dateformat($ymd);
                    if (isset($day_stats[$formatted])) {
                        $cal_data[$d] = $day_stats[$formatted];
                    }
                }
                ?>
                <div class="cal-grid">
                    <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $hdr): ?>
                    <div class="cal-day-hdr"><?php echo $hdr; ?></div>
                    <?php endforeach; ?>
                    <?php for ($blank = 0; $blank < $first_dow; $blank++): ?>
                    <div class="cal-day cal-empty"></div>
                    <?php endfor; ?>
                    <?php for ($d = 1; $d <= $total_days; $d++):
                        if (isset($cal_data[$d])) {
                            $s = $cal_data[$d];
                            $dp = $s['present']; $dt = $s['total'];
                            if ($dp === $dt) $dc = 'cal-present';
                            elseif ($dp === 0) $dc = 'cal-absent';
                            else              $dc = 'cal-mixed';
                            $dpct = $dt > 0 ? round($dp*100/$dt).'%' : '';
                        } else {
                            $dc = 'cal-noclass'; $dpct = '';
                        }
                    ?>
                    <div class="cal-day <?php echo $dc; ?>" title="<?php echo $d.' '.($dpct?:'No class'); ?>">
                        <span class="day-num"><?php echo $d; ?></span>
                        <?php if ($dpct): ?><span class="day-pct"><?php echo $dpct; ?></span><?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
                <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;font-size:10px;">
                    <span class="cal-day cal-present" style="padding:2px 8px;border-radius:5px;">All Present</span>
                    <span class="cal-day cal-mixed"   style="padding:2px 8px;border-radius:5px;">Partial</span>
                    <span class="cal-day cal-absent"  style="padding:2px 8px;border-radius:5px;">All Absent</span>
                    <span class="cal-day cal-noclass" style="padding:2px 8px;border-radius:5px;">No Class</span>
                </div>
            </div>

            <!-- Attendance breakdown donut -->
            <div class="rpt-filter-box" style="padding:16px;text-align:center;">
                <div style="font-size:12px;font-weight:700;color:#7f8c8d;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">
                    <i class="fa fa-pie-chart" style="color:#5b73e8;"></i> Breakdown
                </div>
                <canvas id="breakdownChart" style="max-width:200px;margin:0 auto;"></canvas>
            </div>
        </div>
    </div>

    <?php endif; // empty resultlist
    endif; // isset resultlist ?>

</section>
</div>

<script>
$(document).ready(function() {
    var cls  = '<?php echo set_value('class_id'); ?>';
    var sec  = '<?php echo set_value('section_id'); ?>';
    var std  = '<?php echo set_value('student_id'); ?>';
    var subj = '<?php echo set_value('subject_id'); ?>';
    var dept = '<?php echo set_value('department_id'); ?>';

    if (cls) { loadSections(cls, sec, std, subj); }

    $('#department_id').on('change', function() {
        $('#class_id').val(''); reset();
    });
    $('#class_id').on('change', function() { reset(); loadSections($(this).val(),'','',''); });
    $('#section_id').on('change', function() {
        $('#student_id').html('<option value="">Select</option>');
        var cid = $('#class_id').val(), sid = $(this).val();
        loadStudents(cid, sid, '');
        loadSubjects(cid, sid, '');
    });

    function reset() {
        $('#section_id').html('<option value="">Select</option>');
        $('#student_id').html('<option value="">Select</option>');
        $('#subject_id').html('<option value="">All</option>');
    }

    function loadSections(cid, sel, stdSel, subjSel) {
        if (!cid) return;
        $.getJSON(baseurl+'sections/getByClass', {class_id:cid, department_id:$('#department_id').val()}, function(data) {
            var h = '<option value="">Select</option>';
            $.each(data, function(i,o){ h += '<option value="'+o.section_id+'"'+(sel==o.section_id?' selected':'')+'>'+o.section+'</option>'; });
            $('#section_id').html(h);
            if (sel) { loadStudents(cid, sel, stdSel); loadSubjects(cid, sel, subjSel); }
        });
    }
    function loadStudents(cid, sid, sel) {
        if (!cid||!sid) return;
        $.getJSON(baseurl+'student/getByClassAndSection', {class_id:cid,section_id:sid,department_id:$('#department_id').val()}, function(data) {
            var h = '<option value="">Select</option>';
            $.each(data, function(i,o){ h += '<option value="'+o.id+'"'+(sel==o.id?' selected':'')+'>'+o.full_name+' ('+o.admission_no+')</option>'; });
            $('#student_id').html(h);
        });
    }
    function loadSubjects(cid, sid, sel) {
        if (!cid||!sid) return;
        $.post(baseurl+'admin/subjectgroup/getAllSubjectByClassandSection', {class_id:cid,section_id:sid}, function(data) {
            var h = '<option value="">All</option>';
            $.each(data, function(i,o){ h += '<option value="'+o.subject_id+'"'+(sel==o.subject_id?' selected':'')+'>'+o.subject_name+(o.subject_code?' ('+o.subject_code+')':' ')+'</option>'; });
            $('#subject_id').html(h);
        }, 'json');
    }

    <?php if (isset($resultlist) && !empty($resultlist) && $total_held > 0): ?>
    // Donut chart
    var ctx = document.getElementById('breakdownChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Not Marked'],
                datasets: [{ data: [<?php echo $total_present; ?>, <?php echo $total_absent; ?>, <?php echo $total_na; ?>],
                    backgroundColor: ['#27ae60','#e74c3c','#bdc3c7'], borderWidth: 0 }]
            },
            options: { responsive:true, cutout:'65%', plugins:{ legend:{ position:'bottom', labels:{ font:{size:11} } } } }
        });
    }
    <?php endif; ?>
});
</script>
