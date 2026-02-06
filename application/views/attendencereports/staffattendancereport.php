<style type="text/css">
    @media print {

        .no-print,
        .no-print * {
            display: none !important;
        }
    }

    .att-present {
        background-color: #d4edda;
        color: #155724;
        font-weight: 600;
    }

    .att-half-day {
        background-color: #fff3cd;
        color: #856404;
        font-weight: 600;
    }

    .att-absent {
        background-color: #f8d7da;
        color: #721c24;
        font-weight: 600;
    }
</style>

<div class="content-wrapper" style="min-height: 946px;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><i class="fa fa-sitemap"></i> <?php //echo $this->lang->line('human_resource'); 
                                            ?></h1>
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
                    <form id='form1' action="<?php echo site_url('attendencereports/staffattendancereport') ?>" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-4">
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
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('month'); ?></label><small class="req"> *</small>
                                        <select id="month" name="month" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($monthlist as $m_key => $month) {
                                            ?>
                                                <option value="<?php echo $m_key ?>" <?php
                                                                                        if ($month_selected == $m_key) {
                                                                                            echo "selected =selected";
                                                                                        }
                                                                                        ?>><?php echo $month; ?></option>
                                            <?php
                                                $count++;
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('month'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('year'); ?></label><small class="req"> *</small>
                                        <select id="year" name="year" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($yearlist as $y_key => $year) {
                                            ?>
                                            <option value="<?php echo $year["year"] ?>" 
                                            <?php
                                            if ($year["year"] == $year_selected) {
                                                echo "selected";
                                            }
                                            ?>><?php echo $year["year"]; ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('year'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <button type="submit" name="search" value="search" class="btn btn-primary btn-sm pull-right checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php
                    if (isset($resultlist)) {
                    ?>
                        <div class="" id="attendencelist">
                            <div class="box-header ptbnull"></div>
                            <div class="box-header with-border">
                                <div class="row">
                                    <div class="col-md-4 col-sm-4">
                                        <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $this->lang->line('staff_attendance_report'); ?></h3>
                                    </div>
                                    <div class="col-md-8 col-sm-8">
                                        <div class="pull-right">
                                            <span class="label att-present" style="padding: 4px 8px; margin-right: 6px;">Present</span>
                                            <span class="label att-half-day" style="padding: 4px 8px; margin-right: 6px;">Half Day</span>
                                            <span class="label att-absent" style="padding: 4px 8px;">Absent</span>
                                            <span style="margin-left: 10px;"></span>
                                            <b>WD</b>: Working Days
                                            &nbsp;&nbsp;<b>A*</b>: Total Absent (incl. 0.5 for HD)
                                            &nbsp;&nbsp;<b>P*</b>: Total Present (incl. 0.5 for HD)
                                            &nbsp;&nbsp;<b>H</b>: Holidays
                                            &nbsp;&nbsp;<b>HD</b>: Half Day
                                            &nbsp;&nbsp;<b>WE</b>: Weekends
                                        </div>
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
                                    <div class="download_label"><?php echo $this->lang->line('staff_attendance_report'); ?></div>
                                    <div> <?php echo
                                            $this->customlib->get_postmessage();
                                            ?></div>
                                    <table class="table table-striped table-bordered table-hover example xyz">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <?php echo $this->lang->line('staff_date'); ?>
                                                </th>
                                                <th><br /><span data-toggle="tooltip" title="<?php echo $this->lang->line("gross_present_percentage"); ?>"> (%)</span></th>
                                                <th><br /><span data-toggle="tooltip" title="Total Working Days">WD</span></th>
                                                <th><br /><span data-toggle="tooltip" title="Total Absent (Excluding Holidays)">A*</span></th>
                                                <th><br /><span data-toggle="tooltip" title="Total Present (Including Half Day)">P*</span></th>
                                                <th><br /><span data-toggle="tooltip" title="Total Holidays">H</span></th>
                                                <th colspan="1"><br /><span data-toggle="tooltip" title="Total Half Day">HD</span></th>
                                                <th colspan="1"><br /><span data-toggle="tooltip" title="Weekends">WE</span></th>
                                                <?php
                                                foreach ($attendence_array as $at_key => $at_value) {
                                                     $header_class = '';
                                                    if (!empty($weekend_day_dates) && in_array($at_value, $weekend_day_dates, true)) {
                                                        $header_class = 'bg-danger';
                                                    } elseif (in_array($at_value, $holiday_dates)) {
                                                        $header_class = 'bg-warning';
                                                    }
                                                ?>
                                                        <th class="tdcls text text-center <?php echo $header_class; ?>">
                                                            <?php
                                                            echo date('d', $this->customlib->dateyyyymmddTodateformat($at_value)) . "<br/>" .
                                                                $this->lang->line(strtolower(date('D', $this->customlib->dateyyyymmddTodateformat($at_value))));
                                                            ?>
                                                        </th>
                                                <?php
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                                                                <tbody>
                                                                                    <?php if (empty($student_array)) {
                                                                                    ?>
                                                                                        <tr>
                                                                                            <td colspan="32" class="text-danger text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                                                                        </tr>
                                                                                        <?php
                                                                                    } else {
                                                                                        $row_count = 1;
                                                                                        $i         = 0;
                                                                                        foreach ($student_array as $student_key => $student_value) {
                                                                                            $present_count = $monthAttendance[$i][$student_value['id']]['present'] ?? 0;
                                                                                            $half_day_count = $monthAttendance[$i][$student_value['id']]['half_day'] ?? 0;
                                                                                            $absent_count = $absent_working_day_counts[$student_value['id']] ?? 0;
                                                                                            $working_days = $working_days_count ?? 0;
                                                                                            $total_present = $present_count + ($half_day_count * 0.5);
                                                                                            $total_absent = $absent_count + ($half_day_count * 0.5);

                                                                                            if ($working_days == 0) {
                                                                                                $percentage       = -1;
                                                                                                $print_percentage = "-";
                                                                                            } else {

                                                                                                $percentage       = ($total_present / $working_days) * 100;
                                                                                                $print_percentage = round($percentage, 0);
                                                                                            }
                                        
                                                                                            if (($percentage < 75) && ($percentage >= 0)) {
                                                                                                $label = "class='label label-danger'";
                                                                                            } else if ($percentage > 75) {
                                                                                                $label = "class='label label-success'";
                                                                                            } else {
                                                                                                $label = "class='label label-default'";
                                                                                            }
                                                                                        ?>
                                                                                            <tr>
                                        
                                                                                                <td class="tdclsname">
                                                                                                    <span data-toggle="popover" class="detail_popover" data-original-title="" title="">
                                                                                                        <a href="#"><?php echo $student_value['name'] . " " . $student_value['surname']; ?></a>
                                                                                                    </span>
                                                                                                    <div class="fee_detail_popover" style="display: none"><?php echo $this->lang->line('staff_id'); ?>: <?php echo $student_value['employee_id']; ?></div>
                                                                                                </td>
                                                                                                <td><?php echo "<label $label>" . $print_percentage . "</label>"; ?></td>
                                                                                                                                                                                                <td><?php echo $working_days; ?></td>
                                                                                                                                                                                                <td><?php echo rtrim(rtrim(number_format((float)$total_absent, 1, '.', ''), '0'), '.'); ?></td>
                                                                                                <td><?php echo rtrim(rtrim(number_format((float)$total_present, 1, '.', ''), '0'), '.'); ?></td>
                                                                                                <td><?php echo $holiday_count ?? 0; ?></td>
                                                                                                <td><?php echo $half_day_count; ?></td>
                                                                                                <td><?php echo $weekend_count ?? 0; ?></td>
                                                                                                <?php
                                                                                                foreach ($attendence_array as $at_key => $at_value) {
                                                                                                    $cell_class = '';
                                                                                                    $attendance_row = $resultlist[$at_value][$student_value['id']] ?? [];
                                                                                                    $attendance_key = $attendance_row['key'] ?? null;
                                                                                                    $display_key = $attendance_key ?? '';
                                                                                                    $normalized_key = $attendance_key;
                                                                                                    $present_keys = ['P', 'FHL', 'SHL', 'FHP', 'SHP'];
                                                                                                    $absent_keys = ['A', 'FHA', 'SHA'];
                                                                                                    if (in_array($attendance_key, $present_keys, true)) {
                                                                                                        $normalized_key = 'P';
                                                                                                    } elseif (in_array($attendance_key, $absent_keys, true)) {
                                                                                                        $normalized_key = 'A';
                                                                                                    }
                                        
                                                                                                    $is_weekend_or_holiday = false;
                                                                                                    if (!empty($weekend_day_dates) && in_array($at_value, $weekend_day_dates, true)) {
                                                                                                        $cell_class = 'bg-danger';
                                                                                                        $display_key = 'W';
                                                                                                        $tooltip_text = 'Weekend';
                                                                                                        $is_weekend_or_holiday = true;
                                                                                                    } elseif (in_array($at_value, $holiday_dates) || $attendance_key == 'HO') {
                                                                                                        $cell_class = 'bg-warning';
                                                                                                        $display_key = 'H';
                                                                                                        $tooltip_text = 'Holiday';
                                                                                                        $is_weekend_or_holiday = true;
                                                                                                    } elseif ($normalized_key === 'P') {
                                                                                                        $cell_class = 'att-present';
                                                                                                        $tooltip_text = 'Present';
                                                                                                    } elseif ($attendance_key === 'HD') {
                                                                                                        $cell_class = 'att-half-day';
                                                                                                        $out_time = $attendance_row['out_time'] ?? null;
                                                                                                        $biometric_attendence = (int)($attendance_row['biometric_attendence'] ?? 0);
                                                                                                        if ($biometric_attendence === 1 && (empty($out_time) || $out_time === '00:00:00')) {
                                                                                                            $tooltip_text = 'No Exit Punch Found';
                                                                                                        } else {
                                                                                                            $tooltip_text = 'Half Day';
                                                                                                        }
                                                                                                    } elseif ($normalized_key === 'A') {
                                                                                                        $cell_class = 'att-absent';
                                                                                                        $tooltip_text = 'Absent';
                                                                                                    } else {
                                                                                                        $tooltip_text = 'N/A';
                                                                                                    }
                                                                                                    if (!$is_weekend_or_holiday && $attendance_key && $normalized_key && !in_array($attendance_key, ['HD', 'HO'], true)) {
                                                                                                        $display_key = $normalized_key;
                                                                                                    }
                                                                                                ?>
                                                                                                    <td class="tdcls text text-center <?php echo $cell_class; ?>">
                                                                                                        <center>
                                                                                                        <span data-toggle="popover" class="detail_popover" data-original-title="" title="">
                                                                                                        <a href="#"><?php echo $display_key;  ?></a></span>
                                                                                                        <div class="fee_detail_popover" style="display: none">
                                                                                                            <?php
                                                                                                                if (!empty($resultlist[$at_value][$student_value['id']]['remark'])) {
                                                                                                                    echo $resultlist[$at_value][$student_value['id']]['remark'];
                                                                                                                } else {
                                                                                                                    echo $tooltip_text;
                                                                                                                }
                                                                                                            ?>
                                                                                                        </div>
                                                                                                    </center></td>
                                                                                                <?php
                                                                                                }
                                                                                                ?>
                                                                                            </tr>
                                                                                    <?php
                                                                                            $i++;
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                                </tbody>                                    </table>
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
                    <?php
                    }
                    ?>
                </div><!--./box box-primary-->
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    $('.detail_popover').popover({
        placement: 'right',
        title: '',
        trigger: 'hover',
        container: 'body',
        html: true,
        content: function() {
            return $(this).closest('td').find('.fee_detail_popover').html();
        }
    });

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