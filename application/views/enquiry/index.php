<h3>Admission Enquiry</h3>
<hr>
<?php
if (validation_errors()) {
    echo '<div class="alert alert-danger">' . validation_errors() . '</div>';
}
?>
<form action="<?php echo site_url('enquiry/index'); ?>" method="post">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="name">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="<?php echo set_value('name'); ?>" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="contact">Phone <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" name="contact" id="contact" value="<?php echo set_value('contact'); ?>" maxlength="10" pattern="[0-9]{10}" required>
                <small class="text-muted">Enter 10-digit mobile number</small>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" name="email" value="<?php echo set_value('email'); ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="source">Source <span class="text-danger">*</span></label>
                <select name="source" class="form-control" required>
                    <option value="">Select Source</option>
                    <?php foreach ($sourcelist as $source): ?>
                        <option value="<?php echo $source['source']; ?>" <?php 
                            $default = !empty($prefill_source) ? strtolower($source['source']) === strtolower($prefill_source) : strtolower($source['source']) === 'website';
                            echo set_select('source', $source['source'], $default); 
                        ?>><?php echo $source['source']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <?php /* HIDDEN FIELDS — uncomment to restore State, City, Address, Description
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="state">State</label>
                <select name="state" id="state" class="form-control">
                    <option value="">Select State</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="city">City</label>
                <select name="city" id="city" class="form-control">
                    <option value="">Select City</option>
                </select>
                <input type="text" class="form-control mt-2" name="city_custom" id="city_other_text" placeholder="Enter your city" style="display:none;">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="address">Address</label>
                <textarea class="form-control" name="address"><?php echo set_value('address'); ?></textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" name="description"><?php echo set_value('description'); ?></textarea>
            </div>
        </div>
    </div>
    */ ?>
    <?php $is_school_k12 = (strtolower(trim($sch_setting_detail->institution_type)) != 'college'); ?>
    <?php if (!$is_school_k12): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="course_type">Course Type <span class="text-danger">*</span></label>
                <div>
                    <label class="radio-inline">
                        <input type="radio" name="course_type" value="ug_first_year" <?php echo set_radio('course_type', 'ug_first_year'); ?>> UG First Year
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="course_type" value="ug_lateral" <?php echo set_radio('course_type', 'ug_lateral'); ?>> UG Lateral Entry
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="course_type" value="pg_first_year" <?php echo set_radio('course_type', 'pg_first_year'); ?>> PG First Year
                    </label>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="admission_course_id">Course <span class="text-danger">*</span></label>
                <select name="admission_course_id" id="admission_course_id" class="form-control" required>
                    <?php if ($is_school_k12): ?>
                        <option value="">Select Course</option>
                        <?php foreach ($all_courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo set_select('admission_course_id', $c['id']); ?>><?php echo htmlspecialchars($c['course_name']); ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">Select Course Type First</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    </div>
    <?php /* HIDDEN FIELDS — uncomment to restore Reference, Referencer Details
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="reference">Reference</label>
                <select name="reference" class="form-control">
                    <option value="">Select Reference</option>
                    <?php foreach ($references as $ref): ?>
                        <option value="<?php echo $ref['reference']; ?>" <?php echo set_select('reference', $ref['reference']); ?>><?php echo $ref['reference']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="referencer_details">Referencer Details</label>
                <input type="text" class="form-control" name="referencer_details" value="<?php echo set_value('referencer_details'); ?>">
            </div>
        </div>
    </div>
    */ ?>

    <button type="submit" class="btn btn-primary">Submit Enquiry</button>
</form>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
$(document).ready(function() {
    <?php if (!$is_school_k12): ?>
    // Course data from PHP
    const coursesData = {
        ug_first_year: <?php echo json_encode($ug_first_year_courses); ?>,
        ug_lateral: <?php echo json_encode($ug_lateral_courses); ?>,
        pg_first_year: <?php echo json_encode($pg_first_year_courses); ?>
    };
    
    // Handle course level selection
    $('input[name="course_type"]').on('change', function() {
        const selectedType = $(this).val();
        const courseSelect = $('#admission_course_id');
        
        courseSelect.html('<option value="">Select Course</option>');
        
        if (coursesData[selectedType]) {
            coursesData[selectedType].forEach(function(course) {
                courseSelect.append(
                    $('<option></option>')
                        .attr('value', course.id)
                        .text(course.course_name)
                );
            });
        }
    });
    <?php endif; ?>
    
    // Load India states and cities
    let statesData = {};
    
    $.ajax({
        url: '<?php echo base_url("backend/json-files/india_states_cities.json"); ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            const stateSelect = document.getElementById('state');
            
            // Sort states alphabetically
            data.states.sort((a, b) => a.name.localeCompare(b.name));
            
            data.states.forEach(function(state) {
                // Sort cities alphabetically
                state.cities.sort((a, b) => a.localeCompare(b));
                statesData[state.name] = state.cities;
                
                const option = document.createElement('option');
                option.value = state.name;
                option.textContent = state.name;
                stateSelect.appendChild(option);
            });
        }
    });

    // Populate cities when state is selected
    document.getElementById('state').addEventListener('change', function() {
        const selectedState = this.value;
        const citySelect = document.getElementById('city');
        citySelect.innerHTML = '<option value="">Select City</option>';
        
        if (statesData[selectedState]) {
            statesData[selectedState].forEach(function(city) {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                citySelect.appendChild(option);
            });
        }
        // Always append Others option
        const othersOption = document.createElement('option');
        othersOption.value = 'Others';
        othersOption.textContent = 'Others';
        citySelect.appendChild(othersOption);
        $('#city_other_text').hide().val('');
    });

    // Show/hide city custom text field
    $('#city').on('change', function() {
        if ($(this).val() === 'Others') {
            $('#city_other_text').show().attr('required', true);
        } else {
            $('#city_other_text').hide().removeAttr('required').val('');
        }
    });

    // Phone: digits only
    $('#contact').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);
    });
});
</script>
