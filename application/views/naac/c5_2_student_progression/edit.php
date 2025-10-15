<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c5_2_edit/' . $entry['id']); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year', $entry['academic_year']); ?>">
    </div>

    <div class="form-group">
        <label for="program_name">Program Name</label>
        <input type="text" class="form-control" id="program_name" name="program_name" value="<?php echo set_value('program_name', $entry['program_name']); ?>">
    </div>

    <div class="form-group">
        <label for="total_outgoing_students">Total Outgoing Students</label>
        <input type="number" class="form-control" id="total_outgoing_students" name="total_outgoing_students" value="<?php echo set_value('total_outgoing_students', $entry['total_outgoing_students']); ?>">
    </div>

    <div class="form-group">
        <label for="students_placed">Students Placed</label>
        <input type="number" class="form-control" id="students_placed" name="students_placed" value="<?php echo set_value('students_placed', $entry['students_placed']); ?>">
    </div>

    <div class="form-group">
        <label for="students_to_higher_education">Students to Higher Education</label>
        <input type="number" class="form-control" id="students_to_higher_education" name="students_to_higher_education" value="<?php echo set_value('students_to_higher_education', $entry['students_to_higher_education']); ?>">
    </div>

    <div class="form-group">
        <label for="students_qualified_competitive_exams">Students Qualified Competitive Exams</label>
        <input type="number" class="form-control" id="students_qualified_competitive_exams" name="students_qualified_competitive_exams" value="<?php echo set_value('students_qualified_competitive_exams', $entry['students_qualified_competitive_exams']); ?>">
    </div>

    <div class="form-group">
        <label for="progression_facilitation_description">Progression Facilitation Description</label>
        <textarea class="form-control" id="progression_facilitation_description" name="progression_facilitation_description"><?php echo set_value('progression_facilitation_description', $entry['progression_facilitation_description']); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_placement_report">Document Link (Placement Report)</label>
        <input type="url" class="form-control" id="document_link_placement_report" name="document_link_placement_report" value="<?php echo set_value('document_link_placement_report', $entry['document_link_placement_report']); ?>">
    </div>

    <div class="form-group">
        <label for="document_link_higher_education_data">Document Link (Higher Education Data)</label>
        <input type="url" class="form-control" id="document_link_higher_education_data" name="document_link_higher_education_data" value="<?php echo set_value('document_link_higher_education_data', $entry['document_link_higher_education_data']); ?>">
    </div>

    <button type="submit" class="btn btn-success">Update</button>
    <a href="<?php echo base_url('naac/c5_2_student_progression'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>