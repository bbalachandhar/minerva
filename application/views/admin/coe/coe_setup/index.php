<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-university"></i> <?php echo $this->lang->line('coe_exam_regulations'); ?><button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>
        <div class="row">
            <!-- Filter by session -->
            <div class="col-md-12">
                <form method="get" action="<?php echo site_url('coe/coe_setup'); ?>">
                    <div class="box box-default">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('session'); ?></label>
                                        <select name="session_id" class="form-control" onchange="this.form.submit()">
                                            <?php foreach ($session_list as $s): ?>
                                                <option value="<?php echo $s["id"]; ?>" <?php echo ($s["id"] == $selected_session) ? 'selected' : ''; ?>>
                                                    <?php echo $s["session"]; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <?php if ($this->rbac->hasPrivilege('coe_setup', 'can_add')): ?>
                                <div class="col-md-4" style="margin-top:25px;">
                                    <a href="<?php echo site_url('coe/coe_setup/add'); ?>" class="btn btn-primary btn-sm">
                                        <i class="fa fa-plus-circle"></i> <?php echo $this->lang->line('coe_add_regulation'); ?>
                                    </a>
                                    <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#cloneModal" style="margin-left:6px;">
                                        <i class="fa fa-copy"></i> Clone from Session
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Regulations table -->
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('coe_exam_regulations'); ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="regulationsTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo $this->lang->line('class'); ?></th>
                                    <th><?php echo $this->lang->line('department'); ?></th>
                                    <th><?php echo $this->lang->line('coe_regulation_type'); ?></th>
                                    <th><?php echo $this->lang->line('coe_min_attendance_pct'); ?></th>
                                    <th>Int%&nbsp;/&nbsp;Ext%</th>
                                    <th><?php echo $this->lang->line('coe_has_credit_system'); ?></th>
                                    <th><?php echo $this->lang->line('coe_grading_scheme'); ?></th>
                                    <th><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($regulations)): ?>
                                    <tr><td colspan="9" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td></tr>
                                <?php else: ?>
                                    <?php foreach ($regulations as $i => $r): ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><?php echo htmlspecialchars($r->class_name); ?></td>
                                        <td><?php echo htmlspecialchars($r->department_name ?: '—'); ?></td>
                                        <td>
                                            <?php if ($r->regulation_type === 'affiliated'): ?>
                                                <span class="label label-info"><?php echo $this->lang->line('coe_affiliated'); ?></span>
                                            <?php else: ?>
                                                <span class="label label-success"><?php echo $this->lang->line('coe_autonomous'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $r->min_attendance_pct; ?>%</td>
                                        <td><?php echo $r->internal_marks_pct; ?>% / <?php echo $r->external_marks_pct; ?>%</td>
                                        <td><?php echo $r->has_credit_system ? '<span class="label label-success">CBCS</span>' : '<span class="label label-default">No</span>'; ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $r->grading_scheme)); ?></td>
                                        <td>
                                            <?php if ($this->rbac->hasPrivilege('coe_setup', 'can_edit')): ?>
                                            <a href="<?php echo site_url('coe/coe_setup/edit/' . $r->id); ?>" class="btn btn-xs btn-default" title="<?php echo $this->lang->line('edit'); ?>">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if ($this->rbac->hasPrivilege('coe_setup', 'can_delete')): ?>
                                            <a href="<?php echo site_url('coe/coe_setup/delete/' . $r->id); ?>" class="btn btn-xs btn-danger confirm_delete" title="<?php echo $this->lang->line('delete'); ?>">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#regulationsTable').DataTable({
        "paging":   true,
        "ordering": true,
        "info":     true,
        "searching": true,
        "columnDefs": [{ "orderable": false, "targets": -1 }]
    });
});

$(document).on('click', '.confirm_delete', function(e) {
    e.preventDefault();
    var url = $(this).attr('href');
    Swal.fire({
        title: '<?php echo $this->lang->line("are_you_sure"); ?>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<?php echo $this->lang->line("delete"); ?>'
    }).then(function(result) {
        if (result.value) { window.location = url; }
    });
});
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'exam_regulations']); ?>

<!-- Clone from Session Modal -->
<div class="modal fade" id="cloneModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:10px;overflow:hidden;">
            <form method="POST" action="<?php echo site_url('coe/coe_setup/clone_session'); ?>">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                <div class="modal-header" style="background:linear-gradient(135deg,#3c8dbc,#1a6091);color:#fff;border:none;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.9;">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-copy"></i> Clone Regulations from Another Session</h4>
                </div>
                <div class="modal-body" style="padding:24px;">
                    <p class="text-muted" style="font-size:13px;">All regulations from the source session will be copied to the target session. Classes that already have a regulation in the target session are skipped.</p>
                    <div class="form-group">
                        <label style="font-weight:600;">Copy FROM session <span class="text-danger">*</span></label>
                        <select name="from_session_id" class="form-control" required>
                            <option value="">— Select source session —</option>
                            <?php foreach ($session_list as $s): ?>
                                <option value="<?php echo $s["id"]; ?>"><?php echo htmlspecialchars($s["session"]); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600;">Copy TO session <span class="text-danger">*</span></label>
                        <select name="to_session_id" class="form-control" required>
                            <?php foreach ($session_list as $s): ?>
                                <option value="<?php echo $s["id"]; ?>" <?php echo ($s["id"] == $selected_session) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s["session"]); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f0f0f0;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-copy"></i> Clone Now</button>
                </div>
            </form>
        </div>
    </div>
</div>
