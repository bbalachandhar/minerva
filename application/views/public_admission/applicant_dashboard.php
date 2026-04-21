<section class="content-header">
    <h1><i class="fa fa-dashboard"></i> Dashboard</h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo base_url('public_admission/applicant_dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
        <li class="active">Dashboard</li>
    </ol>
</section>

<section class="content">
    <!-- Info Boxes Row -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <?php
            $ps = (int)($applicant_info->paid_status ?? 0);
            $fs = (int)($applicant_info->form_status ?? 0);
            if ($ps === 2)     { $status_color = 'bg-blue';   $status_label = 'Admitted'; }
            elseif ($ps === 1) { $status_color = 'bg-green';  $status_label = 'Applied'; }
            elseif ($fs === 1) { $status_color = 'bg-aqua';   $status_label = 'Submitted'; }
            else               { $status_color = 'bg-yellow'; $status_label = 'In Progress'; }
            ?>
            <div class="info-box <?php echo $status_color; ?>">
                <span class="info-box-icon"><i class="fa fa-file-text-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Application Status</span>
                    <span class="info-box-number"><?php echo $status_label; ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-inr"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Paid</span>
                    <span class="info-box-number">&#8377; <?php echo number_format($total_paid, 2); ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <?php $balance_color = ($balance > 0) ? 'bg-yellow' : 'bg-green'; ?>
            <div class="info-box <?php echo $balance_color; ?>">
                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Balance Due</span>
                    <span class="info-box-number">&#8377; <?php echo number_format($balance, 2); ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fa fa-id-card-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Reference No</span>
                    <span class="info-box-number" style="font-size:18px;"><?php echo htmlspecialchars($applicant_info->reference_no); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left column: Applicant Details + Payment Summary -->
        <div class="col-md-7">

            <!-- Applicant Details -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-user"></i> Applicant Details</h3>
                </div>
                <div class="box-body no-padding">
                    <div style="display:flex;align-items:flex-start;gap:0;">
                        <!-- Profile photo -->
                        <div style="padding:16px 12px 12px 16px;text-align:center;min-width:120px;">
                            <?php
                            if (!empty($applicant_info->image) && file_exists(FCPATH . $applicant_info->image)) {
                                $photo_src = base_url($applicant_info->image);
                            } elseif (strtolower($applicant_info->gender ?? '') === 'female') {
                                $photo_src = base_url('uploads/staff_images/default_female.jpg');
                            } else {
                                $photo_src = base_url('uploads/staff_images/default_male.jpg');
                            }
                            ?>
                            <img src="<?php echo $photo_src; ?>"
                                 alt="Photo"
                                 style="width:100px;height:110px;object-fit:cover;border-radius:6px;border:2px solid #d2d6de;box-shadow:0 2px 6px rgba(0,0,0,0.15);">
                        </div>
                        <!-- Details table -->
                        <table class="table table-striped" style="margin-bottom:0;flex:1;">
                        <tr>
                            <th width="38%" style="padding-left:12px;">Name</th>
                            <td><?php echo htmlspecialchars(trim($applicant_info->firstname . ' ' . $applicant_info->lastname)); ?></td>
                        </tr>
                        <tr>
                            <th style="padding-left:12px;">Reference No</th>
                            <td><?php echo htmlspecialchars($applicant_info->reference_no); ?></td>
                        </tr>
                        <tr>
                            <th style="padding-left:12px;">Course Applied</th>
                            <td><?php echo htmlspecialchars($applicant_info->course_name ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th style="padding-left:12px;">Quota</th>
                            <td><?php echo htmlspecialchars(ucfirst($applicant_info->quota_type ?? 'N/A')); ?></td>
                        </tr>
                        <tr>
                            <th style="padding-left:12px;">Mobile</th>
                            <td><?php echo htmlspecialchars($applicant_info->mobileno ?? '—'); ?></td>
                        </tr>
                        <tr>
                            <th style="padding-left:12px;">Email</th>
                            <td><?php echo htmlspecialchars($applicant_info->email ?? '—'); ?></td>
                        </tr>
                        <tr>
                            <th style="padding-left:12px;">Applied On</th>
                            <td><?php echo date('d M Y', strtotime($applicant_info->created_at)); ?></td>
                        </tr>
                        <tr>
                            <th style="padding-left:12px;">Application Status</th>
                            <td>
                                <?php
                                if ($ps === 2)     echo '<span class="label label-primary">Admitted</span>';
                                elseif ($ps === 1) echo '<span class="label label-success">Applied</span>';
                                elseif ($fs === 1) echo '<span class="label label-info">Submitted</span>';
                                else               echo '<span class="label label-warning">In Progress</span>';
                                ?>
                            </td>
                        </tr>
                    </table>
                    </div>
                </div>
                <div class="box-footer">
                    <a href="<?php echo base_url('welcome/online_admission_review/' . $applicant_info->reference_no); ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-eye"></i> View Application Form
                    </a>
                </div>
            </div>

            <!-- Fee Summary -->
            <?php if ($total_fee > 0 || !empty($payment_history)): ?>
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-credit-card"></i> Payment History</h3>
                </div>
                <div class="box-body no-padding">
                    <?php if (!empty($payment_history)): ?>
                        <table class="table table-bordered" style="margin-bottom:0;">
                            <thead>
                                <tr style="background:#f9f9f9;">
                                    <th>Date</th>
                                    <th>Receipt / TXN</th>
                                    <th>Fee Type</th>
                                    <th>Mode</th>
                                    <th style="text-align:right;">Amount (&#8377;)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payment_history as $payment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['date']); ?></td>
                                        <td>
                                            <?php
                                            if (!empty($payment['receipt_no'])) {
                                                echo htmlspecialchars($payment['receipt_no']);
                                            } elseif (!empty($payment['txn_id'])) {
                                                echo '<small class="text-muted">TXN: </small>' . htmlspecialchars($payment['txn_id']);
                                            } else {
                                                echo '—';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($payment['fee_type'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst(strtolower($payment['payment_mode'] ?? '—'))); ?></td>
                                        <td style="text-align:right;">&#8377; <?php echo number_format($payment['amount_raw'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot style="background:#f9f9f9;">
                                <?php if ($total_fee > 0): ?>
                                <tr>
                                    <td colspan="4"><strong>Total Fee</strong></td>
                                    <td style="text-align:right;"><strong>&#8377; <?php echo number_format($total_fee, 2); ?></strong></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td colspan="4"><strong>Total Paid</strong></td>
                                    <td style="text-align:right;"><strong class="text-green">&#8377; <?php echo number_format($total_paid, 2); ?></strong></td>
                                </tr>
                                <?php if ($total_fee > 0): ?>
                                <tr>
                                    <td colspan="4"><strong>Balance Due</strong></td>
                                    <td style="text-align:right;"><strong class="<?php echo $balance > 0 ? 'text-red' : 'text-green'; ?>">&#8377; <?php echo number_format($balance, 2); ?></strong></td>
                                </tr>
                                <?php endif; ?>
                            </tfoot>
                        </table>
                        <?php if ($balance > 0): ?>
                        <div class="box-footer" style="text-align:right;">
                            <a href="<?php echo base_url('public_admission/initiate_course_fee_payment'); ?>"
                               class="btn btn-success btn-lg"
                               onclick="return confirm('You will be redirected to the payment gateway to pay ₹<?php echo number_format($balance, 2); ?> as course fee. Continue?');">
                                <i class="fa fa-credit-card"></i>
                                Pay Balance &#8377; <?php echo number_format($balance, 2); ?> Online
                            </a>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="box-body">
                            <div class="callout callout-info" style="margin:0;">
                                <p><i class="fa fa-info-circle"></i> No payments recorded yet.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Right column: Exams -->
        <div class="col-md-5">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-pencil-square-o"></i> Online Exams</h3>
                    <div class="box-tools pull-right">
                        <a href="<?php echo base_url('public_admission/exam_list'); ?>" class="btn btn-warning btn-xs">
                            <i class="fa fa-list"></i> View All
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <?php if (!empty($assigned_exams)): ?>
                        <ul class="products-list product-list-in-box">
                            <?php foreach ((array)$assigned_exams as $exam): ?>
                                <li class="item">
                                    <div class="product-info">
                                        <span class="product-title">
                                            <i class="fa fa-file"></i> <?php echo htmlspecialchars($exam->exam); ?>
                                        </span>
                                        <span class="product-description">
                                            <?php if ($exam->is_attempted == 1): ?>
                                                <span class="label label-success">Attempted</span>
                                                <?php if (!empty($exam->publish_result) || !empty($exam->publish_result_no_answers) || ($exam->is_quiz && !empty($exam->show_result_immediately))): ?>
                                                    &nbsp;<a href="<?php echo site_url('public_admission/exam_view/' . $exam->id); ?>" class="btn btn-xs btn-success">
                                                        <i class="fa fa-bar-chart"></i> View Result
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="label label-info">Pending</span>
                                            <?php endif; ?>
                                            &nbsp;<a href="<?php echo site_url('public_admission/hall_ticket/' . $exam->id); ?>" target="_blank" class="btn btn-xs btn-default">
                                                <i class="fa fa-print"></i> Hall Ticket
                                            </a>
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="callout callout-info">
                            <p><i class="fa fa-info-circle"></i> No exams assigned yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="box-footer clearfix">
                    <a href="<?php echo base_url('public_admission/exam_list'); ?>" class="btn btn-sm btn-warning btn-flat pull-right">
                        <i class="fa fa-arrow-right"></i> Go to Exams
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($is_first_login)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    swal({
        title: 'Welcome!',
        text: 'Welcome to <?php echo addslashes($sch_name ?? 'Applicant Portal'); ?> Applicant Portal. Your application reference number is <?php echo addslashes($applicant_info->reference_no ?? ''); ?>. Please keep this reference number safe for future communication.',
        type: 'success',
        confirmButtonText: 'Get Started',
        confirmButtonColor: '#3c8dbc',
        closeOnClickOutside: false
    });
});
</script>
<?php endif; ?>
