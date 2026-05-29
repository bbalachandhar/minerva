<?php defined('BASEPATH') or exit('No direct script access allowed');
$school_name = $sch_setting->name ?? 'Institution Name';
$school_addr = $sch_setting->address ?? '';
$duty_labels = [
    'chief_superintendent' => 'Chief Superintendent',
    'invigilator'          => 'Invigilator',
    'deputy'               => 'Deputy',
    'flying_squad'         => 'Flying Squad',
];
// Group by room (keyed by room ID so each seating room is a separate block)
$by_room = [];
foreach ($duties as $d) {
    $key = $d->seating_room_id . ' | ' . $d->hall_name . ' | ' . $d->exam_date . ' | ' . $d->session_slot;
    $by_room[$key][] = $d;
}
ksort($by_room);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { box-sizing:border-box; }
body { font-family:Arial,Helvetica,sans-serif;font-size:10pt;color:#1a1a2e;margin:0;padding:0; }
.page-border { border:2px solid #283593;padding:10px;min-height:97%; }
.header { border-bottom:2px solid #283593;padding-bottom:8px;margin-bottom:10px;display:table;width:100%; }
.header-logo   { display:table-cell;width:70px;vertical-align:middle; }
.header-center { display:table-cell;text-align:center;vertical-align:middle;padding:0 10px; }
.school-name { font-size:14pt;font-weight:bold;color:#283593;text-transform:uppercase; }
.school-addr { font-size:8pt;color:#555;margin-top:2px; }
.doc-title   { font-size:12pt;font-weight:bold;color:#b71c1c;letter-spacing:2px;margin-top:4px;text-transform:uppercase; }
.exam-info { width:100%;border-collapse:collapse;margin-bottom:14px;font-size:9.5pt; }
.exam-info td { padding:3px 8px;border:1px solid #c5cae9; }
.exam-info .lbl { background:#e8eaf6;color:#283593;font-weight:bold;width:25%; }
.room-block { margin-bottom:14px; }
.room-title { background:#283593;color:#fff;padding:5px 10px;font-size:10pt;font-weight:bold; }
.duty-table { width:100%;border-collapse:collapse;font-size:9pt; }
.duty-table thead th { background:#e8eaf6;color:#283593;border:1px solid #c5cae9;padding:4px 7px;text-align:left; }
.duty-table tbody td { border:1px solid #c5cae9;padding:4px 7px; }
.duty-table tbody tr:nth-child(even) { background:#f8f9ff; }
.footer-sig { width:100%;margin-top:30px;border-collapse:collapse; }
.footer-sig td { text-align:center;font-size:9pt;border-top:1px solid #333;padding-top:4px;width:33%; }
</style>
</head>
<body>
<div class="page-border">
  <table style="width:100%;border-collapse:collapse;border-bottom:2px solid #283593;padding-bottom:8px;margin-bottom:10px;">
    <tr>
      <td style="width:75px;vertical-align:middle;text-align:center;">
        <?php if ($logo_path): ?>
          <img src="<?php echo $logo_path; ?>" style="width:65px;height:65px;object-fit:contain;">
        <?php endif; ?>
      </td>
      <td style="text-align:center;vertical-align:middle;padding:0 8px;">
        <div class="school-name"><?php echo htmlspecialchars($school_name); ?></div>
        <?php if ($school_addr): ?><div class="school-addr"><?php echo htmlspecialchars($school_addr); ?></div><?php endif; ?>
        <div class="doc-title">&#9632; Invigilation Duty Roster &#9632;</div>
      </td>
      <td style="width:30px;"></td>
    </tr>
  </table>

  <table class="exam-info">
    <tr>
      <td class="lbl">Exam / Event</td>
      <td><?php echo htmlspecialchars($batch_exam->exam ?? '—'); ?></td>
      <td class="lbl">Generated On</td>
      <td><?php echo date('d M Y H:i'); ?></td>
    </tr>
  </table>

  <?php if (empty($by_room)): ?>
    <p style="text-align:center;color:#777;">No duties assigned.</p>
  <?php else: ?>
    <?php foreach ($by_room as $room_key => $room_duties): ?>
      <?php list($room_id, $hall, $date, $slot) = explode(' | ', $room_key); ?>
      <div class="room-block">
        <div class="room-title">
          Room #<?php echo (int)$room_id; ?>
          &bull; <?php echo htmlspecialchars($hall); ?>
          &bull; <?php echo date('d M Y', strtotime($date)); ?>
          &bull; <?php echo $slot === 'FN' ? 'Forenoon (FN)' : 'Afternoon (AN)'; ?>
        </div>
        <table class="duty-table">
          <thead>
            <tr>
              <th style="width:35px;">S.No</th>
              <th>Staff Name</th>
              <th style="width:130px;">Designation</th>
              <th style="width:130px;">Duty Type</th>
              <th>Remarks</th>
              <th style="width:80px;">Signature</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($room_duties as $i => $d): ?>
            <tr>
              <td><?php echo $i+1; ?></td>
              <td><strong><?php echo htmlspecialchars($d->staff_firstname . ' ' . $d->staff_surname); ?></strong></td>
              <td><?php echo htmlspecialchars($d->designation ?? '—'); ?></td>
              <td><?php echo $duty_labels[$d->duty_type] ?? ucwords(str_replace('_',' ',$d->duty_type)); ?></td>
              <td><?php echo htmlspecialchars($d->remarks ?? ''); ?></td>
              <td></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <table class="footer-sig" style="margin-top:40px;">
    <tr>
      <td>Prepared by</td>
      <td>Chief Superintendent</td>
      <td>Controller of Examinations</td>
    </tr>
  </table>

  <p style="text-align:center;font-size:7.5pt;color:#777;margin-top:16px;border-top:1px dotted #bbb;padding-top:5px;">
    Invigilation Duty Roster &bull; <?php echo htmlspecialchars($batch_exam->exam ?? ''); ?> &bull; Generated on <?php echo date('d M Y H:i'); ?>
  </p>
</div>
</body>
</html>
