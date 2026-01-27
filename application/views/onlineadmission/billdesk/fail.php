<!DOCTYPE html>
<html>
<head>
    <title><?php echo $this->lang->line('payment_failed'); ?></title>
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
                                        <div class="successpayment-icon"><i class="fa fa-close"></i></div>
                                        <h1><?php echo $this->lang->line('payment_failed'); ?></h1>
                                        <p><?php echo $this->lang->line('your_transaction_has_failed_due_to_some_technical_error'); ?></p>
                                        <?php if (isset($response['transaction_error_desc'])) { ?>
                                            <p><?php echo $this->lang->line('error'); ?>: <?php echo $response['transaction_error_desc']; ?></p>
                                        <?php } ?>
                                        <p><?php echo $this->lang->line('admission_form_reference'); ?>: <?php echo isset($reference_no) ? $reference_no : ''; ?></p>
                                        <a href="<?php echo base_url('publicadmissionform/confirm_payment'); ?>" class="btn btn-info btn-lg mt30"><?php echo $this->lang->line('try_again'); ?></a>
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