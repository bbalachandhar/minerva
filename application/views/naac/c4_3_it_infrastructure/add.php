<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c4_3_add'); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year'); ?>">
    </div>

    <div class="form-group">
        <label for="computer_student_ratio">Computer-Student Ratio</label>
        <input type="number" step="0.01" class="form-control" id="computer_student_ratio" name="computer_student_ratio" value="<?php echo set_value('computer_student_ratio'); ?>">
    </div>

    <div class="form-group">
        <label for="internet_bandwidth_mbps">Internet Bandwidth (Mbps)</label>
        <input type="number" class="form-control" id="internet_bandwidth_mbps" name="internet_bandwidth_mbps" value="<?php echo set_value('internet_bandwidth_mbps'); ?>">
    </div>

    <div class="form-group">
        <label for="it_policy_description">IT Policy Description</label>
        <textarea class="form-control" id="it_policy_description" name="it_policy_description"><?php echo set_value('it_policy_description'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="e_content_development_facilities">E-Content Development Facilities</label>
        <textarea class="form-control" id="e_content_development_facilities" name="e_content_development_facilities"><?php echo set_value('e_content_development_facilities'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="wifi_availability_description">Wi-Fi Availability Description</label>
        <textarea class="form-control" id="wifi_availability_description" name="wifi_availability_description"><?php echo set_value('wifi_availability_description'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_it_policy">Document Link (IT Policy)</label>
        <input type="url" class="form-control" id="document_link_it_policy" name="document_link_it_policy" value="<?php echo set_value('document_link_it_policy'); ?>">
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
    <a href="<?php echo base_url('naac/c4_3_it_infrastructure'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>