<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c4_2_add'); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year'); ?>">
    </div>

    <div class="form-group">
        <label for="number_of_books">Number of Books</label>
        <input type="number" class="form-control" id="number_of_books" name="number_of_books" value="<?php echo set_value('number_of_books'); ?>">
    </div>

    <div class="form-group">
        <label for="number_of_e_journals">Number of E-Journals</label>
        <input type="number" class="form-control" id="number_of_e_journals" name="number_of_e_journals" value="<?php echo set_value('number_of_e_journals'); ?>">
    </div>

    <div class="form-group">
        <label for="integrated_library_management_system">Integrated Library Management System</label>
        <textarea class="form-control" id="integrated_library_management_system" name="integrated_library_management_system"><?php echo set_value('integrated_library_management_system'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="library_e_resources_description">Library E-Resources Description</label>
        <textarea class="form-control" id="library_e_resources_description" name="library_e_resources_description"><?php echo set_value('library_e_resources_description'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="library_usage_details">Library Usage Details</label>
        <textarea class="form-control" id="library_usage_details" name="library_usage_details"><?php echo set_value('library_usage_details'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_library_report">Document Link (Library Report)</label>
        <input type="url" class="form-control" id="document_link_library_report" name="document_link_library_report" value="<?php echo set_value('document_link_library_report'); ?>">
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
    <a href="<?php echo base_url('naac/c4_2_library_resources'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>