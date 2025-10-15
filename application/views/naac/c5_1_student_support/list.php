<h1><?php echo $page_title; ?></h1>

<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('msg'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo base_url('naac/c5_1_add'); ?>" class="btn btn-primary">Add New Entry</a>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Academic Year</th>
            <th>Total Students Benefited from Scholarships</th>
            <th>Total Amount of Scholarships (Lakhs)</th>
            <th>Support Mechanisms Description</th>
            <th>Capacity Building & Skills Enhancement</th>
            <th>Scholarship Policy Link</th>
            <th>Support Services Link</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($c5_1_data)): ?>
            <?php foreach ($c5_1_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['total_students_benefited_scholarships']; ?></td>
                    <td><?php echo $row['total_amount_scholarships_lakhs']; ?></td>
                    <td><?php echo $row['support_mechanisms_description']; ?></td>
                    <td><?php echo $row['capacity_building_skills_enhancement']; ?></td>
                    <td><a href="<?php echo $row['document_link_scholarship_policy']; ?>" target="_blank">View Policy</a></td>
                    <td><a href="<?php echo $row['document_link_support_services']; ?>" target="_blank">View Services</a></td>
                    <td>
                        <a href="<?php echo base_url('naac/c5_1_edit/' . $row['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo base_url('naac/c5_1_delete/' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No data available for Student Support.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>