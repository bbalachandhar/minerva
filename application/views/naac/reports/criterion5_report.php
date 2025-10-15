<h1><?php echo $page_title; ?></h1>

<p>This report provides an overview of NAAC Criterion 5: Student Support and Progression.</p>

<div class="row">
    <div class="col-md-12">
        <form action="<?php echo base_url('naac_reports/criterion5_report'); ?>" method="get" class="form-inline mb-3">
            <label for="academic_year_filter" class="mr-2">Filter by Academic Year:</label>
            <input type="text" class="form-control mr-2" id="academic_year_filter" name="academic_year" value="<?php echo $this->input->get('academic_year'); ?>">
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<h2>5.1 Student Support</h2>
<?php if (!empty($c5_1_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Total Students Benefited from Scholarships</th>
                <th>Total Amount of Scholarships (Lakhs)</th>
                <th>Support Mechanisms Description</th>
                <th>Capacity Building and Skills Enhancement</th>
                <th>Scholarship Policy Link</th>
                <th>Support Services Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c5_1_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['total_students_benefited_scholarships']; ?></td>
                    <td><?php echo $row['total_amount_scholarships_lakhs']; ?></td>
                    <td><?php echo $row['support_mechanisms_description']; ?></td>
                    <td><?php echo $row['capacity_building_skills_enhancement']; ?></td>
                    <td><a href="<?php echo $row['document_link_scholarship_policy']; ?>" target="_blank">View Policy</a></td>
                    <td><a href="<?php echo $row['document_link_support_services']; ?>" target="_blank">View Services</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Student Support.</p>
<?php endif; ?>

<h2>5.2 Student Progression</h2>
<?php if (!empty($c5_2_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Program Name</th>
                <th>Total Outgoing Students</th>
                <th>Students Placed</th>
                <th>Students to Higher Education</th>
                <th>Students Qualified Competitive Exams</th>
                <th>Progression Facilitation Description</th>
                <th>Placement Report Link</th>
                <th>Higher Education Data Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c5_2_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['program_name']; ?></td>
                    <td><?php echo $row['total_outgoing_students']; ?></td>
                    <td><?php echo $row['students_placed']; ?></td>
                    <td><?php echo $row['students_to_higher_education']; ?></td>
                    <td><?php echo $row['students_qualified_competitive_exams']; ?></td>
                    <td><?php echo $row['progression_facilitation_description']; ?></td>
                    <td><a href="<?php echo $row['document_link_placement_report']; ?>" target="_blank">View Report</a></td>
                    <td><a href="<?php echo $row['document_link_higher_education_data']; ?>" target="_blank">View Data</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Student Progression.</p>
<?php endif; ?>

<h2>5.3 Student Participation and Activities</h2>
<?php if (!empty($c5_3_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Activity Name</th>
                <th>Activity Type</th>
                <th>Number of Students Participated</th>
                <th>Awards/Medals Won</th>
                <th>Promotion of Activities Description</th>
                <th>Activity Report Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c5_3_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['activity_name']; ?></td>
                    <td><?php echo $row['activity_type']; ?></td>
                    <td><?php echo $row['number_of_students_participated']; ?></td>
                    <td><?php echo $row['awards_medals_won']; ?></td>
                    <td><?php echo $row['promotion_of_activities_description']; ?></td>
                    <td><a href="<?php echo $row['document_link_activity_report']; ?>" target="_blank">View Report</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Student Participation and Activities.</p>
<?php endif; ?>

<h2>5.4 Alumni Engagement</h2>
<?php if (!empty($c5_4_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Alumni Association Registered</th>
                <th>Alumni Contribution Description</th>
                <th>Alumni Engagement Activities</th>
                <th>Alumni Report Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c5_4_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['alumni_association_registered']; ?></td>
                    <td><?php echo $row['alumni_contribution_description']; ?></td>
                    <td><?php echo $row['alumni_engagement_activities']; ?></td>
                    <td><a href="<?php echo $row['document_link_alumni_report']; ?>" target="_blank">View Report</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Alumni Engagement.</p>
<?php endif; ?>
