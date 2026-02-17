<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-clock-o"></i> Pending Salary Increments
            <small>Review and approve pending increment requests</small>
        </h1>
    </section>

    <section class="content">
        <!-- Back Button & Bulk Approval -->
        <div style="margin-bottom: 15px; display: flex; gap: 10px; align-items: center;">
            <a href="<?php echo site_url('admin/payroll/bulk_add_increment'); ?>" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to Bulk Add Increment
            </a>
            <?php if (!empty($increments)): ?>
            <button type="button" id="bulk_approve_btn" class="btn btn-success" style="display: none;" data-toggle="modal" data-target="#bulkApproveModal">
                <i class="fa fa-check-circle"></i> Bulk Approve Selected (<span id="selected_count">0</span>)
            </button>
            <?php endif; ?>
        </div>

        <!-- Flash Messages -->
        <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
        </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-list"></i> Pending Approvals 
                            <span class="badge bg-warning"><?php echo count($increments); ?></span>
                        </h3>
                    </div>

                    <div class="box-body">
                        <?php if (!empty($increments)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr style="background: #ecf0f1;">
                                        <th style="width: 3%; text-align: center;">
                                            <input type="checkbox" id="select_all_increments" title="Select all">
                                        </th>
                                        <th style="width: 5%;">ID</th>
                                        <th style="width: 15%;">Staff Member</th>
                                        <th style="width: 11%;">Effective Date</th>
                                        <th style="width: 12%;">Increment</th>
                                        <th style="width: 9%;">Type</th>
                                        <th style="width: 8%;">Kind</th>
                                        <th style="width: 13%;">Merge With</th>
                                        <th style="width: 16%;">Remarks</th>
                                        <th style="width: 8%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($increments as $increment): 
                                        $is_bonus = isset($increment['is_recurring']) && $increment['is_recurring'] == 0;
                                    ?>
                                    <tr class="increment-row" data-increment-id="<?php echo $increment['id']; ?>">
                                        <td style="text-align: center; vertical-align: middle;">
                                            <input type="checkbox" class="increment-checkbox" value="<?php echo $increment['id']; ?>">
                                        </td>
                                        <td><strong>#<?php echo $increment['id']; ?></strong></td>
                                        <td>
                                            <strong><?php echo $increment['name']; ?></strong><br>
                                            <small style="color: #7f8c8d;">Emp ID: <?php echo $increment['employee_id']; ?></small>
                                        </td>
                                        <td>
                                            <span class="label label-default">
                                                <?php echo date('d-M-Y', strtotime($increment['effective_date'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong style="font-size: 13px;">
                                                <?php if ($increment['increment_type'] === 'Fixed'): ?>
                                                    <?php echo $currency_symbol . number_format($increment['increment_amount'], 2); ?>
                                                <?php else: ?>
                                                    <?php echo $increment['increment_percentage']; ?>%
                                                <?php endif; ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="label" style="background: <?php echo ($increment['increment_type'] === 'Fixed') ? '#3498db' : '#9b59b6'; ?>; color: white;">
                                                <?php echo $increment['increment_type']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($is_bonus): ?>
                                                <span class="label label-info" title="One-time, non-recurring"><i class="fa fa-gift"></i> Bonus</span>
                                            <?php else: ?>
                                                <span class="label label-success" title="Recurring, will merge after month 1"><i class="fa fa-level-up"></i> Increment</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($is_bonus): ?>
                                                <small style="color: #999;"><em>N/A</em></small>
                                            <?php else: ?>
                                                <?php echo ucfirst(str_replace('_', ' ', $increment['merge_with'])); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?php echo !empty($increment['remarks']) ? $increment['remarks'] : '<em style="color: #95a5a6;">-</em>'; ?></small>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-xs btn-success" data-toggle="modal" data-target="#approveModal<?php echo $increment['id']; ?>" title="Approve">
                                                <i class="fa fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#rejectModal<?php echo $increment['id']; ?>" title="Reject">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Approve Modal -->
                                    <div class="modal fade" id="approveModal<?php echo $increment['id']; ?>" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header" style="background: #27ae60; color: white;">
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    <h4 class="modal-title"><i class="fa fa-check-circle"></i> Approve Increment</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Approve salary increment for <strong><?php echo $increment['name']; ?></strong>?</p>
                                                    <div style="background: #ecf0f1; padding: 10px; border-radius: 4px;">
                                                        <p style="margin: 0;">
                                                            <strong>Effective Date:</strong> <?php echo date('d-M-Y', strtotime($increment['effective_date'])); ?><br>
                                                            <strong>Increment:</strong> 
                                                            <?php if ($increment['increment_type'] === 'Fixed'): ?>
                                                                <?php echo $currency_symbol . number_format($increment['increment_amount'], 2); ?>
                                                            <?php else: ?>
                                                                <?php echo $increment['increment_percentage']; ?>% of basic salary
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                    <a href="<?php echo site_url('admin/payroll/approve_increment/' . $increment['id']); ?>" class="btn btn-success">
                                                        <i class="fa fa-check"></i> Approve
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal<?php echo $increment['id']; ?>" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header" style="background: #e74c3c; color: white;">
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    <h4 class="modal-title"><i class="fa fa-times-circle"></i> Reject Increment</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Reject salary increment for <strong><?php echo $increment['name']; ?></strong>?</p>
                                                    <div style="background: #ffe6e6; padding: 10px; border-radius: 4px;">
                                                        <p style="margin: 0; font-size: 12px; color: #c0392b;">
                                                            This action will mark the increment as rejected. The staff member will need to resubmit.
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                    <a href="<?php echo site_url('admin/payroll/reject_increment/' . $increment['id']); ?>" class="btn btn-danger">
                                                        <i class="fa fa-times"></i> Reject
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fa fa-check-circle"></i> <strong>No pending increments!</strong> All salary increment requests have been processed.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Approve Modal -->
        <div class="modal fade" id="bulkApproveModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background: #27ae60; color: white;">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-check-circle"></i> Bulk Approve Increments</h4>
                    </div>
                    <div class="modal-body">
                        <p>Approve <strong id="bulk_count_display">0</strong> selected salary increments?</p>
                        <div style="background: #ecf0f1; padding: 10px; border-radius: 4px;">
                            <ul id="bulk_increment_list" style="margin: 0; padding-left: 20px; max-height: 200px; overflow-y: auto;">
                            </ul>
                        </div>
                        <p style="margin-top: 10px; color: #7f8c8d; font-size: 12px;">
                            <i class="fa fa-info-circle"></i> All selected increments will be approved instantly and will take effect from their respective effective dates.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="confirm_bulk_approve">
                            <i class="fa fa-check"></i> Approve All
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Information Box -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> Approval Workflow</h3>
                    </div>
                    <div class="box-body">
                        <ol style="line-height: 1.8;">
                            <li><strong>Staff records increment</strong> - Employee or HR enters increment details</li>
                            <li><strong>Awaits approval</strong> - Request appears in this pending queue</li>
                            <li><strong>HR reviews</strong> - Manager verifies details and approves</li>
                            <li><strong>Takes effect</strong> - Approved increments appear in next paybill</li>
                            <li><strong>Auto-merge</strong> - After first month, increment merges into Basic/Special Allowance</li>
                        </ol>
                        <hr style="margin-top: 15px;">
                        <p style="margin: 0; color: #7f8c8d; font-size: 12px;">
                            <i class="fa fa-info-circle"></i> <strong>Note:</strong> Only pending increments can be managed here. Approved increments will appear in the payment processing schedule automatically.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
console.log('Page loaded - checking jQuery...');

if (typeof jQuery !== 'undefined') {
    console.log('jQuery is available');
} else {
    console.error('jQuery NOT found!');
}

$(document).ready(function() {
    console.log('=== PENDING INCREMENTS PAGE INITIALIZED ===');
    
    // Store selected IDs globally to avoid losing them in modal
    var selectedIncrementIds = [];
    
    // Check if elements exist
    console.log('Elements found:');
    console.log('- #bulk_approve_btn:', $('#bulk_approve_btn').length);
    console.log('- #confirm_bulk_approve:', $('#confirm_bulk_approve').length);
    console.log('- .increment-checkbox:', $('.increment-checkbox').length);
    console.log('- #bulk_increment_list:', $('#bulk_increment_list').length);
    
    // Update selected count
    function updateSelectedCount() {
        var selected = $('.increment-checkbox:checked').length;
        $('#selected_count').text(selected);
        
        if (selected > 0) {
            $('#bulk_approve_btn').show();
        } else {
            $('#bulk_approve_btn').hide();
        }
    }
    
    // Select All functionality
    $('#select_all_increments').on('change', function() {
        console.log('Select All checkbox changed');
        var isChecked = $(this).is(':checked');
        $('.increment-checkbox').prop('checked', isChecked);
        updateSelectedCount();
    });
    
    // Individual checkbox change
    $('.increment-checkbox').on('change', function() {
        updateSelectedCount();
        
        // Update "Select All" checkbox state
        var total = $('.increment-checkbox').length;
        var selected = $('.increment-checkbox:checked').length;
        $('#select_all_increments').prop('checked', total > 0 && total === selected);
    });
    
    // Open bulk approve modal - SIMPLIFIED
    $('#bulk_approve_btn').on('click', function() {
        console.log('=== BULK APPROVE BUTTON CLICKED ===');
        
        var list = $('#bulk_increment_list');
        list.empty();
        var count = 0;
        
        // Immediately capture all checked increments
        $('.increment-checkbox:checked').each(function() {
            var incrementId = $(this).val();
            var row = $('tr[data-increment-id="' + incrementId + '"]');
            var staffName = row.find('td:nth-child(3)').text();
            var effectiveDate = row.find('td:nth-child(4)').text();
            var amount = row.find('td:nth-child(5)').text();
            
            console.log('Row ' + count + ': ID=' + incrementId + ', Name=' + staffName);
            list.append('<li style="padding: 5px 0; border-bottom: 1px solid #ddd;"><strong>' + staffName + '</strong><br><small>Date: ' + effectiveDate + ' | Amount: ' + amount + '</small></li>');
            count++;
        });
        
        console.log('Total checked: ' + count);
        $('#bulk_count_display').text(count);
    });
    
    // Confirm bulk approve - COLLECT FRESH FROM CHECKBOXES
    $(document).on('click', '#confirm_bulk_approve', function(e) {
        e.preventDefault();
        console.log('=== APPROVE ALL CLICKED - COLLECTING FRESH IDS ===');
        
        // COLLECT FRESH - DON'T USE STALE selectedIncrementIds
        var approveIds = [];
        $('.increment-checkbox:checked').each(function() {
            approveIds.push($(this).val());
        });
        
        console.log('Number of IDs to approve:', approveIds.length);
        console.log('IDs:', approveIds);
        
        if (approveIds.length === 0) {
            console.log('ERROR: No IDs collected!');
            alert('ERROR: No increments to approve. Please select checkboxes again.');
            return;
        }
        
        // Show confirmation dialog
        var confirmMessage = 'Are you sure you want to approve ' + approveIds.length + ' salary increment(s)?\n\nThis action cannot be undone.';
        
        if (!confirm(confirmMessage)) {
            console.log('User cancelled');
            return;
        }
        
        // Show loading
        var btn = $(this);
        var originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing ' + approveIds.length + ' records...');
        
        console.log('Sending AJAX with IDs:', approveIds);
        
        // Build form data properly for array
        var formData = new FormData();
        approveIds.forEach(function(id) {
            formData.append('increment_ids[]', id);
        });
        
        console.log('FormData prepared with ' + approveIds.length + ' items');
        
        // Send AJAX request
        $.ajax({
            url: '<?php echo site_url('admin/payroll/bulk_approve_increments'); ?>',
            type: 'POST',
            dataType: 'json',
            processData: false,
            contentType: false,
            data: formData,
            success: function(response) {
                console.log('=== SERVER RESPONSE ===');
                console.log('Response:', response);
                console.log('Approved count:', response.approved);
                console.log('Failed count:', response.failed);
                console.log('Message:', response.message);
                
                if (response.approved > 0) {
                    alert('✓ SUCCESS!\n\nApproved: ' + response.approved + '\nFailed: ' + response.failed + '\n\n' + response.message);
                    setTimeout(function() { location.reload(); }, 500);
                } else {
                    alert('✗ ' + response.message);
                    btn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.log('=== AJAX ERROR ===');
                console.log('Status:', status);
                console.log('Error:', error);
                console.log('Response:', xhr.responseText);
                alert('✗ Error: ' + status + '\n\nCheck browser console (F12) for details');
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Initialize
    console.log('Calling updateSelectedCount...');
    updateSelectedCount();
    console.log('=== INITIALIZATION COMPLETE ===');
});
</script>
