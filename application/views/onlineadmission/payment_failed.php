<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $setting->name;?></title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <meta http-equiv="Cache-control" content="no-cache">
  <meta name="theme-color" content="#424242" />
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/style-main.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/font-awesome.min.css">
</head>
<body class="bg-light-gray">
     <div class="payment-main">
       <div class="container">
        <div class="row">
          <div class="col-lg-6 col-lg-offset-3">
            <div class="failedpayment">
              <div class="failed-circle" style="background:#f39c12;">
                <div class="successpayment-icon"><i class="fa fa-exclamation"></i></div>
              </div>
              <h1 style="color:#e67e22;">Your Application Has Been Saved</h1>
              <p style="font-size:16px; margin-top:10px;">
                Your application (Ref: <strong><?php echo htmlspecialchars($reference_no); ?></strong>) has been recorded.
                However, your application will <strong>not be considered</strong> for the admission process until the
                <strong>Application Fee</strong> has been paid.
              </p>
              <div style="background:#fff3cd; border:1px solid #ffc107; border-radius:6px; padding:15px 20px; margin:20px 0; text-align:left;">
                <p style="margin:0 0 8px;"><strong>Please login to the Applicant Portal to pay the Application Fee:</strong></p>
                <p style="margin:0 0 6px;">
                  <i class="fa fa-link"></i>&nbsp;
                  <a href="https://mce.beebasoft.com/site/applicantlogin" target="_blank">
                    https://mce.beebasoft.com/site/applicantlogin
                  </a>
                </p>
                <p style="margin:0 0 6px;">
                  <i class="fa fa-user"></i>&nbsp; <strong>Username:</strong>
                  <?php echo htmlspecialchars($reference_no); ?>
                </p>
                <p style="margin:0;">
                  <i class="fa fa-key"></i>&nbsp; <strong>Default Password:</strong>
                  <?php echo htmlspecialchars($reference_no); ?>@ApplicantPortal2026
                </p>
              </div>
              <a href="<?php echo site_url('public_admission/online_admission_review/' . $reference_no); ?>" class="btn btn-info btn-lg mt30" style="margin-right:8px;">
                <i class="fa fa-refresh"></i>&nbsp; Try Again
              </a>
              <a href="<?php echo site_url('site/applicantlogin'); ?>" class="btn btn-warning btn-lg mt30">
                <i class="fa fa-sign-in"></i>&nbsp; Login to Applicant Portal
              </a>
            </div>
         </div>  
        </div>  
        </div>  
      </div>     
   </body>
</html>