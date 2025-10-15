<h1><?php echo $page_title; ?></h1>

<p>This report provides an overview of NAAC Criterion 7: Institutional Values and Best Practices.</p>

<div class="row">
    <div class="col-md-12">
        <form action="<?php echo base_url('naac_reports/criterion7_report'); ?>" method="get" class="form-inline mb-3">
            <label for="academic_year_filter" class="mr-2">Filter by Academic Year:</label>
            <input type="text" class="form-control mr-2" id="academic_year_filter" name="academic_year" value="<?php echo $this->input->get('academic_year'); ?>">
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<h2>7.1 Institutional Values and Social Responsibilities</h2>
<?php if (!empty($c7_1_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Gender Equity Measures</th>
                <th>Disabled-Friendly Campus Description</th>
                <th>Inclusive Environment Initiatives</th>
                <th>Human Values and Ethics Activities</th>
                <th>Commemorative Events Details</th>
                <th>Alternate Energy and Conservation Details</th>
                <th>Waste Management Details</th>
                <th>Water Conservation Details</th>
                <th>Green Campus Initiatives</th>
                <th>Quality Audits on Environment and Energy</th>
                <th>Code of Conduct Details</th>
                <th>Gender Equity Policy Link</th>
                <th>Disabled-Friendly Policy Link</th>
                <th>Environmental Audit Link</th>
                <th>Code of Conduct Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c7_1_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['gender_equity_measures']; ?></td>
                    <td><?php echo $row['disabled_friendly_campus_description']; ?></td>
                    <td><?php echo $row['inclusive_environment_initiatives']; ?></td>
                    <td><?php echo $row['human_values_ethics_activities']; ?></td>
                    <td><?php echo $row['commemorative_events_details']; ?></td>
                    <td><?php echo $row['alternate_energy_conservation_details']; ?></td>
                    <td><?php echo $row['waste_management_details']; ?></td>
                    <td><?php echo $row['water_conservation_details']; ?></td>
                    <td><?php echo $row['green_campus_initiatives']; ?></td>
                    <td><?php echo $row['quality_audits_environment_energy']; ?></td>
                    <td><?php echo $row['code_of_conduct_details']; ?></td>
                    <td><a href="<?php echo $row['document_link_gender_equity_policy']; ?>" target="_blank">View Policy</a></td>
                    <td><a href="<?php echo $row['document_link_disabled_friendly_policy']; ?>" target="_blank">View Policy</a></td>
                    <td><a href="<?php echo $row['document_link_environmental_audit']; ?>" target="_blank">View Audit</a></td>
                    <td><a href="<?php echo $row['document_link_code_of_conduct']; ?>" target="_blank">View Code</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Institutional Values and Social Responsibilities.</p>
<?php endif; ?>

<h2>7.2 Best Practices</h2>
<?php if (!empty($c7_2_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Best Practice Title 1</th>
                <th>Best Practice Description 1</th>
                <th>Best Practice Title 2</th>
                <th>Best Practice Description 2</th>
                <th>Best Practice 1 Link</th>
                <th>Best Practice 2 Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c7_2_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['best_practice_title_1']; ?></td>
                    <td><?php echo $row['best_practice_description_1']; ?></td>
                    <td><?php echo $row['best_practice_title_2']; ?></td>
                    <td><?php echo $row['best_practice_description_2']; ?></td>
                    <td><a href="<?php echo $row['document_link_best_practice_1']; ?>" target="_blank">View Practice 1</a></td>
                    <td><a href="<?php echo $row['document_link_best_practice_2']; ?>" target="_blank">View Practice 2</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Best Practices.</p>
<?php endif; ?>

<h2>7.3 Institutional Distinctiveness</h2>
<?php if (!empty($c7_3_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Distinctive Area Description</th>
                <th>Distinctiveness Report Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c7_3_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['distinctive_area_description']; ?></td>
                    <td><a href="<?php echo $row['document_link_distinctiveness_report']; ?>" target="_blank">View Report</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Institutional Distinctiveness.</p>
<?php endif; ?>
