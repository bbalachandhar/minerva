<?php
// Build room × day × period map
$room_map = [];
foreach ($data as $r) {
    $room_map[$r->room_name][$r->day][$r->period_id] = $r;
}
?>
<?php if (empty($rooms)): ?>
<div class="alert alert-warning">No rooms configured. Please add rooms in Room Setup first.</div>
<?php else: ?>
<table class="table table-bordered" style="font-size:11px;">
  <thead>
    <tr style="background:#3c8dbc;color:#fff;">
      <th>Room</th><th>Type</th><th>Cap.</th>
      <?php foreach ($days as $dk=>$dv): foreach ($periods as $p): if (!$p->is_break): ?>
      <th style="text-align:center;white-space:nowrap;"><?php echo substr($dk,0,3).'<br>'.$p->name; ?></th>
      <?php endif; endforeach; endforeach; ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rooms as $room):
      // Count utilization
      $used = 0; $total = 0;
      foreach ($days as $dk=>$dv) { foreach ($periods as $p) { if (!$p->is_break) { $total++; if (!empty($room_map[$room->name][$dk][$p->id])) $used++; } } }
      $util_pct = $total > 0 ? round(($used/$total)*100) : 0;
      $color = $util_pct >= 80 ? 'danger' : ($util_pct >= 50 ? 'warning' : 'success');
    ?>
    <tr>
      <td><strong><?php echo htmlspecialchars($room->name); ?></strong><br>
        <div class="progress progress-xs" style="margin:4px 0 0;">
          <div class="progress-bar progress-bar-<?php echo $color; ?>" style="width:<?php echo $util_pct; ?>%"></div>
        </div>
        <small><?php echo $util_pct; ?>% utilised</small>
      </td>
      <td><span class="label label-default"><?php echo $room->room_type; ?></span></td>
      <td><?php echo $room->capacity; ?></td>
      <?php foreach ($days as $dk=>$dv): foreach ($periods as $p): if (!$p->is_break): ?>
      <?php $e = $room_map[$room->name][$dk][$p->id] ?? null; ?>
      <td style="text-align:center;vertical-align:middle;<?php echo $e ? 'background:#d5f5e3;' : ''; ?>">
        <?php if ($e): ?>
          <small style="font-size:9px;"><strong><?php echo htmlspecialchars($e->class.' '.$e->section); ?></strong><br><?php echo htmlspecialchars($e->subject_name??''); ?></small>
        <?php else: ?><span style="color:#ccc;">—</span><?php endif; ?>
      </td>
      <?php endif; endforeach; endforeach; ?>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
