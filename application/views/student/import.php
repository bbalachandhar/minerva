<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info" style="padding:5px;">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                        <div class="pull-right box-tools">
                            <a href="<?php echo site_url('student/exportformat') ?>">
                                <button class="btn btn-primary btn-sm"><i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_import_file'); ?></button>
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) {
    ?> <div>  <?php echo $this->session->flashdata('msg');
    $this->session->unset_userdata('msg'); ?> </div> <?php }?>
                        <br/>
                        1. <?php echo $this->lang->line('import_student_step1'); ?>
                      <br/>

                        2. <?php echo $this->lang->line('import_student_step2'); ?> <br/>
                        3. <?php echo $this->lang->line('import_student_step3'); ?>
                        <br/>
                        4. <?php echo $this->lang->line('import_student_step4'); ?>
                        <br/>

                        5. <?php echo $this->lang->line('import_student_step5'); ?><br/>

                        6. <?php echo $this->lang->line('import_student_step6'); ?><br/>

                        7. <?php echo $this->lang->line('import_student_step7'); ?><br/>

                        8. <?php echo $this->lang->line('import_student_step8'); ?><br/>

                        9. <?php echo $this->lang->line('import_student_step9'); ?>
                        <hr/></div>
                    <div class="box-body table-responsive">
                        <table class="table table-striped table-bordered table-hover" id="sampledata">
                            <thead>
                                <tr>
                                    <?php
foreach ($fields as $key => $value) {
    echo $value;

    if ($value == 'adhar_no') {
        $value = "national_identification_no";
    }

    if ($value == 'samagra_id') {
        $value = "local_identification_no";
    }

    if ($value == 'firstname') {
        $value = "first_name";
    }

    if ($value == 'middlename') {
        $value = "middle_name";
    }

    if ($value == 'lastname') {
        $value = "last_name";
    }

    if ($value == 'guardian_is') {
        $value = "if_guardian_is";
    }

    if ($value == 'dob') {
        $value = "date_of_birth";
    }

    if ($value == 'category_id') {
        $value = "category";
    }

    if ($value == 'school_house_id') {
        $value = "house";
    }

    if ($value == 'mobileno') {
        $value = "mobile_no";
    }

    if ($value == 'previous_school') {
        $value = "previous_school_details";
    }
    $add = "";

    if (($value == "admission_no") || ($value == "firstname") || ($value == "date_of_birth") || ($value == "if_guardian_is") || ($value == "gender") || ($value == "guardian_name") || ($value == "guardian_phone")) {
        $add = "<span class=text-red>*</span>";
    }
    ?>
                                        <th><?php echo $add . "<span>" . $this->lang->line($value) . "</span>"; ?></th>
                                    <?php }?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php foreach ($fields as $key => $value) {
    ?>
                                        <td><?php echo $this->lang->line('sample_data'); ?></td>
                                    <?php }?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr/>

                    <form action="<?php echo site_url('student/import') ?>"  id="employeeform" name="employeeform" method="post" enctype="multipart/form-data">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputFile"><?php echo $this->lang->line('select_csv_file'); ?></label><small class="req"> *</small>
                                        <div><input class="filestyle form-control" type='file' name='file' id="file" size='20' />
                                            <span class="text-danger"><?php echo form_error('file'); ?></span></div>
                                    </div></div>
                                <div class="col-md-6 pt20">
                                    <button type="button" id="check_records_btn" class="btn btn-warning pull-right" style="margin-right: 10px;">Check Records</button>
                                    <button type="submit" class="btn btn-info pull-right">Import Student</button>
                                </div>

                            </div>
                        </div>
                    </form>
                    <div id="progress-container" style="display: none;">
                        <div class="progress">
                            <div id="progress-bar" class="progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                <span id="progress-text"></span>
                            </div>
                        </div>
                        <div id="message-box"></div>
                    </div>
                    <div id="check-progress-container" style="display: none;">
                        <div class="progress">
                            <div id="check-progress-bar" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                <span id="check-progress-text"></span>
                            </div>
                        </div>
                        <div id="check-message-box"></div>
                    </div>
                    <div id="validation_results" class="box-body" style="display: none;">
                        <h4>Validation Results:</h4>
                        <div id="valid_records"></div>
                        <div id="invalid_records"></div>
                    </div>
                    <div>
                    </div>
                </div>
                </section>
            </div>
