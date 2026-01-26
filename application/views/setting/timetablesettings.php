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
                                            <label class="col-sm-4">Is Dynamic Timetable</label>
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
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    $(".submit_timetablesetting").on('click', function (e) {
        var $this = $(this);
        $this.button('loading');
        $.ajax({
            url: '<?php echo site_url("schsettings/savetimetablesettings") ?>',
            type: 'POST',
            data: $('#timetablesetting_form').serialize(),
            dataType: 'json',
            success: function (data) {
                if (data.status == "fail") {
                    var message = "";
                    $.each(data.error, function (index, value) {
                        message += value;
                    });
                    errorMsg(message);
                } else {
                    successMsg(data.message);
                }
                $this.button('reset');
            }
        });
    });
</script>