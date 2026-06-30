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
          <?php
            $entry    = $entry_map[$dk][$period->id] ?? null;
            $dt_full  = $day_full_dates[$dk] ?? null;
            $absence  = ($dt_full && !empty($absent_map[$dt_full][$period->id]))   ? $absent_map[$dt_full][$period->id]   : null;
            $covering = ($dt_full && !empty($covering_map[$dt_full][$period->id])) ? $covering_map[$dt_full][$period->id] : null;
            $cell_bg  = $absence ? 'background:#fdecea;' : ($covering ? 'background:#fff8e1;' : '');
          ?>
          <td class="tt-cell <?php echo ($entry||$covering) ? 'filled' : ''; ?>" style="min-height:60px;text-align:center;vertical-align:middle;<?php echo $cell_bg; ?>">
            <?php if ($absence): ?>
              <!-- Teacher is absent this day+period — show regular slot dimmed with absent overlay -->
              <?php if ($entry): ?>
                <?php
                  $tc = $type_class[strtolower($entry->subject_type ?? 'other')] ?? 'slot-other';
                  $slot_text = !empty($entry->tt_abbr) ? $entry->tt_abbr : ($entry->subject_code ?: $entry->subject_name);
                ?>
                <span class="slot-tag <?php echo $tc; ?>" style="opacity:.45;"><?php echo htmlspecialchars($slot_text); ?></span><br>
                <?php if ($entry->subject_name): ?><small style="font-size:10px;color:#aaa;text-decoration:line-through;"><?php echo htmlspecialchars($entry->subject_name); ?></small><br><?php endif; ?>
                <small style="font-size:10px;color:#aaa;text-decoration:line-through;"><?php echo htmlspecialchars($entry->class_name.' '.$entry->section_name); ?></small><br>
              <?php endif; ?>
              <div style="margin-top:3px;background:#e74c3c;color:#fff;border-radius:3px;padding:2px 5px;font-size:10px;line-height:1.5;">
                <i class="fa fa-times-circle"></i> ABSENT<br>
                <?php if ($absence->sub_name): ?>
                  <span style="font-size:9px;">Cover: <?php echo htmlspecialchars($absence->sub_name.' '.($absence->sub_surname??'')); ?></span>
                <?php else: ?>
                  <span style="font-size:9px;color:#ffcdd2;">Unassigned</span>
                <?php endif; ?>
              </div>
            <?php elseif ($entry): ?>
              <!-- Regular scheduled slot -->
              <?php
                $tc = $type_class[strtolower($entry->subject_type ?? 'other')] ?? 'slot-other';
                $slot_color = !empty($entry->tt_color) ? $entry->tt_color : null;
                $slot_text  = !empty($entry->tt_abbr)  ? $entry->tt_abbr  : ($entry->subject_code ?: $entry->subject_name);
                $slot_style = $slot_color ? "background-color:{$slot_color};color:#fff;" : '';
              ?>
              <span class="slot-tag <?php echo $slot_color ? '' : $tc; ?>" style="<?php echo $slot_style; ?>"><?php echo htmlspecialchars($slot_text); ?></span><br>
              <?php
                $display_name = $entry->is_free_period
                    ? ($entry->free_period_label ?: '')
                    : ($entry->subject_name ?: ($entry->subject_code ?: ''));
              ?>
              <?php if ($display_name): ?><small style="font-size:10px;color:#444;display:block;line-height:1.3;margin-bottom:2px;"><?php echo htmlspecialchars($display_name); ?></small><?php endif; ?>
              <small style="font-size:11px;"><strong><?php echo htmlspecialchars($entry->class_name); ?> <?php echo htmlspecialchars($entry->section_name); ?></strong></small>
              <?php if ($entry->room_name): ?><br><small style="font-size:10px;color:#777;"><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($entry->room_name); ?></small><?php endif; ?>
              <?php if ($entry->batch_name): ?><br><span class="label label-info" style="font-size:9px;">Batch <?php echo $entry->batch_name; ?></span><?php endif; ?>
              <?php if ($covering): ?>
                <!-- This teacher also has a cover duty here (unusual but handle it) -->
                <div style="margin-top:3px;background:#e67e22;color:#fff;border-radius:3px;padding:1px 4px;font-size:9px;">
                  <i class="fa fa-plus"></i> +Cover: <?php echo htmlspecialchars($covering->class_name.' '.$covering->section_name); ?>
                </div>
              <?php endif; ?>
            <?php elseif ($covering): ?>
              <!-- Free slot but teacher has a cover duty here -->
              <div style="background:#e67e22;color:#fff;border-radius:3px;padding:4px 6px;font-size:10px;line-height:1.5;">
                <i class="fa fa-exchange"></i> <strong>COVER DUTY</strong><br>
                <span style="font-size:9px;"><?php echo htmlspecialchars($covering->class_name.' '.$covering->section_name); ?></span><br>
                <?php if ($covering->subject_name): ?><span style="font-size:9px;"><?php echo htmlspecialchars($covering->subject_name); ?></span><br><?php endif; ?>
                <span style="font-size:9px;color:#ffe0b2;">for <?php echo htmlspecialchars($covering->absent_name.' '.($covering->absent_surname??'')); ?></span>
              </div>
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

<style>
.tt-grid td[style*="background:#fdecea"] { border-left: 3px solid #e74c3c !important; }
.tt-grid td[style*="background:#fff8e1"] { border-left: 3px solid #e67e22 !important; }
</style>
