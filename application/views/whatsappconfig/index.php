<div class="content-wrapper">
      
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="callout callout-info" style="margin-bottom:15px;">
                    <h4><i class="fa fa-info-circle"></i> About This Page</h4>
                    <p>This page configures the <strong>WhatsApp Messaging Gateway</strong> used for sending automated notifications (fee receipts, attendance alerts, exam results, etc.) via <strong>Meta WhatsApp Business API</strong> or <strong>Twilio</strong>.</p>
                    <p style="margin-bottom:0;">
                        <i class="fa fa-arrow-right"></i> Looking for the <strong>WhatsApp contact/chat widget links</strong> shown on the front site, admin panel and student panel?
                        Go to <a href="<?php echo site_url('schsettings/whatsappsettings'); ?>"><strong>System Settings &rarr; WhatsApp Settings</strong></a> instead.
                    </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="nav-tabs-custom theme-shadow">
                    <div class="box-header with-border">
                       <h3 class="box-title titlefix"><?php echo $this->lang->line('whatsapp_messaging_setting'); ?></h3>
                       <div class="box-tools pull-right">
                           <a href="<?php echo site_url('whatsappconfig/instructions'); ?>" class="btn btn-default btn-sm">
                               <i class="fa fa-book"></i> Instructions
                           </a>
                       </div>
                    </div>
                    <ul class="nav nav-tabs">
					
						<li class="active"><a href="#tab_2" data-toggle="tab"><?php echo $this->lang->line('meta_whatsapp_official'); ?></a></li> 
                        <li ><a href="#tab_1" data-toggle="tab"><?php echo $this->lang->line('twilio'); ?></a></li>
						
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane" id="tab_1">
                            <form role="form" id="twilio" action="<?php echo site_url('whatsappconfig/twilio') ?>" class="form-horizontal" method="post">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="minheight170">
                                            <div class="col-md-7">
                                                <?php
                                                $twilio_result = check_in_array('twilio', $list);
                                                ?>
                                                <div class="form-group">
                                                    <label class="col-sm-5 control-label"><?php echo $this->lang->line('twilio_account_sid'); ?><small class="req"> *</small></label>
                                                    <div class="col-sm-7">
                                                        <input autofocus="" type="text" class="form-control" name="twilio_account_sid" value="<?php echo $twilio_result->username; ?>">
                                                        <span class=" text text-danger twilio_account_sid_error"></span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-5 control-label"><?php echo $this->lang->line('authentication_token'); ?><small class="req"> *</small></label>
                                                    <div class="col-sm-7">
                                                        <input type="password" class="form-control" name="twilio_auth_token"  value="<?php echo $twilio_result->password; ?>">
                                                        <span class=" text text-danger twilio_auth_token_error"></span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-5 control-label"><?php echo $this->lang->line('registered_phone_number'); ?><small class="req"> *</small></label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" name="twilio_sender_phone_number"  value="<?php echo $twilio_result->contact; ?>">
                                                        <span class=" text text-danger twilio_sender_phone_number_error"></span>
                                                    </div>
                                                </div>											
                                                <div class="form-group">
                                                    <label class="col-sm-5 control-label"><?php echo $this->lang->line('status'); ?><small class="req"> *</small></label>
                                                    <div class="col-sm-7">
                                                        <select class="form-control" name="twilio_status">
                                                            <?php
                                                            foreach ($statuslist as $s_key => $s_value) {
                                                                ?>
                                                                <option 
                                                                    value="<?php echo $s_key; ?>"
                                                                    <?php
                                                                    if ($twilio_result->is_active == $s_key) {
                                                                        echo "selected=selected";
                                                                    }
                                                                    ?>
                                                                    ><?php echo $s_value; ?></option>
                                                                    <?php
                                                                }
                                                                ?>
                                                        </select>
                                                        <span class=" text text-danger twilio_status_error"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-5 text text-center disblock">
                                                <a href="https://console.twilio.com/" target="_blank"><img src="<?php echo base_url() ?>backend/images/twilio.png<?php echo img_time(); ?>"><p>https://console.twilio.com/</p></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.box-body -->

                                <div class="box-footer">
                                        <div class="col-md-offset-3">
                                            <?php if ($this->rbac->hasPrivilege('whatsapp_messaging', 'can_view')) {
                                                ?>
                                                <button type="submit" class="btn btn-primary btnleftinfo"><?php echo $this->lang->line('save'); ?></button>&nbsp;&nbsp;<span class="twilio_loader"></span>
                                            <?php } ?>
                                        </div>       
                                </div>
                            </form>
                        </div>
					 				
						<div class="tab-pane active" id="tab_2">
                            <form role="form" id="metawhatsapp" action="<?php echo site_url('whatsappconfig/metawhatsapp') ?>" class="form-horizontal" method="post">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="minheight170">
                                            <div class="col-md-7">
                                                <?php
                                                $meta_result = check_in_array('meta', $list);
                                                ?>
                                                <div class="form-group">
                                                    <label class="col-sm-5 control-label"><?php echo  $this->lang->line('access_token'); ?><small class="req"> *</small></label>
                                                    <div class="col-sm-7">
                                                        <input autofocus="" type="text" class="form-control" name="meta_access_token" value="<?php echo $meta_result->authkey; ?>">
                                                        <span class=" text text-danger access_token_error"></span>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label class="col-sm-5 control-label">Phone Number ID <small class="req"> *</small></label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" name="meta_sender_phone_number"  value="<?php echo $meta_result->contact; ?>">
                                                        <small class="text-muted">Numeric ID from Meta Business Suite (not the actual phone number)</small>
                                                        <span class=" text text-danger meta_sender_phone_number_error"></span>
                                                    </div>
                                                </div>	

                                                <div class="form-group">
                                                    <label class="col-sm-5 control-label">WhatsApp Business Account ID (WABA ID)</label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" name="meta_waba_id" value="<?php echo isset($meta_result->waba_id) ? $meta_result->waba_id : ''; ?>">
                                                        <small class="text-muted">Found in Meta Business Suite → WhatsApp → Account ID</small>
                                                    </div>
                                                </div>

												<div class="form-group">
                                                    <label class="col-sm-5 control-label"><?php echo $this->lang->line('language'); ?><small class="req"> *</small></label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" name="meta_language"  value="<?php if(!empty($meta_result->language)){ echo $meta_result->language; } ?>">
                                                        <span class=" text text-danger meta_language_error"></span>
                                                    </div>
                                                </div>
												
                                                <div class="form-group">
                                                    <label class="col-sm-5 control-label"><?php echo $this->lang->line('status'); ?><small class="req"> *</small></label>
                                                    <div class="col-sm-7">
                                                        <select class="form-control" name="meta_status">
                                                            <?php
                                                            foreach ($statuslist as $s_key => $s_value) {
                                                                ?>
                                                                <option 
                                                                    value="<?php echo $s_key; ?>"
                                                                    <?php
                                                                    if ($meta_result->is_active == $s_key) {
                                                                        echo "selected=selected";
                                                                    }
                                                                    ?>
                                                                    ><?php echo $s_value; ?></option>
                                                                    <?php
                                                                }
                                                                ?>
                                                        </select>
                                                        <span class=" text text-danger meta_status_error"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-5 text text-center disblock">
                                                <a href="https://business.facebook.com/" target="_blank"><img src="<?php echo base_url() ?>backend/images/meta.jpg<?php echo img_time(); ?>"><p>https://business.facebook.com/</p></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.box-body -->

                                <div class="box-footer">
                                        <div class="col-md-offset-3">
                                            <?php if ($this->rbac->hasPrivilege('whatsapp_messaging', 'can_view')) {
                                                ?>
                                                <button type="submit" class="btn btn-primary btnleftinfo"><?php echo $this->lang->line('save'); ?></button>&nbsp;&nbsp;<span class="meta_loader"></span>
                                            <?php } ?>
                                        </div>       
                                </div>
                            </form>
                        </div>                       
						
                    </div>
                    <!-- /.tab-content -->
                </div>
            </div>
        </div>

        <!-- ── One-Time Phone Number Activation ─────────────────────────── -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-warning" id="box-activate-number">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-phone-square"></i> One-Time Phone Number Activation</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted" style="margin-bottom:12px;">
                            After adding a phone number to your WABA, Meta requires a one-time registration call before it can send messages.
                            Run this <strong>once per phone number</strong> — the Phone Number ID is pre-filled from your saved config above.
                        </p>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Phone Number ID <small class="req">*</small></label>
                                    <input type="text" id="activate_phone_number_id" class="form-control"
                                        placeholder="e.g. 123456789012345"
                                        value="<?php echo isset($meta_result->contact) ? $meta_result->contact : ''; ?>">
                                    <small class="text-muted">Pre-filled from saved config. Edit if activating a different number.</small>
                                </div>
                                <div class="form-group">
                                    <label>2FA PIN (6 digits) <small class="req">*</small></label>
                                    <input type="text" id="activate_pin" class="form-control" maxlength="6"
                                        placeholder="e.g. 123456">
                                    <small class="text-muted">Set any 6-digit PIN. Note it down — you'll need it if you ever re-register this number.</small>
                                </div>
                                <button type="button" id="btn-activate-number" class="btn btn-warning">
                                    <i class="fa fa-bolt"></i> Activate Number
                                </button>
                                &nbsp;<span id="activate_loader"></span>
                            </div>
                            <div class="col-md-7">
                                <div id="activate_result" style="display:none; margin-top:5px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
