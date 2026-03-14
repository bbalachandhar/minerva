<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-user"></i> Asset Assignment</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Assignment History</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asset ID</th>
                            <th>Assignee Type</th>
                            <th>Assignee ID</th>
                            <th>Assigned On</th>
                            <th>Returned On</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($rows)) { foreach ($rows as $row) { ?>
                        <tr>
                            <td><?php echo (int) $row['id']; ?></td>
                            <td><?php echo (int) $row['asset_id']; ?></td>
                            <td><?php echo html_escape((string) $row['assignee_type']); ?></td>
                            <td><?php echo (int) $row['assignee_id']; ?></td>
                            <td><?php echo html_escape((string) $row['assigned_on']); ?></td>
                            <td><?php echo html_escape((string) $row['returned_on']); ?></td>
                            <td><?php echo html_escape((string) $row['status']); ?></td>
                        </tr>
                    <?php }} else { ?>
                        <tr><td colspan="7" class="text-muted text-center">No assignment records yet.</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
