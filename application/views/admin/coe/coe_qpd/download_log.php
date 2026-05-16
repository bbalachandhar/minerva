<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-download"></i> QPD Download Log — <?= htmlspecialchars($event->exam_group_name ?? $event->exam ?? '') ?></h3>
        <div class="box-tools pull-right">
            <a href="<?= site_url('coe/coe_qpd/manage/' . $batch_exam_id) ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Back to QPD
            </a>
        </div>
    </div>
    <div class="box-body">

        <?php if (empty($log)): ?>
            <p class="text-muted">No downloads recorded yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>File</th>
                            <th>Downloaded By</th>
                            <th>Designation</th>
                            <th>IP Address</th>
                            <th>Date &amp; Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($log as $row): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row->subject_code ?? '') ?> — <?= htmlspecialchars($row->subject_name ?? '') ?></td>
                            <td><i class="fa fa-file-pdf-o text-danger"></i> <?= htmlspecialchars($row->original_filename ?? '') ?></td>
                            <td><?= htmlspecialchars($row->staff_name ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($row->designation ?? '—') ?></td>
                            <td><code><?= htmlspecialchars($row->ip_address ?? '—') ?></code></td>
                            <td><?= date('d M Y h:i:s A', strtotime($row->downloaded_at)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-muted text-right">Total downloads: <strong><?= count($log) ?></strong></p>
        <?php endif; ?>

    </div>
</div>
