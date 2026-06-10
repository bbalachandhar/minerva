<?php
$type_class = ['theory'=>'slot-theory','practical'=>'slot-practical','project'=>'slot-project','other'=>'slot-other'];
?>
<div class="box box-default" data-class="<?php echo $class_id; ?>" data-section="<?php echo $section_id; ?>">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-table"></i> Weekly Timetable</h3>
    <div class="box-tools pull-right">
      <button class="btn btn-xs btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </div>
  </div>
  <div class="box-body table-responsive p-0">
    <table class="table table-bordered tt-grid">
      <thead>
        <tr>
          <th class="time-col">Time</th>
          <?php foreach ($days as $dk => $dv): ?><th><?php echo $dk; ?></th><?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($periods as $period): ?>
        <tr>
          <td class="time-col">
            <strong><?php echo htmlspecialchars($period->name); ?></strong><br>
            <small><?php echo date('h:i', strtotime($period->start_time)) . '<br>' . date('h:i', strtotime($period->end_time)); ?></small>
          </td>

          <?php if ($period->is_break): ?>
          <?php $day_count = count($days); ?>
          <td colspan="<?php echo $day_count; ?>" class="tt-cell break-row text-center text-muted">
            <i class="fa fa-coffee"></i> <?php echo htmlspecialchars($period->break_label ?: $period->name); ?>
          </td>
          <?php else: foreach ($days as $dk => $dv): ?>
          <?php
            $entry = $entry_map[$dk][$period->id][0] ?? null; // 0 = full class
            $is_locked = $entry && $entry->is_locked;
          ?>
          <td class="tt-cell <?php echo $entry ? 'filled' : ''; ?> <?php echo $is_locked ? 'locked-cell' : ''; ?>"
              data-day="<?php echo $dk; ?>"
              data-period="<?php echo $period->id; ?>"
              data-period-name="<?php echo htmlspecialchars($period->name); ?>"
              data-entry-id="<?php echo $entry ? $entry->id : 0; ?>"
              data-locked="<?php echo $entry ? $entry->is_locked : 0; ?>"
              <?php if ($entry): ?>
              data-entry='<?php echo json_encode([
                'sgs_id'    => $entry->subject_group_subject_id,
                'sg_id'     => $entry->subject_group_id,
                'staff_id'  => $entry->staff_id,
                'room_id'   => $entry->room_id,
                'batch_id'  => $entry->batch_id,
                'is_free'   => $entry->is_free_period,
                'free_label'=> $entry->free_period_label,
              ]); ?>'
              <?php endif; ?>>
            <?php if ($entry): ?>
              <?php if ($entry->is_free_period): ?>
                <span class="slot-tag slot-free"><?php echo htmlspecialchars($entry->free_period_label ?: 'Free'); ?></span>
              <?php else: ?>
                <?php $tc = $type_class[strtolower($entry->subject_type ?? 'other')] ?? 'slot-other'; ?>
                <span class="slot-tag <?php echo $tc; ?>"><?php echo htmlspecialchars($entry->subject_code ?: $entry->subject_name); ?></span><br>
                <small style="font-size:10px;"><?php echo htmlspecialchars($entry->staff_name.' '.($entry->staff_surname??'')); ?></small>
                <?php if ($entry->room_name): ?><br><small style="font-size:10px;color:#777;"><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($entry->room_name); ?></small><?php endif; ?>
                <?php if ($entry->batch_name): ?><br><span class="label label-info" style="font-size:9px;">Batch <?php echo $entry->batch_name; ?></span><?php endif; ?>
              <?php endif; ?>
              <?php if ($is_locked): ?><br><i class="fa fa-lock text-danger" style="font-size:10px;"></i><?php endif; ?>
            <?php else: ?>
              <i class="fa fa-plus-circle text-muted" style="font-size:18px;"></i>
            <?php endif; ?>
          </td>
          <?php endforeach; endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
