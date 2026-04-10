<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-users"></i> Assign Applicants</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/onlineexam/assign/' . $id); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> Back To Student Assignment
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
                        <h4>
                            <a href="#" data-toggle="popover" class="detail_popover"><?php echo $onlineexam->exam; ?></a>
                        </h4>
                        <p class="text-muted">Only submitted and not yet enrolled applicants are shown here.</p>

                        <form method="post" action="<?php echo site_url('admin/onlineexam/addapplicants') ?>" id="assign_applicant_form">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <input type="hidden" name="onlineexam_id" value="<?php echo $onlineexam->id; ?>">

                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><input style="vertical-align:text-top;" type="checkbox" id="select_all_applicants"/> <?php echo $this->lang->line('all'); ?></th>
                                            <th>Reference No</th>
                                            <th>Name</th>
                                            <th>Mobile</th>
                                            <th>Email</th>
                                            <th>Gender</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($resultlist)) { ?>
                                            <tr>
                                                <td colspan="6" class="text-danger text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                            </tr>
                                        <?php } else {
                                            foreach ($resultlist as $applicant) {
                                                $sel = ($applicant['onlineexam_student_admission_id'] != 0) ? "checked='checked'" : "";
                                                $fullname = $this->customlib->getFullName($applicant['firstname'], $applicant['middlename'], $applicant['lastname'], $sch_setting->middlename, $sch_setting->lastname);
                                        ?>
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="all_applicants[]" value="<?php echo $applicant['onlineexam_student_admission_id']; ?>">
                                                    <input class="applicant_checkbox" type="checkbox" name="applicants_id[]" value="<?php echo $applicant['id']; ?>" <?php echo $sel; ?>/>
                                                </td>
                                                <td><?php echo $applicant['reference_no']; ?></td>
                                                <td><?php echo $fullname; ?></td>
                                                <td><?php echo $applicant['mobileno']; ?></td>
                                                <td><?php echo $applicant['email']; ?></td>
                                                <td><?php echo $this->lang->line(strtolower($applicant['gender'])); ?></td>
                                            </tr>
                                        <?php } } ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if ($this->rbac->hasPrivilege('online_assign_view_student', 'can_edit')) { ?>
                                <button type="submit" class="allot-applicants btn btn-primary btn-sm pull-right" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>">
                                    <?php echo $this->lang->line('save'); ?>
                                </button>
                            <?php } ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    $("#select_all_applicants").change(function () {
        $(".applicant_checkbox").prop('checked', $(this).prop("checked"));
    });

    $('.applicant_checkbox').change(function () {
        if (false == $(this).prop("checked")) {
            $("#select_all_applicants").prop('checked', false);
        }
        if ($('.applicant_checkbox:checked').length == $('.applicant_checkbox').length) {
            $("#select_all_applicants").prop('checked', true);
        }
    });

    $("#assign_applicant_form").submit(function (e) {
        if (confirm("<?php echo $this->lang->line('are_you_sure'); ?>")) {
            var $btn = $('.allot-applicants');
            $.ajax({
                type: "POST",
                dataType: 'Json',
                url: $("#assign_applicant_form").attr('action'),
                data: $("#assign_applicant_form").serialize(),
                beforeSend: function () {
                    $btn.button('loading');
                },
                success: function (data) {
                    if (data.status == "fail") {
                        var message = "";
                        $.each(data.error, function (index, value) {
                            message += value;
                        });
                        errorMsg(message);
                    } else {
                        successMsg(data.message);
                    }
                    $btn.button('reset');
                },
                complete: function () {
                    $btn.button('reset');
                }
            });
        }
        e.preventDefault();
    });
</script>
