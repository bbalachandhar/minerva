<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c5_3_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Activity Name</th>
            <th>Activity Type</th>
            <th>Number of Students Participated</th>
            <th>Awards/Medals Won</th>
            <th>Promotion of Activities Description</th>
            <th>Activity Report Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c5_3_data)): ?>
            <?php foreach ($c5_3_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['activity_name']; ?></td>
                    <td><?php echo $row['activity_type']; ?></td>
                    <td><?php echo $row['number_of_students_participated']; ?></td>
                    <td><?php echo $row['awards_medals_won']; ?></td>
                    <td><?php echo $row['promotion_of_activities_description']; ?></td>
                    <td><a href="<?php echo $row['document_link_activity_report']; ?>" target="_blank">View Report</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c5_3_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c5_3_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No data available for Student Participation and Activities.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>