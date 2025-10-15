<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c5_3_add'); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year'); ?>">
    </div>

    <div class="form-group">
        <label for="activity_name">Activity Name</label>
        <input type="text" class="form-control" id="activity_name" name="activity_name" value="<?php echo set_value('activity_name'); ?>">
    </div>

    <div class="form-group">
        <label for="activity_type">Activity Type</label>
        <input type="text" class="form-control" id="activity_type" name="activity_type" value="<?php echo set_value('activity_type'); ?>">
    </div>

    <div class="form-group">
        <label for="number_of_students_participated">Number of Students Participated</label>
        <input type="number" class="form-control" id="number_of_students_participated" name="number_of_students_participated" value="<?php echo set_value('number_of_students_participated'); ?>">
    </div>

    <div class="form-group">
        <label for="awards_medals_won">Awards/Medals Won</label>
        <textarea class="form-control" id="awards_medals_won" name="awards_medals_won"><?php echo set_value('awards_medals_won'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="promotion_of_activities_description">Promotion of Activities Description</label>
        <textarea class="form-control" id="promotion_of_activities_description" name="promotion_of_activities_description"><?php echo set_value('promotion_of_activities_description'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_activity_report">Document Link (Activity Report)</label>
        <input type="url" class="form-control" id="document_link_activity_report" name="document_link_activity_report" value="<?php echo set_value('document_link_activity_report'); ?>">
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
    <a href="<?php echo base_url('naac/c5_3_student_participation'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>