<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c3_7_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Partner Organization</th>
            <th>Type of Collaboration</th>
            <th>Purpose of Collaboration</th>
            <th>MoU Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c3_7_data)): ?>
            <?php foreach ($c3_7_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['partner_organization']; ?></td>
                    <td><?php echo $row['type_of_collaboration']; ?></td>
                    <td><?php echo $row['purpose_of_collaboration']; ?></td>
                    <td><a href="<?php echo $row['document_link_mou']; ?>" target="_blank">View MoU</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c3_7_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c3_7_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No data available for Collaboration.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>