<?php
$status_map = [
    'reported'     => 'label-danger',
    'under_review' => 'label-warning',
    'penalised'    => 'label-default',
    'dismissed'    => 'label-success',
];
$type_labels = [
    'copying'          => 'Copying',
    'mobile_phone'     => 'Mobile Phone',
    'impersonation'    => 'Impersonation',
    'unfair_material'  => 'Unfair Material',
    'communication'    => 'Communication',
    'other'            => 'Other',
];
?>
<div class="row">
    <!-- Incident Details -->
    <div class="col-sm-7">
        <table class="table table-bordered table-condensed" style="font-size:13px;">
            <tr>
                <th style="width:38%;background:#f9f9f9;">Hall Ticket No</th>
                <td><strong><?php echo htmlspecialchars($incident->hall_ticket_no); ?></strong></td>
            </tr>
            <tr>
                <th style="background:#f9f9f9;">Student</th>
                <td><?php echo htmlspecialchars($incident->student_name ?? '—'); ?></td>
            </tr>
            <tr>
                <th style="background:#f9f9f9;">Exam Hall</th>
                <td><?php echo htmlspecialchars($incident->hall_name ?? '—'); ?></td>
            </tr>
            <tr>
                <th style="background:#f9f9f9;">Exam Date</th>
                <td><?php echo date('d M Y', strtotime($incident->exam_date)); ?> (<?php echo $incident->session_slot; ?>)</td>
            </tr>
            <tr>
                <th style="background:#f9f9f9;">Incident Type</th>
                <td><span class="label label-danger"><?php echo $type_labels[$incident->incident_type] ?? ucfirst($incident->incident_type); ?></span></td>
            </tr>
            <tr>
                <th style="background:#f9f9f9;">Status</th>
                <td>
                    <span class="label <?php echo $status_map[$incident->status] ?? 'label-default'; ?>">
                        <?php echo ucwords(str_replace('_', ' ', $incident->status)); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th style="background:#f9f9f9;">Description</th>
                <td><?php echo nl2br(htmlspecialchars($incident->description ?? '—')); ?></td>
            </tr>
            <tr>
                <th style="background:#f9f9f9;">Material Seized</th>
                <td><?php echo nl2br(htmlspecialchars($incident->material_seized ?? '—')); ?></td>
            </tr>
            <tr>
                <th style="background:#f9f9f9;">Reported By</th>
                <td><?php echo htmlspecialchars($incident->reported_by_name ?? '—'); ?></td>
            </tr>
            <tr>
                <th style="background:#f9f9f9;">Reported At</th>
                <td><?php echo date('d M Y h:i A', strtotime($incident->created_at)); ?></td>
            </tr>
            <?php if ($incident->witness_name ?? null): ?>
            <tr>
                <th style="background:#f9f9f9;">Witness</th>
                <td><?php echo htmlspecialchars($incident->witness_name); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($incident->penalty ?? null): ?>
            <tr>
                <th style="background:#f9f9f9;">Penalty</th>
                <td><?php echo nl2br(htmlspecialchars($incident->penalty)); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Update Status -->
    <?php if ($this->rbac->hasPrivilege('coe_ufm', 'can_edit')): ?>
    <div class="col-sm-5">
        <div class="box box-warning box-solid" style="margin-bottom:0;">
            <div class="box-header with-border" style="padding:8px 12px;">
                <h4 class="box-title" style="font-size:13px;"><i class="fa fa-pencil"></i> Update Status</h4>
            </div>
            <div class="box-body" style="padding:12px;">
                <form method="post" action="<?php echo site_url('coe/coe_ufm/review/' . $incident->id); ?>">
                    <div class="form-group">
                        <label style="font-size:12px;">Status</label>
                        <select name="status" class="form-control input-sm" required>
                            <option value="reported"     <?php echo $incident->status=='reported'     ? 'selected' : ''; ?>>Reported</option>
                            <option value="under_review" <?php echo $incident->status=='under_review' ? 'selected' : ''; ?>>Under Review</option>
                            <option value="penalised"    <?php echo $incident->status=='penalised'    ? 'selected' : ''; ?>>Penalised</option>
                            <option value="dismissed"    <?php echo $incident->status=='dismissed'    ? 'selected' : ''; ?>>Dismissed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px;">Penalty / Remarks</label>
                        <textarea name="penalty" class="form-control input-sm" rows="4"
                            placeholder="Describe outcome..."><?php echo htmlspecialchars($incident->penalty ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm btn-block">
                        <i class="fa fa-save"></i> Update
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
