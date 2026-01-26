<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-clock-o"></i> <?php echo $this->lang->line('academics'); ?> <small><?php echo $this->lang->line('period_manager'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('add_period'); ?></h3>
                    </div>
                    <form id="form1" action="<?php echo site_url('admin/periods/create') ?>"  id="periodform" name="periodform" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('name'); ?></label><small class="req"> *</small>
                                <input autofocus="" id="name" name="name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('name'); ?>" />
                                <span class="text-danger"><?php echo form_error('name'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('time_from'); ?></label><small class="req"> *</small>
                                <div class="input-group">
                                    <input type="text" name="time_from" class="form-control time" id="time_from" value="<?php echo set_value('time_from'); ?>">
                                    <div class="input-group-addon">
                                        <span class="fa fa-clock-o"></span>
                                    </div>
                                </div>
                                <span class="text-danger"><?php echo form_error('time_from'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('time_to'); ?></label><small class="req"> *</small>
                                <div class="input-group">
                                    <input type="text" name="time_to" class="form-control time" id="time_to" value="<?php echo set_value('time_to'); ?>">
                                    <div class="input-group-addon">
                                        <span class="fa fa-clock-o"></span>
                                    </div>
                                </div>
                                <span class="text-danger"><?php echo form_error('time_to'); ?></span>
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
                        <h3 class="box-title"><?php echo $this->lang->line('period_list'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive mailbox-messages">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('name'); ?></th>
                                        <th><?php echo $this->lang->line('time_from'); ?></th>
                                        <th><?php echo $this->lang->line('time_to'); ?></th>
                                        <th class="text-right"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($period_list)) { ?>
                                        <tr>
                                            <td colspan="4" class="text-danger text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                        </tr>
                                    <?php } else {
                                        foreach ($period_list as $period) { ?>
                                            <tr>
                                                <td class="mailbox-name"> <?php echo $period->name ?></td>
                                                <td class="mailbox-name"> <?php echo $period->time_from ?></td>
                                                <td class="mailbox-name"> <?php echo $period->time_to ?></td>
                                                <td class="mailbox-date pull-right">
                                                    <a href="<?php echo site_url('admin/periods/edit/' . $period->id); ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <a href="<?php echo site_url('admin/periods/delete/' . $period->id); ?>"class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                        <i class="fa fa-remove"></i>
                                                    </a>
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
<script type="text/javascript">
    $(document).ready(function () {
        $('.time').datetimepicker({
            format: 'HH:mm:ss'
        });
    });
</script>
