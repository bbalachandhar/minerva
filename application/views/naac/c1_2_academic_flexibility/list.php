<div class="content-wrapper" style="min-height: 946px;">
    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?php echo $page_title; ?></h3>
            </div>
            <div class="box-body">
                <?php if ($this->session->flashdata('msg')) : ?>
                    <div class="alert alert-success">
                        <?php echo $this->session->flashdata('msg'); ?>
                    </div>
                <?php endif; ?>

                <a href="<?php echo base_url('naac/c1_2_add'); ?>" class="btn btn-primary">Add New Entry</a>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Academic Year</th>
                            <th>Program Name</th>
                            <th>Elective Courses Offered</th>
                            <th>Interdisciplinary Courses Offered</th>
                            <th>Credit Transfer Details</th>
                            <th>Experiential Learning Details</th>
                            <th>Students Undertaking Internships</th>
                            <th>Electives Document Link</th>
                            <th>Internship Policy Link</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($c1_2_data)) : ?>
                            <?php foreach ($c1_2_data as $row) : ?>
                                <tr>
                                    <td><?php echo $row['academic_year']; ?></td>
                                    <td><?php echo $row['program_name']; ?></td>
                                    <td><?php echo $row['elective_courses_offered']; ?></td>
                                    <td><?php echo $row['interdisciplinary_courses_offered']; ?></td>
                                    <td><?php echo $row['credit_transfer_details']; ?></td>
                                    <td><?php echo $row['experiential_learning_details']; ?></td>
                                    <td><?php echo $row['students_undertaking_internships']; ?></td>
                                    <td><a href="<?php echo $row['document_link_electives']; ?>" target="_blank">View Electives</a></td>
                                    <td><a href="<?php echo $row['document_link_internship_policy']; ?>" target="_blank">View Policy</a></td>
                                    <td>
                                        <a href="<?php echo base_url('naac/c1_2_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="<?php echo base_url('naac/c1_2_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="10">No data available for Academic Flexibility.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>