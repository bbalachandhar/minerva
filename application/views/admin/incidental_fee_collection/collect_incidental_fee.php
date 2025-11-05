<?php $this->load->view('layout/header'); ?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-money"></i> <?php echo $this->lang->line('fees_collection'); ?> <small><?php echo $this->lang->line('collect_incidental_fee'); ?></small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('collect_incidental_fee'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
                        <?php echo $this->customlib->get = $this->customlib->getCSRF(); ?>

                        <form action="<?php echo site_url('admin/collect_incidental_fee/searchStudent') ?>" method="post" accept-charset="utf-8" class="form-horizontal">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="class_id" class="col-sm-4 control-label"><?php echo $this->lang->line('class'); ?></label>
                                        <div class="col-sm-8">
                                            <select autofocus="" id="class_id" name="class_id" class="form-control" >
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                <?php foreach ($classes as $class) { ?>
                                                    <option value="<?php echo $class['id'] ?>" <?php echo set_select('class_id', $class['id'], (isset($class_id) && $class_id == $class['id']) ? TRUE : FALSE); ?>><?php echo $class['class'] ?></option>
                                                <?php } ?>
                                            </select>
                                            <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="section_id" class="col-sm-4 control-label"><?php echo $this->lang->line('section'); ?></label>
                                        <div class="col-sm-8">
                                            <select id="section_id" name="section_id" class="form-control" >
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                <?php if (isset($sections)) {
                                                    foreach ($sections as $section) { ?>
                                                        <option value="<?php echo $section['id'] ?>" <?php echo set_select('section_id', $section['id'], (isset($section_id) && $section_id == $section['id']) ? TRUE : FALSE); ?>><?php echo $section['section'] ?></option>
                                                    <?php } 
                                                } ?>
                                            </select>
                                            <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="search_text" class="col-sm-4 control-label">Search</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="search_text" name="search_text" class="form-control" value="<?php echo set_value('search_text'); ?>" placeholder="Search by name, admission no, etc.">
                                        </div>
                                    </div>
                                </div>
                                <!-- The session dropdown has been removed as per user request -->
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </form>

                        <?php if (isset($student_list) && !empty($student_list)) { ?>
                            <hr/>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table id="incidental_fee_table" class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                    <th><?php echo $this->lang->line('student_name'); ?></th>
                                                    <th><?php echo $this->lang->line('class'); ?></th>
                                                    <th><?php echo $this->lang->line('section'); ?></th>
                                                    <th><?php echo $this->lang->line('gender'); ?></th>
                                                    <th><?php echo $this->lang->line('father_name'); ?></th>
                                                    <th class="text-right no-print"><?php echo $this->lang->line('action'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($student_list as $student) { ?>
                                                    <tr>
                                                        <td><?php echo $student['admission_no']; ?></td>
                                                        <td><?php echo $student['firstname'] . " " . $student['lastname']; ?></td>
                                                        <td><?php echo $student['class']; ?></td>
                                                        <td><?php echo $student['section']; ?></td>
                                                        <td><?php echo $student['gender']; ?></td>
                                                        <td><?php echo $student['father_name']; ?></td>
                                                        <td class="mailbox-date pull-right no-print">
                                                            <button type="button" class="btn btn-default btn-xs collect_fee_btn" data-student_id="<?php echo $student['id']; ?>" data-session_id="<?php echo $session_id; ?>" data-toggle="tooltip" title="<?php echo $this->lang->line('collect_fee'); ?>">
                                                                <i class="fa fa-money"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Fee Collection Modal -->
<div class="modal fade" id="feeCollectionModal" tabindex="-1" role="dialog" aria-labelledby="feeCollectionModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="feeCollectionModalLabel"><?php echo $this->lang->line('collect_incidental_fee'); ?></h4>
            </div>
            <form action="<?php echo site_url('admin/collect_incidental_fee/index') ?>" method="post" accept-charset="utf-8" target="_blank">
                <div class="modal-body">
                    <div id="student_details_modal"></div>
                    <hr/>
                    <h4><?php echo $this->lang->line('outstanding_assignments'); ?></h4>
                    <div id="outstanding_assignments_list"></div>

                    <hr/>
                    <div class="form-group">
                        <label for="fee_type_id_modal"><?php echo $this->lang->line('fee_type'); ?></label>
                        <select id="fee_type_id_modal" name="fee_type_id" class="form-control" >
                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                            <?php foreach ($fee_types as $fee_type) { ?>
                                <option value="<?php echo $fee_type['id'] ?>"><?php echo $fee_type['title'] ?></option>
                            <?php } ?>
                        </select>
                        <span class="text-danger"><?php echo form_error('fee_type_id'); ?></span>
                    </div>
                    <div class="form-group">
                        <label for="amount_collected"><?php echo $this->lang->line('amount_collected'); ?></label>
                        <input id="amount_collected" name="amount_collected" type="number" class="form-control" />
                        <span class="text-danger"><?php echo form_error('amount_collected'); ?></span>
                    </div>