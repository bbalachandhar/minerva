<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-upload"></i> Bulk Upload - Meta Leads
            <small>Upload meta campaign enquiry leads using CSV</small>
        </h1>
    </section>

    <style>
        #meta_leads_file {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: 100% !important;
            height: 34px !important;
            padding: 6px 8px !important;
            border: 1px solid #d2d6de !important;
            border-radius: 3px !important;
            background: #fff !important;
            color: #333 !important;
            cursor: pointer !important;
        }
        #meta_leads_file::file-selector-button {
            margin-right: 10px;
            padding: 4px 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
            background: #f5f5f5;
            cursor: pointer;
        }
    </style>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?>

        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Upload CSV</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/enquiry/download_meta_leads_template'); ?>" class="btn btn-info btn-xs">
                                <i class="fa fa-download"></i> Download Sample Template
                            </a>
                        </div>
                    </div>
                    <form method="post" action="<?php echo site_url('admin/enquiry/bulk_meta_leads_upload'); ?>" enctype="multipart/form-data" class="form-horizontal">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">CSV File <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="file" id="meta_leads_file" name="meta_leads_file" class="form-control" accept=".csv" required>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-upload"></i> Upload & Preview
                            </button>
                            <a href="<?php echo site_url('admin/enquiry'); ?>" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> Back to Enquiry
                            </a>
                        </div>
                    </form>
                </div>

                <?php if (!empty($is_preview) && !empty($preview_rows)): ?>
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Preview (First <?php echo count($preview_rows); ?> rows)</h3>
                        <span class="label label-success pull-right">Total rows in CSV: <?php echo (int) $preview_count; ?></span>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <?php foreach ($preview_columns as $column): ?>
                                        <th><?php echo htmlspecialchars($column); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($preview_rows as $row): ?>
                                    <tr>
                                        <?php foreach ($preview_columns as $column): ?>
                                            <td><?php echo htmlspecialchars(isset($row[$column]) ? $row[$column] : ''); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> Instructions</h3>
                    </div>
                    <div class="box-body" style="font-size: 12px; line-height: 1.8;">
                        <p><strong>1.</strong> Click <strong>Download Sample Template</strong>.</p>
                        <p><strong>2.</strong> Fill lead records in CSV.</p>
                        <p><strong>3.</strong> Use date format as <strong>YYYY-MM-DD</strong>.</p>
                        <p><strong>4.</strong> Upload and verify preview.</p>
                        <hr>
                        <p><strong>Recommended columns</strong></p>
                        <ul style="padding-left: 18px; margin-bottom: 0;">
                            <li>name</li>
                            <li>contact</li>
                            <li>email</li>
                            <li>source</li>
                            <li>enquiry_date</li>
                            <li>follow_up_date</li>
                            <li>city</li>
                            <li>state</li>
                            <li>course <em style="color:#999;">(e.g. CSE, EEE, B.Com)</em></li>
                            <li>course_level</li>
                            <li>admission_type</li>
                            <li>description</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
