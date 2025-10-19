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

                <a href="<?php echo base_url('naac/c1_1_add'); ?>" class="btn btn-primary">Add New Entry</a>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Academic Year</th>
                            <th>Program Name</th>
                            <th>Course Code</th>
                            <th>Course Title</th>
                            <th>PO/PSO/CO Relevance</th>
                            <th>Curriculum Development Process</th>
                            <th>Revision Date</th>
                            <th>Syllabus Link</th>
                            <th>Minutes Link</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($c1_1_data)): ?>
                            <?php foreach ($c1_1_data as $row): ?>
                                <tr>
                                    <td><?php echo $row['academic_year']; ?></td>
                                    <td><?php echo $row['program_name']; ?></td>
                                    <td><?php echo $row['course_code']; ?></td>
                                    <td><?php echo $row['course_title']; ?></td>
                                    <td><?php echo $row['po_pso_co_relevance']; ?></td>
                                    <td><?php echo $row['curriculum_development_process']; ?></td>
                                    <td><?php echo $row['curriculum_revision_date']; ?></td>
                                    <td><a href="<?php echo $row['document_link_syllabus']; ?>" target="_blank">View Syllabus</a></td>
                                    <td><a href="<?php echo $row['document_link_minutes']; ?>" target="_blank">View Minutes</a></td>
                                    <td>
                                        <a href="<?php echo base_url('naac/c1_1_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="<?php echo base_url('naac/c1_1_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10">No data available for Curriculum Design and Development.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>