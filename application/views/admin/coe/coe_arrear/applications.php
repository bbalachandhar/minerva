<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-file-text-o"></i> Arrear / Supplementary Applications</h3>
        <div class="box-tools pull-right">
            <a href="<?= site_url('coe/coe_arrear') ?>" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
    </div>
    <div class="box-body">

        <!-- Filters -->
        <form class="form-inline" method="get">
            <div class="form-group">
                <label>Exam Event &nbsp;</label>
                <select name="batch_exam_id" class="form-control input-sm">
                    <option value="">— All Events —</option>
                    <?php foreach ($events as $ev): ?>
                    <option value="<?= $ev->id ?>" <?= $batch_exam_id == $ev->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ev->exam_group_name ?? $ev->exam ?? 'Event #'.$ev->id) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            &nbsp;
            <div class="form-group">
                <label>Status &nbsp;</label>
                <select name="status" class="form-control input-sm">
                    <option value="pending"  <?= $status === 'pending'  ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    <option value="" <?= $status === '' ? 'selected' : '' ?>>All</option>
                </select>
            </div>
            &nbsp;
            <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Filter</button>
        </form>
        <hr>

        <div id="review-msg"></div>

        <?php if (empty($applications)): ?>
            <p class="text-muted">No applications found.</p>
        <?php else: ?>
        <table class="table table-bordered table-hover" style="font-size:13px">
            <thead><tr>
                <th>#</th>
                <th>Student</th>
                <th>Subject</th>
                <th>Type</th>
                <th>Applied At</th>
                <th>Remarks</th>
                <th>Status</th>
                <th>Reviewer</th>
                <th>Reviewer Remarks</th>
                <th>Action</th>
            </tr></thead>
            <tbody>
            <?php $i = 1; foreach ($applications as $a): ?>
            <tr id="approw-<?= $a->id ?>">
                <td><?= $i++ ?></td>
                <td>
                    <strong><?= htmlspecialchars($a->student_name ?? '—') ?></strong><br>
                    <small><?= htmlspecialchars($a->admission_no ?? '') ?></small>
                </td>
                <td><?= htmlspecialchars($a->subject_name ?? '—') ?><br><small><?= htmlspecialchars($a->subject_code ?? '') ?></small></td>
                <td><span class="label label-info"><?= ucfirst($a->application_type) ?></span></td>
                <td><?= date('d M Y', strtotime($a->applied_at)) ?></td>
                <td><?= htmlspecialchars($a->remarks ?? '—') ?></td>
                <td>
                    <?php if ($a->status === 'pending'): ?>
                        <span class="label label-warning">Pending</span>
                    <?php elseif ($a->status === 'approved'): ?>
                        <span class="label label-success">Approved</span>
                    <?php else: ?>
                        <span class="label label-danger">Rejected</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($a->reviewed_by_name ?? '—') ?></td>
                <td><?= htmlspecialchars($a->reviewer_remarks ?? '—') ?></td>
                <td>
                    <?php if ($a->status === 'pending'): ?>
                    <div style="min-width:200px">
                        <input type="text" class="form-control input-sm" id="rm-<?= $a->id ?>" placeholder="Remarks">
                        <div class="btn-group btn-group-xs" style="margin-top:4px">
                            <button class="btn btn-success" onclick="reviewApp(<?= $a->id ?>,'approved')"><i class="fa fa-check"></i> Approve</button>
                            <button class="btn btn-danger"  onclick="reviewApp(<?= $a->id ?>,'rejected')"><i class="fa fa-times"></i> Reject</button>
                        </div>
                    </div>
                    <?php else: ?>—<?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

    </div>
</div>

<script>
function reviewApp(id, action) {
    var remarks = $('#rm-' + id).val();
    $.ajax({
        url: '<?= site_url('coe/coe_arrear/review') ?>/' + id,
        method: 'POST',
        data: {
            action: action,
            remarks: remarks,
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(res) {
            var cls = res.status === 'success' ? 'alert-success' : 'alert-danger';
            $('#review-msg').html('<div class="alert ' + cls + '">' + res.msg + '</div>');
            if (res.status === 'success') setTimeout(function() { location.reload(); }, 1200);
        }
    });
}
</script>
