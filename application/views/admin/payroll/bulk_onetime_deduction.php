<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-upload"></i> Bulk Upload One-Time Deductions
            <small>Upload month/year-specific, non-recurring deductions by employee ID</small>
        </h1>
    </section>

    <style>
        #payroll_onetime_csv_file {
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
        #payroll_onetime_csv_file::file-selector-button {
            margin-right: 10px;
            padding: 4px 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
            background: #f5f5f5;
            cursor: pointer;
        }
    </style>

    <section class="content">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Upload File</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/payroll/download_onetime_deduction_template'); ?>" class="btn btn-info btn-xs">
                                <i class="fa fa-download"></i> Download Sample CSV
                            </a>
                            <a href="<?php echo site_url('admin/payroll/pending_onetime_deductions'); ?>" class="btn btn-warning btn-xs">
                                <i class="fa fa-clock-o"></i> Pending Approval
                            </a>
                        </div>
                    </div>
                    <form method="post" action="<?php echo site_url('admin/payroll/save_bulk_onetime_deduction'); ?>" enctype="multipart/form-data" class="form-horizontal">
                        <div class="box-body">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Month <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select name="month" class="form-control" required>
                                        <option value="">Select Month</option>
                                        <?php foreach ($monthlist as $month_name): ?>
                                            <option value="<?php echo $month_name; ?>"><?php echo $month_name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Year <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="number" min="2000" max="2100" name="year" class="form-control" value="<?php echo (int) $year; ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Default Deduction Type</label>
                                <div class="col-sm-9">
                                    <input type="text" name="deduction_type" class="form-control" placeholder="Optional (e.g. ADVANCE). If missing in CSV, this will be used.">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Remarks</label>
                                <div class="col-sm-9">
                                    <input type="text" name="remarks" class="form-control" placeholder="Optional remarks applied to rows without remarks">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">CSV File <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="file" id="payroll_onetime_csv_file" name="file" class="form-control" accept=".csv" required>
                                    <p class="help-block" style="margin-bottom: 0;">
                                        Required columns: <strong>employee_id</strong>, <strong>amount</strong>.<br>
                                        Optional columns: <strong>deduction_type</strong>, <strong>remarks</strong>.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-upload"></i> Upload One-Time Deductions
                            </button>
                            <a href="<?php echo site_url('admin/payroll/add_increment'); ?>" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> Flow</h3>
                    </div>
                    <div class="box-body" style="font-size: 12px; line-height: 1.8;">
                        <p><strong>1.</strong> Upload CSV for selected month/year</p>
                        <p><strong>2.</strong> Entries are saved as <strong>Pending</strong></p>
                        <p><strong>3.</strong> HR approves from Pending Approval screen</p>
                        <p><strong>4.</strong> Only approved entries are applied in payroll calculations</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
