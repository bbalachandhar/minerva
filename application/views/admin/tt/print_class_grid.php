<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Class Timetable &mdash; <?php echo htmlspecialchars($class_label . ' ' . $section_label); ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; font-size: 12px; padding: 10px; color: #333; }
.header-img { width: 100%; max-height: 140px; object-fit: contain; display: block; }
.header-wrap { border-bottom: 2px solid #3c8dbc; margin-bottom: 10px; padding-bottom: 8px; }
h2 { text-align: center; font-size: 15px; margin: 8px 0 10px; color: #2c3e50; }
table { width: 100%; border-collapse: collapse; font-size: 11px; }
th { background: #3c8dbc; color: #fff; padding: 6px 5px; text-align: center; border: 1px solid #2980b9; white-space: nowrap; }
td { border: 1px solid #bbb; padding: 5px 4px; vertical-align: middle; text-align: center; min-height: 48px; }
.time-col { background: #f4f4f4; text-align: left; padding-left: 6px; width: 90px; }
.break-row td { background: #fffde7; color: #888; text-align: center; font-style: italic; }
.slot-tag { display: inline-block; border-radius: 3px; padding: 2px 6px; font-size: 11px; font-weight: 600; color: #fff; }
.slot-theory    { background: #3498db; }
.slot-practical { background: #e74c3c; }
.slot-project   { background: #f39c12; }
.slot-free      { background: #27ae60; }
.slot-other     { background: #7f8c8d; }
.teacher-name { font-size: 10px; color: #555; display: block; margin-top: 2px; }
.room-name    { font-size: 10px; color: #888; display: block; }
.period-name  { font-weight: bold; display: block; }
.period-time  { font-size: 10px; color: #666; }
.footer-content { margin-top: 12px; font-size: 11px; color: #555; border-top: 1px solid #ddd; padding-top: 8px; }
.print-date { text-align: right; font-size: 10px; color: #aaa; margin-top: 6px; }
@media print {
  @page { margin: 8mm; size: A4 landscape; }
  body  { padding: 0; font-size: 11px; }
  .no-print { display: none !important; }
}
</style>
</head>
<body>

<?php if (!empty($header_img_url)): ?>
<div class="header-wrap">
  <img src="<?php echo htmlspecialchars($header_img_url); ?>" class="header-img" alt="Header">
</div>
<?php endif; ?>

<h2>Class Timetable &mdash; <?php echo htmlspecialchars($class_label . ' ' . $section_label); ?></h2>

<?php
$type_class = [
    'theory'    => 'slot-theory',
    'practical' => 'slot-practical',
    'project'   => 'slot-project',
    'other'     => 'slot-other',
];
?>
<table>
  <thead>
    <tr>
      <th class="time-col">Period / Time</th>
      <?php foreach ($days as $day_name => $dv): ?>
      <th><?php echo htmlspecialchars($day_name); ?></th>
      <?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($periods as $period): ?>
    <?php if ($period->is_break): ?>
    <tr class="break-row">
      <td class="time-col">
        <span class="period-name"><?php echo htmlspecialchars($period->name); ?></span>
        <span class="period-time"><?php echo date('h:i', strtotime($period->start_time)) . ' - ' . date('h:i', strtotime($period->end_time)); ?></span>
      </td>
      <td colspan="<?php echo count($days); ?>">
        <?php echo htmlspecialchars($period->break_label ?: $period->name); ?>
      </td>
    </tr>
    <?php else: ?>
    <tr>
      <td class="time-col">
        <span class="period-name"><?php echo htmlspecialchars($period->name); ?></span>
        <span class="period-time"><?php echo date('h:i', strtotime($period->start_time)) . ' - ' . date('h:i', strtotime($period->end_time)); ?></span>
      </td>
      <?php foreach ($days as $day_name => $dv): ?>
      <?php $entry = $entry_map[$day_name][$period->id][0] ?? null; ?>
      <td>
        <?php if ($entry): ?>
          <?php if ($entry->is_free_period): ?>
            <span class="slot-tag slot-free"><?php echo htmlspecialchars($entry->free_period_label ?: 'Free'); ?></span>
          <?php else: ?>
            <?php
              $tc        = $type_class[strtolower($entry->subject_type ?? 'other')] ?? 'slot-other';
              $slot_style = !empty($entry->tt_color) ? 'style="background:' . htmlspecialchars($entry->tt_color) . '"' : '';
              $slot_class = !empty($entry->tt_color) ? '' : $tc;
              $slot_text  = !empty($entry->tt_abbr) ? $entry->tt_abbr : ($entry->subject_code ?: $entry->subject_name);
            ?>
            <span class="slot-tag <?php echo $slot_class; ?>" <?php echo $slot_style; ?>><?php echo htmlspecialchars($slot_text); ?></span>
            <?php
              $sgs_k = $entry->subject_group_subject_id ?? 0;
              $tname = !empty($joint_teacher_map[$sgs_k])
                      ? $joint_teacher_map[$sgs_k]
                      : trim(($entry->staff_name ?? '') . ' ' . ($entry->staff_surname ?? ''));
              if ($tname): ?>
              <span class="teacher-name"><?php echo htmlspecialchars($tname); ?></span>
            <?php endif; ?>
            <?php if (!empty($entry->room_name)): ?>
              <span class="room-name"><?php echo htmlspecialchars($entry->room_name); ?></span>
            <?php endif; ?>
          <?php endif; ?>
        <?php else: ?>&nbsp;<?php endif; ?>
      </td>
      <?php endforeach; ?>
    </tr>
    <?php endif; ?>
  <?php endforeach; ?>
  </tbody>
</table>

<p class="print-date">Printed: <?php echo date('d M Y, h:i A'); ?></p>

<?php if (!empty($for_print)): ?>
<script>window.onload = function(){ window.print(); };</script>
<?php endif; ?>
</body>
</html>
