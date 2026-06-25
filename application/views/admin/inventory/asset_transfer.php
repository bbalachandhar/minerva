<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-random"></i> Asset Transfer</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Transfer Register</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#transferModal">
                        <i class="fa fa-plus"></i> Create Transfer
                    </button>
                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#bulkTransferModal">
                        <i class="fa fa-upload"></i> Bulk Upload
                    </button>
                </div>
            </div>
            <div class="box-body">
                <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asset</th>
                            <th>From Location</th>
                            <th>To Location</th>
                            <th>Transfer Date</th>
                            <th>Remarks</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($rows)) { foreach ($rows as $row) { ?>
                        <tr>
                            <td><?php echo (int) $row['id']; ?></td>
                            <td><?php echo html_escape((string) $row['asset_tag']); ?></td>
                            <td><?php echo html_escape((string) $row['from_location_name']); ?></td>
                            <td><?php echo html_escape((string) $row['to_location_name']); ?></td>
                            <td><?php echo html_escape((string) $row['transfer_date']); ?></td>
                            <td><?php echo html_escape((string) $row['remarks']); ?></td>
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

<!-- Create Transfer Modal -->
<div class="modal fade" id="transferModal" tabindex="-1" role="dialog" aria-labelledby="transferModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="transferModalLabel"><i class="fa fa-random"></i> Create Transfer</h4>
            </div>
            <form method="post" action="<?php echo site_url('admin/assetmanagement/storetransfer'); ?>">
                <div class="modal-body">
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
                        <label>Issue Target <span class="req">*</span></label>
                        <select name="target_type" class="form-control js-transfer-target-type" required>
                            <option value="place">Place / Location</option>
                            <option value="staff">Staff Person</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>To Location <span class="req">*</span></label>
                        <select name="to_location_id" class="form-control" required>
                            <option value="">Select</option>
                            <?php foreach ((array) $location_rows as $loc) { ?>
                                <option value="<?php echo (int) $loc['id']; ?>">
                                    <?php echo html_escape((string) $loc['location_code'] . ' - ' . $loc['location_name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group js-transfer-target-staff" style="display:none;">
                        <label>Issue To (Staff) <span class="req">*</span></label>
                        <select name="to_assignee_id" class="form-control">
                            <option value="">No Assignee</option>
                            <?php foreach ((array) $staff_rows as $staff) { ?>
                                <option value="<?php echo (int) $staff['id']; ?>">
                                    <?php echo html_escape((string) $staff['employee_id'] . ' - ' . $staff['name'] . ' ' . $staff['surname']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Transfer Date <span class="req">*</span></label>
                        <input type="text" class="form-control date asset-workflow-date" name="transfer_date" value="<?php echo date($this->customlib->getSchoolDateFormat()); ?>" readonly required>
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Transfer Upload Modal -->
<div class="modal fade" id="bulkTransferModal" tabindex="-1" role="dialog" aria-labelledby="bulkTransferModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="bulkTransferModalLabel"><i class="fa fa-upload"></i> Bulk Transfer Upload</h4>
            </div>
            <form method="post" action="<?php echo site_url('admin/assetmanagement/bulktransfer'); ?>" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>CSV File <span class="req">*</span></label>
                        <input type="file" name="transfer_csv" class="form-control" accept=".csv" required>
                    </div>
                    <p>
                        <a href="<?php echo base_url('backend/import/sample_asset_transfer_bulk.csv'); ?>" class="btn btn-default btn-sm" download>
                            <i class="fa fa-download"></i> Download Sample CSV
                        </a>
                    </p>
                    <p class="text-muted">
                        Required headers: <code>asset_id,to_location_id,transfer_date</code><br>
                        Optional: <code>target_type,to_assignee_id,transferred_by,approved_by,status,remarks</code><br>
                        If <code>target_type=staff</code>, <code>to_assignee_id</code> is required. If blank, target defaults to place.<br>
                        Sample file: <code>backend/import/sample_asset_transfer_bulk.csv</code>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info"><i class="fa fa-upload"></i> Upload CSV</button>
                </div>
            </form>
        </div>
    </div>
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

    function toggleTransferTargetFields() {
        var target = $('.js-transfer-target-type').val();
        if (target === 'staff') {
            $('.js-transfer-target-staff').show();
        } else {
            $('.js-transfer-target-staff').hide();
            $('.js-transfer-target-staff select').val('');
        }
    }

    $('.js-transfer-target-type').on('change', toggleTransferTargetFields);
    toggleTransferTargetFields();

    // Re-init datepicker when modal is shown
    $('#transferModal').on('shown.bs.modal', function () {
        $(this).find('.asset-workflow-date').datepicker({
            format: date_format,
            autoclose: true,
            todayHighlight: true,
            weekStart: (typeof start_week !== 'undefined' ? start_week : 0)
        });
        toggleTransferTargetFields();
    });
});
</script>
