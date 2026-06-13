<?php
$r = (array)$record;
$rv = function($k, $default='') use ($r) { return htmlspecialchars($r[$k] ?? $default); };
$rb = function($k) use ($r) { return !empty($r[$k]) ? 1 : 0; };
$fullName = trim(($student->firstname ?? '') . ' ' . ($student->middlename ?? '') . ' ' . ($student->lastname ?? ''));
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-pencil"></i> Edit Health Form — <?php echo htmlspecialchars($fullName); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('admin/studenthealthform'); ?>"><i class="fa fa-arrow-left"></i> Back to List</a></li>
            <li><a href="<?php echo site_url('admin/studenthealthform/view/' . $student->id); ?>">View</a></li>
        </ol>
    </section>
    <section class="content">
        <form method="post" action="<?php echo site_url('admin/studenthealthform/update/' . $student->id); ?>">
            <?php echo $this->customlib->getCSRF(); ?>

            <!-- Student Info (read-only) -->
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-user"></i> Student</h3></div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3"><strong>Name:</strong> <?php echo htmlspecialchars($fullName); ?></div>
                        <div class="col-md-3"><strong>Adm No:</strong> <?php echo htmlspecialchars($student->admission_no ?? ''); ?></div>
                        <div class="col-md-3"><strong>Class:</strong> <?php echo htmlspecialchars(($student->class ?? '') . ' ' . ($student->section ?? '')); ?></div>
                        <div class="col-md-3"><strong>Blood Group:</strong> <?php echo htmlspecialchars($student->blood_group ?? '-'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="box box-default">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-phone"></i> Emergency Contact</h3></div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3 form-group"><label>Contact Name <span class="text-danger">*</span></label><input type="text" name="emergency_contact_name" class="form-control" value="<?php echo $rv('emergency_contact_name'); ?>"></div>
                        <div class="col-md-3 form-group"><label>Relationship</label><input type="text" name="emergency_contact_relation" class="form-control" value="<?php echo $rv('emergency_contact_relation'); ?>"></div>
                        <div class="col-md-3 form-group"><label>Mobile <span class="text-danger">*</span></label><input type="tel" name="emergency_contact_mobile" class="form-control" maxlength="10" value="<?php echo $rv('emergency_contact_mobile'); ?>"></div>
                        <div class="col-md-3 form-group"><label>Alternate Mobile</label><input type="tel" name="emergency_contact_alt_mobile" class="form-control" maxlength="10" value="<?php echo $rv('emergency_contact_alt_mobile'); ?>"></div>
                    </div>
                </div>
            </div>

            <!-- General Health -->
            <div class="box box-default">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-stethoscope"></i> General Health</h3></div>
                <div class="box-body">
                    <?php
                    $toggles = [
                        'wears_spectacles'  => 'Wears Spectacles',
                        'vision_difficulty' => 'Vision Difficulty',
                        'hearing_difficulty'=> 'Hearing Difficulty',
                        'speech_difficulty' => 'Speech Difficulty',
                        'special_assistance'=> 'Requires Special Assistance',
                    ];
                    foreach ($toggles as $key => $label):
                        $val = $rb($key);
                    ?>
                    <div class="form-group">
                        <label><?php echo $label; ?></label>
                        <div>
                            <label class="radio-inline"><input type="radio" name="<?php echo $key; ?>" value="1" <?php if($val) echo 'checked'; ?>> Yes</label>
                            <label class="radio-inline"><input type="radio" name="<?php echo $key; ?>" value="0" <?php if(!$val) echo 'checked'; ?>> No</label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="form-group"><label>Special Assistance Details</label><textarea name="special_assistance_details" class="form-control" rows="2"><?php echo $rv('special_assistance_details'); ?></textarea></div>
                </div>
            </div>

            <!-- Allergies -->
            <div class="box box-default">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Allergies</h3></div>
                <div class="box-body">
                    <?php
                    $allergyOptions = ['allergy_food'=>'Food','allergy_medication'=>'Medication','allergy_insect'=>'Insect Bites/Stings','allergy_dust'=>'Dust/Pollen','allergy_other'=>'Other','allergy_none'=>'No Known Allergies'];
                    foreach ($allergyOptions as $k => $lbl):
                    ?>
                    <label class="checkbox-inline"><input type="checkbox" name="<?php echo $k; ?>" value="1" <?php if($rb($k)) echo 'checked'; ?>> <?php echo $lbl; ?></label>
                    <?php endforeach; ?>
                    <div class="form-group" style="margin-top:10px"><label>Allergy Details</label><textarea name="allergy_details" class="form-control" rows="2"><?php echo $rv('allergy_details'); ?></textarea></div>
                </div>
            </div>

            <!-- Medical History -->
            <div class="box box-default">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-medkit"></i> Medical History</h3></div>
                <div class="box-body">
                    <?php
                    $medOptions = ['med_asthma'=>'Asthma','med_diabetes'=>'Diabetes','med_epilepsy'=>'Epilepsy/Seizures','med_heart'=>'Heart Condition','med_kidney'=>'Kidney Disorder','med_thyroid'=>'Thyroid Disorder','med_physical_disability'=>'Physical Disability','med_learning_difficulty'=>'Learning Difficulty','med_vision_impairment'=>'Vision Impairment','med_hearing_impairment'=>'Hearing Impairment','med_other'=>'Other'];
                    foreach ($medOptions as $k => $lbl):
                    ?>
                    <label class="checkbox-inline" style="margin-bottom:6px"><input type="checkbox" name="<?php echo $k; ?>" value="1" <?php if($rb($k)) echo 'checked'; ?>> <?php echo $lbl; ?></label>
                    <?php endforeach; ?>
                    <div class="form-group" style="margin-top:10px"><label>Details</label><textarea name="med_details" class="form-control" rows="2"><?php echo $rv('med_details'); ?></textarea></div>
                </div>
            </div>

            <!-- Surgery & Medications -->
            <div class="box box-default">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-plus-square"></i> Surgery &amp; Medications</h3></div>
                <div class="box-body">
                    <div class="form-group">
                        <label>Past Surgery / Major Hospitalization</label>
                        <div>
                            <label class="radio-inline"><input type="radio" name="surgery_history" value="1" <?php if($rb('surgery_history')) echo 'checked'; ?>> Yes</label>
                            <label class="radio-inline"><input type="radio" name="surgery_history" value="0" <?php if(!$rb('surgery_history')) echo 'checked'; ?>> No</label>
                        </div>
                    </div>
                    <div class="form-group"><label>Surgery Details</label><textarea name="surgery_details" class="form-control" rows="2"><?php echo $rv('surgery_details'); ?></textarea></div>
                    <div class="form-group"><label>Current Medications</label><textarea name="current_medications" class="form-control" rows="2" placeholder="Name, dosage, frequency..."><?php echo $rv('current_medications'); ?></textarea></div>
                </div>
            </div>

            <!-- Immunization & PE -->
            <div class="box box-default">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-shield"></i> Immunization &amp; Physical Education</h3></div>
                <div class="box-body">
                    <div class="form-group">
                        <label>Vaccinations Up to Date</label>
                        <div>
                            <?php $vc = $rv('vaccinations_uptodate'); ?>
                            <label class="radio-inline"><input type="radio" name="vaccinations_uptodate" value="yes"      <?php if($vc==='yes') echo 'checked'; ?>> Yes</label>
                            <label class="radio-inline"><input type="radio" name="vaccinations_uptodate" value="no"       <?php if($vc==='no') echo 'checked'; ?>> No</label>
                            <label class="radio-inline"><input type="radio" name="vaccinations_uptodate" value="not_sure" <?php if($vc==='not_sure') echo 'checked'; ?>> Not Sure</label>
                        </div>
                    </div>
                    <div class="form-group"><label>Vaccination Remarks</label><textarea name="vaccination_remarks" class="form-control" rows="2"><?php echo $rv('vaccination_remarks'); ?></textarea></div>
                    <div class="form-group">
                        <label>Fit for PE / Sports</label>
                        <div>
                            <label class="radio-inline"><input type="radio" name="pe_fit" value="1" <?php if($rb('pe_fit')) echo 'checked'; ?>> Yes</label>
                            <label class="radio-inline"><input type="radio" name="pe_fit" value="0" <?php if(!$rb('pe_fit')) echo 'checked'; ?>> No</label>
                        </div>
                    </div>
                    <div class="form-group"><label>PE Restrictions</label><textarea name="pe_restrictions" class="form-control" rows="2"><?php echo $rv('pe_restrictions'); ?></textarea></div>
                </div>
            </div>

            <!-- Special Instructions -->
            <div class="box box-default">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-text-o"></i> Special Health Instructions</h3></div>
                <div class="box-body">
                    <div class="form-group"><textarea name="special_health_instructions" class="form-control" rows="4" placeholder="Dietary restrictions, recurring conditions, behavioural notes..."><?php echo $rv('special_health_instructions'); ?></textarea></div>
                </div>
            </div>

            <!-- Sibling -->
            <div class="box box-default">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-users"></i> Sibling</h3></div>
                <div class="box-body">
                    <div class="form-group">
                        <label>Has sibling in this school</label>
                        <div>
                            <label class="radio-inline"><input type="radio" name="has_sibling" value="1" <?php if($rb('has_sibling')) echo 'checked'; ?>> Yes</label>
                            <label class="radio-inline"><input type="radio" name="has_sibling" value="0" <?php if(!$rb('has_sibling')) echo 'checked'; ?>> No</label>
                        </div>
                    </div>
                    <div class="form-group"><label>Sibling Details</label><textarea name="sibling_details" class="form-control" rows="2"><?php echo $rv('sibling_details'); ?></textarea></div>
                </div>
            </div>

            <!-- Declaration -->
            <div class="box box-default">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-check-circle"></i> Declaration</h3></div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6 form-group"><label>Declared by <span class="text-danger">*</span></label><input type="text" name="declaration_name" class="form-control" value="<?php echo $rv('declaration_name'); ?>"></div>
                        <div class="col-md-3 form-group"><label>Date</label><input type="date" name="declaration_date" class="form-control" value="<?php echo $rv('declaration_date', date('Y-m-d')); ?>"></div>
                    </div>
                </div>
            </div>

            <div class="box box-footer">
                <a href="<?php echo site_url('admin/studenthealthform/view/' . $student->id); ?>" class="btn btn-default">Cancel</a>
                <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Save Changes</button>
            </div>

        </form>
    </section>
</div>
