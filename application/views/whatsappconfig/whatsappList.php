<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-gears"></i> <?php // title not needed ?>
            <small class="pull-right">
                <a href="<?php echo site_url('smsconfig'); ?>" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> <?php echo $this->lang->line('sms_setting'); ?>
                </a>
            </small>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="nav-tabs-custom theme-shadow">
                    <div class="box-header with-border">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('whatsapp_gateway'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('smsconfig'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> <?php echo $this->lang->line('sms_setting'); ?>
                            </a>
                        </div>
                    </div>
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_askeva" data-toggle="tab"><?php echo $this->lang->line('ask_eva_whatsapp'); ?></a></li>
                        <!-- future vendors like twilio can be added here as new <li> -->
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_askeva">
                            <form role="form" id="askeva" action="<?php echo site_url('whatsappconfig/askeva') ?>" class="form-horizontal" method="post">
                                <div class="box-body">
                                    <div class="row"><div class="minheight170"><div class="col-md-7">
                                        <?php $askeva_result = check_in_array('whatsapp_askeva', $whatsapplist); ?>
                                        <div class="form-group">
                                            <label class="col-sm-5 control-label"><?php echo $this->lang->line('whatsapp_token'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" name="askeva_token" value="<?php echo $askeva_result->api_id; ?>">
                                                <span class="text text-danger askeva_token_error"></span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-5 control-label"><?php echo $this->lang->line('whatsapp_sender'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" name="askeva_sender" value="<?php echo $askeva_result->contact; ?>">
                                                <span class="text text-danger askeva_sender_error"></span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-5 control-label"><?php echo $this->lang->line('status'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-7">
                                                <select class="form-control" name="askeva_status">
                                                    <?php foreach ($statuslist as $s_key => $s_value) { ?>
                                                        <option value="<?php echo $s_key; ?>" <?php if ($askeva_result->is_active == $s_key) echo "selected=selected"; ?>><?php echo $s_value; ?></option>
                                                    <?php } ?>
                                                </select>
                                                <span class=" text text-danger askeva_status_error"></span>
                                            </div>
                                        </div>
                                    </div></div></div>
                                </div>
                                <div class="box-footer">
                                    <div class="col-md-offset-3">
                                        <?php if ($this->rbac->hasPrivilege('sms_setting', 'can_edit')) { ?>
                                            <button type="submit" class="btn btn-primary btnleftinfo"><?php echo $this->lang->line('save'); ?></button>&nbsp;&nbsp;<span class="askeva_loader"></span>
                                        <?php } ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- /.tab-pane -->
                    </div>
                    <!-- /.tab-content -->
                </div>
            </div>
        </div>
    </section>
</div>

<?php

function check_in_array($find, $array) {
    foreach ($array as $element) {
        if ($find == $element->type) {
            return $element;
        }
    }
    $object = new stdClass();
    $object->id = "";
    $object->type = "";
    $object->api_id = "";
    $object->username = "";
    $object->url = "";
    $object->name = "";
    $object->contact = "";
    $object->password = "";
    $object->authkey = "";
    $object->senderid = "";
    $object->is_active = "";
    return $object;
}
?>

<script type="text/javascript">
    var img_path = "<?php echo base_url() . '/backend/images/loading.gif' ?>";
    $(document).ready(function () {
        $("#askeva").submit(function (e) {
            $("[class$='_error']").html("");
            $(".askeva_loader").html('<img src="' + img_path + '">');
            var url = $(this).attr('action');
            $.ajax({
                type: "POST",
                dataType: 'JSON',
                url: url,
                data: $("#askeva").serialize(),
                success: function (data, textStatus, jqXHR)
                {
                    if (data.st === 1) {
                        $.each(data.msg, function (key, value) {
                            $('.' + key + "_error").html(value);
                        });
                    } else {
                        successMsg(data.msg);
                    }
                    $(".askeva_loader").html("");
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                    $(".askeva_loader").html("");
                }
            });
            e.preventDefault();
        });
    });
</script>
