<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c6_5_add'); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year'); ?>">
    </div>

    <div class="form-group">
        <label for="iqac_initiatives_description">IQAC Initiatives Description</label>
        <textarea class="form-control" id="iqac_initiatives_description" name="iqac_initiatives_description"><?php echo set_value('iqac_initiatives_description'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="quality_assurance_initiatives">Quality Assurance Initiatives</label>
        <textarea class="form-control" id="quality_assurance_initiatives" name="quality_assurance_initiatives"><?php echo set_value('quality_assurance_initiatives'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_iqac_report">Document Link (IQAC Report)</label>
        <input type="url" class="form-control" id="document_link_iqac_report" name="document_link_iqac_report" value="<?php echo set_value('document_link_iqac_report'); ?>">
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
    <a href="<?php echo base_url('naac/c6_5_iqas'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>