<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c6_1_add'); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year'); ?>">
    </div>

    <div class="form-group">
        <label for="governance_vision_mission_alignment">Governance Vision Mission Alignment</label>
        <textarea class="form-control" id="governance_vision_mission_alignment" name="governance_vision_mission_alignment"><?php echo set_value('governance_vision_mission_alignment'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="leadership_effectiveness_description">Leadership Effectiveness Description</label>
        <textarea class="form-control" id="leadership_effectiveness_description" name="leadership_effectiveness_description"><?php echo set_value('leadership_effectiveness_description'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="decentralization_participative_management">Decentralization & Participative Management</label>
        <textarea class="form-control" id="decentralization_participative_management" name="decentralization_participative_management"><?php echo set_value('decentralization_participative_management'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_vision_mission">Document Link (Vision/Mission)</label>
        <input type="url" class="form-control" id="document_link_vision_mission" name="document_link_vision_mission" value="<?php echo set_value('document_link_vision_mission'); ?>">
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
    <a href="<?php echo base_url('naac/c6_1_vision_leadership'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>