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
                            <a href="<?php echo site_url('classes/exportformat') ?>">
                                <button class="btn btn-primary btn-sm"><i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_import_file'); ?></button>
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) {?> <div>  <?php echo $this->session->flashdata('msg');
    $this->session->unset_userdata('msg'); ?> </div> <?php }?>
                        <br/>
                        1. Your CSV data should be in the format below. The first line of your CSV file should be the column headers as in the table example. Also make sure that your file is UTF-8 to avoid unnecessary encoding problems.
                        <br/>
                        2. If you are using a college, the department field is required.
                        <hr/></div>
                    <div class="box-body table-responsive">
                        <table class="table table-striped table-bordered table-hover" id="sampledata">
                            <thead>
                                <tr>
                                    <?php foreach ($fields as $key => $value) {
    if ($value == 'class') {
        $value = "class_name";
    }
    if ($value == 'section') {
        $value = "section_name";
    }
    if ($value == 'department_name') {
        $value = "department_name";
    }
    $add = "";
    if (($value == "class_name")) {
        $add = "<span class=text-red>*</span>";
    }
    ?>
                                        <th><?php echo $add . "<span>" . $this->lang->line($value) . "</span>"; ?></th>
                                    <?php }?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php foreach ($fields as $key => $value) {?>
                                        <td><?php echo $this->lang->line('sample_data'); ?></td>
                                    <?php }?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr/>

                    <form action="<?php echo site_url('classes/import') ?>"  id="employeeform" name="employeeform" method="post" enctype="multipart/form-data">
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
                                    <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('import_class'); ?></button>
                                </div>

                            </div>
                        </div>
                    </form>
                    <div>
                    </div>
                </div>
                </section>
            </div>
