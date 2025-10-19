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

                <a href="<?php echo base_url('naac/c3_5_add'); ?>" class="btn btn-primary">Add New Entry</a>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Academic Year</th>
                            <th>Consultant Name</th>
                            <th>Client Organization</th>
                            <th>Consultancy Area</th>
                            <th>Revenue Generated (Lakhs)</th>
                            <th>Report Link</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($c3_5_data)): ?>
                            <?php foreach ($c3_5_data as $row): ?>
                                <tr>
                                    <td><?php echo $row['academic_year']; ?></td>
                                    <td><?php echo $row['consultant_name']; ?></td>
                                    <td><?php echo $row['client_organization']; ?></td>
                                    <td><?php echo $row['consultancy_area']; ?></td>
                                    <td><?php echo $row['revenue_generated_lakhs']; ?></td>
                                    <td><a href="<?php echo $row['document_link_report']; ?>" target="_blank">View Report</a></td>
                                    <td>
                                        <a href="<?php echo base_url('naac/c3_5_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="<?php echo base_url('naac/c3_5_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No data available for Consultancy.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>