<?php if (empty($rows)): ?>
<div class="text-center" style="padding:36px 20px;">
  <i class="fa fa-inbox fa-3x" style="color:#ccc;"></i>
  <p class="text-muted" style="margin-top:14px;font-size:14px;font-weight:600;">No subject loads found</p>
  <p class="text-muted" style="font-size:12px;margin-bottom:16px;">
    Subject loads must be configured before lessons appear here.<br>
    Go to <a href="<?php echo site_url('admin/tt/subject_load'); ?>"><strong>Subject Load</strong></a> to assign subjects and teachers to each class.
  </p>
</div>
<?php return; endif; ?>
<div class="table-responsive">
<table class="table table-bordered table-hover table-condensed" style="font-size:12px;">
  <thead>
    <tr style="background:#3c8dbc;color:#fff;">
      <th>Class</th>
      <th>Section</th>
      <th>Subject</th>
      <th>Code</th>
      <th>Type</th>
      <th>Teacher</th>
      <th>Emp. ID</th>
      <th>P/W</th>
      <th>Consec.</th>
      <th>Max/Day</th>
      <th>On1</th>
      <th>Batch</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $type_badge = ['theory'=>'label-primary','practical'=>'label-danger','project'=>'label-warning','other'=>'label-default'];
    foreach ($rows as $r):
    ?>
    <tr>
      <td><?php echo htmlspecialchars($r->class_name ?? ''); ?></td>
      <td><?php echo htmlspecialchars($r->section_name ?? ''); ?></td>
      <td><strong><?php echo htmlspecialchars($r->subject_name ?? ''); ?></strong></td>
      <td><?php echo htmlspecialchars($r->subject_code ?? ''); ?></td>
      <td><span class="label <?php echo $type_badge[strtolower($r->subject_type ?? 'other')] ?? 'label-default'; ?>"><?php echo $r->subject_type ?? ''; ?></span></td>
      <td><?php echo $r->staff_name ? htmlspecialchars($r->staff_name.' '.$r->staff_surname) : '<span class="text-danger">— Missing —</span>'; ?></td>
      <td><?php echo htmlspecialchars($r->employee_id ?? ''); ?></td>
      <td><strong><?php echo (int)$r->periods_per_week; ?></strong></td>
      <td><?php echo (int)$r->consecutive_periods; ?></td>
      <td><?php echo (int)($r->max_per_day ?? 2); ?></td>
      <td><?php echo !empty($r->min_per_day) ? '<span class="label label-info">On1</span>' : ''; ?></td>
      <td><?php echo $r->batch_name ? htmlspecialchars($r->batch_name) : 'Full Class'; ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
