<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-warning"></i> UFM Incident #<?php echo $incident->id; ?><button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_ufm/listing/' . $incident->batch_exam_id); ?>"><i class="fa fa-arrow-left"></i> Back to Incidents</a></li>
        </ol>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>
        <?php
            $status_map = [
                'reported'     => 'danger',
                'under_review' => 'warning',
                'penalised'    => 'default',
                'dismissed'    => 'success',
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
            <div class="col-md-8">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">Incident Details</h3>
                        <div class="box-tools pull-right">
                            <span class="label label-<?php echo $status_map[$incident->status] ?? 'default'; ?> label-lg">
                                <?php echo ucwords(str_replace('_', ' ', $incident->status)); ?>
                            </span>
                        </div>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered">
                            <tr><th style="width:35%">Hall Ticket No</th><td><strong><?php echo htmlspecialchars($incident->hall_ticket_no); ?></strong></td></tr>
                            <tr><th>Student</th><td><?php echo htmlspecialchars($incident->student_name); ?></td></tr>
                            <tr><th>Exam Hall</th><td><?php echo htmlspecialchars($incident->hall_name ?? '—'); ?></td></tr>
                            <tr><th>Exam Date</th><td><?php echo date('d M Y', strtotime($incident->exam_date)); ?></td></tr>
                            <tr><th>Session</th><td><?php echo $incident->session_slot; ?></td></tr>
                            <tr><th>Incident Type</th>
                                <td><span class="label label-danger"><?php echo $type_labels[$incident->incident_type] ?? ucfirst($incident->incident_type); ?></span></td>
                            </tr>
                            <tr><th>Description</th><td><?php echo nl2br(htmlspecialchars($incident->description ?? '—')); ?></td></tr>
                            <tr><th>Material Seized</th><td><?php echo nl2br(htmlspecialchars($incident->material_seized ?? '—')); ?></td></tr>
                            <tr><th>Reported By</th><td><?php echo htmlspecialchars($incident->reported_by_name ?? '—'); ?></td></tr>
                            <tr><th>Reported At</th><td><?php echo date('d M Y h:i A', strtotime($incident->created_at)); ?></td></tr>
                            <?php if ($incident->penalty): ?>
                            <tr><th>Penalty</th><td><?php echo nl2br(htmlspecialchars($incident->penalty)); ?></td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>

            <?php if ($this->rbac->hasPrivilege('coe_ufm', 'can_edit')): ?>
            <div class="col-md-4">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Update Status</h3>
                    </div>
                    <div class="box-body">
                        <form method="post" action="<?php echo site_url('coe/coe_ufm/review/' . $incident->id); ?>">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="reported"     <?php echo $incident->status=='reported'     ? 'selected' : ''; ?>>Reported</option>
                                    <option value="under_review" <?php echo $incident->status=='under_review' ? 'selected' : ''; ?>>Under Review</option>
                                    <option value="penalised"    <?php echo $incident->status=='penalised'    ? 'selected' : ''; ?>>Penalised</option>
                                    <option value="dismissed"    <?php echo $incident->status=='dismissed'    ? 'selected' : ''; ?>>Dismissed</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Penalty / Remarks</label>
                                <textarea name="penalty" class="form-control" rows="4" placeholder="Describe the penalty or review outcome..."><?php echo htmlspecialchars($incident->penalty ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-warning btn-block">
                                <i class="fa fa-save"></i> Update
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'ufm']); ?>
