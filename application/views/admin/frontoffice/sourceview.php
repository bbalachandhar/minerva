<div class="content-wrapper" style="min-height: 348px;">
    <section class="content">
        <div class="row">
            <!-- Navigation Pills -->
            <div class="col-md-12" style="margin-bottom:16px;">
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        <a href="<?php echo site_url('admin/visitorspurpose') ?>" class="btn btn-default btn-sm" style="border-radius:20px; font-weight:600; font-size:12px;">
                            <i class="fa fa-bullseye"></i> <?php echo $this->lang->line('purpose'); ?>
                        </a>
                        <a href="<?php echo site_url('admin/complainttype') ?>" class="btn btn-default btn-sm" style="border-radius:20px; font-weight:600; font-size:12px;">
                            <i class="fa fa-exclamation-circle"></i> <?php echo $this->lang->line('complaint_type'); ?>
                        </a>
                        <a href="<?php echo site_url('admin/source') ?>" class="btn btn-primary btn-sm" style="border-radius:20px; font-weight:600; font-size:12px;">
                            <i class="fa fa-share-alt"></i> <?php echo $this->lang->line('source'); ?>
                        </a>
                        <a href="<?php echo site_url('admin/reference') ?>" class="btn btn-default btn-sm" style="border-radius:20px; font-weight:600; font-size:12px;">
                            <i class="fa fa-link"></i> <?php echo $this->lang->line('reference'); ?>
                        </a>
                    </div>
                    <?php if ($this->rbac->hasPrivilege('setup_font_office', 'can_add')) { ?>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openSourceModal()" style="border-radius:6px; font-weight:600;">
                        <i class="fa fa-plus"></i> <?php echo $this->lang->line('add_source'); ?>
                    </button>
                    <?php } ?>
                </div>
            </div>

            <!-- Source List -->
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('source_list'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?>
                        <div class="download_label"><?php echo $this->lang->line('source_list'); ?></div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered example">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">#</th>
                                        <th><?php echo $this->lang->line('source'); ?></th>
                                        <th><?php echo $this->lang->line('description'); ?></th>
                                        <th class="text-right noExport" style="width:100px;"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($source_list)) {
                                        $i = 1;
                                        foreach ($source_list as $key => $value) { ?>
                                            <tr>
                                                <td style="color:#94a3b8; font-weight:600;"><?php echo $i++; ?></td>
                                                <td><strong><?php echo $value['source']; ?></strong></td>
                                                <td style="color:#64748b;"><?php echo $value['description']; ?></td>
                                                <td class="text-right" style="white-space:nowrap;">
                                                    <?php if ($this->rbac->hasPrivilege('setup_font_office', 'can_edit')) { ?>
                                                        <a href="javascript:void(0)" onclick="openSourceModal('<?php echo $value['id']; ?>', '<?php echo addslashes(htmlspecialchars($value['source'], ENT_QUOTES)); ?>', '<?php echo addslashes(htmlspecialchars($value['description'], ENT_QUOTES)); ?>')" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if ($this->rbac->hasPrivilege('setup_font_office', 'can_delete')) { ?>
                                                        <a href="javascript:void(0)" onclick="deleteSource('<?php echo $value['id']; ?>', '<?php echo addslashes(htmlspecialchars($value['source'], ENT_QUOTES)); ?>')" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>">
                                                            <i class="fa fa-remove"></i>
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="sourceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content" style="border-radius:12px; overflow:hidden;">
            <div class="modal-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:16px 20px;">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" id="sourceModalTitle" style="font-weight:700; font-size:16px;">
                    <i class="fa fa-share-alt" style="color:#4f46e5;"></i> <?php echo $this->lang->line('add_source'); ?>
                </h4>
            </div>
            <form id="sourceForm" method="post" action="<?php echo site_url('admin/source'); ?>">
                <?php echo $this->customlib->getCSRF(); ?>
                <input type="hidden" id="source_edit_id" name="edit_id" value="">
                <div class="modal-body" style="padding:20px;">
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; text-transform:uppercase; color:#475569; letter-spacing:0.5px;">
                            <?php echo $this->lang->line('source'); ?> <span class="req">*</span>
                        </label>
                        <input class="form-control" id="modal_source" name="source" value="" placeholder="e.g. Walk-in, Website, Social Media" autofocus>
                        <span class="text-danger" id="source_error"></span>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-size:12px; font-weight:600; text-transform:uppercase; color:#475569; letter-spacing:0.5px;">
                            <?php echo $this->lang->line('description'); ?>
                        </label>
                        <textarea class="form-control" id="modal_description" name="description" rows="3" placeholder="Optional description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e2e8f0; padding:12px 20px;">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary btn-sm" id="sourceSubmitBtn">
                        <i class="fa fa-check"></i> <?php echo $this->lang->line('save'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openSourceModal(id, source, description) {
    $('#source_error').text('');
    if (id) {
        $('#sourceModalTitle').html('<i class="fa fa-pencil" style="color:#4f46e5;"></i> <?php echo $this->lang->line("edit_source"); ?>');
        $('#sourceForm').attr('action', '<?php echo site_url("admin/source/edit/"); ?>' + id);
        $('#source_edit_id').val(id);
        $('#modal_source').val(source);
        $('#modal_description').val(description);
    } else {
        $('#sourceModalTitle').html('<i class="fa fa-plus" style="color:#4f46e5;"></i> <?php echo $this->lang->line("add_source"); ?>');
        $('#sourceForm').attr('action', '<?php echo site_url("admin/source"); ?>');
        $('#source_edit_id').val('');
        $('#modal_source').val('');
        $('#modal_description').val('');
    }
    $('#sourceModal').modal('show');
    setTimeout(function() { $('#modal_source').focus(); }, 500);
}

function deleteSource(id, name) {
    swal({
        title: '<?php echo $this->lang->line("delete_confirm"); ?>',
        text: 'Delete source "' + name + '"?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: '<?php echo $this->lang->line("delete"); ?>',
        cancelButtonText: '<?php echo $this->lang->line("cancel"); ?>'
    }, function(isConfirm) {
        if (isConfirm) {
            window.location.href = '<?php echo base_url(); ?>admin/source/delete/' + id;
        }
    });
}
</script>
