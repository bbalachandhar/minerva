<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c4_1_add'); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year'); ?>">
    </div>

    <div class="form-group">
        <label for="classrooms_ict_enabled_percentage">Classrooms ICT Enabled (%)</label>
        <input type="number" step="0.01" class="form-control" id="classrooms_ict_enabled_percentage" name="classrooms_ict_enabled_percentage" value="<?php echo set_value('classrooms_ict_enabled_percentage'); ?>">
    </div>

    <div class="form-group">
        <label for="seminar_halls_ict_enabled_percentage">Seminar Halls ICT Enabled (%)</label>
        <input type="number" step="0.01" class="form-control" id="seminar_halls_ict_enabled_percentage" name="seminar_halls_ict_enabled_percentage" value="<?php echo set_value('seminar_halls_ict_enabled_percentage'); ?>">
    </div>

    <div class="form-group">
        <label for="physical_facilities_description">Physical Facilities Description</label>
        <textarea class="form-control" id="physical_facilities_description" name="physical_facilities_description"><?php echo set_value('physical_facilities_description'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="facilities_for_cultural_sports">Facilities for Cultural and Sports Activities</label>
        <textarea class="form-control" id="facilities_for_cultural_sports" name="facilities_for_cultural_sports"><?php echo set_value('facilities_for_cultural_sports'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_facilities_audit">Document Link (Facilities Audit)</label>
        <input type="url" class="form-control" id="document_link_facilities_audit" name="document_link_facilities_audit" value="<?php echo set_value('document_link_facilities_audit'); ?>">
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
    <a href="<?php echo base_url('naac/c4_1_physical_facilities'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>