<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c6_1_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Governance Vision Mission Alignment</th>
            <th>Leadership Effectiveness Description</th>
            <th>Decentralization & Participative Management</th>
            <th>Vision/Mission Document Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c6_1_data)): ?>
            <?php foreach ($c6_1_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['governance_vision_mission_alignment']; ?></td>
                    <td><?php echo $row['leadership_effectiveness_description']; ?></td>
                    <td><?php echo $row['decentralization_participative_management']; ?></td>
                    <td><a href="<?php echo $row['document_link_vision_mission']; ?>" target="_blank">View Document</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c6_1_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c6_1_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No data available for Institutional Vision and Leadership.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>