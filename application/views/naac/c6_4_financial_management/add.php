<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c6_4_add'); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year'); ?>">
    </div>

    <div class="form-group">
        <label for="internal_audits_regularity">Internal Audits Regularity</label>
        <textarea class="form-control" id="internal_audits_regularity" name="internal_audits_regularity"><?php echo set_value('internal_audits_regularity'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="external_audits_regularity">External Audits Regularity</label>
        <textarea class="form-control" id="external_audits_regularity" name="external_audits_regularity"><?php echo set_value('external_audits_regularity'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="funds_grants_received_lakhs">Funds/Grants Received (in Lakhs)</label>
        <input type="number" step="0.01" class="form-control" id="funds_grants_received_lakhs" name="funds_grants_received_lakhs" value="<?php echo set_value('funds_grants_received_lakhs'); ?>">
    </div>

    <div class="form-group">
        <label for="resource_mobilization_strategies">Resource Mobilization Strategies</label>
        <textarea class="form-control" id="resource_mobilization_strategies" name="resource_mobilization_strategies"><?php echo set_value('resource_mobilization_strategies'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_audit_reports">Document Link (Audit Reports)</label>
        <input type="url" class="form-control" id="document_link_audit_reports" name="document_link_audit_reports" value="<?php echo set_value('document_link_audit_reports'); ?>">
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
    <a href="<?php echo base_url('naac/c6_4_financial_management'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>