<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-ioxhost"></i> <?php echo $this->lang->line('front_office'); ?></h1>
    </section>
    <style>
    .ml-20 { margin-left: 5rem; }
    #enquirytable tbody td.cell-alert-red { background-color: #c62828 !important; color: #ffffff !important; font-weight: 600; }
    #enquirytable tbody td.cell-alert-yellow { background-color: #f9a825 !important; color: #000000 !important; font-weight: 600; }
    #enquirytable tbody td.cell-alert-green { background-color: #2e7d32 !important; color: #ffffff !important; font-weight: 600; }
    </style>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="col-md-12">
                        <?php echo $this->session->flashdata('msg');
$this->session->unset_userdata('msg'); ?>
                    </div>
                    <form role="form" action="<?php echo site_url('admin/enquiry') ?>" method="post" class="">
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-6 col-md-2 col-lg-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('department'); ?></label>
                                    <select  id="department" name="department_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select') ?></option>
                                        <?php foreach ($department_list as $key => $value) {
    ?>
                                            <option <?php
if ($value["id"] == $selected_department) {
        echo "selected";
    }
    ?> value="<?php echo $value["id"] ?>"><?php echo $value["department_name"] ?></option>
                                            <?php }?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-2 col-lg-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('class'); ?></label>
                                    <select  id="class" name="class" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select') ?></option>
                                        <?php foreach ($class_list as $key => $value) {
    ?>
                                            <option <?php
if ($value["id"] == $selected_class) {
        echo "selected";
    }
    ?> value="<?php echo $value["id"] ?>"><?php echo $value["class"] ?></option>
                                            <?php }?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-2 col-lg-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('source'); ?></label>
                                    <select  id="source" name="source" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select') ?></option>
                                        <?php foreach ($sourcelist as $key => $value) {
    ?>
                                            <option <?php
if ($value["source"] == $source_select) {
        echo "selected";
    }
    ?> value="<?php echo $value["source"] ?>"><?php echo $value["source"] ?></option>
                                            <?php }?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('source'); ?></span>
                                </div>
                            </div>
                             <div class="col-sm-3 col-md-2 col-lg-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('enquiry_from_date'); ?></label>
                                        <input type="text" autocomplete="off" name="from_date" class="form-control  date"  value="<?php echo set_value('from_date') ?>">
                                        <span class="text-danger"><?php echo form_error('from_date'); ?></span>
                                    </div>
                            </div>
                            <div class="col-sm-3 col-md-2 col-lg-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('enquiry_to_date'); ?></label>
                                        <input type="text" autocomplete="off" name="to_date" class="form-control  date"  value="<?php echo set_value('to_date') ?>">
                                        <span class="text-danger"><?php echo form_error('to_date'); ?></span>
                                    </div>
                            </div>
                            <div class="col-sm-3 col-md-2 col-lg-2">
                                <div class="form-group">
                                    <label>Last Follow Up From</label>
                                    <input type="text" autocomplete="off" name="last_follow_up_from" class="form-control date" value="<?php echo isset($last_follow_up_from) ? $last_follow_up_from : ''; ?>">
                                </div>
                            </div>
                            <div class="col-sm-3 col-md-2 col-lg-2">
                                <div class="form-group">
                                    <label>Last Follow Up To</label>
                                    <input type="text" autocomplete="off" name="last_follow_up_to" class="form-control date" value="<?php echo isset($last_follow_up_to) ? $last_follow_up_to : ''; ?>">
                                </div>
                            </div>
                            <div class="col-sm-3 col-md-2 col-lg-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('status'); ?></label>
                                    <select  id="status" name="status" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select') ?></option>
                                        <option value="all" <?php
