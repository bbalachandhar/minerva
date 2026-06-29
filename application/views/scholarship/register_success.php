<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo !empty($already_registered) ? 'Already Registered' : 'Registration Successful'; ?> — Scholarship Exam</title>
<link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/font-awesome.min.css">
<style>
body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f0f2f5; margin: 0; }
.success-header { background: linear-gradient(135deg, #10b981, #059669); color: #fff; padding: 24px 0; text-align: center; }
.success-header h1 { font-size: 22px; font-weight: 800; margin: 0; }
.success-card { max-width: 550px; margin: -30px auto 40px; background: #fff; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.1); padding: 32px 36px; position: relative; z-index: 1; text-align: center; }
.success-icon { width: 72px; height: 72px; border-radius: 50%; background: #d1fae5; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
.success-icon i { font-size: 36px; color: #10b981; }
.cred-box { background: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 10px; padding: 20px; margin: 20px 0; text-align: left; }
.cred-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
.cred-row:last-child { border-bottom: none; }
.cred-label { font-size: 12px; text-transform: uppercase; color: #94a3b8; font-weight: 600; letter-spacing: 0.5px; }
.cred-value { font-size: 16px; font-weight: 700; color: #0f172a; }
.cred-value.primary { color: #4f46e5; }
.warn-box { background: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #92400e; text-align: left; margin-top: 16px; }
.btn-login { background: #4f46e5; border: none; color: #fff; font-size: 15px; font-weight: 700; padding: 12px 32px; border-radius: 8px; margin-top: 20px; display: inline-block; text-decoration: none; }
.btn-login:hover { background: #4338ca; color: #fff; text-decoration: none; }
.footer-text { text-align: center; font-size: 12px; color: #94a3b8; margin: 20px 0; }
</style>
</head>
<body>

<?php if (!empty($already_registered)): ?>

<div class="success-header" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
    <h1><i class="fa fa-info-circle"></i> Already Registered</h1>
</div>

<div class="success-card">
    <div class="success-icon" style="background:#fef3c7;"><i class="fa fa-exclamation-triangle" style="color:#f59e0b;"></i></div>
    <h2 style="font-size:20px; font-weight:700; color:#1e293b; margin-bottom:4px;">You have already registered!</h2>
    <p style="color:#64748b; font-size:14px;">A scholarship exam registration already exists for <strong><?php echo htmlspecialchars($existing_email); ?></strong>.</p>

    <div class="cred-box">
        <div class="cred-row">
            <span class="cred-label">Your Reference No</span>
            <span class="cred-value primary"><?php echo $existing_ref; ?></span>
        </div>
    </div>

    <div class="warn-box">
        <i class="fa fa-info-circle"></i> If you forgot your password, please contact the institution office with your Reference No.
    </div>

    <a href="<?php echo site_url('site/applicantlogin'); ?>" class="btn-login">
        <i class="fa fa-sign-in"></i> Login to Exam Portal
    </a>
</div>

<?php else: ?>

<div class="success-header">
    <h1><i class="fa fa-check-circle"></i> Registration Successful!</h1>
</div>

<div class="success-card">
    <div class="success-icon"><i class="fa fa-check"></i></div>
    <h2 style="font-size:20px; font-weight:700; color:#1e293b; margin-bottom:4px;">Welcome, <?php echo htmlspecialchars($firstname); ?>!</h2>
    <p style="color:#64748b; font-size:14px;">Your scholarship exam registration is complete. <?php echo $assigned_exams; ?> exam(s) have been assigned to your account.</p>

    <div style="background:#d1fae5; border:1px solid #10b981; border-radius:8px; padding:14px 16px; font-size:14px; color:#065f46; margin-bottom:16px;">
        <i class="fa fa-check-circle"></i> <strong>Form submitted successfully!</strong> Do not submit again.
    </div>

    <div class="cred-box">
        <div class="cred-row">
            <span class="cred-label">Reference No (Username)</span>
            <span class="cred-value primary"><?php echo $reference_no; ?></span>
        </div>
        <div class="cred-row">
            <span class="cred-label">Password</span>
            <span class="cred-value"><?php echo htmlspecialchars($password); ?></span>
        </div>
    </div>

    <div class="warn-box">
        <i class="fa fa-exclamation-triangle"></i> <strong>Important:</strong> Please save your Reference No and Password. You will need these to login, download your hall ticket, and take the exam.
    </div>

    <a href="<?php echo site_url('site/applicantlogin'); ?>" class="btn-login">
        <i class="fa fa-sign-in"></i> Login to Exam Portal
    </a>
</div>

<?php endif; ?>

<div class="footer-text">
    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($sch_setting['name'] ?? ''); ?>. Powered by Minerva ERP.
</div>

</body>
</html>
