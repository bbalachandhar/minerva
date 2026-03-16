<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-wrench"></i> Asset Maintenance</h1>
    </section>
    <section class="content">
        <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>

        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Add Maintenance Record</h3>
                    </div>
                    <form method="post" action="<?php echo site_url('admin/assetmanagement/storemaintenance'); ?>">
                        <div class="box-body">
                            <div class="form-group">
                                <label>Asset <span class="req">*</span></label>
                                <select name="asset_id" class="form-control" required>
                                    <option value="">Select</option>
                                    <?php foreach ((array) $asset_rows as $asset) { ?>
                                        <option value="<?php echo (int) $asset['id']; ?>">
                                            <?php echo html_escape((string) $asset['asset_tag'] . ' - ' . $asset['asset_name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Maintenance Type <span class="req">*</span></label>
                                <select name="maintenance_type" class="form-control" required>
                                    <option value="breakdown">Breakdown</option>
                                    <option value="preventive">Preventive</option>
                                    <option value="amc">AMC</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Vendor Name</label>
                                <input type="text" class="form-control" name="vendor_name" maxlength="191">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Opened On <span class="req">*</span></label>
                                        <input type="text" class="form-control date maintenance-date" name="opened_on" value="<?php echo date($this->customlib->getSchoolDateFormat()); ?>" readonly required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status <span class="req">*</span></label>
                                        <select name="status" class="form-control" required>
                                            <option value="open">Open</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="closed">Closed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Issue Description</label>
                                <textarea name="issue_description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Resolution Note (if closed)</label>
                                        <input type="text" class="form-control" name="resolution_note" maxlength="255">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Closed On (if closed)</label>
                                        <input type="text" class="form-control date maintenance-date" name="closed_on" value="<?php echo date($this->customlib->getSchoolDateFormat()); ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Cost Amount</label>
                                        <input type="number" step="0.01" class="form-control" name="cost_amount" value="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Next Due Date</label>
                                        <input type="text" class="form-control date maintenance-date" name="next_due_date" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Maintenance</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Bulk Maintenance Upload</h3>
                    </div>
                    <form method="post" action="<?php echo site_url('admin/assetmanagement/bulkmaintenance'); ?>" enctype="multipart/form-data">
                        <div class="box-body">
                            <div class="form-group">
                                <label>CSV File <span class="req">*</span></label>
                                <input type="file" name="maintenance_csv" class="form-control" accept=".csv" required>
                            </div>
                            <p>
                                <a href="<?php echo base_url('backend/import/sample_asset_maintenance_bulk.csv'); ?>" class="btn btn-default btn-sm" download>
                                    <i class="fa fa-download"></i> Download Sample CSV
                                </a>
                            </p>
                            <p class="text-muted">
                                Required headers: <code>asset_id,maintenance_type,opened_on</code><br>
                                Optional: <code>vendor_name,closed_on,status,issue_description,resolution_note,cost_amount,next_due_date,created_by</code><br>
                                Sample file: <code>backend/import/sample_asset_maintenance_bulk.csv</code>
                            </p>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info"><i class="fa fa-upload"></i> Upload CSV</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($rows)) { foreach ($rows as $row) { ?>
                        <tr>
                            <td><?php echo (int) $row['id']; ?></td>
                            <td><?php echo html_escape((string) ($row['asset_tag'] . ' - ' . $row['asset_name'])); ?></td>
                            <td><?php echo html_escape((string) $row['maintenance_type']); ?></td>
                            <td><?php echo html_escape((string) $row['vendor_name']); ?></td>
                            <td><?php echo html_escape((string) $row['opened_on']); ?></td>
                            <td><?php echo html_escape((string) $row['closed_on']); ?></td>
                            <td><?php echo html_escape((string) $row['status']); ?></td>
                            <td><?php echo html_escape((string) $row['cost_amount']); ?></td>
                            <td>
                                <?php if ((string) $row['status'] !== 'closed') { ?>
                                    <form method="post" action="<?php echo site_url('admin/assetmanagement/completemaintenance'); ?>" style="display:flex; gap:6px; align-items:center;">
                                        <input type="hidden" name="maintenance_id" value="<?php echo (int) $row['id']; ?>">
                                        <input type="text" name="resolution_note" class="form-control" placeholder="Resolution" style="max-width:140px;">
                                        <input type="number" step="0.01" name="cost_amount" class="form-control" value="<?php echo html_escape((string) $row['cost_amount']); ?>" style="max-width:90px;">
                                        <input type="text" name="closed_on" class="form-control date maintenance-date" value="<?php echo date($this->customlib->getSchoolDateFormat()); ?>" readonly style="max-width:140px;">
                                        <button type="submit" class="btn btn-xs btn-success">Close</button>
                                    </form>
                                <?php } else { ?>
                                    <span class="label label-default">Closed</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php }} else { ?>
                        <tr><td colspan="9" class="text-muted text-center">No maintenance records yet.</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
$(document).ready(function () {
    var date_format = '<?php echo strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy']); ?>';
    $('.maintenance-date').datepicker({
        format: date_format,
        autoclose: true,
        todayHighlight: true,
        weekStart: (typeof start_week !== 'undefined' ? start_week : 0)
    });
});
</script>
