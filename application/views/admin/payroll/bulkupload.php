<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-sitemap"></i> <?php echo $this->lang->line('human_resource'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-upload"></i> <?php echo $this->lang->line('bulk_upload'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo base_url() ?>admin/payroll" class="btn btn-primary btn-sm"><i class="fa fa-arrow-left"></i> <?php echo $this->lang->line('back'); ?></a>
                            <a href="<?php echo base_url() ?>backend/import/sample_bulk_payroll.csv" class="btn btn-primary btn-sm" download><i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_import_file'); ?></a>
                        </div>
                    </div>
                    <form id='form1' action="<?php echo site_url('admin/payroll/bulkimport') ?>"  method="post" accept-charset="utf-8" enctype="multipart/form-data">
                        <div class="box-body">
                            <div class="row">
                                <?php if ($this->session->flashdata('msg')) { ?>
                                    <?php echo $this->session->flashdata('msg') ?>
                                <?php } ?>
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('month') ?></label><small class="req"> *</small>
                                        <select autofocus="" id="month" name="month" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($this->customlib->getMonthDropdown() as $m_key => $month_value) {
                                                ?>
                                                <option value="<?php echo $m_key ?>"><?php echo $month_value; ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('month'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('year'); ?></label><small class="req"> *</small>
                                        <select autofocus="" id="year" name="year" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <option value="<?php echo date("Y", strtotime("-1 year")) ?>"><?php echo date("Y", strtotime("-1 year")) ?></option>
                                            <option value="<?php echo date("Y") ?>"><?php echo date("Y") ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('year'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputFile"><?php echo $this->lang->line('select_csv_file'); ?></label><small class="req"> *</small>
                                        <div><input class="filestyle form-control" type='file' name='file' id="file" size='20' />
                                            <span class="text-danger"><?php echo form_error('file'); ?></span></div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" name="search" value="search" class="btn btn-primary btn-sm pull-right checkbox-toggle"><i class="fa fa-upload"></i> <?php echo $this->lang->line('upload'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="box-body pb0">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info"><?php echo $this->lang->line('bulk_upload_payroll_instruction'); ?></div>
                                <ol>
                                    <li><?php echo $this->lang->line('bulk_upload_payroll_instruction1'); ?></li>
                                    <li><?php echo $this->lang->line('bulk_upload_payroll_instruction2'); ?></li>
                                </ol>
                                <span class="text text-danger"><?php echo form_error('file'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
