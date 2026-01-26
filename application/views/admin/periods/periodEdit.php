<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-clock-o"></i> <?php echo $this->lang->line('academics'); ?> <small><?php echo $this->lang->line('period_manager'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('edit_period'); ?></h3>
                    </div>
                    <form action="<?php echo site_url('admin/periods/edit/' . $id) ?>"  id="periodform" name="periodform" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <input type="hidden" name="id" value="<?php echo $period->id; ?>">
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('name'); ?></label><small class="req"> *</small>
                                <input autofocus="" id="name" name="name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('name', $period->name); ?>" />
                                <span class="text-danger"><?php echo form_error('name'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('time_from'); ?></label><small class="req"> *</small>
                                <div class="input-group">
                                    <input type="text" name="time_from" class="form-control time" id="time_from" value="<?php echo set_value('time_from', $period->time_from); ?>">
                                    <div class="input-group-addon">
                                        <span class="fa fa-clock-o"></span>
                                    </div>
                                </div>
                                <span class="text-danger"><?php echo form_error('time_from'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('time_to'); ?></label><small class="req"> *</small>
                                <div class="input-group">
                                    <input type="text" name="time_to" class="form-control time" id="time_to" value="<?php echo set_value('time_to', $period->time_to); ?>">
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
