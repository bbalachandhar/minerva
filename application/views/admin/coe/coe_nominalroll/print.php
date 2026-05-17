<?php defined('BASEPATH') or exit('No direct script access allowed');
$school_name = $sch_setting->name ?? 'Institution Name';
$school_addr = $sch_setting->address ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { box-sizing:border-box; }
body { font-family:Arial,Helvetica,sans-serif;font-size:10pt;color:#1a1a2e;margin:0;padding:0; }
.page-border { border:2px solid #1a237e;padding:10px;min-height:97%; }
.header { border-bottom:2px solid #1a237e;padding-bottom:8px;margin-bottom:10px;display:table;width:100%; }
.header-logo   { display:table-cell;width:70px;vertical-align:middle; }
.header-center { display:table-cell;text-align:center;vertical-align:middle;padding:0 10px; }
.header-right  { display:table-cell;width:100px;vertical-align:middle;text-align:right;font-size:8pt;color:#555; }
.school-name { font-size:14pt;font-weight:bold;color:#1a237e;text-transform:uppercase; }
.school-addr { font-size:8pt;color:#555;margin-top:2px; }
.doc-title   { font-size:12pt;font-weight:bold;color:#b71c1c;letter-spacing:2px;margin-top:4px;text-transform:uppercase; }
.info-section { width:100%;border-collapse:collapse;margin-bottom:10px;font-size:9.5pt; }
.info-section td { padding:3px 8px;border:1px solid #c5cae9; }
.info-section .lbl { background:#e8eaf6;color:#1a237e;font-weight:bold;width:28%; }
.section-header { background:#1a237e;color:#fff;font-size:10pt;font-weight:bold;padding:5px 10px;margin-bottom:0; }
.roll-table { width:100%;border-collapse:collapse;font-size:9.5pt; }
.roll-table thead th { background:#e8eaf6;color:#1a237e;border:1px solid #c5cae9;padding:5px 7px;text-align:left; }
.roll-table tbody td { border:1px solid #c5cae9;padding:4px 7px; }
.roll-table tbody tr:nth-child(even) { background:#f8f9ff; }
.arrear { color:#b71c1c;font-weight:bold;font-size:8.5pt; }
.footer-sig { width:100%;margin-top:30px;border-collapse:collapse; }
.footer-sig td { text-align:center;font-size:9pt;border-top:1px solid #333;padding-top:4px;width:33%; }
.watermark { color:#b71c1c;font-size:8pt;font-weight:bold;letter-spacing:1px;text-align:center;padding:3px 0;background:#fce4ec;border:1px solid #f48fb1;border-radius:3px;margin-bottom:6px; }
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
        <?php if ($school_addr): ?><div class="school-addr"><?php echo htmlspecialchars($school_addr); ?></div><?php endif; ?>
        <div class="doc-title">&#9632; Nominal Roll &#9632;</div>
      </td>
      <td style="width:110px;vertical-align:middle;text-align:right;font-size:8pt;color:#555;">
        <?php if ($roll->is_final): ?>
          <div style="color:#2e7d32;font-weight:bold;">&#9745; FINALIZED</div>
        <?php else: ?>
          <div style="color:#e65100;font-weight:bold;">&#9744; DRAFT</div>
        <?php endif; ?>
        <div style="margin-top:4px;">Date: <?php echo date('d M Y'); ?></div>
      </td>
    </tr>
  </table>

  <?php if (!$roll->is_final): ?>
    <div class="watermark">DRAFT &mdash; NOT FOR OFFICIAL USE UNTIL FINALIZED</div>
  <?php endif; ?>

  <!-- INFO -->
  <table class="info-section">
    <tr>
      <td class="lbl">Exam / Event</td><td><?php echo htmlspecialchars($roll->exam_name ?? '—'); ?></td>
      <td class="lbl">Subject</td><td><?php echo htmlspecialchars($roll->subject_name ?? '—'); ?> (<?php echo htmlspecialchars($roll->subject_code ?? '—'); ?>)</td>
    </tr>
    <tr>
      <td class="lbl">Academic Session</td><td><?php echo htmlspecialchars($roll->session_name ?? '—'); ?></td>
      <td class="lbl">Exam Date</td><td><?php echo $roll->exam_date ? date('d M Y', strtotime($roll->exam_date)) : '—'; ?></td>
    </tr>
    <tr>
      <td class="lbl">Subject Type</td><td><?php echo ucfirst($roll->subject_type ?? '—'); ?></td>
      <td class="lbl">Total Students</td><td><strong><?php echo (int)$roll->total_students; ?></strong></td>
    </tr>
  </table>

  <!-- STUDENT TABLE -->
  <div class="section-header">Registered Students</div>
  <table class="roll-table">
    <thead>
      <tr>
        <th style="width:35px;">S.No</th>
        <th style="width:90px;">Reg. No</th>
        <th>Student Name</th>
        <th style="width:100px;">Programme</th>
        <th style="width:55px;">Category</th>
        <th style="width:50px;">Arrear</th>
        <th style="width:60px;">Signature</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($students)): ?>
        <tr><td colspan="7" style="text-align:center;color:#777;">No student data</td></tr>
      <?php else: ?>
        <?php foreach ($students as $i => $st): ?>
        <tr>
          <td><?php echo $i + 1; ?></td>
          <td><?php echo htmlspecialchars($st['register_no'] ?? '—'); ?></td>
          <td><strong><?php echo htmlspecialchars(($st['firstname'] ?? '') . ' ' . ($st['lastname'] ?? '')); ?></strong></td>
          <td><?php echo htmlspecialchars($st['class_name'] ?? '—'); ?></td>
          <td style="text-align:center;">
            <?php if ($st['cbcs_category'] ?? null): ?>
              <span style="background:#e3f2fd;color:#1565c0;border:1px solid #90caf9;border-radius:10px;padding:1px 5px;font-size:8pt;"><?php echo strtoupper($st['cbcs_category']); ?></span>
            <?php else: ?>—<?php endif; ?>
          </td>
          <td style="text-align:center;">
            <?php if ($st['is_arrear'] ?? false): ?>
              <span class="arrear">A</span>
            <?php else: ?>
              <span style="color:#2e7d32;font-size:8.5pt;">R</span>
            <?php endif; ?>
          </td>
          <td></td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- SIGNATURES -->
  <table class="footer-sig" style="margin-top:40px;">
    <tr>
      <td>Prepared by</td>
      <td>Chief Superintendent</td>
      <td>Controller of Examinations</td>
    </tr>
  </table>

  <p style="text-align:center;font-size:7.5pt;color:#777;margin-top:16px;border-top:1px dotted #bbb;padding-top:5px;">
    Generated on <?php echo date('d M Y H:i'); ?> &bull;
    <?php echo htmlspecialchars($roll->exam_name ?? ''); ?> &bull;
    <?php echo htmlspecialchars($roll->subject_name ?? ''); ?>
    (<?php echo htmlspecialchars($roll->subject_code ?? ''); ?>)
  </p>
</div>
</body>
</html>
