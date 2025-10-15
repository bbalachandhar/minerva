<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c3_7_add'); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year'); ?>">
    </div>

    <div class="form-group">
        <label for="partner_organization">Partner Organization</label>
        <input type="text" class="form-control" id="partner_organization" name="partner_organization" value="<?php echo set_value('partner_organization'); ?>">
    </div>

    <div class="form-group">
        <label for="type_of_collaboration">Type of Collaboration</label>
        <input type="text" class="form-control" id="type_of_collaboration" name="type_of_collaboration" value="<?php echo set_value('type_of_collaboration'); ?>">
    </div>

    <div class="form-group">
        <label for="purpose_of_collaboration">Purpose of Collaboration</label>
        <textarea class="form-control" id="purpose_of_collaboration" name="purpose_of_collaboration"><?php echo set_value('purpose_of_collaboration'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_mou">Document Link (MoU)</label>
        <input type="url" class="form-control" id="document_link_mou" name="document_link_mou" value="<?php echo set_value('document_link_mou'); ?>">
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
    <a href="<?php echo base_url('naac/c3_7_collaboration'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>