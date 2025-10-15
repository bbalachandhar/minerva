<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c4_3_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Computer-Student Ratio</th>
            <th>Internet Bandwidth (Mbps)</th>
            <th>IT Policy Description</th>
            <th>E-Content Development Facilities</th>
            <th>Wi-Fi Availability Description</th>
            <th>IT Policy Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c4_3_data)): ?>
            <?php foreach ($c4_3_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['computer_student_ratio']; ?></td>
                    <td><?php echo $row['internet_bandwidth_mbps']; ?></td>
                    <td><?php echo $row['it_policy_description']; ?></td>
                    <td><?php echo $row['e_content_development_facilities']; ?></td>
                    <td><?php echo $row['wifi_availability_description']; ?></td>
                    <td><a href="<?php echo $row['document_link_it_policy']; ?>" target="_blank">View Policy</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c4_3_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c4_3_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No data available for IT Infrastructure.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>