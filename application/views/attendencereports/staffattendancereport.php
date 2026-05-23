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

    .btn-colored-excel {
        background-color: #2f3640;
        color: #ffffff !important;
        border-color: #2f3640;
        margin-right: 6px;
    }
    .btn-colored-excel:hover,
    .btn-colored-excel:focus {
        background-color: #1e272e;
        border-color: #1e272e;
        color: #ffffff !important;
    }
</style>

<?php
$is_punch_report = !empty($is_punch_report);
$report_post_url = !empty($report_post_url) ? $report_post_url : site_url('attendencereports/staffattendancereport');
$report_heading = !empty($report_heading) ? $report_heading : $this->lang->line('staff_attendance_report');
?>

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
                    <form id='form1' action="<?php echo $report_post_url; ?>" method="post" accept-charset="utf-8">
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
                                        <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $report_heading; ?></h3>
                                    </div>
                                    <div class="col-md-8 col-sm-8">
                                        <div class="pull-right">
                                            <?php if (!empty($month_selected) && !empty($year_selected)) { ?>
                                                <a class="btn btn-xs btn-colored-excel" href="<?php echo site_url('attendencereports/staffattendancereport_export_excel?role=' . urlencode($role_selected) . '&month=' . urlencode($month_selected) . '&year=' . urlencode($year_selected) . '&with_punch_report=' . ($is_punch_report ? '1' : '0')); ?>" title="Export in Excel(Colored)"><i class="fa fa-file-excel-o"></i> Export in Excel(Colored)</a>
                                            <?php } ?>
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
                                            &nbsp;&nbsp;<b>TL</b>: Total Late
                                            &nbsp;&nbsp;<b>TP</b>: Total Permission
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
                                    <div class="download_label"><?php echo $report_heading; ?></div>
                                    <div> <?php echo
                                            $this->customlib->get_postmessage();
                                            ?></div>
                                    <table class="table table-striped table-bordered table-hover staff-attendance-report" style="white-space:nowrap;">
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
                                                <th colspan="1"><br /><span data-toggle="tooltip" title="Total Late Counts">TL</span></th>
                                                <th colspan="1"><br /><span data-toggle="tooltip" title="Total Permission Counts">TP</span></th>
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
                                                                                            $working_days = $working_days_count ?? 0;
                                                                                            $summary = $staff_working_day_summary[$student_value['id']] ?? ['present_equivalent' => 0, 'absent_equivalent' => 0, 'half_day_count' => 0];
                                                                                            $total_present = (float) ($summary['present_equivalent'] ?? 0);
                                                                                            $total_absent = (float) ($summary['absent_equivalent'] ?? 0);
                                                                                            $half_day_count = (float) ($summary['half_day_count'] ?? 0);

                                                                                            $total_late = $total_late_counts[$student_value['id']] ?? 0;
                                                                                            $total_permission = $total_permission_counts[$student_value['id']] ?? 0;

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
                                                                                                <td>
                                                                                                    <?php if ((float) $half_day_count > 0) { ?>
                                                                                                        <a href="#" onclick="showAttendanceDetail('<?php echo $student_value['id']; ?>','HD'); return false;">
                                                                                                            <?php echo $half_day_count; ?> <i class="fa fa-eye"></i>
                                                                                                        </a>
                                                                                                    <?php } else { ?>
                                                                                                        <?php echo $half_day_count; ?>
                                                                                                    <?php } ?>
                                                                                                </td>
                                                                                                <td><?php echo $weekend_count ?? 0; ?></td>
                                                                                                <td>
                                                                                                    <?php if ((int) $total_late > 0) { ?>
                                                                                                        <a href="#" onclick="showAttendanceDetail('<?php echo $student_value['id']; ?>','TL'); return false;">
                                                                                                            <?php echo $total_late; ?> <i class="fa fa-eye"></i>
                                                                                                        </a>
                                                                                                    <?php } else { ?>
                                                                                                        <?php echo $total_late; ?>
                                                                                                    <?php } ?>
                                                                                                </td>
                                                                                                <td>
                                                                                                    <?php if ((int) $total_permission > 0) { ?>
                                                                                                        <a href="#" onclick="showAttendanceDetail('<?php echo $student_value['id']; ?>','TP'); return false;">
                                                                                                            <?php echo $total_permission; ?> <i class="fa fa-eye"></i>
                                                                                                        </a>
                                                                                                    <?php } else { ?>
                                                                                                        <?php echo $total_permission; ?>
                                                                                                    <?php } ?>
                                                                                                </td>
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
                                                                                                        $variant_labels = ['FHL' => 'First Half Late', 'FHP' => 'First Half Permission', 'SHL' => 'Second Half Late', 'SHP' => 'Second Half Permission'];
                                                                                                        $tooltip_text = $variant_labels[$attendance_key] ?? 'Present';
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
                                                                                                        // Show the specific key (FHL, SHL, FHP, SHP) instead of collapsing to P.
                                                                                                        // Only normalize FHA/SHA to A for simpler display.
                                                                                                        if (in_array($attendance_key, ['FHA', 'SHA'], true)) {
                                                                                                            $display_key = 'A';
                                                                                                        } else {
                                                                                                            $display_key = $attendance_key;
                                                                                                        }
                                                                                                    }
                                                                                                ?>
                                                                                                    <td class="tdcls text text-center <?php echo $cell_class; ?>">
                                                                                                        <center>
                                                                                                        <span data-toggle="popover" class="detail_popover" data-original-title="" title="">
                                                                                                        <a href="#"><?php echo $display_key;  ?></a></span>
                                                                                                        <?php
                                                                                                        if ($is_punch_report && !$is_weekend_or_holiday && ($normalized_key === 'P' || $attendance_key === 'HD')) {
                                                                                                            $first_punch = !empty($attendance_row['in_time']) && $attendance_row['in_time'] !== '00:00:00' ? date('H:i', strtotime($attendance_row['in_time'])) : '-';
                                                                                                            $last_punch = !empty($attendance_row['out_time']) && $attendance_row['out_time'] !== '00:00:00' ? date('H:i', strtotime($attendance_row['out_time'])) : '-';
                                                                                                        ?>
                                                                                                            <div style="font-size:10px; line-height:1.2; margin-top:2px; white-space:nowrap;">IN: <?php echo $first_punch; ?></div>
                                                                                                            <div style="font-size:10px; line-height:1.2; white-space:nowrap;">OUT: <?php echo $last_punch; ?></div>
                                                                                                        <?php
                                                                                                        }
                                                                                                        ?>
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

