<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c5_2_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Program Name</th>
            <th>Total Outgoing Students</th>
            <th>Students Placed</th>
            <th>Students to Higher Education</th>
            <th>Students Qualified Competitive Exams</th>
            <th>Progression Facilitation Description</th>
            <th>Placement Report Link</th>
            <th>Higher Education Data Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c5_2_data)): ?>
            <?php foreach ($c5_2_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['program_name']; ?></td>
                    <td><?php echo $row['total_outgoing_students']; ?></td>
                    <td><?php echo $row['students_placed']; ?></td>
                    <td><?php echo $row['students_to_higher_education']; ?></td>
                    <td><?php echo $row['students_qualified_competitive_exams']; ?></td>
                    <td><?php echo $row['progression_facilitation_description']; ?></td>
                    <td><a href="<?php echo $row['document_link_placement_report']; ?>" target="_blank">View Report</a></td>
                    <td><a href="<?php echo $row['document_link_higher_education_data']; ?>" target="_blank">View Data</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c5_2_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c5_2_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10">No data available for Student Progression.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>