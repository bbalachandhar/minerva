<div class="content-wrapper">
<section class="content-header">
    <h1><?php echo $this->lang->line('class_timetable'); ?></h1>
</section>
<section class="content">
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">
            <i class="fa fa-table"></i>
            <?php echo htmlspecialchars($class_label . ' ' . $section_label); ?> &mdash; <?php echo $this->lang->line('class_timetable'); ?>
        </h3>
        <div class="box-tools pull-right">
            <?php if (!empty($periods)): ?>
            <button class="btn btn-default btn-sm" id="btn-print-tt" title="<?php echo $this->lang->line('print'); ?>">
                <i class="fa fa-print"></i> <?php echo $this->lang->line('print'); ?>
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="box-body table-responsive p-0">

    <?php if (empty($periods)): ?>
        <div class="text-center text-muted" style="padding:40px;">
            <i class="fa fa-calendar-o fa-3x" style="color:#ddd;"></i>
            <p style="margin-top:12px;"><?php echo $this->lang->line('no_record_found'); ?></p>
        </div>
    <?php else: ?>

    <?php
    $type_class = [
        'theory'    => 'slot-theory',
        'practical' => 'slot-practical',
        'project'   => 'slot-project',
        'other'     => 'slot-other',
    ];
    $today = date('l'); // Monday, Tuesday, etc.
    ?>

    <style>
    .tt-student-grid th, .tt-student-grid td { vertical-align: middle; text-align: center; font-size: 12px; }
    .tt-student-grid .time-col { text-align: left; background: #f9f9f9; white-space: nowrap; min-width: 80px; }
    .tt-student-grid .today-col { background: #fffde7; }
    .tt-student-grid th.today-col { background: #f0a500; color: #fff; }
    .tt-student-grid .break-row td { background: #f4f4f4; color: #999; font-style: italic; }
    .slot-tag { display: inline-block; border-radius: 3px; padding: 2px 7px; font-size: 11px; font-weight: 600; color: #fff; }
    .slot-theory    { background: #3498db; }
    .slot-practical { background: #e74c3c; }
    .slot-project   { background: #f39c12; }
    .slot-free      { background: #27ae60; }
    .slot-other     { background: #7f8c8d; }
    .slot-sub { display: block; font-size: 10px; color: #555; margin-top: 2px; }
    .slot-room { display: block; font-size: 10px; color: #888; }
    </style>

    <table class="table table-bordered tt-student-grid">
        <thead>
            <tr>
                <th class="time-col"><?php echo $this->lang->line('time'); ?></th>
                <?php foreach ($days as $day_name => $day_val): ?>
                <th class="<?php echo ($day_name === $today) ? 'today-col' : ''; ?>">
                    <?php echo $this->lang->line(strtolower($day_name)); ?>
                    <?php if ($day_name === $today): ?><br><small style="font-size:10px;"><?php echo $this->lang->line('today'); ?></small><?php endif; ?>
                </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($periods as $period): ?>
            <?php if ($period->is_break): ?>
            <tr class="break-row">
                <td class="time-col">
                    <strong><?php echo htmlspecialchars($period->name); ?></strong><br>
                    <small><?php echo date('h:i A', strtotime($period->start_time)); ?></small>
                </td>
                <td colspan="<?php echo count($days); ?>">
                    <i class="fa fa-coffee"></i> <?php echo htmlspecialchars($period->break_label ?? $period->name); ?>
                </td>
            </tr>
            <?php else: ?>
            <tr>
                <td class="time-col">
                    <strong><?php echo htmlspecialchars($period->name); ?></strong><br>
                    <small><?php echo date('h:i', strtotime($period->start_time)) . ' - ' . date('h:i', strtotime($period->end_time)); ?></small>
                </td>
                <?php foreach ($days as $day_name => $day_val): ?>
                <?php $entry = $entry_map[$day_name][$period->id] ?? null; ?>
                <td class="<?php echo ($day_name === $today) ? 'today-col' : ''; ?>" style="min-height:52px;">
                    <?php if ($entry): ?>
                        <?php if ($entry->is_free_period): ?>
                            <span class="slot-tag slot-free"><?php echo htmlspecialchars($entry->free_period_label ?: 'Free'); ?></span>
                        <?php else: ?>
                            <?php
                                $tc         = $type_class[strtolower($entry->subject_type ?? 'other')] ?? 'slot-other';
                                $slot_color = !empty($entry->tt_color) ? $entry->tt_color : null;
                                $slot_text  = !empty($entry->tt_abbr) ? $entry->tt_abbr : ($entry->subject_code ?: $entry->subject_name);
                                $slot_style = $slot_color ? "background:{$slot_color};" : '';
                                $slot_cls   = $slot_color ? '' : $tc;
                            ?>
                            <span class="slot-tag <?php echo $slot_cls; ?>" style="<?php echo $slot_style; ?>"><?php echo htmlspecialchars($slot_text); ?></span>
                            <?php $tname = trim(($entry->staff_name ?? '') . ' ' . ($entry->staff_surname ?? '')); ?>
                            <?php if ($tname): ?><span class="slot-sub"><?php echo htmlspecialchars($tname); ?></span><?php endif; ?>
                            <?php if (!empty($entry->room_name)): ?><span class="slot-room"><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($entry->room_name); ?></span><?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?><span class="text-muted">&mdash;</span><?php endif; ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php endif; ?>

    </div>
</div>
</section>
</div>

<script>
$(document).on('click', '#btn-print-tt', function() {
    var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('user/timetable/printclasstimetable'); ?>', {}, function(res) {
        $btn.prop('disabled', false).html('<i class="fa fa-print"></i> <?php echo addslashes($this->lang->line('print')); ?>');
        if (res.status === '1') {
            var w = window.open('', 'TimetablePrint');
            w.document.open();
            w.document.write(res.page);
            w.document.close();
        }
    }, 'json').fail(function() {
        $btn.prop('disabled', false).html('<i class="fa fa-print"></i> <?php echo addslashes($this->lang->line('print')); ?>');
    });
});
</script>
