<?php
$r = $record; // may be null
$yn = function($v) { return $v ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>'; };
$fullName = trim(($student->firstname ?? '') . ' ' . ($student->middlename ?? '') . ' ' . ($student->lastname ?? ''));
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-heartbeat"></i> Health Form — <?php echo htmlspecialchars($fullName); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('admin/studenthealthform'); ?>"><i class="fa fa-arrow-left"></i> Back to List</a></li>
        </ol>
    </section>
    <section class="content">
        <?php if ($this->session->flashdata('msg')) echo $this->session->flashdata('msg'); ?>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-user"></i> Student Information</h3>
                        <div class="box-tools pull-right">
                            <?php if ($can_edit): ?>
                            <a href="<?php echo site_url('admin/studenthealthform/edit/' . $student->id); ?>" class="btn btn-sm btn-default"><i class="fa fa-pencil"></i> Edit</a>
                            <?php endif; ?>
                            <?php if ($r && $r->form_token): ?>
                            <a href="<?php echo site_url('studenthealthform/pdf/' . $r->form_token); ?>" target="_blank" class="btn btn-sm btn-danger"><i class="fa fa-file-pdf-o"></i> Download PDF</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3"><strong>Name:</strong> <?php echo htmlspecialchars($fullName); ?></div>
                            <div class="col-md-3"><strong>Admission No:</strong> <?php echo htmlspecialchars($student->admission_no ?? '-'); ?></div>
                            <div class="col-md-3"><strong>Class:</strong> <?php echo htmlspecialchars(($student->class ?? '') . ' ' . ($student->section ?? '')); ?></div>
                            <div class="col-md-3"><strong>Blood Group:</strong> <?php echo htmlspecialchars($student->blood_group ?? '-'); ?></div>
                        </div>
                    </div>
                </div>

                <?php if (!$r): ?>
                <div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> No health form submitted yet for this student.
                    <?php if ($can_edit): ?>
                    <a href="<?php echo site_url('admin/studenthealthform/edit/' . $student->id); ?>" class="btn btn-sm btn-primary" style="margin-left:10px"><i class="fa fa-pencil"></i> Fill Form</a>
                    <?php endif; ?>
                </div>
                <?php else: ?>

                <!-- Emergency Contact -->
                <div class="box box-default">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-phone"></i> Emergency Contact</h3></div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3"><strong>Name:</strong> <?php echo htmlspecialchars($r->emergency_contact_name ?? '-'); ?></div>
                            <div class="col-md-3"><strong>Relation:</strong> <?php echo htmlspecialchars($r->emergency_contact_relation ?? '-'); ?></div>
                            <div class="col-md-3"><strong>Mobile:</strong> <?php echo htmlspecialchars($r->emergency_contact_mobile ?? '-'); ?></div>
                            <div class="col-md-3"><strong>Alternate:</strong> <?php echo htmlspecialchars($r->emergency_contact_alt_mobile ?? '-'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- General Health -->
                <div class="box box-default">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-stethoscope"></i> General Health</h3></div>
                    <div class="box-body">
                        <table class="table table-condensed table-bordered" style="max-width:600px">
                            <tr><td>Wears Spectacles</td><td><?php echo $yn($r->wears_spectacles); ?></td></tr>
                            <tr><td>Vision Difficulty</td><td><?php echo $yn($r->vision_difficulty); ?></td></tr>
                            <tr><td>Hearing Difficulty</td><td><?php echo $yn($r->hearing_difficulty); ?></td></tr>
                            <tr><td>Speech Difficulty</td><td><?php echo $yn($r->speech_difficulty); ?></td></tr>
                            <tr><td>Requires Special Assistance</td><td><?php echo $yn($r->special_assistance); ?></td></tr>
                        </table>
                        <?php if ($r->special_assistance && $r->special_assistance_details): ?>
                        <p><strong>Details:</strong> <?php echo nl2br(htmlspecialchars($r->special_assistance_details)); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Allergies -->
                <div class="box box-default">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Allergies</h3></div>
                    <div class="box-body">
                        <?php
                        $allergies = [];
                        if ($r->allergy_food)       $allergies[] = 'Food';
                        if ($r->allergy_medication)  $allergies[] = 'Medication';
                        if ($r->allergy_insect)      $allergies[] = 'Insect Bites/Stings';
                        if ($r->allergy_dust)        $allergies[] = 'Dust/Pollen';
                        if ($r->allergy_other)       $allergies[] = 'Other';
                        if ($r->allergy_none)        $allergies[] = 'No Known Allergies';
                        ?>
                        <?php if ($allergies): ?>
                            <?php foreach($allergies as $a): ?><span class="label label-warning" style="margin-right:4px"><?php echo $a; ?></span><?php endforeach; ?>
                        <?php else: ?><em>None selected</em><?php endif; ?>
                        <?php if ($r->allergy_details): ?><p style="margin-top:8px"><strong>Details:</strong> <?php echo nl2br(htmlspecialchars($r->allergy_details)); ?></p><?php endif; ?>
                    </div>
                </div>

                <!-- Medical History -->
                <div class="box box-default">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-medkit"></i> Medical History</h3></div>
                    <div class="box-body">
                        <?php
                        $conds = [];
                        if ($r->med_asthma)              $conds[] = 'Asthma';
                        if ($r->med_diabetes)            $conds[] = 'Diabetes';
                        if ($r->med_epilepsy)            $conds[] = 'Epilepsy/Seizures';
                        if ($r->med_heart)               $conds[] = 'Heart Condition';
                        if ($r->med_kidney)              $conds[] = 'Kidney Disorder';
                        if ($r->med_thyroid)             $conds[] = 'Thyroid Disorder';
                        if ($r->med_physical_disability) $conds[] = 'Physical Disability';
                        if ($r->med_learning_difficulty) $conds[] = 'Learning Difficulty';
                        if ($r->med_vision_impairment)   $conds[] = 'Vision Impairment';
                        if ($r->med_hearing_impairment)  $conds[] = 'Hearing Impairment';
                        if ($r->med_other)               $conds[] = 'Other';
                        ?>
                        <?php if ($conds): ?>
                            <?php foreach($conds as $c): ?><span class="label label-danger" style="margin-right:4px"><?php echo $c; ?></span><?php endforeach; ?>
                        <?php else: ?><em>None</em><?php endif; ?>
                        <?php if ($r->med_details): ?><p style="margin-top:8px"><strong>Details:</strong> <?php echo nl2br(htmlspecialchars($r->med_details)); ?></p><?php endif; ?>
                    </div>
                </div>

                <!-- Surgery & Medications -->
                <div class="box box-default">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-plus-square"></i> Surgery &amp; Medications</h3></div>
                    <div class="box-body">
                        <p><strong>Past Surgery/Hospitalization:</strong> <?php echo $yn($r->surgery_history); ?></p>
                        <?php if ($r->surgery_history && $r->surgery_details): ?><p><?php echo nl2br(htmlspecialchars($r->surgery_details)); ?></p><?php endif; ?>
                        <p><strong>Currently on Medication:</strong> <?php echo $yn(isset($r->current_medications) && $r->current_medications != ''); ?></p>
                        <?php if ($r->current_medications): ?><p><?php echo nl2br(htmlspecialchars($r->current_medications)); ?></p><?php endif; ?>
                    </div>
                </div>

                <!-- Immunization & PE -->
                <div class="box box-default">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-shield"></i> Immunization &amp; Physical Education</h3></div>
                    <div class="box-body">
                        <p><strong>Vaccinations Up to Date:</strong>
                            <?php
                            $vc = $r->vaccinations_uptodate ?? '';
                            if ($vc === 'yes') echo '<span class="label label-success">Yes</span>';
                            elseif ($vc === 'no') echo '<span class="label label-danger">No</span>';
                            elseif ($vc === 'not_sure') echo '<span class="label label-warning">Not Sure</span>';
                            else echo '-';
                            ?>
                        </p>
                        <?php if ($r->vaccination_remarks): ?><p><strong>Remarks:</strong> <?php echo nl2br(htmlspecialchars($r->vaccination_remarks)); ?></p><?php endif; ?>
                        <p><strong>Fit for PE/Sports:</strong> <?php echo $yn($r->pe_fit ?? 1); ?></p>
                        <?php if (!($r->pe_fit ?? 1) && $r->pe_restrictions): ?><p><strong>Restrictions:</strong> <?php echo nl2br(htmlspecialchars($r->pe_restrictions)); ?></p><?php endif; ?>
                    </div>
                </div>

                <!-- Special Instructions -->
                <?php if ($r->special_health_instructions): ?>
                <div class="box box-default">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-text-o"></i> Special Health Instructions</h3></div>
                    <div class="box-body"><?php echo nl2br(htmlspecialchars($r->special_health_instructions)); ?></div>
                </div>
                <?php endif; ?>

                <!-- Declaration -->
                <div class="box box-default">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-check-circle"></i> Declaration</h3></div>
                    <div class="box-body">
                        <p><strong>Declared by:</strong> <?php echo htmlspecialchars($r->declaration_name ?? '-'); ?></p>
                        <p><strong>Date:</strong> <?php echo $r->declaration_date ? date('d M Y', strtotime($r->declaration_date)) : '-'; ?></p>
                    </div>
                </div>

                <?php endif; // end $r check ?>
            </div>
        </div>
    </section>
</div>
