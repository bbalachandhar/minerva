<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-users"></i> Bulk Add Salary Increment
            <small>Add individual salary increments for multiple staff members</small>
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
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Bulk Increment Configuration</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/payroll/add_increment'); ?>" class="btn btn-default btn-xs">
                                <i class="fa fa-user"></i> Single Staff
                            </a>
                        </div>
                    </div>

                    <form method="POST" action="<?php echo site_url('admin/payroll/save_bulk_increment'); ?>" id="bulk_increment_form">
                        <div class="box-body">
                            
                            <!-- Configuration Section -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i> <strong>Setup:</strong> Configure effective date and merge strategy below. Then add individual increment amounts for each selected staff in the table.
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Effective Date -->
                                    <div class="form-group">
                                        <label>Effective Date <span class="text-danger">*</span></label>
                                        <input type="date" name="effective_date" class="form-control" required>
                                        <small class="form-text text-muted">Date from which increment takes effect</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- Merge Strategy -->
                                    <div class="form-group">
                                        <label>Merge After First Month <span class="text-danger">*</span></label>
                                        <select name="merge_with" class="form-control" required>
                                            <option value="basic">Basic Salary (Recommended)</option>
                                            <option value="special_allowance">Special Allowance</option>
                                        </select>
                                        <small class="form-text text-muted">Where increment will be merged from month 2 onwards</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div class="form-group">
                                <label>Remarks (Optional)</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="e.g., Annual Increment 2026, Performance-based, etc."></textarea>
                            </div>

                            <hr>

                            <!-- Role Filter -->
                            <div class="form-group">
                                <label>Filter by Role (Optional)</label>
                                <select id="role_filter" class="form-control">
                                    <option value="">All Roles</option>
                                    <?php if (!empty($roles)) {
                                        foreach ($roles as $role) {
                                            echo "<option value='" . htmlspecialchars($role['type']) . "'>" . htmlspecialchars($role['type']) . "</option>";
                                        }
                                    } ?>
                                </select>
                            </div>

                            <!-- Staff Selection & Increment Table -->
                            <div class="form-group">
                                <label>
                                    Add Individual Increments <span class="text-danger">*</span>
                                    <span style="float: right; margin-right: 100px;">
                                        <input type="checkbox" id="select_all"> <strong>Select All</strong>
                                    </span>
                                </label>
                                <div style="max-height: 600px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                                    <table class="table table-striped table-hover table-condensed" id="staff_table" style="margin: 0;">
                                        <thead style="position: sticky; top: 0; background: #34495e; color: white; z-index: 10;">
                                            <tr>
                                                <th style="width: 3%;" class="text-center">
                                                    <input type="checkbox" id="select_all_header" style="margin: 0;">
                                                </th>
                                                <th style="width: 7%;">Emp ID</th>
                                                <th style="width: 18%;">Staff Name</th>
                                                <th style="width: 12%;">Role</th>
                                                <th style="width: 12%;">Dept</th>
                                                <th style="width: 12%;"><strong>Current Basic</strong></th>
                                                <th style="width: 8%;"><strong>Type</strong></th>
                                                <th style="width: 20%;"><strong>Amount</strong></th>
                                                <th style="width: 8%;"><strong>Kind</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($stafflist)) {
                                                foreach ($stafflist as $staff) {
                                                    $basic_salary = isset($staff['basic_salary']) ? $staff['basic_salary'] : 0;
                                                    $role = isset($staff['role']) ? $staff['role'] : '';
                                                    $department = isset($staff['department']) ? $staff['department'] : '';
                                                    $staff_id = $staff['id'];
                                                    ?>
                                            <tr data-role="<?php echo htmlspecialchars($role); ?>" data-staff-id="<?php echo $staff_id; ?>" data-basic="<?php echo $basic_salary; ?>">
                                                <td class="text-center" style="padding-top: 10px;">
                                                    <input type="checkbox" name="staff_ids[]" value="<?php echo $staff_id; ?>" class="staff_checkbox" onchange="updateRowVisibility(<?php echo $staff_id; ?>)">
                                                </td>
                                                <td><?php echo htmlspecialchars($staff['employee_id']); ?></td>
                                                <td><strong><?php echo htmlspecialchars($staff['name'] . ' ' . substr($staff['surname'] ?? '', 0, 1)); ?></strong></td>
                                                <td><span class="label label-info" style="font-size: 11px;"><?php echo htmlspecialchars($role); ?></span></td>
                                                <td><small><?php echo htmlspecialchars(substr($department ?? 'N/A', 0, 12)); ?></small></td>
                                                <td style="font-weight: 600; color: #27ae60;"><?php echo $currency_symbol . number_format($basic_salary, 2); ?></td>
                                                <td>
                                                    <select name="increment_type[<?php echo $staff_id; ?>]" class="form-control increment-type-select" style="font-size: 12px; padding: 4px 5px; display: none;" onchange="updatePreview(<?php echo $staff_id; ?>)">
                                                        <option value="Fixed">Fixed ₹</option>
                                                        <option value="Percentage">%</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <div style="display: none; position: relative;" class="increment-input-group">
                                                        <div style="display: flex; gap: 5px; align-items: center;">
                                                            <input type="number" name="increment_amount[<?php echo $staff_id; ?>]" class="form-control increment-amount-input" style="font-size: 12px; padding: 4px 5px; flex: 1;" placeholder="0.00" step="0.01" onkeyup="updatePreview(<?php echo $staff_id; ?>)" onchange="updatePreview(<?php echo $staff_id; ?>)">
                                                            <small style="color: #27ae60; font-weight: 600; white-space: nowrap; min-width: 90px;" class="preview-text">-</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="display: none;" class="recurring-toggle-cell">
                                                    <select name="is_recurring[<?php echo $staff_id; ?>]" class="form-control is-recurring-select" style="font-size: 11px; padding: 4px 5px;">
                                                        <option value="1">Increment</option>
                                                        <option value="0">Bonus</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <?php }
                                            } else { ?>
                                            <tr>
                                                <td colspan="8" class="text-center">No staff members found</td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted" style="margin-top: 10px; display: block;">
                                    <strong>Selected:</strong> <span id="selected_count" style="color: #2980b9;">0</span> staff | 
                                    <strong>Total Increment:</strong> <span id="total_increment" style="color: #27ae60; font-weight: 600;">₹0.00</span>
                                </small>
                            </div>

                        </div>

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-lg" id="submit_btn" disabled>
                                <i class="fa fa-save"></i> Save & Submit for Approval
                            </button>
                            <a href="<?php echo site_url('admin/payroll'); ?>" class="btn btn-default btn-lg">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Information Panels -->
        <div class="row" style="margin-top: 20px;">
            <div class="col-md-4">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> How It Works</h3>
                    </div>
                    <div class="box-body">
                        <ol style="padding-left: 18px; line-height: 2.2;">
                            <li>Set effective date & merge strategy</li>
                            <li>Filter by role (optional)</li>
                            <li>Check staff to select them</li>
                            <li>Choose <strong>Type</strong> (Fixed/%) for each</li>
                            <li>Enter <strong>Increment Amount</strong></li>
                            <li>View preview with new salary</li>
                            <li>Submit for HR approval</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-cog"></i> Increment Types</h3>
                    </div>
                    <div class="box-body">
                        <p style="margin-bottom: 15px;"><strong>📌 Fixed Amount (₹)</strong></p>
                        <p style="font-size: 12px; color: #666; margin-bottom: 10px;">Same flat amount for each staff<br>Example: Staff A & B each get ₹8,000</p>
                        
                        <p style="margin-bottom: 15px;"><strong>📌 Percentage (%)</strong></p>
                        <p style="font-size: 12px; color: #666; margin-bottom: 10px;">Calculated on each staff's basic salary<br>Example: 16% = ₹2,496 for one, ₹3,200 for another</p>
                        
                        <p style="font-size: 11px; color: #e74c3c; margin-top: 10px; font-style: italic;">⚠️ Each staff can have different type/amount</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-info" style="border-top-color: #00bcd4;">
                    <div class="box-header with-border" style="background-color: #e0f7fa;">
                        <h3 class="box-title"><i class="fa fa-level-up"></i> Recurring vs Bonus</h3>
                    </div>
                    <div class="box-body" style="font-size: 12px;">
                        <p style="margin-bottom: 12px;"><strong style="color: #27ae60;">✓ Increment</strong></p>
                        <p style="font-size: 11px; color: #666; margin-bottom: 10px;">• Permanent salary change<br>• Merges into basic salary from month 2<br>• Forms part of future salary calculations</p>
                        
                        <hr style="margin: 12px 0; border-color: #ddd;">

                        <p style="margin-bottom: 12px;"><strong style="color: #ff9800;">💰 Bonus</strong></p>
                        <p style="font-size: 11px; color: #666; margin-bottom: 5px;">• One-time, non-recurring payment<br>• Applies only for current month<br>• Does NOT merge into salary<br>• Disappears in next month</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-calculator"></i> Examples</h3>
                    </div>
                    <div class="box-body" style="font-size: 12px; line-height: 1.8;">
                        <p style="margin-bottom: 8px;"><strong>✓ Increment: ₹8,000</strong></p>
                        <small style="color: #27ae60;">Month 1: Basic ₹15,600 + ₹8,000 = ₹23,600</small><br>
                        <small style="color: #27ae60;">Month 2 & beyond: Basic ₹23,600 (merged)</small>
                        
                        <hr style="margin: 10px 0;">

                        <p style="margin-bottom: 8px;"><strong>💰 Bonus: ₹5,000</strong></p>
                        <small style="color: #ff9800;">Month 1: Basic ₹15,600 + ₹5,000 = ₹20,600</small><br>
                        <small style="color: #ff9800;">Month 2 & beyond: Basic ₹15,600 (no bonus)</small>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    const currencySymbol = '<?php echo $currency_symbol; ?>';

    // Toggle increment input on checkbox change
    function updateRowVisibility(staffId) {
        const checkbox = $(`input[name="staff_ids[]"][value="${staffId}"]`);
        const isChecked = checkbox.prop('checked');
        const row = checkbox.closest('tr');
        const typeSelect = row.find('.increment-type-select');
        const inputGroup = row.find('.increment-input-group');
        const recurringToggle = row.find('.recurring-toggle-cell');
        
        if (isChecked) {
            typeSelect.show().val('Fixed');
            inputGroup.show();
            recurringToggle.show();
            row.css('background-color', '#f0f8ff');
        } else {
            typeSelect.hide();
            inputGroup.hide();
            recurringToggle.hide();
            row.css('background-color', '');
            row.find('.increment-amount-input').val('');
            row.find('.preview-text').html('-');
        }
        
        updateSelectedCount();
        updateTotalIncrement();
    }

    // Update preview for each staff
    function updatePreview(staffId) {
        const row = $(`tr[data-staff-id="${staffId}"]`);
        const typeSelect = row.find('.increment-type-select');
        const amountInput = row.find('.increment-amount-input');
        const previewText = row.find('.preview-text');
        const basicSalary = parseFloat(row.data('basic')) || 0;
        
        const incrementType = typeSelect.val();
        const incrementValue = parseFloat(amountInput.val()) || 0;
        
        if (incrementValue <= 0) {
            previewText.html('-');
            updateTotalIncrement();
            return;
        }
        
        let newSalary = basicSalary;
        let displayText = '';
        
        if (incrementType === 'Fixed') {
            newSalary = basicSalary + incrementValue;
            displayText = `New: ${currencySymbol}${newSalary.toFixed(2)}`;
        } else {
            const percentageAmount = (basicSalary * incrementValue / 100);
            newSalary = basicSalary + percentageAmount;
            displayText = `+${percentageAmount.toFixed(2)} = ${currencySymbol}${newSalary.toFixed(2)}`;
        }
        
        previewText.html(displayText);
        updateTotalIncrement();
    }

    // Calculate total increment
    function updateTotalIncrement() {
        let total = 0;
        
        $('.staff_checkbox:checked').each(function() {
            const staffId = $(this).val();
            const row = $(`tr[data-staff-id="${staffId}"]`);
            const typeSelect = row.find('.increment-type-select');
            const amountInput = row.find('.increment-amount-input');
            const basicSalary = parseFloat(row.data('basic')) || 0;
            
            const incrementType = typeSelect.val();
            const incrementValue = parseFloat(amountInput.val()) || 0;
            
            if (incrementValue > 0) {
                if (incrementType === 'Fixed') {
                    total += incrementValue;
                } else {
                    total += (basicSalary * incrementValue / 100);
                }
            }
        });
        
        $('#total_increment').text(currencySymbol + total.toFixed(2));
    }

    // Update selected count
    function updateSelectedCount() {
        const count = $('input[name="staff_ids[]"]:checked').length;
        $('#selected_count').text(count);
        $('#submit_btn').prop('disabled', count === 0);
    }

    // Select all functionality - Optimized for performance
    $('#select_all, #select_all_header').change(function() {
        const isChecked = $(this).prop('checked');
        $('#select_all').prop('checked', isChecked);
        $('#select_all_header').prop('checked', isChecked);
        
        // Batch update all visible checkboxes
        const visibleRows = $('tbody tr:visible');
        visibleRows.each(function(index) {
            const checkbox = $(this).find('input[name="staff_ids[]"]');
            if (checkbox.length) {
                checkbox.prop('checked', isChecked);
                // Only show/hide fields, defer preview updates
                const row = checkbox.closest('tr');
                const typeSelect = row.find('.increment-type-select');
                const inputGroup = row.find('.increment-input-group');
                
                if (isChecked) {
                    typeSelect.show().val('Fixed');
                    inputGroup.show();
                    row.css('background-color', '#f0f8ff');
                } else {
                    typeSelect.hide();
                    inputGroup.hide();
                    row.css('background-color', '');
                    row.find('.increment-amount-input').val('');
                    row.find('.preview-text').html('-');
                }
            }
        });
        
        // Update counts and totals only once (not for each row)
        updateSelectedCount();
        updateTotalIncrement();
    });

    // Role filter
    $('#role_filter').change(function() {
        const selectedRole = $(this).val();
        
        $('tbody tr').each(function() {
            const rowRole = $(this).data('role');
            
            if (selectedRole === '') {
                $(this).show();
            } else {
                $(this).toggle(rowRole === selectedRole);
                if (rowRole !== selectedRole) {
                    const checkbox = $(this).find('input[name="staff_ids[]"]');
                    checkbox.prop('checked', false);
                    updateRowVisibility(checkbox.val());
                }
            }
        });
        
        $('#select_all, #select_all_header').prop('checked', false);
        updateSelectedCount();
    });

    // Form validation before submit
    $('#bulk_increment_form').on('submit', function(e) {
        let hasError = false;
        let errorMessage = '';
        
        $('input[name="staff_ids[]"]:checked').each(function() {
            const staffId = $(this).val();
            const row = $(`tr[data-staff-id="${staffId}"]`);
            const amountInput = row.find('.increment-amount-input');
            const amount = parseFloat(amountInput.val());
            const staffName = row.find('td:nth-child(3)').text();
            
            if (isNaN(amount) || amount <= 0) {
                row.css('background-color', '#ffebee');
                hasError = true;
                errorMessage = `Enter valid increment amount for: ${staffName}`;
            }
        });
        
        if (hasError) {
            e.preventDefault();
            alert('⚠️ ' + errorMessage);
        }
    });

    // Initialize
    updateSelectedCount();
    updateTotalIncrement();
</script>
    </section>
</div>
