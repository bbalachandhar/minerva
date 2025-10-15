<h1><?php echo $page_title; ?></h1>

<p>This report provides an overview of NAAC Criterion 6: Governance, Leadership and Management.</p>

<div class="row">
    <div class="col-md-12">
        <form action="<?php echo base_url('naac_reports/criterion6_report'); ?>" method="get" class="form-inline mb-3">
            <label for="academic_year_filter" class="mr-2">Filter by Academic Year:</label>
            <input type="text" class="form-control mr-2" id="academic_year_filter" name="academic_year" value="<?php echo $this->input->get('academic_year'); ?>">
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<h2>6.1 Vision, Mission and Leadership</h2>
<?php if (!empty($c6_1_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Governance Vision Mission Alignment</th>
                <th>Leadership Effectiveness Description</th>
                <th>Decentralization and Participative Management</th>
                <th>Vision Mission Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c6_1_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['governance_vision_mission_alignment']; ?></td>
                    <td><?php echo $row['leadership_effectiveness_description']; ?></td>
                    <td><?php echo $row['decentralization_participative_management']; ?></td>
                    <td><a href="<?php echo $row['document_link_vision_mission']; ?>" target="_blank">View Document</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Vision, Mission and Leadership.</p>
<?php endif; ?>

<h2>6.2 Strategy Development and Deployment</h2>
<?php if (!empty($c6_2_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Strategic Plan Description</th>
                <th>E-governance Implementation Areas</th>
                <th>Strategic Plan Link</th>
                <th>E-governance Report Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c6_2_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['strategic_plan_description']; ?></td>
                    <td><?php echo $row['e_governance_implementation_areas']; ?></td>
                    <td><a href="<?php echo $row['document_link_strategic_plan']; ?>" target="_blank">View Plan</a></td>
                    <td><a href="<?php echo $row['document_link_e_governance_report']; ?>" target="_blank">View Report</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Strategy Development and Deployment.</p>
<?php endif; ?>

<h2>6.3 Faculty Empowerment Strategies</h2>
<?php if (!empty($c6_3_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Welfare Measures Description</th>
                <th>Teachers Received Financial Support</th>
                <th>Professional Development Programs Organized</th>
                <th>Teachers Undergoing FDP</th>
                <th>Performance Appraisal System</th>
                <th>Welfare Policy Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c6_3_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['welfare_measures_description']; ?></td>
                    <td><?php echo $row['teachers_received_financial_support']; ?></td>
                    <td><?php echo $row['professional_development_programs_organized']; ?></td>
                    <td><?php echo $row['teachers_undergoing_fdp']; ?></td>
                    <td><?php echo $row['performance_appraisal_system']; ?></td>
                    <td><a href="<?php echo $row['document_link_welfare_policy']; ?>" target="_blank">View Policy</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Faculty Empowerment Strategies.</p>
<?php endif; ?>

<h2>6.4 Financial Management and Resource Mobilization</h2>
<?php if (!empty($c6_4_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Internal Audits Regularity</th>
                <th>External Audits Regularity</th>
                <th>Funds/Grants Received (Lakhs)</th>
                <th>Resource Mobilization Strategies</th>
                <th>Audit Reports Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c6_4_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['internal_audits_regularity']; ?></td>
                    <td><?php echo $row['external_audits_regularity']; ?></td>
                    <td><?php echo $row['funds_grants_received_lakhs']; ?></td>
                    <td><?php echo $row['resource_mobilization_strategies']; ?></td>
                    <td><a href="<?php echo $row['document_link_audit_reports']; ?>" target="_blank">View Reports</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Financial Management and Resource Mobilization.</p>
<?php endif; ?>

<h2>6.5 Internal Quality Assurance System (IQAS)</h2>
<?php if (!empty($c6_5_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>IQAC Initiatives Description</th>
                <th>Quality Assurance Initiatives</th>
                <th>IQAC Report Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c6_5_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['iqac_initiatives_description']; ?></td>
                    <td><?php echo $row['quality_assurance_initiatives']; ?></td>
                    <td><a href="<?php echo $row['document_link_iqac_report']; ?>" target="_blank">View Report</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Internal Quality Assurance System (IQAS).</p>
<?php endif; ?>
