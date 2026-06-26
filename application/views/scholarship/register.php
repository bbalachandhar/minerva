<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $sch_setting['name'] ?? 'Scholarship Exam'; ?> — Registration</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<style>
    :root {
        --primary: #1e3a5f;
        --primary-light: #1e40af;
        --accent: #2563eb;
        --text-dark: #111827;
        --text-muted: #6b7280;
        --text-label: #374151;
        --border: #d1d5db;
        --border-light: #e5e7eb;
        --bg-page: #f3f4f6;
        --bg-card: #ffffff;
        --danger: #dc2626;
        --radius-sm: 8px;
        --radius-md: 12px;
    }
    *, *::before, *::after { box-sizing: border-box; }
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--bg-page); color: var(--text-dark); margin: 0; padding: 0; line-height: 1.6;
    }
    .page-wrapper { max-width: 720px; margin: 0 auto; padding: 0 16px 48px; }
    .form-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 0 0 var(--radius-md) var(--radius-md);
        padding: 28px 32px; display: flex; align-items: center; gap: 20px;
        margin-bottom: 28px; box-shadow: 0 4px 20px rgba(30,58,95,0.25);
    }
    .form-header .logo-img {
        height: 60px; width: auto; object-fit: contain;
        background: #fff; border-radius: 8px; padding: 6px; flex-shrink: 0;
    }
    .form-header .header-text { flex: 1; text-align: center; color: #fff; }
    .form-header .header-text h1 { font-size: 20px; font-weight: 800; margin: 0 0 4px; letter-spacing: 0.5px; }
    .form-header .header-text p { font-size: 12px; margin: 0; opacity: 0.85; }
    .form-header .header-text .badge-line {
        display: inline-block; background: rgba(255,255,255,0.15); padding: 4px 14px;
        border-radius: 20px; font-size: 12px; font-weight: 600; margin-top: 8px; letter-spacing: 0.5px;
    }
    @media (max-width: 600px) {
        .form-header { flex-direction: column; text-align: center; padding: 20px 16px; }
        .form-header .header-text h1 { font-size: 16px; }
    }
    .section-card {
        background: var(--bg-card); border-radius: var(--radius-md);
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        padding: 28px; margin-bottom: 20px; border-left: 4px solid var(--accent);
    }
    .section-title {
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px;
        color: var(--text-muted); margin: 0 0 20px; padding-bottom: 10px;
        border-bottom: 1px solid var(--border-light); display: flex; align-items: center; gap: 8px;
    }
    .section-title i { font-size: 16px; color: var(--accent); }
    .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .field-grid .full-width { grid-column: 1 / -1; }
    @media (max-width: 600px) { .field-grid { grid-template-columns: 1fr; } }
    .form-label {
        display: block; font-size: 12px; font-weight: 600; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-label); margin-bottom: 6px;
    }
    .form-control, .form-select {
        height: 44px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: 14px; padding: 8px 14px; transition: border-color 0.2s, box-shadow 0.2s; background: #fff;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,0.12); outline: none;
    }
    .form-text { font-size: 11px; color: var(--text-muted); margin-top: 4px; }
    .info-banner {
        background: #fffbeb; border: 1px solid #fde68a; border-radius: var(--radius-sm);
        padding: 12px 16px; margin-bottom: 20px; font-size: 13px; color: #92400e;
        display: flex; align-items: flex-start; gap: 8px;
    }
    .info-banner i { font-size: 16px; margin-top: 1px; flex-shrink: 0; }
    .btn-submit {
        display: block; width: 100%; height: 48px;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        color: #fff; border: none; border-radius: var(--radius-sm);
        font-size: 15px; font-weight: 700; letter-spacing: 0.5px; cursor: pointer;
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .btn-submit:hover { box-shadow: 0 6px 20px rgba(37,99,235,0.3); transform: translateY(-1px); }
    .alert-danger {
        background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
        border-radius: var(--radius-sm); padding: 12px 16px; font-size: 13px; margin-bottom: 16px;
    }
    .upload-zone {
        border: 2px dashed var(--border); border-radius: var(--radius-sm); padding: 16px;
        text-align: center; cursor: pointer; transition: border-color 0.2s, background 0.2s; position: relative;
    }
    .upload-zone:hover { border-color: var(--accent); background: #f8fbff; }
    .upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .upload-zone .upload-icon { font-size: 24px; color: #bbb; }
    .upload-zone .upload-text { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
    .empty-state {
        text-align: center; padding: 48px 20px;
        background: var(--bg-card); border-radius: var(--radius-md);
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    .empty-state i { font-size: 48px; color: #d1d5db; }
    .empty-state h3 { font-size: 18px; color: var(--text-muted); margin: 16px 0 8px; }
    .empty-state p { color: #9ca3af; font-size: 14px; }
    .page-footer { text-align: center; padding: 16px 0; font-size: 11px; color: var(--text-muted); }
</style>
</head>
<body>
<div class="page-wrapper">
    <div class="form-header">
        <?php if (!empty($sch_setting['admission_logo_left'])): ?>
        <img src="<?php echo base_url('uploads/logos/' . $sch_setting['admission_logo_left']); ?>" alt="" class="logo-img">
        <?php elseif (!empty($sch_setting['image'])): ?>
        <img src="<?php echo base_url('uploads/school_content/logo/' . $sch_setting['image']); ?>" alt="" class="logo-img">
        <?php endif; ?>
        <div class="header-text">
            <h1><?php echo htmlspecialchars($sch_setting['name'] ?? 'Institution'); ?></h1>
            <?php if (!empty($sch_setting['address'])): ?>
            <p><?php echo $sch_setting['address']; ?><br>Ph: <?php echo $sch_setting['phone'] ?? ''; ?><?php if (!empty($sch_setting['email'])): ?> | <?php echo $sch_setting['email']; ?><?php endif; ?></p>
            <?php endif; ?>
            <span class="badge-line"><i class="bi bi-mortarboard-fill"></i> Scholarship Exam Registration</span>
        </div>
        <?php if (!empty($sch_setting['admission_logo_right'])): ?>
        <img src="<?php echo base_url('uploads/logos/' . $sch_setting['admission_logo_right']); ?>" alt="" class="logo-img">
        <?php endif; ?>
    </div>

    <?php if (!empty($no_exams)): ?>
    <div class="empty-state">
        <i class="bi bi-calendar-x"></i>
        <h3>No Active Scholarship Exams</h3>
        <p>There are no scholarship exams open for registration at this time.</p>
    </div>
    <?php else: ?>

    <div class="info-banner">
        <i class="bi bi-info-circle-fill"></i>
        <div>Registration is <strong>free</strong>. After registration you will receive login credentials to access your exam dashboard, download hall ticket, and take the exam.</div>
    </div>

    <?php $validation_errors = validation_errors();
    if (!empty($validation_errors)): ?>
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $validation_errors; ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo site_url('scholarship_register/submit'); ?>" enctype="multipart/form-data">
        <?php if (function_exists('form_hidden')) echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

        <div class="section-card">
            <div class="section-title"><i class="bi bi-person-fill"></i> Personal Information</div>
            <div class="field-grid">
                <div>
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="firstname" class="form-control" value="<?php echo set_value('firstname'); ?>" placeholder="First Name" required>
                </div>
                <div>
                    <label class="form-label">Last Name</label>
                    <input type="text" name="lastname" class="form-control" value="<?php echo set_value('lastname'); ?>" placeholder="Last Name">
                </div>
                <div>
                    <label class="form-label">Mobile <span class="text-danger">*</span></label>
                    <input type="tel" name="mobile" class="form-control" value="<?php echo set_value('mobile'); ?>" placeholder="10-digit mobile" required>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo set_value('email'); ?>" placeholder="your@email.com">
                </div>
                <div>
                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="">Select</option>
                        <option value="Male" <?php echo set_select('gender', 'Male'); ?>>Male</option>
                        <option value="Female" <?php echo set_select('gender', 'Female'); ?>>Female</option>
                        <option value="Other" <?php echo set_select('gender', 'Other'); ?>>Other</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" class="form-control" value="<?php echo set_value('dob'); ?>">
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-title"><i class="bi bi-mortarboard-fill"></i> Course & School</div>
            <div class="field-grid">
                <div class="full-width">
                    <label class="form-label">Preferred Course <span class="text-danger">*</span></label>
                    <select name="preferred_course_id" class="form-select" required>
                        <option value="">-- Select Course --</option>
                        <?php if (!empty($courses)) {
                            foreach ($courses as $c) {
                                $cid = is_array($c) ? $c['id'] : $c->id;
                                $cname = is_array($c) ? $c['course_name'] : $c->course_name;
                                $ccode = is_array($c) ? ($c['course_code'] ?? '') : ($c->course_code ?? '');
                                $sel = set_select('preferred_course_id', $cid);
                                $display = htmlspecialchars($cname) . ($ccode ? ' (' . htmlspecialchars($ccode) . ')' : '');
                                echo '<option value="' . $cid . '" ' . $sel . '>' . $display . '</option>';
                            }
                        } ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">School / College Name</label>
                    <input type="text" name="school_name" class="form-control" value="<?php echo set_value('school_name'); ?>" placeholder="Current school or college">
                </div>
                <div>
                    <label class="form-label">City</label>
                    <input type="text" name="school_city" class="form-control" value="<?php echo set_value('school_city'); ?>" placeholder="City">
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-title"><i class="bi bi-camera-fill"></i> Photo</div>
            <div class="upload-zone" id="photo-zone">
                <div class="upload-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                <div class="upload-text">Click or drop photo here</div>
                <input type="file" name="photo" id="photo" accept="image/jpeg,image/png">
                <img id="photo-preview" src="" alt="" style="display:none; max-height:80px; border-radius:6px; margin-top:8px;">
            </div>
            <div class="form-text"><i class="bi bi-info-circle"></i> JPG, PNG only. Max 300KB.</div>
        </div>

        <button type="submit" class="btn-submit">
            <i class="bi bi-check-circle-fill me-2"></i> Register for Scholarship Exam
        </button>
    </form>

    <?php endif; ?>

    <div class="page-footer">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($sch_setting['name'] ?? ''); ?>. Powered by Minerva</div>
</div>

<script>
document.getElementById('photo')?.addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    var ext = file.name.split('.').pop().toLowerCase();
    if (['jpg','jpeg','png'].indexOf(ext) === -1) {
        alert('Only JPG, PNG files are allowed.');
        this.value = '';
        return;
    }
    if (file.size > 300 * 1024) {
        alert('Photo size ' + Math.round(file.size/1024) + 'KB exceeds 300KB limit.');
        this.value = '';
        return;
    }
    var reader = new FileReader();
    reader.onload = function(e) {
        var preview = document.getElementById('photo-preview');
        preview.src = e.target.result;
        preview.style.display = 'block';
        document.querySelector('#photo-zone .upload-icon').style.display = 'none';
        document.querySelector('#photo-zone .upload-text').textContent = file.name;
    };
    reader.readAsDataURL(file);
});
</script>
</body>
</html>
