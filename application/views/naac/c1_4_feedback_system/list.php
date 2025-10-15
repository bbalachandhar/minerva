<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c1_4_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Stakeholder Type</th>
            <th>Feedback Mechanism</th>
            <th>Feedback Analysis Report</th>
            <th>Action Taken Report</th>
            <th>Feedback Forms Link</th>
            <th>Analysis Report Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c1_4_data)): ?>
            <?php foreach ($c1_4_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['stakeholder_type']; ?></td>
                    <td><?php echo $row['feedback_mechanism']; ?></td>
                    <td><?php echo $row['feedback_analysis_report']; ?></td>
                    <td><?php echo $row['action_taken_report']; ?></td>
                    <td><a href="<?php echo $row['document_link_feedback_forms']; ?>" target="_blank">View Forms</a></td>
                    <td><a href="<?php echo $row['document_link_analysis_report']; ?>" target="_blank">View Report</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c1_4_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c1_4_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No data available for Feedback System.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>