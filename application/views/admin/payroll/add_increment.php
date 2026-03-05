<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-plus-circle"></i> Add Salary Increment
            <small>Record a new salary increment for an employee</small>
        </h1>
    </section>

    <section class="content">
        <!-- Flash Messages -->
        <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Increment Details</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/payroll/bulk_add_increment'); ?>" class="btn btn-success btn-xs">
                                <i class="fa fa-users"></i> Bulk Add (Multiple Staff)
                            </a>
                            <a href="<?php echo site_url('admin/payroll/bulk_onetime_deduction'); ?>" class="btn btn-warning btn-xs">
                                <i class="fa fa-upload"></i> One-Time Deduction Upload
                            </a>
                        </div>
                    </div>

                    <form method="POST" action="<?php echo site_url('admin/payroll/save_increment'); ?>" class="form-horizontal" id="increment_form">
                        <div class="box-body">
                            <!-- Staff Selection -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Staff Member <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select name="staff_id" class="form-control select2" id="staff_id" required>
                                        <option value="">Select Staff Member</option>
                                        <?php if (!empty($stafflist)) {
                                            foreach ($stafflist as $emp) {
                                                $selected = ($staff_id == $emp['id']) ? 'selected' : '';
                                                echo "<option value='" . $emp['id'] . "' $selected>" . $emp['name'] . " (" . $emp['employee_id'] . ")</option>";
                                            }
                                        } ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Current Salary Display -->
                            <?php if ($staff_id && !empty($staff)): ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Current Basic Salary</label>
                                <div class="col-sm-9">
                                    <p class="form-control-static form-control-static-large" style="font-weight: 700; font-size: 16px; color: #27ae60;">
                                        <?php echo $currency_symbol . number_format($current_salary, 2); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Last Increment Info -->
                            <?php if (!empty($last_increment)): ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Last Increment</label>
                                <div class="col-sm-9">
                                    <p class="form-control-static">
                                        <strong><?php echo date('d-M-Y', strtotime($last_increment['effective_date'])); ?></strong> - 
                                        <?php 
                                            if ($last_increment['increment_type'] === 'Fixed') {
                                                echo $currency_symbol . number_format($last_increment['increment_amount'], 2);
                                            } else {
                                                echo $last_increment['increment_percentage'] . '%';
                                            }
                                        ?>
                                        <span class="label label-info"><?php echo $last_increment['approval_status']; ?></span>
                                    </p>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>

                            <!-- Effective Date -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Effective Date <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="date" name="effective_date" class="form-control" required>
                                    <small class="form-text text-muted">Date from which increment takes effect</small>
                                </div>
                            </div>

                            <!-- Increment Type Selection -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Increment Type <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="increment_type" value="Fixed" required onchange="updateIncrementType()"> 
                                            <strong>Fixed Amount</strong> (e.g., ₹8,000)
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="increment_type" value="Percentage" onchange="updateIncrementType()"> 
                                            <strong>Percentage-Based</strong> (e.g., 16% of basic)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Fixed Amount Input -->
                            <div class="form-group" id="fixed_amount_group" style="display: none;">
                                <label class="col-sm-3 control-label">Increment Amount <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" name="increment_amount" class="form-control" id="increment_amount" placeholder="0.00">
                                    <small class="form-text text-muted">Enter the fixed increment amount</small>
                                </div>
                            </div>

                            <!-- Percentage Input -->
                            <div class="form-group" id="percentage_group" style="display: none;">
                                <label class="col-sm-3 control-label">Increment Percentage <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <input type="number" name="increment_percentage" class="form-control" id="increment_percentage" placeholder="0" min="0" max="100" step="0.01">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <small class="form-text text-muted">Percentage will be calculated on current basic salary</small>
                                </div>
                            </div>

                            <!-- Preview Calculation -->
                            <div class="form-group" id="preview_group" style="display: none; background: #ecf0f1; padding: 15px; border-radius: 4px; margin-left: 25%; margin-right: 8%;">
                                <p style="margin: 0;">
                                    <strong>Preview:</strong> New salary will be <span style="color: #27ae60; font-weight: 700; font-size: 16px;" id="preview_salary">--</span>
                                </p>
                            </div>

                            <!-- Merge Strategy -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Merge After Month <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="merge_with" value="basic" required checked> 
                                            Merge into <strong>Basic Salary</strong> (Recommended)
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="merge_with" value="special_allowance"> 
                                            Merge into <strong>Special Allowance</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">After the first month, increment will be merged into selected component</small>
                                </div>
                            </div>

                            <!-- Recurring vs One-Time Bonus -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Kind <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="is_recurring" value="1" required checked onchange="updateKindLabel()"> 
                                            <strong>Recurring Increment</strong> <small style="color: #27ae60;">(Permanent salary change)</small>
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="is_recurring" value="0" onchange="updateKindLabel()"> 
                                            <strong>One-Time Bonus</strong> <small style="color: #ff9800;">(This month only)</small>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted" id="kind_info" style="display: block; margin-top: 8px; color: #27ae60;">
                                        ✓ Will become permanent salary from next month
                                    </small>
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Remarks</label>
                                <div class="col-sm-9">
                                    <textarea name="remarks" class="form-control" rows="3" placeholder="e.g., Annual increment, promotion, etc."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="box-footer">
                            <a href="<?php echo site_url('admin/payroll/increments'); ?>" class="btn btn-default">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary pull-right">
                                <i class="fa fa-save"></i> Save & Submit for Approval
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Information Panel -->
            <div class="col-md-4">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> How It Works</h3>
                    </div>
                    <div class="box-body">
                        <h5><strong>Increment Display:</strong></h5>
                        <p style="font-size: 12px; line-height: 1.6;">
                            ✓ <strong>Month 1:</strong> Shows as separate "Increment" line<br>
                            ✓ <strong>Month 2+:</strong> Merged into selected component<br>
                            ✓ <strong>Automatic:</strong> HPF & TDS recalculated
                        </p>

                        <hr>

                        <h5><strong>Increment Types:</strong></h5>
                        <p style="font-size: 12px; line-height: 1.6;">
                            <strong>Fixed:</strong> ₹8,000 - Same amount for all<br>
                            <strong>%:</strong> 16% - Percentage of basic salary
                        </p>

                        <hr>

                        <h5><strong>Process:</strong></h5>
                        <ol style="font-size: 12px;">
                            <li>Record increment details</li>
                            <li>Submit for HR approval</li>
                            <li>Appears in pending queue</li>
                            <li>HR reviews and approves</li>
                            <li>Shows in paybill automatically</li>
                        </ol>

                        <hr>

                        <div style="background: #e8f4f8; padding: 10px; border-radius: 4px; border-left: 4px solid #3498db;">
                            <strong style="color: #2c3e50;">Note:</strong>
                            <p style="margin: 5px 0 0 0; font-size: 12px; color: #555;">
                                Only HR managers can approve increments. Approved increments will automatically appear in payrolls starting from the effective date.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(function() {
    $('.select2').select2();

    // Update on staff change
    $('#staff_id').on('change', function() {
        if ($(this).val()) {
            window.location.href = '<?php echo site_url('admin/payroll/add_increment'); ?>/' + $(this).val();
        }
    });

    // Handle increment type change
    window.updateIncrementType = function() {
        var type = $('input[name="increment_type"]:checked').val();
        var currentSalary = <?php echo $current_salary ?? 0; ?>;
        
        if (type === 'Fixed') {
            $('#fixed_amount_group').show();
            $('#percentage_group').hide();
            $('#preview_group').show();
            
            $('#increment_amount').on('input', function() {
                var newSalary = currentSalary + parseFloat($(this).val() || 0);
                $('#preview_salary').text('<?php echo $currency_symbol; ?>' + newSalary.toFixed(2));
            });
        } else if (type === 'Percentage') {
            $('#fixed_amount_group').hide();
            $('#percentage_group').show();
            $('#preview_group').show();
            
            $('#increment_percentage').on('input', function() {
                var percentage = parseFloat($(this).val() || 0);
                var amount = (currentSalary * percentage / 100);
                var newSalary = currentSalary + amount;
                $('#preview_salary').text('<?php echo $currency_symbol; ?>' + newSalary.toFixed(2));
            });
        } else {
            $('#fixed_amount_group').hide();
            $('#percentage_group').hide();
            $('#preview_group').hide();
        }
    };

    // Initialize
    var selectedType = $('input[name="increment_type"]:checked').val();
    if (selectedType) {
        updateIncrementType();
    }
});
</script>
