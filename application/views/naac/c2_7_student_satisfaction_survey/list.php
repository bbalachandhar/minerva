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

                <a href="<?php echo base_url('naac/c2_7_add'); ?>" class="btn btn-primary">Add New Entry</a>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Academic Year</th>
                            <th>Survey Methodology</th>
                            <th>Total Students Enrolled</th>
                            <th>Total Students Surveyed</th>
                            <th>SSS Analysis Report</th>
                            <th>Action Taken on SSS</th>
                            <th>Survey Report Link</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($c2_7_data)): ?>
                            <?php foreach ($c2_7_data as $row): ?>
                                <tr>
                                    <td><?php echo $row['academic_year']; ?></td>
                                    <td><?php echo $row['survey_methodology']; ?></td>
                                    <td><?php echo $row['total_students_enrolled']; ?></td>
                                    <td><?php echo $row['total_students_surveyed']; ?></td>
                                    <td><?php echo $row['sss_analysis_report']; ?></td>
                                    <td><?php echo $row['action_taken_on_sss']; ?></td>
                                    <td><a href="<?php echo $row['document_link_survey_report']; ?>" target="_blank">View Report</a></td>
                                    <td>
                                        <a href="<?php echo base_url('naac/c2_7_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="<?php echo base_url('naac/c2_7_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">No data available for Student Satisfaction Survey.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>