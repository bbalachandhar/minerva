<?php if (empty($wrapped_layout)): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Review - <?php echo isset($reference_no) ? $reference_no : ''; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<?php endif; ?>

<?php
$applied_course_name = 'Not Selected';
$effective_course_id = (!empty($admission_course_id)) ? $admission_course_id : ((!empty($ug_course_id)) ? $ug_course_id : null);
if (!empty($effective_course_id) && isset($course_names[$effective_course_id])) {
    $applied_course_name = $course_names[$effective_course_id];
}
$is_barch_review = (isset($applied_course_name) && stripos($applied_course_name, 'ARCH') !== false);
$at_label = '';
if (isset($admission_type)) {
    if ($admission_type === 'lateral') $at_label = 'Lateral Entry';
    elseif ($admission_type === 'first_year') $at_label = 'First Year';
}
$fee_paid = (isset($paid_status) && $paid_status == 1);
$is_submitted = (isset($form_status) && (int)$form_status === 1);
$hsc = [
    'maths_marks' => isset($maths_marks) ? $maths_marks : '', 'total_maths' => isset($total_maths) ? $total_maths : '', 'maths_perc' => isset($maths_perc) ? $maths_perc : '',
    'physics_marks' => isset($physics_marks) ? $physics_marks : '', 'total_physics' => isset($total_physics) ? $total_physics : '', 'physics_perc' => isset($physics_perc) ? $physics_perc : '',
    'chemistry_marks' => isset($chemistry_marks) ? $chemistry_marks : '', 'total_chemistry' => isset($total_chemistry) ? $total_chemistry : '', 'chemistry_perc' => isset($chemistry_perc) ? $chemistry_perc : '',
    'average_marks' => isset($average_marks) ? $average_marks : '', 'cutoff_marks' => isset($cutoff_marks) ? $cutoff_marks : '',
];
$show_course_details = (isset($course_level) && $course_level == 'ug') || (isset($ug_course_id) && $ug_course_id) || ($hsc['total_maths'] !== '' && $hsc['total_maths'] !== null) || ($hsc['total_physics'] !== '' && $hsc['total_physics'] !== null);
$v = function($val, $default = 'N/A') { return (!empty($val) || $val === '0' || $val === 0) ? htmlspecialchars($val) : $default; };
?>

<style>
*,:before,:after{box-sizing:border-box;}
.ar{
    --c-primary:#4361ee; --c-primary-light:#eef1ff;
    --c-dark:#0f172a; --c-text:#334155; --c-muted:#64748b; --c-subtle:#94a3b8;
    --c-border:#e2e8f0; --c-bg:#f1f5f9; --c-white:#fff;
    --c-success:#10b981; --c-danger:#ef4444; --c-warning:#f59e0b;
    --radius:10px; --shadow:0 1px 3px rgba(0,0,0,.06),0 1px 2px rgba(0,0,0,.04);
    font-family:'Inter',system-ui,-apple-system,sans-serif; color:var(--c-text); line-height:1.5;
    background:var(--c-bg); padding:28px 16px; min-height:100vh;
}
.ar *{margin:0;padding:0;}

/* layout */
.ar-wrap{max-width:880px;margin:0 auto;}

