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
                                    <label>Course</label>
                                    <select id="admission_course_id_filter" name="admission_course_id_filter" class="form-control">
                                        <option value="">All Courses</option>
                                        <?php if (!empty($ug_first_year_courses)): ?>
                                        <optgroup label="UG First Year">
                                            <?php foreach ($ug_first_year_courses as $c): ?>
                                            <option value="<?php echo (int)$c['id']; ?>"><?php echo html_escape($c['course_name']); ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                        <?php endif; ?>
                                        <?php if (!empty($ug_lateral_courses)): ?>
                                        <optgroup label="UG Lateral">
                                            <?php foreach ($ug_lateral_courses as $c): ?>
                                            <option value="<?php echo (int)$c['id']; ?>"><?php echo html_escape($c['course_name']); ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                        <?php endif; ?>
                                        <?php if (!empty($pg_first_year_courses)): ?>
                                        <optgroup label="PG First Year">
                                            <?php foreach ($pg_first_year_courses as $c): ?>
                                            <option value="<?php echo (int)$c['id']; ?>"><?php echo html_escape($c['course_name']); ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                        <?php endif; ?>
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
                            <div class="col-sm-6 col-md-2 col-lg-2">
                                <div class="form-group">
                                    <label>Lead Vendor</label>
                                    <select id="lead_vendor_id" name="lead_vendor_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach (($lead_vendor_list ?? []) as $vendor) { ?>
                                            <option value="<?php echo (int) $vendor['id']; ?>" <?php echo ((int) ($selected_lead_vendor ?? 0) === (int) $vendor['id']) ? 'selected' : ''; ?>>
                                                <?php echo html_escape($vendor['vendor_name'] . ' (' . $vendor['vendor_code'] . ')'); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-3 col-md-2 col-lg-2">
                                <div class="form-group">
                                    <label>Is Duplicate?</label>
                                    <select id="is_duplicate" name="is_duplicate" class="form-control">
                                        <option value="">All</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
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
                                    <label>Next Follow Up From</label>
                                    <input type="text" autocomplete="off" name="last_follow_up_from" class="form-control date" value="<?php echo isset($last_follow_up_from) ? $last_follow_up_from : ''; ?>">
                                </div>
                            </div>
                            <div class="col-sm-3 col-md-2 col-lg-2">
                                <div class="form-group">
                                    <label>Next Follow Up To</label>
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
                                        <a href="<?php echo site_url('admin/enquiry/bulk_meta_leads_upload'); ?>" class="btn btn-sm btn-info" style="margin-right: 6px;"><i class="fa fa-upload"></i> Bulk Upload</a>
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
                                                    <th>Course Applied</th>
                                                    <th><?php echo $this->lang->line('source'); ?></th>
                                                    <th>Lead Vendor</th>
                                                    <th>Dup. Source</th>
                                                    <th><?php echo $this->lang->line('enquiry_date'); ?></th>
                                                    <th><?php echo $this->lang->line('last_follow_up_date'); ?></th>
                                                    <th><?php echo $this->lang->line('next_follow_up_date'); ?></th>
                                                    <th><?php echo $this->lang->line('status'); ?></th>
                                                    <th class="text-right noExport1"><?php echo $this->lang->line('action'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table><!-- /.table -->
                                        <div style="text-align:right;font-weight:bold;padding:6px 4px;" id="enquiry-list-footer"></div>
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
                                            <input id="number" autocomplete="off" name="contact" placeholder="" type="text" class="form-control" value="<?php echo set_value('contact'); ?>" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" required />
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
                                            <input type="text" id="date" name="date" class="form-control date" data-date-end-date="+0d" value="<?php echo set_value('date', date($this->customlib->getSchoolDateFormat())); ?>" readonly="" required>
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
        } else if (!/^[0-9]{10}$/.test(contactVal)) {
            setAddEnquiryError('contact', 'Phone must be exactly 10 digits.');
            isValid = false;
        }
        if (!sourceVal) {
            setAddEnquiryError('source', 'Source is required.');
            isValid = false;
        }
        if (!dateVal) {
            setAddEnquiryError('date', 'Date is required.');
            isValid = false;
        } else {
            var selectedDate = moment(dateVal, [calendar_date_time_format, 'DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY-MM-DD'], true);
            if (!selectedDate.isValid()) {
                selectedDate = moment(dateVal);
            }

            if (selectedDate.isValid() && selectedDate.startOf('day').isAfter(moment().startOf('day'))) {
                setAddEnquiryError('date', 'Date cannot be a future date.');
                isValid = false;
            }
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

    $(document).on('input', '#formadd [name="contact"]', function () {
        var digitsOnly = ($(this).val() || '').replace(/\D/g, '').slice(0, 10);
        if ($(this).val() !== digitsOnly) {
            $(this).val(digitsOnly);
        }
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
        // Reload only the DataTable rows, not the whole page
        if (window.enquiryTable) {
            window.enquiryTable.ajax.reload(null, false);
        }
    }
</script>
<script type="text/javascript">
    // Active filter state — updated when the user clicks Search
    var dtFilters = {
        filter_status:                '<?php echo addslashes($status); ?>',
        filter_admission_course_id:   '',
        filter_source:                '<?php echo addslashes($source_select); ?>',
        filter_lead_vendor_id:        '<?php echo (int)$selected_lead_vendor; ?>',
        filter_is_duplicate:          '',
        filter_date_from:          '',
        filter_date_to:            '',
        filter_next_followup_from: '',
        filter_next_followup_to:   ''
    };

    $(document).ready(function () {
        window.enquiryTable = $("#enquirytable").DataTable({
            processing:  true,
            serverSide:  true,
            ajax: {
                url:  '<?php echo base_url("admin/enquiry/dtenquirylist"); ?>',
                type: 'GET',
                data: function (d) {
                    return $.extend({}, d, dtFilters);
                }
            },
            pageLength: 50,
            order: [[7, 'desc']],
            dom: "Bfrtip",
            columns: [
                { data: 0,  orderable: true  },   // ref_no
                { data: 1,  orderable: true  },   // name
                { data: 2,  orderable: true  },   // contact
                { data: 3,  orderable: false },   // course applied
                { data: 4,  orderable: true  },   // source
                { data: 5,  orderable: false },   // lead vendor
                { data: 6,  orderable: false },   // dup source
                { data: 7,  orderable: true  },   // enquiry date
                { data: 8,  orderable: true  },   // last follow-up
                { data: 9,  orderable: true  },   // next follow-up
                { data: 10, orderable: true  },   // status
                { data: 11, orderable: false },   // actions
                { data: 12, visible: false, orderable: false, searchable: false }, // followup colour
                { data: 13, visible: false, orderable: false, searchable: false }  // status colour
            ],
            createdRow: function (row, data) {
                if (data[12]) { $('td', row).eq(9).addClass(data[12]); }
                if (data[13]) { $('td', row).eq(10).addClass(data[13]); }
            },
            drawCallback: function () {
                var info = this.api().page.info();
                $('#enquiry-list-footer').text('Total Records: ' + info.recordsDisplay);
            },
            buttons: [
                {
                    extend:    'copyHtml5',
                    text:      '<i class="fa fa-files-o"></i>',
                    titleAttr: 'Copy (current page)',
                    title:     $('.download_label').html(),
                    exportOptions: { columns: ':visible:not(:last-child)' }
                },
                {
                    text:      '<i class="fa fa-file-excel-o"></i>',
                    titleAttr: 'Export to Excel (all filtered records)',
                    action: function () {
                        window.location = '<?php echo base_url("admin/enquiry/exportenquiry"); ?>?' + $.param(dtFilters);
                    }
                },
                {
                    text:      '<i class="fa fa-file-text-o"></i>',
                    titleAttr: 'Export to CSV (all filtered records)',
                    action: function () {
                        window.location = '<?php echo base_url("admin/enquiry/exportenquiry"); ?>?' + $.param(dtFilters);
                    }
                },
                {
                    extend:    'pdfHtml5',
                    text:      '<i class="fa fa-file-pdf-o"></i>',
                    titleAttr: 'PDF (current page)',
                    title:     $('.download_label').html(),
                    exportOptions: { columns: ':visible:not(:last-child)' }
                },
                {
                    extend:    'print',
                    text:      '<i class="fa fa-print"></i>',
                    titleAttr: 'Print (current page)',
                    title:     $('.download_label').html(),
                    customize: function (win) {
                        $(win.document.body).css('font-size', '10pt');
                        $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit');
                    },
                    exportOptions: { columns: ':visible:not(:last-child)' }
                },
                {
                    extend:        'colvis',
                    text:          '<i class="fa fa-columns"></i>',
                    titleAttr:     'Columns',
                    postfixButtons:['colvisRestore']
                }
            ]
        });

        // Intercept the filter form — update dtFilters and reload DT without a full page reload
        $('form[action$="admin/enquiry"]').on('submit', function (e) {
            e.preventDefault();
            dtFilters.filter_status                = $('[name="status"]').val()                      || '';
            dtFilters.filter_admission_course_id   = $('[name="admission_course_id_filter"]').val() || '';
            dtFilters.filter_source                = $('[name="source"]').val()                      || '';
            dtFilters.filter_lead_vendor_id     = $('[name="lead_vendor_id"]').val()      || '';
            dtFilters.filter_is_duplicate       = $('[name="is_duplicate"]').val()        || '';
            dtFilters.filter_date_from          = $('[name="from_date"]').val()           || '';
            dtFilters.filter_date_to            = $('[name="to_date"]').val()             || '';
            dtFilters.filter_next_followup_from = $('[name="last_follow_up_from"]').val() || '';
            dtFilters.filter_next_followup_to   = $('[name="last_follow_up_to"]').val()   || '';
            window.enquiryTable.ajax.reload();
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