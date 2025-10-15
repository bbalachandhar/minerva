<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c3_3_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Innovation Ecosystem Description</th>
            <th>Number of Startups</th>
            <th>Incubation Policy Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c3_3_data)): ?>
            <?php foreach ($c3_3_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['innovation_ecosystem_description']; ?></td>
                    <td><?php echo $row['number_of_startups']; ?></td>
                    <td><a href="<?php echo $row['document_link_incubation_policy']; ?>" target="_blank">View Policy</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c3_3_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c3_3_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No data available for Innovation Ecosystem.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>