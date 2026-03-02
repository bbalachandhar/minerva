<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <?php $this->load->view('setting/_settingmenu'); ?>
            <div class="col-md-10">
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><i class="fa fa-sliders"></i> Leave Management Policy</h3>
                    </div>
                    <div class="box-body">
                        <form id="leavePolicyForm" method="post">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Substitution Required Roles</label>
                                        <select name="leave_substitution_required_roles[]" id="leave_substitution_required_roles" class="form-control leave-policy-multiselect" multiple>
                                            <?php foreach (($all_roles ?? []) as $role) { ?>
                                                <?php $role_id = (int) ($role['id'] ?? 0); ?>
                                                <option value="<?php echo $role_id; ?>" <?php echo in_array($role_id, $leave_policy['substitution_required_roles'] ?? [], true) ? 'selected' : ''; ?>>
                                                    <?php echo $role['name']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <p class="help-block">Only these roles must provide substitute mapping during leave.</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Roles Allowed to Apply Leave for Past Dates</label>
                                        <select name="leave_past_date_allowed_roles[]" id="leave_past_date_allowed_roles" class="form-control leave-policy-multiselect" multiple>
                                            <?php foreach (($all_roles ?? []) as $role) { ?>
                                                <?php $role_id = (int) ($role['id'] ?? 0); ?>
                                                <option value="<?php echo $role_id; ?>" <?php echo in_array($role_id, $leave_policy['past_date_allowed_roles'] ?? [], true) ? 'selected' : ''; ?>>
                                                    <?php echo $role['name']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <p class="help-block">Only selected roles can apply leave/OD for previous dates. Others can apply for today and future dates only.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="leave_enable_half_day" value="1" <?php echo !empty($leave_policy['half_day_enabled']) ? 'checked' : ''; ?>>
                                            Enable Half-Day Leave
                                        </label>
                                        <p class="help-block">Allow staff to apply half-day leave (first half/second half).</p>
                                    </div>
                                </div>

                                <input type="hidden" name="leave_half_day_allowed_roles[]" value="">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Half-Day Allowed Leave Types</label>
                                        <select name="leave_half_day_allowed_types[]" id="leave_half_day_allowed_types" class="form-control leave-policy-multiselect" multiple>
                                            <?php foreach (($leave_types ?? []) as $leave_type) { ?>
                                                <?php $leave_type_id = (int) ($leave_type['id'] ?? 0); ?>
                                                <option value="<?php echo $leave_type_id; ?>" <?php echo in_array($leave_type_id, $leave_policy['half_day_allowed_types'] ?? [], true) ? 'selected' : ''; ?>>
                                                    <?php echo $leave_type['type']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <p class="help-block">If empty, all leave types can be used for half-day leave.</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Leave Approver</label>
                                        <select name="leave_approver_id" id="leave_approver_id" class="form-control">
                                            <option value="0">Select</option>
                                            <?php foreach (($staff_list ?? []) as $staff) { ?>
                                                <?php
                                                $staff_id = (int) ($staff['id'] ?? 0);
                                                $staff_name = trim((string) (($staff['name'] ?? '') . ' ' . ($staff['surname'] ?? '')));
                                                $employee_id = trim((string) ($staff['employee_id'] ?? ''));
                                                $designation = trim((string) ($staff['designation'] ?? ''));
                                                ?>
                                                <option value="<?php echo $staff_id; ?>" <?php echo ((int) ($leave_policy['leave_approver_id'] ?? 0) === $staff_id) ? 'selected' : ''; ?>>
                                                    <?php echo html_escape($staff_name . ($employee_id !== '' ? ' (' . $employee_id . ')' : '') . ($designation !== '' ? ' - ' . $designation : '')); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <p class="help-block">Selected staff will be leave approver. If no HOD is found, this approver is used as recommender fallback.</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Self Recommender + Approver Roles</label>
                                        <select name="leave_self_approve_roles[]" id="leave_self_approve_roles" class="form-control leave-policy-multiselect" multiple>
                                            <?php foreach (($all_roles ?? []) as $role) { ?>
                                                <?php $role_id = (int) ($role['id'] ?? 0); ?>
                                                <option value="<?php echo $role_id; ?>" <?php echo in_array($role_id, $leave_policy['self_approve_roles'] ?? [], true) ? 'selected' : ''; ?>>
                                                    <?php echo $role['name']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <p class="help-block">For these roles, recommender and approver will be the same employee.</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Working Day Override Holiday Types</label>
                                        <input type="text" name="leave_workday_override_types" id="leave_workday_override_types" class="form-control" value="<?php echo html_escape($leave_policy['workday_override_types'] ?? ''); ?>" placeholder="compensation,comp-off,compoff,compensatory off">
                                        <p class="help-block">Comma-separated holiday type labels that should be treated as working days.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary" id="saveLeavePolicyBtn" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Saving...">
                                    <i class="fa fa-save"></i> Save Leave Policy
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
            $('.leave-policy-multiselect').select2({
                width: '100%',
                closeOnSelect: false,
                placeholder: 'Select one or more',
                allowClear: true
            });
        }

        $('#leavePolicyForm').on('submit', function(e) {
            e.preventDefault();
            var $btn = $('#saveLeavePolicyBtn');
            $btn.button('loading');
            $.ajax({
                url: '<?php echo site_url('schsettings/saveleavepolicy'); ?>',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response && (response.status === 1 || response.status === '1')) {
                        successMsg('Leave policy saved successfully.');
                    } else {
                        errorMsg('Failed to save leave policy.');
                    }
                },
                error: function() {
                    errorMsg('Failed to save leave policy.');
                },
                complete: function() {
                    $btn.button('reset');
                }
            });
        });
    });
</script>
