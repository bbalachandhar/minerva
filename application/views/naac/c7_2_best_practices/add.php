<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c7_2_add'); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year'); ?>">
    </div>

    <div class="form-group">
        <label for="best_practice_title_1">Best Practice 1 Title</label>
        <input type="text" class="form-control" id="best_practice_title_1" name="best_practice_title_1" value="<?php echo set_value('best_practice_title_1'); ?>">
    </div>

    <div class="form-group">
        <label for="best_practice_description_1">Best Practice 1 Description</label>
        <textarea class="form-control" id="best_practice_description_1" name="best_practice_description_1"><?php echo set_value('best_practice_description_1'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="best_practice_title_2">Best Practice 2 Title</label>
        <input type="text" class="form-control" id="best_practice_title_2" name="best_practice_title_2" value="<?php echo set_value('best_practice_title_2'); ?>">
    </div>

    <div class="form-group">
        <label for="best_practice_description_2">Best Practice 2 Description</label>
        <textarea class="form-control" id="best_practice_description_2" name="best_practice_description_2"><?php echo set_value('best_practice_description_2'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_best_practice_1">Document Link (Best Practice 1)</label>
        <input type="url" class="form-control" id="document_link_best_practice_1" name="document_link_best_practice_1" value="<?php echo set_value('document_link_best_practice_1'); ?>">
    </div>

    <div class="form-group">
        <label for="document_link_best_practice_2">Document Link (Best Practice 2)</label>
        <input type="url" class="form-control" id="document_link_best_practice_2" name="document_link_best_practice_2" value="<?php echo set_value('document_link_best_practice_2'); ?>">
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
    <a href="<?php echo base_url('naac/c7_2_best_practices'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>