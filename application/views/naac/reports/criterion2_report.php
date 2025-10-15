<h1><?php echo $page_title; ?></h1>

<p>This report provides an overview of NAAC Criterion 2: Teaching-Learning and Evaluation.</p>

<div class="row">
    <div class="col-md-12">
        <form action="<?php echo base_url('naac_reports/criterion2_report'); ?>" method="get" class="form-inline mb-3">
            <label for="academic_year_filter" class="mr-2">Filter by Academic Year:</label>
            <input type="text" class="form-control mr-2" id="academic_year_filter" name="academic_year" value="<?php echo $this->input->get('academic_year'); ?>">
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<h2>2.1 Student Enrolment and Profile</h2>
<?php if (!empty($c2_1_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Program Name</th>
                <th>Total Sanctioned Seats</th>
                <th>Total Students Admitted</th>
                <th>Students From Other States</th>
                <th>Students From Other Countries</th>
                <th>Reserved Category Seats Filled</th>
                <th>Admission Process Description</th>
                <th>Admission Policy Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c2_1_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['program_name']; ?></td>
                    <td><?php echo $row['total_sanctioned_seats']; ?></td>
                    <td><?php echo $row['total_students_admitted']; ?></td>
                    <td><?php echo $row['students_from_other_states']; ?></td>
                    <td><?php echo $row['students_from_other_countries']; ?></td>
                    <td><?php echo $row['reserved_category_seats_filled']; ?></td>
                    <td><?php echo $row['admission_process_description']; ?></td>
                    <td><a href="<?php echo $row['document_link_admission_policy']; ?>" target="_blank">View Policy</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Student Enrolment and Profile.</p>
<?php endif; ?>

<h2>2.2 Catering to Student Diversity</h2>
<?php if (!empty($c2_2_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Program Name</th>
                <th>Learning Level Assessment Methods</th>
                <th>Advanced Learner Programs</th>
                <th>Slow Learner Programs</th>
                <th>Support For Diverse Learners</th>
                <th>Support Policy Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c2_2_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['program_name']; ?></td>
                    <td><?php echo $row['learning_level_assessment_methods']; ?></td>
                    <td><?php echo $row['advanced_learner_programs']; ?></td>
                    <td><?php echo $row['slow_learner_programs']; ?></td>
                    <td><?php echo $row['support_for_diverse_learners']; ?></td>
                    <td><a href="<?php echo $row['document_link_support_policy']; ?>" target="_blank">View Policy</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Catering to Student Diversity.</p>
<?php endif; ?>

<h2>2.3 Teaching-Learning Process</h2>
<?php if (!empty($c2_3_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Program Name</th>
                <th>Course Code</th>
                <th>Teacher Name</th>
                <th>Teaching Methodologies Used</th>
                <th>ICT Tools Used</th>
                <th>Percentage of Teachers Using ICT</th>
                <th>Teaching Plan Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c2_3_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['program_name']; ?></td>
                    <td><?php echo $row['course_code']; ?></td>
                    <td><?php echo $row['teacher_name']; ?></td>
                    <td><?php echo $row['teaching_methodologies_used']; ?></td>
                    <td><?php echo $row['ict_tools_used']; ?></td>
                    <td><?php echo $row['percentage_teachers_using_ict']; ?></td>
                    <td><a href="<?php echo $row['document_link_teaching_plan']; ?>" target="_blank">View Plan</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Teaching-Learning Process.</p>
<?php endif; ?>

<h2>2.4 Teacher Profile and Quality</h2>
<?php if (!empty($c2_4_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Teacher Name</th>
                <th>Highest Qualification</th>
                <th>Years of Experience</th>
                <th>PhD Status</th>
                <th>Professional Development Activities</th>
                <th>CV Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c2_4_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['teacher_name']; ?></td>
                    <td><?php echo $row['highest_qualification']; ?></td>
                    <td><?php echo $row['years_of_experience']; ?></td>
                    <td><?php echo $row['phd_status']; ?></td>
                    <td><?php echo $row['professional_development_activities']; ?></td>
                    <td><a href="<?php echo $row['document_link_cv']; ?>" target="_blank">View CV</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Teacher Profile and Quality.</p>
<?php endif; ?>

<h2>2.5 Evaluation Process and Reforms</h2>
<?php if (!empty($c2_5_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Program Name</th>
                <th>Evaluation Reforms Description</th>
                <th>Transparency in Evaluation</th>
                <th>Grievance Redressal Mechanism</th>
                <th>Evaluation Policy Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c2_5_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['program_name']; ?></td>
                    <td><?php echo $row['evaluation_reforms_description']; ?></td>
                    <td><?php echo $row['transparency_in_evaluation']; ?></td>
                    <td><?php echo $row['grievance_redressal_mechanism']; ?></td>
                    <td><a href="<?php echo $row['document_link_evaluation_policy']; ?>" target="_blank">View Policy</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Evaluation Process and Reforms.</p>
<?php endif; ?>

<h2>2.6 Student Performance and Learning Outcome</h2>
<?php if (!empty($c2_6_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Program Name</th>
                <th>Course Code</th>
                <th>Student ID</th>
                <th>Grade/Percentage</th>
                <th>PO/CO Attainment Description</th>
                <th>Results Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c2_6_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['program_name']; ?></td>
                    <td><?php echo $row['course_code']; ?></td>
                    <td><?php echo $row['student_id']; ?></td>
                    <td><?php echo $row['grade_percentage']; ?></td>
                    <td><?php echo $row['po_co_attainment_description']; ?></td>
                    <td><a href="<?php echo $row['document_link_results']; ?>" target="_blank">View Results</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Student Performance and Learning Outcome.</p>
<?php endif; ?>

<h2>2.7 Student Satisfaction Survey</h2>
<?php if (!empty($c2_7_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Survey Methodology</th>
                <th>Total Students Enrolled</th>
                <th>Total Students Surveyed</th>
                <th>SSS Analysis Report</th>
                <th>Action Taken on SSS</th>
                <th>Survey Report Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c2_7_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['survey_methodology']; ?></td>
                    <td><?php echo $row['total_students_enrolled']; ?></td>
                    <td><?php echo $row['total_students_surveyed']; ?></td>
                    <td><?php echo $row['sss_analysis_report']; ?></td>
                    <td><?php echo $row['action_taken_on_sss']; ?></td>
                    <td><a href="<?php echo $row['document_link_survey_report']; ?>" target="_blank">View Report</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Student Satisfaction Survey.</p>
<?php endif; ?>
