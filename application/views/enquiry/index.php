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
                <input type="text" class="form-control" name="name" value="<?php echo set_value('name'); ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="contact">Phone <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="contact" value="<?php echo set_value('contact'); ?>">
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
                <select name="source" class="form-control">
                    <option value="">Select Source</option>
                    <?php foreach ($sourcelist as $source): ?>
                        <option value="<?php echo $source['source']; ?>" <?php echo set_select('source', $source['source']); ?>><?php echo $source['source']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
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
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="admission_course_id">Course <span class="text-danger">*</span></label>
                <select name="admission_course_id" id="admission_course_id" class="form-control">
                    <option value="">Select Course Type First</option>
                </select>
            </div>
        </div>
    </div>
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

    <button type="submit" class="btn btn-primary">Submit Enquiry</button>
</form>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
$(document).ready(function() {
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
    });
});
</script>
