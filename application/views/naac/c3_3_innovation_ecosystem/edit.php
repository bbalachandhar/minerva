<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c3_3_edit/' . $entry['id']); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year', $entry['academic_year']); ?>">
    </div>

    <div class="form-group">
        <label for="innovation_ecosystem_description">Innovation Ecosystem Description</label>
        <textarea class="form-control" id="innovation_ecosystem_description" name="innovation_ecosystem_description"><?php echo set_value('innovation_ecosystem_description', $entry['innovation_ecosystem_description']); ?></textarea>
    </div>

    <div class="form-group">
        <label for="number_of_startups">Number of Startups</label>
        <input type="number" class="form-control" id="number_of_startups" name="number_of_startups" value="<?php echo set_value('number_of_startups', $entry['number_of_startups']); ?>">
    </div>

    <div class="form-group">
        <label for="document_link_incubation_policy">Document Link (Incubation Policy)</label>
        <input type="url" class="form-control" id="document_link_incubation_policy" name="document_link_incubation_policy" value="<?php echo set_value('document_link_incubation_policy', $entry['document_link_incubation_policy']); ?>">
    </div>

    <button type="submit" class="btn btn-success">Update</button>
    <a href="<?php echo base_url('naac/c3_3_innovation_ecosystem'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>