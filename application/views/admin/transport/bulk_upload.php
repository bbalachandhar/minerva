<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-bus"></i> <?php echo $this->lang->line('transport'); ?></h1>
    </section>
    <section class="content"> 
        <div class="row">                 
            <div class="col-md-12">
                <div class="box box-primary" id="route">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('bulk_upload_transport'); ?></h3>   
                    </div>
                    <form action="<?php echo site_url('admin/transport/bulk_upload') ?>" id="form1" method="POST" enctype="multipart/form-data">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php echo $this->session->flashdata('msg');
                                $this->session->unset_userdata('msg'); ?>
                            <?php } ?>
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputFile"><?php echo $this->lang->line('select_csv_file'); ?></label>
                                        <input type="file" name="file" id="file" class="form-control filestyle" />
                                        <span class="text-danger"><?php echo form_error('file'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6 pt20">
                                    <button type="submit" class="btn btn-info pull-right" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('upload'); ?></button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="<?php echo site_url('admin/transport/exportformat') ?>" class="btn btn-default btn-sm pull-right"><i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_import_file'); ?></a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>       
    </section>
</div>