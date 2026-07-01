<?php
if (validation_errors()) {
    echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>' . validation_errors() . '</div>';
}
?>
<form action="<?php echo site_url('enquiry/index'); ?>" method="post">

    <!-- Enquiry Details -->
    <div class="section-card">
        <div class="section-title"><i class="bi bi-person-lines-fill"></i> Admission Enquiry</div>
        <div class="field-grid">
            <div>
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="<?php echo set_value('name'); ?>" placeholder="Enter your full name" required>
            </div>
            <div>
                <label class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" name="contact" id="contact" value="<?php echo set_value('contact'); ?>" maxlength="10" pattern="[0-9]{10}" placeholder="10-digit mobile number" required>
                <div class="form-text">Enter 10-digit mobile number</div>
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="<?php echo set_value('email'); ?>" placeholder="your.email@example.com">
            </div>
            <div>
                <label class="form-label">Source <span class="text-danger">*</span></label>
                <select name="source" class="form-select" required>
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
    <div class="section-card">
        <div class="section-title"><i class="bi bi-geo-alt-fill"></i> Location Details</div>
        <div class="field-grid">
            <div>
                <label class="form-label">State</label>
                <select name="state" id="state" class="form-select">
                    <option value="">Select State</option>
                </select>
            </div>
            <div>
                <label class="form-label">City</label>
                <select name="city" id="city" class="form-select">
                    <option value="">Select City</option>
                </select>
                <input type="text" class="form-control mt-2" name="city_custom" id="city_other_text" placeholder="Enter your city" style="display:none;">
            </div>
            <div>
                <label class="form-label">Address</label>
                <textarea class="form-control" name="address" rows="2" placeholder="Enter your address"><?php echo set_value('address'); ?></textarea>
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="2" placeholder="Any additional details"><?php echo set_value('description'); ?></textarea>
            </div>
        </div>
    </div>
    */ ?>

    <!-- Course Selection -->
    <div class="section-card">
        <div class="section-title"><i class="bi bi-mortarboard-fill"></i> Course Selection</div>

        <?php $is_school_k12 = (strtolower(trim($sch_setting_detail->institution_type)) != 'college'); ?>
        <?php if (!$is_school_k12): ?>
        <div style="margin-bottom: 16px;">
            <label class="form-label">Course Type <span class="text-danger">*</span></label>
            <div class="course-type-group">
                <div class="ct-option">
                    <input type="radio" name="course_type" id="ct_ug" value="ug_first_year" <?php echo set_radio('course_type', 'ug_first_year'); ?>>
                    <label class="ct-label" for="ct_ug"><i class="bi bi-book"></i><br>UG First Year</label>
                </div>
                <div class="ct-option">
                    <input type="radio" name="course_type" id="ct_lateral" value="ug_lateral" <?php echo set_radio('course_type', 'ug_lateral'); ?>>
                    <label class="ct-label" for="ct_lateral"><i class="bi bi-arrow-right-circle"></i><br>UG Lateral Entry</label>
                </div>
                <div class="ct-option">
                    <input type="radio" name="course_type" id="ct_pg" value="pg_first_year" <?php echo set_radio('course_type', 'pg_first_year'); ?>>
                    <label class="ct-label" for="ct_pg"><i class="bi bi-mortarboard"></i><br>PG First Year</label>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div>
            <label class="form-label">Course <span class="text-danger">*</span></label>
            <select name="admission_course_id" id="admission_course_id" class="form-select" required>
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

    <?php /* HIDDEN FIELDS — uncomment to restore Reference, Referencer Details
    <div class="section-card">
        <div class="section-title"><i class="bi bi-people-fill"></i> Reference</div>
        <div class="field-grid">
            <div>
                <label class="form-label">Reference</label>
                <select name="reference" class="form-select">
                    <option value="">Select Reference</option>
                    <?php foreach ($references as $ref): ?>
                        <option value="<?php echo $ref['reference']; ?>" <?php echo set_select('reference', $ref['reference']); ?>><?php echo $ref['reference']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Referencer Details</label>
                <input type="text" class="form-control" name="referencer_details" value="<?php echo set_value('referencer_details'); ?>" placeholder="Name or contact">
            </div>
        </div>
    </div>
    */ ?>

    <!-- Submit -->
    <button type="submit" class="btn-submit">
        <i class="bi bi-send-fill me-2"></i> Submit Enquiry
    </button>

</form>

<script>
$(document).ready(function() {
    <?php if (!$is_school_k12): ?>
    const coursesData = {
        ug_first_year: <?php echo json_encode($ug_first_year_courses); ?>,
        ug_lateral: <?php echo json_encode($ug_lateral_courses); ?>,
        pg_first_year: <?php echo json_encode($pg_first_year_courses); ?>
    };

    function populateCourses(type) {
        const courseSelect = $('#admission_course_id');
        courseSelect.html('<option value="">Select Course</option>');
        if (coursesData[type]) {
            coursesData[type].forEach(function(course) {
                courseSelect.append(
                    $('<option></option>').attr('value', course.id).text(course.course_name)
                );
            });
        }
    }

    $('input[name="course_type"]').on('change', function() {
        populateCourses($(this).val());
    });

    // Auto-select first available type on load so courses appear immediately
    var preselectedType = '<?php echo set_value('course_type'); ?>';
    if (preselectedType) {
        $('input[name="course_type"][value="' + preselectedType + '"]').prop('checked', true);
        populateCourses(preselectedType);
    } else {
        // Default: check first radio and populate its courses
        var $firstRadio = $('input[name="course_type"]:first');
        $firstRadio.prop('checked', true);
        populateCourses($firstRadio.val());
    }
    <?php endif; ?>

    // Load India states and cities
    let statesData = {};
    var stateEl = document.getElementById('state');
    if (stateEl) {
        $.ajax({
            url: '<?php echo base_url("backend/json-files/india_states_cities.json"); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                data.states.sort((a, b) => a.name.localeCompare(b.name));
                data.states.forEach(function(state) {
                    state.cities.sort((a, b) => a.localeCompare(b));
                    statesData[state.name] = state.cities;
                    const option = document.createElement('option');
                    option.value = state.name;
                    option.textContent = state.name;
                    stateEl.appendChild(option);
                });
            }
        });

        stateEl.addEventListener('change', function() {
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
            const othersOption = document.createElement('option');
            othersOption.value = 'Others';
            othersOption.textContent = 'Others';
            citySelect.appendChild(othersOption);
            $('#city_other_text').hide().val('');
        });

        $('#city').on('change', function() {
            if ($(this).val() === 'Others') {
                $('#city_other_text').show().attr('required', true);
            } else {
                $('#city_other_text').hide().removeAttr('required').val('');
            }
        });
    }

    // Phone: digits only
    $('#contact').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);
    });
});
</script>
