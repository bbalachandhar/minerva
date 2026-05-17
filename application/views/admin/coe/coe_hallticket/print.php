<?php
/**
 * Hall Ticket PDF Template
 * Rendered by mPDF (A4 portrait). Inline styles only — no external CSS dependency.
 * Variables: $ht (hall ticket + student + exam data), $subjects (array),
 *            $qr_img (base64 data URI), $student_photo (absolute file path),
 *            $logo_path (absolute file path or null), $sch_setting (school settings row)
 */
defined('BASEPATH') or exit('No direct script access allowed');

$student_name = trim(($ht->firstname ?? '') . ' ' . ($ht->lastname ?? ''));
$register_no  = $ht->register_no ?? '—';
$dob          = $ht->dob ? date('d / m / Y', strtotime($ht->dob)) : '—';
$gender       = ucfirst($ht->gender ?? '—');
$class_name   = $ht->class_name ?? '—';
$section_name = $ht->section_name ?? '';
$dept_name    = $ht->department_name ?? '';
$exam_name    = $ht->exam_name ?? '—';
$exam_group   = $ht->exam_group_name ?? '—';
$session_name = $ht->session_name ?? '—';
$date_from    = $ht->date_from ? date('d M Y', strtotime($ht->date_from)) : '—';
$date_to      = $ht->date_to   ? date('d M Y', strtotime($ht->date_to))   : '—';
$school_name  = $sch_setting->name ?? 'Institution Name';
$school_addr  = $sch_setting->address ?? '';
$ht_no        = $ht->hall_ticket_no ?? '—';
$is_valid     = (bool)($ht->is_valid ?? true);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  * { box-sizing: border-box; }
  body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #1a1a2e; margin: 0; padding: 0; }
  .page-border { border: 3px double #1a237e; padding: 10px; min-height: 97%; }
  /* School name */
  .school-name { font-size: 15pt; font-weight: bold; color: #1a237e; text-transform: uppercase; }
  .school-addr { font-size: 8pt; color: #444; margin-top: 2px; }
  .ht-title    { font-size: 13pt; font-weight: bold; color: #b71c1c; letter-spacing: 2px; margin-top: 4px; text-transform: uppercase; }
  .ht-subtitle { font-size: 9pt; color: #555; margin-top: 2px; }
  /* HT Number Banner */
  .ht-number-banner {
    background: #1a237e; color: #fff; text-align: center;
    font-size: 14pt; font-weight: bold; letter-spacing: 3px;
    padding: 6px 0; border-radius: 4px; margin-bottom: 10px;
  }
  .ht-number-banner .ht-label { font-size: 8pt; font-weight: normal; letter-spacing: 1px; display: block; }
  /* Invalid watermark */
  .invalid-banner { background: #b71c1c; color: #fff; text-align: center; font-size: 11pt; font-weight: bold; padding: 4px 0; margin-bottom: 8px; border-radius: 3px; letter-spacing: 2px; }
  /* Info grid */
  .info-table { width: 100%; border-collapse: collapse; font-size: 10pt; }
  .info-table td { padding: 3px 6px; border: none; }
  .info-table .lbl { color: #555; font-weight: bold; width: 40%; white-space: nowrap; }
  .info-table .val { color: #1a1a2e; }
  /* Divider */
  .divider { border-top: 1.5px solid #1a237e; margin: 8px 0; }
  /* Subjects table */
  .section-title { background: #1a237e; color: #fff; font-size: 10pt; font-weight: bold; padding: 5px 10px; text-transform: uppercase; letter-spacing: 1px; }
  .subjects-table { width: 100%; border-collapse: collapse; font-size: 10pt; }
  .subjects-table thead th { background: #e8eaf6; color: #1a237e; border: 1px solid #c5cae9; padding: 5px 8px; text-align: left; font-weight: bold; }
  .subjects-table tbody td { border: 1px solid #c5cae9; padding: 5px 8px; }
  .subjects-table tbody tr:nth-child(even) { background: #f8f9ff; }
  .arrear-yes { color: #b71c1c; font-weight: bold; font-size: 9pt; }
  .arrear-no  { color: #2e7d32; font-size: 9pt; }
  .cat-pill   { background: #e3f2fd; color: #1565c0; border: 1px solid #90caf9; border-radius: 10px; padding: 1px 7px; font-size: 8.5pt; }
  /* Declaration + signatures */
  .declaration-box { border: 1px solid #90a4ae; border-radius: 4px; padding: 8px 10px; margin-top: 10px; font-size: 8.5pt; color: #444; background: #fafafa; }
</style>
</head>
<body>
<div class="page-border">

  <!-- HEADER -->
  <table style="width:100%;border-collapse:collapse;border-bottom:2px solid #1a237e;padding-bottom:8px;margin-bottom:10px;">
    <tr>
      <!-- Logo -->
      <td style="width:75px;vertical-align:middle;text-align:center;">
        <?php if ($logo_path): ?>
          <img src="<?php echo $logo_path; ?>" style="width:65px;height:65px;object-fit:contain;" alt="Logo">
        <?php else: ?>
          <div style="width:65px;height:65px;background:#e8eaf6;border:1px solid #c5cae9;border-radius:4px;text-align:center;font-size:8pt;font-weight:bold;color:#1a237e;padding-top:22px;">LOGO</div>
        <?php endif; ?>
      </td>
      <!-- School name + title -->
      <td style="text-align:center;vertical-align:middle;padding:0 8px;">
        <div class="school-name"><?php echo htmlspecialchars($school_name); ?></div>
        <?php if ($school_addr): ?>
          <div class="school-addr"><?php echo htmlspecialchars($school_addr); ?></div>
        <?php endif; ?>
        <div class="ht-title">&#9632; Hall Ticket &#9632;</div>
        <div class="ht-subtitle"><?php echo htmlspecialchars($exam_group); ?> &mdash; <?php echo htmlspecialchars($exam_name); ?></div>
      </td>
      <!-- Exam period -->
      <td style="width:120px;vertical-align:middle;text-align:center;">
        <div style="font-size:8pt;color:#555;font-weight:bold;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Exam Period</div>
        <div style="font-size:9pt;font-weight:bold;color:#1a237e;white-space:nowrap;"><?php echo $date_from; ?></div>
        <div style="font-size:8pt;color:#555;">to</div>
        <div style="font-size:9pt;font-weight:bold;color:#1a237e;white-space:nowrap;"><?php echo $date_to; ?></div>
      </td>
    </tr>
  </table>

  <!-- HT NUMBER BANNER -->
  <div class="ht-number-banner">
    <span class="ht-label">Hall Ticket Number</span>
    <?php echo htmlspecialchars($ht_no); ?>
  </div>

  <!-- INVALID WARNING -->
  <?php if (!$is_valid): ?>
  <div class="invalid-banner">&#9888; THIS HALL TICKET HAS BEEN INVALIDATED &#9888;</div>
  <?php endif; ?>

  <!-- STUDENT INFO + QR -->
  <table style="width:100%;border-collapse:collapse;margin-bottom:10px;">
    <tr>
      <!-- Photo -->
      <td style="width:95px;vertical-align:top;text-align:center;padding-right:10px;">
        <?php if (is_file($student_photo)): ?>
          <img src="<?php echo $student_photo; ?>" style="width:85px;height:100px;object-fit:cover;border:2px solid #90a4ae;border-radius:4px;" alt="Photo">
        <?php else: ?>
          <div style="width:85px;height:100px;background:#e0e0e0;border:2px solid #bdbdbd;border-radius:4px;text-align:center;font-size:8pt;color:#757575;padding-top:38px;">No Photo</div>
        <?php endif; ?>
      </td>
      <!-- Student info -->
      <td style="vertical-align:top;padding-left:4px;">
        <table class="info-table">
          <tr>
            <td class="lbl">Student Name</td>
            <td class="val"><strong><?php echo htmlspecialchars($student_name); ?></strong></td>
          </tr>
          <tr>
            <td class="lbl">Register No</td>
            <td class="val"><?php echo htmlspecialchars($register_no); ?></td>
          </tr>
          <tr>
            <td class="lbl">Date of Birth</td>
            <td class="val"><?php echo htmlspecialchars($dob); ?></td>
          </tr>
          <tr>
            <td class="lbl">Gender</td>
            <td class="val"><?php echo htmlspecialchars($gender); ?></td>
          </tr>
          <tr>
            <td class="lbl">Programme</td>
            <td class="val"><?php echo htmlspecialchars($class_name); ?><?php echo $dept_name ? ' &mdash; ' . htmlspecialchars($dept_name) : ''; ?></td>
          </tr>
          <tr>
            <td class="lbl">Section</td>
            <td class="val"><?php echo $section_name ? htmlspecialchars($section_name) : '—'; ?></td>
          </tr>
          <tr>
            <td class="lbl">Academic Session</td>
            <td class="val"><?php echo htmlspecialchars($session_name); ?></td>
          </tr>
        </table>
      </td>
      <!-- QR Code -->
      <td style="width:115px;vertical-align:top;text-align:center;padding-left:8px;">
        <img src="<?php echo $qr_img; ?>" style="width:100px;height:100px;" alt="QR Code">
        <div style="font-size:7.5pt;color:#555;margin-top:3px;">Scan for verification</div>
      </td>
    </tr>
  </table>

  <div class="divider"></div>

  <!-- SUBJECTS TABLE -->
  <div class="section-title">Subjects Registered for Examination</div>
  <table class="subjects-table">
    <thead>
      <tr>
        <th style="width:30px;">#</th>
        <th>Subject</th>
        <th style="width:90px;">Code</th>
        <th style="width:70px;">Type</th>
        <th style="width:75px;">Category</th>
        <th style="width:55px;">Arrear</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($subjects)): ?>
        <tr><td colspan="6" style="text-align:center;color:#777;">No subjects found</td></tr>
      <?php else: ?>
        <?php foreach ($subjects as $i => $sub): ?>
        <tr>
          <td><?php echo $i + 1; ?></td>
          <td><strong><?php echo htmlspecialchars($sub->subject_name ?? '—'); ?></strong></td>
          <td><?php echo htmlspecialchars($sub->subject_code ?? '—'); ?></td>
          <td><?php echo htmlspecialchars(ucfirst($sub->subject_type ?? '—')); ?></td>
          <td>
            <?php if ($sub->cbcs_category ?? null): ?>
              <span class="cat-pill"><?php echo htmlspecialchars(strtoupper($sub->cbcs_category)); ?></span>
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
          <td class="text-center">
            <?php if ($sub->is_arrear ?? false): ?>
              <span class="arrear-yes">&#9679; Arrear</span>
            <?php else: ?>
              <span class="arrear-no">Regular</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="divider" style="margin-top:10px;"></div>

  <!-- DECLARATION -->
  <div class="declaration-box">
    <strong>Declaration:</strong>
    I hereby declare that I am the bonafide student of this institution and the information furnished above is correct to the best of my knowledge.
    I undertake to abide by the rules and regulations of the examination.
  </div>

  <!-- SIGNATURES -->
  <table style="width:100%;margin-top:30px;border-collapse:collapse;">
    <tr>
      <td style="width:33%;text-align:center;border-top:1px solid #333;padding-top:4px;font-size:9pt;">Student Signature</td>
      <td style="width:33%;text-align:center;border-top:1px solid #333;padding-top:4px;font-size:9pt;">Chief Superintendent</td>
      <td style="width:33%;text-align:center;border-top:1px solid #333;padding-top:4px;font-size:9pt;">Controller of Examinations</td>
    </tr>
  </table>

  <!-- Footer note -->
  <p style="text-align:center;font-size:7.5pt;color:#777;margin-top:14px;border-top:1px dotted #bbb;padding-top:5px;">
    This hall ticket is valid only for <?php echo htmlspecialchars($exam_name); ?> &bull;
    Hall Ticket No: <strong><?php echo htmlspecialchars($ht_no); ?></strong> &bull;
    Generated: <?php echo $ht->generated_at ? date('d M Y', strtotime($ht->generated_at)) : date('d M Y'); ?>
  </p>

</div><!-- /.page-border -->
</body>
</html>
