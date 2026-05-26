<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-calendar-check-o"></i> Manage Exam Schedule
            <small><?php echo htmlspecialchars($event->exam_group_name ?? $event->exam ?? ''); ?></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_dashboard'); ?>"><i class="fa fa-home"></i> CoE</a></li>
            <li><a href="<?php echo site_url('coe/coe_schedule'); ?>">Exam Schedule</a></li>
            <li class="active"><?php echo htmlspecialchars($event->exam ?? ''); ?></li>
        </ol>
    </section>

    <section class="content">

        <a href="<?php echo site_url('coe/coe_schedule'); ?>" class="btn btn-default btn-sm" style="margin-bottom:14px;">
            <i class="fa fa-arrow-left"></i> Back to Schedule List
        </a>
        &nbsp;
        <a href="<?php echo site_url('coe/coe_schedule/print_pdf/' . $batch_exam_id); ?>" target="_blank" class="btn btn-danger btn-sm" style="margin-bottom:14px;">
            <i class="fa fa-file-pdf-o"></i> Export PDF
        </a>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-calendar"></i> Subject Schedule — <?php echo htmlspecialchars($event->exam ?? ''); ?></h3>
            </div>
            <div class="box-body">

                <div id="save-msg"></div>

                <?php if (empty($subjects)): ?>
                    <p class="text-muted text-center" style="padding:20px 0;">
                        <i class="fa fa-book fa-2x" style="display:block;margin-bottom:8px;"></i>
                        No subjects found for this exam event.
                    </p>
                <?php else: ?>

                <form id="schedule-form">
                    <input type="hidden" name="batch_exam_id" value="<?php echo $batch_exam_id; ?>">
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" style="font-size:13px">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Exam Date <span class="text-danger">*</span></th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Session</th>
                                    <th>Hall</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjects as $sub): ?>
                                <?php $s = $schedule_idx[$sub->subject_id] ?? null; ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($sub->subject_code); ?></strong><br>
                                        <small><?php echo htmlspecialchars($sub->subject_name); ?></small>
                                    </td>
                                    <td>
                                        <input type="date" name="schedule[<?php echo $sub->subject_id; ?>][exam_date]"
                                            class="form-control input-sm"
                                            value="<?php echo $s ? htmlspecialchars($s->exam_date ?? '') : ''; ?>">
                                    </td>
                                    <td>
                                        <input type="time" name="schedule[<?php echo $sub->subject_id; ?>][start_time]"
                                            class="form-control input-sm"
                                            value="<?php echo $s ? htmlspecialchars($s->start_time ?? '') : ''; ?>">
                                    </td>
                                    <td>
                                        <input type="time" name="schedule[<?php echo $sub->subject_id; ?>][end_time]"
                                            class="form-control input-sm"
                                            value="<?php echo $s ? htmlspecialchars($s->end_time ?? '') : ''; ?>">
                                    </td>
                                    <td>
                                        <select name="schedule[<?php echo $sub->subject_id; ?>][session_slot]" class="form-control input-sm">
                                            <option value="FN" <?php echo ($s && $s->session_slot === 'FN') ? 'selected' : ''; ?>>Forenoon (FN)</option>
                                            <option value="AN" <?php echo ($s && $s->session_slot === 'AN') ? 'selected' : ''; ?>>Afternoon (AN)</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="schedule[<?php echo $sub->subject_id; ?>][hall_id]" class="form-control input-sm">
                                            <option value="">— Any —</option>
                                            <?php foreach ($halls as $h): ?>
                                            <option value="<?php echo $h->id; ?>" <?php echo ($s && (int)$s->hall_id === (int)$h->id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($h->hall_name); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="schedule[<?php echo $sub->subject_id; ?>][notes]"
                                            class="form-control input-sm"
                                            value="<?php echo $s ? htmlspecialchars($s->notes ?? '') : ''; ?>"
                                            placeholder="Optional">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <button type="button" id="save-schedule" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Schedule
                    </button>
                    &nbsp;
                    <a href="<?php echo site_url('coe/coe_schedule'); ?>" class="btn btn-default">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </form>

                <?php endif; ?>

            </div>
        </div>

    </section>
</div>

<script>
$(function () {
    $('#save-schedule').on('click', function () {
        var btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        $.ajax({
            url: '<?php echo site_url('coe/coe_schedule/save_schedule'); ?>',
            method: 'POST',
            data: $('#schedule-form').serialize(),
            dataType: 'json',
            success: function (res) {
                var cls = res.status === 'success' ? 'alert-success' : 'alert-danger';
                $('#save-msg').html('<div class="alert ' + cls + '">' + res.msg + '</div>');
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Schedule');
            },
            error: function () {
                $('#save-msg').html('<div class="alert alert-danger">Server error.</div>');
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Schedule');
            }
        });
    });
});
</script>

    <div class="box-body">

        <div id="save-msg"></div>

        <?php if (empty($subjects)): ?>
            <p class="text-muted">No subjects found for this exam event.</p>
        <?php else: ?>

        <form id="schedule-form">
            <input type="hidden" name="batch_exam_id" value="<?= $batch_exam_id ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

            <div class="table-responsive">
                <table class="table table-bordered table-hover" style="font-size:13px">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Date <span class="text-danger">*</span></th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Session</th>
                            <th>Hall</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $sub): ?>
                        <?php $s = $schedule_idx[$sub->subject_id] ?? null; ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($sub->subject_code) ?></strong><br>
                                <small><?= htmlspecialchars($sub->subject_name) ?></small>
                            </td>
                            <td>
                                <input type="date" name="schedule[<?= $sub->subject_id ?>][exam_date]"
                                    class="form-control input-sm"
                                    value="<?= $s ? htmlspecialchars($s->exam_date ?? '') : '' ?>">
                            </td>
                            <td>
                                <input type="time" name="schedule[<?= $sub->subject_id ?>][start_time]"
                                    class="form-control input-sm"
                                    value="<?= $s ? htmlspecialchars($s->start_time ?? '') : '' ?>">
                            </td>
                            <td>
                                <input type="time" name="schedule[<?= $sub->subject_id ?>][end_time]"
                                    class="form-control input-sm"
                                    value="<?= $s ? htmlspecialchars($s->end_time ?? '') : '' ?>">
                            </td>
                            <td>
                                <select name="schedule[<?= $sub->subject_id ?>][session_slot]" class="form-control input-sm">
                                    <option value="FN" <?= ($s && $s->session_slot === 'FN') ? 'selected' : '' ?>>Forenoon (FN)</option>
                                    <option value="AN" <?= ($s && $s->session_slot === 'AN') ? 'selected' : '' ?>>Afternoon (AN)</option>
                                </select>
                            </td>
                            <td>
                                <select name="schedule[<?= $sub->subject_id ?>][hall_id]" class="form-control input-sm">
                                    <option value="">— Any —</option>
                                    <?php foreach ($halls as $h): ?>
                                    <option value="<?= $h->id ?>" <?= ($s && (int)$s->hall_id === (int)$h->id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($h->hall_name) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="schedule[<?= $sub->subject_id ?>][notes]"
                                    class="form-control input-sm"
                                    value="<?= $s ? htmlspecialchars($s->notes ?? '') : '' ?>"
                                    placeholder="Optional notes">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button type="button" id="save-schedule" class="btn btn-primary">
                <i class="fa fa-save"></i> Save Schedule
            </button>
        </form>

        <?php endif; ?>

    </div>
</div>

<script>
$(function () {
    $('#save-schedule').on('click', function () {
        var btn = $(this).prop('disabled', true).text('Saving...');
        $.ajax({
            url: '<?= site_url('coe/coe_schedule/save_schedule') ?>',
            method: 'POST',
            data: $('#schedule-form').serialize(),
            dataType: 'json',
            success: function (res) {
                var cls = res.status === 'success' ? 'alert-success' : 'alert-danger';
                $('#save-msg').html('<div class="alert ' + cls + '">' + res.msg + '</div>');
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Schedule');
            },
            error: function () {
                $('#save-msg').html('<div class="alert alert-danger">Server error.</div>');
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Schedule');
            }
        });
    });
});
</script>
