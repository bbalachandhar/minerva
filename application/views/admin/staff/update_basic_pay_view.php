<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-sitemap"></i> <?php echo $this->lang->line('human_resource'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-upload"></i> <?php echo "Upload Staff Basic Pay CSV"; ?></h3>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) {
    echo $this->session->flashdata('msg');
} ?>
                        <form action="<?php echo site_url('admin/staff/update_all_staff_basic_pay') ?>" method="post" enctype="multipart/form-data">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="form-group">
                                <label for="csv_file"><?php echo "Select CSV File"; ?></label>
                                <input type="file" name="csv_file" id="csv_file" class="form-control filestyle" required>
                                <p class="help-block"><?php echo "The CSV file should have 'NAME' and 'BASIC PAY' columns."; ?></p>
                            </div>
                            <button type="submit" name="submit" value="upload" class="btn btn-primary"><?php echo "Upload and Update"; ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
