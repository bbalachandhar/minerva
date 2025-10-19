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

                <a href="<?php echo base_url('naac/c3_4_add'); ?>" class="btn btn-primary">Add New Entry</a>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Academic Year</th>
                            <th>Author Name</th>
                            <th>Publication Title</th>
                            <th>Journal Name</th>
                            <th>UGC CARE List</th>
                            <th>Indexed In</th>
                            <th>Award Name</th>
                            <th>Awarding Agency</th>
                            <th>Publication Link</th>
                            <th>Award Link</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($c3_4_data)): ?>
                            <?php foreach ($c3_4_data as $row): ?>
                                <tr>
                                    <td><?php echo $row['academic_year']; ?></td>
                                    <td><?php echo $row['author_name']; ?></td>
                                    <td><?php echo $row['publication_title']; ?></td>
                                    <td><?php echo $row['journal_name']; ?></td>
                                    <td><?php echo $row['ugc_care_list']; ?></td>
                                    <td><?php echo $row['indexed_in']; ?></td>
                                    <td><?php echo $row['award_name']; ?></td>
                                    <td><?php echo $row['awarding_agency']; ?></td>
                                    <td><a href="<?php echo $row['document_link_publication']; ?>" target="_blank">View Publication</a></td>
                                    <td><a href="<?php echo $row['document_link_award']; ?>" target="_blank">View Award</a></td>
                                    <td>
                                        <a href="<?php echo base_url('naac/c3_4_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="<?php echo base_url('naac/c3_4_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11">No data available for Research Publications and Awards.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>