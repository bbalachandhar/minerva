<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c4_4_add'); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year'); ?>">
    </div>

    <div class="form-group">
        <label for="expenditure_on_maintenance_lakhs">Expenditure on Maintenance (in Lakhs)</label>
        <input type="number" step="0.01" class="form-control" id="expenditure_on_maintenance_lakhs" name="expenditure_on_maintenance_lakhs" value="<?php echo set_value('expenditure_on_maintenance_lakhs'); ?>">
    </div>

    <div class="form-group">
        <label for="maintenance_systems_procedures">Maintenance Systems and Procedures</label>
        <textarea class="form-control" id="maintenance_systems_procedures" name="maintenance_systems_procedures"><?php echo set_value('maintenance_systems_procedures'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="utilization_of_facilities">Utilization of Facilities</label>
        <textarea class="form-control" id="utilization_of_facilities" name="utilization_of_facilities"><?php echo set_value('utilization_of_facilities'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_audited_statements">Document Link (Audited Statements)</label>
        <input type="url" class="form-control" id="document_link_audited_statements" name="document_link_audited_statements" value="<?php echo set_value('document_link_audited_statements'); ?>">
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
    <a href="<?php echo base_url('naac/c4_4_campus_maintenance'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>