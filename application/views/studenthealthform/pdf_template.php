<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: dejavusans, Arial, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 0; }
.header-img { width: 100%; max-height: 120px; object-fit: contain; display: block; margin-bottom: 10px; }
h1 { text-align: center; font-size: 14px; font-weight: 700; margin: 0 0 2px; text-transform: uppercase; }
h2 { text-align: center; font-size: 12px; font-weight: 700; margin: 0 0 2px; text-transform: uppercase; }
.subtitle { text-align: center; font-size: 11px; color: #555; margin-bottom: 10px; }
hr { border: none; border-top: 2px solid #b71c1c; margin: 8px 0; }
.section { margin-bottom: 12px; }
.section-title { background: #b71c1c; color: #fff; font-weight: 700; font-size: 11px; padding: 4px 8px; margin-bottom: 6px; }
table.info { width: 100%; border-collapse: collapse; }
table.info td { padding: 4px 6px; vertical-align: top; }
table.info td.label { font-weight: 700; color: #555; width: 30%; white-space: nowrap; }
table.info td.val { border-bottom: 1px solid #ddd; }
table.info td.val-wide { border-bottom: 1px solid #ddd; }
.yn { display: inline-block; padding: 1px 8px; border-radius: 3px; font-weight: 700; font-size: 10px; }
.yn-yes { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
.yn-no  { background: #fdecea; color: #c62828; border: 1px solid #ffcdd2; }
.yn-ns  { background: #fff8e1; color: #f57f17; border: 1px solid #ffe082; }
.tag    { display: inline-block; background: #fdecea; color: #c62828; border: 1px solid #ffcdd2; border-radius: 3px; padding: 1px 7px; margin: 2px 2px 2px 0; font-size: 10px; font-weight: 700; }
.tag-g  { background: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }
table.siblings { width: 100%; border-collapse: collapse; font-size: 10px; }
table.siblings th { background: #f5f5f5; padding: 4px 6px; text-align: left; border: 1px solid #ddd; }
table.siblings td { padding: 4px 6px; border: 1px solid #ddd; }
.decl-box { border: 1px solid #ddd; border-radius: 4px; padding: 8px 10px; font-size: 10px; color: #555; line-height: 1.6; margin-bottom: 10px; }
.sig-row { display: flex; gap: 20px; margin-top: 16px; }
.sig-cell { flex: 1; border-top: 1px solid #999; padding-top: 4px; font-size: 10px; color: #555; }
.school-use { border: 1px solid #999; padding: 8px 10px; margin-top: 12px; }
.school-use-title { font-weight: 700; font-size: 11px; margin-bottom: 8px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
.sig-line { border-bottom: 1px solid #999; height: 20px; margin-bottom: 4px; }
.footer-note { text-align: center; font-size: 9px; color: #999; margin-top: 16px; }
</style>
</head>
<body>

<?php if (!empty($header_img)): ?>
<img class="header-img" src="<?php echo $header_img; ?>" alt="<?php echo htmlspecialchars($school_name); ?>">
<?php else: ?>
<h1><?php echo htmlspecialchars($school_name); ?></h1>
<?php endif; ?>

<h2>Student Health &amp; Emergency Information Card</h2>
<div class="subtitle">Academic Year: <?php echo htmlspecialchars($student->session_name ?? date('Y') . '-' . (date('Y')+1)); ?></div>
<hr>

<!-- 1. STUDENT INFO -->
<div class="section">
    <div class="section-title">1. Student Information</div>
    <table class="info">
        <tr>
            <td class="label">Student Name</td>
            <td class="val"><?php echo htmlspecialchars(trim(($student->firstname??'').' '.($student->middlename??'').' '.($student->lastname??''))); ?></td>
            <td class="label">Admission No.</td>
            <td class="val"><?php echo htmlspecialchars($student->admission_no ?? ''); ?></td>
        </tr>
        <tr>
            <td class="label">EMIS No.</td>
            <td class="val"><?php echo htmlspecialchars($student->emis_num ?? ''); ?></td>
            <td class="label">Class &amp; Section</td>
            <td class="val"><?php echo htmlspecialchars(trim(($student->class??'').' '.($student->section??''))); ?></td>
        </tr>
        <tr>
            <td class="label">Date of Birth</td>
            <td class="val"><?php echo $student->dob ? date('d/m/Y', strtotime($student->dob)) : ''; ?></td>
            <td class="label">Gender</td>
            <td class="val"><?php echo htmlspecialchars($student->gender ?? ''); ?></td>
        </tr>
        <tr>
            <td class="label">Blood Group</td>
            <td class="val"><?php echo htmlspecialchars($student->blood_group ?? ''); ?></td>
            <td class="label">Admission Date</td>
            <td class="val"><?php echo $student->admission_date ? date('d/m/Y', strtotime($student->admission_date)) : ''; ?></td>
        </tr>
        <tr>
            <td class="label">Previous School</td>
            <td class="val-wide" colspan="3"><?php echo htmlspecialchars($student->previous_school ?? '—'); ?></td>
        </tr>
    </table>
</div>

<!-- 2. SIBLING DETAILS -->
<div class="section">
    <div class="section-title">2. Sibling Details</div>
    <table class="info">
        <tr>
            <td class="label">Has Sibling(s) in School</td>
            <td class="val-wide" colspan="3">
                <?php if ($record->has_sibling): ?>
                    <span class="yn yn-yes">YES</span>
                    <?php if ($record->sibling_details): ?>
                        &nbsp; <?php echo htmlspecialchars($record->sibling_details); ?>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="yn yn-no">NO</span>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>

<!-- 3. PARENT/GUARDIAN -->
<div class="section">
    <div class="section-title">3. Parent / Guardian Details</div>
    <table class="info">
        <tr>
            <td class="label">Father's Name</td>
            <td class="val"><?php echo htmlspecialchars($student->father_name ?? ''); ?></td>
            <td class="label">Occupation</td>
            <td class="val"><?php echo htmlspecialchars($student->father_occupation ?? ''); ?></td>
        </tr>
        <tr>
            <td class="label">Father's Mobile</td>
            <td class="val-wide" colspan="3"><?php echo htmlspecialchars($student->father_phone ?? ''); ?></td>
        </tr>
        <tr>
            <td class="label">Mother's Name</td>
            <td class="val"><?php echo htmlspecialchars($student->mother_name ?? ''); ?></td>
            <td class="label">Occupation</td>
            <td class="val"><?php echo htmlspecialchars($student->mother_occupation ?? ''); ?></td>
        </tr>
        <tr>
            <td class="label">Mother's Mobile</td>
            <td class="val-wide" colspan="3"><?php echo htmlspecialchars($student->mother_phone ?? ''); ?></td>
        </tr>
        <tr>
            <td class="label">Residential Address</td>
            <td class="val-wide" colspan="3"><?php echo htmlspecialchars($student->current_address ?? ''); ?></td>
        </tr>
    </table>
</div>

<!-- 4. EMERGENCY CONTACT -->
<div class="section">
    <div class="section-title">4. Emergency Contact Information</div>
    <table class="info">
        <tr>
            <td class="label">Contact Person</td>
            <td class="val"><?php echo htmlspecialchars($record->emergency_contact_name ?? ''); ?></td>
            <td class="label">Relationship</td>
            <td class="val"><?php echo htmlspecialchars($record->emergency_contact_relation ?? ''); ?></td>
        </tr>
        <tr>
            <td class="label">Mobile No.</td>
            <td class="val"><?php echo htmlspecialchars($record->emergency_contact_mobile ?? ''); ?></td>
            <td class="label">Alternate Mobile</td>
            <td class="val"><?php echo htmlspecialchars($record->emergency_contact_alt_mobile ?? ''); ?></td>
        </tr>
    </table>
</div>

<!-- 5. GENERAL HEALTH -->
<div class="section">
    <div class="section-title">5. General Health Information</div>
    <?php
    function yn($val) {
        if ($val) return '<span class="yn yn-yes">YES</span>';
        return '<span class="yn yn-no">NO</span>';
    }
    ?>
    <table class="info">
        <tr>
            <td class="label">Wears Spectacles</td><td class="val"><?php echo yn($record->wears_spectacles); ?></td>
            <td class="label">Vision Difficulties</td><td class="val"><?php echo yn($record->vision_difficulty); ?></td>
        </tr>
        <tr>
            <td class="label">Hearing Difficulties</td><td class="val"><?php echo yn($record->hearing_difficulty); ?></td>
            <td class="label">Speech Difficulties</td><td class="val"><?php echo yn($record->speech_difficulty); ?></td>
        </tr>
        <tr>
            <td class="label">Requires Special Assistance</td>
            <td class="val-wide" colspan="3"><?php echo yn($record->special_assistance); ?>
                <?php if ($record->special_assistance && $record->special_assistance_details): ?>
                    &nbsp; <?php echo htmlspecialchars($record->special_assistance_details); ?>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>

<!-- 6. ALLERGIES -->
<div class="section">
    <div class="section-title">6. Allergies</div>
    <table class="info">
        <tr><td style="padding:4px 6px;">
            <?php
            $allergies = [];
            if ($record->allergy_food)       $allergies[] = 'Food';
            if ($record->allergy_medication)  $allergies[] = 'Medication';
            if ($record->allergy_insect)      $allergies[] = 'Insect Bites/Stings';
            if ($record->allergy_dust)        $allergies[] = 'Dust/Pollen';
            if ($record->allergy_other)       $allergies[] = 'Other';
            if ($record->allergy_none)        $allergies[] = 'No Known Allergies';
            if (empty($allergies)) $allergies[] = 'Not specified';
            foreach ($allergies as $a) {
                $cls = ($a === 'No Known Allergies') ? 'tag tag-g' : 'tag';
                echo '<span class="'.$cls.'">'.htmlspecialchars($a).'</span> ';
            }
            ?>
        </td></tr>
        <?php if ($record->allergy_details): ?>
        <tr><td class="label" style="padding:4px 6px;">Details</td></tr>
        <tr><td style="padding:2px 6px 4px;"><?php echo htmlspecialchars($record->allergy_details); ?></td></tr>
        <?php endif; ?>
    </table>
</div>

<!-- 7. MEDICAL HISTORY -->
<div class="section">
    <div class="section-title">7. Medical History</div>
    <table class="info">
        <tr><td style="padding:4px 6px;">
            <?php
            $conditions = [];
            if ($record->med_asthma)             $conditions[] = 'Asthma';
            if ($record->med_diabetes)            $conditions[] = 'Diabetes';
            if ($record->med_epilepsy)            $conditions[] = 'Epilepsy/Seizures';
            if ($record->med_heart)               $conditions[] = 'Heart Condition';
            if ($record->med_kidney)              $conditions[] = 'Kidney Disorder';
            if ($record->med_thyroid)             $conditions[] = 'Thyroid Disorder';
            if ($record->med_physical_disability) $conditions[] = 'Physical Disability';
            if ($record->med_learning_difficulty) $conditions[] = 'Learning Difficulty';
            if ($record->med_vision_impairment)   $conditions[] = 'Vision Impairment';
            if ($record->med_hearing_impairment)  $conditions[] = 'Hearing Impairment';
            if ($record->med_other)               $conditions[] = 'Other';
            if (empty($conditions)) echo '<span class="tag tag-g">None Reported</span>';
            foreach ($conditions as $c) echo '<span class="tag">'.htmlspecialchars($c).'</span> ';
            ?>
        </td></tr>
        <?php if ($record->med_details): ?>
        <tr><td style="padding:2px 6px 4px;"><?php echo htmlspecialchars($record->med_details); ?></td></tr>
        <?php endif; ?>
    </table>
</div>

<!-- 8. SURGERY & MEDICATIONS -->
<div class="section">
    <div class="section-title">8. Surgery / Hospitalization &amp; Current Medications</div>
    <table class="info">
        <tr>
            <td class="label">Surgery/Hospitalization History</td>
            <td class="val-wide" colspan="3"><?php echo yn($record->surgery_history); ?>
                <?php if ($record->surgery_history && $record->surgery_details): ?>
                    &nbsp; <?php echo htmlspecialchars($record->surgery_details); ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td class="label">Current Medications</td>
            <td class="val-wide" colspan="3"><?php echo $record->current_medications ? htmlspecialchars($record->current_medications) : '—'; ?></td>
        </tr>
    </table>
</div>

<!-- 9. IMMUNIZATION & PE -->
<div class="section">
    <div class="section-title">9. Immunization &amp; Physical Education</div>
    <table class="info">
        <tr>
            <td class="label">Vaccinations Up to Date</td>
            <td class="val">
                <?php
                if ($record->vaccinations_uptodate === 'yes') echo '<span class="yn yn-yes">YES</span>';
                elseif ($record->vaccinations_uptodate === 'no') echo '<span class="yn yn-no">NO</span>';
                elseif ($record->vaccinations_uptodate === 'not_sure') echo '<span class="yn yn-ns">NOT SURE</span>';
                else echo '—';
                ?>
            </td>
            <td class="label">Remarks</td>
            <td class="val"><?php echo htmlspecialchars($record->vaccination_remarks ?? ''); ?></td>
        </tr>
        <tr>
            <td class="label">Fit for PE &amp; Sports</td>
            <td class="val-wide" colspan="3"><?php echo yn($record->pe_fit); ?>
                <?php if (!$record->pe_fit && $record->pe_restrictions): ?>
                    &nbsp; Restrictions: <?php echo htmlspecialchars($record->pe_restrictions); ?>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>

<!-- 10. SPECIAL INSTRUCTIONS -->
<?php if ($record->special_health_instructions): ?>
<div class="section">
    <div class="section-title">10. Special Health Care Instructions</div>
    <table class="info">
        <tr><td style="padding:4px 6px;"><?php echo htmlspecialchars($record->special_health_instructions); ?></td></tr>
    </table>
</div>
<?php endif; ?>

<!-- 11. DECLARATION -->
<div class="section">
    <div class="section-title">11. Parent / Guardian Declaration</div>
    <div class="decl-box">
        I hereby declare that the information provided in this form is true and complete to the best of my knowledge. I undertake to inform the school of any changes in my child's health condition or emergency contact details. I authorize the school to administer first aid and seek emergency medical treatment for my child whenever necessary and when immediate contact with the parent/guardian is not possible.
    </div>
    <table class="info">
        <tr>
            <td class="label">Parent/Guardian Name</td>
            <td class="val"><?php echo htmlspecialchars($record->declaration_name ?? ''); ?></td>
            <td class="label">Date</td>
            <td class="val"><?php echo $record->declaration_date ? date('d/m/Y', strtotime($record->declaration_date)) : ''; ?></td>
        </tr>
        <tr>
            <td class="label">Signature</td>
            <td class="val-wide" colspan="3" style="height:28px;"></td>
        </tr>
    </table>
</div>

<!-- FOR SCHOOL USE -->
<div class="school-use">
    <div class="school-use-title">FOR SCHOOL USE ONLY</div>
    <table style="width:100%"><tr>
        <td style="width:50%;padding-right:20px;">
            <div class="sig-line"></div>
            <div style="font-size:10px;color:#555;">Class Teacher's Signature</div>
        </td>
        <td style="width:50%;">
            <div class="sig-line"></div>
            <div style="font-size:10px;color:#555;">Principal's Signature &amp; School Seal</div>
        </td>
    </tr></table>
</div>

<div class="footer-note">
    Submitted on <?php echo $record->submitted_at ? date('d M Y, h:i A', strtotime($record->submitted_at)) : date('d M Y'); ?> &bull; <?php echo htmlspecialchars($school_name); ?>
</div>

</body>
</html>
