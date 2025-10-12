<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-money"></i> <?php echo $this->lang->line('fees_collection'); ?>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('bulk_import_fees_master'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo base_url(); ?>admin/feemaster/exportformat" class="btn btn-default btn-sm"><i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_import_file'); ?></a>
                        </div>
                    </div>
                    <div class="box-body">
                        <p>1. Your CSV data should be in the format below. The first line of your CSV file should be the column headers as in the table example. Also make sure that your file is UTF-8 to avoid unnecessary encoding problems.</p>
                        <p>2. The 'fee_group_name', 'fee_type_name', 'amount', and 'due_date' fields are mandatory. 'fine_type' can be 'none', 'percentage', or 'fix'. If 'fine_type' is 'percentage', then 'percentage' field is mandatory. If 'fine_type' is 'fix', then 'fix_amount' field is mandatory.</p>
                        <hr/>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-striped table-bordered table-hover" id="sampledata">
                            <thead>
                                <tr>
                                    <th><?php echo $this->lang->line('fee_group_name'); ?>*</th>
                                    <th><?php echo $this->lang->line('fee_type_name'); ?>*</th>
                                    <th><?php echo $this->lang->line('amount'); ?>*</th>
                                    <th><?php echo $this->lang->line('due_date'); ?>*</th>
                                    <th><?php echo $this->lang->line('fine_type'); ?></th>
                                    <th><?php echo $this->lang->line('percentage'); ?></th>
                                    <th><?php echo $this->lang->line('fix_amount'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo $this->lang->line('sample_data'); ?></td>
                                    <td><?php echo $this->lang->line('sample_data'); ?></td>
                                    <td><?php echo $this->lang->line('sample_data'); ?></td>
                                    <td>YYYY-MM-DD</td>
                                    <td>none</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr/>
                    <form action="<?php echo base_url(); ?>admin/feemaster/bulk_import" method="post" enctype="multipart/form-data">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) {
                                echo $this->session->flashdata('msg');
                                $this->session->unset_userdata('msg');
                            } ?>
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="exampleInputFile"><?php echo $this->lang->line('select_csv_file'); ?></label><small class="req"> *</small>
                                        <div style="display: flex; align-items: center;">
                                            <input class="filestyle form-control" type='file' name='file' id="file" size='20' style="flex-grow: 1;" />
                                            <button type="submit" class="btn btn-info" style="margin-left: 20px;"><?php echo $this->lang->line('import'); ?></button>
                                        </div>
                                        <span class="text-danger"><?php echo form_error('file'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>