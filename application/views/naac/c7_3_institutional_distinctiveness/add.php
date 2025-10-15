<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c7_3_add'); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year'); ?>">
    </div>

    <div class="form-group">
        <label for="distinctive_area_description">Distinctive Area Description</label>
        <textarea class="form-control" id="distinctive_area_description" name="distinctive_area_description"><?php echo set_value('distinctive_area_description'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_distinctiveness_report">Document Link (Distinctiveness Report)</label>
        <input type="url" class="form-control" id="document_link_distinctiveness_report" name="document_link_distinctiveness_report" value="<?php echo set_value('document_link_distinctiveness_report'); ?>">
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
    <a href="<?php echo base_url('naac/c7_3_institutional_distinctiveness'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>