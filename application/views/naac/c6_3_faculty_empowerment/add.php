<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c6_3_add'); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year'); ?>">
    </div>

    <div class="form-group">
        <label for="welfare_measures_description">Welfare Measures Description</label>
        <textarea class="form-control" id="welfare_measures_description" name="welfare_measures_description"><?php echo set_value('welfare_measures_description'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="teachers_received_financial_support">Teachers Received Financial Support</label>
        <input type="number" class="form-control" id="teachers_received_financial_support" name="teachers_received_financial_support" value="<?php echo set_value('teachers_received_financial_support'); ?>">
    </div>

    <div class="form-group">
        <label for="professional_development_programs_organized">Professional Development Programs Organized</label>
        <input type="number" class="form-control" id="professional_development_programs_organized" name="professional_development_programs_organized" value="<?php echo set_value('professional_development_programs_organized'); ?>">
    </div>

    <div class="form-group">
        <label for="teachers_undergoing_fdp">Teachers Undergoing FDP</label>
        <input type="number" class="form-control" id="teachers_undergoing_fdp" name="teachers_undergoing_fdp" value="<?php echo set_value('teachers_undergoing_fdp'); ?>">
    </div>

    <div class="form-group">
        <label for="performance_appraisal_system">Performance Appraisal System</label>
        <textarea class="form-control" id="performance_appraisal_system" name="performance_appraisal_system"><?php echo set_value('performance_appraisal_system'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_welfare_policy">Document Link (Welfare Policy)</label>
        <input type="url" class="form-control" id="document_link_welfare_policy" name="document_link_welfare_policy" value="<?php echo set_value('document_link_welfare_policy'); ?>">
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
    <a href="<?php echo base_url('naac/c6_3_faculty_empowerment'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>