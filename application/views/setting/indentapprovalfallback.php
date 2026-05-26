<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <?php $this->load->view('setting/_settingmenu'); ?>
            <div class="col-md-10">
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><i class="fa fa-check-square-o"></i> Indent Approval Fallback</h3>
                    </div>
                    <div class="box-body">
                        <form id="indentApprovalFallbackForm" method="post">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Indent Approver L1 (Mandatory)</label>
                                        <select name="indent_fallback_l1_staff_id" id="indent_fallback_l1_staff_id" class="form-control" required>
                                            <option value="0">Select</option>
                                            <?php foreach (($staff_list ?? []) as $staff) { ?>
                                                <?php
                                                $staff_id = (int) ($staff['id'] ?? 0);
                                                $staff_name = trim((string) (($staff['name'] ?? '') . ' ' . ($staff['surname'] ?? '')));
                                                $employee_id = trim((string) ($staff['employee_id'] ?? ''));
                                                ?>
                                                <option value="<?php echo $staff_id; ?>" <?php echo ((int) ($indent_fallback_policy['l1_staff_id'] ?? 0) === $staff_id) ? 'selected' : ''; ?>>
                                                    <?php echo html_escape($staff_name . ($employee_id !== '' ? ' (' . $employee_id . ')' : '')); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <p class="help-block">This approver is assigned as the Level 1 indent approver.</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="indent_fallback_superadmin_can_override_l1" value="1" <?php echo !empty($indent_fallback_policy['superadmin_can_override_l1']) ? 'checked' : ''; ?>>
                                            Allow Super Admin to override Indent Approver L1
                                        </label>
                                        <p class="help-block">Super Admin can change only Level 1 on indent creation. Level 2 remains fixed to the configured fallback approver.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Indent Approver L2 (Mandatory)</label>
                                        <select name="indent_fallback_l2_staff_id" id="indent_fallback_l2_staff_id" class="form-control" required>
                                            <option value="0">Select</option>
                                            <?php foreach (($staff_list ?? []) as $staff) { ?>
                                                <?php
                                                $staff_id = (int) ($staff['id'] ?? 0);
                                                $staff_name = trim((string) (($staff['name'] ?? '') . ' ' . ($staff['surname'] ?? '')));
                                                $employee_id = trim((string) ($staff['employee_id'] ?? ''));
                                                ?>
                                                <option value="<?php echo $staff_id; ?>" <?php echo ((int) ($indent_fallback_policy['l2_staff_id'] ?? 0) === $staff_id) ? 'selected' : ''; ?>>
                                                    <?php echo html_escape($staff_name . ($employee_id !== '' ? ' (' . $employee_id . ')' : '')); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <p class="help-block">This approver is always used as the final indent approver.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary" id="saveIndentApprovalFallbackBtn" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Saving...">
                                    <i class="fa fa-save"></i> Save Indent Approval Fallback
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if ($.fn.select2) {
            $('#indent_fallback_l1_staff_id, #indent_fallback_l2_staff_id').select2({
                width: '100%',
                placeholder: 'Search by name or employee ID...',
                allowClear: true
            });
        }

        $('#indentApprovalFallbackForm').on('submit', function(e) {
            e.preventDefault();
            var $btn = $('#saveIndentApprovalFallbackBtn');
            $btn.button('loading');
            $.ajax({
                url: '<?php echo site_url('schsettings/saveindentapprovalfallback'); ?>',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response && (response.status === 1 || response.status === '1')) {
                        successMsg('Indent approval fallback saved successfully.');
                    } else {
                        errorMsg(response && response.message ? response.message : 'Failed to save indent approval fallback.');
                    }
                },
                error: function() {
                    errorMsg('Failed to save indent approval fallback.');
                },
                complete: function() {
                    $btn.button('reset');
                }
            });
        });
    });
</script>