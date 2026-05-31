<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login : <?php echo htmlspecialchars($name); ?></title>
    <link href="<?php echo base_url(); ?>uploads/school_content/admin_small_logo/<?php echo $this->setting_model->getAdminsmalllogo(); ?>" rel="shortcut icon" type="image/x-icon">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,100,300,500">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/font-awesome/css/font-awesome.min.css">
    <?php $btn_color = !empty($school['app_primary_color_code']) ? htmlspecialchars($school['app_primary_color_code']) : '#1F4E79'; ?>
    <style>
        body { margin: 0; padding: 0; font-family: 'Roboto', sans-serif; }
        .login-bg {
            position: fixed; inset: 0; z-index: 0;
            background-color: #1F4E79; background-size: cover; background-position: center; background-repeat: no-repeat;
            filter: blur(6px) brightness(0.55);
            transform: scale(1.04);
        }
        .login-wrap { position: relative; z-index: 1; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card { background: rgba(255,255,255,0.97); border-radius: 10px; padding: 36px 32px 28px; max-width: 420px; width: 100%; box-shadow: 0 12px 48px rgba(0,0,0,0.45); }
        .login-logo { text-align: center; margin-bottom: 16px; }
        .login-logo img { max-height: 80px; width: auto; }
        .login-title { text-align: center; color: #1F4E79; font-size: 21px; font-weight: 700; margin-bottom: 4px; }
        .login-subtitle { text-align: center; color: #888; font-size: 13px; margin-bottom: 24px; }
        .form-group { margin-bottom: 16px; }
        .form-control { border-radius: 4px; height: 40px; }
        .input-icon { position: relative; }
        .input-icon .fa { position: absolute; top: 50%; right: 12px; transform: translateY(-50%); color: #aaa; }
        .btn-login { background: <?php echo $btn_color; ?>; color: #fff; width: 100%; padding: 10px; border: none; border-radius: 4px; font-size: 15px; font-weight: 500; margin-top: 4px; cursor: pointer; transition: filter 0.2s; }
        .btn-login:hover { filter: brightness(0.85); }
        .login-footer { text-align: center; margin-top: 18px; font-size: 13px; }
        .login-footer a { color: #1F4E79; }
        .captcha-row { display: flex; gap: 10px; align-items: center; }
        .captcha-row .captcha-input { flex: 1; }
        .fa-refresh.catpcha { cursor: pointer; color: #1F4E79; font-size: 18px; margin-left: 6px; }
    </style>
</head>
<body>
<div class="login-bg"<?php if (!empty($school['admin_login_page_background'])): ?> style="background-image:url('<?php echo base_url(); ?>uploads/school_content/login_image/<?php echo htmlspecialchars($school['admin_login_page_background']); ?>')"<?php endif; ?>></div>
<div class="login-wrap">
    <div class="login-card">
        <div class="login-logo">
            <img src="<?php echo base_url(); ?>uploads/school_content/logo/<?php echo $this->setting_model->getPrintlogo(); ?>" alt="<?php echo htmlspecialchars($name); ?>">
        </div>
        <div class="login-title"><?php echo $this->lang->line('admin_login'); ?></div>
        <div class="login-subtitle"><?php echo htmlspecialchars($name); ?></div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('message')): ?>
            <div class="alert alert-success"><?php echo $this->session->flashdata('message'); ?></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('disable_message')): ?>
            <div class="alert alert-danger"><?php echo $this->session->flashdata('disable_message'); ?></div>
        <?php endif; ?>

        <form action="<?php echo site_url('site/login'); ?>" method="post">
            <?php echo $this->customlib->getCSRF(); ?>
            <div class="form-group">
                <label><?php echo $this->lang->line('username'); ?></label>
                <div class="input-icon">
                    <input type="text" name="username" value="<?php echo set_value('username'); ?>" placeholder="<?php echo $this->lang->line('username'); ?>" class="form-control" id="form-username" autocomplete="username" autofocus>
                    <span class="fa fa-user"></span>
                </div>
                <span class="text-danger small"><?php echo form_error('username'); ?></span>
            </div>
            <div class="form-group">
                <label><?php echo $this->lang->line('password'); ?></label>
                <div class="input-icon">
                    <input type="password" name="password" value="<?php echo set_value('password'); ?>" placeholder="<?php echo $this->lang->line('password'); ?>" class="form-control" id="form-password" autocomplete="current-password">
                    <span class="fa fa-lock"></span>
                </div>
                <span class="text-danger small"><?php echo form_error('password'); ?></span>
            </div>
            <?php if ($is_captcha): ?>
            <div class="form-group">
                <label><?php echo $this->lang->line('captcha'); ?></label>
                <div class="captcha-row">
                    <span id="captcha_image"><?php echo $captcha_image; ?></span>
                    <span title="Refresh Captcha" class="fa fa-refresh catpcha" onclick="refreshCaptcha()"></span>
                    <input type="text" name="captcha" placeholder="<?php echo $this->lang->line('captcha'); ?>" class="form-control captcha-input" autocomplete="off" id="captcha">
                </div>
                <span class="text-danger small"><?php echo form_error('captcha'); ?></span>
            </div>
            <?php endif; ?>
            <button type="submit" class="btn-login"><?php echo $this->lang->line('sign_in'); ?> &nbsp;<i class="fa fa-sign-in"></i></button>
        </form>

        <div style="border-top:1px solid #eee;margin:14px 0;"></div>
        <div style="text-align:center;">
            <a href="#" onclick="document.getElementById('qr-section').style.display=(document.getElementById('qr-section').style.display=='none'?'block':'none');return false;" style="color:#1F4E79;font-size:13px;"><i class="fa fa-mobile" style="font-size:16px;"></i> &nbsp;Scan to set up Minerva mobile app</a>
            <div id="qr-section" style="display:none;margin-top:12px;">
                <img src="<?php echo site_url('site/app_qr_png'); ?>" alt="App Setup QR Code" style="width:160px;height:160px;border:1px solid #e0e0e0;border-radius:6px;padding:4px;">
                <div style="font-size:11px;color:#888;margin-top:6px;">Open Minerva app &rarr; tap <strong>Scan QR</strong> on the server setup screen to fill in the API URL automatically.</div>
            </div>
        </div>

        <div class="login-footer">
            <a href="<?php echo site_url('site/forgotpassword'); ?>"><i class="fa fa-key"></i> <?php echo $this->lang->line('forgot_password'); ?>?</a>
        </div>
    </div>
</div>
<script src="<?php echo base_url(); ?>backend/usertemplate/assets/js/jquery-1.11.1.min.js"></script>
<script src="<?php echo base_url(); ?>backend/usertemplate/assets/bootstrap/js/bootstrap.min.js"></script>
<script>
function refreshCaptcha() {
    $.ajax({
        type: "POST",
        url: "<?php echo base_url('site/refreshCaptcha'); ?>",
        data: {},
        success: function(captcha) { $("#captcha_image").html(captcha); }
    });
}
</script>
</body>
</html>
