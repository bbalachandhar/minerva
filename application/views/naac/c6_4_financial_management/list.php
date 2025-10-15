<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c6_4_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Internal Audits Regularity</th>
            <th>External Audits Regularity</th>
            <th>Funds/Grants Received (Lakhs)</th>
            <th>Resource Mobilization Strategies</th>
            <th>Audit Reports Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c6_4_data)): ?>
            <?php foreach ($c6_4_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['internal_audits_regularity']; ?></td>
                    <td><?php echo $row['external_audits_regularity']; ?></td>
                    <td><?php echo $row['funds_grants_received_lakhs']; ?></td>
                    <td><?php echo $row['resource_mobilization_strategies']; ?></td>
                    <td><a href="<?php echo $row['document_link_audit_reports']; ?>" target="_blank">View Reports</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c6_4_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c6_4_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No data available for Financial Management and Resource Mobilization.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>