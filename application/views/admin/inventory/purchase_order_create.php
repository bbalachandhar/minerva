<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-plus-square-o"></i> Create Purchase Order</h1>
    </section>
    <section class="content">
        <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">PO From Approved Indent</h3>
            </div>
            <form method="post" action="<?php echo site_url('admin/inventoryprocurement/storepo'); ?>">
                <div class="box-body">
                    <?php if (!empty($has_rule_engine)) { ?>
                        <div class="alert alert-info">
                            Approval matrix is enabled. Approvers will be auto-routed by configured rules (department/amount).
                            Manual approver fields below are fallback only when no active rule matches.
                        </div>
                    <?php } ?>
                    <?php if (empty($configured_l2_approver)) { ?>
                        <div class="alert alert-danger">
                            PO fallback settings are incomplete. Configure PO Approver L2 in System Settings &gt; PO Approval Fallback before creating manual-route POs.
                        </div>
                    <?php } else { ?>
                        <div class="alert alert-warning" id="poApproverFallbackSummary">
                            Fallback route will prefill Level 1 from the indent department head when available, otherwise Level 1 will fall back to the configured Level 2 approver.
                        </div>
                    <?php } ?>
                    <?php echo $this->customlib->getCSRF(); ?>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>PO Date <span class="text-danger">*</span></label>
                                <input type="text" name="po_date" class="form-control date" readonly value="<?php echo date($this->customlib->getSchoolDateFormat()); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Approved Indent <span class="text-danger">*</span></label>
                                <select name="indent_id" class="form-control" required>
                                    <option value="">Select</option>
                                    <?php foreach ($approved_indents as $row) { ?>
                                        <option value="<?php echo (int) $row['id']; ?>" data-department-id="<?php echo (int) ($row['department_id'] ?? 0); ?>">
                                            <?php echo html_escape((string) $row['indent_no']); ?>
                                            (<?php echo html_escape((string) $row['request_date']); ?>)
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Supplier <span class="text-danger">*</span></label>
                                <select name="supplier_id" class="form-control" required>
                                    <option value="">Select</option>
                                    <?php foreach ($suppliers as $row) { ?>
                                        <option value="<?php echo (int) $row['id']; ?>"><?php echo html_escape((string) $row['item_supplier']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Expected Delivery Date</label>
                                <input type="text" name="expected_delivery_date" class="form-control date" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tax %</label>
                                <input type="number" min="0" step="0.01" name="tax_percent" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>PO Approver L1 (Fallback)</label>
                                <select name="approver_staff_id" id="po_approver_l1" class="form-control" <?php echo (!empty($is_super_admin) && !empty($po_fallback_settings['superadmin_can_override_l1'])) ? '' : 'disabled'; ?>>
                                    <option value="">Select</option>
                                    <?php foreach ($approvers as $row) { ?>
                                        <option value="<?php echo (int) $row['id']; ?>">
                                            <?php echo html_escape(trim((string) $row['name'] . ' ' . $row['surname'])); ?>
                                            <?php if (!empty($row['employee_id'])) { ?>
                                                (<?php echo html_escape((string) $row['employee_id']); ?>)
                                            <?php } ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <p class="help-block">
                                    <?php if (!empty($is_super_admin) && !empty($po_fallback_settings['superadmin_can_override_l1'])) { ?>
                                        Super Admin can override Level 1 after the system prefills it.
                                    <?php } else { ?>
                                        This is auto-prefilled from the indent department head or Level 2 fallback.
                                    <?php } ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>PO Approver L2 (Fallback)</label>
                                <select name="approver_level2_staff_id" id="po_approver_l2" class="form-control" disabled>
                                    <option value="">Select</option>
                                    <?php foreach ($approvers as $row) { ?>
                                        <option value="<?php echo (int) $row['id']; ?>">
                                            <?php echo html_escape(trim((string) $row['name'] . ' ' . $row['surname'])); ?>
                                            <?php if (!empty($row['employee_id'])) { ?>
                                                (<?php echo html_escape((string) $row['employee_id']); ?>)
                                            <?php } ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <p class="help-block">This stays fixed to the configured final approver.</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right">Create PO</button>
                    <a href="<?php echo site_url('admin/inventoryprocurement/purchaseorders'); ?>" class="btn btn-default">Back</a>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
(function($) {
    'use strict';

    var fallbackSettings = <?php echo json_encode($po_fallback_settings ?? []); ?>;
    var departmentHeadMap = <?php echo json_encode($department_head_map ?? []); ?>;
    var configuredL2 = <?php echo json_encode([
        'id' => (int) ($configured_l2_approver['id'] ?? 0),
        'name' => trim((string) (($configured_l2_approver['name'] ?? '') . ' ' . ($configured_l2_approver['surname'] ?? ''))),
        'employee_id' => (string) ($configured_l2_approver['employee_id'] ?? ''),
    ]); ?>;

    function approverLabel(row) {
        if (!row || !row.id) {
            return '';
        }

        var text = row.name || '';
        if (row.employee_id) {
            text += ' (' + row.employee_id + ')';
        }
        return $.trim(text);
    }

    function setSelectValue(selector, value) {
        $(selector).val(value ? String(value) : '');
        if ($.fn.select2 && $(selector).data('select2')) {
            $(selector).trigger('change');
        }
    }

    function updateFallbackApprovers() {
        var $indent = $('select[name="indent_id"] option:selected');
        var departmentId = parseInt($indent.data('departmentId') || 0, 10);
        var l2Id = parseInt(fallbackSettings.l2_staff_id || 0, 10);
        var l1Id = l2Id;
        var summary = 'Fallback route will use the configured Level 2 approver for both levels.';

        if (fallbackSettings.use_department_head_l1 && departmentId && departmentHeadMap[String(departmentId)] && departmentHeadMap[String(departmentId)].id) {
            l1Id = parseInt(departmentHeadMap[String(departmentId)].id, 10);
            summary = 'Fallback route will use the indent department head as Level 1 and the configured staff as Level 2.';
        } else if (l2Id > 0) {
            summary = 'No active department head is mapped for this indent. Level 1 will fall back to the configured Level 2 approver.';
        }

        setSelectValue('#po_approver_l1', l1Id > 0 ? l1Id : '');
        setSelectValue('#po_approver_l2', l2Id > 0 ? l2Id : '');

        if (configuredL2.id) {
            summary += ' Final approver: ' + approverLabel(configuredL2) + '.';
        }
        $('#poApproverFallbackSummary').text(summary);
    }

    if ($.fn.select2) {
        $('select[name="indent_id"], select[name="supplier_id"], #po_approver_l1, #po_approver_l2').select2({
            width: '100%'
        });
    }

    $('select[name="indent_id"]').on('change', updateFallbackApprovers);
    updateFallbackApprovers();
})(jQuery);
</script>