<script>
$(document).ready(function (e) {
    $('#employeeform').on('submit', (function (e) {
        e.preventDefault();
        $('#progress-container').show();
        $('#validation_results').hide(); // Hide validation results when importing
        $('#check-progress-container').hide(); // Hide check progress bar when importing
        var $progressBar = $('#progress-bar');
        var $progressText = $('#progress-text');
        var $messageBox = $('#message-box');
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function (data) {
                //don't do anything, the progress checker will handle it
            },
            error: function () {
                //handle error
            }
        });

        var progressChecker = setInterval(function() {
            $.ajax({
                url: '<?php echo site_url('student/import_progress') ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'processing') {
                        var percentComplete = (response.processed / response.total) * 100;
                        $progressBar.width(percentComplete + '%');
                        $progressText.text(response.processed + ' / ' + response.total);
                    } else if (response.status === 'completed') {
                        clearInterval(progressChecker);
                        $progressBar.width('100%');
                        $progressText.text('Completed');
                        $messageBox.html(response.message);
                        // Optionally, reload the page after a delay
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    }
                }
            });
        }, 1000);
    }));

    // New event listener for check_records_btn
    $('#check_records_btn').on('click', function(e) {
        e.preventDefault();
        $('#progress-container').hide(); // Hide import progress bar when checking records
        $('#validation_results').hide(); // Hide validation results initially
        $('#check-progress-container').show(); // Show check progress bar
        $('#check-progress-bar').width('0%');
        $('#check-progress-text').text('0 / 0');
        $('#check-message-box').html('');

        var $checkProgressBar = $('#check-progress-bar');
        var $checkProgressText = $('#check-progress-text');
        var $checkMessageBox = $('#check-message-box');

        var formData = new FormData($('#employeeform')[0]);
        var fileInput = $('#file')[0];
        if (fileInput.files.length > 0) {
            formData.append('file', fileInput.files[0]);
        }

        $.ajax({
            url: '<?php echo site_url('student/check_import_data') ?>',
            type: "POST",
            data: formData,
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $checkMessageBox.html('<p>Checking records...</p>');
            },
            success: function (response) {
                clearInterval(checkProgressChecker);
                $('#check-progress-container').hide(); // Hide progress bar after completion
                $('#validation_results').show(); // Show validation results

                if (response.status === 'success') {
                    var validHtml = '<h4>Valid Records: ' + response.valid_count + '</h4>';
                    if (response.valid_records.length > 0) {
                        validHtml += '<ul class="list-group">';
                        $.each(response.valid_records, function(i, record) {
                            validHtml += '<li class="list-group-item list-group-item-success">Row ' + record.row_number + ': ' + record.message + '</li>';
                        });
                        validHtml += '</ul>';
                    } else {
                        validHtml += '<p>No valid records found.</p>';
                    }
                    $('#valid_records').html(validHtml);

                    var invalidHtml = '<h4>Invalid Records: ' + response.invalid_count + '</h4>';
                    if (response.invalid_records.length > 0) {
                        invalidHtml += '<ul class="list-group">';
                        $.each(response.invalid_records, function(i, record) {
                            invalidHtml += '<li class="list-group-item list-group-item-danger">Row ' + record.row_number + ': ' + record.message + '</li>';
                        });
                        invalidHtml += '</ul>';
                    } else {
                        invalidHtml += '<p>No invalid records found.</p>';
                    }
                    $('#invalid_records').html(invalidHtml);

                } else {
                    $('#validation_results').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function (xhr, status, error) {
                clearInterval(checkProgressChecker);
                $('#check-progress-container').hide();
                $('#validation_results').show();
                $('#validation_results').html('<div class="alert alert-danger">An error occurred during validation: ' + xhr.responseText + '</div>');
            }
        });

        var checkProgressChecker = setInterval(function() {
            $.ajax({
                url: '<?php echo site_url('student/check_progress') ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'processing') {
                        var percentComplete = (response.processed / response.total) * 100;
                        $checkProgressBar.width(percentComplete + '%');
                        $checkProgressText.text(response.processed + ' / ' + response.total);
                    } else if (response.status === 'completed') {
                        clearInterval(checkProgressChecker);
                        // The success callback of the main AJAX call will handle final display
                    }
                }
            });
        }, 1000);
    });
});
</script>
