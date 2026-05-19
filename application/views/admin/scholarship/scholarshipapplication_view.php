<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-graduation-cap"></i> Scholarship Application — Detail</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="<?php echo site_url('admin/scholarshipapplication'); ?>">Scholarship Applications</a></li>
            <li class="active">View</li>
        </ol>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>
        <?php
        $app = $application;
        $badges = ['pending'=>'warning','verified'=>'info','approved'=>'success','rejected'=>'danger'];
        $badge  = $badges[$app['status']] ?? 'default';
        ?>

        <div class="row">
            <!-- Left: details -->
            <div class="col-md-7">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Application Details</h3>
                        <span class="pull-right label label-<?php echo $badge; ?>" style="font-size:13px;padding:5px 10px">
                            <?php echo ucfirst($app['status']); ?>
                        </span>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-condensed">
                            <tr><th style="width:35%">Reference No</th><td><?php echo htmlspecialchars($app['reference_no'] ?? ''); ?></td></tr>
                            <tr><th>Applicant Name</th><td><?php echo htmlspecialchars(($app['firstname'] ?? '') . ' ' . ($app['lastname'] ?? '')); ?></td></tr>
                            <tr><th>Mobile</th><td><?php echo htmlspecialchars($app['mobileno'] ?? ''); ?></td></tr>
                            <tr><th>Email</th><td><?php echo htmlspecialchars($app['email'] ?? ''); ?></td></tr>
                            <tr><th>Scholarship Type</th><td><?php echo htmlspecialchars($app['scholarship_name'] ?? ''); ?></td></tr>
                            <tr><th>Applicant Remarks</th><td><?php echo nl2br(htmlspecialchars($app['applicant_remarks'] ?? '')); ?></td></tr>
                            <tr><th>Supporting Document</th>
                                <td>
                                    <?php if (!empty($app['document'])): ?>
                                        <a href="<?php echo site_url('admin/scholarshipapplication/download/' . $app['id']); ?>"
                                           class="btn btn-xs btn-info" target="_blank">
                                            <i class="fa fa-download"></i> Download Document
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No document uploaded</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr><th>Applied On</th><td><?php echo date('d M Y h:i A', strtotime($app['created_at'])); ?></td></tr>
                        </table>

                        <!-- Verification info -->
                        <?php if (!empty($app['verifier_id'])): ?>
                        <hr/>
                        <h4><i class="fa fa-check-circle text-info"></i> Verification</h4>
                        <table class="table table-condensed">
                            <tr><th style="width:35%">Verified By</th><td><?php echo htmlspecialchars($app['verifier_name'] ?? 'Staff #' . $app['verifier_id']); ?></td></tr>
                            <tr><th>Verified On</th><td><?php echo $app['verified_at'] ? date('d M Y h:i A', strtotime($app['verified_at'])) : '—'; ?></td></tr>
                            <tr><th>Verifier Remarks</th><td><?php echo nl2br(htmlspecialchars($app['verifier_remarks'] ?? '')); ?></td></tr>
                        </table>
                        <?php endif; ?>

                        <!-- Approval info -->
                        <?php if (!empty($app['approver_id'])): ?>
                        <hr/>
                        <h4><i class="fa fa-thumbs-up text-success"></i> Approval</h4>
                        <table class="table table-condensed">
                            <tr><th style="width:35%">Actioned By</th><td><?php echo htmlspecialchars($app['approver_name'] ?? 'Staff #' . $app['approver_id']); ?></td></tr>
                            <tr><th>Actioned On</th><td><?php echo $app['approved_at'] ? date('d M Y h:i A', strtotime($app['approved_at'])) : '—'; ?></td></tr>
                            <tr><th>Approver Remarks</th><td><?php echo nl2br(htmlspecialchars($app['approver_remarks'] ?? '')); ?></td></tr>
                        </table>
                        <?php endif; ?>
                    </div>
                    <div class="box-footer">
                        <a href="<?php echo site_url('admin/scholarshipapplication'); ?>" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right: action panels -->
            <div class="col-md-5">

                <!-- Verify panel (shown only to the designated verifier when status=pending) -->
                <?php if ($can_verify && $app['status'] === 'pending'): ?>
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-check"></i> Verify Application</h3>
                    </div>
                    <form action="<?php echo site_url('admin/scholarshipapplication/verify/' . $app['id']); ?>" method="post">
                        <div class="box-body">
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="verifier_remarks" class="form-control" rows="3" placeholder="Add your remarks..."></textarea>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" name="action" value="verified" class="btn btn-info">
                                <i class="fa fa-check-circle"></i> Mark Verified
                            </button>
                            <button type="submit" name="action" value="rejected" class="btn btn-danger pull-right"
                                    onclick="return confirm('Reject this application?')">
                                <i class="fa fa-times"></i> Reject
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Approve panel (shown only to the designated approver when status=verified) -->
                <?php if ($can_approve && $app['status'] === 'verified'): ?>
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-thumbs-up"></i> Approve / Reject Application</h3>
                    </div>
                    <form action="<?php echo site_url('admin/scholarshipapplication/approve/' . $app['id']); ?>" method="post">
                        <div class="box-body">
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="approver_remarks" class="form-control" rows="3" placeholder="Add your decision remarks..."></textarea>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" name="action" value="approved" class="btn btn-success">
                                <i class="fa fa-thumbs-up"></i> Approve
                            </button>
                            <button type="submit" name="action" value="rejected" class="btn btn-danger pull-right"
                                    onclick="return confirm('Reject this application?')">
                                <i class="fa fa-times"></i> Reject
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Status message if no action available -->
                <?php if (!$can_verify && !$can_approve): ?>
                <div class="callout callout-default">
                    <p><i class="fa fa-info-circle"></i> You are in view-only mode. Only the designated verifier and approver can take action on this application.</p>
                </div>
                <?php endif; ?>

                <?php if ($app['status'] === 'approved'): ?>
                <div class="callout callout-success">
                    <h4><i class="fa fa-check-circle"></i> This scholarship has been Approved.</h4>
                </div>
                <?php elseif ($app['status'] === 'rejected'): ?>
                <div class="callout callout-danger">
                    <h4><i class="fa fa-times-circle"></i> This application has been Rejected.</h4>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </section>
</div>
