<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-shield"></i> Flying Squad Visits</h3>
    </div>
    <div class="box-body">
        <?php if (empty($events)): ?>
            <p class="text-muted">No exam events found for this session.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead><tr>
                    <th>#</th>
                    <th>Exam Event</th>
                    <th>Action</th>
                </tr></thead>
                <tbody>
                <?php $i = 1; foreach ($events as $ev): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($ev->exam_group_name ?? $ev->exam ?? $ev->exam_group_class_batch_exam_id ?? 'Event #'.$ev->id) ?></td>
                    <td>
                        <a href="<?= site_url('coe/coe_flyingsquad/manage/' . ($ev->batch_exam_id ?? $ev->id ?? 0)) ?>" class="btn btn-sm btn-primary">
                            <i class="fa fa-shield"></i> Manage Visits
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
