<?php
// Group data by class+section
$grouped = [];
foreach ($data as $r) {
    $key = $r->class_id . '_' . $r->section_id;
    if (!isset($grouped[$key])) {
        $grouped[$key] = ['class'=>$r->class_name,'section'=>$r->section_name,'entries'=>[]];
    }
    $grouped[$key]['entries'][] = $r;
}
$type_class = ['theory'=>'slot-theory','practical'=>'slot-practical','project'=>'slot-project','other'=>'slot-other'];
?>
<?php foreach ($grouped as $class_data): ?>
<div style="margin-bottom:20px;">
<h4 style="background:#3c8dbc;color:#fff;padding:8px 12px;border-radius:4px;">
  <i class="fa fa-graduation-cap"></i> <?php echo htmlspecialchars($class_data['class'].' — '.$class_data['section']); ?>
</h4>
<?php
$entry_map = [];
foreach ($class_data['entries'] as $e) {
    $entry_map[$e->day][$e->period_id][] = $e;
}
?>
<table class="table table-bordered tt-grid" style="font-size:11px;">
  <thead>
    <tr>
      <th class="time-col" style="background:#3c8dbc;color:#fff;">Period</th>
      <?php foreach ($days as $dk=>$dv): ?><th style="background:#3c8dbc;color:#fff;text-align:center;"><?php echo $dk; ?></th><?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($periods as $p): ?>
    <tr>
      <td class="time-col"><strong><?php echo $p->name; ?></strong><br><small><?php echo date('h:i',strtotime($p->start_time)); ?></small></td>
      <?php if ($p->is_break): ?>
      <td colspan="<?php echo count($days); ?>" style="background:#fffde7;text-align:center;color:#aaa;"><i class="fa fa-coffee"></i> <?php echo $p->break_label ?: $p->name; ?></td>
      <?php else: foreach ($days as $dk=>$dv): ?>
      <td style="text-align:center;vertical-align:middle;min-height:45px;">
        <?php foreach ($entry_map[$dk][$p->id] ?? [] as $e): ?>
          <?php $tc = $type_class[strtolower($e->subject_type??'other')]??'slot-other'; ?>
          <span class="slot-tag <?php echo $tc; ?>" style="font-size:10px;"><?php echo htmlspecialchars($e->subject_code?:$e->subject_name); ?></span><br>
          <small style="font-size:9px;"><?php echo htmlspecialchars($e->staff_name.' '.($e->staff_surname??'')); ?></small>
        <?php endforeach; ?>
        <?php if (empty($entry_map[$dk][$p->id] ?? [])): ?><span style="color:#ccc;">—</span><?php endif; ?>
      </td>
      <?php endforeach; endif; ?>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endforeach; ?>
<?php if (empty($grouped)): ?>
<div class="alert alert-warning text-center">No timetable entries found. Please generate or add a timetable first.</div>
<?php endif; ?>
