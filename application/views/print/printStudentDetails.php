<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #222; margin: 0; padding: 0; line-height: 1.4; }
.header-img { width: 100%; height: auto; display: block; }
h2.title { font-size: 13px; text-align: center; margin: 6px 0 4px; padding: 3px 0; border-bottom: 2px solid #333; text-transform: uppercase; letter-spacing: 1px; }
.sec { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #fff; background: #333; padding: 2px 6px; margin: 5px 0 3px; letter-spacing: 0.5px; }
table.d { width: 100%; border-collapse: collapse; }
table.d th { text-align: left; font-weight: 600; color: #555; padding: 1px 4px 1px 0; vertical-align: top; font-size: 9px; white-space: nowrap; }
table.d td { padding: 1px 4px; vertical-align: top; font-size: 10px; color: #111; }
.two-col { width: 100%; }
.two-col > tbody > tr > td { width: 50%; vertical-align: top; padding-right: 8px; }
.photo-cell { text-align: center; vertical-align: top; }
.photo-cell img { width: 55px; height: 55px; border-radius: 3px; border: 1px solid #ccc; }
.qr-cell img { width: 50px; height: 50px; }
.ft { font-size: 8px; color: #888; text-align: center; margin-top: 6px; padding-top: 3px; border-top: 1px solid #ddd; }
</style>
</head>
<body>
<?php
$fmt = function($d) {
    if (empty($d) || $d == '0000-00-00' || $d == 'NULL') return '';
    return date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($d));
};
$v = function($key) use ($student) {
    return isset($student[$key]) && $student[$key] !== '' && $student[$key] !== null && $student[$key] !== 'NULL' ? trim($student[$key]) : '';
};
$lastname = ($sch_setting->lastname) ? $v('lastname') : '';
$name = $this->customlib->getFullName($v('firstname'), $v('middlename'), $lastname, $sch_setting->middlename, $sch_setting->lastname ?: '');
$img = !empty($student["image"]) ? $this->media_storage->getImageURL($student["image"]) : $this->media_storage->getImageURL($student['gender']=='Female' ? "uploads/student_images/default_female.jpg" : "uploads/student_images/default_male.jpg");
$cat = '';
if (!empty($category_list)) { foreach ($category_list as $c) { if ($student['category_id']==$c['id']) { $cat=$c['category']; break; } } }
?>
<div style="border-bottom:1px solid #ddd;"><img class="header-img" src="<?php echo $this->media_storage->getImageURL('/uploads/print_headerfooter/general_purpose/'.$this->setting_model->get_general_purpose_header()); ?>"></div>

<h2 class="title">Student Profile</h2>

<table style="width:100%; margin-bottom:4px;">
<tr>
<td class="photo-cell" style="width:60px;"><img src="<?php echo $img; ?>"></td>
<td style="padding-left:6px; vertical-align:top;">
<div style="font-size:15px; font-weight:bold; margin-bottom:1px;"><?php echo $name; ?></div>
<table class="d">
<tr><th>Admission No</th><td><strong><?php echo $v('admission_no'); ?></strong></td><th>Class / Section</th><td><?php echo $v('class').' / '.$v('section'); ?></td></tr>
<?php if ($v('roll_no')) { ?><tr><th>Roll No</th><td><?php echo $v('roll_no'); ?></td><?php if ($v('register_no')) { ?><th>Register No</th><td><?php echo $v('register_no'); ?></td><?php } else { ?><td colspan="2"></td><?php } ?></tr><?php } ?>
<?php if ($v('register_no') && !$v('roll_no')) { ?><tr><th>Register No</th><td><?php echo $v('register_no'); ?></td><td colspan="2"></td></tr><?php } ?>
</table>
</td>
<td class="qr-cell" style="width:55px; text-align:right; vertical-align:top;">
<?php if ($sch_setting->student_barcode == 1 && file_exists(FCPATH.'uploads/student_id_card/qrcode/'.$student['id'].'.png')) { ?>
<img src="<?php echo $this->media_storage->getImageURL('uploads/student_id_card/qrcode/'.$student['id'].'.png'); ?>">
<?php } ?>
</td>
</tr>
</table>

<table class="two-col"><tr>
<!-- LEFT -->
<td>
<div class="sec">Personal Details</div>
<table class="d">
<tr><th>Gender</th><td><?php echo $this->lang->line(strtolower((string)$student['gender'])); ?></td></tr>
<?php if ($fmt($student['dob'])) { ?><tr><th>Date of Birth</th><td><?php echo $fmt($student['dob']); ?></td></tr><?php } ?>
<?php if ($fmt($student['admission_date'])) { ?><tr><th>Admission Date</th><td><?php echo $fmt($student['admission_date']); ?></td></tr><?php } ?>
<?php if ($cat) { ?><tr><th>Category</th><td><?php echo $cat; ?></td></tr><?php } ?>
<?php if ($v('religion')) { ?><tr><th>Religion</th><td><?php echo $v('religion'); ?></td></tr><?php } ?>
<?php if ($v('cast')) { ?><tr><th>Caste</th><td><?php echo $v('cast'); ?></td></tr><?php } ?>
<?php if ($v('mobileno')) { ?><tr><th>Mobile</th><td><?php echo $v('mobileno'); ?></td></tr><?php } ?>
<?php if ($v('email')) { ?><tr><th>Email</th><td><?php echo $v('email'); ?></td></tr><?php } ?>
<?php if ($v('blood_group')) { ?><tr><th>Blood Group</th><td><?php echo $v('blood_group'); ?></td></tr><?php } ?>
<?php if ($v('rte')) { ?><tr><th>RTE</th><td><?php echo $v('rte'); ?></td></tr><?php } ?>
<?php if ($v('height')) { ?><tr><th>Height</th><td><?php echo $v('height'); ?></td></tr><?php } ?>
<?php if ($v('weight')) { ?><tr><th>Weight</th><td><?php echo $v('weight'); ?></td></tr><?php } ?>
<?php if ($v('house_name')) { ?><tr><th>House</th><td><?php echo $v('house_name'); ?></td></tr><?php } ?>
</table>

<?php if ($v('regulation_id') || $v('emis_num') || $v('hsc_reg_no') || $v('ug_reg_no') || $v('abc_id') || $v('medium') || $v('allotment_no') || $v('consortium_no') || $v('application_no') || $v('migration_cert_num')) { ?>
<div class="sec">Academic IDs</div>
<table class="d">
<?php if ($v('regulation_id')) { ?><tr><th>Regulation</th><td><?php echo $v('regulation_id'); ?></td></tr><?php } ?>
<?php if ($v('emis_num')) { ?><tr><th>EMIS Number</th><td><?php echo $v('emis_num'); ?></td></tr><?php } ?>
<?php if ($v('hsc_reg_no')) { ?><tr><th>HSC Reg No</th><td><?php echo $v('hsc_reg_no'); ?></td></tr><?php } ?>
<?php if ($v('ug_reg_no')) { ?><tr><th>UG Reg No</th><td><?php echo $v('ug_reg_no'); ?></td></tr><?php } ?>
<?php if ($v('abc_id')) { ?><tr><th>ABC ID</th><td><?php echo $v('abc_id'); ?></td></tr><?php } ?>
<?php if ($v('medium')) { ?><tr><th>Medium</th><td><?php echo $v('medium'); ?></td></tr><?php } ?>
<?php if ($v('allotment_no')) { ?><tr><th>Allotment No</th><td><?php echo $v('allotment_no'); ?></td></tr><?php } ?>
<?php if ($v('consortium_no')) { ?><tr><th>Consortium No</th><td><?php echo $v('consortium_no'); ?></td></tr><?php } ?>
<?php if ($v('application_no')) { ?><tr><th>Application No</th><td><?php echo $v('application_no'); ?></td></tr><?php } ?>
<?php if ($v('migration_cert_num')) { ?><tr><th>Migration Cert</th><td><?php echo $v('migration_cert_num'); ?></td></tr><?php } ?>
</table>
<?php } ?>

<?php if ($v('adhar_no') || $v('samagra_id') || $v('father_adhar_no') || $v('mother_adhar_no')) { ?>
<div class="sec">Identity</div>
<table class="d">
<?php if ($v('adhar_no')) { ?><tr><th>Aadhaar No</th><td><?php echo $v('adhar_no'); ?></td></tr><?php } ?>
<?php if ($v('samagra_id')) { ?><tr><th>Local ID</th><td><?php echo $v('samagra_id'); ?></td></tr><?php } ?>
<?php if ($v('father_adhar_no')) { ?><tr><th>Father Aadhaar</th><td><?php echo $v('father_adhar_no'); ?></td></tr><?php } ?>
<?php if ($v('mother_adhar_no')) { ?><tr><th>Mother Aadhaar</th><td><?php echo $v('mother_adhar_no'); ?></td></tr><?php } ?>
</table>
<?php } ?>

<?php if ($v('current_address') || $v('permanent_address')) { ?>
<div class="sec">Address</div>
<table class="d">
<?php if ($v('current_address')) { ?><tr><th>Current</th><td><?php echo $v('current_address'); ?></td></tr><?php } ?>
<?php if ($v('permanent_address')) { ?><tr><th>Permanent</th><td><?php echo $v('permanent_address'); ?></td></tr><?php } ?>
</table>
<?php } ?>
</td>

<!-- RIGHT -->
<td>
<?php if ($v('father_name') || $v('mother_name') || $v('guardian_name')) { ?>
<div class="sec">Parent / Guardian</div>
<table class="d">
<?php if ($v('father_name')) { ?><tr><th>Father</th><td><?php echo $v('father_name'); ?></td></tr><?php } ?>
<?php if ($v('father_phone')) { ?><tr><th>Father Phone</th><td><?php echo $v('father_phone'); ?></td></tr><?php } ?>
<?php if ($v('father_occupation')) { ?><tr><th>Father Occupation</th><td><?php echo $v('father_occupation'); ?></td></tr><?php } ?>
<?php if ($v('mother_name')) { ?><tr><th>Mother</th><td><?php echo $v('mother_name'); ?></td></tr><?php } ?>
<?php if ($v('mother_phone')) { ?><tr><th>Mother Phone</th><td><?php echo $v('mother_phone'); ?></td></tr><?php } ?>
<?php if ($v('mother_occupation')) { ?><tr><th>Mother Occupation</th><td><?php echo $v('mother_occupation'); ?></td></tr><?php } ?>
<?php if ($v('guardian_name')) { ?><tr><th>Guardian</th><td><?php echo $v('guardian_name'); ?></td></tr><?php } ?>
<?php if ($v('guardian_is')) { ?><tr><th>Guardian Is</th><td><?php echo ucfirst($v('guardian_is')); ?></td></tr><?php } ?>
<?php if ($v('guardian_relation')) { ?><tr><th>Relation</th><td><?php echo $v('guardian_relation'); ?></td></tr><?php } ?>
<?php if ($v('guardian_phone')) { ?><tr><th>Guardian Phone</th><td><?php echo $v('guardian_phone'); ?></td></tr><?php } ?>
<?php if ($v('guardian_email')) { ?><tr><th>Guardian Email</th><td><?php echo $v('guardian_email'); ?></td></tr><?php } ?>
<?php if ($v('guardian_occupation')) { ?><tr><th>Guardian Occupation</th><td><?php echo $v('guardian_occupation'); ?></td></tr><?php } ?>
<?php if ($v('guardian_address')) { ?><tr><th>Guardian Address</th><td><?php echo $v('guardian_address'); ?></td></tr><?php } ?>
</table>
<?php } ?>

<?php if (!empty($student['route_title'])) { ?>
<div class="sec">Transport</div>
<table class="d">
<tr><th>Route</th><td><?php echo $v('route_title'); ?></td></tr>
<?php if ($v('pickup_point_name')) { ?><tr><th>Pickup Point</th><td><?php echo $v('pickup_point_name'); ?></td></tr><?php } ?>
<?php if ($v('vehicle_no')) { ?><tr><th>Vehicle</th><td><?php echo $v('vehicle_no'); ?></td></tr><?php } ?>
<?php if ($v('driver_name')) { ?><tr><th>Driver</th><td><?php echo $v('driver_name'); ?> <?php if ($v('driver_contact')) echo '('.$v('driver_contact').')'; ?></td></tr><?php } ?>
</table>
<?php } ?>

<?php if (!empty($student['hostel_room_id']) && $student['hostel_room_id'] != 0) { ?>
<div class="sec">Hostel</div>
<table class="d">
<?php if ($v('hostel_name')) { ?><tr><th>Hostel</th><td><?php echo $v('hostel_name'); ?></td></tr><?php } ?>
<?php if ($v('room_no')) { ?><tr><th>Room No</th><td><?php echo $v('room_no'); ?> <?php if ($v('room_type')) echo '('.$v('room_type').')'; ?></td></tr><?php } ?>
</table>
<?php } ?>

<?php if ($v('bank_account_no') || $v('bank_name') || $v('ifsc_code')) { ?>
<div class="sec">Bank Details</div>
<table class="d">
<?php if ($v('bank_account_no')) { ?><tr><th>Account No</th><td><?php echo $v('bank_account_no'); ?></td></tr><?php } ?>
<?php if ($v('bank_name')) { ?><tr><th>Bank</th><td><?php echo $v('bank_name'); ?></td></tr><?php } ?>
<?php if ($v('ifsc_code')) { ?><tr><th>IFSC</th><td><?php echo $v('ifsc_code'); ?></td></tr><?php } ?>
</table>
<?php } ?>

<?php if ($v('previous_school')) { ?>
<div class="sec">Previous School</div>
<table class="d"><tr><td><?php echo $v('previous_school'); ?></td></tr></table>
<?php } ?>

<?php if ($v('note')) { ?>
<div class="sec">Note</div>
<table class="d"><tr><td><?php echo $v('note'); ?></td></tr></table>
<?php } ?>

<div class="sec">Session Info</div>
<table class="d">
<tr><th>Session</th><td><?php echo $v('session'); ?></td></tr>
<tr><th>Status</th><td><?php echo ucfirst($v('is_active')) == 'Yes' ? 'Active' : $v('is_active'); ?></td></tr>
<?php if ($v('created_at')) { ?><tr><th>Registered</th><td><?php echo date('d-m-Y', strtotime($student['created_at'])); ?></td></tr><?php } ?>
</table>
</td>
</tr></table>

<div class="ft"><?php echo $this->setting_model->get_general_purpose_footer(); ?></div>
</body>
</html>
