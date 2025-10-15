<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c6_2_edit/' . $entry['id']); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year', $entry['academic_year']); ?>">
    </div>

    <div class="form-group">
        <label for="strategic_plan_description">Strategic Plan Description</label>
        <textarea class="form-control" id="strategic_plan_description" name="strategic_plan_description"><?php echo set_value('strategic_plan_description', $entry['strategic_plan_description']); ?></textarea>
    </div>

    <div class="form-group">
        <label for="e_governance_implementation_areas">E-Governance Implementation Areas</label>
        <textarea class="form-control" id="e_governance_implementation_areas" name="e_governance_implementation_areas"><?php echo set_value('e_governance_implementation_areas', $entry['e_governance_implementation_areas']); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_strategic_plan">Document Link (Strategic Plan)</label>
        <input type="url" class="form-control" id="document_link_strategic_plan" name="document_link_strategic_plan" value="<?php echo set_value('document_link_strategic_plan', $entry['document_link_strategic_plan']); ?>">
    </div>

    <div class="form-group">
        <label for="document_link_e_governance_report">Document Link (E-Governance Report)</label>
        <input type="url" class="form-control" id="document_link_e_governance_report" name="document_link_e_governance_report" value="<?php echo set_value('document_link_e_governance_report', $entry['document_link_e_governance_report']); ?>">
    </div>

    <button type="submit" class="btn btn-success">Update</button>
    <a href="<?php echo base_url('naac/c6_2_strategy_deployment'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>