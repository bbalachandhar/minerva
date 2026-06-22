<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>All Teacher Timetables</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; font-size: 11px; padding: 0; color: #333; }
.page { page-break-after: always; padding: 10px; }
.page:last-child { page-break-after: auto; }
.header-img { width: 100%; max-height: 100px; object-fit: contain; display: block; }
.header-wrap { border-bottom: 2px solid #3c8dbc; margin-bottom: 6px; padding-bottom: 4px; }
h2 { text-align: center; font-size: 14px; margin: 4px 0 2px; color: #2c3e50; }
h3 { text-align: center; font-size: 11px; color: #666; font-weight: normal; margin-bottom: 6px; }
table { width: 100%; border-collapse: collapse; font-size: 10px; }
th { background: #3c8dbc; color: #fff; padding: 4px 3px; text-align: center; border: 1px solid #2980b9; white-space: nowrap; font-size: 10px; }
td { border: 1px solid #bbb; padding: 3px 2px; vertical-align: middle; text-align: center; }
.time-col { background: #f4f4f4; text-align: left; padding-left: 4px; width: 80px; }
.break-row td { background: #fffde7; color: #888; text-align: center; font-style: italic; }
.slot-tag { display: inline-block; border-radius: 3px; padding: 1px 5px; font-size: 10px; font-weight: 600; color: #fff; }
.slot-theory    { background: #3498db; }
.slot-practical { background: #e74c3c; }
.slot-project   { background: #f39c12; }
.slot-free      { background: #27ae60; }
.slot-other     { background: #7f8c8d; }
.cls-name { font-size: 9px; font-weight: bold; display: block; margin-top: 1px; }
.room-name { font-size: 9px; color: #888; display: block; }
.period-name { font-weight: bold; display: block; font-size: 10px; }
.period-time { font-size: 9px; color: #666; }
.print-footer { text-align: right; font-size: 9px; color: #aaa; margin-top: 4px; }
.page-num { text-align: center; font-size: 9px; color: #999; margin-top: 4px; }
@media print {
  @page { margin: 6mm; size: A4 landscape; }
  body { padding: 0; }
}
</style>
</head>
<body>

<?php
$type_class = ['theory'=>'slot-theory','practical'=>'slot-practical','project'=>'slot-project','other'=>'slot-other'];
$total = count($teachers);
$num = 0;
foreach ($teachers as $t):
    $staff = $t['staff'];
    $entry_map = $t['entry_map'];
    $num++;
?>
<div class="page">

<?php if (!empty($header_img_url)): ?>
<div class="header-wrap">
  <img src="<?php echo htmlspecialchars($header_img_url); ?>" class="header-img" alt="Header">
</div>
<?php elseif (!empty($school_name)): ?>
<div class="header-wrap" style="text-align:center;">
  <h2 style="font-size:15px;margin:2px 0;"><?php echo htmlspecialchars($school_name); ?></h2>
</div>
<?php endif; ?>

<h2>Teacher Timetable</h2>
<h3><?php echo htmlspecialchars(($staff->name ?? '').' '.($staff->surname ?? '')); ?>
  <?php if (!empty($staff->employee_id)): ?> &mdash; <?php echo htmlspecialchars($staff->employee_id); ?><?php endif; ?>
</h3>

<table>
  <thead>
    <tr>
      <th class="time-col">Period / Time</th>
      <?php foreach ($days as $day_name => $dv): ?>
      <th><?php echo htmlspecialchars($day_name); ?>
        <?php if (!empty($day_dates[$day_name])): ?><br><small style="font-weight:normal;font-size:8px;"><?php echo $day_dates[$day_name]; ?></small><?php endif; ?>
      </th>
      <?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($periods as $period): ?>
    <?php if ($period->is_break): ?>
    <tr class="break-row">
      <td class="time-col">
        <span class="period-name"><?php echo htmlspecialchars($period->name); ?></span>
        <span class="period-time"><?php echo date('h:i', strtotime($period->start_time)).' - '.date('h:i', strtotime($period->end_time)); ?></span>
      </td>
      <td colspan="<?php echo count($days); ?>"><?php echo htmlspecialchars($period->break_label ?: $period->name); ?></td>
    </tr>
    <?php else: ?>
    <tr>
      <td class="time-col">
        <span class="period-name"><?php echo htmlspecialchars($period->name); ?></span>
        <span class="period-time"><?php echo date('h:i', strtotime($period->start_time)).' - '.date('h:i', strtotime($period->end_time)); ?></span>
      </td>
      <?php foreach ($days as $day_name => $dv): ?>
      <?php $entry = $entry_map[$day_name][$period->id] ?? null; ?>
      <td>
        <?php if ($entry): ?>
          <?php
            $tc        = $type_class[strtolower($entry->subject_type ?? 'other')] ?? 'slot-other';
            $slot_style = !empty($entry->tt_color) ? 'style="background:'.htmlspecialchars($entry->tt_color).'"' : '';
            $slot_class = !empty($entry->tt_color) ? '' : $tc;
            $slot_text  = !empty($entry->tt_abbr) ? $entry->tt_abbr : ($entry->subject_code ?: $entry->subject_name);
          ?>
          <span class="slot-tag <?php echo $slot_class; ?>" <?php echo $slot_style; ?>><?php echo htmlspecialchars($slot_text); ?></span>
          <span class="cls-name"><?php echo htmlspecialchars($entry->class_name.' '.$entry->section_name); ?></span>
          <?php if (!empty($entry->room_name)): ?>
            <span class="room-name"><?php echo htmlspecialchars($entry->room_name); ?></span>
          <?php endif; ?>
        <?php else: ?>&nbsp;<?php endif; ?>
      </td>
      <?php endforeach; ?>
    </tr>
    <?php endif; ?>
  <?php endforeach; ?>
  </tbody>
</table>

<div class="print-footer">
  <span style="float:left;">Page <?php echo $num; ?> of <?php echo $total; ?></span>
  Printed: <?php echo date('d M Y, h:i A'); ?>
</div>

</div>
<?php endforeach; ?>

<script>window.onload = function(){ window.print(); };</script>
</body>
</html>
