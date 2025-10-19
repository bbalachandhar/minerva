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

                <a href="<?php echo base_url('naac/c3_6_add'); ?>" class="btn btn-primary">Add New Entry</a>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Academic Year</th>
                            <th>Activity Name</th>
                            <th>Organizing Unit</th>
                            <th>Number of Students Participated</th>
                            <th>Number of Public Benefited</th>
                            <th>Extension Activity Impact</th>
                            <th>Report Link</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($c3_6_data)): ?>
                            <?php foreach ($c3_6_data as $row): ?>
                                <tr>
                                    <td><?php echo $row['academic_year']; ?></td>
                                    <td><?php echo $row['activity_name']; ?></td>
                                    <td><?php echo $row['organizing_unit']; ?></td>
                                    <td><?php echo $row['number_of_students_participated']; ?></td>
                                    <td><?php echo $row['number_of_public_benefited']; ?></td>
                                    <td><?php echo $row['extension_activity_impact']; ?></td>
                                    <td><a href="<?php echo $row['document_link_report']; ?>" target="_blank">View Report</a></td>
                                    <td>
                                        <a href="<?php echo base_url('naac/c3_6_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="<?php echo base_url('naac/c3_6_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">No data available for Extension Activities.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>