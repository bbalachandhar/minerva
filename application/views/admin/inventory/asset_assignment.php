<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-user"></i> Asset Assignment</h1>
    </section>
    <section class="content">
        <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>

        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Assign Asset</h3>
                    </div>
                    <form method="post" action="<?php echo site_url('admin/assetmanagement/storeassignment'); ?>">
                        <div class="box-body">
                            <div class="form-group">
                                <label>Asset <span class="req">*</span></label>
                                <select name="asset_id" class="form-control" required>
                                    <option value="">Select</option>
                                    <?php foreach ((array) $asset_rows as $asset) { ?>
                                        <option value="<?php echo (int) $asset['id']; ?>">
                                            <?php echo html_escape((string) $asset['asset_tag'] . ' - ' . $asset['asset_name'] . ' (' . $asset['current_status'] . ')'); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Issue Target <span class="req">*</span></label>
                                <select name="assignee_type" class="form-control js-assignment-target-type" required>
                                    <option value="staff">Staff Person</option>
                                    <option value="place">Place / Location</option>
                                </select>
                            </div>
                            <div class="form-group js-assignment-target-staff">
                                <label>Issue To (Staff) <span class="req">*</span></label>
                                <select name="assignee_id" class="form-control">
                                    <option value="">Select</option>
                                    <?php foreach ((array) $staff_rows as $staff) { ?>
                                        <option value="<?php echo (int) $staff['id']; ?>">
                                            <?php echo html_escape((string) $staff['employee_id'] . ' - ' . $staff['name'] . ' ' . $staff['surname']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group js-assignment-target-place" style="display:none;">
                                <label>Issue To (Place) <span class="req">*</span></label>
                                <select name="place_location_id" class="form-control">
                                    <option value="">Select</option>
                                    <?php foreach ((array) $location_rows as $loc) { ?>
                                        <option value="<?php echo (int) $loc['id']; ?>">
                                            <?php echo html_escape((string) $loc['location_code'] . ' - ' . $loc['location_name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Assigned On <span class="req">*</span></label>
                                <input type="text" class="form-control date asset-workflow-date" name="assigned_on" value="<?php echo date($this->customlib->getSchoolDateFormat()); ?>" readonly required>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Assignment</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Bulk Assignment Upload</h3>
                    </div>
                    <form method="post" action="<?php echo site_url('admin/assetmanagement/bulkassignment'); ?>" enctype="multipart/form-data">
                        <div class="box-body">
                            <div class="form-group">
                                <label>CSV File <span class="req">*</span></label>
                                <input type="file" name="assignment_csv" class="form-control" accept=".csv" required>
                            </div>
                            <p>
                                <a href="<?php echo base_url('backend/import/sample_asset_assignment_bulk.csv'); ?>" class="btn btn-default btn-sm" download>
                                    <i class="fa fa-download"></i> Download Sample CSV
                                </a>
                            </p>
                            <p class="text-muted">
                                Required headers: <code>asset_id,assignee_type,assigned_on</code><br>
                                For <code>assignee_type=staff</code> use <code>assignee_id</code>. For <code>assignee_type=place</code> use <code>assignee_id</code> or <code>place_location_id</code>.<br>
                                Optional: <code>assigned_by</code><br>
                                Sample file: <code>backend/import/sample_asset_assignment_bulk.csv</code>
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
                <h3 class="box-title">Assignment History</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asset</th>
                            <th>Issue Target</th>
                            <th>Issue To</th>
                            <th>Assigned On</th>
                            <th>Returned On</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($rows)) { foreach ($rows as $row) { ?>
                        <tr>
                            <td><?php echo (int) $row['id']; ?></td>
                            <td><?php echo html_escape((string) ($row['asset_tag'] . ' - ' . $row['asset_name'])); ?></td>
                            <td><?php echo (string) $row['assignee_type'] === 'place' ? 'Place / Location' : 'Staff Person'; ?></td>
                            <td>
                                <?php if ((string) $row['assignee_type'] === 'place') { ?>
                                    <?php echo html_escape((string) ($row['assignee_location_code'] . ' - ' . $row['assignee_location_name'])); ?>
                                <?php } else { ?>
                                    <?php echo html_escape((string) ($row['employee_id'] . ' - ' . $row['name'] . ' ' . $row['surname'])); ?>
                                <?php } ?>
                            </td>
                            <td><?php echo html_escape((string) $row['assigned_on']); ?></td>
                            <td><?php echo html_escape((string) $row['returned_on']); ?></td>
                            <td><?php echo html_escape((string) $row['status']); ?></td>
                            <td>
                                <?php if ((string) $row['status'] === 'assigned') { ?>
                                    <form method="post" action="<?php echo site_url('admin/assetmanagement/markreturn'); ?>" style="display:flex; gap:6px;">
                                        <input type="hidden" name="assignment_id" value="<?php echo (int) $row['id']; ?>">
                                        <input type="text" name="return_note" class="form-control" placeholder="Return note" style="max-width:160px;">
                                        <button type="submit" class="btn btn-xs btn-success">Mark Returned</button>
                                    </form>
                                <?php } else { ?>
                                    <span class="label label-default">Completed</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php }} else { ?>
                        <tr><td colspan="8" class="text-muted text-center">No assignment records yet.</td></tr>
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
    $('.asset-workflow-date').datepicker({
        format: date_format,
        autoclose: true,
        todayHighlight: true,
        weekStart: (typeof start_week !== 'undefined' ? start_week : 0)
    });

    function toggleAssignmentTargetFields() {
        var target = $('.js-assignment-target-type').val();
        if (target === 'place') {
            $('.js-assignment-target-place').show();
            $('.js-assignment-target-staff').hide();
            $('.js-assignment-target-staff select').val('');
        } else {
            $('.js-assignment-target-staff').show();
            $('.js-assignment-target-place').hide();
            $('.js-assignment-target-place select').val('');
        }
    }

    $('.js-assignment-target-type').on('change', toggleAssignmentTargetFields);
    toggleAssignmentTargetFields();
});
</script>
