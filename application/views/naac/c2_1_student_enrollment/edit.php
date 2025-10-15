<div class="content-wrapper" style="min-height: 408px;">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <form action="<?php echo site_url('naac/c2_1_edit/' . $entry['id']); ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                        <div class="box-body">
                            <div class="bozero">
                                <h4 class="pagetitleh-whitebg"><?php echo $page_title; ?></h4>
                                <div class="around10">
                                    <?php if ($this->session->flashdata('msg')): ?>
                                        <div class="alert alert-success">
                                            <?php echo $this->session->flashdata('msg'); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php echo validation_errors(); ?>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="academic_year">Academic Year</label><small class="req"> *</small>
                                                <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year', $entry['academic_year']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="program_name">Program Name</label><small class="req"> *</small>
                                                <input type="text" class="form-control" id="program_name" name="program_name" value="<?php echo set_value('program_name', $entry['program_name']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="total_sanctioned_seats">Total Sanctioned Seats</label>
                                                <input type="number" class="form-control" id="total_sanctioned_seats" name="total_sanctioned_seats" value="<?php echo set_value('total_sanctioned_seats', $entry['total_sanctioned_seats']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="total_students_admitted">Total Students Admitted</label>
                                                <input type="number" class="form-control" id="total_students_admitted" name="total_students_admitted" value="<?php echo set_value('total_students_admitted', $entry['total_students_admitted']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="students_from_other_states">Students from Other States</label>
                                                <input type="number" class="form-control" id="students_from_other_states" name="students_from_other_states" value="<?php echo set_value('students_from_other_states', $entry['students_from_other_states']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="students_from_other_countries">Students from Other Countries</label>
                                                <input type="number" class="form-control" id="students_from_other_countries" name="students_from_other_countries" value="<?php echo set_value('students_from_other_countries', $entry['students_from_other_countries']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="reserved_category_seats_filled">Reserved Category Seats Filled</label>
                                                <input type="number" class="form-control" id="reserved_category_seats_filled" name="reserved_category_seats_filled" value="<?php echo set_value('reserved_category_seats_filled', $entry['reserved_category_seats_filled']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="admission_process_description">Admission Process Description</label>
                                                <textarea class="form-control" id="admission_process_description" name="admission_process_description"><?php echo set_value('admission_process_description', $entry['admission_process_description']); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="document_link_admission_policy">Document Link (Admission Policy)</label>
                                                <input type="url" class="form-control" id="document_link_admission_policy" name="document_link_admission_policy" value="<?php echo set_value('document_link_admission_policy', $entry['document_link_admission_policy']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right">Update</button>
                            <a href="<?php echo base_url('naac/c2_1_student_enrollment'); ?>" class="btn btn-default pull-right mr-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>