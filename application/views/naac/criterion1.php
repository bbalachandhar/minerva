<h1>NAAC Criterion 1: Curriculum Design and Development</h1>
<p>Details for Criterion 1 will be displayed here.</p>
<p>This section will include functionalities to manage curriculum-related data for NAAC compliance.</p>

<a href="<?php echo base_url('naac/add_criterion1'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Program Name</th>
            <th>Course Code</th>
            <th>Course Title</th>
            <th>Learning Outcomes</th>
            <th>Revision Date</th>
            <th>Feedback Mechanism</th>
            <th>Document Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($criterion1_data)): ?>
            <?php foreach ($criterion1_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['program_name']; ?></td>
                    <td><?php echo $row['course_code']; ?></td>
                    <td><?php echo $row['course_title']; ?></td>
                    <td><?php echo $row['learning_outcomes']; ?></td>
                    <td><?php echo $row['curriculum_revision_date']; ?></td>
                    <td><?php echo $row['stakeholder_feedback_mechanism']; ?></td>
                    <td><a href="<?php echo $row['document_link']; ?>" target="_blank">View Document</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/edit_criterion1/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/delete_criterion1/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">No data available for Criterion 1.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>