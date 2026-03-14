<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-check-square-o"></i> PO Approvals</h1>
    </section>
    <section class="content">
        <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Pending and Processed Approvals</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>PO No</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Level</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Decision Date</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rows)) { foreach ($rows as $row) { ?>
                            <tr>
                                <td><?php echo (int) $row['id']; ?></td>
                                <td><?php echo html_escape((string) $row['po_no']); ?></td>
                                <td><?php echo html_escape((string) $row['po_date']); ?></td>
                                <td><?php echo html_escape((string) ($row['supplier_name'] ?? '')); ?></td>
                                <td><?php echo (int) $row['approval_level']; ?></td>
                                <td><?php echo html_escape((string) $row['total_amount']); ?></td>
                                <td><?php echo html_escape((string) $row['decision']); ?></td>
                                <td><?php echo html_escape((string) ($row['decision_date'] ?? '')); ?></td>
                                <td class="text-right">
                                    <?php if ((string) $row['decision'] === 'pending') { ?>
                                        <form method="post" action="<?php echo site_url('admin/inventoryprocurement/podecision/' . (int) $row['id']); ?>" class="form-inline" style="display:inline-block;">
                                            <?php echo $this->customlib->getCSRF(); ?>
                                            <input type="text" name="comments" class="form-control input-sm" placeholder="Comments">
                                            <button type="submit" name="decision" value="approved" class="btn btn-xs btn-success">Approve</button>
                                            <button type="submit" name="decision" value="rejected" class="btn btn-xs btn-danger">Reject</button>
                                        </form>
                                    <?php } else { ?>
                                        <span class="text-muted">-</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php }} else { ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">No PO approvals assigned.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
