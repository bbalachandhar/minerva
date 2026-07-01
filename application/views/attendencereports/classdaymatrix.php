<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<style>
@media print {
    .no-print, .no-print * { display:none !important; }
    .content-wrapper { margin:0!important; }
    .content-header, .main-sidebar, .main-header { display:none!important; }
    .mat-wrap { overflow:visible!important; }
    .cdm-table { font-size:9pt; }
    .cdm-table th { background:#333!important; color:#fff!important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .att-P { background:#d1fae5!important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .att-A { background:#fee2e2!important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .att-L { background:#fef3c7!important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .att-H { background:#dbeafe!important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
}
.rfb { background:#fff; border-radius:12px; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,.06); padding:18px 20px 12px; margin-bottom:18px; }
.rfb .form-group label { font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; margin-bottom:4px; display:block; }
.rfb .form-control { border-radius:8px; border-color:#e5e7eb; font-size:13px; height:36px; }
.rpt-search-btn { background:linear-gradient(135deg,#6366f1,#8b5cf6); border:none; border-radius:8px; color:#fff; padding:7px 20px; font-size:13px; font-weight:600; }
/* Day banner */
.day-banner { background:linear-gradient(135deg,#1e293b,#334155); color:#fff; border-radius:12px; padding:16px 22px; margin-bottom:16px; display:flex; align-items:center; gap:16px; flex-wrap:wrap; }
.day-banner-date { font-size:22px; font-weight:800; }
.day-banner-day  { font-size:14px; opacity:.7; }
.day-banner-stats { margin-left:auto; display:flex; gap:14px; }
.day-stat { text-align:center; }
.day-stat-val { font-size:22px; font-weight:800; }
.day-stat-lbl { font-size:10px; opacity:.7; }
.day-banner-actions { display:flex; gap:8px; }
.dba-btn { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:7px; font-size:11px; font-weight:700; border:1.5px solid rgba(255,255,255,.4); background:rgba(255,255,255,.1); color:#fff; cursor:pointer; text-decoration:none; }
.dba-btn:hover { background:rgba(255,255,255,.2); text-decoration:none; color:#fff; }
/* Matrix table */
.mat-wrap { overflow-x:auto; }
.cdm-table { border-collapse:separate; border-spacing:0; width:100%; font-size:12px; white-space:nowrap; }
.cdm-table th { background:#1e293b; color:#fff; font-size:11px; font-weight:700; padding:8px 10px; text-align:center; position:sticky; top:0; z-index:2; }
.cdm-table th.sticky-col { position:sticky; left:0; z-index:3; background:#0f172a; text-align:left; min-width:180px; }
.cdm-table th.period-th { min-width:90px; max-width:120px; white-space:normal; text-align:center; }
.cdm-table th.period-th small { display:block; font-weight:400; opacity:.7; font-size:9px; }
.cdm-table td { padding:7px 10px; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
.cdm-table td.sticky-col { position:sticky; left:0; z-index:1; background:#fff; border-right:2px solid #e5e7eb; }
.cdm-table tr:hover td { background:#f8f9fb; }
.cdm-table tr:hover td.sticky-col { background:#f8f9fb; }
.cdm-table .foot-row td { background:#f1f5f9; font-weight:700; font-size:11px; border-top:2px solid #e5e7eb; }
/* Attendance pills */
.att-pill { display:inline-block; border-radius:6px; padding:3px 10px; font-weight:800; font-size:11px; text-align:center; min-width:32px; }
.att-P    { background:#d1fae5; color:#065f46; }
.att-A    { background:#fee2e2; color:#991b1b; }
.att-L    { background:#fef3c7; color:#78350f; }
.att-H    { background:#dbeafe; color:#1e40af; }
.att-F    { background:#f3e8ff; color:#6b21a8; }
.att-NA   { background:#f3f4f6; color:#9ca3af; }
.att-CNF  { background:#fff3cd; color:#92400e; font-size:9px; }
/* Student info */
.std-name { font-size:12px; font-weight:700; color:#1f2937; }
.std-roll { font-size:10px; color:#9ca3af; }
/* Overall column */
.overall-pct { display:inline-block; border-radius:6px; padding:3px 8px; font-weight:800; font-size:11px; }
.pct-good { background:#d1fae5; color:#065f46; }
.pct-warn { background:#fef3c7; color:#78350f; }
.pct-low  { background:#fee2e2; color:#991b1b; }
.pct-na   { background:#f3f4f6; color:#9ca3af; }
/* Print header */
.print-header { display:none; }
@media print {
    .print-header { display:block!important; text-align:center; margin-bottom:12px; border-bottom:2px solid #000; padding-bottom:8px; }
    .print-header h2 { margin:0; font-size:15pt; }
    .print-header p { margin:2px 0; font-size:9pt; }
}
</style>

<div class="content-wrapper">
<section class="content-header">
  <h1 style="font-size:20px;font-weight:700;color:#111827;">
    <i class="fa fa-table" style="color:#6366f1;margin-right:8px;"></i>
    Class Day Attendance Matrix
    <small style="font-size:13px;font-weight:400;color:#6b7280;margin-left:6px;">All students × All periods for a date</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
    <li><a href="<?php echo site_url('admin/attendancedashboard/index'); ?>">Attendance Dashboard</a></li>
    <li class="active">Day Matrix</li>
  </ol>
</section>

<section class="content" style="padding:10px 15px 40px;">
  <?php $this->load->view('attendencereports/_attendance'); ?>

  <!-- Filter -->
  <div class="rfb no-print">
    <form method="post" action="<?php echo site_url('attendencereports/classdaymatrix'); ?>">
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
        <div class="col-md-3">
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
        <div class="col-md-3">
          <div class="form-group">
            <label>Date <small class="req">*</small></label>
            <div style="position:relative;">
              <input type="text" name="date" id="pick_date" class="form-control" autocomplete="off"
                     value="<?php echo htmlspecialchars($this->input->post('date')); ?>" placeholder="Select date" readonly>
              <i class="fa fa-calendar" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);color:#6366f1;pointer-events:none;"></i>
            </div>
          </div>
        </div>
        <div class="col-md-2" style="padding-top:22px;">
          <button type="submit" class="btn rpt-search-btn btn-block"><i class="fa fa-search"></i> Generate</button>
        </div>
      </div>
    </form>
  </div>

  <?php if ($matrix !== null): ?>

  <?php if (empty($matrix['periods'])): ?>
  <div style="background:#fff;border-radius:12px;padding:40px;text-align:center;color:#6b7280;border:1px solid #e5e7eb;">
    <i class="fa fa-calendar-times-o" style="font-size:32px;display:block;margin-bottom:10px;opacity:.3;"></i>
    No periods scheduled for this class on <?php echo htmlspecialchars($date_formatted); ?> (<?php echo $day_name; ?>).<br>
    <small>Check the timetable for this class-section.</small>
  </div>
  <?php elseif (empty($matrix['students'])): ?>
  <div style="background:#fff;border-radius:12px;padding:40px;text-align:center;color:#6b7280;border:1px solid #e5e7eb;">
    <i class="fa fa-users" style="font-size:32px;display:block;margin-bottom:10px;opacity:.3;"></i>
    No students enrolled in this class-section.
  </div>
  <?php else:
    $periods  = $matrix['periods'];
    $students = $matrix['students'];
    $att_map  = $matrix['att_map'];

    // Build key-value lookup from attendence types
    $type_key = [];
    foreach ($attendencetypeslist as $at) {
        $type_key[$at['id']] = strtoupper(strip_tags($at['key_value'] ?? ''));
    }

    // Summary counts across all students/periods
    $total_cells = 0; $total_marked = 0; $total_present = 0;
    foreach ($students as $s) {
        foreach ($periods as $p) {
            $total_cells++;
            $aid = $att_map[$s['student_session_id']][$p['id']] ?? null;
            if ($aid) { $total_marked++; if (($type_key[$aid] ?? '') === 'P') $total_present++; }
        }
    }

    // Check for time-slot conflicts in this day
    $time_freq = [];
    foreach ($periods as $p) { $slot = $p['time_from']; $time_freq[$slot] = ($time_freq[$slot] ?? 0) + 1; }
    $has_conflict = max($time_freq) > 1;
  ?>

  <!-- Print header -->
  <div class="print-header">
    <h2><?php echo htmlspecialchars($sch_setting->name); ?></h2>
    <p><strong>Class Day Attendance Matrix</strong> &bull; <?php echo htmlspecialchars($date_formatted); ?> (<?php echo $day_name; ?>)</p>
    <p>Generated: <?php echo date('d M Y H:i'); ?></p>
  </div>

  <!-- Day banner -->
  <div class="day-banner no-print">
    <div>
      <div class="day-banner-date"><?php echo htmlspecialchars($date_formatted); ?></div>
      <div class="day-banner-day"><?php echo $day_name; ?> &bull; <?php echo count($periods); ?> periods &bull; <?php echo count($students); ?> students</div>
    </div>
    <div class="day-banner-stats">
      <div class="day-stat"><div class="day-stat-val" style="color:#4ade80;"><?php echo $total_present; ?></div><div class="day-stat-lbl">Present</div></div>
      <div class="day-stat"><div class="day-stat-val" style="color:#f87171;"><?php echo $total_marked - $total_present; ?></div><div class="day-stat-lbl">Absent/Other</div></div>
      <div class="day-stat"><div class="day-stat-val" style="color:#94a3b8;"><?php echo $total_cells - $total_marked; ?></div><div class="day-stat-lbl">Not Marked</div></div>
      <div class="day-stat"><div class="day-stat-val"><?php echo $total_marked > 0 ? round($total_present * 100 / $total_marked) . '%' : '—'; ?></div><div class="day-stat-lbl">Present %</div></div>
    </div>
    <div class="day-banner-actions">
      <button onclick="window.print()" class="dba-btn"><i class="fa fa-print"></i> Print</button>
      <button onclick="exportMatrixCSV()" class="dba-btn"><i class="fa fa-file-excel-o"></i> CSV</button>
      <button onclick="exportMatrixPDF()" class="dba-btn"><i class="fa fa-file-pdf-o"></i> PDF</button>
    </div>
  </div>

  <?php if ($has_conflict): ?>
  <div style="background:#fff3cd;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:12px;color:#78350f;" class="no-print">
    <i class="fa fa-exclamation-triangle"></i> <strong>Timetable conflict:</strong> Some time slots have 2+ subjects scheduled simultaneously. Fix via Auto Timetable → Subject Load.
  </div>
  <?php endif; ?>

  <!-- Matrix table -->
  <div style="background:#fff;border-radius:12px;border:1px solid #e5e7eb;box-shadow:0 1px 6px rgba(0,0,0,.06);">
    <div class="mat-wrap">
      <table class="cdm-table" id="cdm-tbl">
        <thead>
          <tr>
            <th class="sticky-col" style="min-width:200px;">
              Student <span style="font-weight:400;opacity:.6;font-size:10px;">(Name / Roll)</span>
            </th>
            <?php $p_num = 1; foreach ($periods as $p): $slot_dup = ($time_freq[$p['time_from']] ?? 1) > 1; ?>
            <th class="period-th" <?php if ($slot_dup) echo 'style="background:#b45309;"'; ?>>
              P<?php echo $p_num++; ?>
              <small><?php echo htmlspecialchars($p['subject_code'] ?: $p['subject_name']); ?></small>
              <small><?php echo htmlspecialchars($p['time_from']); ?></small>
            </th>
            <?php endforeach; ?>
            <th style="min-width:80px;background:#1e3a5f;">Overall</th>
          </tr>
          <!-- Teacher row -->
          <tr style="background:#0f172a;">
            <th class="sticky-col" style="font-weight:400;font-size:10px;color:rgba(255,255,255,.5);">Teacher →</th>
            <?php foreach ($periods as $p): ?>
            <th style="font-size:9px;font-weight:400;color:rgba(255,255,255,.6);text-align:center;"><?php echo htmlspecialchars($p['teacher_name']); ?></th>
            <?php endforeach; ?>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $i => $s):
          $ssid = $s['student_session_id'];
          $sp = 0; $st = 0;
          foreach ($periods as $p) {
              $aid = $att_map[$ssid][$p['id']] ?? null;
              if ($aid) { $st++; if (($type_key[$aid] ?? '') === 'P') $sp++; }
          }
          $pct     = $st > 0 ? round($sp * 100 / $st) : null;
          $pct_cls = is_null($pct) ? 'pct-na' : ($pct >= 80 ? 'pct-good' : ($pct >= 60 ? 'pct-warn' : 'pct-low'));
        ?>
        <tr>
          <td class="sticky-col">
            <span class="std-name"><?php echo htmlspecialchars($this->customlib->getFullName($s['firstname'], $s['middlename'], $s['lastname'], $sch_setting->middlename, $sch_setting->lastname)); ?></span>
            <br>
            <?php
            // Suppress browser phone-number auto-detection with a zero-width space every 4 chars
            $adm = htmlspecialchars($s['admission_no']);
            $roll = htmlspecialchars($s['roll_no']);
            $show_roll = ($roll && $roll !== $adm); // only show roll if different from adm no
            ?>
            <span class="std-roll" style="-webkit-text-size-adjust:none;" translate="no">
              <?php if ($show_roll): ?>
              Roll: <?php echo $roll; ?> &bull;
              <?php endif; ?>
              Adm: <?php echo chunk_split(htmlspecialchars($s['admission_no']), 4, '<wbr>'); ?>
            </span>
          </td>
          <?php foreach ($periods as $p):
            $aid  = $att_map[$ssid][$p['id']] ?? null;
            $key  = $aid ? ($type_key[$aid] ?? 'NA') : 'NA';
            $pill = 'att-'.($key ?: 'NA');
            $slot_dup = ($time_freq[$p['time_from']] ?? 1) > 1;
          ?>
          <td style="text-align:center;">
            <span class="att-pill <?php echo $slot_dup ? 'att-CNF' : $pill; ?>">
              <?php echo $slot_dup ? '⚠' : htmlspecialchars($key ?: 'N/A'); ?>
            </span>
          </td>
          <?php endforeach; ?>
          <td style="text-align:center;">
            <span class="overall-pct <?php echo $pct_cls; ?>">
              <?php echo is_null($pct) ? '—' : ($sp.'/'.$st.' '.$pct.'%'); ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr class="foot-row">
            <td class="sticky-col" style="color:#6366f1;font-size:12px;">
              <i class="fa fa-bar-chart"></i> Period Summary
            </td>
            <?php foreach ($periods as $p):
              $pp = 0; $pm = 0;
              foreach ($students as $s) {
                  $aid = $att_map[$s['student_session_id']][$p['id']] ?? null;
                  if ($aid) { $pm++; if (($type_key[$aid] ?? '') === 'P') $pp++; }
              }
              $ppct = count($students) > 0 ? round($pp * 100 / count($students)) : 0;
              $pcls = $pm === 0 ? 'att-NA' : ($ppct >= 75 ? 'att-P' : ($ppct >= 50 ? 'att-L' : 'att-A'));
            ?>
            <td style="text-align:center;">
              <span class="att-pill <?php echo $pcls; ?>" style="font-size:10px;">
                <?php echo $pm > 0 ? $pp.'/'.count($students) : 'N/M'; ?>
              </span>
            </td>
            <?php endforeach; ?>
            <td style="text-align:center;font-size:11px;font-weight:700;">
              <?php echo $total_marked > 0 ? round($total_present * 100 / count($students) / count($periods)) . '%' : '—'; ?>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <?php endif; ?>
  <?php endif; ?>
</section>
</div>

<script>
var SCHOOL   = '<?php echo addslashes(htmlspecialchars($sch_setting->name ?? '')); ?>';
var RPT_DATE = '<?php echo isset($date_formatted) ? addslashes(htmlspecialchars($date_formatted)) : ''; ?>';
var RPT_DAY  = '<?php echo isset($day_name) ? addslashes($day_name) : ''; ?>';

// Flatpickr runs inline — no footer dependency
flatpickr('#pick_date', { dateFormat:'Y-m-d', altInput:true, altFormat:'D, d M Y', maxDate:'today', allowInput:false });

$(window).on('load', function() {
    var savedCls = '<?php echo addslashes($this->input->post('class_id') ?: ''); ?>';
    var savedSec = '<?php echo addslashes($this->input->post('section_id') ?: ''); ?>';
    var allClasses = []; // cache for client-side dept filtering

    // ── Apply Select2 ─────────────────────────────────────────────
    var s2 = {width:'100%', allowClear:true};
    $('#department_id').select2($.extend({},s2,{placeholder:'— All Departments (optional) —'}));
    $('#class_id').select2($.extend({},s2,{placeholder:'— Select Class —'}));
    $('#section_id').select2($.extend({},s2,{placeholder:'— Select Section —'}));

    // ── Load ALL classes once on page load ─────────────────────────
    $.getJSON(baseurl + 'attendencereports/getAllAcademicClasses', function(data) {
        allClasses = data;
        renderClasses(allClasses, savedCls, function() {
            if (savedCls) loadSections(savedCls, savedSec);
        });
    });

    // ── Department → filter class list (client-side, no extra AJAX) ─
    $('#department_id').on('select2:select select2:unselect select2:clear change', function() {
        var deptId = $(this).val();
        var filtered = deptId ? allClasses.filter(function(c){ return String(c.department_id) === String(deptId); }) : allClasses;
        renderClasses(filtered, '', null);
        resetDrop('#section_id', '— Select Section —');
    });

    // ── Class → load sections ──────────────────────────────────────
    $(document).on('change', '#class_id', function() {
        var cid = $(this).val();
        resetDrop('#section_id', '— Select Section —');
        if (cid) loadSections(cid, '');
    });
    $('#class_id').on('select2:select', function() {
        resetDrop('#section_id', '— Select Section —');
        var cid = $(this).val();
        if (cid) loadSections(cid, '');
    });

    function renderClasses(classes, sel, callback) {
        var h = '<option value="">— Select Class —</option>';
        $.each(classes, function(i,c) {
            h += '<option value="'+c.id+'" data-dept="'+c.department_id+'"' + (sel && sel==c.id?' selected':'') + '>'+esc(c['class'])+'</option>';
        });
        $('#class_id').html(h).trigger('change.select2');
        if (callback) callback();
    }
    function loadSections(cid, sel) {
        if (!cid) return;
        $.getJSON(baseurl+'sections/getByClass', {class_id:cid, department_id:$('#department_id').val()}, function(data) {
            var h = '<option value="">— Select Section —</option>';
            $.each(data, function(i,o) { h += '<option value="'+o.section_id+'"'+(sel && sel==o.section_id?' selected':'')+'>'+esc(o.section)+'</option>'; });
            $('#section_id').html(h).trigger('change.select2');
        });
    }
    function resetDrop(sel, ph) { $(sel).html('<option value="">'+ph+'</option>').trigger('change.select2'); }
    function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
});

function exportMatrixCSV() {
    var tbl = document.getElementById('cdm-tbl');
    if (!tbl) return;
    var rows = [];
    tbl.querySelectorAll('tr').forEach(function(tr) {
        var row = [];
        tr.querySelectorAll('th,td').forEach(function(c){ row.push(c.textContent.trim().replace(/\s+/g,' ')); });
        rows.push(row);
    });
    var csv = rows.map(function(r){return r.map(function(c){return '"'+String(c||'').replace(/"/g,'""')+'"';}).join(',');}).join('\r\n');
    var a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob(['﻿'+csv],{type:'text/csv;charset=utf-8;'}));
    a.download = 'day_matrix_'+RPT_DATE.replace(/[^0-9]/g,'_')+'.csv'; a.click();
}

function exportMatrixPDF() {
    var tbl = document.getElementById('cdm-tbl');
    if (!tbl) return;
    var w = window.open('','_blank','width=1100,height=800');
    w.document.write('<!DOCTYPE html><html><head><meta charset="utf-8"><title>Day Matrix</title>'+
    '<style>body{font-family:Arial,sans-serif;font-size:10pt;margin:16px;}h3{margin:0 0 4px;}p{margin:0 0 12px;color:#555;}'+
    'table{width:100%;border-collapse:collapse;font-size:9pt;}th,td{border:1px solid #ccc;padding:5px 7px;}'+
    'th{background:#1e293b;color:#fff;}tr:nth-child(even){background:#f8f9fb;}'+
    '@media print{button{display:none!important;}}</style>'+
    '</head><body><h3>'+SCHOOL+' — Class Day Attendance</h3>'+
    '<p>'+RPT_DATE+' ('+RPT_DAY+')</p>'+tbl.outerHTML+
    '<br><button onclick="window.print()" style="padding:8px 20px;background:#6366f1;color:#fff;border:none;border-radius:6px;cursor:pointer;">🖨 Print</button>'+
    '</body></html>');
    w.document.close();
}
</script>
