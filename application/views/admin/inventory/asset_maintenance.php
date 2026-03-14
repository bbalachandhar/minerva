<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-wrench"></i> Asset Maintenance</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Maintenance Log</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asset ID</th>
                            <th>Type</th>
                            <th>Vendor</th>
                            <th>Opened</th>
                            <th>Closed</th>
                            <th>Status</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($rows)) { foreach ($rows as $row) { ?>
                        <tr>
                            <td><?php echo (int) $row['id']; ?></td>
                            <td><?php echo (int) $row['asset_id']; ?></td>
                            <td><?php echo html_escape((string) $row['maintenance_type']); ?></td>
                            <td><?php echo html_escape((string) $row['vendor_name']); ?></td>
                            <td><?php echo html_escape((string) $row['opened_on']); ?></td>
                            <td><?php echo html_escape((string) $row['closed_on']); ?></td>
                            <td><?php echo html_escape((string) $row['status']); ?></td>
                            <td><?php echo html_escape((string) $row['cost_amount']); ?></td>
                        </tr>
                    <?php }} else { ?>
                        <tr><td colspan="8" class="text-muted text-center">No maintenance records yet.</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
