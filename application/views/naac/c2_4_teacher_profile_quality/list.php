<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c2_4_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Teacher Name</th>
            <th>Highest Qualification</th>
            <th>Years of Experience</th>
            <th>PhD Status</th>
            <th>Professional Development Activities</th>
            <th>CV Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c2_4_data)): ?>
            <?php foreach ($c2_4_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['teacher_name']; ?></td>
                    <td><?php echo $row['highest_qualification']; ?></td>
                    <td><?php echo $row['years_of_experience']; ?></td>
                    <td><?php echo $row['phd_status']; ?></td>
                    <td><?php echo $row['professional_development_activities']; ?></td>
                    <td><a href="<?php echo $row['document_link_cv']; ?>" target="_blank">View CV</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c2_4_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c2_4_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No data available for Teacher Profile and Quality.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>