<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <title><?php echo $this->setting[0]['name']; ?></title>
        <link href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo base_url(); ?>backend/dist/css/style-main.css" rel="stylesheet">
    </head>
    <body style="background: #ededed;">
        <div class="container">
            <div class="row">
                <div class="paddtop20">
                    <div class="col-md-8 col-md-offset-2 text-center">
                        <img src="<?php echo base_url('uploads/school_content/logo/' . $this->setting[0]['image']); ?>">
                    </div>
                    <div class="col-md-6 col-md-offset-3 mt20">
                        <div class="paymentbg">
                            <div class="invtext">Payment Pending</div>
                            <div class="padd20">
                                <p>Your payment status is pending. Please wait for up to 60 minutes for the status to be updated.</p>
                                <p>If money was deducted from your account, do not attempt to pay again immediately.</p>
                                <?php if (isset($response['transaction_error_desc'])) { ?>
                                    <p>Status: <?php echo $response['transaction_error_desc']; ?></p>
                                <?php } ?>
                                <p>Redirecting back to fees in 10 seconds...</p>
                                <div class="text-center mt20">
                                    <a href="<?php echo base_url('user/user/getfees'); ?>" class="btn btn-primary">Back to Fees</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            setTimeout(function(){
                window.location.href = "<?php echo base_url('user/user/getfees'); ?>";
            }, 10000);
        </script>
    </body>
</html>
