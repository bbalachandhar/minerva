<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-truck"></i> Goods Receipts (GRN)</h1>
    </section>
    <section class="content">
        <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">GRN Register</h3>
                <div class="box-tools pull-right">
                    <a href="<?php echo site_url('admin/inventoryprocurement/creategrn'); ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Create GRN
                    </a>
                </div>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>GRN No</th>
                            <th>Date</th>
                            <th>PO No</th>
                            <th>PO ID</th>
                            <th>Received By</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($rows)) { foreach ($rows as $row) { ?>
                        <tr>
                            <td><?php echo (int) $row['id']; ?></td>
                            <td><?php echo html_escape((string) $row['grn_no']); ?></td>
                            <td><?php echo html_escape((string) $row['grn_date']); ?></td>
                            <td><?php echo html_escape((string) ($row['po_no'] ?? '')); ?></td>
                            <td><?php echo (int) $row['po_id']; ?></td>
                            <td><?php echo !empty($row['receiver_name']) ? html_escape((string) $row['receiver_name']) : (int) $row['received_by']; ?></td>
                            <td><?php echo html_escape((string) $row['status']); ?></td>
                        </tr>
                    <?php }} else { ?>
                        <tr><td colspan="7" class="text-muted text-center">No GRN records yet.</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
