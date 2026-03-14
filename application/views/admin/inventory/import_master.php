<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-upload"></i> <?php echo html_escape($module_label); ?> Bulk Upload
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if ($this->session->flashdata('msg')) { ?>
                    <?php echo $this->session->flashdata('msg'); ?>
                <?php } ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="callout callout-info">
                    <h4 style="margin-top:0;">First-Time Institution Onboarding Order</h4>
                    <ol style="margin-bottom:10px; padding-left:18px;">
                        <?php foreach ($onboarding_steps as $step) { ?>
                            <li><?php echo html_escape($step); ?></li>
                        <?php } ?>
                    </ol>
                    <a href="<?php echo $guide_url; ?>" class="btn btn-info btn-sm">
                        <i class="fa fa-book"></i> Open Full Inventory Guide
                    </a>
                    <a href="<?php echo $back_url; ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Module
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">CSV Format and Upload</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo $download_url; ?>" class="btn btn-primary btn-sm">
                                <i class="fa fa-download"></i> Download Sample CSV
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <p>Use UTF-8 CSV files. Keep the first row as headers exactly like the sample below.</p>
                        <ul style="padding-left:18px;">
                            <?php foreach ($instructions as $instruction) { ?>
                                <li><?php echo html_escape($instruction); ?></li>
                            <?php } ?>
                        </ul>

                        <div class="table-responsive" style="margin-top:15px;">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <?php foreach ($headers as $header) { ?>
                                            <th><?php echo html_escape($header); ?></th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <?php foreach ($sample_row as $value) { ?>
                                            <td><?php echo html_escape($value); ?></td>
                                        <?php } ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <hr>

                        <form action="<?php echo $action_url; ?>" method="post" enctype="multipart/form-data">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Select CSV File</label><small class="req"> *</small>
                                        <input type="file" name="file" id="file" class="filestyle form-control" data-height="40">
                                        <span class="text-danger"><?php echo form_error('file'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4" style="padding-top:25px;">
                                    <button type="submit" class="btn btn-success pull-right">
                                        <i class="fa fa-upload"></i> Upload <?php echo html_escape($module_label); ?> CSV
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