if ($status == "all") {
    echo "selected";
}
?>><?php echo $this->lang->line('all') ?></option>
                                                <?php foreach ($enquiry_status as $enkey => $envalue) {
    ?>
                                            <option <?php
if ($enkey == $status) {
        echo "selected";
    }
    ?> value="<?php echo $enkey ?>"><?php echo $envalue ?></option>
                                        <?php }?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('status'); ?></span>
                                </div>
                            </div>
                            <div class="col-sm-3 col-md-2 col-lg-2">
                                <div class="form-group pl10">
                                    <label class="displayblock opacity d-sm-none">&nbsp;</label>
                                    <button type="submit" name="search" value="search_filter" class="btn btn-primary smallbtn28 btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="ptt10">
                        <div class="bordertop">
                            <div class="box-header with-border">
                                <h3 class="box-title titlefix"> <?php echo $this->lang->line('admission_enquiry'); ?></h3>
                                <div class="box-tools pull-right">
                                    <?php if ($this->rbac->hasPrivilege('admission_enquiry', 'can_view')) {?>
                                        <a href="<?php echo site_url('admin/enquiry/bulk_meta_leads_upload'); ?>" class="btn btn-sm btn-info" style="margin-right: 6px;"><i class="fa fa-upload"></i> Bulk Upload - Meta Leads</a>
                                    <?php }?>
                                    <?php if ($this->rbac->hasPrivilege('admission_enquiry', 'can_add')) {?>
                                        <button type="button" class="btn btn-sm btn-primary openmodal" ><i class="fa fa-plus"></i> <?php echo $this->lang->line('add'); ?></button>
                                    <?php }?>
                                </div><!-- /.box-tools -->
                            </div><!-- /.box-header -->
                            <div class="box-body">
                                <div class="download_label"><?php echo $this->lang->line('admission_enquiry_list'); ?></div>
                                <div class="mailbox-messages">
                                    <div class="table-responsive overflow-visible-lg">
                                        <table class="table table-hover table-striped table-bordered" id="enquirytable">
                                            <thead>
                                                <tr>
                                                    <th>Reference No</th>
                                                    <th><?php echo $this->lang->line('name'); ?></th>
                                                    <th><?php echo $this->lang->line('phone'); ?></th>
                                                    <th><?php echo $this->lang->line('source'); ?></th>
                                                    <th><?php echo $this->lang->line('enquiry_date'); ?></th>
                                                    <th><?php echo $this->lang->line('last_follow_up_date'); ?></th>
                                                    <th><?php echo $this->lang->line('next_follow_up_date'); ?></th>
                                                    <th><?php echo $this->lang->line('status'); ?></th>
                                                    <th class="text-right noExport1"><?php echo $this->lang->line('action'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php

if (empty($enquiry_list)) {
    ?>
                                                    <?php
} else {
    foreach ($enquiry_list as $key => $value) {
        $current_date = date("Y-m-d");
        $next_followup_date = isset($value["next_date"]) ? trim((string) $value["next_date"]) : '';
        $display_next_date = $next_followup_date;
        if (empty($display_next_date)) {
            $display_next_date = isset($value["follow_up_date"]) ? $value["follow_up_date"] : '';
        }

        $status_key = strtolower(trim((string) ($value["status"] ?? '')));
        $followup_cell_class = '';
        $status_cell_class = '';
        if ($status_key === 'application_done') {
            $followup_cell_class = 'cell-alert-green';
            $status_cell_class = 'cell-alert-green';
        } elseif (!empty($display_next_date) && $display_next_date !== '0000-00-00') {
            if ($display_next_date === $current_date) {
                $followup_cell_class = 'cell-alert-yellow';
                $status_cell_class = 'cell-alert-yellow';
            } elseif ($display_next_date < $current_date && $status_key === 'active') {
                $followup_cell_class = 'cell-alert-red';
                $status_cell_class = 'cell-alert-red';
            }
        }
        ?>
                                                        <tr>
                                                            <td class="mailbox-name">
                                                                <?php echo !empty($value['ref_no']) ? $value['ref_no'] : date('Y') . str_pad($value['id'], 6, '0', STR_PAD_LEFT); ?>
                                                            </td>
                                                            <td class="mailbox-name"><?php echo $value['name']; ?> </td>
                                                            <td class="mailbox-name"><?php echo $value['contact']; ?> </td>
                                                            <td class="mailbox-name"><?php echo $value['source']; ?></td>
                                                            <td class="mailbox-name" data-order="<?php echo $value['date']; ?>"> <?php
if (!empty($value["date"])) {
            echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value['date']));
        }
        ?></td>
                                                            <td class="mailbox-name"> <?php
if (!empty($value["followupdate"])) {
            echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value['followupdate']));
        }
        ?></td>
                                                            <td class="mailbox-name <?php echo $followup_cell_class; ?>" data-order="<?php echo (!empty($display_next_date) && $display_next_date != '0000-00-00') ? $display_next_date : '9999-12-31'; ?>"> <?php
