<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c6_3_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Welfare Measures Description</th>
            <th>Teachers Received Financial Support</th>
            <th>Professional Development Programs Organized</th>
            <th>Teachers Undergoing FDP</th>
            <th>Performance Appraisal System</th>
            <th>Welfare Policy Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c6_3_data)): ?>
            <?php foreach ($c6_3_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['welfare_measures_description']; ?></td>
                    <td><?php echo $row['teachers_received_financial_support']; ?></td>
                    <td><?php echo $row['professional_development_programs_organized']; ?></td>
                    <td><?php echo $row['teachers_undergoing_fdp']; ?></td>
                    <td><?php echo $row['performance_appraisal_system']; ?></td>
                    <td><a href="<?php echo $row['document_link_welfare_policy']; ?>" target="_blank">View Policy</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c6_3_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c6_3_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No data available for Faculty Empowerment Strategies.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>