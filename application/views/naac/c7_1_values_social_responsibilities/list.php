<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c7_1_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Gender Equity Measures</th>
            <th>Disabled-Friendly Campus Description</th>
            <th>Inclusive Environment Initiatives</th>
            <th>Human Values & Ethics Activities</th>
            <th>Commemorative Events Details</th>
            <th>Alternate Energy & Conservation Details</th>
            <th>Waste Management Details</th>
            <th>Water Conservation Details</th>
            <th>Green Campus Initiatives</th>
            <th>Quality Audits (Environment & Energy)</th>
            <th>Code of Conduct Details</th>
            <th>Gender Equity Policy Link</th>
            <th>Disabled-Friendly Policy Link</th>
            <th>Environmental Audit Link</th>
            <th>Code of Conduct Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c7_1_data)): ?>
            <?php foreach ($c7_1_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['gender_equity_measures']; ?></td>
                    <td><?php echo $row['disabled_friendly_campus_description']; ?></td>
                    <td><?php echo $row['inclusive_environment_initiatives']; ?></td>
                    <td><?php echo $row['human_values_ethics_activities']; ?></td>
                    <td><?php echo $row['commemorative_events_details']; ?></td>
                    <td><?php echo $row['alternate_energy_conservation_details']; ?></td>
                    <td><?php echo $row['waste_management_details']; ?></td>
                    <td><?php echo $row['water_conservation_details']; ?></td>
                    <td><?php echo $row['green_campus_initiatives']; ?></td>
                    <td><?php echo $row['quality_audits_environment_energy']; ?></td>
                    <td><?php echo $row['code_of_conduct_details']; ?></td>
                    <td><a href="<?php echo $row['document_link_gender_equity_policy']; ?>" target="_blank">View Policy</a></td>
                    <td><a href="<?php echo $row['document_link_disabled_friendly_policy']; ?>" target="_blank">View Policy</a></td>
                    <td><a href="<?php echo $row['document_link_environmental_audit']; ?>" target="_blank">View Audit</a></td>
                    <td><a href="<?php echo $row['document_link_code_of_conduct']; ?>" target="_blank">View Code</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c7_1_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c7_1_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="17">No data available for Institutional Values and Social Responsibilities.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>