if (!empty($display_next_date) && $display_next_date != '0000-00-00') {
            echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($display_next_date));
        }
        ?></td>
                                                            <td class="<?php echo $status_cell_class; ?>"> <?php echo $enquiry_status[$value["status"]] ?></td>
                                                            <td class="mailbox-date text-right white-space-nowrap">
                                                                                                                                <?php if ($this->rbac->hasPrivilege('follow_up_admission_enquiry', 'can_view')) {?>
                                                                                                                                    <a class="btn btn-default btn-xs" onclick="follow_up('<?php echo $value['id']; ?>', '<?php echo $value['status']; ?>', '<?php echo $value['created_by']; ?>');"  data-target="#follow_up" data-toggle="modal"  title="<?php echo $this->lang->line('follow_up_admission_enquiry'); ?>">
                                                                                                                                        <i class="fa fa-phone"></i>
                                                                                                                                    </a>
                                                                                                                                    <a href="<?php echo site_url('publicadmissionform?email=' . $value['email'] . '&name=' . $value['name'] . '&mobileno=' . $value['contact'] . '&enquiry_id=' . $value['id']); ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="Create Admission" target="_blank">
                                                                                                                                        <i class="fa fa-user-plus"></i>
                                                                                                                                    </a>                                                                                                                                <?php }
                                                                ?>                                                                <?php if ($this->rbac->hasPrivilege('admission_enquiry', 'can_edit')) {?>
                                                                    <a  onclick="getRecord('<?php echo $value['id']; ?>', '<?php echo $value['status']; ?>')" class="btn btn-default btn-xs" data-target="#myModaledit" data-toggle="modal"   title="<?php echo $this->lang->line('edit'); ?>"><i class="fa fa-pencil"></i>
                                                                    </a>
                                                                <?php }
        ?>
                                                                <?php if ($this->rbac->hasPrivilege('admission_enquiry', 'can_delete')) {?>
                                                                    <a href="#" class="btn btn-default btn-xs" data-toggle="tooltip" title="" onclick="delete_enquiry('<?php echo $value["id"] ?>')" data-original-title="<?php echo $this->lang->line('delete'); ?>">
                                                                        <i class="fa fa-remove"></i>
                                                                    </a>
                                                                <?php }
        ?>
                                                            </td>
                                                        </tr>
                                                        <?php
}
}
?>
                                            </tbody>
                                        </table><!-- /.table -->
                                    </div>
                                </div><!-- /.mail-box-messages -->
                            </div><!-- /.box-body -->
                        </div>
                    </div>
                </div>
            </div>
    </section>
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-media-content">
                <div class="modal-header modal-media-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="box-title"> <?php echo $this->lang->line('admission_enquiry'); ?></h4>
                </div>
                <div class="modal-body pt0 pb0">
                <form id="formadd" method="post" class="ptt10">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('name'); ?></label><small class="req"> *</small>
                                            <input type="text" id="name_add" autocomplete="off" class="form-control" value="<?php echo set_value('name'); ?>" name="name" required>
                                            <span id="name_add_error" class="text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('phone'); ?></label><small class="req"> *</small> <small class="req"><span id="phone_error_message"></span></small>
                                            <input id="number" autocomplete="off" name="contact" placeholder="" type="text" class="form-control"  value="<?php echo set_value('contact'); ?>" required />
                                            <span id="contact_add_error" class="text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('email'); ?></label>
                                            <input type="text" value="<?php echo set_value('email'); ?>" name="email" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>State</label>
                                            <select name="state" id="state_add" class="form-control">
                                                <option value="">Select State</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>City</label>
                                            <select name="city" id="city_add" class="form-control">
                                                <option value="">Select City</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="email"><?php echo $this->lang->line('address'); ?></label>
                                            <textarea name="address" class="form-control" ><?php echo set_value('address'); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="email"><?php echo $this->lang->line('description'); ?></label>
                                            <textarea name="description" class="form-control" ><?php echo set_value('description'); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('date'); ?><small class="req"> *</small></label>
                                            <input type="text" id="date" name="date" class="form-control date" value="<?php echo set_value('date', date($this->customlib->getSchoolDateFormat())); ?>" readonly="" required>
                                            <span id="date_add_error" class="text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('next_follow_up_date'); ?><small class="req"> *</small></label>
                                            <input type="text" id="date_of_call" name="follow_up_date"class="form-control date" value="<?php echo set_value('follow_up_date', date($this->customlib->getSchoolDateFormat())); ?>" readonly="" required>
                                            <span id="follow_up_date_add_error" class="text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('assigned'); ?></label>
                                            <select name="assigned" class="form-control">
                                                <option value=""><?php echo $this->lang->line('select') ?></option>
                                                <?php foreach ($stff_list as $key => $stff_list_value) {?>
                                                    <option value="<?php echo $stff_list_value['id']; ?>" ><?php echo $this->customlib->getStaffFullName($stff_list_value['name'], $stff_list_value['surname'],  $stff_list_value['employee_id']); ?></option>
                                                <?php }
