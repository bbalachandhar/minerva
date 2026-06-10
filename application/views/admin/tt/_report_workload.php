<?php if (empty($data)): ?>
<div class="alert alert-warning text-center">No timetable data found.</div>
<?php else: ?>
<table class="table table-bordered table-hover table-striped" style="font-size:13px;">
  <thead>
    <tr style="background:#3c8dbc;color:#fff;">
      <th>#</th>
      <th>Teacher</th>
      <th>Employee ID</th>
      <th class="text-center">Total Slots</th>
      <th class="text-center">Teaching Periods</th>
      <th class="text-center">Workload Bar</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($data as $i => $r):
      $pct = min(100, round(($r->teaching_periods / 40) * 100));
      $color = $pct >= 80 ? 'danger' : ($pct >= 60 ? 'warning' : 'success');
    ?>
    <tr>
      <td><?php echo $i+1; ?></td>
      <td><strong><?php echo htmlspecialchars($r->name.' '.$r->surname); ?></strong></td>
      <td><?php echo htmlspecialchars($r->employee_id ?? ''); ?></td>
      <td class="text-center"><?php echo $r->total_periods; ?></td>
      <td class="text-center"><strong><?php echo $r->teaching_periods; ?></strong> / week</td>
      <td style="min-width:150px;">
        <div class="progress progress-xs" style="margin:8px 0;">
          <div class="progress-bar progress-bar-<?php echo $color; ?>" style="width:<?php echo $pct; ?>%"></div>
        </div>
        <small><?php echo $r->teaching_periods; ?> periods/week</small>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<div class="callout callout-info" style="font-size:12px;">
  <strong>Note:</strong> Bar is based on 40 periods/week as 100%. Adjust in Teacher Constraints for per-teacher limits.
</div>
<?php endif; ?>
