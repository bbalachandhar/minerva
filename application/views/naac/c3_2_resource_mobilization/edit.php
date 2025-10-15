<div class="content-wrapper" style="min-height: 408px;">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <form action="<?php echo site_url('naac/c3_2_edit/' . $entry['id']); ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
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
                                                <label for="teacher_name">Teacher Name</label><small class="req"> *</small>
                                                <input type="text" class="form-control" id="teacher_name" name="teacher_name" value="<?php echo set_value('teacher_name', $entry['teacher_name']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="project_title">Project Title</label><small class="req"> *</small>
                                                <input type="text" class="form-control" id="project_title" name="project_title" value="<?php echo set_value('project_title', $entry['project_title']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="funding_agency">Funding Agency</label>
                                                <input type="text" class="form-control" id="funding_agency" name="funding_agency" value="<?php echo set_value('funding_agency', $entry['funding_agency']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="amount_received_lakhs">Amount Received (in Lakhs)</label>
                                                <input type="number" step="0.01" class="form-control" id="amount_received_lakhs" name="amount_received_lakhs" value="<?php echo set_value('amount_received_lakhs', $entry['amount_received_lakhs']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="project_type">Project Type</label>
                                                <input type="text" class="form-control" id="project_type" name="project_type" value="<?php echo set_value('project_type', $entry['project_type']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="document_link_sanction_letter">Document Link (Sanction Letter)</label>
                                                <input type="url" class="form-control" id="document_link_sanction_letter" name="document_link_sanction_letter" value="<?php echo set_value('document_link_sanction_letter', $entry['document_link_sanction_letter']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right">Update</button>
                            <a href="<?php echo base_url('naac/c3_2_resource_mobilization'); ?>" class="btn btn-default pull-right mr-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>