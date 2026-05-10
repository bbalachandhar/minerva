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
.page-border { border:2px solid #006064;padding:10px;min-height:97%; }
.header { border-bottom:2px solid #006064;padding-bottom:8px;margin-bottom:10px;display:table;width:100%; }
.header-logo   { display:table-cell;width:70px;vertical-align:middle; }
.header-center { display:table-cell;text-align:center;vertical-align:middle;padding:0 10px; }
.school-name { font-size:14pt;font-weight:bold;color:#006064;text-transform:uppercase; }
.school-addr { font-size:8pt;color:#555;margin-top:2px; }
.doc-title   { font-size:12pt;font-weight:bold;color:#b71c1c;letter-spacing:2px;margin-top:4px;text-transform:uppercase; }
.info-table { width:100%;border-collapse:collapse;margin-bottom:10px;font-size:9.5pt; }
.info-table td { padding:3px 8px;border:1px solid #b2dfdb; }
.info-table .lbl { background:#e0f2f1;color:#006064;font-weight:bold;width:28%; }
.seat-table { width:100%;border-collapse:collapse;font-size:9.5pt; }
.seat-table thead th { background:#e0f2f1;color:#006064;border:1px solid #b2dfdb;padding:5px 7px;text-align:left; }
.seat-table tbody td { border:1px solid #b2dfdb;padding:4px 7px; }
.seat-table tbody tr:nth-child(even) { background:#f1fffe; }
.footer-sig { width:100%;margin-top:30px;border-collapse:collapse; }
.footer-sig td { text-align:center;font-size:9pt;border-top:1px solid #333;padding-top:4px;width:33%; }
</style>
</head>
<body>
<div class="page-border">
  <div class="header">
    <div class="header-logo">
      <?php if ($logo_path && is_file($logo_path)): ?>
        <img src="<?php echo $logo_path; ?>" style="width:65px;height:65px;object-fit:contain;">
      <?php endif; ?>
    </div>
    <div class="header-center">
      <div class="school-name"><?php echo htmlspecialchars($school_name); ?></div>
      <?php if ($school_addr): ?><div class="school-addr"><?php echo htmlspecialchars($school_addr); ?></div><?php endif; ?>
      <div class="doc-title">&#9632; Seating Arrangement &#9632;</div>
    </div>
  </div>

  <table class="info-table">
    <tr>
      <td class="lbl">Exam Hall</td><td><strong><?php echo htmlspecialchars($room->hall_name); ?></strong><?php if ($room->location ?? null): ?> &mdash; <?php echo htmlspecialchars($room->location); ?><?php endif; ?></td>
      <td class="lbl">Exam / Event</td><td><?php echo htmlspecialchars($room->exam_name ?? '—'); ?></td>
    </tr>
    <tr>
      <td class="lbl">Date &amp; Session</td>
      <td><?php echo date('d M Y', strtotime($room->exam_date)); ?> &bull; <?php echo $room->session_slot === 'FN' ? 'Forenoon (FN)' : 'Afternoon (AN)'; ?></td>
      <td class="lbl">Capacity / Assigned</td>
      <td><?php echo $room->effective_capacity; ?> / <?php echo count($assignments); ?></td>
    </tr>
    <?php if ($room->subject_name ?? null): ?>
    <tr>
      <td class="lbl">Subject</td><td colspan="3"><?php echo htmlspecialchars($room->subject_name); ?> (<?php echo htmlspecialchars($room->subject_code ?? ''); ?>)</td>
    </tr>
    <?php endif; ?>
  </table>

  <table class="seat-table">
    <thead>
      <tr>
        <th style="width:40px;">S.No</th>
        <th style="width:65px;">Seat</th>
        <th style="width:85px;">Reg. No.</th>
        <th>Student Name</th>
        <th style="width:100px;">Programme</th>
        <th style="width:90px;">Hall Ticket</th>
        <th style="width:65px;">Signature</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($assignments)): ?>
        <tr><td colspan="7" style="text-align:center;color:#777;">No students assigned</td></tr>
      <?php else: ?>
        <?php foreach ($assignments as $i => $a): ?>
        <tr>
          <td><?php echo $i+1; ?></td>
          <td><strong style="color:#006064;"><?php echo htmlspecialchars($a->seat_number); ?></strong></td>
          <td><?php echo htmlspecialchars($a->register_no ?? '—'); ?></td>
          <td><strong><?php echo htmlspecialchars($a->firstname . ' ' . $a->lastname); ?></strong></td>
          <td><?php echo htmlspecialchars($a->class_name ?? '—'); ?></td>
          <td><?php echo htmlspecialchars($a->hall_ticket_no ?? '—'); ?></td>
          <td></td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <table class="footer-sig" style="margin-top:40px;">
    <tr>
      <td>Room Invigilator</td>
      <td>Chief Superintendent</td>
      <td>Controller of Examinations</td>
    </tr>
  </table>

  <p style="text-align:center;font-size:7.5pt;color:#777;margin-top:16px;border-top:1px dotted #bbb;padding-top:5px;">
    Seating Plan &bull; <?php echo htmlspecialchars($room->hall_name); ?> &bull;
    <?php echo date('d M Y', strtotime($room->exam_date)); ?> &bull;
    <?php echo $room->session_slot; ?> &bull; Generated on <?php echo date('d M Y H:i'); ?>
  </p>
</div>
</body>
</html>
