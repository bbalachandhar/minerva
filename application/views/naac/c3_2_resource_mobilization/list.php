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

                <a href="<?php echo base_url('naac/c3_2_add'); ?>" class="btn btn-primary">Add New Entry</a>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Academic Year</th>
                            <th>Teacher Name</th>
                            <th>Project Title</th>
                            <th>Funding Agency</th>
                            <th>Amount Received (Lakhs)</th>
                            <th>Project Type</th>
                            <th>Sanction Letter Link</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($c3_2_data)): ?>
                            <?php foreach ($c3_2_data as $row): ?>
                                <tr>
                                    <td><?php echo $row['academic_year']; ?></td>
                                    <td><?php echo $row['teacher_name']; ?></td>
                                    <td><?php echo $row['project_title']; ?></td>
                                    <td><?php echo $row['funding_agency']; ?></td>
                                    <td><?php echo $row['amount_received_lakhs']; ?></td>
                                    <td><?php echo $row['project_type']; ?></td>
                                    <td><a href="<?php echo $row['document_link_sanction_letter']; ?>" target="_blank">View Letter</a></td>
                                    <td>
                                        <a href="<?php echo base_url('naac/c3_2_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="<?php echo base_url('naac/c3_2_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">No data available for Resource Mobilization for Research.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>