<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-file-text-o"></i> Inventory Indents</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Indent Requests</h3>
                <div class="box-tools pull-right">
                    <a href="<?php echo site_url('admin/inventoryindent/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Raise Indent
                    </a>
                </div>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Indent No</th>
                            <th>Requested By</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Estimated Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($indent_rows)) { foreach ($indent_rows as $row) { ?>
                        <tr>
                            <td><?php echo (int) $row['id']; ?></td>
                            <td><?php echo html_escape((string) $row['indent_no']); ?></td>
                            <td><?php echo !empty($row['requested_by_name']) ? html_escape((string) $row['requested_by_name']) : (int) $row['requested_by']; ?></td>
                            <td><?php echo html_escape((string) $row['request_date']); ?></td>
                            <td>
                                <?php $status = strtolower((string) $row['status']); ?>
                                <?php if ($status === 'approved') { ?>
                                    <span class="label label-success">Approved</span>
                                <?php } elseif ($status === 'rejected') { ?>
                                    <span class="label label-danger">Rejected</span>
                                <?php } else { ?>
                                    <span class="label label-warning"><?php echo html_escape(ucfirst((string) $row['status'])); ?></span>
                                <?php } ?>
                            </td>
                            <td><?php echo html_escape((string) $row['priority']); ?></td>
                            <td><?php echo html_escape((string) $row['total_estimated_cost']); ?></td>
                        </tr>
                    <?php }} else { ?>
                        <tr><td colspan="7" class="text-muted text-center">No indent records yet. Run migration and start creating indents.</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
