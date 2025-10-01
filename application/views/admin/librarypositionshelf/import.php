<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-book"></i> Library</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Horizontal Form -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Import Position Shelf</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo base_url(); ?>admin/librarypositionshelf/import_sample" class="btn btn-sm btn-primary"><i class="fa fa-download"></i> Download Sample</a>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="alert alert-info">
                            Please make sure that your XLS file is in the following format.
                            The first line of your XLS file should be the column headers as in the table example. Also make sure that your file is UTF-8 to avoid unnecessary encoding problems.
                        </div>
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Position Shelf Name</th>
                                    <th>Position Rack Name</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Position Shelf Name (Required)</td>
                                    <td>Position Rack Name (Required)</td>
                                    <td>Description</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- form start -->
                    <form id="form1" action="<?php echo site_url('admin/librarypositionshelf/import') ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php
                                    echo $this->session->flashdata('msg');
                                    $this->session->unset_userdata('msg');
                                ?>
                            <?php } ?>
                            <?php
                            if (isset($error_message)) {
                                echo "<div class='alert alert-danger'>" . $error_message . "</div>";
                            }
                            ?>
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="form-group">
                                <label for="exampleInputFile">Select Excel File<small class="req"> *</small></label>
                                <input type="file" name="file" id="file" size="20" class="dropify" />
                                <span class="text-danger"><?php echo form_error('file'); ?></span>
                            </div>
                        </div><!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right">Import</button>
                        </div>
                    </form>
                </div>
            </div><!--/.col (right) -->
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<script>
    $(document).ready(function () {
        $('.dropify').dropify();
    });
</script>
