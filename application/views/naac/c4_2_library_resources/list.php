<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c4_2_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Number of Books</th>
            <th>Number of E-Journals</th>
            <th>Integrated Library Management System</th>
            <th>Library E-Resources Description</th>
            <th>Library Usage Details</th>
            <th>Library Report Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c4_2_data)): ?>
            <?php foreach ($c4_2_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['number_of_books']; ?></td>
                    <td><?php echo $row['number_of_e_journals']; ?></td>
                    <td><?php echo $row['integrated_library_management_system']; ?></td>
                    <td><?php echo $row['library_e_resources_description']; ?></td>
                    <td><?php echo $row['library_usage_details']; ?></td>
                    <td><a href="<?php echo $row['document_link_library_report']; ?>" target="_blank">View Report</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c4_2_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c4_2_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No data available for Library as a Learning Resource.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>