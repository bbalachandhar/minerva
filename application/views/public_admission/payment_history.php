<section class="content-header">
    <h1><i class="fa fa-file-text-o"></i> Fee Payment Receipt</h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo base_url('public_admission/applicant_dashboard'); ?>"><i class="fa fa-home"></i> Dashboard</a></li>
        <li class="active">Fee Payment Receipt</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-file-text-o"></i> Online Admission Fee Receipt</h3>
                    <div class="box-tools pull-right">
                        <button onclick="window.print()" class="btn btn-default btn-sm no-print">
                            <i class="fa fa-print"></i> Print
                        </button>
                    </div>
                </div>
                <div class="box-body" id="printable-receipt">
                    <!-- Print header image -->
                    <?php if (!empty($header_image)): ?>
                        <div style="text-align:center; margin-bottom:10px;">
                            <img src="<?php echo $header_image; ?>" style="max-width:100%; height:auto;">
                        </div>
                    <?php else: ?>
                        <div style="text-align:center; margin-bottom:10px; padding:10px; border-bottom:2px solid #367fa9;">
                            <h4 style="margin:0 0 4px;"><?php echo htmlspecialchars($sch_name); ?></h4>
                            <?php if (!empty($sch_phone) || !empty($sch_email)): ?>
                                <small style="color:#666;">
                                    <?php if (!empty($sch_phone)) echo 'Ph: ' . htmlspecialchars($sch_phone); ?>
                                    <?php if (!empty($sch_phone) && !empty($sch_email)) echo '  |  '; ?>
                                    <?php if (!empty($sch_email)) echo htmlspecialchars($sch_email); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <h4 style="text-align:center; text-transform:uppercase; font-weight:bold; margin:10px 0 15px;">
                        Online Admission Fee Receipt
                    </h4>

                    <!-- Applicant Details -->
                    <table class="table table-condensed" style="width:100%; margin-bottom:15px;">
                        <tr>
                            <td width="15%"><strong>Applicant Name</strong></td>
                            <td width="35%">: <?php echo htmlspecialchars(trim($applicant_info->firstname . ' ' . $applicant_info->lastname)); ?></td>
                            <td width="15%"><strong>Reference No.</strong></td>
                            <td width="35%">: <?php echo htmlspecialchars($applicant_info->reference_no); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Course</strong></td>
                            <td>: <?php echo htmlspecialchars($applicant_info->course_name ?? 'N/A'); ?></td>
                            <td><strong>Quota</strong></td>
                            <td>: <?php echo htmlspecialchars($applicant_info->quota_type ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Mobile</strong></td>
                            <td>: <?php echo htmlspecialchars($applicant_info->mobileno ?? 'N/A'); ?></td>
                            <td><strong>Email</strong></td>
                            <td>: <?php echo htmlspecialchars($applicant_info->email ?? 'N/A'); ?></td>
                        </tr>
                    </table>

                    <h5 style="border-bottom:1px solid #ddd; padding-bottom:5px; margin-bottom:10px;"><strong>Payment History</strong></h5>

                    <?php if (!empty($payment_history)): ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr style="background:#367fa9; color:#fff;">
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Receipt No.</th>
                                    <th>Fee Type</th>
                                    <th>Payment Mode</th>
                                    <th style="text-align:right;">Amount (&#8377;)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = 1; foreach ($payment_history as $payment): ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><?php echo htmlspecialchars($payment['date']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['receipt_no'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($payment['fee_type'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars(strtolower($payment['payment_mode'] ?? '')); ?></td>
                                        <td style="text-align:right;">&#8377; <?php echo number_format($payment['amount_raw'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="callout callout-info">
                            <p><i class="fa fa-info-circle"></i> No payment records found.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Totals -->
                    <table class="table" style="width:340px; margin-left:auto; margin-top:5px;">
                        <?php if ($total_fee > 0): ?>
                        <tr>
                            <td><strong>Total Course Fee:</strong></td>
                            <td style="text-align:right;"><strong>&#8377; <?php echo number_format($total_fee, 2); ?></strong></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td><strong>Total Paid:</strong></td>
                            <td style="text-align:right;"><strong class="text-green">&#8377; <?php echo number_format($total_paid, 2); ?></strong></td>
                        </tr>
                        <?php if ($total_fee > 0): ?>
                        <tr>
                            <td><strong>Balance Due:</strong></td>
                            <td style="text-align:right;"><strong class="<?php echo $balance > 0 ? 'text-red' : 'text-green'; ?>">&#8377; <?php echo number_format($balance, 2); ?></strong></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div>
    </div>
</section>

<style>
@media print {
    .no-print, .main-header, .main-sidebar, .main-footer, .content-header { display: none !important; }
    .content-wrapper { margin-left: 0 !important; }
    .box { box-shadow: none !important; border: none !important; }
    body { background: #fff !important; }
}
</style>
