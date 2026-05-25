<?php
$app    = $application;
$badges = ['pending'=>'warning','verified'=>'info','approved'=>'success','rejected'=>'danger'];
$badge  = $badges[$app['status']] ?? 'default';
// When loaded standalone (direct URL), wrap in full layout containers
$is_ajax = defined('BASEPATH') && isset($_SERVER['HTTP_X_REQUESTED_WITH'])
           && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$is_ajax): ?>
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
<?php endif; ?>

        <?php if ($is_ajax): ?><?php echo $this->session->flashdata('msg'); ?><?php endif; ?>

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
                            <tr><th>Scholarship Amount</th>
                                <td>
                                    <?php
                                    $eff_amount = isset($app['override_amount']) && $app['override_amount'] !== null
                                        ? $app['override_amount'] : ($app['scholarship_amount'] ?? null);
                                    if ($eff_amount !== null && $eff_amount !== ''):
                                        echo '<strong>&#8377; ' . number_format((float)$eff_amount, 2) . '</strong>';
                                        if (isset($app['override_amount']) && $app['override_amount'] !== null):
                                            echo ' <span class="label label-warning">Overridden</span>';
                                            if (!empty($app['scholarship_amount'])):
                                                echo ' <small class="text-muted">(Type default: &#8377; ' . number_format((float)$app['scholarship_amount'], 2) . ')</small>';
                                            endif;
                                        endif;
                                    else:
                                        echo '<span class="text-muted">&mdash; Not set</span>';
                                    endif;
                                    ?>
                                </td>
                            </tr>
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
                        <?php if ($is_ajax): ?>
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </button>
                        <?php else: ?>
                        <a href="<?php echo site_url('admin/scholarshipapplication'); ?>" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                        <?php endif; ?>
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

                <!-- Amount Override panel (any admin with can_edit) -->
                <?php if ($this->rbac->hasPrivilege('scholarship_application', 'can_edit')): ?>
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-pencil-square-o"></i> Override Scholarship Amount</h3>
                    </div>
                    <form action="<?php echo site_url('admin/scholarshipapplication/override_amount/' . $app['id']); ?>" method="post"
                          onsubmit="return validateOverride(this);">
                        <div class="box-body">
                            <?php if (!empty($app['override_amount'])): ?>
                            <div class="callout callout-warning" style="margin-bottom:10px">
                                <strong>Current override:</strong> &#8377; <?php echo number_format((float)$app['override_amount'], 2); ?><br/>
                                <small class="text-muted"><?php echo nl2br(htmlspecialchars($app['override_comment'] ?? '')); ?></small>
                                <?php if (!empty($app['override_by'])): ?>
                                <br/><small class="text-muted">By staff #<?php echo $app['override_by']; ?> on <?php echo date('d M Y h:i A', strtotime($app['override_at'])); ?></small>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label>Override Amount (&#8377;) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="override_amount" class="form-control"
                                       value="<?php echo htmlspecialchars($app['override_amount'] ?? $app['scholarship_amount'] ?? ''); ?>"
                                       placeholder="Enter new amount" required />
                            </div>
                            <div class="form-group">
                                <label>Reason / Comment <span class="text-danger">* mandatory</span></label>
                                <textarea name="override_comment" class="form-control" rows="3" id="overrideComment"
                                          placeholder="You MUST explain why this amount is being overridden..."></textarea>
                                <small class="text-danger" id="overrideCommentErr" style="display:none">Comment is required before saving an override.</small>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-warning">
                                <i class="fa fa-save"></i> Save Override
                            </button>
                        </div>
                    </form>
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

<?php if (!$is_ajax): ?>
    </section>
</div>
<?php endif; ?>

<script>
function validateOverride(form) {
    var comment = document.getElementById('overrideComment');
    var err     = document.getElementById('overrideCommentErr');
    if (!comment || comment.value.trim() === '') {
        if (err) { err.style.display = 'block'; }
        if (comment) { comment.focus(); }
        return false;
    }
    if (err) { err.style.display = 'none'; }
    return true;
}
</script>
