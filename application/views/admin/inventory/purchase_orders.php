<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-shopping-cart"></i> Purchase Orders</h1>
    </section>
    <section class="content">
        <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">PO Register</h3>
                <div class="box-tools pull-right">
                    <?php if (!empty($has_po_approval)) { ?>
                        <a href="<?php echo site_url('admin/inventoryprocurement/poapprovals'); ?>" class="btn btn-info btn-sm">
                            <i class="fa fa-check-square-o"></i> PO Approvals
                        </a>
                    <?php } ?>
                    <a href="<?php echo site_url('admin/inventoryprocurement/createpo'); ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Create PO
                    </a>
                </div>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>PO No</th>
                            <th>Indent</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th>Subtotal</th>
                            <th>Tax</th>
                            <th>Total</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($rows)) { foreach ($rows as $row) { ?>
                        <tr>
                            <td><?php echo (int) $row['id']; ?></td>
                            <td><?php echo html_escape((string) $row['po_no']); ?></td>
                            <td><?php echo html_escape((string) ($row['indent_no'] ?? '')); ?></td>
                            <td><?php echo html_escape((string) $row['po_date']); ?></td>
                            <td><?php echo !empty($row['supplier_name']) ? html_escape((string) $row['supplier_name']) : (int) $row['supplier_id']; ?></td>
                            <td><?php echo html_escape((string) $row['status']); ?></td>
                            <td><?php echo html_escape((string) $row['subtotal']); ?></td>
                            <td><?php echo html_escape((string) $row['tax_amount']); ?></td>
                            <td><?php echo html_escape((string) $row['total_amount']); ?></td>
                            <td class="text-right">
                                <?php if ((string) $row['status'] === 'approved' || (string) $row['status'] === 'partially_received') { ?>
                                    <a href="<?php echo site_url('admin/inventoryprocurement/creategrn?po_id=' . (int) $row['id']); ?>" class="btn btn-xs btn-success">
                                        <i class="fa fa-truck"></i> Receive
                                    </a>
                                <?php } else { ?>
                                    <span class="text-muted">-</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php }} else { ?>
                        <tr><td colspan="10" class="text-muted text-center">No purchase orders yet.</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
