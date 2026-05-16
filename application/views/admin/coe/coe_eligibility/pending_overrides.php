<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-user-plus"></i> Eligibility Override Requests — <?= htmlspecialchars($event->exam_group_name ?? $event->exam ?? '') ?></h3>
        <div class="box-tools pull-right">
            <a href="<?= site_url('coe/coe_eligibility?batch_exam_id=' . $batch_exam_id) ?>" class="btn btn-sm btn-default">
                <i class="fa fa-arrow-left"></i> Back to Eligibility
            </a>
        </div>
    </div>
    <div class="box-body">

        <div id="action-msg"></div>

        <?php if (empty($requests)): ?>
            <p class="text-muted">No override requests found.</p>
        <?php else: ?>

        <table class="table table-bordered table-hover" style="font-size:13px">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Requested By</th>
                    <th>Reason</th>
                    <th>Requested At</th>
                    <th>Status</th>
                    <th>Approver Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($requests as $r): ?>
                <tr id="row-<?= $r->id ?>">
                    <td><?= $i++ ?></td>
                    <td>
                        <strong><?= htmlspecialchars($r->student_name ?? '—') ?></strong><br>
                        <small><?= htmlspecialchars($r->admission_no ?? '') ?></small>
                    </td>
                    <td><?= htmlspecialchars($r->requested_by_name ?? '—') ?></td>
                    <td><?= htmlspecialchars($r->reason) ?></td>
                    <td><?= date('d M Y h:i A', strtotime($r->requested_at)) ?></td>
                    <td>
                        <?php if ($r->status === 'pending'): ?>
                            <span class="label label-warning">Pending</span>
                        <?php elseif ($r->status === 'approved'): ?>
                            <span class="label label-success">Approved</span>
                        <?php else: ?>
                            <span class="label label-danger">Rejected</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($r->approver_remarks ?? '—') ?></td>
                    <td>
                        <?php if ($r->status === 'pending'): ?>
                        <div class="input-group input-group-sm" style="width:260px">
                            <input type="text" class="form-control remarks-input" placeholder="Remarks (optional)" id="remarks-<?= $r->id ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-success btn-xs" onclick="approveOrReject(<?= $r->id ?>, 'approve')">
                                    <i class="fa fa-check"></i> Approve
                                </button>
                                <button class="btn btn-danger btn-xs" onclick="approveOrReject(<?= $r->id ?>, 'reject')">
                                    <i class="fa fa-times"></i> Reject
                                </button>
                            </span>
                        </div>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php endif; ?>

    </div>
</div>

<script>
function approveOrReject(req_id, action) {
    var remarks = $('#remarks-' + req_id).val();
    var url = '<?= site_url('coe/coe_eligibility') ?>/' + (action === 'approve' ? 'approve_override' : 'reject_override') + '/' + req_id;
    $.ajax({
        url: url,
        method: 'POST',
        data: {
            remarks: remarks,
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(res) {
            var cls = res.status === 'success' ? 'alert-success' : 'alert-danger';
            $('#action-msg').html('<div class="alert ' + cls + '">' + res.msg + '</div>');
            if (res.status === 'success') {
                setTimeout(function() { location.reload(); }, 1200);
            }
        }
    });
}
</script>
