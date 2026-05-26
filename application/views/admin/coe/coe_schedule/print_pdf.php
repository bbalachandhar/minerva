<?php defined('BASEPATH') or exit('No direct script access allowed');
$school_name = $sch_setting->name    ?? 'Institution Name';
$school_addr = $sch_setting->address ?? '';
$school_phone = $sch_setting->phone  ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { box-sizing:border-box; }
body { font-family:Arial,Helvetica,sans-serif; font-size:10pt; color:#1a1a2e; margin:0; padding:0; }
.page-border { border:2px solid #1a237e; padding:12px; min-height:97%; }

/* Header */
.school-name { font-size:14pt; font-weight:bold; color:#1a237e; text-transform:uppercase; }
.school-addr { font-size:8.5pt; color:#555; margin-top:2px; }
.doc-title   { font-size:12pt; font-weight:bold; color:#b71c1c; letter-spacing:2px; margin-top:5px; text-transform:uppercase; }

/* Info table */
.info-table { width:100%; border-collapse:collapse; margin:10px 0; font-size:9.5pt; }
.info-table td { padding:4px 8px; border:1px solid #c5cae9; }
.info-table .lbl { background:#e8eaf6; color:#1a237e; font-weight:bold; width:22%; }

/* Schedule table */
.sch-table { width:100%; border-collapse:collapse; font-size:9.5pt; margin-top:12px; }
.sch-table thead th {
    background:#1a237e; color:#fff;
    border:1px solid #1a237e;
    padding:6px 8px; text-align:left;
}
.sch-table tbody td { border:1px solid #c5cae9; padding:5px 8px; vertical-align:middle; }
.sch-table tbody tr:nth-child(even) { background:#f5f5ff; }
.subject-code { font-weight:bold; color:#1a237e; }
.session-fn   { color:#1565c0; font-weight:bold; }
.session-an   { color:#6a1b9a; font-weight:bold; }
.hall-name    { font-size:8.5pt; color:#555; }
.no-data      { text-align:center; padding:20px; color:#888; font-style:italic; }

/* Footer */
.footer-sig { width:100%; border-collapse:collapse; margin-top:40px; }
.footer-sig td { text-align:center; font-size:9pt; border-top:1px solid #555; padding-top:5px; width:33%; }
.generated  { font-size:8pt; color:#888; text-align:right; margin-top:8px; }
</style>
</head>
<body>
<div class="page-border">

  <!-- HEADER -->
  <table style="width:100%;border-collapse:collapse;border-bottom:2px solid #1a237e;padding-bottom:8px;margin-bottom:10px;">
    <tr>
      <td style="width:75px;vertical-align:middle;text-align:center;">
        <?php if ($logo_path): ?>
          <img src="<?php echo $logo_path; ?>" style="width:65px;height:65px;object-fit:contain;">
        <?php endif; ?>
      </td>
      <td style="text-align:center;vertical-align:middle;padding:0 8px;">
        <div class="school-name"><?php echo htmlspecialchars($school_name); ?></div>
        <?php if ($school_addr): ?>
          <div class="school-addr"><?php echo htmlspecialchars($school_addr); ?></div>
        <?php endif; ?>
        <?php if ($school_phone): ?>
          <div class="school-addr">Ph: <?php echo htmlspecialchars($school_phone); ?></div>
        <?php endif; ?>
        <div class="doc-title">&#9632; Exam Time Table &#9632;</div>
      </td>
      <td style="width:100px;vertical-align:middle;text-align:right;font-size:8pt;color:#555;">
        <div>Date: <?php echo date('d M Y'); ?></div>
      </td>
    </tr>
  </table>

  <!-- EXAM INFO -->
  <table class="info-table">
    <tr>
      <td class="lbl">Exam Event</td>
      <td><?php echo htmlspecialchars($event->exam ?? '—'); ?></td>
      <td class="lbl">Class</td>
      <td><?php echo htmlspecialchars($event->class_name ?? '—'); ?></td>
    </tr>
    <tr>
      <td class="lbl">Exam Group</td>
      <td><?php echo htmlspecialchars($event->exam_group_name ?? '—'); ?></td>
      <td class="lbl">Exam Period</td>
      <td>
        <?php
        $df = $event->date_from ?? '';
        $dt = $event->date_to   ?? '';
        if ($df && $dt) {
            echo date('d M Y', strtotime($df)) . ' &mdash; ' . date('d M Y', strtotime($dt));
        } elseif ($df) {
            echo date('d M Y', strtotime($df));
        } else {
            echo '—';
        }
        ?>
      </td>
    </tr>
  </table>

  <!-- SCHEDULE TABLE -->
  <?php if (empty($schedule)): ?>
    <p class="no-data">No schedule entries found for this exam event.</p>
  <?php else: ?>
  <table class="sch-table">
    <thead>
      <tr>
        <th style="width:5%;">#</th>
        <th style="width:12%;">Subject Code</th>
        <th style="width:28%;">Subject Name</th>
        <th style="width:13%;">Exam Date</th>
        <th style="width:10%;">Start Time</th>
        <th style="width:10%;">End Time</th>
        <th style="width:8%;">Session</th>
        <th style="width:14%;">Hall</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; foreach ($schedule as $row): ?>
      <tr>
        <td><?php echo $i++; ?></td>
        <td><span class="subject-code"><?php echo htmlspecialchars($row->subject_code ?? ''); ?></span></td>
        <td><?php echo htmlspecialchars($row->subject_name ?? ''); ?></td>
        <td><?php echo $row->exam_date ? date('d M Y', strtotime($row->exam_date)) : '<span style="color:#999;">—</span>'; ?></td>
        <td><?php echo $row->start_time ? date('h:i A', strtotime($row->start_time)) : '—'; ?></td>
        <td><?php echo $row->end_time   ? date('h:i A', strtotime($row->end_time))   : '—'; ?></td>
        <td>
          <?php if ($row->session_slot === 'FN'): ?>
            <span class="session-fn">FN</span>
          <?php else: ?>
            <span class="session-an">AN</span>
          <?php endif; ?>
        </td>
        <td><span class="hall-name"><?php echo htmlspecialchars($row->hall_name ?? '—'); ?></span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <!-- FOOTER SIGNATURES -->
  <table class="footer-sig">
    <tr>
      <td>Prepared By</td>
      <td></td>
      <td>Controller of Examinations</td>
    </tr>
  </table>

  <div class="generated">Generated on <?php echo date('d M Y, h:i A'); ?></div>

</div>
</body>
</html>
