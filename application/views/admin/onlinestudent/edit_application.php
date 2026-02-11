<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat();

// Initialize variables to prevent undefined warnings
$ug_details = isset($ug_details) && !empty($ug_details) ? $ug_details : array();
$reference_details = isset($reference_details) && !empty($reference_details) ? $reference_details : array();
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Edit Application Form - <?php echo $student['reference_no']; ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/onlinestudent'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <form action="<?php echo site_url("admin/onlinestudent/edit_application/" . $id) ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
                            <?php echo $this->customlib->getCSRF(); ?>

                            <!-- Reference Number & Course Level (Read-Only) -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Application Ref No (Read-Only)</label>
                                        <input type="text" class="form-control" value="<?php echo $student['reference_no']; ?>" readonly style="background-color: #e9ecef;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Form Status (Read-Only)</label>
                                        <input type="text" class="form-control" value="<?php echo ($student['form_status'] == 1) ? 'Submitted' : 'Draft'; ?>" readonly style="background-color: #e9ecef;">
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Information Section -->
                            <hr>
                            <h4 style="margin-top: 20px; margin-bottom: 15px;"><strong>Personal Information</strong></h4>

                            <!-- Student Name & Gender -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="user_name">Full Name <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" id="user_name" name="user_name" placeholder="Enter full name" value="<?php echo set_value('user_name', $student['firstname']); ?>" required>
                                        <span class="text-danger"><?php echo form_error('user_name'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gender">Gender <span style="color:red;">*</span></label>
                                        <select class="form-control" id="gender" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="Male" <?php echo set_select('gender', 'Male', $student['gender'] == 'Male'); ?>>Male</option>
                                            <option value="Female" <?php echo set_select('gender', 'Female', $student['gender'] == 'Female'); ?>>Female</option>
                                            <option value="Other" <?php echo set_select('gender', 'Other', $student['gender'] == 'Other'); ?>>Other</option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('gender'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Date of Birth & Aadhar -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dob">Date of Birth <span style="color:red;">*</span></label>
                                        <input type="date" class="form-control" id="dob" name="dob" value="<?php echo set_value('dob', $student['dob']); ?>" required>
                                        <span class="text-danger"><?php echo form_error('dob'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="aadhaar">Aadhaar Number <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" id="aadhaar" name="aadhaar" placeholder="12-digit Aadhaar number" minlength="12" maxlength="12" value="<?php echo set_value('aadhaar', $student['adhar_no']); ?>" onKeyPress="return checkIt(event);" required>
                                        <span class="text-danger"><?php echo form_error('aadhaar'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information Section -->
                            <hr>
                            <h4 style="margin-top: 20px; margin-bottom: 15px;"><strong>Contact Information</strong></h4>

                            <!-- Email & Student Mobile -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="student_email">Email <span style="color:red;">*</span></label>
                                        <input type="email" class="form-control" id="student_email" name="student_email" placeholder="Enter email address" value="<?php echo set_value('student_email', $student['email']); ?>" required>
                                        <span class="text-danger"><?php echo form_error('student_email'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="student_mobile">Student Mobile <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" id="student_mobile" name="student_mobile" placeholder="10-digit mobile number" minlength="10" maxlength="10" value="<?php echo set_value('student_mobile', $student['mobileno']); ?>" onchange="validateMobile(this)" onKeyPress="return checkIt(event);" required>
                                        <span class="text-danger"><?php echo form_error('student_mobile'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Father's Details Section -->
                            <hr>
                            <h4 style="margin-top: 20px; margin-bottom: 15px;"><strong>Father's Information</strong></h4>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="father_name">Father's Name <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" id="father_name" name="father_name" placeholder="Enter father's name" value="<?php echo set_value('father_name', $student['father_name']); ?>" onkeydown="return allowAlphabets(event);" required>
                                        <span class="text-danger"><?php echo form_error('father_name'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="father_mobile">Father's Mobile <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" id="father_mobile" name="father_mobile" placeholder="10-digit mobile number" minlength="10" maxlength="10" value="<?php echo set_value('father_mobile', $student['father_phone']); ?>" onchange="validateMobile(this)" onKeyPress="return checkIt(event);" required>
                                        <span class="text-danger"><?php echo form_error('father_mobile'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="father_occupation">Father's Occupation</label>
                                        <input type="text" class="form-control" id="father_occupation" name="father_occupation" placeholder="Enter occupation" value="<?php echo set_value('father_occupation', $student['father_occupation']); ?>" onkeydown="return allowAlphabets(event);">
                                    </div>
                                </div>
                            </div>

                            <!-- Mother's Details Section -->
                            <hr>
                            <h4 style="margin-top: 20px; margin-bottom: 15px;"><strong>Mother's Information</strong></h4>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="mother_name">Mother's Name <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" id="mother_name" name="mother_name" placeholder="Enter mother's name" value="<?php echo set_value('mother_name', $student['mother_name']); ?>" onkeydown="return allowAlphabets(event);" required>
                                        <span class="text-danger"><?php echo form_error('mother_name'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="mother_mobile">Mother's Mobile <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" id="mother_mobile" name="mother_mobile" placeholder="10-digit mobile number" minlength="10" maxlength="10" value="<?php echo set_value('mother_mobile', $student['mother_phone']); ?>" onchange="validateMobile(this)" onKeyPress="return checkIt(event);" required>
                                        <span class="text-danger"><?php echo form_error('mother_mobile'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="mother_occupation">Mother's Occupation</label>
                                        <input type="text" class="form-control" id="mother_occupation" name="mother_occupation" placeholder="Enter occupation" value="<?php echo set_value('mother_occupation', $student['mother_occupation']); ?>" onkeydown="return allowAlphabets(event);">
                                    </div>
                                </div>
                            </div>

                            <!-- Address Section -->
                            <hr>
                            <h4 style="margin-top: 20px; margin-bottom: 15px;"><strong>Address Information</strong></h4>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="current_address">Current Address</label>
                                        <textarea class="form-control" id="current_address" name="current_address" rows="3" placeholder="Enter current address"><?php echo set_value('current_address', $student['current_address']); ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="permanent_address">Permanent Address</label>
                                        <textarea class="form-control" id="permanent_address" name="permanent_address" rows="3" placeholder="Enter permanent address"><?php echo set_value('permanent_address', $student['permanent_address']); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>State</label>
                                        <input type="text" class="form-control" name="state" placeholder="Enter state" value="<?php echo set_value('state', $student['state']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>City</label>
                                        <input type="text" class="form-control" name="city" placeholder="Enter city" value="<?php echo set_value('city', $student['city']); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- References Section -->
                            <hr>
                            <h4 style="margin-top: 20px; margin-bottom: 15px;"><strong>References Details (Optional)</strong></h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="referral_name">Referrer Name</label>
                                        <input type="text" class="form-control" id="referral_name" name="referral_name" placeholder="Enter referrer name" value="<?php echo set_value('referral_name', (isset($reference_details['referrer_name']) ? $reference_details['referrer_name'] : '')); ?>" onkeydown="return allowAlphabets(event);">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="relationship">Relationship</label>
                                        <input type="text" class="form-control" id="relationship" name="relationship" placeholder="Enter relationship" value="<?php echo set_value('relationship', (isset($reference_details['relationship']) ? $reference_details['relationship'] : '')); ?>" onkeydown="return allowAlphabets(event);">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="phone_no">Phone No</label>
                                        <input type="text" class="form-control" id="phone_no" name="phone_no" placeholder="10-digit number" minlength="10" maxlength="10" value="<?php echo set_value('phone_no', (isset($reference_details['phone_no']) ? $reference_details['phone_no'] : '')); ?>" onKeyPress="return checkIt(event);">
                                    </div>
                                </div>
                            </div>

                            <!-- HSC Details Section -->
                            <hr>
                            <h4 style="margin-top: 20px; margin-bottom: 15px;"><strong>HSC Examination Details</strong></h4>
                            <div class="table-responsive">
                                <table class="table table-bordered text-center">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Subject</th>
                                            <th>Total Marks</th>
                                            <th>Marks Obtained</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Maths (M)</strong></td>
                                            <td><input type="number" step="1" class="form-control text-center" name="total_maths" value="<?php echo set_value('total_maths', $student['total_maths']); ?>" oncalcchange="calculatePercentage('maths')"></td>
                                            <td><input type="number" step="1" class="form-control text-center" name="maths_marks" value="<?php echo set_value('maths_marks', $student['maths_marks']); ?>" oncalcchange="calculatePercentage('maths')"></td>
                                            <td><input type="number" step="0.01" class="form-control text-center" name="maths_perc" value="<?php echo set_value('maths_perc', $student['maths_perc']); ?>" readonly></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Physics (P)</strong></td>
                                            <td><input type="number" step="1" class="form-control text-center" name="total_physics" value="<?php echo set_value('total_physics', $student['total_physics']); ?>" oncalcchange="calculatePercentage('physics')"></td>
                                            <td><input type="number" step="1" class="form-control text-center" name="physics_marks" value="<?php echo set_value('physics_marks', $student['physics_marks']); ?>" oncalcchange="calculatePercentage('physics')"></td>
                                            <td><input type="number" step="0.01" class="form-control text-center" name="physics_perc" value="<?php echo set_value('physics_perc', $student['physics_perc']); ?>" readonly></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Chemistry (C)</strong></td>
                                            <td><input type="number" step="1" class="form-control text-center" name="total_chemistry" value="<?php echo set_value('total_chemistry', $student['total_chemistry']); ?>" oncalcchange="calculatePercentage('chemistry')"></td>
                                            <td><input type="number" step="1" class="form-control text-center" name="chemistry_marks" value="<?php echo set_value('chemistry_marks', $student['chemistry_marks']); ?>" oncalcchange="calculatePercentage('chemistry')"></td>
                                            <td><input type="number" step="0.01" class="form-control text-center" name="chemistry_perc" value="<?php echo set_value('chemistry_perc', $student['chemistry_perc']); ?>" readonly></td>
                                        </tr>
                                        <tr class="bg-light">
                                            <td><strong>Average: (P+C+M)/3</strong></td>
                                            <td colspan="3"><input type="number" step="0.01" class="form-control text-center" name="average_marks" value="<?php echo set_value('average_marks', $student['average_marks']); ?>" readonly></td>
                                        </tr>
                                        <tr class="bg-light">
                                            <td><strong>Cut Off: (P+C)/2 + M</strong></td>
                                            <td colspan="3"><input type="number" step="0.01" class="form-control text-center" name="cutoff_marks" value="<?php echo set_value('cutoff_marks', $student['cutoff_marks']); ?>" readonly></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Additional Fields -->
                            <hr>
                            <h4 style="margin-top: 20px; margin-bottom: 15px;"><strong>Additional Information</strong></h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="school_name">Name of School (X Std)</label>
                                        <input type="text" class="form-control" id="school_name" name="school_name" placeholder="Enter school name" value="<?php echo set_value('school_name', (isset($ug_details['school_name']) ? $ug_details['school_name'] : '')); ?>" onkeydown="return allowAlphabets(event);">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tenth_passing">Year of Passing (X Std)</label>
                                        <input type="text" class="form-control" id="tenth_passing" name="tenth_passing" placeholder="YYYY" minlength="4" maxlength="4" value="<?php echo set_value('tenth_passing', (isset($ug_details['tenth_passing']) ? $ug_details['tenth_passing'] : '')); ?>" onKeyPress="return checkIt(event);">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Edit and Save
                            </button>
                            <a href="<?php echo site_url('admin/onlinestudent'); ?>" class="btn btn-default">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
                <!-- /.box -->
            </div>
            <!--/.col (left) -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script>
function checkIt(e) {
    if (e.charCode >= 48 && e.charCode <= 57)
        return true;
    else
        return false;
}

function validateMobile(el) {
    if (el.value.length !== 10 && el.value.length > 0) {
        $(el).closest('.form-group').find('.text-danger').text('Mobile number must be 10 digits');
        el.focus();
    } else {
        $(el).closest('.form-group').find('.text-danger').text('');
    }
}

function allowAlphabets(event) {
    var key = event.keyCode;
    if ((key >= 65 && key <= 90) || (key >= 97 && key <= 122) || key == 32 || key == 8 || key == 0) {
        return true;
    } else {
        return false;
    }
}

function calculatePercentage(subject) {
    var totalId = 'total_' + subject;
    var marksId = subject + '_marks';
    var percId = subject + '_perc';
    
    var total = parseFloat($('#' + totalId).val()) || 0;
    var marks = parseFloat($('#' + marksId).val()) || 0;
    
    if (total > 0) {
        var percentage = (marks * 100) / total;
        $('#' + percId).val(percentage.toFixed(2));
    } else {
        $('#' + percId).val('0.00');
    }
    
    // Recalculate average and cutoff
    calculateAverageAndCutoff();
}

function calculateAverageAndCutoff() {
    var mathsPerc = parseFloat($('#maths_perc').val()) || 0;
    var physicsPerc = parseFloat($('#physics_perc').val()) || 0;
    var chemistryPerc = parseFloat($('#chemistry_perc').val()) || 0;
    
    var average = (mathsPerc + physicsPerc + chemistryPerc) / 3;
    $('#average_marks').val(average.toFixed(2));
    
    var cutoff = ((physicsPerc + chemistryPerc) / 2) + mathsPerc;
    $('#cutoff_marks').val(cutoff.toFixed(2));
}

$(document).ready(function() {
    // Attach change events to HSC fields
    $('[name="total_maths"], [name="maths_marks"]').on('change', function() {
        calculatePercentage('maths');
    });
    $('[name="total_physics"], [name="physics_marks"]').on('change', function() {
        calculatePercentage('physics');
    });
    $('[name="total_chemistry"], [name="chemistry_marks"]').on('change', function() {
        calculatePercentage('chemistry');
    });
});
</script>

