<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-calendar"></i> Exam Subject Schedule</h3>
    </div>
    <div class="box-body">
        <?php if (empty($events)): ?>
            <p class="text-muted">No exam events found for this session.</p>
        <?php else: ?>
            <table class="table table-bordered table-hover">
                <thead><tr><th>Exam Event</th><th>Dates</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($events as $evt): ?>
                    <tr>
                        <td><?= htmlspecialchars($evt->exam_group_name ?? $evt->exam ?? '') ?></td>
                        <td><?= htmlspecialchars($evt->date_from ?? '—') ?> to <?= htmlspecialchars($evt->date_to ?? '—') ?></td>
                        <td>
                            <a href="<?= site_url('coe/coe_schedule/manage/' . ($evt->batch_exam_id ?? $evt->id ?? 0)) ?>" class="btn btn-sm btn-primary">
                                <i class="fa fa-calendar-check-o"></i> Manage Schedule
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
