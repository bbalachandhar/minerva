<div class="content-wrapper" style="min-height: 946px;">
    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?php echo $page_title; ?></h3>
            </div>
            <div class="box-body">
                <?php if ($this->session->flashdata('msg')): ?>
                    <div class="alert alert-success">
                        <?php echo $this->session->flashdata('msg'); ?>
                    </div>
                <?php endif; ?>

                <a href="<?php echo base_url('naac/c4_4_add'); ?>" class="btn btn-primary">Add New Entry</a>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Academic Year</th>
                            <th>Expenditure on Maintenance (Lakhs)</th>
                            <th>Maintenance Systems and Procedures</th>
                            <th>Utilization of Facilities</th>
                            <th>Audited Statements Link</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($c4_4_data)): ?>
                            <?php foreach ($c4_4_data as $row): ?>
                                <tr>
                                    <td><?php echo $row['academic_year']; ?></td>
                                    <td><?php echo $row['expenditure_on_maintenance_lakhs']; ?></td>
                                    <td><?php echo $row['maintenance_systems_procedures']; ?></td>
                                    <td><?php echo $row['utilization_of_facilities']; ?></td>
                                    <td><a href="<?php echo $row['document_link_audited_statements']; ?>" target="_blank">View Statements</a></td>
                                    <td>
                                        <a href="<?php echo base_url('naac/c4_4_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="<?php echo base_url('naac/c4_4_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No data available for Maintenance of Campus Infrastructure.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>