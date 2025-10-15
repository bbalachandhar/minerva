<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c5_4_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Alumni Association Registered</th>
            <th>Alumni Contribution Description</th>
            <th>Alumni Engagement Activities</th>
            <th>Alumni Report Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c5_4_data)): ?>
            <?php foreach ($c5_4_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['alumni_association_registered']; ?></td>
                    <td><?php echo $row['alumni_contribution_description']; ?></td>
                    <td><?php echo $row['alumni_engagement_activities']; ?></td>
                    <td><a href="<?php echo $row['document_link_alumni_report']; ?>" target="_blank">View Report</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c5_4_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c5_4_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No data available for Alumni Engagement.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>