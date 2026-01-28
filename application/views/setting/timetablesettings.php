<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <?php $this->load->view('setting/_settingmenu'); ?>
            <div class="col-md-10">
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><i class="fa fa-gear"></i> Timetable Settings</h3>
                    </div>
                    <div class="">
                        <form role="form" id="timetablesetting_form" action="<?php echo site_url('schsettings/savetimetablesettings') ?>" class="" method="post">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4">Is Dynamic Timetable <small class="text-info">(1 = Dynamic, 0 = Static)</small></label>
                                            <div class="col-sm-8">
                                                <div class="material-switch">
                                                    <input id="is_dynamic_timetable" name="is_dynamic_timetable" type="checkbox" class="chk" value="1" <?php echo (isset($result->is_dynamic_timetable) && $result->is_dynamic_timetable == 1) ? 'checked="checked"' : ''; ?>>
                                                    <label for="is_dynamic_timetable" class="label-success"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="sch_id" value="<?php echo $result->id; ?>">
                            </div>
                            <div class="box-footer">
                                <button type="button" class="btn btn-primary submit_timetablesetting pull-right edit_setting" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('processing'); ?>"> <?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if (isset($result->is_dynamic_timetable) && $result->is_dynamic_timetable == 0) { ?>
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><i class="fa fa-clock-o"></i> <?php echo $this->lang->line('period_manager'); ?></h3>
                    </div>
                    <div class="box-body">
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
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">

    $(document).ready(function () {

        $('.time').datetimepicker({

            format: 'HH:mm:ss'

        });

        $(document).on('click', '.submit_timetablesetting', function (e) {
            e.preventDefault();
            var $this = $(this);
            $this.button('loading');
            var form = $('#timetablesetting_form');
            var url = form.attr('action');
            var formData = form.serialize();

            $.ajax({
                url: url,
                type: "POST",
                data: formData,
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
                        window.location.reload(true);
                    }
                    $this.button('reset');
                },
                error: function () {
                    errorMsg("An error occurred. Please try again.");
                    $this.button('reset');
                }
            });
        });

    });

</script>