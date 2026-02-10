<style type="text/css">
    @media print {

        .no-print,
        .no-print * {
            display: none !important;
        }
    }
</style>
<div class="content-wrapper" style="min-height: 946px;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-calendar-check-o"></i> <?php //echo $this->lang->line('attendance'); 
                                                    ?> <small> <?php //echo $this->lang->line('by_date1'); 
                                                                ?></small>
        </h1>
    </section>
    <section class="content">
        <?php $this->load->view('attendencereports/_attendance'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form id='form1' action="<?php echo site_url('attendencereports/staffdaywiseattendancereport') ?>" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('role'); ?></label>
                                        <select id="role" name="role" class="form-control">
                                            <option value="select"><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($role as $role_key => $value) {
                                            ?>
                                                <option value="<?php echo $value["type"] ?>" <?php
                                                                                                if ($role_selected == $value["type"]) {
                                                                                                    echo "selected =selected";
                                                                                                }
                                                                                                ?>><?php echo $value["type"]; ?></option>
                                            <?php
                                                $count++;
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('role'); ?></span>
                                    </div>
                                </div>


                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">From Date</label>
                                        <input name="from_date" placeholder="" type="text" class="form-control date" value="<?php echo set_value('from_date', date($this->customlib->getSchoolDateFormat())); ?>" readonly="readonly" />
                                        <span class="text-danger"><?php echo form_error('from_date'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">To Date</label>
                                        <input name="to_date" placeholder="" type="text" class="form-control date" value="<?php echo set_value('to_date', date($this->customlib->getSchoolDateFormat())); ?>" readonly="readonly" />
                                        <span class="text-danger"><?php echo form_error('to_date'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"> <?php echo $this->lang->line('source'); ?></label>
                                        <select id="attendance_mode" name="attendance_mode" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <option value="1" <?php echo set_select('attendance_mode', 1, (set_value('attendance_mode') == 1) ? TRUE : FALSE); ?>><?php echo $this->lang->line('manual'); ?></option>
                                            <option value="2" <?php echo set_select('attendance_mode', 2, (set_value('attendance_mode') == 2) ? TRUE : FALSE); ?>><?php echo $this->lang->line('qrcode') . " / " . $this->lang->line('barcode'); ?></option>
                                            <option value="3" <?php echo set_select('attendance_mode', 3, (set_value('attendance_mode') == 3) ? TRUE : FALSE); ?>><?php echo $this->lang->line('biometric'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('attendance_mode'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" name="search" value="search" class="btn btn-primary btn-sm pull-right checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <?php
                    if ($this->module_lib->hasActive('student_attendance')) {

                        if (isset($resultlist)) {
                    ?>
                            <div class="" id="attendencelist">
                                <div class="box-header ptbnull"></div>
                                <div class="box-header with-border">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $this->lang->line('staff_day_wise_attendance_report'); ?> </h3>
                                        </div>

                                    </div>
                                </div>
                                <div class="box-body table-responsive">
                                    <?php
                                    if (!empty($resultlist)) {
                                    ?>
                                        <div class="mailbox-controls">
                                            <div class="pull-right">
                                            </div>
                                        </div>
                                        <div class="download_label"><?php echo $this->lang->line('staff_day_wise_attendance_report'); ?></div>
                                        <div class="alert alert-info no-print" style="margin-top: 10px;">
                                            <strong><i class="fa fa-info-circle"></i> <?php echo $this->lang->line('color_legend'); ?>:</strong>
                                            <span style="display: inline-block; margin-left: 10px; padding: 3px 8px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 3px;"><?php echo $this->lang->line('present'); ?></span>
                                            <span style="display: inline-block; margin-left: 10px; padding: 3px 8px; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 3px;"><?php echo $this->lang->line('late'); ?></span>
                                            <span style="display: inline-block; margin-left: 10px; padding: 3px 8px; background-color: #cce5ff; border: 1px solid #66afe9; border-radius: 3px;"><?php echo $this->lang->line('permission'); ?></span>
                                            <span style="display: inline-block; margin-left: 10px; padding: 3px 8px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 3px;"><?php echo $this->lang->line('absent'); ?></span>
                                            <span style="display: inline-block; margin-left: 10px; padding: 3px 8px; background-color: #e2e3e5; border: 1px solid #d6d8db; border-radius: 3px;"><?php echo $this->lang->line('half_day'); ?></span>
                                        </div>
                                        <table class="table table-hover table-striped example">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th><?php echo $this->lang->line('staff_id'); ?></th>
                                                    <th><?php echo $this->lang->line('role'); ?></th>
                                                    <th><?php echo $this->lang->line('name'); ?></th>
                                                    <th><?php echo $this->lang->line('date'); ?></th>
                                                    <th width="10%" class="text text-center"><?php echo $this->lang->line('attendance'); ?></th>
                                                    <th><?php echo $this->lang->line('punch_in'); ?></th>
                                                    <th><?php echo $this->lang->line('punch_out'); ?></th>
                                                    <th><?php echo $this->lang->line('total_hours'); ?></th>
                                                    <?php
                                                    if ($sch_setting->staff_biometric) {
                                                    ?>
                                                        <th><?php echo $this->lang->line('source'); ?></th>
                                                    <?php
                                                    }
                                                    ?>

                                                </tr>
                                            </thead>
                                            <tbody>

                                                <?php
                                                $row_count = 1;
                                                foreach ($resultlist as $key => $value) {

                                                ?>
                                                    <tr>
                                                        <td>

                                                            <?php echo $row_count; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $value['employee_id']; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $value['user_type']; ?>
                                                        </td>
                                                        <td>

                                                            <?php echo $value['name'] . " " . $value['surname']; ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            if (!empty($value['date']) && $value['date'] != 'xxx') {
                                                                echo date($this->customlib->getSchoolDateFormat(), strtotime($value['date']));
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="text text-center">
                                                            <?php

                                                            if (IsNullOrEmptyString($value['staff_attendance_type_id'])) {

                                                            ?>
                                                                <span class="label label-danger"><?php echo $this->lang->line('n_a'); ?> </span>
                                                            <?php

                                                            } else {
                                                            ?>
                                                                <span class="<?php echo $value['long_name_style']; ?>"><?php echo $this->lang->line(strtolower($value['long_lang_name'])); ?></span>
                                                            <?php

                                                            }
                                                            ?>
                                                        </td>
                                                        <td style="<?php
                                                            // Color code based on both attendance status AND actual in time
                                                            $bg_color = '';
                                                            $key = isset($value['key']) ? strtoupper(trim($value['key'])) : '';
                                                            
                                                            // Only apply color if attendance is marked
                                                            if (!empty($value['in_time'])) {
                                                                // Get role-based attendance settings
                                                                $role_id = isset($value['role_id']) ? $value['role_id'] : null;
                                                                $is_late = false;
                                                                
                                                                // Check if this staff arrived late based on their role's "Present" time configuration
                                                                if ($role_id && !empty($attendance_settings[$role_id][1])) {
                                                                    $present_time_to = strtotime($attendance_settings[$role_id][1]['to']);
                                                                    $in_time = strtotime($value['in_time']);
                                                                    if ($in_time > $present_time_to) {
                                                                        $is_late = true;
                                                                    }
                                                                }
                                                                
                                                                // Apply colors based on attendance status or late arrival
                                                                if ($is_late && in_array($key, ['P', 'FHL', 'SHL', ''])) {
                                                                    // Late arrival - yellow background
                                                                    $bg_color = 'background-color: #fff3cd;';
                                                                } elseif (in_array($key, ['FHL', 'SHL'])) {
                                                                    $bg_color = 'background-color: #fff3cd;'; // Yellow for late
                                                                } elseif (in_array($key, ['FHP', 'SHP'])) {
                                                                    $bg_color = 'background-color: #cce5ff;'; // Blue for permission
                                                                } elseif (in_array($key, ['A', 'FHA', 'SHA'])) {
                                                                    $bg_color = 'background-color: #f8d7da;'; // Red for absent
                                                                } elseif ($key == 'HD') {
                                                                    $bg_color = 'background-color: #e2e3e5;'; // Gray for half day
                                                                } elseif ($key == 'P') {
                                                                    $bg_color = 'background-color: #d4edda;'; // Green for present
                                                                }
                                                            }
                                                            echo $bg_color;
                                                            ?>">
                                                            <?php
                                                            if (!empty($value['in_time'])) {
                                                                echo date('h:i A', strtotime($value['in_time']));
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            if (!empty($value['out_time'])) {
                                                                echo date('h:i A', strtotime($value['out_time']));
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            if (!empty($value['in_time']) && !empty($value['out_time'])) {
                                                                $in = strtotime($value['in_time']);
                                                                $out = strtotime($value['out_time']);
                                                                $diff_seconds = $out - $in;
                                                                if ($diff_seconds > 0) {
                                                                    $hours = floor($diff_seconds / 3600);
                                                                    $minutes = floor(($diff_seconds % 3600) / 60);
                                                                    echo sprintf('%02d:%02d hrs', $hours, $minutes);
                                                                } else {
                                                                    echo '-';
                                                                }
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                        </td>
                                                        <?php
                                                        if ($sch_setting->staff_biometric) {
                                                        ?>
                                                            <td>
                                                                <?php

                                                                if (IsNullOrEmptyString($value['biometric_attendence']) && IsNullOrEmptyString($value['qrcode_attendance'])) {
                                                                    echo $this->lang->line('n_a');
                                                                } elseif (($value['biometric_attendence'] == 0) && ($value['qrcode_attendance']  == 0)) {
                                                                    echo $this->lang->line('manual');
                                                                } elseif ($value['biometric_attendence']) {
                                                                    echo $this->lang->line('biometric');
                                                                } elseif ($value['qrcode_attendance']) {
                                                                    echo $this->lang->line('qrcode') . " / " . $this->lang->line('barcode');
                                                                }

                                                                ?>
                                                            </td>
                                                        <?php
                                                        }
                                                        ?>


                                                    </tr>
                                                <?php
                                                    $row_count++;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    <?php
                                    } else {
                                    ?>
                                        <div class="alert alert-info">
                                            <?php echo $this->lang->line('no_attendance_prepared'); ?>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                </div><!-- ./box box-primary -->
        <?php
                        }
                    }
        ?>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function() {

        $('.detail_popover').popover({
            placement: 'right',
            title: '',
            trigger: 'hover',
            container: 'body',
            html: true,
            content: function() {
                return $(this).closest('th').find('.fee_detail_popover').html();
            }
        });



        var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy',]) ?>';
        $('#date').datepicker({
            format: date_format,
            autoclose: true
        });
    });
</script>

<script type="text/javascript">
    var base_url = '<?php echo base_url() ?>';

    function printDiv(elem) {
        Popup(jQuery(elem).html());
    }

    function Popup(data) {
        var frame1 = $('<iframe />');
        frame1[0].name = "frame1";
        frame1.css({
            "position": "absolute",
            "top": "-1000000px"
        });
        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
        frameDoc.document.open();
        //Create a new HTML document.
        frameDoc.document.write('<html>');
        frameDoc.document.write('<head>');
        frameDoc.document.write('<title></title>');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/bootstrap/css/bootstrap.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/font-awesome.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/ionicons.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/AdminLTE.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/skins/_all-skins.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/iCheck/flat/blue.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/morris/morris.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/jvectormap/jquery-jvectormap-1.2.2.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/datepicker/datepicker3.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/daterangepicker/daterangepicker-bs3.css">');
        frameDoc.document.write('</head>');
        frameDoc.document.write('<body>');
        frameDoc.document.write(data);
        frameDoc.document.write('</body>');
        frameDoc.document.write('</html>');
        frameDoc.document.close();
        setTimeout(function() {
            window.frames["frame1"].focus();
            window.frames["frame1"].print();
            frame1.remove();
        }, 500);

        return true;
    }
</script>