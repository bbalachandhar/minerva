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
                    <a href="<?php echo site_url('admin/visitorspurpose'); ?>" class="btn btn-default btn-sm" style="border-radius:6px; font-weight:600;"><i class="fa fa-arrow-left"></i> Back to List</a>
                </div>
            </div>
            <?php if ($this->rbac->hasPrivilege('setup_font_office', 'can_add') || $this->rbac->hasPrivilege('setup_font_office', 'can_edit')) { ?>
            <div class="col-md-6 col-md-offset-3">
                <div class="box box-primary">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-pencil" style="color:#4f46e5; margin-right:6px;"></i><?php echo $this->lang->line('edit_purpose'); ?></h3></div>
                    <form id="form1" action="<?php echo site_url('admin/visitorspurpose/edit/' . $visitors_purpose_data['id']) ?>" method="post">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <div class="box-body">
                            <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?>
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; text-transform:uppercase; color:#475569; letter-spacing:0.5px;"><?php echo $this->lang->line('purpose'); ?> <span class="req">*</span></label>
                                <input class="form-control" name="visitors_purpose" value="<?php echo set_value('visitors_purpose', $visitors_purpose_data['visitors_purpose']); ?>" autofocus />
                                <span class="text-danger"><?php echo form_error('visitors_purpose'); ?></span>
                            </div>
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; text-transform:uppercase; color:#475569; letter-spacing:0.5px;"><?php echo $this->lang->line('description'); ?></label>
                                <textarea class="form-control" name="description" rows="3"><?php echo set_value('description', $visitors_purpose_data['description']); ?></textarea>
                            </div>
                        </div>
                        <div class="box-footer" style="display:flex; justify-content:space-between;">
                            <a href="<?php echo site_url('admin/visitorspurpose'); ?>" class="btn btn-default"><?php echo $this->lang->line('cancel'); ?></a>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> <?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
            <?php } ?>
        </div>
    </section>
</div>