<div id="attendance_detail_modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Attendance Details</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Session</th>
                                <th>Punch In</th>
                                <th>Punch Out</th>
                            </tr>
                        </thead>
                        <tbody id="attendance_detail_body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
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

    function showAttendanceDetail(staff_id, type) {
        var month = '<?php echo $month_selected ?? ''; ?>';
        var year = '<?php echo $year_selected ?? ''; ?>';

        $('#attendance_detail_body').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
        $('#attendance_detail_modal').modal('show');

        $.ajax({
            url: base_url + 'attendencereports/staffAttendanceDetail',
            type: 'POST',
            dataType: 'json',
            data: {
                staff_id: staff_id,
                month: month,
                year: year,
                type: type
            },
            success: function (response) {
                if (!response || response.status !== 'success' || !response.rows || response.rows.length === 0) {
                    $('#attendance_detail_body').html('<tr><td colspan="4" class="text-center">No records found.</td></tr>');
                    return;
                }

                var rowsHtml = '';
                $.each(response.rows, function (i, row) {
                    rowsHtml += '<tr>'
                        + '<td>' + row.date + '</td>'
                        + '<td>' + row.session + '</td>'
                        + '<td>' + row.in_time + '</td>'
                        + '<td>' + row.out_time + '</td>'
                        + '</tr>';
                });

                $('#attendance_detail_body').html(rowsHtml);
            },
            error: function () {
                $('#attendance_detail_body').html('<tr><td colspan="4" class="text-center">Failed to load data.</td></tr>');
            }
        });
    }
</script>