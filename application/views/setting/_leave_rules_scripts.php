<script type="text/javascript">
// ========================================================================
// Monthly Leave Increment Rules Management
// ========================================================================

$(document).ready(function() {
    
    // Edit Rule
    $(document).on('click', '.editRuleBtn', function() {
        var $row = $(this).closest('tr');
        $row.find('.days-display').hide();
        $row.find('.days-edit').show().focus();
        $row.find('.editRuleBtn').hide();
        $row.find('.saveRuleBtn, .cancelRuleBtn').show();
    });
    
    // Cancel Edit
    $(document).on('click', '.cancelRuleBtn', function() {
        var $row = $(this).closest('tr');
        var originalValue = $row.find('.days-display').text().trim();
        $row.find('.days-edit').val(originalValue).hide();
        $row.find('.days-display').show();
        $row.find('.saveRuleBtn, .cancelRuleBtn').hide();
        $row.find('.editRuleBtn').show();
    });
    
    // Save Rule
    $(document).on('click', '.saveRuleBtn', function() {
        var $btn = $(this);
        var $row = $btn.closest('tr');
        var ruleId = $row.data('rule-id');
        var incrementDays = $row.find('.days-edit').val();
        
        if (!incrementDays || incrementDays < 0 || incrementDays > 31) {
            errorMsg('Please enter valid days (0-31)');
            return;
        }
        
        $btn.prop('disabled', true);
        
        $.ajax({
            url: base_url + 'schsettings/ajax_save_leave_rule',
            type: 'POST',
            data: {
                id: ruleId,
                increment_days: incrementDays,
                enabled: $row.find('.toggleRuleBtn').data('enabled')
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $row.find('.days-display').text(parseFloat(incrementDays).toFixed(2));
                    $row.find('.days-edit').hide();
                    $row.find('.days-display').show();
                    $row.find('.saveRuleBtn, .cancelRuleBtn').hide();
                    $row.find('.editRuleBtn').show();
                    successMsg(response.message);
                } else {
                    errorMsg(response.message);
                }
                $btn.prop('disabled', false);
            },
            error: function() {
                errorMsg('Error saving rule');
                $btn.prop('disabled', false);
            }
        });
    });
    
    // Toggle Rule Status
    $(document).on('click', '.toggleRuleBtn', function() {
        var $btn = $(this);
        var $row = $btn.closest('tr');
        var ruleId = $row.data('rule-id');
        var currentStatus = parseInt($btn.data('enabled'));
        var newStatus = currentStatus ? 0 : 1;
        
        $btn.prop('disabled', true);
        
        $.ajax({
            url: base_url + 'schsettings/ajax_toggle_leave_rule',
            type: 'POST',
            data: {
                id: ruleId,
                enabled: newStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $btn.data('enabled', newStatus);
                    
                    if (newStatus) {
                        $btn.removeClass('btn-info').addClass('btn-warning')
                            .attr('title', 'Disable')
                            .find('i').removeClass('fa-check').addClass('fa-ban');
                        $row.find('.status-badge').removeClass('label-danger').addClass('label-success').text('Enabled');
                    } else {
                        $btn.removeClass('btn-warning').addClass('btn-info')
                            .attr('title', 'Enable')
                            .find('i').removeClass('fa-ban').addClass('fa-check');
                        $row.find('.status-badge').removeClass('label-success').addClass('label-danger').text('Disabled');
                    }
                    
                    successMsg(response.message);
                } else {
                    errorMsg(response.message);
                }
                $btn.prop('disabled', false);
            },
            error: function() {
                errorMsg('Error toggling rule status');
                $btn.prop('disabled', false);
            }
        });
    });
    
    // Delete Rule
    $(document).on('click', '.deleteRuleBtn', function() {
        var $btn = $(this);
        var $row = $btn.closest('tr');
        var ruleId = $row.data('rule-id');
        var leaveType = $row.find('td:eq(1)').text().trim();
        
        if (!confirm('Are you sure you want to delete the rule for "' + leaveType + '"?')) {
            return;
        }
        
        $btn.prop('disabled', true);
        
        $.ajax({
            url: base_url + 'schsettings/ajax_delete_leave_rule',
            type: 'POST',
            data: { id: ruleId },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $row.fadeOut(300, function() {
                        $(this).remove();
                        
                        if ($('#leaveRulesTable tbody tr').length === 0) {
                            $('#leaveRulesTable tbody').html(
                                '<tr><td colspan="5" class="text-center text-muted">' +
                                '<i class="fa fa-info-circle"></i> No rules configured yet. Click "Add New Rule" to configure a leave type for auto increment.' +
                                '</td></tr>'
                            );
                        } else {
                            $('#leaveRulesTable tbody tr').each(function(index) {
                                $(this).find('td:first').text(index + 1);
                            });
                        }
                    });
                    successMsg(response.message);
                } else {
                    errorMsg(response.message);
                    $btn.prop('disabled', false);
                }
            },
            error: function() {
                errorMsg('Error deleting rule');
                $btn.prop('disabled', false);
            }
        });
    });
    
    // Add New Rule
    $('#addLeaveRuleBtn').on('click', function() {
        var options = '<option value="">Select Leave Type</option>';
        <?php foreach ($leave_types as $lt): ?>
        var hasRule<?php echo $lt['id']; ?> = <?php 
            $has_rule = false;
            foreach ($leave_increment_rules as $rule) {
                if ($rule['leave_type_id'] == $lt['id']) {
                    $has_rule = true;
                    break;
                }
            }
            echo $has_rule ? 'true' : 'false';
        ?>;
        if (!hasRule<?php echo $lt['id']; ?>) {
            options += '<option value="<?php echo $lt['id']; ?>"><?php echo htmlspecialchars($lt['type']); ?></option>';
        }
        <?php endforeach; ?>
        
        var modalHtml = '<div class="modal fade" id="addLeaveRuleModal" tabindex="-1" role="dialog">' +
            '<div class="modal-dialog" role="document">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>' +
            '<h4 class="modal-title"><i class="fa fa-plus"></i> Add New Leave Increment Rule</h4>' +
            '</div>' +
            '<div class="modal-body">' +
            '<form id="addLeaveRuleForm">' +
            '<div class="form-group">' +
            '<label>Leave Type <span class="text-danger">*</span></label>' +
            '<select class="form-control" name="leave_type_id" id="newLeaveTypeId" required>' +
            options +
            '</select>' +
            '</div>' +
            '<div class="form-group">' +
            '<label>Days to Increment per Month <span class="text-danger">*</span></label>' +
            '<input type="number" class="form-control" name="increment_days" id="newIncrementDays" ' +
            'value="1.00" step="0.5" min="0" max="31" required>' +
            '<small class="text-muted">Enter days to add each month (e.g., 1 for 1 day/month, 0.5 for half day)</small>' +
            '</div>' +
            '<div class="form-group">' +
            '<label>' +
            '<input type="checkbox" name="enabled" id="newEnabled" checked> Enable this rule' +
            '</label>' +
            '</div>' +
            '</form>' +
            '</div>' +
            '<div class="modal-footer">' +
            '<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' +
            '<button type="button" class="btn btn-primary" id="saveNewRuleBtn">Save Rule</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
        
        $('#addLeaveRuleModal').remove();
        $('body').append(modalHtml);
        $('#addLeaveRuleModal').modal('show');
    });
    
    // Save New Rule
    $(document).on('click', '#saveNewRuleBtn', function() {
        var $btn = $(this);
        var leaveTypeId = $('#newLeaveTypeId').val();
        var incrementDays = $('#newIncrementDays').val();
        var enabled = $('#newEnabled').is(':checked') ? 1 : 0;
        
        if (!leaveTypeId) {
            errorMsg('Please select a leave type');
            return;
        }
        
        if (!incrementDays || incrementDays < 0 || incrementDays > 31) {
            errorMsg('Please enter valid days (0-31)');
            return;
        }
        
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: base_url + 'schsettings/ajax_save_leave_rule',
            type: 'POST',
            data: {
                id: null,
                leave_type_id: leaveTypeId,
                increment_days: incrementDays,
                enabled: enabled
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    successMsg(response.message);
                    $('#addLeaveRuleModal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    errorMsg(response.message);
                    $btn.prop('disabled', false).html('Save Rule');
                }
            },
            error: function() {
                errorMsg('Error adding rule');
                $btn.prop('disabled', false).html('Save Rule');
            }
        });
    });
    
});
</script>
