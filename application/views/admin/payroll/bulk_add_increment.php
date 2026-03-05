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
        <style>
            .bulk-increment-csv-panel {
                background: #f5f9ff;
                border: 1px solid #d6e4ff;
                border-radius: 6px;
                padding: 12px 14px;
                margin-bottom: 12px;
            }
            .bulk-increment-csv-panel .help-block {
                margin: 6px 0 0;
                font-size: 12px;
                color: #6b7a90;
            }
            #csv_increment_file {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                width: 100% !important;
                cursor: pointer !important;
                padding: 6px 8px !important;
                height: 34px !important;
                border: 1px solid #ddd !important;
                border-radius: 4px !important;
                background-color: white !important;
                color: #333 !important;
            }
            #csv_increment_file::file-selector-button {
                cursor: pointer;
                background-color: #f0f0f0;
                padding: 4px 8px;
                border: 1px solid #ccc;
                border-radius: 3px;
                margin-right: 10px;
            }
            .bulk-increment-table-wrapper {
                max-height: 600px;
                overflow-y: auto;
                border: 1px solid #ddd;
                border-radius: 4px;
                background: #f9f9f9;
                position: relative;
            }
            .csv-loading-overlay {
                position: absolute;
                inset: 0;
                background: rgba(255, 255, 255, 0.9);
                display: none;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                z-index: 20;
            }
            .csv-loading-spinner {
                width: 44px;
                height: 44px;
                border: 4px solid #e0e0e0;
                border-top: 4px solid #1e88e5;
                border-radius: 50%;
                animation: csv-spin 0.9s linear infinite;
                margin-bottom: 10px;
            }
            .csv-loading-text {
                font-size: 13px;
                color: #34495e;
                font-weight: 600;
                text-align: center;
            }
            .csv-upload-status {
                margin-top: 6px;
                font-size: 12px;
                color: #2c3e50;
            }
            @keyframes csv-spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
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
                            <a href="<?php echo site_url('admin/payroll/bulk_onetime_deduction'); ?>" class="btn btn-warning btn-xs">
                                <i class="fa fa-upload"></i> One-Time Deduction Upload
                            </a>
                            <a href="<?php echo site_url('admin/payroll/pending_onetime_deductions'); ?>" class="btn btn-info btn-xs">
                                <i class="fa fa-clock-o"></i> Pending Deductions
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

                            <!-- Override Existing Increments -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="override_checkbox" id="override_checkbox"> 
                                            <strong>Override existing increments if any staff member already has one in this month</strong>
                                        </label>
                                        <small class="form-text text-muted">When checked, existing increments for the same month will be replaced with new values</small>
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

                            <!-- CSV Upload -->
                            <div class="bulk-increment-csv-panel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="csv_increment_file">Upload CSV (Employee ID, Increment Amount)</label>
                                        <input type="file" id="csv_increment_file" accept=".csv" style="cursor: pointer; width: 100%; padding: 6px 8px; border: 1px solid #ddd; border-radius: 4px;">
                                        <div class="help-block">Format: employee_id, increment_amount (first row can be header)</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="csv-upload-status" id="csv_upload_status">No file uploaded.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Staff Selection & Increment Table -->
                            <div class="form-group">
                                <label>
                                    Add Individual Increments <span class="text-danger">*</span>
                                    <span style="float: right; margin-right: 100px;">
                                        <input type="checkbox" id="select_all"> <strong>Select All</strong>
                                    </span>
                                </label>
                                <div class="bulk-increment-table-wrapper">
                                    <div class="csv-loading-overlay" id="csv_loading_overlay">
                                        <div class="csv-loading-spinner"></div>
                                        <div class="csv-loading-text" id="csv_loading_text">Applying increments...</div>
                                    </div>
                                    <table class="table table-striped table-hover table-condensed" id="staff_table" style="margin: 0;">
                                        <thead style="position: sticky; top: 0; background: #34495e; color: white; z-index: 10;">
                                            <tr>
                                                <th style="width: 3%;" class="text-center">
                                                    <input type="checkbox" id="select_all_header" style="margin: 0;">
                                                </th>
                                                <th style="width: 7%;">Emp ID</th>
                                                <th style="width: 14%;">Staff Name</th>
                                                <th style="width: 10%;">Role</th>
                                                <th style="width: 10%;">Dept</th>
                                                <th style="width: 10%;"><strong>Current Basic</strong></th>
                                                <th style="width: 8%;"><strong>Type</strong></th>
                                                <th style="width: 24%;"><strong>Inc Amount</strong></th>
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
                                                    $special_allowance = isset($staff['special_allowance']) ? $staff['special_allowance'] : 0;
                                                    ?>
                                            <!-- DEBUG: Staff ID <?php echo $staff_id; ?> SA: <?php echo $special_allowance; ?> -->
                                            <tr data-role="<?php echo htmlspecialchars($role); ?>" data-staff-id="<?php echo $staff_id; ?>" data-employee-id="<?php echo htmlspecialchars($staff['employee_id']); ?>" data-basic="<?php echo $basic_salary; ?>" data-special-allowance="<?php echo $special_allowance; ?>">
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
                            <input type="hidden" name="override_existing" id="override_existing_field" value="0">
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
        const specialAllowance = parseFloat(row.data('special-allowance')) || 0;
        const mergeWith = $('select[name="merge_with"]').val();
        
        const incrementType = typeSelect.val();
        const incrementValue = parseFloat(amountInput.val()) || 0;
        
        if (incrementValue <= 0) {
            previewText.html('-');
            updateTotalIncrement();
            return;
        }
        
        let newSalary = basicSalary;
        let displayText = '';
        
        let incrementAmount = 0;
        if (incrementType === 'Fixed') {
            incrementAmount = incrementValue;
        } else {
            incrementAmount = (basicSalary * incrementValue / 100);
        }

        if (mergeWith === 'special_allowance') {
            const newAllowance = specialAllowance + incrementAmount;
            displayText = `Special Allowance: ${currencySymbol}${specialAllowance.toFixed(2)} → ${currencySymbol}${newAllowance.toFixed(2)}`;
        } else {
            newSalary = basicSalary + incrementAmount;
            displayText = `New: ${currencySymbol}${newSalary.toFixed(2)}`;
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

    // Form validation and override flag handler - COMBINED
    $('#bulk_increment_form').on('submit', function(e) {
        console.log('Form submit handler triggered');
        
        // FIRST: Set the override flag based on checkbox state
        const overrideCheckbox = $('#override_checkbox');
        const overrideFlagField = $('#override_existing_field');
        
        if (overrideCheckbox.is(':checked')) {
            overrideFlagField.val('1');
            console.log('✓ Override flag SET TO 1 - will delete existing increments');
        } else {
            overrideFlagField.val('0');
            console.log('✗ Override flag SET TO 0 - will reject duplicates');
        }
        
        // SECOND: Validate form inputs
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
            return false;
        }
        
        console.log('Form validation passed, submitting with override_existing=' + overrideFlagField.val());
        // Allow form to submit (don't prevent default)
        return true;
    });

    // Remove the duplicate override handler at the bottom - no longer needed
    
    // Initialize
    updateSelectedCount();
    updateTotalIncrement();

    function splitCsvLine(line) {
        const result = [];
        let current = '';
        let inQuotes = false;

        for (let i = 0; i < line.length; i++) {
            const char = line[i];
            if (char === '"') {
                if (inQuotes && line[i + 1] === '"') {
                    current += '"';
                    i++;
                } else {
                    inQuotes = !inQuotes;
                }
            } else if (char === ',' && !inQuotes) {
                result.push(current.trim());
                current = '';
            } else {
                current += char;
            }
        }
        result.push(current.trim());
        return result;
    }

    function parseCsvText(text) {
        const normalized = text.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
        const lines = normalized.split('\n').filter(line => line.trim() !== '');
        const records = [];

        lines.forEach((line, index) => {
            const cols = splitCsvLine(line);
            if (!cols.length) {
                return;
            }
            const firstCol = (cols[0] || '').toLowerCase();
            const secondCol = (cols[1] || '').toLowerCase();
            if (index === 0 && (firstCol.includes('employee') || firstCol.includes('emp') || secondCol.includes('increment'))) {
                return;
            }

            const employeeId = (cols[0] || '').trim();
            const incrementRaw = (cols[1] || '').trim();
            if (!employeeId) {
                return;
            }
            const incrementValue = parseFloat(incrementRaw);
            records.push({
                employeeId,
                incrementValue: isNaN(incrementValue) ? 0 : incrementValue
            });
        });

        return records;
    }

    function buildEmployeeIdMap() {
        const map = {};
        $('tr[data-employee-id]').each(function() {
            const key = String($(this).data('employee-id')).trim();
            if (key) {
                map[key] = $(this);
            }
        });
        return map;
    }

    function showCsvOverlay(message) {
        $('#csv_loading_text').text(message || 'Applying increments...');
        $('#csv_loading_overlay').css('display', 'flex');
    }

    function hideCsvOverlay() {
        $('#csv_loading_overlay').hide();
    }

    function applyCsvRecords(records) {
        const staffMap = buildEmployeeIdMap();
        let index = 0;
        let applied = 0;
        let skipped = 0;
        let missing = 0;

        showCsvOverlay('Applying increments...');

        function processBatch() {
            const batchEnd = Math.min(index + 50, records.length);
            for (; index < batchEnd; index++) {
                const record = records[index];
                const row = staffMap[String(record.employeeId).trim()];
                if (!row) {
                    missing++;
                    continue;
                }
                const staffId = row.data('staff-id');
                const checkbox = row.find('input[name="staff_ids[]"]');
                const incrementValue = parseFloat(record.incrementValue) || 0;

                if (incrementValue > 0) {
                    checkbox.prop('checked', true);
                    updateRowVisibility(staffId);
                    row.find('.increment-type-select').val('Fixed');
                    row.find('.increment-amount-input').val(incrementValue.toFixed(2));
                    updatePreview(staffId);
                    applied++;
                } else {
                    checkbox.prop('checked', false);
                    updateRowVisibility(staffId);
                    skipped++;
                }
            }

            $('#csv_loading_text').text(`Applying increments... ${index}/${records.length}`);

            if (index < records.length) {
                setTimeout(processBatch, 0);
            } else {
                hideCsvOverlay();
                updateSelectedCount();
                updateTotalIncrement();
                $('#csv_upload_status').text(`Applied: ${applied}, Skipped: ${skipped}, Not found: ${missing}`);
            }
        }

        processBatch();
    }

    $('#csv_increment_file').on('change', function() {
        const file = this.files[0];
        if (!file) {
            return;
        }

        $('#csv_upload_status').text(`Reading ${file.name}...`);
        showCsvOverlay('Reading CSV...');

        const reader = new FileReader();
        reader.onload = function(e) {
            const content = e.target.result || '';
            const records = parseCsvText(content);
            if (!records.length) {
                hideCsvOverlay();
                $('#csv_upload_status').text('No valid rows found in CSV.');
                return;
            }
            applyCsvRecords(records);
        };
        reader.onerror = function() {
            hideCsvOverlay();
            $('#csv_upload_status').text('Failed to read CSV. Please try again.');
        };
        reader.readAsText(file);
    });
</script>
    </section>
</div>
