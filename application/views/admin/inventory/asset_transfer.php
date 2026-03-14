<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-random"></i> Asset Transfer</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Transfer Register</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asset ID</th>
                            <th>From Location</th>
                            <th>To Location</th>
                            <th>Transfer Date</th>
                            <th>Transferred By</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($rows)) { foreach ($rows as $row) { ?>
                        <tr>
                            <td><?php echo (int) $row['id']; ?></td>
                            <td><?php echo (int) $row['asset_id']; ?></td>
                            <td><?php echo (int) $row['from_location_id']; ?></td>
                            <td><?php echo (int) $row['to_location_id']; ?></td>
                            <td><?php echo html_escape((string) $row['transfer_date']); ?></td>
                            <td><?php echo (int) $row['transferred_by']; ?></td>
                            <td><?php echo html_escape((string) $row['status']); ?></td>
                        </tr>
                    <?php }} else { ?>
                        <tr><td colspan="7" class="text-muted text-center">No transfer records yet.</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
