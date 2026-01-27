<!DOCTYPE html>
<html>
<head>
    <title><?php echo $this->lang->line('payment_pending'); ?></title>
    <link href="<?php echo base_url(); ?>backend/dist/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="content-wrapper">
                    <section class="content">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <div class="successpayment-icon"><i class="fa fa-info-circle"></i></div>
                                        <h1><?php echo $this->lang->line('payment_pending'); ?></h1>
                                        <p><?php echo $this->lang->line('your_payment_is_pending'); ?></p>
                                        <p><?php echo $this->lang->line('please_check_your_transaction_status_later'); ?></p>
                                        <p><?php echo $this->lang->line('admission_form_reference'); ?>: <?php echo isset($reference_no) ? $reference_no : ''; ?></p>
                                        <a href="<?php echo base_url('publicadmissionform/online_admission_review/' . $reference_no); ?>" class="btn btn-info btn-lg mt30"><?php echo $this->lang->line('view_application'); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</body>
</html>