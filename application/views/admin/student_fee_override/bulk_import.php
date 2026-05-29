<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-md-8">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-upload"></i> Bulk Import Student Fee Override</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/student_fee_override'); ?>" class="btn btn-sm btn-default">
                                <i class="fa fa-arrow-left"></i> Back to Override List
                            </a>
                            <a href="<?php echo site_url('admin/student_fee_override/exportformat'); ?>" class="btn btn-sm btn-success">
                                <i class="fa fa-download"></i> Download Sample CSV
                            </a>
                        </div>
                    </div>

                    <div class="box-body">

                        <?php if ($this->session->flashdata('msg')): ?>
                            <?php echo $this->session->flashdata('msg'); ?>
                        <?php endif; ?>

                        <div class="callout callout-info">
                            <h4><i class="fa fa-info-circle"></i> Instructions</h4>
                            <ul>
                                <li>Upload a CSV file with columns: <strong>admission_no, fee_type_code, override_amount, note</strong></li>
                                <li>Valid fee type codes: <strong>TUTFEE</strong> (Tuition Fee), <strong>OTHERFEE</strong> (Other Fee)</li>
                                <li>If an override already exists for a student + fee type, it will be <strong>updated</strong>.</li>
                                <li>Override amount must be <strong>&ge; amount already paid</strong> by the student.</li>
                                <li>Download the sample CSV file above as a reference.</li>
                            </ul>
                        </div>

                        <form method="post" action="<?php echo site_url('admin/student_fee_override/bulk_import'); ?>"
                              enctype="multipart/form-data" id="bulkImportForm">
                            <?php echo $this->customlib->getCSRF(); ?>

                            <div class="form-group">
                                <label>Session <span class="text-danger">*</span></label>
                                <select name="session_id" class="form-control select2" style="width:250px" required>
                                    <?php foreach ($sessions as $sess): ?>
                                        <option value="<?php echo $sess->id; ?>"
                                            <?php if ($current_session_id == $sess->id) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($sess->session); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Select the academic session for the import.</small>
                            </div>

                            <div class="form-group">
                                <label>CSV File <span class="text-danger">*</span></label>
                                <input type="file" name="file" accept=".csv" required class="form-control" style="width:350px">
                                <?php echo form_error('file', '<small class="text-danger">', '</small>'); ?>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-upload"></i> Import
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

            </div>

            <div class="col-md-4">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-file-text-o"></i> CSV Format</h3>
                    </div>
                    <div class="box-body">
                        <p>Your CSV file must have these exact column headers in the first row:</p>
                        <table class="table table-condensed table-bordered">
                            <thead>
                                <tr>
                                    <th>Column</th>
                                    <th>Example</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>admission_no</td><td>MCE2022CS001</td></tr>
                                <tr><td>fee_type_code</td><td>TUTFEE</td></tr>
                                <tr><td>override_amount</td><td>45000</td></tr>
                                <tr><td>note</td><td>Management concession</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
