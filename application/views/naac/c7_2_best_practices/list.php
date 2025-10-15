<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c7_2_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Best Practice 1 Title</th>
            <th>Best Practice 1 Description</th>
            <th>Best Practice 2 Title</th>
            <th>Best Practice 2 Description</th>
            <th>Best Practice 1 Link</th>
            <th>Best Practice 2 Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c7_2_data)): ?>
            <?php foreach ($c7_2_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['best_practice_title_1']; ?></td>
                    <td><?php echo $row['best_practice_description_1']; ?></td>
                    <td><?php echo $row['best_practice_title_2']; ?></td>
                    <td><?php echo $row['best_practice_description_2']; ?></td>
                    <td><a href="<?php echo $row['document_link_best_practice_1']; ?>" target="_blank">View Practice 1</a></td>
                    <td><a href="<?php echo $row['document_link_best_practice_2']; ?>" target="_blank">View Practice 2</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c7_2_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c7_2_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No data available for Best Practices.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>