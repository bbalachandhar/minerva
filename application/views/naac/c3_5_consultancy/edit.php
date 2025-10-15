<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c3_5_edit/' . $entry['id']); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year', $entry['academic_year']); ?>">
    </div>

    <div class="form-group">
        <label for="consultant_name">Consultant Name</label>
        <input type="text" class="form-control" id="consultant_name" name="consultant_name" value="<?php echo set_value('consultant_name', $entry['consultant_name']); ?>">
    </div>

    <div class="form-group">
        <label for="client_organization">Client Organization</label>
        <input type="text" class="form-control" id="client_organization" name="client_organization" value="<?php echo set_value('client_organization', $entry['client_organization']); ?>">
    </div>

    <div class="form-group">
        <label for="consultancy_area">Consultancy Area</label>
        <textarea class="form-control" id="consultancy_area" name="consultancy_area"><?php echo set_value('consultancy_area', $entry['consultancy_area']); ?></textarea>
    </div>

    <div class="form-group">
        <label for="revenue_generated_lakhs">Revenue Generated (in Lakhs)</label>
        <input type="number" step="0.01" class="form-control" id="revenue_generated_lakhs" name="revenue_generated_lakhs" value="<?php echo set_value('revenue_generated_lakhs', $entry['revenue_generated_lakhs']); ?>">
    </div>

    <div class="form-group">
        <label for="document_link_report">Document Link (Report)</label>
        <input type="url" class="form-control" id="document_link_report" name="document_link_report" value="<?php echo set_value('document_link_report', $entry['document_link_report']); ?>">
    </div>

    <button type="submit" class="btn btn-success">Update</button>
    <a href="<?php echo base_url('naac/c3_5_consultancy'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>