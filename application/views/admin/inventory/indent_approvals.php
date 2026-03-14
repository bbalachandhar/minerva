<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-check-square-o"></i> Indent Approvals</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Approval Workflow Log</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Indent</th>
                            <th>Requester</th>
                            <th>Request Date</th>
                            <th>Approver Staff</th>
                            <th>Level</th>
                            <th>Decision</th>
                            <th>Decision Date</th>
                            <th>Comments</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($approval_rows)) { foreach ($approval_rows as $row) { ?>
                        <tr>
                            <td><?php echo (int) $row['id']; ?></td>
                            <td><?php echo html_escape((string) $row['indent_no']); ?></td>
                            <td><?php echo html_escape((string) ($row['requested_by_name'] ?? '')); ?></td>
                            <td><?php echo html_escape((string) ($row['request_date'] ?? '')); ?></td>
                            <td><?php echo !empty($row['approver_name']) ? html_escape((string) $row['approver_name']) : (int) $row['approver_staff_id']; ?></td>
                            <td><?php echo (int) $row['approval_level']; ?></td>
                            <td><?php echo html_escape((string) $row['decision']); ?></td>
                            <td><?php echo html_escape((string) $row['decision_date']); ?></td>
                            <td><?php echo html_escape((string) ($row['comments'] ?? '')); ?></td>
                            <td>
                                <?php if (strtolower((string) $row['decision']) === 'pending') { ?>
                                    <form action="<?php echo site_url('admin/inventoryindent/decision/' . (int) $row['id']); ?>" method="post" style="display:inline-block; width:100%;">
                                        <?php echo $this->customlib->getCSRF(); ?>
                                        <input type="text" name="comments" class="form-control input-sm" placeholder="Comments" style="margin-bottom:4px;">
                                        <button type="submit" name="decision" value="approved" class="btn btn-success btn-xs">Approve</button>
                                        <button type="submit" name="decision" value="rejected" class="btn btn-danger btn-xs">Reject</button>
                                    </form>
                                <?php } else { ?>
                                    <span class="text-muted">Completed</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php }} else { ?>
                        <tr><td colspan="10" class="text-muted text-center">No indent approvals assigned.</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
