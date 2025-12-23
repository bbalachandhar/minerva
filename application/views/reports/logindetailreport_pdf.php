<!DOCTYPE html>
<html>
<head>
    <title><?php echo $this->lang->line('student_login_credential_report'); ?></title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/AdminLTE.min.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('student_login_credential_report'); ?></h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th><?php echo $this->lang->line('admission_no'); ?></th>
                                    <th><?php echo $this->lang->line('student_name'); ?></th>
                                    <th><?php echo $this->lang->line('class'); ?></th>
                                    <th><?php echo $this->lang->line('section'); ?></th>
                                    <th><?php echo $this->lang->line('username'); ?></th>
                                    <th><?php echo $this->lang->line('password'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($report_data)) {
                                    foreach ($report_data as $student) { ?>
                                        <tr>
                                            <td><?php echo $student->admission_no; ?></td>
                                            <td><?php echo $student->student_name; ?></td>
                                            <td><?php echo $student->class; ?></td>
                                            <td><?php echo $student->section; ?></td>
                                            <td><?php echo $student->username; ?></td>
                                            <td><?php echo $student->password; ?></td>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr>
                                        <td colspan="6" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>