<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/edit_criterion1/' . $entry['id']); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year', $entry['academic_year']); ?>">
    </div>

    <div class="form-group">
        <label for="program_name">Program Name</label>
        <input type="text" class="form-control" id="program_name" name="program_name" value="<?php echo set_value('program_name', $entry['program_name']); ?>">
    </div>

    <div class="form-group">
        <label for="course_code">Course Code</label>
        <input type="text" class="form-control" id="course_code" name="course_code" value="<?php echo set_value('course_code', $entry['course_code']); ?>">
    </div>

    <div class="form-group">
        <label for="course_title">Course Title</label>
        <input type="text" class="form-control" id="course_title" name="course_title" value="<?php echo set_value('course_title', $entry['course_title']); ?>">
    </div>

    <div class="form-group">
        <label for="learning_outcomes">Learning Outcomes</label>
        <textarea class="form-control" id="learning_outcomes" name="learning_outcomes"><?php echo set_value('learning_outcomes', $entry['learning_outcomes']); ?></textarea>
    </div>

    <div class="form-group">
        <label for="curriculum_revision_date">Curriculum Revision Date</label>
        <input type="date" class="form-control" id="curriculum_revision_date" name="curriculum_revision_date" value="<?php echo set_value('curriculum_revision_date', $entry['curriculum_revision_date']); ?>">
    </div>

    <div class="form-group">
        <label for="stakeholder_feedback_mechanism">Stakeholder Feedback Mechanism</label>
        <textarea class="form-control" id="stakeholder_feedback_mechanism" name="stakeholder_feedback_mechanism"><?php echo set_value('stakeholder_feedback_mechanism', $entry['stakeholder_feedback_mechanism']); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link">Document Link</label>
        <input type="url" class="form-control" id="document_link" name="document_link" value="<?php echo set_value('document_link', $entry['document_link']); ?>">
    </div>

    <button type="submit" class="btn btn-success">Update</button>
    <a href="<?php echo base_url('naac/criterion1'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>