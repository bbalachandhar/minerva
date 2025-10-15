<h1><?php echo $page_title; ?></h1>

<p>This report provides an overview of NAAC Criterion 3: Research, Innovations and Extension.</p>

<div class="row">
    <div class="col-md-12">
        <form action="<?php echo base_url('naac_reports/criterion3_report'); ?>" method="get" class="form-inline mb-3">
            <label for="academic_year_filter" class="mr-2">Filter by Academic Year:</label>
            <input type="text" class="form-control mr-2" id="academic_year_filter" name="academic_year" value="<?php echo $this->input->get('academic_year'); ?>">
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<h2>3.1 Promotion of Research and Facilities</h2>
<?php if (!empty($c3_1_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Research Promotion Policy</th>
                <th>Research Facilities Description</th>
                <th>Policy Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c3_1_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['research_promotion_policy']; ?></td>
                    <td><?php echo $row['research_facilities_description']; ?></td>
                    <td><a href="<?php echo $row['document_link_policy']; ?>" target="_blank">View Policy</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Promotion of Research and Facilities.</p>
<?php endif; ?>

<h2>3.2 Resource Mobilization for Research</h2>
<?php if (!empty($c3_2_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Teacher Name</th>
                <th>Project Title</th>
                <th>Funding Agency</th>
                <th>Amount Received (Lakhs)</th>
                <th>Project Type</th>
                <th>Sanction Letter Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c3_2_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['teacher_name']; ?></td>
                    <td><?php echo $row['project_title']; ?></td>
                    <td><?php echo $row['funding_agency']; ?></td>
                    <td><?php echo $row['amount_received_lakhs']; ?></td>
                    <td><?php echo $row['project_type']; ?></td>
                    <td><a href="<?php echo $row['document_link_sanction_letter']; ?>" target="_blank">View Letter</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Resource Mobilization for Research.</p>
<?php endif; ?>

<h2>3.3 Innovation Ecosystem</h2>
<?php if (!empty($c3_3_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Innovation Ecosystem Description</th>
                <th>Number of Startups</th>
                <th>Incubation Policy Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c3_3_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['innovation_ecosystem_description']; ?></td>
                    <td><?php echo $row['number_of_startups']; ?></td>
                    <td><a href="<?php echo $row['document_link_incubation_policy']; ?>" target="_blank">View Policy</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Innovation Ecosystem.</p>
<?php endif; ?>

<h2>3.4 Research Publications and Awards</h2>
<?php if (!empty($c3_4_data)): ?>
    <table class="table table-bordered">
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
            </tr>
        </thead>
        <tbody>
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
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Research Publications and Awards.</p>
<?php endif; ?>

<h2>3.5 Consultancy</h2>
<?php if (!empty($c3_5_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Consultant Name</th>
                <th>Client Organization</th>
                <th>Consultancy Area</th>
                <th>Revenue Generated (Lakhs)</th>
                <th>Report Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c3_5_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['consultant_name']; ?></td>
                    <td><?php echo $row['client_organization']; ?></td>
                    <td><?php echo $row['consultancy_area']; ?></td>
                    <td><?php echo $row['revenue_generated_lakhs']; ?></td>
                    <td><a href="<?php echo $row['document_link_report']; ?>" target="_blank">View Report</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Consultancy.</p>
<?php endif; ?>

<h2>3.6 Extension Activities</h2>
<?php if (!empty($c3_6_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Activity Name</th>
                <th>Organizing Unit</th>
                <th>Number of Students Participated</th>
                <th>Number of Public Benefited</th>
                <th>Extension Activity Impact</th>
                <th>Report Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c3_6_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['activity_name']; ?></td>
                    <td><?php echo $row['organizing_unit']; ?></td>
                    <td><?php echo $row['number_of_students_participated']; ?></td>
                    <td><?php echo $row['number_of_public_benefited']; ?></td>
                    <td><?php echo $row['extension_activity_impact']; ?></td>
                    <td><a href="<?php echo $row['document_link_report']; ?>" target="_blank">View Report</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Extension Activities.</p>
<?php endif; ?>

<h2>3.7 Collaboration</h2>
<?php if (!empty($c3_7_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Partner Organization</th>
                <th>Type of Collaboration</th>
                <th>Purpose of Collaboration</th>
                <th>MOU Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c3_7_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['partner_organization']; ?></td>
                    <td><?php echo $row['type_of_collaboration']; ?></td>
                    <td><?php echo $row['purpose_of_collaboration']; ?></td>
                    <td><a href="<?php echo $row['document_link_mou']; ?>" target="_blank">View MOU</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Collaboration.</p>
<?php endif; ?>
