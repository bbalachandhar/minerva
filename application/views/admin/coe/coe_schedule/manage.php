<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-calendar-check-o"></i> Exam Schedule — <?= htmlspecialchars($event->exam_group_name ?? $event->exam ?? '') ?></h3>
        <div class="box-tools pull-right">
            <a href="<?= site_url('coe/coe_schedule') ?>" class="btn btn-sm btn-default">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
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
