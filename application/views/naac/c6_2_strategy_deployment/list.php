<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c6_2_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Strategic Plan Description</th>
            <th>E-Governance Implementation Areas</th>
            <th>Strategic Plan Link</th>
            <th>E-Governance Report Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c6_2_data)): ?>
            <?php foreach ($c6_2_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['strategic_plan_description']; ?></td>
                    <td><?php echo $row['e_governance_implementation_areas']; ?></td>
                    <td><a href="<?php echo $row['document_link_strategic_plan']; ?>" target="_blank">View Plan</a></td>
                    <td><a href="<?php echo $row['document_link_e_governance_report']; ?>" target="_blank">View Report</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c6_2_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c6_2_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No data available for Strategy Development and Deployment.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>