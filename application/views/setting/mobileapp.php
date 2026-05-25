<div class="content-wrapper" style="min-height: 348px;">    
    <section class="content">
        <div class="row">
        
            <?php $this->load->view('setting/_settingmenu'); ?>
            
            <!-- left column -->
            <div class="col-md-10">
                <!-- general form elements -->

                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><i class="fa fa-gear"></i> <?php echo $this->lang->line('mobile_app'); ?></h3>
                        <div class="box-tools pull-right">
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="">
                        <form role="form" id="mobileapp_form" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="sch_id" value="<?php echo $result->id; ?>">
                            <div class="box-body">                       
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="relative">   

                                            <h4 class="session-head"><?php echo $this->lang->line('user_mobile_app'); ?> </h4>
                                          
                                        </div>
                                    </div><!--./col-md-12-->

                                    <!-- App Logo Upload -->
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><strong>Client App Logo</strong><br><small class="text-muted">This logo appears on the mobile app login screen (290px X 51px)</small></label>
                                            <div class="col-sm-8">
                                                <div class="card-body-logo-img" style="margin-bottom:8px;">
                                                    <?php if (empty($result->app_logo)): ?>
                                                        <img id="app_logo_preview" src="<?php echo $this->media_storage->getImageURL('uploads/school_content/logo/images.png'); ?>" class="img-responsive" alt="No logo" style="max-height:60px;">
                                                    <?php else: ?>
                                                        <img id="app_logo_preview" src="<?php echo $this->media_storage->getImageURL('uploads/school_content/logo/app_logo/' . $result->app_logo); ?>" class="img-responsive" alt="App Logo" style="max-height:60px;">
                                                    <?php endif; ?>
                                                </div>
                                                <a href="#" role="button" class="btn btn-primary btn-sm upload_app_logo_mobileapp" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('processing'); ?>"><?php echo $this->lang->line('update'); ?></a>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /App Logo Upload -->

                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-sm-4"> <?php echo $this->lang->line('user_mobile_app_api_url') ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="mobile_api_url" id="mobile_api_url" class="form-control" value="<?php echo $result->mobile_api_url; ?>">
                                                <span class="text-danger"><?php echo form_error('mobile_api_url'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-sm-4"> <?php echo $this->lang->line('user_mobile_app_primary_color_code') ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="app_primary_color_code" id="app_primary_color_code" class="form-control" value="<?php echo $result->app_primary_color_code; ?>">
                                                <span class="text-danger"><?php echo form_error('app_primary_color_code'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-sm-4"> <?php echo $this->lang->line('user_mobile_app_secondary_color_code'); ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="app_secondary_color_code" id="app_secondary_color_code" class="form-control" value="<?php echo $result->app_secondary_color_code; ?>">
                                                <span class="text-danger"><?php echo form_error('app_secondary_color_code'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('enable_student_profile_edit') ?></label>
                                            <div class="col-sm-8">
                                                <div class="material-switch">
                                                    <input id="student_profile_edit" name="student_profile_edit" type="checkbox" class="chk" value="1" <?php echo ($result->student_profile_edit == 1) ? 'checked="checked"' : ''; ?>>
                                                    <label for="student_profile_edit" class="label-success"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="settinghr"></div>
                                        <div class="relative">   
                                            <h4 class="session-head"><?php echo $this->lang->line('staff_mobile_app_settings'); ?> </h4>
                                        </div>
                                    </div><!--./col-md-12-->
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-sm-4"> <?php echo $this->lang->line('enable_staff_profile_edit') ?></label>
                                            <div class="col-sm-8">
                                                <div class="material-switch">
                                                    <input id="staff_profile_edit" name="staff_profile_edit" type="checkbox" class="chk" value="1" <?php echo ($result->staff_profile_edit == 1) ? 'checked="checked"' : ''; ?>>
                                                    <label for="staff_profile_edit" class="label-success"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-sm-4"> <?php echo $this->lang->line('enable_staff_self_profile_edit') ?></label>
                                            <div class="col-sm-8">
                                                <div class="material-switch">
                                                    <input id="staff_self_edit" name="staff_self_edit" type="checkbox" class="chk" value="1" <?php echo ($result->staff_self_edit == 1) ? 'checked="checked"' : ''; ?>>
                                                    <label for="staff_self_edit" class="label-success"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                <div class="row hidden">
                                    <div class="col-md-12">
                                        <div class="settinghr"></div>
                                        <div class="relative">   
                                            <h4 class="session-head"><?php echo $this->lang->line('admin_mobile_app'); ?> </h4>
                                        </div>
                                    </div><!--./col-md-12-->
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-sm-4"> <?php echo $this->lang->line('admin_mobile_app_api_url') ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="admin_mobile_api_url" id="admin_mobile_api_url" class="form-control" value="<?php echo $result->admin_mobile_api_url ?? ''; ?>">
                                                <span class="text-danger"><?php echo form_error('admin_mobile_api_url'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-sm-4"> <?php echo $this->lang->line('admin_mobile_app_primary_color_code') ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="admin_app_primary_color_code" id="admin_app_primary_color_code" class="form-control" value="<?php echo $result->admin_app_primary_color_code ?? ''; ?>">
                                                <span class="text-danger"><?php echo form_error('admin_app_primary_color_code'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-sm-4"> <?php echo $this->lang->line('admin_mobile_app_secondary_color_code'); ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="admin_app_secondary_color_code" id="admin_app_secondary_color_code" class="form-control" value="<?php echo $result->admin_app_secondary_color_code ?? ''; ?>">
                                                <span class="text-danger"><?php echo form_error('admin_app_secondary_color_code'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                            </div><!-- /.box-body -->
                            <div class="box-footer">
                                <?php
                                if ($this->rbac->hasPrivilege('general_setting', 'can_edit')) {
                                    ?>
                                    <button type="button" class="btn btn-primary submit_schsetting pull-right edit_mobileapp" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('processing'); ?>"> <?php echo $this->lang->line('save'); ?></button>
                                    <?php
                                }
                                ?>
                            </div>
                        </form>
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col (left) -->
            <!-- right column -->
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<!-- new END -->

</div><!-- /.content-wrapper -->

<!-- App Logo Upload Modal -->
<div class="modal fade" id="modal-upload_app_logo_mobileapp" tabindex="-1" role="dialog" aria-labelledby="appLogoModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="appLogoModalLabel">Client App Logo</h4>
            </div>
            <div class="modal-body upload_logo_body">
                <form class="box_upload boxupload has-advanced-upload" method="post" action="<?php echo site_url('schsettings/ajax_applogo') ?>" enctype="multipart/form-data">
                    <input value="<?php echo $result->id ?>" type="hidden" name="id" id="id_app_logo_mobileapp"/>
                    <input type="file" name="file" id="file_applogo_mobileapp">
                    <div class="box__input upload-app_logo_mobileapp_area" id="uploadapp_logo_mobileapp">
                        <i class="fa fa-download box__icon"></i>
                        <label><strong><?php echo $this->lang->line('choose_a_file_or_drag_it_here'); ?></strong></label>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /App Logo Upload Modal -->



<script type="text/javascript">
    var base_url = '<?php echo base_url(); ?>';
 
    // App Logo Upload Modal - open on button click
    $('.upload_app_logo_mobileapp').on('click', function (e) {
        e.preventDefault();
        var $this = $(this);
        $this.button('loading');
        $('#modal-upload_app_logo_mobileapp').modal({
            show: true,
            backdrop: 'static',
            keyboard: false
        });
    });
    $('#modal-upload_app_logo_mobileapp').on('shown.bs.modal', function () {
        $('.upload_app_logo_mobileapp').button('reset');
    });

    $(function () {
        // Drag enter
        $('.upload-app_logo_mobileapp_area').on('dragenter', function (e) {
            e.stopPropagation(); e.preventDefault();
        });
        // Drag over
        $('.upload-app_logo_mobileapp_area').on('dragover', function (e) {
            e.stopPropagation(); e.preventDefault();
        });
        // Drop
        $('.upload-app_logo_mobileapp_area').on('drop', function (e) {
            e.stopPropagation(); e.preventDefault();
            var file = e.originalEvent.dataTransfer.files;
            var fd = new FormData();
            fd.append('file', file[0]);
            fd.append('id', $('#id_app_logo_mobileapp').val());
            uploadAppLogoMobileapp(fd);
        });
        // Open file picker on div click
        $('#uploadapp_logo_mobileapp').click(function () {
            $('#file_applogo_mobileapp').click();
        });
        // File selected
        $('#file_applogo_mobileapp').change(function () {
            var fd = new FormData();
            fd.append('file', $('#file_applogo_mobileapp')[0].files[0]);
            fd.append('id', $('#id_app_logo_mobileapp').val());
            uploadAppLogoMobileapp(fd);
        });
    });

    function uploadAppLogoMobileapp(formdata) {
        $.ajax({
            url: '<?php echo site_url('schsettings/ajax_applogo') ?>',
            type: 'post',
            data: formdata,
            contentType: false,
            processData: false,
            dataType: 'json',
            cache: false,
            beforeSend: function () {
                $('#modal-upload_app_logo_mobileapp').addClass('modal_loading');
            },
            success: function (response) {
                if (response.success) {
                    successMsg(response.message);
                    window.location.reload(true);
                } else {
                    errorMsg(response.error.file);
                }
            },
            error: function () {},
            complete: function () {
                $('#modal-upload_app_logo_mobileapp').removeClass('modal_loading');
            }
        });
    }

    $(".edit_mobileapp").on('click', function (e) {
        var $this = $(this);
        $this.button('loading');
        $.ajax({
            url: '<?php echo site_url("schsettings/savemobileapp") ?>',
            type: 'POST',
            data: $('#mobileapp_form').serialize(),
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