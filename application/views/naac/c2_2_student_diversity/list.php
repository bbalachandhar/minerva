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

                <a href="<?php echo base_url('naac/c2_2_add'); ?>" class="btn btn-primary">Add New Entry</a>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Academic Year</th>
                            <th>Program Name</th>
                            <th>Learning Level Assessment Methods</th>
                            <th>Advanced Learner Programs</th>
                            <th>Slow Learner Programs</th>
                            <th>Support for Diverse Learners</th>
                            <th>Support Policy Link</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($c2_2_data)): ?>
                            <?php foreach ($c2_2_data as $row): ?>
                                <tr>
                                    <td><?php echo $row['academic_year']; ?></td>
                                    <td><?php echo $row['program_name']; ?></td>
                                    <td><?php echo $row['learning_level_assessment_methods']; ?></td>
                                    <td><?php echo $row['advanced_learner_programs']; ?></td>
                                    <td><?php echo $row['slow_learner_programs']; ?></td>
                                    <td><?php echo $row['support_for_diverse_learners']; ?></td>
                                    <td><a href="<?php echo $row['document_link_support_policy']; ?>" target="_blank">View Policy</a></td>
                                    <td>
                                        <a href="<?php echo base_url('naac/c2_2_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="<?php echo base_url('naac/c2_2_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">No data available for Catering to Student Diversity.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>