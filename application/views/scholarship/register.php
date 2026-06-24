<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $sch_setting['name'] ?? 'Scholarship Exam'; ?> — Registration</title>
<link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/font-awesome.min.css">
<style>
body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f0f2f5; margin: 0; }
.reg-header { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; padding: 36px 0 50px; text-align: center; }
.reg-header h1 { font-size: 26px; font-weight: 800; margin: 0; }
.reg-header p { font-size: 16px; opacity: 1; margin: 8px 0 0; font-weight: 600; }
.reg-logo { max-height: 100px; margin-bottom: 14px; }
.reg-card { max-width: 650px; margin: -30px auto 40px; background: #fff; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.1); padding: 32px 36px; position: relative; z-index: 1; }
.reg-card h2 { font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 20px; }
.form-group label { font-size: 12px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.4px; }
.form-group label .req { color: #ef4444; }
.form-control { height: 40px; border: 1.5px solid #e2e8f0; border-radius: 6px; font-size: 14px; }
.form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
select.form-control { appearance: none; background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 8.825L1.175 4 2.238 2.938 6 6.7l3.763-3.763L10.825 4z'/%3E%3C/svg%3E") no-repeat right 12px center; padding-right: 32px; }
.btn-register { background: #4f46e5; border: none; color: #fff; font-size: 15px; font-weight: 700; padding: 12px; border-radius: 8px; width: 100%; }
.btn-register:hover { background: #4338ca; color: #fff; }
.text-danger { font-size: 12px; }
.info-banner { background: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 13px; color: #92400e; }
.footer-text { text-align: center; font-size: 12px; color: #94a3b8; margin: 20px 0; }
</style>
</head>
<body>

<div class="reg-header">
    <?php if (!empty($sch_setting['image'])) { ?>
        <img src="<?php echo base_url('uploads/school_content/logo/' . $sch_setting['image']); ?>" class="reg-logo" alt="Logo">
    <?php } ?>
    <h1><?php echo htmlspecialchars($sch_setting['name'] ?? 'Institution'); ?></h1>
    <p><i class="fa fa-graduation-cap"></i> Scholarship Exam Registration</p>
</div>

<div class="reg-card">
    <?php if (!empty($no_exams)): ?>
        <div style="text-align:center; padding:40px 0;">
            <i class="fa fa-calendar-times-o" style="font-size:48px; color:#94a3b8;"></i>
            <h3 style="color:#64748b; margin-top:16px;">No Active Scholarship Exams</h3>
            <p style="color:#94a3b8;">There are no scholarship exams open for registration at this time.</p>
        </div>
    <?php else: ?>

    <h2><i class="fa fa-edit" style="color:#4f46e5;"></i> Registration Form</h2>

    <div class="info-banner">
        <i class="fa fa-info-circle"></i> Registration is <strong>free</strong>. After registration you will receive login credentials to access your exam dashboard, download hall ticket, and take the exam.
    </div>

    <?php $validation_errors = validation_errors();
    if (!empty($validation_errors)): ?>
        <div class="alert alert-danger" style="border-radius:8px; font-size:13px;">
            <?php echo $validation_errors; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo site_url('scholarship_register/submit'); ?>" enctype="multipart/form-data">
        <?php if (function_exists('form_hidden')) echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>First Name <span class="req">*</span></label>
                    <input type="text" name="firstname" class="form-control" value="<?php echo set_value('firstname'); ?>" placeholder="First Name" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="lastname" class="form-control" value="<?php echo set_value('lastname'); ?>" placeholder="Last Name">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Mobile Number <span class="req">*</span></label>
                    <input type="tel" name="mobile" class="form-control" value="<?php echo set_value('mobile'); ?>" placeholder="10-digit mobile" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo set_value('email'); ?>" placeholder="your@email.com">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Gender <span class="req">*</span></label>
                    <select name="gender" class="form-control" required>
                        <option value="">Select</option>
                        <option value="Male" <?php echo set_select('gender', 'Male'); ?>>Male</option>
                        <option value="Female" <?php echo set_select('gender', 'Female'); ?>>Female</option>
                        <option value="Other" <?php echo set_select('gender', 'Other'); ?>>Other</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" class="form-control" value="<?php echo set_value('dob'); ?>">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Preferred Course <span class="req">*</span></label>
                    <select name="preferred_course_id" class="form-control" required>
                        <option value="">-- Select Course --</option>
                        <?php if (!empty($courses)) {
                            foreach ($courses as $c) {
                                $cid = is_array($c) ? $c['id'] : $c->id;
                                $cname = is_array($c) ? $c['course_name'] : $c->course_name;
                                $ccode = is_array($c) ? $c['course_code'] : $c->course_code;
                                $sel = set_select('preferred_course_id', $cid);
                                echo '<option value="' . $cid . '" ' . $sel . '>' . htmlspecialchars($cname) . ' (' . htmlspecialchars($ccode) . ')</option>';
                            }
                        } ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>School / College Name</label>
                    <input type="text" name="school_name" class="form-control" value="<?php echo set_value('school_name'); ?>" placeholder="Current school or college">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="school_city" class="form-control" value="<?php echo set_value('school_city'); ?>" placeholder="City">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Photo</label>
                    <input type="file" name="photo" id="photo" class="form-control" accept="image/jpeg,image/png" style="padding:8px; border:1.5px dashed #e2e8f0; border-radius:6px; cursor:pointer;">
                    <span style="font-size:11px; color:#94a3b8; margin-top:4px; display:block;"><i class="fa fa-info-circle"></i> JPG, PNG only. Max 300KB.</span>
                </div>
            </div>
        </div>

        <div style="margin-top:16px;">
            <button type="submit" class="btn btn-register"><i class="fa fa-check-circle"></i> Register for Scholarship Exam</button>
        </div>
    </form>

    <?php endif; ?>
</div>

<div class="footer-text">
    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($sch_setting['name'] ?? ''); ?>. Powered by Minerva ERP.
</div>

<script>
document.getElementById('photo').addEventListener('change', function() {
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
    }
});
</script>
</body>
</html>
