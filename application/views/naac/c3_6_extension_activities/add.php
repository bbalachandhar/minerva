<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c3_6_add'); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year'); ?>">
    </div>

    <div class="form-group">
        <label for="activity_name">Activity Name</label>
        <input type="text" class="form-control" id="activity_name" name="activity_name" value="<?php echo set_value('activity_name'); ?>">
    </div>

    <div class="form-group">
        <label for="organizing_unit">Organizing Unit</label>
        <input type="text" class="form-control" id="organizing_unit" name="organizing_unit" value="<?php echo set_value('organizing_unit'); ?>">
    </div>

    <div class="form-group">
        <label for="number_of_students_participated">Number of Students Participated</label>
        <input type="number" class="form-control" id="number_of_students_participated" name="number_of_students_participated" value="<?php echo set_value('number_of_students_participated'); ?>">
    </div>

    <div class="form-group">
        <label for="number_of_public_benefited">Number of Public Benefited</label>
        <input type="number" class="form-control" id="number_of_public_benefited" name="number_of_public_benefited" value="<?php echo set_value('number_of_public_benefited'); ?>">
    </div>

    <div class="form-group">
        <label for="extension_activity_impact">Extension Activity Impact</label>
        <textarea class="form-control" id="extension_activity_impact" name="extension_activity_impact"><?php echo set_value('extension_activity_impact'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_report">Document Link (Report)</label>
        <input type="url" class="form-control" id="document_link_report" name="document_link_report" value="<?php echo set_value('document_link_report'); ?>">
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
    <a href="<?php echo base_url('naac/c3_6_extension_activities'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>