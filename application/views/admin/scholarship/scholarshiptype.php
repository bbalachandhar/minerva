<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-graduation-cap"></i> Scholarship Types</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Scholarship Types</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Scholarship Type List</h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('scholarship_type', 'can_add')): ?>
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addScholarshipModal">
                                <i class="fa fa-plus"></i> Add Scholarship Type
                            </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#settingsModal">
                                <i class="fa fa-cog"></i> Approver Settings
                            </button>
                            <a href="<?php echo site_url('admin/scholarshipapplication'); ?>" class="btn btn-info btn-sm">
                                <i class="fa fa-list"></i> View Applications
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php echo $this->session->flashdata('msg'); ?>
                    </div>
                    <div class="callout callout-info" style="margin:10px 15px 0">
                        <p><i class="fa fa-info-circle"></i>
                        <strong>Verifier is set per scholarship type.</strong>
                        Each type can have a different verifier (e.g., PET for sports, admin staff for management).
                        The final <strong>Approver</strong> is a single person set via <em>Approver Settings</em>.</p>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered table-hover table-striped example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Scholarship Name</th>
                                    <th>Description</th>
                                    <th>Amount (&#8377;)</th>
                                    <th>Verifier</th>
                                    <th>Sort</th>
                                    <th>Status</th>
                                    <th class="noExport text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($scholarship_types as $i => $t): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo htmlspecialchars($t['name']); ?></td>
                                    <td><?php echo htmlspecialchars($t['description'] ?? ''); ?></td>
                                    <td><?php echo isset($t['amount']) && $t['amount'] !== null ? '&#8377; ' . number_format((float)$t['amount'], 2) : '<span class="text-muted">—</span>'; ?></td>
                                    <td>
                                        <?php if (!empty($t['verifier_id'])): ?>
                                            <?php
                                            $vstaff = null;
                                            foreach ($staff_list as $s) {
                                                if ((int)$s['id'] === (int)$t['verifier_id']) { $vstaff = $s; break; }
                                            }
                                            echo $vstaff ? htmlspecialchars($vstaff['name'] . ' ' . $vstaff['surname']) : '<span class="text-muted">Staff #' . $t['verifier_id'] . '</span>';
                                            ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo (int)$t['sort_order']; ?></td>
                                    <td>
                                        <?php if ($t['is_active']): ?>
                                            <span class="label label-success">Active</span>
                                        <?php else: ?>
                                            <span class="label label-default">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                Actions <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                <?php if ($this->rbac->hasPrivilege('scholarship_type', 'can_edit')): ?>
                                                <li>
                                                    <a href="<?php echo site_url('admin/scholarshiptype/edit/' . $t['id']); ?>">
                                                        <i class="fa fa-edit"></i> Edit
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                                <?php if ($this->rbac->hasPrivilege('scholarship_type', 'can_delete')): ?>
                                                <li>
                                                    <a href="<?php echo site_url('admin/scholarshiptype/delete/' . $t['id']); ?>"
                                                       onclick="return confirm('Delete this scholarship type?')">
                                                        <i class="fa fa-trash text-danger"></i> Delete
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($scholarship_types)): ?>
                                <tr><td colspan="8" class="text-center text-muted">No scholarship types found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Add Scholarship Type Modal -->
<?php if ($this->rbac->hasPrivilege('scholarship_type', 'can_add')): ?>
<div class="modal fade" id="addScholarshipModal" tabindex="-1" role="dialog" aria-labelledby="addScholarshipModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="addScholarshipModalLabel"><i class="fa fa-graduation-cap"></i> Add Scholarship Type</h4>
            </div>
            <form action="<?php echo site_url('admin/scholarshiptype'); ?>" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Scholarship Name <small class="req">*</small></label>
                        <input type="text" name="name" class="form-control" value="<?php echo set_value('name'); ?>" maxlength="300" placeholder="Enter scholarship name"/>
                        <span class="text-danger"><?php echo form_error('name'); ?></span>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional description"><?php echo set_value('description'); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Amount (&#8377;)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" value="<?php echo set_value('amount'); ?>" placeholder="Leave blank if not fixed"/>
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?php echo set_value('sort_order', 0); ?>" min="0"/>
                    </div>
                    <div class="form-group">
                        <label>Verifier <small class="text-muted">(who verifies applications of this type)</small></label>
                        <select name="verifier_id" class="form-control select2-modal">
                            <option value="">-- None assigned --</option>
                            <?php foreach ($staff_list as $s): ?>
                            <option value="<?php echo $s['id']; ?>"
                                <?php echo set_value('verifier_id') == $s['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['name'] . ' ' . $s['surname']); ?>
                                <?php if (!empty($s['designation'])): ?>(<?php echo htmlspecialchars($s['designation']); ?>)<?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Approver Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" role="dialog" aria-labelledby="settingsModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="settingsModalLabel"><i class="fa fa-cog"></i> Final Approver Setting</h4>
            </div>
            <div class="modal-body">
                <div class="callout callout-info">
                    <p><strong>Global Approver:</strong><br/>
                    The final approver makes the grant decision for <em>all</em> scholarship types.<br/>
                    The verifier for each type is set individually on each type's edit page.</p>
                </div>
                <div id="settingsMsg"></div>
                <form id="settingsForm">
                    <div class="form-group">
                        <label>Final Approver <small class="req">*</small></label>
                        <select name="approver_id" id="settingsApprover" class="form-control">
                            <option value="">-- Select Approver --</option>
                            <?php foreach ($staff_list as $s): ?>
                            <option value="<?php echo $s['id']; ?>"
                                <?php echo (isset($settings['approver_id']) && (int)$settings['approver_id'] === (int)$s['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['name'] . ' ' . $s['surname']); ?>
                                <?php if (!empty($s['designation'])): ?>(<?php echo htmlspecialchars($s['designation']); ?>)<?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="settingsSaveBtn"><i class="fa fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    // --- Approver Settings Modal JS ---
    $('#settingsSaveBtn').on('click', function () {
        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        var data = { approver_id: $('#settingsApprover').val() };
        $.post('<?php echo site_url('admin/scholarshipapplication/settings_ajax'); ?>', data, function (res) {
            if (res.success) {
                $('#settingsMsg').html('<div class="alert alert-success">' + res.msg + '</div>');
                setTimeout(function () { location.reload(); }, 800);
            } else {
                $('#settingsMsg').html('<div class="alert alert-danger">' + res.msg + '</div>');
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
            }
        }, 'json').fail(function () {
            $('#settingsMsg').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
        });
    });

    $('#settingsModal').on('show.bs.modal', function () {
        $('#settingsMsg').html('');
        $('#settingsSaveBtn').prop('disabled', false).html('<i class="fa fa-save"></i> Save');
    });

    $('#settingsApprover').select2({ dropdownParent: $('#settingsModal'), width: '100%' });

    // --- Add Scholarship Modal JS ---
    $('#addScholarshipModal').on('shown.bs.modal', function () {
        $(this).find('.select2-modal').select2({ dropdownParent: $('#addScholarshipModal'), width: '100%' });
    });

    // Auto-open modal if there are validation errors
    <?php if (validation_errors()): ?>
    $('#addScholarshipModal').modal('show');
    <?php endif; ?>
});
</script>
