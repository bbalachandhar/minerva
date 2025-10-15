<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c3_4_edit/' . $entry['id']); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year', $entry['academic_year']); ?>">
    </div>

    <div class="form-group">
        <label for="author_name">Author Name</label>
        <input type="text" class="form-control" id="author_name" name="author_name" value="<?php echo set_value('author_name', $entry['author_name']); ?>">
    </div>

    <div class="form-group">
        <label for="publication_title">Publication Title</label>
        <input type="text" class="form-control" id="publication_title" name="publication_title" value="<?php echo set_value('publication_title', $entry['publication_title']); ?>">
    </div>

    <div class="form-group">
        <label for="journal_name">Journal Name</label>
        <input type="text" class="form-control" id="journal_name" name="journal_name" value="<?php echo set_value('journal_name', $entry['journal_name']); ?>">
    </div>

    <div class="form-group">
        <label for="ugc_care_list">UGC CARE List</label>
        <select class="form-control" id="ugc_care_list" name="ugc_care_list">
            <option value="">Select</option>
            <option value="Yes" <?php echo set_select('ugc_care_list', 'Yes', ($entry['ugc_care_list'] == 'Yes')); ?>>Yes</option>
            <option value="No" <?php echo set_select('ugc_care_list', 'No', ($entry['ugc_care_list'] == 'No')); ?>>No</option>
        </select>
    </div>

    <div class="form-group">
        <label for="indexed_in">Indexed In (e.g., Scopus, Web of Science)</label>
        <input type="text" class="form-control" id="indexed_in" name="indexed_in" value="<?php echo set_value('indexed_in', $entry['indexed_in']); ?>">
    </div>

    <div class="form-group">
        <label for="award_name">Award Name</label>
        <input type="text" class="form-control" id="award_name" name="award_name" value="<?php echo set_value('award_name', $entry['award_name']); ?>">
    </div>

    <div class="form-group">
        <label for="awarding_agency">Awarding Agency</label>
        <input type="text" class="form-control" id="awarding_agency" name="awarding_agency" value="<?php echo set_value('awarding_agency', $entry['awarding_agency']); ?>">
    </div>

    <div class="form-group">
        <label for="document_link_publication">Document Link (Publication)</label>
        <input type="url" class="form-control" id="document_link_publication" name="document_link_publication" value="<?php echo set_value('document_link_publication', $entry['document_link_publication']); ?>">
    </div>

    <div class="form-group">
        <label for="document_link_award">Document Link (Award)</label>
        <input type="url" class="form-control" id="document_link_award" name="document_link_award" value="<?php echo set_value('document_link_award', $entry['document_link_award']); ?>">
    </div>

    <button type="submit" class="btn btn-success">Update</button>
    <a href="<?php echo base_url('naac/c3_4_research_publications_awards'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>