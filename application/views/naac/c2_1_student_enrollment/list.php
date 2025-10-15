<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c2_1_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Program Name</th>
            <th>Total Sanctioned Seats</th>
            <th>Total Students Admitted</th>
            <th>Students from Other States</th>
            <th>Students from Other Countries</th>
            <th>Reserved Category Seats Filled</th>
            <th>Admission Process Description</th>
            <th>Admission Policy Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c2_1_data)): ?>
            <?php foreach ($c2_1_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['program_name']; ?></td>
                    <td><?php echo $row['total_sanctioned_seats']; ?></td>
                    <td><?php echo $row['total_students_admitted']; ?></td>
                    <td><?php echo $row['students_from_other_states']; ?></td>
                    <td><?php echo $row['students_from_other_countries']; ?></td>
                    <td><?php echo $row['reserved_category_seats_filled']; ?></td>
                    <td><?php echo $row['admission_process_description']; ?></td>
                    <td><a href="<?php echo $row['document_link_admission_policy']; ?>" target="_blank">View Policy</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c2_1_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c2_1_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10">No data available for Student Enrolment and Profile.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>