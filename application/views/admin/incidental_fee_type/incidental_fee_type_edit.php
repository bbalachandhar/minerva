<?php $this->load->view('layout/header'); ?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-money"></i> <?php echo $this->lang->line('fees_collection'); ?> <small><?php echo $this->lang->line('incidental_fee_type'); ?></small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('edit_incidental_fee_type'); ?></h3>
                    </div>
                    <form action="<?php echo site_url('admin/incidental_fee_type/edit/' . $incidental_fee_type['id']) ?>" id="incidental_fee_type_form" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
                            <?php echo $this->customlib->get = $this->customlib->getCSRF(); ?>
                            <div class="form-group">
                                <label for="title"><?php echo $this->lang->line('title'); ?></label>
                                <input id="title" name="title" type="text" class="form-control" value="<?php echo set_value('title', $incidental_fee_type['title']); ?>" />
                                <span class="text-danger"><?php echo form_error('title'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="description"><?php echo $this->lang->line('description'); ?></label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo set_value('description', $incidental_fee_type['description']); ?></textarea>
                                <span class="text-danger"><?php echo form_error('description'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="default_amount"><?php echo $this->lang->line('default_amount'); ?></label>
                                <input id="default_amount" name="default_amount" type="text" class="form-control" value="<?php echo set_value('default_amount', $incidental_fee_type['default_amount']); ?>" />
                                <span class="text-danger"><?php echo form_error('default_amount'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="is_assignable"><?php echo $this->lang->line('is_assignable'); ?></label>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="is_assignable" value="1" <?php echo set_checkbox('is_assignable', '1', ($incidental_fee_type['is_assignable'] == 1) ? TRUE : FALSE); ?>> <?php echo $this->lang->line('yes'); ?>
                                    </label>
                                </div>
                                <span class="text-danger"><?php echo form_error('is_assignable'); ?></span>
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
                        <h3 class="box-title"><?php echo $this->lang->line('incidental_fee_type_list'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover gemini-datatable">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('title'); ?></th>
                                        <th><?php echo $this->lang->line('description'); ?></th>
                                        <th><?php echo $this->lang->line('default_amount'); ?></th>
                                        <th><?php echo $this->lang->line('is_assignable'); ?></th>
                                        <th class="text-right no-print"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($incidental_fee_type_list as $incidental_fee_type_item) { ?>
                                        <tr>
                                            <td class="mailbox-name"><?php echo $incidental_fee_type_item['title'] ?></td>
                                            <td class="mailbox-name"><?php echo $incidental_fee_type_item['description'] ?></td>
                                            <td class="mailbox-name"><?php echo $incidental_fee_type_item['default_amount'] ?></td>
                                            <td class="mailbox-name"><?php echo ($incidental_fee_type_item['is_assignable'] == 1) ? $this->lang->line('yes') : $this->lang->line('no'); ?></td>
                                            <td class="mailbox-date pull-right no-print">
                                                <a href="<?php echo base_url(); ?>admin/incidental_fee_type/edit/<?php echo $incidental_fee_type_item['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                                <a href="<?php echo base_url(); ?>admin/incidental_fee_type/delete/<?php echo $incidental_fee_type_item['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                    <i class="fa fa-remove"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php $this->load->view('layout/footer'); ?>