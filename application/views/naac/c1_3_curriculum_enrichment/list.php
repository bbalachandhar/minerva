<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c1_3_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Program Name</th>
            <th>Cross-Cutting Issues Integrated</th>
            <th>Value-Added Courses Offered</th>
            <th>Students Enrolled in Value-Added Courses</th>
            <th>Project/Field Work Details</th>
            <th>Value-Added Syllabus Link</th>
            <th>Project Reports Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c1_3_data)): ?>
            <?php foreach ($c1_3_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['program_name']; ?></td>
                    <td><?php echo $row['cross_cutting_issues_integrated']; ?></td>
                    <td><?php echo $row['value_added_courses_offered']; ?></td>
                    <td><?php echo $row['students_enrolled_value_added']; ?></td>
                    <td><?php echo $row['project_field_work_details']; ?></td>
                    <td><a href="<?php echo $row['document_link_value_added_syllabus']; ?>" target="_blank">View Syllabus</a></td>
                    <td><a href="<?php echo $row['document_link_project_reports']; ?>" target="_blank">View Reports</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c1_3_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c1_3_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">No data available for Curriculum Enrichment.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>