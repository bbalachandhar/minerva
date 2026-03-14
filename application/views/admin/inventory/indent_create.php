<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-plus-square-o"></i> Raise Indent</h1>
    </section>
    <section class="content">
        <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Create New Indent</h3>
            </div>
            <form method="post" action="<?php echo site_url('admin/inventoryindent/store'); ?>">
                <div class="box-body">
                    <?php echo $this->customlib->getCSRF(); ?>
                    <?php
                    $indent_policy = $indent_fallback_settings ?? [];
                    $configured_indent_l2 = $configured_l2_approver ?? null;
                    $indent_l2_label = '';
                    if (!empty($configured_indent_l2)) {
                        $indent_l2_label = trim((string) (($configured_indent_l2['name'] ?? '') . ' ' . ($configured_indent_l2['surname'] ?? '')));
                        $indent_l2_employee = trim((string) ($configured_indent_l2['employee_id'] ?? ''));
                        if ($indent_l2_employee !== '') {
                            $indent_l2_label .= ' (' . $indent_l2_employee . ')';
                        }
                    }
                    $requester_department_id = (int) ($requester_department_id ?? 0);
                    $requester_department_head = $department_head_map[$requester_department_id] ?? null;
                    $indent_head_label = '';
                    if (!empty($requester_department_head)) {
                        $indent_head_label = trim((string) ($requester_department_head['name'] ?? ''));
                        $indent_head_employee = trim((string) ($requester_department_head['employee_id'] ?? ''));
                        if ($indent_head_employee !== '') {
                            $indent_head_label .= ' (' . $indent_head_employee . ')';
                        }
                    }
                    ?>
                    <?php if (empty($configured_indent_l2)) { ?>
                        <div class="alert alert-danger">
                            Indent approval fallback settings are incomplete. Configure Indent Approver L2 in System Settings &gt; Indent Approval Fallback before creating indents.
                        </div>
                    <?php } else { ?>
                        <div class="alert alert-info" id="indentApprovalSummary">
                            <?php if (!empty($indent_policy['use_department_head_l1']) && !empty($requester_department_head)) { ?>
                                Fallback route will use the requester department head as Level 1 and the configured staff as Level 2.
                            <?php } elseif (!empty($indent_policy['use_department_head_l1'])) { ?>
                                No active department head is mapped for the requester department. Level 1 will fall back to the configured Level 2 approver.
                            <?php } else { ?>
                                Department-head-based Level 1 is disabled. The configured fallback approver will be used for approval routing.
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Request Date <span class="text-danger">*</span></label>
                                <input type="text" name="request_date" class="form-control date" readonly value="<?php echo date($this->customlib->getSchoolDateFormat()); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Required By Date</label>
                                <input type="text" name="required_by_date" class="form-control date" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Priority <span class="text-danger">*</span></label>
                                <select name="priority" class="form-control" required>
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Category</label>
                                <select id="item_category_id" name="item_category_id" class="form-control">
                                    <option value="">Select</option>
                                    <?php foreach ($itemcatlist as $item_category) { ?>
                                        <option value="<?php echo (int) $item_category['id']; ?>"><?php echo html_escape((string) $item_category['item_category']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Indent Approver L1</label>
                                <select name="approver_staff_id" id="indent_approver_l1" class="form-control" <?php echo (!empty($is_super_admin) && !empty($indent_policy['superadmin_can_override_l1'])) ? '' : 'disabled'; ?> data-default-l2-id="<?php echo (int) ($indent_policy['l2_staff_id'] ?? 0); ?>" data-head-id="<?php echo (int) ($requester_department_head['id'] ?? 0); ?>" data-head-name="<?php echo html_escape((string) $indent_head_label); ?>">
                                    <?php if (!empty($requester_department_head)) { ?>
                                        <option value="<?php echo (int) $requester_department_head['id']; ?>"><?php echo html_escape($indent_head_label); ?></option>
                                    <?php } ?>
                                    <?php if (!empty($configured_indent_l2)) { ?>
                                        <option value="<?php echo (int) $configured_indent_l2['id']; ?>"><?php echo html_escape($indent_l2_label); ?></option>
                                    <?php } ?>
                                    <?php foreach (($staff_list ?? []) as $staff) { ?>
                                        <?php
                                        $staff_id = (int) ($staff['id'] ?? 0);
                                        if ($staff_id <= 0) {
                                            continue;
                                        }
                                        $staff_name = trim((string) (($staff['name'] ?? '') . ' ' . ($staff['surname'] ?? '')));
                                        $employee_id = trim((string) ($staff['employee_id'] ?? ''));
                                        $staff_label = $staff_name . ($employee_id !== '' ? ' (' . $employee_id . ')' : '');
                                        ?>
                                        <option value="<?php echo $staff_id; ?>"><?php echo html_escape($staff_label); ?></option>
                                    <?php } ?>
                                </select>
                                <p class="help-block">This is auto-prefilled from the requester department head or falls back to Level 2. Super Admin can change it only when override is enabled.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Indent Approver L2 (Mandatory)</label>
                                <select name="indent_approver_level2_staff_id" id="indent_approver_l2" class="form-control" disabled>
                                    <option value="<?php echo !empty($configured_indent_l2) ? (int) $configured_indent_l2['id'] : 0; ?>"><?php echo html_escape($indent_l2_label !== '' ? $indent_l2_label : 'Select'); ?></option>
                                </select>
                                <p class="help-block">This approver is always the final approver and also acts as the Level 1 fallback when no department head is available.</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Item</label>
                                <select id="item_id" name="item_id" class="form-control">
                                    <option value="">Select Category First</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Item Name (if not in list) <span class="text-danger">*</span></label>
                                <input type="text" name="item_name" class="form-control" placeholder="Optional if Item selected">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Quantity <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>UOM</label>
                                <input type="text" name="uom" class="form-control" placeholder="Nos">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Estimated Unit Cost</label>
                                <input type="number" step="0.01" min="0" name="estimated_unit_cost" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group">
                                <label>Specification</label>
                                <textarea name="spec" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right">Submit Indent</button>
                    <a href="<?php echo site_url('admin/inventoryindent'); ?>" class="btn btn-default">Back</a>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
(function($) {
    'use strict';

    var base_url = '<?php echo base_url(); ?>';
    var indentPolicy = {
        useDepartmentHeadL1: <?php echo !empty($indent_policy['use_department_head_l1']) ? 'true' : 'false'; ?>,
        l2StaffId: <?php echo (int) ($indent_policy['l2_staff_id'] ?? 0); ?>,
        superadminCanOverrideL1: <?php echo !empty($indent_policy['superadmin_can_override_l1']) ? 'true' : 'false'; ?>,
        isSuperAdmin: <?php echo !empty($is_super_admin) ? 'true' : 'false'; ?>,
        departmentHeadId: <?php echo (int) ($requester_department_head['id'] ?? 0); ?>,
        departmentHeadLabel: <?php echo json_encode((string) $indent_head_label); ?>,
        l2Label: <?php echo json_encode((string) $indent_l2_label); ?>
    };

    function setSelectValue($select, value) {
        if (!value) {
            return;
        }
        $select.val(String(value));
        if ($.fn.select2 && $select.hasClass('select2-hidden-accessible')) {
            $select.trigger('change');
        }
    }

    function updateIndentApprovers() {
        var l1Id = indentPolicy.l2StaffId;
        var summary = 'Department-head-based Level 1 is disabled. The configured fallback approver will be used for approval routing.';

        if (indentPolicy.useDepartmentHeadL1) {
            if (indentPolicy.departmentHeadId > 0) {
                l1Id = indentPolicy.departmentHeadId;
                summary = 'Fallback route will use the requester department head as Level 1 and the configured staff as Level 2.';
            } else {
                summary = 'No active department head is mapped for the requester department. Level 1 will fall back to the configured Level 2 approver.';
            }
        }

        setSelectValue($('#indent_approver_l1'), l1Id);
        setSelectValue($('#indent_approver_l2'), indentPolicy.l2StaffId);
        $('#indentApprovalSummary').text(summary);
    }

    function populateItem(itemCategoryId) {
        $('#item_id').html('<option value="">Loading...</option>');
        $.ajax({
            type: 'GET',
            url: base_url + 'admin/itemstock/getItemByCategory',
            data: {item_category_id: itemCategoryId},
            dataType: 'json',
            success: function(data) {
                var html = '<option value="">Select</option>';
                $.each(data || [], function(i, obj) {
                    html += '<option value="' + obj.id + '">' + obj.name + '</option>';
                });
                $('#item_id').html(html);
            },
            error: function() {
                $('#item_id').html('<option value="">Select</option>');
            }
        });
    }

    $('#item_category_id').on('change', function() {
        var categoryId = $(this).val();
        if (categoryId) {
            populateItem(categoryId);
        } else {
            $('#item_id').html('<option value="">Select Category First</option>');
        }
    });

    if ($.fn.select2) {
        $('#indent_approver_l1, #indent_approver_l2').select2({
            width: '100%'
        });
    }

    updateIndentApprovers();
})(jQuery);
</script>
