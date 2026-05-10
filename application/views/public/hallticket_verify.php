<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hall Ticket Verification</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; background: #f0f2f5; color: #1a1a2e; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
  .card { background: #fff; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.12); max-width: 520px; width: 100%; overflow: hidden; }
  .card-header { padding: 18px 24px; text-align: center; }
  .card-header .school-logo { width: 60px; height: 60px; object-fit: contain; margin-bottom: 8px; }
  .card-header .school-name { font-size: 15px; font-weight: bold; color: #1a237e; text-transform: uppercase; }
  .card-header .school-addr { font-size: 11px; color: #666; margin-top: 3px; }
  .status-banner { padding: 14px 24px; text-align: center; font-size: 16px; font-weight: bold; letter-spacing: 1px; }
  .status-valid   { background: #e8f5e9; color: #2e7d32; border-bottom: 3px solid #2e7d32; }
  .status-invalid { background: #ffebee; color: #c62828; border-bottom: 3px solid #c62828; }
  .status-error   { background: #fff3e0; color: #e65100; border-bottom: 3px solid #e65100; }
  .status-icon { font-size: 28px; display: block; margin-bottom: 4px; }
  .card-body { padding: 20px 24px; }
  .student-row { display: flex; gap: 16px; align-items: flex-start; margin-bottom: 16px; }
  .student-photo { width: 80px; height: 96px; object-fit: cover; border: 2px solid #90a4ae; border-radius: 6px; flex-shrink: 0; }
  .student-photo-placeholder { width: 80px; height: 96px; background: #e0e0e0; border: 2px solid #bdbdbd; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 11px; color: #757575; flex-shrink: 0; text-align: center; }
  .info-grid { width: 100%; border-collapse: collapse; font-size: 13px; }
  .info-grid tr td { padding: 5px 0; vertical-align: top; }
  .info-grid .lbl { color: #555; font-weight: bold; width: 44%; padding-right: 8px; white-space: nowrap; }
  .info-grid .val { color: #1a1a2e; }
  .divider { border: none; border-top: 1px solid #e0e0e0; margin: 14px 0; }
  .exam-box { background: #e8eaf6; border-radius: 6px; padding: 12px 16px; font-size: 13px; }
  .exam-box .exam-name { font-size: 15px; font-weight: bold; color: #1a237e; margin-bottom: 6px; }
  .exam-box .exam-period { color: #444; }
  .footer-note { text-align: center; font-size: 11px; color: #999; padding: 12px 24px; border-top: 1px solid #f0f0f0; }
  .invalidated-warning { background: #ffebee; border: 1px solid #ef9a9a; border-radius: 6px; padding: 10px 14px; color: #c62828; font-size: 13px; margin-top: 12px; text-align: center; }
</style>
</head>
<body>
<div class="card">

  <?php
  $school_name = $ht->school_name  ?? 'Institution';
  $school_addr = $ht->school_address ?? '';
  $logo_file   = $ht->admin_logo    ?? '';
  $logo_url    = $logo_file ? base_url('uploads/logos/' . htmlspecialchars($logo_file)) : '';
  ?>

  <!-- Header -->
  <div class="card-header" style="border-bottom: 2px solid #1a237e;">
    <?php if ($logo_url): ?>
      <img src="<?php echo $logo_url; ?>" class="school-logo" alt="Logo">
    <?php endif; ?>
    <div class="school-name"><?php echo htmlspecialchars($school_name); ?></div>
    <?php if ($school_addr): ?>
      <div class="school-addr"><?php echo htmlspecialchars($school_addr); ?></div>
    <?php endif; ?>
  </div>

  <?php if (!empty($error) || !$ht): ?>
    <!-- Error / not found -->
    <div class="status-banner status-error">
      <span class="status-icon">&#9888;</span>
      <?php echo htmlspecialchars($error ?? 'Verification failed.'); ?>
    </div>
    <div class="card-body" style="text-align:center;color:#888;padding:30px;">
      This QR code could not be verified. Please contact the institution.
    </div>

  <?php elseif (!$is_valid): ?>
    <!-- Found but invalidated -->
    <div class="status-banner status-invalid">
      <span class="status-icon">&#10008;</span>
      HALL TICKET INVALIDATED
    </div>
    <div class="card-body">
      <?php $student_name = trim(($ht->firstname ?? '') . ' ' . ($ht->lastname ?? '')); ?>
      <div class="invalidated-warning">
        This hall ticket for <strong><?php echo htmlspecialchars($student_name); ?></strong>
        (<?php echo htmlspecialchars($ht->hall_ticket_no); ?>) has been marked invalid by the institution.
      </div>
    </div>

  <?php else: ?>
    <!-- Valid hall ticket -->
    <div class="status-banner status-valid">
      <span class="status-icon">&#10004;</span>
      HALL TICKET VERIFIED
    </div>
    <div class="card-body">

      <?php
      $student_name = trim(($ht->firstname ?? '') . ' ' . ($ht->lastname ?? ''));
      $photo_path   = FCPATH . 'uploads/student_images/' . ($ht->student_image ?? '');
      $photo_url    = ($ht->student_image && is_file($photo_path))
                      ? base_url('uploads/student_images/' . htmlspecialchars($ht->student_image))
                      : null;
      ?>

      <!-- Student photo + basic info -->
      <div class="student-row">
        <?php if ($photo_url): ?>
          <img src="<?php echo $photo_url; ?>" class="student-photo" alt="Photo">
        <?php else: ?>
          <div class="student-photo-placeholder">No Photo</div>
        <?php endif; ?>

        <table class="info-grid">
          <tr>
            <td class="lbl">Student Name</td>
            <td class="val"><strong><?php echo htmlspecialchars($student_name); ?></strong></td>
          </tr>
          <tr>
            <td class="lbl">Hall Ticket No</td>
            <td class="val"><strong><?php echo htmlspecialchars($ht->hall_ticket_no); ?></strong></td>
          </tr>
          <tr>
            <td class="lbl">Programme</td>
            <td class="val"><?php echo htmlspecialchars(($ht->class_name ?? '') . ($ht->department_name ? ' — ' . $ht->department_name : '')); ?></td>
          </tr>
          <tr>
            <td class="lbl">Section</td>
            <td class="val"><?php echo htmlspecialchars($ht->section_name ?? '—'); ?></td>
          </tr>
          <tr>
            <td class="lbl">Academic Session</td>
            <td class="val"><?php echo htmlspecialchars($ht->session_name ?? '—'); ?></td>
          </tr>
        </table>
      </div>

      <hr class="divider">

      <!-- Exam details -->
      <div class="exam-box">
        <div class="exam-name"><?php echo htmlspecialchars($ht->exam_name ?? '—'); ?></div>
        <div class="exam-period">
          <?php
          $from = $ht->date_from ? date('d M Y', strtotime($ht->date_from)) : '—';
          $to   = $ht->date_to   ? date('d M Y', strtotime($ht->date_to))   : '—';
          echo 'Exam Period: ' . htmlspecialchars($from) . ' &mdash; ' . htmlspecialchars($to);
          ?>
        </div>
      </div>

    </div><!-- /.card-body -->

  <?php endif; ?>

  <div class="footer-note">
    Verified on <?php echo date('d M Y, h:i A'); ?> &bull; <?php echo htmlspecialchars($school_name); ?>
  </div>

</div><!-- /.card -->
</body>
</html>
