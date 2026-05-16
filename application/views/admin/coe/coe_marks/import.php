<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-upload"></i> Import Marks from CSV — <?= htmlspecialchars($event->exam_group_name ?? $event->exam ?? '') ?></h3>
        <div class="box-tools pull-right">
            <a href="<?= site_url('coe/coe_marks/import_template/' . $batch_exam_id) ?>" class="btn btn-sm btn-default">
                <i class="fa fa-download"></i> Download Template
            </a>
            <a href="<?= site_url('coe/coe_marks/listing/' . $batch_exam_id) ?>" class="btn btn-sm btn-default">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <div class="box-body">

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($import_result)): ?>
            <div class="alert alert-<?= empty($import_result['errors']) ? 'success' : 'warning' ?>">
                <strong><?= (int) $import_result['imported'] ?> row(s) imported successfully.</strong>
                <?php if (!empty($import_result['errors'])): ?>
                    <ul class="mt-1 mb-0">
                        <?php foreach ($import_result['errors'] as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="callout callout-info">
                    <h4>CSV Format</h4>
                    <p>The CSV must have these columns in order:</p>
                    <ol>
                        <li><code>admission_no</code> — student admission number</li>
                        <li><code>subject_code</code> — subject code as configured</li>
                        <li><code>internal_marks</code> — numeric</li>
                        <li><code>external_marks</code> — numeric</li>
                    </ol>
                    <p>Download the pre-filled template above to get the correct admission numbers and subject codes for this exam event.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-default box-solid">
                    <div class="box-header"><h4 class="box-title">Upload CSV File</h4></div>
                    <div class="box-body">
                        <form method="post" action="<?= site_url('coe/coe_marks/import/' . $batch_exam_id) ?>" enctype="multipart/form-data">
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                            <div class="form-group">
                                <label>CSV File <span class="text-danger">*</span></label>
                                <input type="file" name="marks_csv" accept=".csv" class="form-control" required>
                                <p class="help-block">Max 2 MB. CSV only.</p>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-upload"></i> Import Marks
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($subjects)): ?>
        <h4>Available Subjects in this Event</h4>
        <table class="table table-bordered table-condensed" style="font-size:13px">
            <thead><tr><th>Subject Code</th><th>Subject Name</th><th>Credits</th></tr></thead>
            <tbody>
                <?php foreach ($subjects as $s): ?>
                <tr>
                    <td><code><?= htmlspecialchars($s->subject_code) ?></code></td>
                    <td><?= htmlspecialchars($s->subject_name) ?></td>
                    <td><?= (int)($s->credits ?? 4) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

    </div>
</div>
