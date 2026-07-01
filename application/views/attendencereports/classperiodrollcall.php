<!-- Flatpickr for date picker -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<style>
@media print {
    .no-print, .no-print * { display:none !important; }
    .content-wrapper { margin:0!important; padding:0!important; }
    .content-header { display:none!important; }
    .main-sidebar, .main-header { display:none!important; }
    body { font-size:11pt; }
    .print-box { border:none!important; box-shadow:none!important; }
}
.rfb { background:#fff; border-radius:12px; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,.06); padding:18px 20px 12px; margin-bottom:18px; }
.rfb .form-group label { font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; margin-bottom:4px; display:block; }
.rfb .form-control { border-radius:8px; border-color:#e5e7eb; font-size:13px; height:36px; }
.rpt-search-btn { background:linear-gradient(135deg,#6366f1,#8b5cf6); border:none; border-radius:8px; color:#fff; padding:7px 20px; font-size:13px; font-weight:600; }
/* Period banner */
.period-banner { background:linear-gradient(135deg,#0f172a,#1e293b); color:#fff; border-radius:12px; padding:16px 22px; margin-bottom:16px; display:flex; align-items:center; gap:18px; flex-wrap:wrap; }
.period-banner-icon { width:48px; height:48px; border-radius:12px; background:rgba(255,255,255,.1); display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
.period-banner-subj { font-size:18px; font-weight:800; }
.period-banner-meta { font-size:12px; opacity:.7; margin-top:3px; }
.period-banner-stats { margin-left:auto; display:flex; gap:16px; }
.period-stat { text-align:center; }
.period-stat-val { font-size:24px; font-weight:800; }
.period-stat-lbl { font-size:10px; opacity:.7; }
.period-banner-actions { display:flex; gap:8px; }
.pba-btn { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:7px; font-size:11px; font-weight:700; border:1.5px solid rgba(255,255,255,.4); background:rgba(255,255,255,.1); color:#fff; cursor:pointer; text-decoration:none; }
.pba-btn:hover { background:rgba(255,255,255,.2); text-decoration:none; color:#fff; }
/* Roll call table */
.rc-table { width:100%; border-collapse:collapse; font-size:13px; }
.rc-table th { background:#1e293b; color:#fff; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:.5px; padding:10px 14px; }
.rc-table td { padding:9px 14px; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
.rc-table tr:hover td { background:#f8f9fb; }
.rc-table .att-P { background:#d1fae5; color:#065f46; border-radius:7px; padding:3px 12px; font-weight:800; font-size:12px; display:inline-block; }
.rc-table .att-A { background:#fee2e2; color:#991b1b; border-radius:7px; padding:3px 12px; font-weight:800; font-size:12px; display:inline-block; }
.rc-table .att-L { background:#fef3c7; color:#78350f; border-radius:7px; padding:3px 12px; font-weight:800; font-size:12px; display:inline-block; }
.rc-table .att-H { background:#dbeafe; color:#1e40af; border-radius:7px; padding:3px 12px; font-weight:800; font-size:12px; display:inline-block; }
.rc-table .att-NA { background:#f3f4f6; color:#6b7280; border-radius:7px; padding:3px 12px; font-weight:700; font-size:12px; display:inline-block; }
.rc-summary { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
.rc-sum { flex:1; min-width:100px; background:#fff; border-radius:10px; border:1px solid #e5e7eb; padding:10px 14px; text-align:center; }
.rc-sum-val { font-size:22px; font-weight:800; }
.rc-sum-lbl { font-size:10px; color:#6b7280; font-weight:600; }
/* Print styles */
.print-header { display:none; }
@media print {
    .print-header { display:block!important; text-align:center; margin-bottom:12px; border-bottom:2px solid #000; padding-bottom:8px; }
    .print-header h2 { margin:0; font-size:16pt; }
    .print-header p { margin:3px 0; font-size:10pt; color:#333; }
    .rc-table th { background:#333!important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .att-P { background:#d1fae5!important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .att-A { background:#fee2e2!important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
}
</style>

<div class="content-wrapper">
<section class="content-header">
  <h1 style="font-size:20px;font-weight:700;color:#111827;">
    <i class="fa fa-list-alt" style="color:#6366f1;margin-right:8px;"></i>
    Period Attendance Roll Call
    <small style="font-size:13px;font-weight:400;color:#6b7280;margin-left:6px;">One period · All students</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
    <li><a href="<?php echo site_url('admin/attendancedashboard/index'); ?>">Attendance Dashboard</a></li>
    <li class="active">Period Roll Call</li>
  </ol>
</section>

<section class="content" style="padding:10px 15px 40px;">
  <?php $this->load->view('attendencereports/_attendance'); ?>

  <!-- Filter -->
  <div class="rfb no-print">
    <form method="post" action="<?php echo site_url('attendencereports/classperiodrollcall'); ?>" id="roll-form">
      <?php echo $this->customlib->getCSRF(); ?>
      <div class="row">
        <?php if ($sch_setting->institution_type == 'college'): ?>
        <div class="col-md-2">
          <div class="form-group">
            <label>Department</label>
            <select id="department_id" name="department_id" class="form-control">
              <option value="">All</option>
              <?php foreach ($department_list as $d): ?>
              <option value="<?php echo $d['id']; ?>" <?php if ($this->input->post('department_id')==$d['id']) echo 'selected'; ?>><?php echo htmlspecialchars($d['department_name']); ?></option>
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
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>Section <small class="req">*</small></label>
            <select id="section_id" name="section_id" class="form-control"><option value="">Select Section</option></select>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>Date <small class="req">*</small></label>
            <div style="position:relative;">
              <input type="text" name="date" id="pick_date" class="form-control" autocomplete="off"
                     value="<?php echo htmlspecialchars($this->input->post('date')); ?>" placeholder="Select date" readonly>
              <i class="fa fa-calendar" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);color:#6366f1;pointer-events:none;"></i>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>Period / Subject <small class="req">*</small></label>
            <select id="subject_timetable_id" name="subject_timetable_id" class="form-control">
              <option value="">Pick date + class first</option>
              <?php if (!empty($periods)): foreach ($periods as $p): ?>
              <option value="<?php echo $p['id']; ?>" <?php if ($this->input->post('subject_timetable_id')==$p['id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($p['subject_name'].' ('.$p['time_from'].')'); ?>
              </option>
              <?php endforeach; endif; ?>
            </select>
          </div>
        </div>
        <div class="col-md-2" style="padding-top:22px;">
          <button type="submit" class="btn rpt-search-btn btn-block"><i class="fa fa-search"></i> Load Roll</button>
        </div>
      </div>
    </form>
  </div>

  <?php if ($result !== null): ?>

  <!-- Print header (visible only when printing) -->
  <div class="print-header">
    <h2><?php echo htmlspecialchars($sch_setting->name); ?></h2>
    <?php if ($selected_period): ?>
    <p><strong><?php echo htmlspecialchars($selected_period['subject_name']); ?></strong>
    (<?php echo htmlspecialchars($selected_period['subject_code']); ?>)
    &nbsp;&bull;&nbsp; <?php echo htmlspecialchars($selected_period['time_from']); ?> – <?php echo htmlspecialchars($selected_period['time_to']); ?>
    &nbsp;&bull;&nbsp; <?php echo htmlspecialchars($date_formatted); ?></p>
    <p>Teacher: <?php echo htmlspecialchars($selected_period['teacher_name']); ?></p>
    <?php endif; ?>
  </div>

  <?php if (!empty($result)):
    // Compute stats
    $rc_present = 0; $rc_absent = 0; $rc_late = 0; $rc_other = 0; $rc_na = 0;
    foreach ($result as $r) {
        $t = $r['attendence_type_id'] ?? '';
        if (!$t || $t === '0') { $rc_na++; continue; }
        // Look up key
        $key = '';
        foreach ($attendencetypeslist as $at) { if ($at['id'] == $t) { $key = strip_tags($at['key_value']); break; } }
        $k = strtoupper($key);
        if ($k === 'P') $rc_present++;
        elseif ($k === 'A') $rc_absent++;
        elseif ($k === 'L') $rc_late++;
        else $rc_other++;
    }
    $total_students = count($result);
  ?>

  <!-- Period banner -->
  <?php if ($selected_period): ?>
  <div class="period-banner no-print">
    <div class="period-banner-icon"><i class="fa fa-book"></i></div>
    <div>
      <div class="period-banner-subj"><?php echo htmlspecialchars($selected_period['subject_name']); ?>
        <span style="font-size:13px;opacity:.6;font-weight:400;"> (<?php echo htmlspecialchars($selected_period['subject_code']); ?>)</span>
      </div>
      <div class="period-banner-meta">
        <i class="fa fa-clock-o"></i> <?php echo htmlspecialchars($selected_period['time_from']); ?> – <?php echo htmlspecialchars($selected_period['time_to']); ?>
        &nbsp;&bull;&nbsp; <?php echo htmlspecialchars($date_formatted); ?>
        &nbsp;&bull;&nbsp; <i class="fa fa-user"></i> <?php echo htmlspecialchars($selected_period['teacher_name']); ?>
      </div>
    </div>
    <div class="period-banner-stats">
      <div class="period-stat"><div class="period-stat-val" style="color:#4ade80;"><?php echo $rc_present; ?></div><div class="period-stat-lbl">Present</div></div>
      <div class="period-stat"><div class="period-stat-val" style="color:#f87171;"><?php echo $rc_absent; ?></div><div class="period-stat-lbl">Absent</div></div>
      <div class="period-stat"><div class="period-stat-val" style="color:#fbbf24;"><?php echo $rc_late; ?></div><div class="period-stat-lbl">Late</div></div>
      <div class="period-stat"><div class="period-stat-val" style="color:#94a3b8;"><?php echo $rc_na; ?></div><div class="period-stat-lbl">N/A</div></div>
      <div class="period-stat"><div class="period-stat-val"><?php echo $total_students; ?></div><div class="period-stat-lbl">Total</div></div>
    </div>
    <div class="period-banner-actions">
      <button onclick="window.print()" class="pba-btn"><i class="fa fa-print"></i> Print</button>
      <button onclick="exportRollCSV()" class="pba-btn"><i class="fa fa-file-excel-o"></i> CSV</button>
    </div>
  </div>
  <?php endif; ?>

  <!-- Roll call table -->
  <div class="print-box" style="background:#fff;border-radius:12px;border:1px solid #e5e7eb;box-shadow:0 1px 6px rgba(0,0,0,.06);overflow:hidden;">
    <table class="rc-table">
      <thead>
        <tr>
          <th style="width:40px;">#</th>
          <th>Student Name</th>
          <th style="width:100px;">Roll No</th>
          <th style="width:130px;">Adm. No</th>
          <th style="width:110px;text-align:center;">Status</th>
          <th>Remark</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($result as $i => $r):
          $t = $r['attendence_type_id'] ?? '';
          $key = '';
          $type_name = 'N/A';
          if ($t && $t !== '0') {
              foreach ($attendencetypeslist as $at) {
                  if ($at['id'] == $t) { $key = strtoupper(strip_tags($at['key_value'])); $type_name = $at['type']; break; }
              }
          }
          $pill_cls = $key ? 'att-'.$key : 'att-NA';
          $pill_txt = $key ?: 'N/A';
          $full_name = trim($r['firstname'].' '.($r['middlename'] ? $r['middlename'].' ' : '').$r['lastname']);
        ?>
        <tr>
          <td style="color:#9ca3af;"><?php echo $i+1; ?></td>
          <td><strong><?php echo htmlspecialchars($full_name); ?></strong></td>
          <?php
          $rc_adm  = $r['admission_no'] ?? '';
          $rc_roll = $r['roll_no'] ?? '';
          $rc_show_roll = ($rc_roll && $rc_roll !== $rc_adm);
          ?>
          <td translate="no" style="-webkit-text-size-adjust:none;"><?php echo $rc_show_roll ? htmlspecialchars($rc_roll) : htmlspecialchars($rc_adm); ?></td>
          <td style="font-family:monospace;font-size:12px;" translate="no"><?php echo htmlspecialchars($rc_adm); ?></td>
          <td style="text-align:center;"><span class="<?php echo $pill_cls; ?>"><?php echo $pill_txt; ?></span></td>
          <td style="font-size:12px;color:#6b7280;"><?php echo htmlspecialchars($r['remark'] ?? ''); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php else: ?>
  <div style="background:#fff;border-radius:12px;padding:40px;text-align:center;color:#6b7280;border:1px solid #e5e7eb;">
    <i class="fa fa-users" style="font-size:32px;display:block;margin-bottom:10px;opacity:.3;"></i>
    No students found for the selected criteria.
  </div>
  <?php endif; ?>
  <?php endif; ?>
</section>
</div>

<script>
var SCHOOL = '<?php echo addslashes(htmlspecialchars($sch_setting->name ?? '')); ?>';

// Flatpickr date picker
flatpickr('#pick_date', { dateFormat:'Y-m-d', altInput:true, altFormat:'D, d M Y', maxDate:'today', allowInput:false });

$(window).on('load', function() {
    var savedCls = '<?php echo addslashes($this->input->post('class_id') ?: ''); ?>';
    var savedSec = '<?php echo addslashes($this->input->post('section_id') ?: ''); ?>';
    var savedStt = '<?php echo addslashes($this->input->post('subject_timetable_id') ?: ''); ?>';
    var savedDt  = '<?php echo addslashes($this->input->post('date') ?: ''); ?>';
    var allClasses = [];

    // ── Select2 ─────────────────────────────────────────────────
    var s2 = {width:'100%', allowClear:true};
    $('#department_id').select2($.extend({},s2,{placeholder:'— All Departments (optional) —'}));
    $('#class_id').select2($.extend({},s2,{placeholder:'— Select Class —'}));
    $('#section_id').select2($.extend({},s2,{placeholder:'— Select Section —'}));
    $('#subject_timetable_id').select2($.extend({},s2,{placeholder:'— Select Period —'}));

    // ── Load all classes on page load ────────────────────────────
    $.getJSON(baseurl+'attendencereports/getAllAcademicClasses', function(data) {
        allClasses = data;
        renderClasses(allClasses, savedCls, function() {
            if (savedCls) {
                loadSections(savedCls, savedSec, function() {
                    if (savedSec && savedDt) loadPeriods();
                });
            }
        });
    });

    // ── Department → filter classes (client-side) ────────────────
    $('#department_id').on('select2:select select2:unselect select2:clear change', function() {
        var deptId = $(this).val();
        var filtered = deptId ? allClasses.filter(function(c){ return String(c.department_id) === String(deptId); }) : allClasses;
        renderClasses(filtered, '', null);
        resetDrop('#section_id','— Select Section —');
        resetDrop('#subject_timetable_id','— Select Period —');
    });

    // ── Class → load sections ────────────────────────────────────
    $(document).on('change', '#class_id', function() {
        var cid=$(this).val();
        resetDrop('#section_id','— Select Section —');
        resetDrop('#subject_timetable_id','— Select Period —');
        if (cid) loadSections(cid, '', null);
    });
    $('#class_id').on('select2:select', function() {
        var cid=$(this).val();
        resetDrop('#section_id','— Select Section —');
        resetDrop('#subject_timetable_id','— Select Period —');
        if (cid) loadSections(cid, '', null);
    });

    // ── Section or Date change → load periods ────────────────────
    $(document).on('change','#section_id', loadPeriods);
    $('#section_id').on('select2:select', loadPeriods);
    $('#pick_date').on('change', loadPeriods);

    // ── Helpers ──────────────────────────────────────────────────
    function renderClasses(classes, sel, callback) {
        var h = '<option value="">— Select Class —</option>';
        $.each(classes, function(i,c) {
            h += '<option value="'+c.id+'" data-dept="'+c.department_id+'"'+(sel && sel==c.id?' selected':'')+'>'+esc(c['class'])+'</option>';
        });
        $('#class_id').html(h).trigger('change.select2');
        if (callback) callback();
    }
    function loadSections(cid, sel, callback) {
        if (!cid) return;
        $.getJSON(baseurl+'sections/getByClass',{class_id:cid,department_id:$('#department_id').val()},function(data){
            var h='<option value="">— Select Section —</option>';
            $.each(data,function(i,o){h+='<option value="'+o.section_id+'"'+(sel && sel==o.section_id?' selected':'')+'>'+esc(o.section)+'</option>';});
            $('#section_id').html(h).trigger('change.select2');
            if (callback) callback();
        });
    }
    function loadPeriods() {
        var cid=$('#class_id').val(), sid=$('#section_id').val(), dt=$('#pick_date').val();
        if (!cid || !sid || !dt) return;
        $.getJSON(baseurl+'attendencereports/getPeriodsForDay',{class_id:cid,section_id:sid,date:dt},function(data){
            var h='<option value="">— Select Period —</option>';
            $.each(data,function(i,o){h+='<option value="'+o.id+'"'+(savedStt && savedStt==o.id?' selected':'')+'>'+esc(o.subject_name)+' ('+esc(o.time_from)+')</option>';});
            $('#subject_timetable_id').html(h).trigger('change.select2');
        });
    }
    function resetDrop(sel,ph){ $(sel).html('<option value="">'+ph+'</option>').trigger('change.select2'); }
    function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
});

function exportRollCSV() {
    var rows = [['#','Student Name','Roll No','Adm No','Status','Remark']];
    document.querySelectorAll('.rc-table tbody tr').forEach(function(tr, i) {
        var cells = tr.querySelectorAll('td');
        rows.push([i+1, cells[1]?.textContent.trim(), cells[2]?.textContent.trim(), cells[3]?.textContent.trim(), cells[4]?.textContent.trim(), cells[5]?.textContent.trim()]);
    });
    var csv = rows.map(function(r){return r.map(function(c){return'"'+String(c||'').replace(/"/g,'""')+'"';}).join(',');}).join('\r\n');
    var a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob(['﻿'+csv],{type:'text/csv;charset=utf-8;'}));
    a.download = 'period_rollcall.csv'; a.click();
}
</script>
