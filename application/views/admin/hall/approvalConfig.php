
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-cogs"></i> <?php echo $this->lang->line('approval_configuration'); ?></h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $title; ?></h3>
                    </div>
                    <form action="<?php echo isset($config->id) ? site_url('admin/hall/edit_approval_config/' . $config->id) : site_url('admin/hall/approval_configuration') ?>" id="approvalconfigform" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php echo $this->session->flashdata('msg') ?>
                            <?php } ?>
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="form-group">
                                <label for="approver_type"><?php echo $this->lang->line('approver_type'); ?></label><small class="req"> *</small>
                                <select autofocus="" id="approver_type" name="approver_type" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <option value="role" <?php echo (isset($config->approver_type) && $config->approver_type == 'role') ? 'selected' : set_select('approver_type', 'role'); ?>><?php echo $this->lang->line('role'); ?></option>
                                    <option value="staff" <?php echo (isset($config->approver_type) && $config->approver_type == 'staff') ? 'selected' : set_select('approver_type', 'staff'); ?>><?php echo $this->lang->line('staff'); ?></option>
                                </select>
                                <span class="text-danger"><?php echo form_error('approver_type'); ?></span>
                            </div>
                            <div class="form-group" id="approver_id_div">
                                <label for="approver_id"><?php echo $this->lang->line('approver'); ?></label><small class="req"> *</small>
                                    <select id="approver_id" name="approver_id" class="form-control select2">
                                        <option value="">Select</option>
                                        <option value="1">Super Admin (9000)</option>
                                        <option value="217">S.KARUPPASWAMY (MCE2014ME002)</option>
                                        <option value="379">ABILASH (MCE2024MBA014)</option>
                                    </select>                                <span class="text-danger"><?php echo form_error('approver_id'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="hall_id"><?php echo $this->lang->line('hall'); ?></label>
                                <select id="hall_id" name="hall_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('all_halls'); ?></option>
                                    <?php
                                    foreach ($hallList as $hall) {
                                        ?>
                                        <option value="<?php echo $hall->id ?>" <?php echo (isset($config->hall_id) && $config->hall_id == $hall->id) ? 'selected' : set_select('hall_id', $hall->id); ?>><?php echo $hall->name . ' (' . $hall->location . ')' ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('hall_id'); ?></span>
                            </div>
                            <div class="form-group">
                                <label class="control-label"><?php echo $this->lang->line('can_approve'); ?></label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="can_approve" value="1" <?php echo (isset($config->can_approve) && $config->can_approve == '1') ? 'checked' : set_checkbox('can_approve', '1', true); ?>> <?php echo $this->lang->line('yes'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('approval_configuration_list'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover approval-config-table">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('approver_type'); ?></th>
                                        <th><?php echo $this->lang->line('approver'); ?></th>
                                        <th><?php echo $this->lang->line('hall'); ?></th>
                                        <th><?php echo $this->lang->line('can_approve'); ?></th>
                                        <th class="text-right no-print"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (empty($configList)) {
                                        ?>
                                        <tr>
                                            <td colspan="5" class="text-danger text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                        </tr>
                                        <?php
                                    } else {
                                        foreach ($configList as $config) {
                                            ?>
                                            <tr>
                                                <td class="mailbox-name"><?php echo $this->lang->line($config->approver_type); ?></td>
                                                <td class="mailbox-name">
                                                    <?php
                                                    if ($config->approver_type == 'role') {
                                                        echo $config->role_name;
                                                    } else { // staff
                                                        echo $config->staff_name . ' (' . $config->employee_id . ')';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="mailbox-name"><?php echo !empty($config->hall_name) ? $config->hall_name : $this->lang->line('all_halls'); ?></td>
                                                <td class="mailbox-name"><?php echo ($config->can_approve) ? $this->lang->line('yes') : $this->lang->line('no'); ?></td>
                                                <td class="mailbox-date pull-right no-print">
                                                    <a href="<?php echo base_url(); ?>admin/hall/edit_approval_config/<?php echo $config->id ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <a href="<?php echo base_url(); ?>admin/hall/delete_approval_config/<?php echo $config->id ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                        <i class="fa fa-remove"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        // Function to load approvers based on type
        function loadApprovers(approverType, selectedId = '') {
            var approverSelect = $('#approver_id');
            approverSelect.empty();
            approverSelect.append($('<option></option>').attr('value', '').text('<?php echo $this->lang->line('select'); ?>'));

            if (approverType === 'role') {
                var roleList = <?php echo json_encode($roleList); ?>;
                $.each(roleList, function (i, role) {
                    approverSelect.append($('<option></option>').attr('value', role.id).text(role.name));
                });
                        } else if (approverType === 'staff') {
                            var staffList = <?php echo json_encode($staffList); ?>;
                            $.each(staffList, function (i, staff) {
                                approverSelect.append($('<option></option>').attr('value', staff.id).text(staff.name + ' (' + staff.employee_id + ')'));
                            });
                        }    if (selectedId) {
        approverSelect.val(String(selectedId));
        console.log('approverSelect.val(): ' + approverSelect.val());
    }        }

        // Initial load if approver_type is already set (e.g., after form validation error or edit mode)
        var initialApproverType = $('#approver_type option:selected').val(); // Get the selected value
        var initialApproverId = '<?php echo isset($config) ? ($config->approver_type == "role" ? $config->role_id : $config->staff_id) : set_value('approver_id'); ?>';
        if (initialApproverType) {
            loadApprovers(initialApproverType, initialApproverId);
        }

        // On change of approver type
        $('#approver_type').on('change', function () {
            var approverType = $(this).val();
            loadApprovers(approverType);
        });

        $('#approver_id').select2();

        // Initialize DataTable

    });
</script>
<script type="text/javascript">
    $(document).on('click', '.delete-approval-config', function () {
        var configId = $(this).data('id');
        if (confirm('<?php echo $this->lang->line('delete_confirm') ?>')) {
            $.ajax({
                url: '<?php echo base_url(); ?>admin/hall/delete_approval_config/' + configId,
                type: 'POST',
                dataType: 'json',
                success: function (res) {
                    if (res.status === "fail") {
                        var message = "";
                        $.each(res.error, function (index, value) {
                            message += value;
                        });
                        errorMsg(message);
                    } else {
                        successMsg(res.message);
                        // Reload the datatable after successful deletion
                        $('.approval-config-table').DataTable().ajax.reload();
                    }
                },
                error: function (xhr) {
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                }
            });
        }
    });
</script>