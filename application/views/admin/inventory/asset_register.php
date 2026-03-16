<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-desktop"></i> Asset Register</h1>
    </section>
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Filters</h3>
            </div>
            <form method="get" action="<?php echo site_url('admin/assetmanagement/register'); ?>">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All</option>
                                    <option value="in_stock" <?php echo (($filters['status'] ?? '') === 'in_stock') ? 'selected' : ''; ?>>In Stock</option>
                                    <option value="assigned" <?php echo (($filters['status'] ?? '') === 'assigned') ? 'selected' : ''; ?>>Assigned</option>
                                    <option value="under_maintenance" <?php echo (($filters['status'] ?? '') === 'under_maintenance') ? 'selected' : ''; ?>>Under Maintenance</option>
                                    <option value="disposed" <?php echo (($filters['status'] ?? '') === 'disposed') ? 'selected' : ''; ?>>Disposed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Holder Type</label>
                                <select name="assignee_type" class="form-control">
                                    <option value="">All</option>
                                    <option value="staff" <?php echo (($filters['assignee_type'] ?? '') === 'staff') ? 'selected' : ''; ?>>Staff</option>
                                    <option value="place" <?php echo (($filters['assignee_type'] ?? '') === 'place') ? 'selected' : ''; ?>>Place</option>
                                    <option value="unassigned" <?php echo (($filters['assignee_type'] ?? '') === 'unassigned') ? 'selected' : ''; ?>>Unassigned</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Location</label>
                                <select name="location_id" class="form-control">
                                    <option value="">All</option>
                                    <?php foreach ((array) $location_rows as $loc) { ?>
                                        <option value="<?php echo (int) $loc['id']; ?>" <?php echo ((int) ($filters['location_id'] ?? 0) === (int) $loc['id']) ? 'selected' : ''; ?>>
                                            <?php echo html_escape((string) $loc['location_code'] . ' - ' . $loc['location_name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Staff Holder</label>
                                <select name="staff_id" class="form-control">
                                    <option value="">All</option>
                                    <?php foreach ((array) $staff_rows as $staff) { ?>
                                        <option value="<?php echo (int) $staff['id']; ?>" <?php echo ((int) ($filters['staff_id'] ?? 0) === (int) $staff['id']) ? 'selected' : ''; ?>>
                                            <?php echo html_escape((string) $staff['employee_id'] . ' - ' . $staff['name'] . ' ' . $staff['surname']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Warranty</label>
                                <select name="warranty_state" class="form-control">
                                    <option value="">All</option>
                                    <option value="active" <?php echo (($filters['warranty_state'] ?? '') === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="due_30" <?php echo (($filters['warranty_state'] ?? '') === 'due_30') ? 'selected' : ''; ?>>Due in 30 Days</option>
                                    <option value="expired" <?php echo (($filters['warranty_state'] ?? '') === 'expired') ? 'selected' : ''; ?>>Expired</option>
                                    <option value="missing" <?php echo (($filters['warranty_state'] ?? '') === 'missing') ? 'selected' : ''; ?>>Not Set</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Maintenance Open</label>
                                <select name="maintenance_open" class="form-control">
                                    <option value="">All</option>
                                    <option value="yes" <?php echo (($filters['maintenance_open'] ?? '') === 'yes') ? 'selected' : ''; ?>>Yes</option>
                                    <option value="no" <?php echo (($filters['maintenance_open'] ?? '') === 'no') ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>License</label>
                                <select name="license_state" class="form-control">
                                    <option value="">All</option>
                                    <option value="active" <?php echo (($filters['license_state'] ?? '') === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="due_30" <?php echo (($filters['license_state'] ?? '') === 'due_30') ? 'selected' : ''; ?>>Due in 30 Days</option>
                                    <option value="expired" <?php echo (($filters['license_state'] ?? '') === 'expired') ? 'selected' : ''; ?>>Expired</option>
                                    <option value="missing" <?php echo (($filters['license_state'] ?? '') === 'missing') ? 'selected' : ''; ?>>Not Set</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Apply Filters</button>
                    <a href="<?php echo site_url('admin/assetmanagement/register'); ?>" class="btn btn-default">Reset</a>
                </div>
            </form>
        </div>

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
                            <th>Current Holder</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Warranty</th>
                            <th>License</th>
                            <th>Maintenance</th>
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
                            <td>
                                <?php if ((string) $row['assigned_to_type'] === 'staff') { ?>
                                    <?php echo html_escape((string) trim(($row['assigned_employee_id'] ?? '') . ' - ' . ($row['assigned_name'] ?? '') . ' ' . ($row['assigned_surname'] ?? ''))); ?>
                                <?php } elseif ((string) $row['assigned_to_type'] === 'place') { ?>
                                    <?php echo html_escape((string) (($row['location_code'] ?? '') . ' - ' . ($row['location_name'] ?? ''))); ?>
                                <?php } else { ?>
                                    <span class="text-muted">In stock</span>
                                <?php } ?>
                            </td>
                            <td><?php echo html_escape((string) $row['current_status']); ?></td>
                            <td><?php echo html_escape((string) $row['location_name']); ?></td>
                            <td>
                                <?php if (!empty($row['warranty_start']) || !empty($row['warranty_end'])) { ?>
                                    <?php echo html_escape((string) $row['warranty_start']); ?> - <?php echo html_escape((string) $row['warranty_end']); ?>
                                <?php } else { ?>
                                    <span class="text-muted">Not set</span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if (!empty($row['license_key'])) { ?>
                                    <div><code><?php echo html_escape((string) $row['license_key']); ?></code></div>
                                    <div class="text-muted"><?php echo html_escape((string) ($row['license_valid_from'] ?? '')); ?> - <?php echo html_escape((string) ($row['license_valid_till'] ?? '')); ?></div>
                                <?php } else { ?>
                                    <span class="text-muted">Not set</span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ((int) ($row['open_maintenance_count'] ?? 0) > 0) { ?>
                                    <span class="label label-warning">Open</span>
                                <?php } else { ?>
                                    <span class="label label-success">Clear</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php }} else { ?>
                        <tr><td colspan="11" class="text-muted text-center">No assets in register yet.</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
