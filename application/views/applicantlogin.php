<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Applicant Login : <?php echo htmlspecialchars($name); ?></title>
    <link href="<?php echo base_url(); ?>uploads/school_content/admin_small_logo/<?php $this->setting_model->getAdminsmalllogo();?>" rel="shortcut icon" type="image/x-icon">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,100,300,500">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/css/form-elements.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/css/style.css">
    <style>
        body { background: #1F4E79; }
        .login-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #fff; border-radius: 8px; padding: 36px 32px 28px; max-width: 400px; width: 100%; box-shadow: 0 8px 32px rgba(0,0,0,0.18); }
        .login-logo { text-align: center; margin-bottom: 18px; }
        .login-logo img { max-height: 80px; width: auto; }
        .login-title { text-align: center; color: #1F4E79; font-size: 20px; font-weight: 600; margin-bottom: 6px; }
        .login-subtitle { text-align: center; color: #888; font-size: 13px; margin-bottom: 22px; }
        .form-control { border-radius: 4px; }
        .btn-login { background: #1F4E79; color: #fff; width: 100%; padding: 10px; border: none; border-radius: 4px; font-size: 15px; font-weight: 500; margin-top: 6px; }
        .btn-login:hover { background: #163d61; color: #fff; }
        .back-link { text-align: center; margin-top: 16px; font-size: 13px; }
        .back-link a { color: #1F4E79; }
        .hint-box { background: #eaf3fb; border-left: 4px solid #1F4E79; padding: 10px 14px; border-radius: 4px; font-size: 12px; color: #444; margin-top: 10px; }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="login-logo">
            <img src="<?php echo base_url(); ?>uploads/school_content/logo/<?php echo $this->setting_model->getPrintlogo();?>" alt="<?php echo htmlspecialchars($name); ?>">
        </div>
        <div class="login-title">Online Exam Portal</div>
        <div class="login-subtitle"><?php echo htmlspecialchars($name); ?></div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="<?php echo site_url('site/applicantlogin'); ?>" method="post">
            <?php echo $this->customlib->getCSRF(); ?>
            <div class="form-group">
                <label>Reference Number <span class="text-danger">*</span></label>
                <input type="text" name="username" value="<?php echo set_value('username'); ?>" placeholder="e.g. MCE2026001" class="form-control" autocomplete="username" autofocus>
                <span class="text-danger"><?php echo form_error('username'); ?></span>
            </div>
            <div class="form-group">
                <label>Password <span class="text-danger">*</span></label>
                <input type="password" name="password" placeholder="Password" class="form-control" autocomplete="current-password">
                <span class="text-danger"><?php echo form_error('password'); ?></span>
            </div>
            <button type="submit" class="btn btn-login">Sign In &nbsp;<i class="fa fa-sign-in"></i></button>
        </form>

        <div class="hint-box">
            <i class="fa fa-info-circle"></i>&nbsp;
            Your <strong>username</strong> is your Reference Number.<br>
            Your <strong>password</strong> is your Reference Number followed by <code>@ApplicantPortal<?php echo date('Y'); ?></code>.
        </div>

        <div class="back-link">
            <a href="<?php echo site_url('site/userlogin'); ?>"><i class="fa fa-arrow-left"></i> Back to main login</a>
        </div>
    </div>
</div>
<script src="<?php echo base_url(); ?>backend/usertemplate/assets/js/jquery-1.11.1.min.js"></script>
<script src="<?php echo base_url(); ?>backend/usertemplate/assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