/* header */
.ar-header-img{width:100%;height:auto;display:block;border-radius:var(--radius) var(--radius) 0 0;}
.ar-hero{
    background:var(--c-white);border-radius:var(--radius);box-shadow:var(--shadow);
    border:1px solid var(--c-border);margin-bottom:24px;overflow:hidden;
}
.ar-hero__body{padding:24px 28px;display:flex;gap:24px;align-items:flex-start;}
.ar-hero__photo{
    width:110px;height:130px;border-radius:8px;border:2px solid var(--c-border);
    object-fit:cover;flex-shrink:0;background:#f8fafc;
}
.ar-hero__photo--empty{
    width:110px;height:130px;border-radius:8px;border:2px dashed var(--c-border);
    display:flex;align-items:center;justify-content:center;color:var(--c-subtle);font-size:12px;
    background:#f8fafc;flex-shrink:0;
}
.ar-hero__info{flex:1;min-width:0;}
.ar-hero__name{font-size:22px;font-weight:800;color:var(--c-dark);margin-bottom:4px;letter-spacing:-.3px;}
.ar-hero__course{font-size:15px;font-weight:600;color:var(--c-primary);margin-bottom:12px;}
.ar-hero__meta{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;}
.ar-tag{
    display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;
    font-size:12px;font-weight:600;letter-spacing:.2px;
}
.ar-tag--ref{background:#e0e7ff;color:#3730a3;}
.ar-tag--year{background:#fef3c7;color:#92400e;}
.ar-tag--quota{background:#f0fdf4;color:#166534;}
.ar-tag--type{background:#fce7f3;color:#9d174d;}
.ar-tag--paid{background:#d1fae5;color:#065f46;}
.ar-tag--pending{background:#fee2e2;color:#991b1b;}
.ar-hero__grid{display:grid;grid-template-columns:1fr 1fr;gap:6px 24px;}
.ar-hero__item{font-size:13px;color:var(--c-muted);}
.ar-hero__item strong{color:var(--c-text);font-weight:600;}

/* cards */
.ar-card{
    background:var(--c-white);border-radius:var(--radius);box-shadow:var(--shadow);
    border:1px solid var(--c-border);margin-bottom:20px;overflow:hidden;
}
.ar-card__head{
    display:flex;align-items:center;gap:10px;
    padding:14px 24px;border-bottom:1px solid var(--c-border);background:#fafbfc;
}
.ar-card__ico{
    width:30px;height:30px;border-radius:7px;display:flex;align-items:center;
    justify-content:center;font-size:13px;flex-shrink:0;
}
.ar-card__ico--blue{background:#dbeafe;color:#2563eb;}
.ar-card__ico--purple{background:#ede9fe;color:#7c3aed;}
.ar-card__ico--green{background:#d1fae5;color:#059669;}
.ar-card__ico--orange{background:#ffedd5;color:#ea580c;}
.ar-card__ico--pink{background:#fce7f3;color:#db2777;}
.ar-card__ico--teal{background:#ccfbf1;color:#0d9488;}
.ar-card__ico--slate{background:#e2e8f0;color:#475569;}
.ar-card__ico--amber{background:#fef3c7;color:#d97706;}
.ar-card__ico--red{background:#fee2e2;color:#dc2626;}
.ar-card__ttl{font-size:14px;font-weight:700;color:var(--c-dark);}
.ar-card__body{padding:20px 24px;}

/* data rows */
.ar-grid{display:grid;gap:14px 28px;}
.ar-grid--2{grid-template-columns:1fr 1fr;}
.ar-grid--3{grid-template-columns:1fr 1fr 1fr;}
.ar-grid--4{grid-template-columns:1fr 1fr 1fr 1fr;}
@media(max-width:768px){.ar-grid--2,.ar-grid--3,.ar-grid--4{grid-template-columns:1fr;}}
.ar-datum{}
.ar-datum__label{font-size:11px;font-weight:600;color:var(--c-subtle);text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;}
.ar-datum__value{font-size:14px;font-weight:500;color:var(--c-dark);word-break:break-word;}
.ar-datum__value--na{color:var(--c-subtle);font-style:italic;font-weight:400;}
.ar-datum__value--highlight{font-size:22px;font-weight:800;color:var(--c-primary);}
.ar-datum__value--danger{font-size:22px;font-weight:800;color:#dc2626;}

/* marks table */
.ar-tbl{width:100%;border-collapse:collapse;}
.ar-tbl th{
    padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;
    letter-spacing:.5px;color:var(--c-subtle);background:#f8fafc;border-bottom:2px solid var(--c-border);text-align:center;
}
.ar-tbl th:first-child{text-align:left;}
.ar-tbl td{padding:12px 16px;border-bottom:1px solid var(--c-border);text-align:center;font-size:14px;font-weight:500;color:var(--c-dark);}
.ar-tbl td:first-child{text-align:left;font-weight:600;}
.ar-tbl tr:last-child td{border-bottom:none;}
.ar-tbl .ar-tbl__highlight{background:#f8fafc;}
.ar-tbl .ar-tbl__big{font-size:18px;font-weight:800;color:var(--c-primary);text-align:center;}

/* scholarship table */
.ar-sch{width:100%;border-collapse:collapse;}
.ar-sch th{padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--c-subtle);background:#f8fafc;border-bottom:2px solid var(--c-border);text-align:left;}
.ar-sch td{padding:12px 16px;border-bottom:1px solid var(--c-border);font-size:14px;}
.ar-sch tr:last-child td{border-bottom:none;}

/* declaration */
.ar-decl{border:2px solid var(--c-border);border-radius:8px;padding:28px;margin-top:4px;}
.ar-decl__title{font-size:16px;font-weight:700;color:var(--c-dark);text-transform:uppercase;padding-bottom:12px;border-bottom:2px solid var(--c-dark);margin-bottom:20px;}
.ar-decl ol{padding-left:20px;margin-bottom:24px;}
.ar-decl ol li{margin-bottom:10px;font-size:13px;line-height:1.7;color:var(--c-text);}
.ar-decl__banner{background:#1e293b;color:#fff;padding:12px 20px;text-align:center;font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin:24px 0;border-radius:6px;}
.ar-decl__sig-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:24px;}
.ar-decl__sig{font-size:13px;font-weight:600;color:var(--c-dark);}
.ar-decl__sig-line{border-bottom:1px solid #000;height:40px;margin-top:8px;margin-bottom:16px;}

/* login info */
.ar-login{
    background:var(--c-primary-light);border:1px solid #c7d2fe;border-radius:var(--radius);
    padding:20px 24px;margin-bottom:20px;
}
.ar-login__title{font-size:15px;font-weight:700;color:var(--c-dark);margin-bottom:10px;}
.ar-login p{font-size:13px;color:var(--c-text);margin-bottom:6px;}
.ar-login a{color:var(--c-primary);font-weight:600;text-decoration:none;}
.ar-login a:hover{text-decoration:underline;}

/* print button */
.ar-print-btn{
    display:inline-flex;align-items:center;gap:8px;padding:12px 32px;border-radius:8px;
    font-size:14px;font-weight:700;border:none;cursor:pointer;
    background:var(--c-primary);color:#fff;transition:all .15s;
}
.ar-print-btn:hover{background:#3451d1;box-shadow:0 4px 12px rgba(67,97,238,.3);}

/* print */
@media print{
    .no-print{display:none!important;}
    body,.ar{background:#fff!important;padding:0!important;}
    .ar-wrap{max-width:100%!important;}
    .ar-card,.ar-hero{box-shadow:none!important;border:1px solid #ddd!important;break-inside:avoid;}
    .ar-card__head{background:#f5f5f5!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}
    .ar-tag{-webkit-print-color-adjust:exact;print-color-adjust:exact;}
    .ar-tbl .ar-tbl__highlight{-webkit-print-color-adjust:exact;print-color-adjust:exact;}
    .ar-decl__banner{-webkit-print-color-adjust:exact;print-color-adjust:exact;}
    body{font-size:9pt;}
}
</style>

<div class="ar">
<div class="ar-wrap printable-area">

    <!-- ━━ Header Image ━━ -->
    <?php if (!empty($general_purpose_header_image)): ?>
    <div class="ar-hero" style="margin-bottom:0;border-radius:var(--radius) var(--radius) 0 0;border-bottom:none;">
        <img class="ar-header-img" src="<?php echo base_url('uploads/print_headerfooter/general_purpose/' . $general_purpose_header_image); ?>">
    </div>
    <?php endif; ?>

    <!-- ━━ Hero Card ━━ -->
    <div class="ar-hero" style="<?php echo !empty($general_purpose_header_image) ? 'border-radius:0 0 var(--radius) var(--radius);border-top:none;' : ''; ?>">
        <div class="ar-hero__body">
            <?php if (isset($student_pic) && !empty($student_pic)): ?>
                <img class="ar-hero__photo" src="<?php echo base_url() . $student_pic; ?>" alt="Photo">
            <?php else: ?>
                <div class="ar-hero__photo--empty">No Photo</div>
            <?php endif; ?>
            <div class="ar-hero__info">
                <div class="ar-hero__name"><?php echo $v(isset($firstname) ? $firstname : '', 'N/A'); ?></div>
                <div class="ar-hero__course"><?php echo $applied_course_name; ?></div>
                <div class="ar-hero__meta">
                    <span class="ar-tag ar-tag--ref">#<?php echo $v($reference_no ?? ''); ?></span>
                    <?php if (!empty($at_label)): ?><span class="ar-tag ar-tag--type"><?php echo $at_label; ?></span><?php endif; ?>
                    <?php if (!empty($quota_type)): ?><span class="ar-tag ar-tag--quota"><?php echo ucfirst($quota_type); ?></span><?php endif; ?>
                    <span class="ar-tag ar-tag--year"><?php echo isset($academic_year) ? $academic_year : '2026-2027'; ?></span>
                    <span class="ar-tag <?php echo $fee_paid ? 'ar-tag--paid' : 'ar-tag--pending'; ?>"><?php echo $fee_paid ? 'Fee Paid' : 'Fee Pending'; ?></span>
                </div>
                <?php if ($is_submitted && !empty($applicant_username)): ?>
                <div class="ar-hero__grid">
                    <div class="ar-hero__item"><strong>Login:</strong> <?php echo $applicant_username; ?></div>
                    <div class="ar-hero__item"><strong>Password:</strong> <?php echo $applicant_password; ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ━━ Personal Details ━━ -->
    <div class="ar-card">
        <div class="ar-card__head">
            <div class="ar-card__ico ar-card__ico--purple"><i class="fa fa-user"></i><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
            <span class="ar-card__ttl">Personal Details</span>
        </div>
        <div class="ar-card__body">
            <div class="ar-grid ar-grid--3">
                <div class="ar-datum"><div class="ar-datum__label">Full Name</div><div class="ar-datum__value"><?php echo $v($firstname ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Gender</div><div class="ar-datum__value"><?php echo $v(isset($gender) ? ucfirst($gender) : ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Community</div><div class="ar-datum__value"><?php echo $v($community ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Date of Birth</div><div class="ar-datum__value"><?php echo (isset($dob) && !empty($dob) && $dob !== '0000-00-00') ? date($this->customlib->getSchoolDateFormat(), strtotime($dob)) : 'N/A'; ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Aadhaar Number</div><div class="ar-datum__value"><?php echo $v($adhar_no ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Email</div><div class="ar-datum__value"><?php echo $v($email ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Student Mobile</div><div class="ar-datum__value"><?php echo $v($mobileno ?? ''); ?></div></div>
            </div>
        </div>
    </div>

    <!-- ━━ Family Information ━━ -->
    <div class="ar-card">
        <div class="ar-card__head">
            <div class="ar-card__ico ar-card__ico--orange"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
            <span class="ar-card__ttl">Family Information</span>
        </div>
        <div class="ar-card__body">
            <div class="ar-grid ar-grid--3">
                <div class="ar-datum"><div class="ar-datum__label">Father's Name</div><div class="ar-datum__value"><?php echo $v($father_name ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Father's Mobile</div><div class="ar-datum__value"><?php echo $v($father_phone ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Father's Occupation</div><div class="ar-datum__value"><?php echo $v($father_occupation ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Mother's Name</div><div class="ar-datum__value"><?php echo $v($mother_name ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Mother's Mobile</div><div class="ar-datum__value"><?php echo $v($mother_phone ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Mother's Occupation</div><div class="ar-datum__value"><?php echo $v($mother_occupation ?? ''); ?></div></div>
            </div>
        </div>
    </div>

    <!-- ━━ Address ━━ -->
    <div class="ar-card">
        <div class="ar-card__head">
            <div class="ar-card__ico ar-card__ico--teal"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
            <span class="ar-card__ttl">Address</span>
        </div>
        <div class="ar-card__body">
            <div class="ar-grid ar-grid--2">
                <div class="ar-datum"><div class="ar-datum__label">Communication Address</div><div class="ar-datum__value"><?php echo $v($current_address ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Permanent Address</div><div class="ar-datum__value"><?php echo $v($permanent_address ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">State</div><div class="ar-datum__value"><?php echo $v($state ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">City</div><div class="ar-datum__value"><?php echo $v($city ?? ''); ?></div></div>
            </div>
        </div>
    </div>

    <!-- ━━ References ━━ -->
    <?php if (isset($reference_details) && $reference_details): ?>
    <div class="ar-card">
        <div class="ar-card__head">
            <div class="ar-card__ico ar-card__ico--slate"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></div>
            <span class="ar-card__ttl">Reference Details</span>
        </div>
        <div class="ar-card__body">
            <div class="ar-grid ar-grid--3">
                <div class="ar-datum"><div class="ar-datum__label">Referrer Name</div><div class="ar-datum__value"><?php echo $v($reference_details['referrer_name'] ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Relationship</div><div class="ar-datum__value"><?php echo $v($reference_details['relationship'] ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Phone</div><div class="ar-datum__value"><?php echo $v($reference_details['phone_no'] ?? ''); ?></div></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ━━ Education Details ━━ -->
    <?php if ($show_course_details): ?>
    <div class="ar-card">
        <div class="ar-card__head">
            <div class="ar-card__ico ar-card__ico--blue"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
            <span class="ar-card__ttl">Education Details</span>
        </div>
        <div class="ar-card__body">
            <div class="ar-grid ar-grid--2">
                <div class="ar-datum"><div class="ar-datum__label">School (X Std)</div><div class="ar-datum__value"><?php echo $v($school_name_x ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">School (XII Std)</div><div class="ar-datum__value"><?php echo $v($school_name_xii ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Year of Passing (X Std)</div><div class="ar-datum__value"><?php echo $v($passing_year_x ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">X Marks (%)</div><div class="ar-datum__value"><?php echo ($tenth_marks_percentage !== null && $tenth_marks_percentage !== '') ? $tenth_marks_percentage . '%' : 'N/A'; ?></div></div>
            </div>
        </div>
    </div>

    <!-- ━━ HSC Examination ━━ -->
    <?php if (!$is_barch_review): ?>
    <div class="ar-card">
        <div class="ar-card__head">
            <div class="ar-card__ico ar-card__ico--pink"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
            <span class="ar-card__ttl">HSC Examination</span>
        </div>
        <div class="ar-card__body" style="padding:0;">
            <table class="ar-tbl">
                <thead><tr><th>Subject</th><th>Marks Obtained</th><th>Maximum Marks</th><th>Percentage</th></tr></thead>
                <tbody>
                    <tr><td>Maths (M)</td><td><?php echo $v($hsc['maths_marks'], '-'); ?></td><td><?php echo $v($hsc['total_maths'], '-'); ?></td><td><?php echo $hsc['maths_perc'] !== '' ? $hsc['maths_perc'] . '%' : '-'; ?></td></tr>
                    <tr><td>Physics (P)</td><td><?php echo $v($hsc['physics_marks'], '-'); ?></td><td><?php echo $v($hsc['total_physics'], '-'); ?></td><td><?php echo $hsc['physics_perc'] !== '' ? $hsc['physics_perc'] . '%' : '-'; ?></td></tr>
                    <tr><td>Chemistry (C)</td><td><?php echo $v($hsc['chemistry_marks'], '-'); ?></td><td><?php echo $v($hsc['total_chemistry'], '-'); ?></td><td><?php echo $hsc['chemistry_perc'] !== '' ? $hsc['chemistry_perc'] . '%' : '-'; ?></td></tr>
                    <tr class="ar-tbl__highlight"><td style="font-weight:700;">Average (P+C+M)/3</td><td colspan="3" class="ar-tbl__big"><?php echo $v($hsc['average_marks'], '-'); ?></td></tr>
                    <tr class="ar-tbl__highlight"><td style="font-weight:700;">Cut Off: (P+C)/2 + M</td><td colspan="3" class="ar-tbl__big" style="color:#dc2626;"><?php echo $v($hsc['cutoff_marks'], '-'); ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <!-- B.Arch: NATA + HSC combined -->
    <div class="ar-card">
        <div class="ar-card__head">
            <div class="ar-card__ico ar-card__ico--amber"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
            <span class="ar-card__ttl">B.Arch Score Details</span>
        </div>
        <div class="ar-card__body">
            <div class="ar-grid ar-grid--3">
                <div class="ar-datum">
                    <div class="ar-datum__label">NATA Score</div>
                    <div class="ar-datum__value ar-datum__value--highlight"><?php echo $v(isset($nata_details['nata_score']) ? $nata_details['nata_score'] : '', '-'); ?></div>
                </div>
                <div class="ar-datum">
                    <div class="ar-datum__label">HSC Total Score</div>
                    <div class="ar-datum__value ar-datum__value--highlight"><?php echo isset($hsc_marks_obtained) ? number_format((float)$hsc_marks_obtained, 0) : '0'; ?> / <?php echo isset($hsc_total_marks) ? number_format((float)$hsc_total_marks, 0) : '0'; ?></div>
                </div>
                <div class="ar-datum">
                    <div class="ar-datum__label">Cut Off: NATA + (Obtained/Total)&times;200</div>
                    <div class="ar-datum__value ar-datum__value--danger"><?php echo $v($hsc['cutoff_marks'], '-'); ?></div>
                </div>
            </div>
            <?php if (isset($nata_details) && is_array($nata_details) && !empty($nata_details['application_number'])): ?>
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--c-border);">
                <div class="ar-grid ar-grid--3">
                    <div class="ar-datum"><div class="ar-datum__label">NATA Application No</div><div class="ar-datum__value"><?php echo $v($nata_details['application_number']); ?></div></div>
                    <div class="ar-datum"><div class="ar-datum__label">NATA Year</div><div class="ar-datum__value"><?php echo $v($nata_details['nata_year']); ?></div></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- HSC subject-wise for B.Arch too -->
    <?php if ($hsc['total_maths'] !== '' || $hsc['total_physics'] !== '' || $hsc['total_chemistry'] !== ''): ?>
    <div class="ar-card">
        <div class="ar-card__head">
            <div class="ar-card__ico ar-card__ico--pink"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
            <span class="ar-card__ttl">HSC Examination</span>
        </div>
        <div class="ar-card__body" style="padding:0;">
            <table class="ar-tbl">
                <thead><tr><th>Subject</th><th>Marks Obtained</th><th>Maximum Marks</th><th>Percentage</th></tr></thead>
                <tbody>
                    <tr><td>Maths (M)</td><td><?php echo $v($hsc['maths_marks'], '-'); ?></td><td><?php echo $v($hsc['total_maths'], '-'); ?></td><td><?php echo $hsc['maths_perc'] !== '' ? $hsc['maths_perc'] . '%' : '-'; ?></td></tr>
                    <tr><td>Physics (P)</td><td><?php echo $v($hsc['physics_marks'], '-'); ?></td><td><?php echo $v($hsc['total_physics'], '-'); ?></td><td><?php echo $hsc['physics_perc'] !== '' ? $hsc['physics_perc'] . '%' : '-'; ?></td></tr>
                    <tr><td>Chemistry (C)</td><td><?php echo $v($hsc['chemistry_marks'], '-'); ?></td><td><?php echo $v($hsc['total_chemistry'], '-'); ?></td><td><?php echo $hsc['chemistry_perc'] !== '' ? $hsc['chemistry_perc'] . '%' : '-'; ?></td></tr>
                    <tr class="ar-tbl__highlight"><td style="font-weight:700;">Average (P+C+M)/3</td><td colspan="3" class="ar-tbl__big"><?php echo $v($hsc['average_marks'], '-'); ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    <?php endif; ?>

    <!-- ━━ Lateral Entry Details ━━ -->
    <?php if (isset($course_level) && $course_level == 'lateral' && isset($lateral_details) && $lateral_details): ?>
    <div class="ar-card">
        <div class="ar-card__head">
            <div class="ar-card__ico ar-card__ico--green"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
            <span class="ar-card__ttl">Lateral Entry Details</span>
        </div>
        <div class="ar-card__body">
            <?php
            $lat_course_id = $lateral_details['lateral_course_id'];
            $lat_course_name = isset($course_names[$lat_course_id]) ? $course_names[$lat_course_id] : $lat_course_id;
            ?>
            <div class="ar-grid ar-grid--3" style="margin-bottom:20px;">
                <div class="ar-datum"><div class="ar-datum__label">Course</div><div class="ar-datum__value"><?php echo $v($lat_course_name); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">School (X Std)</div><div class="ar-datum__value"><?php echo $v($school_name_x ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Year of Passing (X)</div><div class="ar-datum__value"><?php echo $v($passing_year_x ?? ''); ?></div></div>
            </div>
            <?php $pre_final = json_decode($lateral_details['pre_final_sem_subjects']); if ($pre_final): ?>
            <div style="margin-bottom:16px;font-size:13px;font-weight:700;color:var(--c-dark);">Pre-Final Semester</div>
            <table class="ar-tbl" style="margin-bottom:20px;"><thead><tr><th>Subject</th><th>Marks</th><th>Total</th></tr></thead><tbody>
            <?php foreach($pre_final as $s): ?><tr><td><?php echo $s->subject; ?></td><td><?php echo $s->marks; ?></td><td><?php echo $s->total_marks; ?></td></tr><?php endforeach; ?>
            </tbody></table>
            <?php endif; ?>
            <?php $final = json_decode($lateral_details['final_sem_subjects']); if ($final): ?>
            <div style="margin-bottom:16px;font-size:13px;font-weight:700;color:var(--c-dark);">Final Semester</div>
            <table class="ar-tbl"><thead><tr><th>Subject</th><th>Marks</th><th>Total</th></tr></thead><tbody>
            <?php foreach($final as $s): ?><tr><td><?php echo $s->subject; ?></td><td><?php echo $s->marks; ?></td><td><?php echo $s->total_marks; ?></td></tr><?php endforeach; ?>
            </tbody></table>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ━━ PG Details ━━ -->
    <?php if (isset($course_level) && $course_level == 'pg' && isset($pg_details) && $pg_details): ?>
    <div class="ar-card">
        <div class="ar-card__head">
            <div class="ar-card__ico ar-card__ico--purple"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 6 3 6 3s3 0 6-3v-5"/></svg></div>
            <span class="ar-card__ttl">Postgraduate Details</span>
        </div>
        <div class="ar-card__body">
            <div class="ar-grid ar-grid--3">
                <div class="ar-datum"><div class="ar-datum__label">Course</div><div class="ar-datum__value"><?php echo $v($pg_details['pg_course_id'] ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Qualifying Exam</div><div class="ar-datum__value"><?php echo $v($pg_details['qualifying_exam'] ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Branch</div><div class="ar-datum__value"><?php echo $v($pg_details['branch'] ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">Year of Passing</div><div class="ar-datum__value"><?php echo $v($pg_details['year_of_passing'] ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">College</div><div class="ar-datum__value"><?php echo $v($pg_details['college_name'] ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">University</div><div class="ar-datum__value"><?php echo $v($pg_details['university_name'] ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">TANCET/PGETA App No</div><div class="ar-datum__value"><?php echo $v($pg_details['tancet_pgeta_app_no'] ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">TANCET/PGETA Year</div><div class="ar-datum__value"><?php echo $v($pg_details['tancet_pgeta_year'] ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">TANCET/PGETA Score</div><div class="ar-datum__value"><?php echo $v($pg_details['tancet_pgeta_score'] ?? ''); ?></div></div>
                <div class="ar-datum"><div class="ar-datum__label">UG Degree Score (%)</div><div class="ar-datum__value"><?php echo $v($pg_details['ug_degree_score'] ?? ''); ?></div></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ━━ Scholarships ━━ -->
    <?php if (!empty($scholarships)): ?>
    <div class="ar-card">
        <div class="ar-card__head">
            <div class="ar-card__ico ar-card__ico--amber"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg></div>
            <span class="ar-card__ttl">Scholarships</span>
        </div>
        <div class="ar-card__body" style="padding:0;">
            <table class="ar-sch">
                <thead><tr><th>Scholarship</th><th style="width:120px;">Status</th><th style="width:160px;text-align:right;">Amount</th></tr></thead>
                <tbody>
                <?php foreach ($scholarships as $sch):
                    $effective = (isset($sch['override_amount']) && $sch['override_amount'] !== null) ? $sch['override_amount'] : $sch['default_amount'];
                    $is_not_eligible = ((float)($effective ?? 0) === 0.0 && !empty($sch['override_comment']));
                    $sc = ['approved'=>'#059669','rejected'=>'#dc2626','verified'=>'#2563eb','pending'=>'#d97706'];
                    $scolor = isset($sc[$sch['status']]) ? $sc[$sch['status']] : '#64748b';
                ?>
                <tr>
                    <td style="font-weight:500;"><?php echo htmlspecialchars($sch['scholarship_name']); ?></td>
                    <td><span style="color:<?php echo $scolor; ?>;font-weight:600;font-size:13px;"><?php echo ucfirst($sch['status']); ?></span></td>
                    <td style="text-align:right;">
                        <?php if ($is_not_eligible): ?>
                            <span style="color:#dc2626;font-weight:500;">Not Eligible</span>
                        <?php elseif ($effective !== null && $effective !== ''): ?>
                            <strong>&#8377; <?php echo number_format((float)$effective, 2); ?></strong>
                        <?php else: ?>
                            <span style="color:var(--c-subtle);">TBD</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- ━━ Declaration ━━ -->
    <div class="ar-card">
        <div class="ar-card__head">
            <div class="ar-card__ico ar-card__ico--red"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></div>
            <span class="ar-card__ttl">Declaration</span>
        </div>
        <div class="ar-card__body">
            <div class="ar-decl">
                <p style="margin-bottom:8px;font-weight:600;color:var(--c-dark);">SIGNATURE OF STUDENT ________________________</p>
                <ol>
                    <li>I hereby declare that I myself responsible for the timely payment of all fees payable to the College, as per the rules of the Management amended from time to time in respect of my ward Mr./Ms.__________________ during the period of his / her study at the Institution.</li>
                    <li>I assure you my ward will not discontinue the studies at the Institution under any circumstances after joining the course.</li>
                    <li>I hold myself responsible for the good behaviour of my ward and ensure he / she adheres to the rules and regulations of the college.</li>
                </ol>
                <p style="margin-bottom:24px;font-weight:600;color:var(--c-dark);">SIGNATURE OF THE PARENT ________________________</p>

                <div class="ar-decl__banner">Joint Declaration by the Applicant and Parent</div>

                <p style="text-align:justify;line-height:1.8;font-size:13px;color:var(--c-text);margin-bottom:0;">
                    The information furnished above is true and correct to the best of our knowledge. The original certificate will be produced at the time of admission or on demand. In case of any information furnished above, is found to be incorrect or false at later date on verification, we agree to forfeit the admission and shall not claim any compensation / refund.
                </p>

                <div class="ar-decl__sig-grid">
                    <div><div class="ar-decl__sig">Signature of the Student</div><div class="ar-decl__sig-line"></div></div>
                    <div><div class="ar-decl__sig">Signature of the Parent</div><div class="ar-decl__sig-line"></div></div>
                    <div><div class="ar-decl__sig">Date</div><div class="ar-decl__sig-line"></div></div>
                    <div><div class="ar-decl__sig">Place</div><div class="ar-decl__sig-line"></div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ━━ Login Info + Print ━━ -->
    <div class="no-print">
        <div class="ar-login">
            <div class="ar-login__title">First Time Login?</div>
            <p><strong>Login URL:</strong> <a href="<?php echo site_url('site/applicantlogin'); ?>"><?php echo site_url('site/applicantlogin'); ?></a></p>
            <p><strong>Default Password:</strong> Your Reference Number + @ApplicantPortal<?php echo date('Y'); ?></p>
            <p style="margin-bottom:0;"><strong>Example:</strong> If your reference is REF001, password is REF001@ApplicantPortal<?php echo date('Y'); ?></p>
        </div>
        <div style="text-align:center;padding-bottom:12px;">
            <button onclick="window.print()" class="ar-print-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print Application
            </button>
        </div>
    </div>

</div>
</div>

<?php if (empty($wrapped_layout)): ?>
</body>
</html>
<?php endif; ?>
