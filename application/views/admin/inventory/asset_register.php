<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-desktop"></i> Asset Register</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Tracked Asset Units</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asset Tag</th>
                            <th>Name</th>
                            <th>Item ID</th>
                            <th>Serial No</th>
                            <th>Status</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($rows)) { foreach ($rows as $row) { ?>
                        <tr>
                            <td><?php echo (int) $row['id']; ?></td>
                            <td><?php echo html_escape((string) $row['asset_tag']); ?></td>
                            <td><?php echo html_escape((string) $row['asset_name']); ?></td>
                            <td><?php echo (int) $row['item_id']; ?></td>
                            <td><?php echo html_escape((string) $row['serial_no']); ?></td>
                            <td><?php echo html_escape((string) $row['current_status']); ?></td>
                            <td><?php echo html_escape((string) $row['location_name']); ?></td>
                        </tr>
                    <?php }} else { ?>
                        <tr><td colspan="7" class="text-muted text-center">No assets in register yet.</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
