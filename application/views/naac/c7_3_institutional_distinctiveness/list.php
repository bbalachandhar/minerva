<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c7_3_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Distinctive Area Description</th>
            <th>Distinctiveness Report Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c7_3_data)): ?>
            <?php foreach ($c7_3_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['distinctive_area_description']; ?></td>
                    <td><a href="<?php echo $row['document_link_distinctiveness_report']; ?>" target="_blank">View Report</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c7_3_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c7_3_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No data available for Institutional Distinctiveness.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>