?>
                                            </select>
                                        </div><!--./form-group-->
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('source'); ?></label> <small class="req"> *</small>
                                            <select name="source" id="source_add" class="form-control" required>
                                                <option value=""><?php echo $this->lang->line('select') ?></option>
                                                <?php foreach ($sourcelist as $key => $value) {?>
                                                    <option value="<?php echo $value['source']; ?>"><?php echo $value['source']; ?></option>
                                                <?php }
?>
                                            </select>
                                            <span id="source_add_error" class="text-danger"></span>
                                        </div><!--./form-group-->
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('reference'); ?></label>
                                            <select name="reference" class="form-control">
                                                <option value=""><?php echo $this->lang->line('select') ?></option>
                                                <?php foreach ($Reference as $key => $value) {?>
                                                    <option value="<?php echo $value['reference']; ?>" <?php if (set_value('reference') == $value['reference']) {?>selected=""<?php }?>><?php echo $value['reference']; ?></option>
                                                <?php }
?>
                                            </select>
                                        </div><!--./form-group-->
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="pwd">Referencer Details</label>
                                            <input type="text" class="form-control" name="referencer_details" value="<?php echo set_value('referencer_details'); ?>">
                                        </div><!--./form-group-->
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="pwd">Course Type</label><small class="req"> *</small>
                                            <div>
                                                <label class="radio-inline">
                                                    <input type="radio" name="course_type" value="ug_first_year" required> UG First Year
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" name="course_type" value="ug_lateral"> UG Lateral
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" name="course_type" value="pg_first_year"> PG First Year
                                                </label>
                                            </div>
                                            <span id="course_type_add_error" class="text-danger"></span>
                                        </div><!--./form-group-->
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="pwd">Course</label><small class="req"> *</small>
                                            <select name="admission_course_id" id="admission_course_id_add" class="form-control" required>
                                                <option value="">Select Course Type First</option>
                                            </select>
                                            <span id="admission_course_id_add_error" class="text-danger"></span>
                                        </div><!--./form-group-->
                                    </div>
                                </div><!--./row-->
                        </div><!--./col-md-12-->
                    </div><!--./row-->
                    <div class="row">
                        <div class="box-footer col-md-12">
                            <button type="submit" class="btn btn-info pull-right" id="submit" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('save') ?></button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="myModaledit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-media-content">
                <div class="modal-header modal-media-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h4 class="box-title"><?php echo $this->lang->line('edit_admission_enquiry'); ?></h4>
                </div>
                <div class="modal-body pt0 pb0" id="getdetails">
                    <div id="alert_message">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="follow_up" tabindex="-1" role="dialog" aria-labelledby="follow_up">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-media-content">
                <div class="modal-header modal-media-header">
                    <button type="button" class="close" onclick="update()" data-dismiss="modal">&times;</button>
                    <h4 class="box-title"><?php echo $this->lang->line('follow_up_admission_enquiry'); ?></h4>
                </div>
                <div class="modal-body pt0 pb0 pr-xl-1" id="getdetails_follow_up">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function (e) {
        $('#myModal,#follow_up,#myModaledit').modal({
        backdrop: 'static',
        keyboard: false,
        show:false
        });
    });

    function clearAddEnquiryErrors() {
        $('#name_add_error').html('');
        $('#contact_add_error').html('');
        $('#source_add_error').html('');
        $('#date_add_error').html('');
        $('#follow_up_date_add_error').html('');
        $('#course_type_add_error').html('');
        $('#admission_course_id_add_error').html('');
    }

    function setAddEnquiryError(field, message) {
        var messageText = message ? $('<div>').html(message).text() : 'This field is required';
        if (field === 'name') {
            $('#name_add_error').html(messageText);
        } else if (field === 'contact') {
            $('#contact_add_error').html(messageText);
        } else if (field === 'source') {
            $('#source_add_error').html(messageText);
        } else if (field === 'date') {
            $('#date_add_error').html(messageText);
        } else if (field === 'follow_up_date') {
            $('#follow_up_date_add_error').html(messageText);
        } else if (field === 'course_type') {
            $('#course_type_add_error').html(messageText);
        } else if (field === 'admission_course_id') {
            $('#admission_course_id_add_error').html(messageText);
        }
    }

    function validateAddEnquiryForm() {
        var $form = $('#formadd');
        clearAddEnquiryErrors();

        var isValid = true;
        var nameVal = $.trim($form.find('[name="name"]').val());
        var contactVal = $.trim($form.find('[name="contact"]').val());
        var sourceVal = $.trim($form.find('[name="source"]').val());
        var dateVal = $.trim($form.find('[name="date"]').val());
        var followDateVal = $.trim($form.find('[name="follow_up_date"]').val());
        var courseTypeVal = $form.find('input[name="course_type"]:checked').val();
        var courseVal = $.trim($form.find('[name="admission_course_id"]').val());

        if (!nameVal) {
            setAddEnquiryError('name', 'Name is required.');
            isValid = false;
        }
        if (!contactVal) {
            setAddEnquiryError('contact', 'Phone is required.');
            isValid = false;
        }
        if (!sourceVal) {
            setAddEnquiryError('source', 'Source is required.');
            isValid = false;
        }
        if (!dateVal) {
            setAddEnquiryError('date', 'Date is required.');
            isValid = false;
        }
        if (!followDateVal) {
            setAddEnquiryError('follow_up_date', 'Next follow up date is required.');
            isValid = false;
        }
        if (!courseTypeVal) {
            setAddEnquiryError('course_type', 'Course type is required.');
            isValid = false;
        }
        if (!courseVal) {
            setAddEnquiryError('admission_course_id', 'Course is required.');
            isValid = false;
        }

        return isValid;
    }

    $(".openmodal").click(function(){
        $('#formadd').trigger("reset");
        clearAddEnquiryErrors();
        $('#admission_course_id_add').html('<option value="">Select Course Type First</option>');
         $("#myModal").modal();
    });
