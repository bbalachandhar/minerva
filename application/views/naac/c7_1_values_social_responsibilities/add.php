<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<?php echo validation_errors(); ?>

<?php echo form_open('naac/c7_1_add'); ?>

    <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year'); ?>">
    </div>

    <div class="form-group">
        <label for="gender_equity_measures">Gender Equity Measures</label>
        <textarea class="form-control" id="gender_equity_measures" name="gender_equity_measures"><?php echo set_value('gender_equity_measures'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="disabled_friendly_campus_description">Disabled-Friendly Campus Description</label>
        <textarea class="form-control" id="disabled_friendly_campus_description" name="disabled_friendly_campus_description"><?php echo set_value('disabled_friendly_campus_description'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="inclusive_environment_initiatives">Inclusive Environment Initiatives</label>
        <textarea class="form-control" id="inclusive_environment_initiatives" name="inclusive_environment_initiatives"><?php echo set_value('inclusive_environment_initiatives'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="human_values_ethics_activities">Human Values & Ethics Activities</label>
        <textarea class="form-control" id="human_values_ethics_activities" name="human_values_ethics_activities"><?php echo set_value('human_values_ethics_activities'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="commemorative_events_details">Commemorative Events Details</label>
        <textarea class="form-control" id="commemorative_events_details" name="commemorative_events_details"><?php echo set_value('commemorative_events_details'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="alternate_energy_conservation_details">Alternate Energy & Conservation Details</label>
        <textarea class="form-control" id="alternate_energy_conservation_details" name="alternate_energy_conservation_details"><?php echo set_value('alternate_energy_conservation_details'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="waste_management_details">Waste Management Details</label>
        <textarea class="form-control" id="waste_management_details" name="waste_management_details"><?php echo set_value('waste_management_details'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="water_conservation_details">Water Conservation Details</label>
        <textarea class="form-control" id="water_conservation_details" name="water_conservation_details"><?php echo set_value('water_conservation_details'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="green_campus_initiatives">Green Campus Initiatives</label>
        <textarea class="form-control" id="green_campus_initiatives" name="green_campus_initiatives"><?php echo set_value('green_campus_initiatives'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="quality_audits_environment_energy">Quality Audits (Environment & Energy)</label>
        <textarea class="form-control" id="quality_audits_environment_energy" name="quality_audits_environment_energy"><?php echo set_value('quality_audits_environment_energy'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="code_of_conduct_details">Code of Conduct Details</label>
        <textarea class="form-control" id="code_of_conduct_details" name="code_of_conduct_details"><?php echo set_value('code_of_conduct_details'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="document_link_gender_equity_policy">Document Link (Gender Equity Policy)</label>
        <input type="url" class="form-control" id="document_link_gender_equity_policy" name="document_link_gender_equity_policy" value="<?php echo set_value('document_link_gender_equity_policy'); ?>">
    </div>

    <div class="form-group">
        <label for="document_link_disabled_friendly_policy">Document Link (Disabled-Friendly Policy)</label>
        <input type="url" class="form-control" id="document_link_disabled_friendly_policy" name="document_link_disabled_friendly_policy" value="<?php echo set_value('document_link_disabled_friendly_policy'); ?>">
    </div>

    <div class="form-group">
        <label for="document_link_environmental_audit">Document Link (Environmental Audit)</label>
        <input type="url" class="form-control" id="document_link_environmental_audit" name="document_link_environmental_audit" value="<?php echo set_value('document_link_environmental_audit'); ?>">
    </div>

    <div class="form-group">
        <label for="document_link_code_of_conduct">Document Link (Code of Conduct)</label>
        <input type="url" class="form-control" id="document_link_code_of_conduct" name="document_link_code_of_conduct" value="<?php echo set_value('document_link_code_of_conduct'); ?>">
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
    <a href="<?php echo base_url('naac/c7_1_values_social_responsibilities'); ?>" class="btn btn-secondary">Cancel</a>

<?php echo form_close(); ?>