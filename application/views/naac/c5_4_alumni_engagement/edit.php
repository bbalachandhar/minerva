<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c5_4_edit/' . $entry['id']); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year', $entry['academic_year']); ?>">
    </div>

    <div class="form-group">
        <label for="alumni_association_registered">Alumni Association Registered</label>
        <select class="form-control" id="alumni_association_registered" name="alumni_association_registered">
            <option value="">Select</option>
            <option value="Yes" <?php echo set_select('alumni_association_registered', 'Yes', ($entry['alumni_association_registered'] == 'Yes')); ?>>Yes</option>
            <option value="No" <?php echo set_select('alumni_association_registered', 'No', ($entry['alumni_association_registered'] == 'No')); ?>>No</option>
        </select>
    </div>

    <div class="form-group">
        <label for="alumni_contribution_description">Alumni Contribution Description</label>
        <textarea class="form-control" id="alumni_contribution_description" name="alumni_contribution_description"><?php echo set_value('alumni_contribution_description', $entry['alumni_contribution_description']); ?></textarea>
    </div>

    <div class="form-group">
        <label for="alumni_engagement_activities">Alumni Engagement Activities</label>
        <textarea class="form-control" id="alumni_engagement_activities" name="alumni_engagement_activities"><?php echo set_value('alumni_engagement_activities', $entry['alumni_engagement_activities']); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_alumni_report">Document Link (Alumni Report)</label>
        <input type="url" class="form-control" id="document_link_alumni_report" name="document_link_alumni_report" value="<?php echo set_value('document_link_alumni_report', $entry['document_link_alumni_report']); ?>">
    </div>

    <button type="submit" class="btn btn-success">Update</button>
    <a href="<?php echo base_url('naac/c5_4_alumni_engagement'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>