</script>
<script>
    $(document).ready(function () {
              moment.locale('en', {          week: { dow: start_week }
        });
     $('#enquiry_date').daterangepicker(
        {
            locale: {
                    format: calendar_date_time_format
                }
        });
    });

    $(document).on('input change', '#formadd [name="name"], #formadd [name="contact"], #formadd [name="source"], #formadd [name="date"], #formadd [name="follow_up_date"], #formadd [name="admission_course_id"]', function () {
        validateAddEnquiryForm();
    });

    $(document).on('change', '#formadd input[name="course_type"]', function () {
        validateAddEnquiryForm();
    });

    function getRecord(id, status) {
        $.ajax({
            url: '<?php echo base_url(); ?>admin/enquiry/details/' + id + '/' + status,
            success: function (result) {
                $('#getdetails').html(result);
            }
        });
    }

    function postRecord(id) {
        $.ajax({
            url: '<?php echo base_url(); ?>admin/enquiry/editpost/' + id,
            type: 'POST',
            data: $("#myForm1").serialize(),
            dataType: 'json',
            success: function (data) {
                if (data.status == "fail") {
                    var message = "";
                    $.each(data.error, function (index, value) {
                        message += value;
                    });
                    errorMsg(message);
                } else {
                    successMsg(data.message);
                    window.location.reload(true);
                }
            },
            error: function () {
                alert("<?php echo $this->lang->line('fail'); ?>");
            }
        });
    }

    $("#formadd").on('submit', (function (e) {
        e.preventDefault();
        var $this = $(this).find("button[type=submit]");

        if (!validateAddEnquiryForm()) {
            return false;
        }

        $.ajax({
            url: "<?php echo site_url("admin/enquiry/add/") ?>",
            type: "POST",
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                $this.button('loading');

            },
            success: function (res)
            {
                if (res.status == "fail") {
                    clearAddEnquiryErrors();
                    var hasFieldError = false;
                    $.each(res.error, function (index, value) {
                        if ($.trim($('<div>').html(value).text()) !== '') {
                            setAddEnquiryError(index, value);
                            hasFieldError = true;
                        }
                    });

                    if (!hasFieldError) {
                        errorMsg("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    }
                } else {
                    successMsg(res.message);
                    window.location.reload(true);
                }
            },
            error: function (xhr) { // if error occured
                alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                $this.button('reset');
            },
            complete: function () {
                $this.button('reset');
            }
        });
    }));

    function delete_enquiry(id) {
        if (confirm('<?php echo $this->lang->line('delete_confirm') ?>')) {
            $.ajax({
                url: '<?php echo base_url(); ?>admin/enquiry/delete/' + id,
                type: 'POST',
                dataType: 'json',
                success: function (data) {
                    if (data.status == "fail") {
                        var message = "";
                        $.each(data.error, function (index, value) {
                            message += value;
                        });
                        errorMsg(message);
                    } else {
                        successMsg(data.message);
                        window.location.reload(true);
                    }
                }
            })
        }
    }

    function follow_up(id, status, created_by) {
        $.ajax({
            url: '<?php echo base_url(); ?>admin/enquiry/follow_up/' + id + '/' + status+ '/' + created_by,
            success: function (data) {
                $('#getdetails_follow_up').html(data);
                $.ajax({
                    url: '<?php echo base_url(); ?>admin/enquiry/follow_up_list/' + id,
                    success: function (data) {
                        $('#timeline').html(data);
                    },
                    error: function () {
                        alert("<?php echo $this->lang->line('fail'); ?>");
                    }
                });
            },
            error: function () {
                alert("<?php echo $this->lang->line('fail'); ?>");
            }
        });
    }

    function update() {
        window.location.reload(true);
    }
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $("#enquirytable").DataTable({
            searching: true,
            paging: true,
            bSort: true,
            info: false,
            /* default ordering: next follow up date ascending */
            order: [[6, 'asc']],
            dom: "Bfrtip",
            buttons: [

                {
                    extend: 'copyHtml5',
                    text: '<i class="fa fa-files-o"></i>',
                    titleAttr: 'Copy',
                    title: $('.download_label').html(),
                    exportOptions: {
                        columns: ':visible'
                    }
                },

                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i>',
                    titleAttr: 'Excel',
                    title: $('.download_label').html(),
                    exportOptions: {
                        columns: ':visible'
                    }
                },

                {
                    extend: 'csvHtml5',
                    text: '<i class="fa fa-file-text-o"></i>',
                    titleAttr: 'CSV',
                    title: $('.download_label').html(),
                    exportOptions: {
                        columns: ':visible'
                    }
                },

                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i>',
                    titleAttr: 'PDF',
                    title: $('.download_label').html(),
                    exportOptions: {
                        columns: ':visible'

                    }
                },

                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i>',
                    titleAttr: 'Print',
                    title: $('.download_label').html(),
                    customize: function (win) {
                        $(win.document.body)
                                .css('font-size', '10pt');

                        $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                    },
                    exportOptions: {                    
                        columns: 'th:not(:last-child)'                    
                    },
                },

                {
                    extend: 'colvis',
                    text: '<i class="fa fa-columns"></i>',
                    titleAttr: 'Columns',
                    title: $('.download_label').html(),
                    postfixButtons: ['colvisRestore']
                },
            ]
        });
    });

    $('#number').blur(function(){
        $('#phone_error_message').html('');
        var phoneVal = $.trim($('#number').val());
        if (phoneVal === '') {
            return;
        }
        $.ajax({
                url: '<?php echo base_url(); ?>admin/enquiry/check_number',
                type: 'POST',
                data: {phone_number:$('#number').val()},
                dataType: 'json',
                success: function (data) {
                    if (data.status == "success") {
                       $('#phone_error_message').html('('+data.message+')');
                    }
                }
        })
    })

    $(document).on('change', '#department', function (e) {
        $('#class').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        var department_id = $(this).val();
        var base_url = '<?php echo base_url() ?>';
        if (department_id != "") {
            $.ajax({
                type: "POST",
                url: base_url + "report/getClassesByDepartment",
                data: {'department_id': department_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj)
                    {
                        var sel = "";
                        $('#class').append("<option value=" + obj.id + " " + sel + ">" + obj.class + "</option>");
                    });
                }
            });
        }
    });

    // Load state and city data for Add modal
    var statesData = [];
    $.ajax({
        url: '<?php echo base_url(); ?>backend/json-files/india_states_cities.json',
        dataType: 'json',
        success: function(data) {
            statesData = data.states;
            // Sort states alphabetically
            statesData.sort(function(a, b) {
                return a.name.localeCompare(b.name);
            });
            // Populate state dropdown
            $.each(statesData, function(index, state) {
                $('#state_add').append('<option value="' + state.name + '">' + state.name + '</option>');
            });
        }
    });

    // Handle state change for Add modal
    $(document).on('change', '#state_add', function() {
        var selectedState = $(this).val();
        $('#city_add').html('<option value="">Select City</option>');
        
        if (selectedState) {
            var state = statesData.find(function(s) {
                return s.name === selectedState;
            });
            
            if (state && state.cities) {
                // Sort cities alphabetically
                var sortedCities = state.cities.slice().sort();
                $.each(sortedCities, function(index, city) {
                    $('#city_add').append('<option value="' + city + '">' + city + '</option>');
                });
            }
        }
    });
    
    // Course level and course dropdown logic for Add modal
    var coursesData = {
        ug_first_year: <?php echo json_encode($ug_first_year_courses); ?>,
        ug_lateral: <?php echo json_encode($ug_lateral_courses); ?>,
        pg_first_year: <?php echo json_encode($pg_first_year_courses); ?>
    };
    
    $(document).on('change', 'input[name="course_type"]', function() {
        var selectedType = $(this).val();
        var courseSelect = $('#admission_course_id_add');
        
        courseSelect.html('<option value="">Select Course</option>');
        
        if (coursesData[selectedType]) {
            $.each(coursesData[selectedType], function(index, course) {
                courseSelect.append('<option value="' + course.id + '">' + course.course_name + '</option>');
            });
        }
    });
</script>