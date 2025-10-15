<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c2_3_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Program Name</th>
            <th>Course Code</th>
            <th>Teacher Name</th>
            <th>Teaching Methodologies Used</th>
            <th>ICT Tools Used</th>
            <th>% Teachers Using ICT</th>
            <th>Teaching Plan Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c2_3_data)): ?>
            <?php foreach ($c2_3_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['program_name']; ?></td>
                    <td><?php echo $row['course_code']; ?></td>
                    <td><?php echo $row['teacher_name']; ?></td>
                    <td><?php echo $row['teaching_methodologies_used']; ?></td>
                    <td><?php echo $row['ict_tools_used']; ?></td>
                    <td><?php echo $row['percentage_teachers_using_ict']; ?></td>
                    <td><a href="<?php echo $row['document_link_teaching_plan']; ?>" target="_blank">View Plan</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c2_3_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c2_3_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">No data available for Teaching-Learning Process.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>