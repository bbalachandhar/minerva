<div class="content-wrapper" style="min-height: 348px;">
    <section class="content">
        <div class="row">
            <div class="col-md-12" style="margin-bottom:16px;">
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        <a href="<?php echo site_url('admin/visitorspurpose') ?>" class="btn btn-primary btn-sm" style="border-radius:20px; font-weight:600; font-size:12px;"><i class="fa fa-bullseye"></i> <?php echo $this->lang->line('purpose'); ?></a>
                        <a href="<?php echo site_url('admin/complainttype') ?>" class="btn btn-default btn-sm" style="border-radius:20px; font-weight:600; font-size:12px;"><i class="fa fa-exclamation-circle"></i> <?php echo $this->lang->line('complaint_type'); ?></a>
                        <a href="<?php echo site_url('admin/source') ?>" class="btn btn-default btn-sm" style="border-radius:20px; font-weight:600; font-size:12px;"><i class="fa fa-share-alt"></i> <?php echo $this->lang->line('source'); ?></a>
                        <a href="<?php echo site_url('admin/reference') ?>" class="btn btn-default btn-sm" style="border-radius:20px; font-weight:600; font-size:12px;"><i class="fa fa-link"></i> <?php echo $this->lang->line('reference'); ?></a>
                        </div>
                    <?php if ($this->rbac->hasPrivilege('setup_font_office', 'can_add')) { ?>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openItemModal()" style="border-radius:6px; font-weight:600;"><i class="fa fa-plus"></i> <?php echo $this->lang->line('add_purpose'); ?></button>
                    <?php } ?>
                </div>
            </div>
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header ptbnull"><h3 class="box-title titlefix"><?php echo $this->lang->line('purpose_list'); ?></h3></div>
                    <div class="box-body">
                        <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?>
                        <div class="download_label"><?php echo $this->lang->line('purpose_list'); ?></div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered example">
                                <thead><tr><th style="width:40px;">#</th><th><?php echo $this->lang->line('purpose'); ?></th><th><?php echo $this->lang->line('description'); ?></th><th class="text-right noExport" style="width:100px;"><?php echo $this->lang->line('action'); ?></th></tr></thead>
                                <tbody>
                                    <?php if (!empty($visitors_purpose_list)) { $i=1; foreach ($visitors_purpose_list as $value) { ?>
                                    <tr>
                                        <td style="color:#94a3b8; font-weight:600;"><?php echo $i++; ?></td>
                                        <td><strong><?php echo $value['visitors_purpose']; ?></strong></td>
                                        <td style="color:#64748b;"><?php echo $value['description']; ?></td>
                                        <td class="text-right" style="white-space:nowrap;">
                                            <?php if ($this->rbac->hasPrivilege('setup_font_office', 'can_edit')) { ?>
                                            <a href="javascript:void(0)" onclick="openItemModal('<?php echo $value['id']; ?>', '<?php echo addslashes(htmlspecialchars($value['visitors_purpose'], ENT_QUOTES)); ?>', '<?php echo addslashes(htmlspecialchars($value['description'], ENT_QUOTES)); ?>')" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>"><i class="fa fa-pencil"></i></a>
                                            <?php } if ($this->rbac->hasPrivilege('setup_font_office', 'can_delete')) { ?>
                                            <a href="javascript:void(0)" onclick="deleteItem('<?php echo $value['id']; ?>', '<?php echo addslashes(htmlspecialchars($value['visitors_purpose'], ENT_QUOTES)); ?>')" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>"><i class="fa fa-remove"></i></a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="itemModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content" style="border-radius:12px; overflow:hidden;">
            <div class="modal-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:16px 20px;">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" id="itemModalTitle" style="font-weight:700; font-size:16px;"><i class="fa fa-bullseye" style="color:#4f46e5;"></i> <?php echo $this->lang->line('add_purpose'); ?></h4>
            </div>
            <form id="itemForm" method="post" action="<?php echo site_url('admin/visitorspurpose'); ?>">
                <?php echo $this->customlib->getCSRF(); ?>
                <div class="modal-body" style="padding:20px;">
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; text-transform:uppercase; color:#475569; letter-spacing:0.5px;"><?php echo $this->lang->line('purpose'); ?> <span class="req">*</span></label>
                        <input class="form-control" id="modal_field" name="visitors_purpose" value="" autofocus>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-size:12px; font-weight:600; text-transform:uppercase; color:#475569; letter-spacing:0.5px;"><?php echo $this->lang->line('description'); ?></label>
                        <textarea class="form-control" id="modal_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e2e8f0; padding:12px 20px;">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-check"></i> <?php echo $this->lang->line('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function openItemModal(id, name, desc) {
    if (id) {
        $('#itemModalTitle').html('<i class="fa fa-pencil" style="color:#4f46e5;"></i> <?php echo $this->lang->line("edit_purpose"); ?>');
        $('#itemForm').attr('action', '<?php echo site_url("admin/visitorspurpose/edit/"); ?>' + id);
        $('#modal_field').val(name); $('#modal_description').val(desc);
    } else {
        $('#itemModalTitle').html('<i class="fa fa-plus" style="color:#4f46e5;"></i> <?php echo $this->lang->line("add_purpose"); ?>');
        $('#itemForm').attr('action', '<?php echo site_url("admin/visitorspurpose"); ?>');
        $('#modal_field').val(''); $('#modal_description').val('');
    }
    $('#itemModal').modal('show'); setTimeout(function(){ $('#modal_field').focus(); }, 500);
}
function deleteItem(id, name) {
    swal({ title: '<?php echo $this->lang->line("delete_confirm"); ?>', text: 'Delete "' + name + '"?', type: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: '<?php echo $this->lang->line("delete"); ?>' }, function(c) { if(c) window.location.href = '<?php echo base_url(); ?>admin/visitorspurpose/delete/' + id; });
}
</script>