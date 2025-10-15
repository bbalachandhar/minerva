<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c5_1_add'); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year'); ?>">
    </div>

    <div class="form-group">
        <label for="total_students_benefited_scholarships">Total Students Benefited from Scholarships</label>
        <input type="number" class="form-control" id="total_students_benefited_scholarships" name="total_students_benefited_scholarships" value="<?php echo set_value('total_students_benefited_scholarships'); ?>">
    </div>

    <div class="form-group">
        <label for="total_amount_scholarships_lakhs">Total Amount of Scholarships (in Lakhs)</label>
        <input type="number" step="0.01" class="form-control" id="total_amount_scholarships_lakhs" name="total_amount_scholarships_lakhs" value="<?php echo set_value('total_amount_scholarships_lakhs'); ?>">
    </div>

    <div class="form-group">
        <label for="support_mechanisms_description">Support Mechanisms Description</label>
        <textarea class="form-control" id="support_mechanisms_description" name="support_mechanisms_description"><?php echo set_value('support_mechanisms_description'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="capacity_building_skills_enhancement">Capacity Building & Skills Enhancement</label>
        <textarea class="form-control" id="capacity_building_skills_enhancement" name="capacity_building_skills_enhancement"><?php echo set_value('capacity_building_skills_enhancement'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_scholarship_policy">Document Link (Scholarship Policy)</label>
        <input type="url" class="form-control" id="document_link_scholarship_policy" name="document_link_scholarship_policy" value="<?php echo set_value('document_link_scholarship_policy'); ?>">
    </div>

    <div class="form-group">
        <label for="document_link_support_services">Document Link (Support Services)</label>
        <input type="url" class="form-control" id="document_link_support_services" name="document_link_support_services" value="<?php echo set_value('document_link_support_services'); ?>">
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
    <a href="<?php echo base_url('naac/c5_1_student_support'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>