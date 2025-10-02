<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info" style="padding:5px;">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('import_book') ?></h3>
                        <div class="pull-right box-tools">
                            <a href="<?php echo site_url('admin/book/exportformat') ?>">
                                <button class="btn btn-primary btn-sm"><i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_import_file'); ?></button>
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) {?> <div>  <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?> </div> <?php }?>
                        <br/>
                        1. <?php echo $this->lang->line('book_instruction_one'); ?><br/>
                        2. <?php echo $this->lang->line('book_instruction_two'); ?><br/>
                        <hr/></div>
                    <div class="box-body table-responsive" style="overflow-x: auto;">
                        <table class="table table-striped table-bordered table-hover" id="sampledata">
                            <thead>
                                <tr>
                                    <?php
$mandatory_fields_list = array('book_title', 'book_no', 'isbn_no', 'author');
foreach ($fields as $field) {
    $add = in_array($field, $mandatory_fields_list) ? "<span class='text-red d-inline'>* </span>" : "";
    $display_value = $this->lang->line($field);
    ?>
                                        <th><?php echo $add . "<span>" . $display_value . "</span>"; ?></th>
                                    <?php }?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php foreach ($fields as $field) { ?>
                                        <td><?php echo "Sample Data" ?></td>
                                    <?php }?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr/>
                    <form action="<?php echo site_url('admin/book/import') ?>"  id="employeeform" name="employeeform" method="post" enctype="multipart/form-data">
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
                                    <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('import_book'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div>
                    </div>
                </div>
                </section>
            </div>