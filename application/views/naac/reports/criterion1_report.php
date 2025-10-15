<h1><?php echo $page_title; ?></h1>

<p>This report provides an overview of NAAC Criterion 1: Curricular Aspects.</p>

<div class="row">
    <div class="col-md-12">
        <form action="<?php echo base_url('naac_reports/criterion1_report'); ?>" method="get" class="form-inline mb-3">
            <label for="academic_year_filter" class="mr-2">Filter by Academic Year:</label>
            <input type="text" class="form-control mr-2" id="academic_year_filter" name="academic_year" value="<?php echo $this->input->get('academic_year'); ?>">
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<h2>1.1 Curriculum Design and Development</h2>
<?php if (!empty($c1_1_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Program Name</th>
                <th>Course Code</th>
                <th>Course Title</th>
                <th>PO/PSO/CO Relevance</th>
                <th>Curriculum Development Process</th>
                <th>Revision Date</th>
                <th>Syllabus Link</th>
                <th>Minutes Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c1_1_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['program_name']; ?></td>
                    <td><?php echo $row['course_code']; ?></td>
                    <td><?php echo $row['course_title']; ?></td>
                    <td><?php echo $row['po_pso_co_relevance']; ?></td>
                    <td><?php echo $row['curriculum_development_process']; ?></td>
                    <td><?php echo $row['curriculum_revision_date']; ?></td>
                    <td><a href="<?php echo $row['document_link_syllabus']; ?>" target="_blank">View Syllabus</a></td>
                    <td><a href="<?php echo $row['document_link_minutes']; ?>" target="_blank">View Minutes</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Curriculum Design and Development.</p>
<?php endif; ?>

<h2>1.2 Academic Flexibility</h2>
<?php if (!empty($c1_2_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Program Name</th>
                <th>Elective Courses Offered</th>
                <th>Interdisciplinary Courses Offered</th>
                <th>Credit Transfer Details</th>
                <th>Experiential Learning Details</th>
                <th>Students Undertaking Internships</th>
                <th>Electives Document Link</th>
                <th>Internship Policy Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c1_2_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['program_name']; ?></td>
                    <td><?php echo $row['elective_courses_offered']; ?></td>
                    <td><?php echo $row['interdisciplinary_courses_offered']; ?></td>
                    <td><?php echo $row['credit_transfer_details']; ?></td>
                    <td><?php echo $row['experiential_learning_details']; ?></td>
                    <td><?php echo $row['students_undertaking_internships']; ?></td>
                    <td><a href="<?php echo $row['document_link_electives']; ?>" target="_blank">View Electives</a></td>
                    <td><a href="<?php echo $row['document_link_internship_policy']; ?>" target="_blank">View Policy</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Academic Flexibility.</p>
<?php endif; ?>

<h2>1.3 Curriculum Enrichment</h2>
<?php if (!empty($c1_3_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Program Name</th>
                <th>Cross-Cutting Issues Integrated</th>
                <th>Value-Added Courses Offered</th>
                <th>Students Enrolled in Value-Added Courses</th>
                <th>Project/Field Work Details</th>
                <th>Value-Added Syllabus Link</th>
                <th>Project Reports Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c1_3_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['program_name']; ?></td>
                    <td><?php echo $row['cross_cutting_issues_integrated']; ?></td>
                    <td><?php echo $row['value_added_courses_offered']; ?></td>
                    <td><?php echo $row['students_enrolled_value_added']; ?></td>
                    <td><?php echo $row['project_field_work_details']; ?></td>
                    <td><a href="<?php echo $row['document_link_value_added_syllabus']; ?>" target="_blank">View Syllabus</a></td>
                    <td><a href="<?php echo $row['document_link_project_reports']; ?>" target="_blank">View Reports</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Curriculum Enrichment.</p>
<?php endif; ?>

<h2>1.4 Feedback System</h2>
<?php if (!empty($c1_4_data)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Stakeholder Type</th>
                <th>Feedback Mechanism</th>
                <th>Feedback Analysis Report</th>
                <th>Action Taken Report</th>
                <th>Feedback Forms Link</th>
                <th>Analysis Report Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($c1_4_data as $row): ?>
                <tr>
                    <td><?php echo $row['academic_year']; ?></td>
                    <td><?php echo $row['stakeholder_type']; ?></td>
                    <td><?php echo $row['feedback_mechanism']; ?></td>
                    <td><?php echo $row['feedback_analysis_report']; ?></td>
                    <td><?php echo $row['action_taken_report']; ?></td>
                    <td><a href="<?php echo $row['document_link_feedback_forms']; ?>" target="_blank">View Forms</a></td>
                    <td><a href="<?php echo $row['document_link_analysis_report']; ?>" target="_blank">View Report</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available for Feedback System.</p>
<?php endif; ?>