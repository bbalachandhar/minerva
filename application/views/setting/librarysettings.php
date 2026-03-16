<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <?php $this->load->view('setting/_settingmenu'); ?>
            <div class="col-md-10">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title titlefix"><i class="fa fa-book"></i> Library Settings</h3>
                    </div>
                    <div class="box-body">
                        <form id="librarySettingsForm" method="post" action="<?php echo site_url('schsettings/savelibrarysettings'); ?>">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <input type="hidden" name="sch_id" value="<?php echo (int) ($result->id ?? 0); ?>">

                            <div class="row">
                                <div class="col-md-6">
                                    <h4 style="margin-top:0;">Student Policy</h4>
                                    <div class="form-group">
                                        <label>Max Books Allowed (Student) <small class="req">*</small></label>
                                        <input type="number" min="1" max="50" class="form-control" name="student_max_books_allowed" value="<?php echo (int) ($library_policy['student_max_books_allowed'] ?? 3); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Default Return Days (Student) <small class="req">*</small></label>
                                        <input type="number" min="1" max="365" class="form-control" name="student_book_return_days" value="<?php echo (int) ($library_policy['student_book_return_days'] ?? 15); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h4 style="margin-top:0;">Staff Policy</h4>
                                    <div class="form-group">
                                        <label>Max Books Allowed (Staff) <small class="req">*</small></label>
                                        <input type="number" min="1" max="50" class="form-control" name="staff_max_books_allowed" value="<?php echo (int) ($library_policy['staff_max_books_allowed'] ?? 5); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Default Return Days (Staff) <small class="req">*</small></label>
                                        <input type="number" min="1" max="365" class="form-control" name="staff_book_return_days" value="<?php echo (int) ($library_policy['staff_book_return_days'] ?? 30); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary pull-right" id="librarySettingsSaveBtn">
                                        <i class="fa fa-save"></i> Save
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
(function($) {
    'use strict';

    $('#librarySettingsForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#librarySettingsSaveBtn');
        $btn.button('loading');

        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 1 || response.status === '1' || response.status === 'success') {
                    successMsg(response.message || 'Settings saved successfully');
                } else {
                    errorMsg(response.message || 'Unable to save library settings');
                }
            },
            error: function() {
                errorMsg('Unable to save library settings');
            },
            complete: function() {
                $btn.button('reset');
            }
        });
    });
})(jQuery);
</script>
