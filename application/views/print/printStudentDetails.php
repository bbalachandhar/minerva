<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 0; }
.header-img { width: 100%; height: auto; display: block; }
h2.title { font-size: 14px; text-align: center; margin: 8px 0 6px; padding: 4px 0; border-bottom: 2px solid #333; text-transform: uppercase; letter-spacing: 1px; }
.section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #fff; background: #333; padding: 3px 8px; margin: 6px 0 4px; letter-spacing: 0.5px; }
table.info { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
table.info th { text-align: left; font-weight: 600; color: #555; padding: 2px 6px 2px 0; width: 35%; vertical-align: top; font-size: 10px; white-space: nowrap; }
table.info td { padding: 2px 4px; vertical-align: top; font-size: 11px; color: #111; }
.two-col { width: 100%; }
.two-col td { width: 50%; vertical-align: top; padding-right: 10px; }
.photo-cell { text-align: center; vertical-align: top; }
.photo-cell img { width: 60px; height: 60px; border-radius: 4px; border: 1px solid #ddd; }
.qr-cell img { width: 55px; height: 55px; }
.footer-text { font-size: 9px; color: #888; text-align: center; margin-top: 8px; padding-top: 4px; border-top: 1px solid #ddd; }
</style>
</head>
<body>
<?php
// Helper to format dates
$fmt_date = function($d) {
    if (empty($d) || $d == '0000-00-00') return '';
    return date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($d));
};

// Student name
$lastname = ($sch_setting->lastname) ? $student['lastname'] : '';
$student_name = $this->customlib->getFullName($student['firstname'], $student['middlename'], $lastname, $sch_setting->middlename, $sch_setting->lastname ?: '');

// Photo
if (!empty($student["image"])) {
    $image_url = $this->media_storage->getImageURL($student["image"]);
} else {
    $image_url = $this->media_storage->getImageURL($student['gender'] == 'Female' ? "uploads/student_images/default_female.jpg" : "uploads/student_images/default_male.jpg");
}

// Category name
$category_name = '';
if (!empty($category_list)) {
    foreach ($category_list as $cat) {
        if ($student['category_id'] == $cat['id']) { $category_name = $cat['category']; break; }
    }
}
?>

<!-- Header Image -->
<div style="border-bottom:1px solid #ddd;">
    <img class="header-img" src="<?php echo $this->media_storage->getImageURL('/uploads/print_headerfooter/general_purpose/'.$this->setting_model->get_general_purpose_header()); ?>">
</div>

<h2 class="title">Student Profile</h2>

<!-- Top: Photo + Basic Info + QR -->
<table style="width:100%; margin-bottom:6px;">
    <tr>
        <td class="photo-cell" style="width:70px;">
            <img src="<?php echo $image_url; ?>">
        </td>
        <td style="padding-left:8px; vertical-align:top;">
            <div style="font-size:16px; font-weight:bold; margin-bottom:2px;"><?php echo $student_name; ?></div>
            <table class="info" style="margin:0;">
                <tr><th>Admission No</th><td><strong><?php echo $student['admission_no']; ?></strong></td></tr>
                <tr><th>Class / Section</th><td><?php echo $student['class'] . ' / ' . $student['section']; ?></td></tr>
                <?php if ($sch_setting->roll_no && !empty($student['roll_no'])) { ?>
                <tr><th>Roll No</th><td><?php echo $student['roll_no']; ?></td></tr>
                <?php } ?>
            </table>
        </td>
        <td class="qr-cell" style="width:65px; text-align:right; vertical-align:top;">
            <?php if ($sch_setting->student_barcode == 1) { ?>
                <img src="<?php echo $this->media_storage->getImageURL('uploads/student_id_card/qrcode/' . $student['id'] . '.png'); ?>">
            <?php } ?>
        </td>
    </tr>
</table>

<!-- Two-column layout -->
<table class="two-col">
<tr>
<!-- LEFT COLUMN -->
<td>
    <div class="section-title">Personal Details</div>
    <table class="info">
        <tr><th>Gender</th><td><?php echo $this->lang->line(strtolower((string)$student['gender'])); ?></td></tr>
        <tr><th>Date of Birth</th><td><?php echo $fmt_date($student['dob']); ?></td></tr>
        <?php if ($sch_setting->admission_date && !empty($student['admission_date'])) { ?>
        <tr><th>Admission Date</th><td><?php echo $fmt_date($student['admission_date']); ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->category && $category_name) { ?>
        <tr><th>Category</th><td><?php echo $category_name; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->religion && !empty($student['religion'])) { ?>
        <tr><th>Religion</th><td><?php echo $student['religion']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->cast && !empty($student['cast'])) { ?>
        <tr><th>Caste</th><td><?php echo $student['cast']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->mobile_no && !empty($student['mobileno'])) { ?>
        <tr><th>Mobile</th><td><?php echo $student['mobileno']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->student_email && !empty($student['email'])) { ?>
        <tr><th>Email</th><td><?php echo $student['email']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->is_blood_group && !empty($student['blood_group'])) { ?>
        <tr><th>Blood Group</th><td><?php echo $student['blood_group']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->national_identification_no && !empty($student['adhar_no'])) { ?>
        <tr><th>Aadhaar No</th><td><?php echo $student['adhar_no']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->rte && !empty($student['rte'])) { ?>
        <tr><th>RTE</th><td><?php echo $student['rte']; ?></td></tr>
        <?php } ?>
    </table>

    <?php if ($sch_setting->current_address || $sch_setting->permanent_address) { ?>
    <div class="section-title">Address</div>
    <table class="info">
        <?php if ($sch_setting->current_address && !empty($student['current_address'])) { ?>
        <tr><th>Current</th><td><?php echo $student['current_address']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->permanent_address && !empty($student['permanent_address'])) { ?>
        <tr><th>Permanent</th><td><?php echo $student['permanent_address']; ?></td></tr>
        <?php } ?>
    </table>
    <?php } ?>

    <?php if ($sch_setting->bank_account_no || $sch_setting->bank_name) { ?>
    <div class="section-title">Bank Details</div>
    <table class="info">
        <?php if ($sch_setting->bank_account_no && !empty($student['bank_account_no'])) { ?>
        <tr><th>Account No</th><td><?php echo $student['bank_account_no']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->bank_name && !empty($student['bank_name'])) { ?>
        <tr><th>Bank</th><td><?php echo $student['bank_name']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->ifsc_code && !empty($student['ifsc_code'])) { ?>
        <tr><th>IFSC</th><td><?php echo $student['ifsc_code']; ?></td></tr>
        <?php } ?>
    </table>
    <?php } ?>
</td>

<!-- RIGHT COLUMN -->
<td>
    <div class="section-title">Parent / Guardian</div>
    <table class="info">
        <?php if ($sch_setting->father_name && !empty($student['father_name'])) { ?>
        <tr><th>Father</th><td><?php echo $student['father_name']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->father_phone && !empty($student['father_phone'])) { ?>
        <tr><th>Father Phone</th><td><?php echo $student['father_phone']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->father_occupation && !empty($student['father_occupation'])) { ?>
        <tr><th>Father Occupation</th><td><?php echo $student['father_occupation']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->mother_name && !empty($student['mother_name'])) { ?>
        <tr><th>Mother</th><td><?php echo $student['mother_name']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->mother_phone && !empty($student['mother_phone'])) { ?>
        <tr><th>Mother Phone</th><td><?php echo $student['mother_phone']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->mother_occupation && !empty($student['mother_occupation'])) { ?>
        <tr><th>Mother Occupation</th><td><?php echo $student['mother_occupation']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->guardian_name && !empty($student['guardian_name'])) { ?>
        <tr><th>Guardian</th><td><?php echo $student['guardian_name']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->guardian_relation && !empty($student['guardian_relation'])) { ?>
        <tr><th>Relation</th><td><?php echo $student['guardian_relation']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->guardian_phone && !empty($student['guardian_phone'])) { ?>
        <tr><th>Guardian Phone</th><td><?php echo $student['guardian_phone']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->guardian_email && !empty($student['guardian_email'])) { ?>
        <tr><th>Guardian Email</th><td><?php echo $student['guardian_email']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->guardian_occupation && !empty($student['guardian_occupation'])) { ?>
        <tr><th>Guardian Occupation</th><td><?php echo $student['guardian_occupation']; ?></td></tr>
        <?php } ?>
        <?php if ($sch_setting->guardian_address && !empty($student['guardian_address'])) { ?>
        <tr><th>Guardian Address</th><td><?php echo $student['guardian_address']; ?></td></tr>
        <?php } ?>
    </table>

    <?php if ($sch_setting->route_list && !empty($student['route_title'])) { ?>
    <div class="section-title">Transport</div>
    <table class="info">
        <tr><th>Route</th><td><?php echo $student['route_title']; ?></td></tr>
        <?php if (!empty($student['pickup_point_name'])) { ?>
        <tr><th>Pickup Point</th><td><?php echo $student['pickup_point_name']; ?></td></tr>
        <?php } ?>
        <?php if (!empty($student['vehicle_no'])) { ?>
        <tr><th>Vehicle</th><td><?php echo $student['vehicle_no']; ?></td></tr>
        <?php } ?>
    </table>
    <?php } ?>

    <?php if ($sch_setting->previous_school_details && !empty($student['previous_school'])) { ?>
    <div class="section-title">Previous School</div>
    <table class="info">
        <tr><td colspan="2" style="padding:2px 0;"><?php echo $student['previous_school']; ?></td></tr>
    </table>
    <?php } ?>

    <?php if ($sch_setting->student_note && !empty($student['note'])) { ?>
    <div class="section-title">Note</div>
    <table class="info">
        <tr><td colspan="2" style="padding:2px 0;"><?php echo $student['note']; ?></td></tr>
    </table>
    <?php } ?>
</td>
</tr>
</table>

<!-- Footer -->
<div class="footer-text"><?php echo $this->setting_model->get_general_purpose_footer(); ?></div>

</body>
</html>