</div>



</div>
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
(function ($) {
    "use strict";

    var img_path = "<?php echo base_url('backend/images/loading.gif'); ?>";

    /* ------------------------- Twilio Form Submit -------------------------- */
    $(document).on('submit', '#twilio', function (e) {
        e.preventDefault();

        $("[class$='_error']").html('');
        $(".twilio_loader").html('<img src="' + img_path + '">');

        $.ajax({
            type: "POST",
            dataType: "JSON",
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function (data) {
                if (data.st === 1) {
                    $.each(data.msg, function (key, value) {
                        $('.' + key + '_error').html(value);
                    });
                } else {
                    successMsg(data.msg);
                }
                $(".twilio_loader").html('');
            },
            error: function () {
                $(".twilio_loader").html('');
            }
        });
    });

    /* -------------------------  Activate Phone Number  -------------------------- */
    $(document).on('click', '#btn-activate-number', function () {
        var phone_id = $.trim($('#activate_phone_number_id').val());
        var pin      = $.trim($('#activate_pin').val());

        if (!phone_id) { alert('Please enter a Phone Number ID.'); return; }
        if (!/^\d{6}$/.test(pin)) { alert('PIN must be exactly 6 digits.'); return; }

        $('#activate_result').hide();
        $('#activate_loader').html('<img src="' + img_path + '">');
        $(this).prop('disabled', true);

        $.ajax({
            type: "POST",
            dataType: "JSON",
            url: "<?php echo site_url('whatsappconfig/activate_phone_number'); ?>",
            data: { phone_number_id: phone_id, pin: pin },
            success: function (data) {
                $('#activate_loader').html('');
                $('#btn-activate-number').prop('disabled', false);
                if (data.st === 0) {
                    $('#activate_result')
                        .removeClass('alert-danger').addClass('alert alert-success')
                        .html('<i class="fa fa-check-circle"></i> <strong>Activated successfully!</strong> The number is now ready to send messages.')
                        .show();
                } else {
                    $('#activate_result')
                        .removeClass('alert-success').addClass('alert alert-danger')
                        .html('<i class="fa fa-times-circle"></i> <strong>Error:</strong> ' + data.msg)
                        .show();
                }
            },
            error: function () {
                $('#activate_loader').html('');
                $('#btn-activate-number').prop('disabled', false);
                $('#activate_result')
                    .removeClass('alert-success').addClass('alert alert-danger')
                    .html('<i class="fa fa-times-circle"></i> Request failed. Check your network.')
                    .show();
            }
        });
    });

    /* -------------------------  Meta WhatsApp Form Submit  -------------------------- */
    $(document).on('submit', '#metawhatsapp', function (e) {
        e.preventDefault();

        $("[class$='_error']").html('');
        $(".meta_loader").html('<img src="' + img_path + '">');

        $.ajax({
            type: "POST",
            dataType: "JSON",
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function (data) {
                if (data.st === 1) {
                    $.each(data.msg, function (key, value) {
                        $('.' + key + '_error').html(value);
                    });
                } else {
                    successMsg(data.msg);
                }
                $(".meta_loader").html('');
            },
            error: function () {
                $(".meta_loader").html('');
            }
        });
    });

})(jQuery);
</script>
