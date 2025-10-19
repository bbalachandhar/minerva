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

                <a href="<?php echo base_url('naac/c4_1_add'); ?>" class="btn btn-primary">Add New Entry</a>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Academic Year</th>
                            <th>Classrooms ICT Enabled (%)</th>
                            <th>Seminar Halls ICT Enabled (%)</th>
                            <th>Physical Facilities Description</th>
                            <th>Cultural/Sports Facilities</th>
                            <th>Facilities Audit Link</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($c4_1_data)): ?>
                            <?php foreach ($c4_1_data as $row): ?>
                                <tr>
                                    <td><?php echo $row['academic_year']; ?></td>
                                    <td><?php echo $row['classrooms_ict_enabled_percentage']; ?></td>
                                    <td><?php echo $row['seminar_halls_ict_enabled_percentage']; ?></td>
                                    <td><?php echo $row['physical_facilities_description']; ?></td>
                                    <td><?php echo $row['facilities_for_cultural_sports']; ?></td>
                                    <td><a href="<?php echo $row['document_link_facilities_audit']; ?>" target="_blank">View Audit</a></td>
                                    <td>
                                        <a href="<?php echo base_url('naac/c4_1_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="<?php echo base_url('naac/c4_1_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No data available for Physical Facilities.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>