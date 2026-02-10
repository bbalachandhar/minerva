<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-calendar-check-o"></i> <?php echo $this->lang->line('initial_leave_balance_setup'); ?>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>"><i class="fa fa-dashboard"></i> <?php echo $this->lang->line('dashboard'); ?></a></li>
            <li><a href="#"><?php echo $this->lang->line('human_resource'); ?></a></li>
            <li class="active"><?php echo $this->lang->line('initial_leave_balance_setup'); ?></li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list"></i> Set Initial Leave Balances</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-success btn-sm" id="saveAllBalancesBtn">
                                <i class="fa fa-save"></i> Save All Balances
                            </button>
                        </div>
                    </div>

                    <div class="box-body">
                        <!-- Info Alert -->
                        <div class="alert alert-info">
                            <h4><i class="fa fa-info-circle"></i> Important Information:</h4>
                            <ul>
                                <li><strong>Purpose:</strong> Use this page to set current leave balances for all staff members (one-time setup).</li>
                                <li><strong>How it works:</strong> Enter the current paid leave balance for each staff member. After saving, the system will mark today (<?php echo date('d-M-Y'); ?>) as the last processed date.</li>
                                <li><strong>Next increment:</strong> From next month onwards (starting <?php echo date('M Y', strtotime('+1 month')); ?>), the cron job will automatically add configured days to these balances.</li>
                                <li><strong>Leave Types Configured:</strong> 
                                    <?php if (!empty($leave_rules)): ?>
                                        <?php foreach ($leave_rules as $rule): ?>
                                            <span class="label label-success"><?php echo $rule['leave_type_name']; ?> (+<?php echo $rule['increment_days']; ?> days/month)</span> 
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-danger">No leave types configured for auto-increment. Please configure in Settings first.</span>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </div>

                        <?php if (empty($leave_rules)): ?>
                            <div class="alert alert-warning">
                                <h4><i class="fa fa-exclamation-triangle"></i> No Leave Types Configured</h4>
                                <p>Please go to <a href="<?php echo base_url('schsettings'); ?>">General Settings</a> and configure leave types for monthly increment first.</p>
                            </div>
                        <?php else: ?>
                            <!-- Filter Bar -->
                            <div class="row" style="margin-bottom: 15px;">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                        <input type="text" id="searchStaff" class="form-control" placeholder="Search by name or employee ID...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-default" id="setAllZeroBtn">
                                        <i class="fa fa-refresh"></i> Set All to 0
                                    </button>
                                    <button type="button" class="btn btn-info" id="autoCalculateBtn" title="Calculate based on months passed">
                                        <i class="fa fa-calculator"></i> Auto Calculate
                                    </button>
                                </div>
                                <div class="col-md-4 text-right">
                                    <span class="label label-info">Total Staff: <span id="totalStaffCount"><?php echo count($staff_list); ?></span></span>
                                </div>
                            </div>

                            <!-- Staff Leave Balance Table -->
                            <div class="table-responsive">
                                <form id="leaveBalanceForm">
                                    <table class="table table-striped table-bordered table-hover" id="leaveBalanceTable">
                                        <thead>
                                            <tr class="bg-light-blue">
                                                <th width="5%">#</th>
                                                <th width="15%">Employee ID</th>
                                                <th width="25%">Staff Name</th>
                                                <?php foreach ($leave_rules as $rule): ?>
                                                    <th width="<?php echo floor(55 / count($leave_rules)); ?>%">
                                                        <?php echo $rule['leave_type_name']; ?>
                                                        <br><small class="text-muted">(+<?php echo $rule['increment_days']; ?>/month)</small>
                                                    </th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($staff_list)): ?>
                                                <tr>
                                                    <td colspan="<?php echo 3 + count($leave_rules); ?>" class="text-center">
                                                        No active staff found
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($staff_list as $index => $staff): ?>
                                                    <tr data-staff-id="<?php echo $staff['id']; ?>" 
                                                        data-employee-id="<?php echo $staff['employee_id']; ?>" 
                                                        data-staff-name="<?php echo $staff['name'] . ' ' . $staff['surname']; ?>">
                                                        <td><?php echo $index + 1; ?></td>
                                                        <td>
                                                            <strong><?php echo $staff['employee_id']; ?></strong>
                                                        </td>
                                                        <td>
                                                            <?php echo $staff['name'] . ' ' . $staff['surname']; ?>
                                                            <?php if ($staff['designation']): ?>
                                                                <br><small class="text-muted"><?php echo $staff['designation']; ?></small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <?php foreach ($leave_rules as $rule): ?>
                                                            <?php 
                                                            $current_balance = isset($balances[$staff['id']][$rule['leave_type_id']]) 
                                                                ? $balances[$staff['id']][$rule['leave_type_id']]['alloted_leave'] 
                                                                : '0.00';
                                                            ?>
                                                            <td>
                                                                <div class="input-group input-group-sm">
                                                                    <input type="number" 
                                                                           class="form-control leave-balance-input" 
                                                                           name="balances[<?php echo $staff['id']; ?>][<?php echo $rule['leave_type_id']; ?>]"
                                                                           value="<?php echo $current_balance; ?>"
                                                                           step="0.5" 
                                                                           min="0" 
                                                                           max="999"
                                                                           placeholder="0.00"
                                                                           data-rule-increment="<?php echo $rule['increment_days']; ?>">
                                                                    <span class="input-group-addon">
                                                                        <i class="fa fa-calendar-o"></i>
                                                                    </span>
                                                                </div>
                                                            </td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($leave_rules)): ?>
                        <div class="box-footer">
                            <button type="button" class="btn btn-success btn-lg pull-right" id="saveAllBalancesBtnBottom">
                                <i class="fa fa-save"></i> Save All Balances
                            </button>
                            <p class="text-muted">
                                <i class="fa fa-info-circle"></i> After saving, the system will set today (<?php echo date('d-M-Y'); ?>) as the last processed date. 
                                The cron job will start incrementing from next month.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    var base_url = '<?php echo base_url(); ?>';

    $(document).ready(function() {
        
        // Search functionality
        $('#searchStaff').on('keyup', function() {
            var searchText = $(this).val().toLowerCase();
            var visibleCount = 0;
            
            $('#leaveBalanceTable tbody tr').each(function() {
                var employeeId = $(this).data('employee-id');
                var staffName = $(this).data('staff-name');
                
                if (employeeId && staffName) {
                    if (employeeId.toLowerCase().indexOf(searchText) > -1 || 
                        staffName.toLowerCase().indexOf(searchText) > -1) {
                        $(this).show();
                        visibleCount++;
                    } else {
                        $(this).hide();
                    }
                }
            });
            
            $('#totalStaffCount').text(visibleCount);
        });

        // Set all to 0
        $('#setAllZeroBtn').on('click', function() {
            if (confirm('Are you sure you want to set all leave balances to 0?')) {
                $('.leave-balance-input').val('0.00');
                successMsg('All balances set to 0');
            }
        });

        // Auto calculate based on months passed
        $('#autoCalculateBtn').on('click', function() {
            var resetMonth = <?php echo isset($settings->leave_reset_month) ? $settings->leave_reset_month : 1; ?>;
            var currentMonth = <?php echo date('n'); ?>; // February = 2
            
            var monthsPassed = currentMonth >= resetMonth 
                ? currentMonth - resetMonth 
                : 12 + currentMonth - resetMonth;
            
            if (monthsPassed === 0) {
                errorMsg('No months have passed since reset month. Balance will be 0.');
                return;
            }
            
            var message = 'Months passed since last reset: ' + monthsPassed + '\n\n';
            message += 'This will calculate balances as:\n';
            <?php foreach ($leave_rules as $rule): ?>
                message += '<?php echo $rule['leave_type_name']; ?>: ' + monthsPassed + ' × <?php echo $rule['increment_days']; ?> = ' + (monthsPassed * <?php echo $rule['increment_days']; ?>) + ' days\n';
            <?php endforeach; ?>
            message += '\nProceed?';
            
            if (confirm(message)) {
                $('.leave-balance-input').each(function() {
                    var incrementPerMonth = parseFloat($(this).data('rule-increment'));
                    var calculatedBalance = (monthsPassed * incrementPerMonth).toFixed(2);
                    $(this).val(calculatedBalance);
                });
                successMsg('Balances calculated for ' + monthsPassed + ' month(s)');
            }
        });

        // Save balances
        function saveBalances() {
            var $btn = $('#saveAllBalancesBtn, #saveAllBalancesBtnBottom');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
            
            $.ajax({
                url: base_url + 'leave_balance_setup/ajax_save_balances',
                type: 'POST',
                data: $('#leaveBalanceForm').serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status == 'success') {
                        successMsg(response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        errorMsg(response.message);
                        $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save All Balances');
                    }
                },
                error: function() {
                    errorMsg('Error saving balances. Please try again.');
                    $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save All Balances');
                }
            });
        }

        $('#saveAllBalancesBtn, #saveAllBalancesBtnBottom').on('click', function() {
            if (confirm('Save all leave balances? This will set today as the last processed date and future increments will start from next month.')) {
                saveBalances();
            }
        });

        // Highlight changed inputs
        $('.leave-balance-input').on('change', function() {
            $(this).addClass('bg-warning');
        });
    });
</script>

<style>
    .bg-warning {
        background-color: #fcf8e3 !important;
    }
    .leave-balance-input {
        text-align: center;
        font-weight: bold;
    }
    #leaveBalanceTable thead th {
        text-align: center;
        vertical-align: middle;
    }
    #leaveBalanceTable tbody td {
        vertical-align: middle;
    }
</style>
