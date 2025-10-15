<h1><?php echo $page_title; ?></h1>

<p>This report provides an overview of NAAC Criterion 4: Infrastructure and Learning Resources.</p>

<div class="row">
    <div class="col-md-12">
        <form action="<?php echo base_url('naac_reports/criterion4_report'); ?>" method="get" class="form-inline mb-3">
            <label for="academic_year_filter" class="mr-2">Filter by Academic Year:</label>
            <input type="text" class="form-control mr-2" id="academic_year_filter" name="academic_year" value="<?php echo $this->input->get('academic_year'); ?>">
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<h2>4.1 Physical Facilities</h2>
<?php if (!empty($c4_1_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Classrooms ICT Enabled (%)</th>
                <th>Seminar Halls ICT Enabled (%)</th>
                <th>Physical Facilities Description</th>
                <th>Facilities for Cultural and Sports</th>
                <th>Facilities Audit Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c4_1_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['classrooms_ict_enabled_percentage']; ?></td>
                    <td><?php echo $row['seminar_halls_ict_enabled_percentage']; ?></td>
                    <td><?php echo $row['physical_facilities_description']; ?></td>
                    <td><?php echo $row['facilities_for_cultural_sports']; ?></td>
                    <td><a href="<?php echo $row['document_link_facilities_audit']; ?>" target="_blank">View Audit</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Physical Facilities.</p>
<?php endif; ?>

<h2>4.2 Library as a Learning Resource</h2>
<?php if (!empty($c4_2_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Number of Books</th>
                <th>Number of e-Journals</th>
                <th>Integrated Library Management System</th>
                <th>Library e-Resources Description</th>
                <th>Library Usage Details</th>
                <th>Library Report Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c4_2_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['number_of_books']; ?></td>
                    <td><?php echo $row['number_of_e_journals']; ?></td>
                    <td><?php echo $row['integrated_library_management_system']; ?></td>
                    <td><?php echo $row['library_e_resources_description']; ?></td>
                    <td><?php echo $row['library_usage_details']; ?></td>
                    <td><a href="<?php echo $row['document_link_library_report']; ?>" target="_blank">View Report</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Library as a Learning Resource.</p>
<?php endif; ?>

<h2>4.3 IT Infrastructure</h2>
<?php if (!empty($c4_3_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Computer Student Ratio</th>
                <th>Internet Bandwidth (Mbps)</th>
                <th>IT Policy Description</th>
                <th>E-content Development Facilities</th>
                <th>WiFi Availability Description</th>
                <th>IT Policy Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c4_3_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['computer_student_ratio']; ?></td>
                    <td><?php echo $row['internet_bandwidth_mbps']; ?></td>
                    <td><?php echo $row['it_policy_description']; ?></td>
                    <td><?php echo $row['e_content_development_facilities']; ?></td>
                    <td><?php echo $row['wifi_availability_description']; ?></td>
                    <td><a href="<?php echo $row['document_link_it_policy']; ?>" target="_blank">View Policy</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for IT Infrastructure.</p>
<?php endif; ?>

<h2>4.4 Maintenance of Campus Infrastructure</h2>
<?php if (!empty($c4_4_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Expenditure on Maintenance (Lakhs)</th>
                <th>Maintenance Systems and Procedures</th>
                <th>Utilization of Facilities</th>
                <th>Audited Statements Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c4_4_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['expenditure_on_maintenance_lakhs']; ?></td>
                    <td><?php echo $row['maintenance_systems_procedures']; ?></td>
                    <td><?php echo $row['utilization_of_facilities']; ?></td>
                    <td><a href="<?php echo $row['document_link_audited_statements']; ?>" target="_blank">View Statements</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Maintenance of Campus Infrastructure.</p>
<?php endif; ?>
