<?php $type_class = ['theory'=>'slot-theory','practical'=>'slot-practical','project'=>'slot-project','other'=>'slot-other']; ?>
<div class="box box-default">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-calendar"></i> Weekly Schedule</h3>
    <div class="box-tools">
      <small class="text-muted">Total periods shown: <?php echo count($entry_map) ? array_sum(array_map('count', $entry_map)) : 0; ?></small>
    </div>
  </div>
  <div class="box-body table-responsive p-0">
    <table class="table table-bordered tt-grid">
      <thead>
        <tr>
          <th class="time-col">Time</th>
          <?php foreach ($days as $dk => $dv): ?>
          <th><?php echo $dk; ?><?php if (!empty($day_dates[$dk])): ?><br><small style="font-weight:normal;font-size:10px;"><?php echo $day_dates[$dk]; ?></small><?php endif; ?></th>
          <?php endforeach; ?>
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
          <td colspan="<?php echo count($days); ?>" class="break-row text-center text-muted">
            <i class="fa fa-coffee"></i> <?php echo htmlspecialchars($period->break_label ?: $period->name); ?>
          </td>
          <?php else: foreach ($days as $dk => $dv): ?>
          <?php $entry = $entry_map[$dk][$period->id] ?? null; ?>
          <td class="tt-cell <?php echo $entry ? 'filled' : ''; ?>" style="min-height:60px;text-align:center;vertical-align:middle;">
            <?php if ($entry): ?>
              <?php
                $tc = $type_class[strtolower($entry->subject_type ?? 'other')] ?? 'slot-other';
                $slot_color = !empty($entry->tt_color) ? $entry->tt_color : null;
                $slot_text  = !empty($entry->tt_abbr)  ? $entry->tt_abbr  : ($entry->subject_code ?: $entry->subject_name);
                $slot_style = $slot_color ? "background-color:{$slot_color};color:#fff;" : '';
              ?>
              <span class="slot-tag <?php echo $slot_color ? '' : $tc; ?>" style="<?php echo $slot_style; ?>"><?php echo htmlspecialchars($slot_text); ?></span><br>
              <small style="font-size:11px;"><strong><?php echo htmlspecialchars($entry->class_name); ?> <?php echo htmlspecialchars($entry->section_name); ?></strong></small>
              <?php if ($entry->room_name): ?><br><small style="font-size:10px;color:#777;"><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($entry->room_name); ?></small><?php endif; ?>
              <?php if ($entry->batch_name): ?><br><span class="label label-info" style="font-size:9px;">Batch <?php echo $entry->batch_name; ?></span><?php endif; ?>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <?php endforeach; endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
