<?php
$special_attendance_title = $this->lang->line('special_attendance');
if (empty($special_attendance_title)) {
    $special_attendance_title = 'Special Attendance';
}
$department_label = $this->lang->line('department');
if (empty($department_label)) {
    $department_label = 'Department';
}
$all_departments_label = $this->lang->line('all_departments');
if (empty($all_departments_label)) {
    $all_departments_label = 'All Departments';
}
$month_label = $this->lang->line('month');
if (empty($month_label)) {
    $month_label = 'Month';
}
$year_label = $this->lang->line('year');
if (empty($year_label)) {
    $year_label = 'Year';
}
$working_days_label = $this->lang->line('working_days');
if (empty($working_days_label)) {
    $working_days_label = 'Working Days';
}
$load_staff_label = $this->lang->line('load') ?: 'Load Staff';
$generate_label = $this->lang->line('generate') ?: 'Generate';
$process_label = $this->lang->line('process') ?: 'Process';
$staff_id_label = $this->lang->line('staff_id');
if (empty($staff_id_label)) {
    $staff_id_label = 'Staff ID';
}
$name_label = $this->lang->line('name');
if (empty($name_label)) {
    $name_label = 'Name';
}
$days_present_label = $this->lang->line('days_present');
if (empty($days_present_label)) {
    $days_present_label = 'Days Present';
}
$remarks_label = $this->lang->line('remarks');
if (empty($remarks_label)) {
    $remarks_label = 'Remarks';
}
$select_label = $this->lang->line('select');
if (empty($select_label)) {
    $select_label = 'Select';
}
$reason_label = $this->lang->line('reason');
if (empty($reason_label)) {
    $reason_label = 'Reason';
}
$reason_options = array(
    'Overtime Adjustment',
    'Missed Punch Correction',
    'Field Work Assignment',
    'Official Travel',
    'Training Session',
    'Emergency Coverage',
    'Shift Swap',
    'System Downtime',
    'Administrative Duty',
    'Other'
);
$no_staff_message = $this->lang->line('no_record_found');
if (empty($no_staff_message)) {
    $no_staff_message = 'No active staff found for the selected department.';
}
$confirm_generate_text = 'Generating special attendance will delete any existing special-attendance punches for the selected staff in this month and recreate them. Continue?';
$confirm_process_text = 'Processing attendance will remove previously generated attendance records for the selected staff in this month and rebuild them from the current punches. Continue?';
$cancelled_text = 'Operation cancelled by user.';
$select_staff_warning = $this->lang->line('please_select_staff_member');
if (empty($select_staff_warning)) {
    $select_staff_warning = 'Please select at least one staff member.';
}
$loading_text = $this->lang->line('loading');
if (empty($loading_text)) {
    $loading_text = 'Loading...';
}
$generate_success_text = $this->lang->line('attendance_generated_successfully');
if (empty($generate_success_text)) {
    $generate_success_text = 'Attendance generated successfully.';
}
$process_success_text = $this->lang->line('attendance_processed_successfully');
if (empty($process_success_text)) {
    $process_success_text = 'Attendance processed successfully.';
}
$positive_days_warning = 'Enter Days Present greater than zero for at least one selected staff member.';
$skipped_days_info = 'staff member(s) skipped due to zero or negative Days Present.';
$current_year = (int)date('Y');
$months = array(
    'January','February','March','April','May','June','July','August','September','October','November','December'
);
?>
<style>
.special-attendance-toast {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    min-width: 260px;
    max-width: 480px;
    color: #fff;
    padding: 12px 18px;
    border-radius: 4px;
    display: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    text-align: center;
    font-weight: 600;
}
.special-attendance-toast.toast-warning { background-color: #f0ad4e; }
.special-attendance-toast.toast-info { background-color: #5bc0de; }
.special-attendance-toast.toast-success { background-color: #5cb85c; }
.special-attendance-toast.toast-danger { background-color: #d9534f; }
</style>
<div class="content-wrapper">
    <section class="content-header">
        <h1><?php echo htmlspecialchars($special_attendance_title); ?></h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary" id="special-attendance-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo htmlspecialchars($special_attendance_title); ?></h3>
                        <span class="pull-right text-muted" id="working-days-badge" style="display:none;"></span>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="department_id"><?php echo htmlspecialchars($department_label); ?></label>
                                    <select class="form-control" id="department_id">
                                        <option value="">-- <?php echo htmlspecialchars($all_departments_label); ?> --</option>
                                        <?php foreach ($departments as $department): ?>
                                            <option value="<?php echo (int)$department['id']; ?>"><?php echo htmlspecialchars($department['department_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="attendance_month"><?php echo htmlspecialchars($month_label); ?><small class="req"> *</small></label>
                                    <select class="form-control" id="attendance_month">
                                        <option value="">-- <?php echo htmlspecialchars($month_label); ?> --</option>
                                        <?php foreach ($months as $month): ?>
                                            <option value="<?php echo $month; ?>"<?php echo ($month === date('F')) ? ' selected="selected"' : ''; ?>><?php echo $month; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="attendance_year"><?php echo htmlspecialchars($year_label); ?><small class="req"> *</small></label>
                                    <input type="number" class="form-control" id="attendance_year" value="<?php echo $current_year; ?>" min="2000" max="2100">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="working_days"><?php echo htmlspecialchars($working_days_label); ?></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="working_days" readonly>
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default" id="refresh_working_days" title="Refresh"><i class="fa fa-refresh"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

        <div class="row">
            <div class="col-md-9">
                <div class="form-group">
                    <label for="special_reason"><?php echo htmlspecialchars($reason_label); ?></label>
                    <select class="form-control" id="special_reason">
                        <option value="">-- <?php echo htmlspecialchars($select_label); ?> --</option>
                        <?php foreach ($reason_options as $option): ?>
                            <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <label class="hidden-xs">&nbsp;</label>
                <button type="button" class="btn btn-primary btn-block" id="load_employees"><i class="fa fa-search"></i> <?php echo htmlspecialchars($load_staff_label); ?></button>
            </div>
        </div>

        <div id="special-attendance-message" class="alert" style="display:none;"></div>

        <div class="table-responsive" id="employees-wrapper" style="display:none;">
            <table class="table table-bordered table-striped" id="employees-table">
                <thead>
                    <tr>
                        <th style="width:40px;" class="text-center"><input type="checkbox" id="select_all"></th>
                        <th><?php echo htmlspecialchars($staff_id_label); ?></th>
                        <th><?php echo htmlspecialchars($name_label); ?></th>
                        <th><?php echo htmlspecialchars($department_label); ?></th>
                        <th style="width:140px;" class="text-center"><?php echo htmlspecialchars($days_present_label); ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>

        <div class="clearfix">
            <button type="button" class="btn btn-primary" id="generate_attendance" disabled><i class="fa fa-cogs"></i> <?php echo htmlspecialchars($generate_label); ?></button>
            <button type="button" class="btn btn-success" id="process_attendance" disabled><i class="fa fa-check"></i> <?php echo htmlspecialchars($process_label); ?></button>
        </div>
    </div>
</div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
(function($){
    var $department = $('#department_id');
    var $month = $('#attendance_month');
    var $year = $('#attendance_year');
    var $workingDays = $('#working_days');
    var $message = $('#special-attendance-message');
    var $wrapper = $('#employees-wrapper');
    var $tableBody = $('#employees-table tbody');
    var $selectAll = $('#select_all');
    var $generateBtn = $('#generate_attendance');
    var $processBtn = $('#process_attendance');
    var $reason = $('#special_reason');
    var workingDaysCache = null;
    var toastTimer = null;

    function getToastContainer() {
        var $toast = $('#special-attendance-toast');
        if (!$toast.length) {
            $toast = $('<div id="special-attendance-toast" class="special-attendance-toast" role="alert"></div>').appendTo('body');
        }
        return $toast;
    }

    function showToast(type, text, duration) {
        if (!text) {
            return;
        }
        var displayMs = duration || 3500;
        var $toast = getToastContainer();
        $toast.stop(true, true)
            .removeClass('toast-success toast-danger toast-warning toast-info')
            .addClass('toast-' + type)
            .text(text)
            .fadeIn(150);
        if (toastTimer) {
            clearTimeout(toastTimer);
        }
        toastTimer = setTimeout(function(){
            $toast.fadeOut(200);
        }, displayMs);
    }

    function showMessage(type, text) {
        if (!text) {
            $message.hide();
            return;
        }
        $message.removeClass('alert-success alert-danger alert-warning alert-info').addClass('alert-' + type).text(text).show();
    }

    function formDataValid(requireDepartment) {
        var departmentId = $department.val();
        var month = $month.val();
        var year = $.trim($year.val());

        if (requireDepartment && !departmentId) {
            showMessage('warning', '<?php echo addslashes($department_label); ?> is required.');
            return false;
        }
        if (!requireDepartment && !departmentId) {
            departmentId = '';
        }
        if (!month) {
            showMessage('warning', '<?php echo addslashes($month_label); ?> is required.');
            return false;
        }
        if (!year) {
            showMessage('warning', '<?php echo addslashes($year_label); ?> is required.');
            return false;
        }
        return {
            department: departmentId,
            month: month,
            year: year
        };
    }

    function updateButtonsState() {
        var hasRows = $tableBody.find('tr').length > 0;
        var anySelected = $tableBody.find('.employee-select:checked').length > 0;
        $generateBtn.prop('disabled', !(hasRows && anySelected));
        $processBtn.prop('disabled', !(hasRows && anySelected));
    }

    function renderEmployees(employees) {
        $tableBody.empty();
        if (!employees || !employees.length) {
            $wrapper.hide();
            showMessage('info', '<?php echo addslashes($no_staff_message); ?>');
            updateButtonsState();
            return;
        }

        var workingDays = parseFloat($workingDays.val());
        if (!isFinite(workingDays)) {
            workingDays = null;
        }

        var rows = [];
        $.each(employees, function(_, emp){
            var presentDays = parseFloat(emp.present_days);
            if (!isFinite(presentDays)) {
                presentDays = '';
            }
            if (workingDays !== null && presentDays > workingDays) {
                presentDays = workingDays;
            }
            rows.push('<tr data-employee-id="' + emp.id + '">\n' +
                '   <td class="text-center"><input type="checkbox" class="employee-select" checked></td>\n' +
                '   <td>' + (emp.code ? emp.code : '-') + '</td>\n' +
                '   <td>' + (emp.name ? emp.name : '-') + '</td>\n' +
                '   <td>' + (emp.department ? emp.department : '-') + '</td>\n' +
                '   <td class="text-center"><input type="number" class="form-control input-sm days-present" min="0" step="0.5"' +
                (workingDays !== null ? ' max="' + workingDays + '"' : '') + ' value="' + (presentDays === '' ? '' : presentDays) + '"></td>\n' +
                '</tr>');
        });
        $tableBody.html(rows.join('\n'));
        $wrapper.show();
        var deptText = $department.val() ? $department.find('option:selected').text() : '<?php echo addslashes($all_departments_label); ?>';
        showMessage('info', employees.length + ' staff member(s) loaded (' + deptText + ').');
        updateButtonsState();
    }

    function fetchWorkingDays(callback) {
        var data = formDataValid(false);
        if (!data) {
            if (callback) { callback(null); }
            return;
        }
        $workingDays.prop('placeholder', '<?php echo addslashes($loading_text); ?>');
        $.ajax({
            url: baseurl + 'admin/specialattendance/get_working_days',
            type: 'POST',
            dataType: 'json',
            data: {
                month: data.month,
                year: data.year
            },
            success: function(response){
                if (response && typeof response.working_days !== 'undefined') {
                    workingDaysCache = {
                        month: data.month,
                        year: data.year,
                        payload: response
                    };
                    $workingDays.val(response.working_days);
                    var badgeText = '<?php echo addslashes($working_days_label); ?>: ' + response.working_days;
                    if (response.holidays && response.holidays.length) {
                        badgeText += ' | Holidays: ' + response.holidays.length;
                    }
                    $('#working-days-badge').text(badgeText).show();
                    if (callback) { callback(response); }
                } else {
                    $workingDays.val('');
                    $('#working-days-badge').hide();
                    workingDaysCache = null;
                    if (callback) { callback(null); }
                }
            },
            error: function(xhr){
                $workingDays.val('');
                $('#working-days-badge').hide();
                workingDaysCache = null;
                var msg = '<?php echo addslashes($this->lang->line('something_went_wrong') ?: 'Unable to fetch working days.'); ?>';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    msg = xhr.responseJSON.error;
                }
                showMessage('danger', msg);
                if (callback) { callback(null); }
            },
            complete: function(){
                $workingDays.prop('placeholder', '');
            }
        });
    }

    function loadEmployees() {
        var data = formDataValid(false);
        if (!data) {
            return;
        }
        showMessage('info', '<?php echo addslashes($loading_text); ?>');
        $wrapper.hide();
        $generateBtn.prop('disabled', true);
        $processBtn.prop('disabled', true);

        var proceed = function(){
            $.ajax({
                url: baseurl + 'admin/specialattendance/get_employees_by_department',
                type: 'POST',
                dataType: 'json',
                data: {
                    department_id: data.department,
                    month: data.month,
                    year: data.year
                },
                success: function(staffResponse){
                    renderEmployees(staffResponse);
                },
                error: function(xhr){
                    var msg = '<?php echo addslashes($this->lang->line('something_went_wrong') ?: 'Unable to load staff.'); ?>';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        msg = xhr.responseJSON.error;
                    }
                    showMessage('danger', msg);
                    $wrapper.hide();
                }
            });
        };

        if (!workingDaysCache || workingDaysCache.month !== data.month || workingDaysCache.year !== data.year) {
            fetchWorkingDays(function(){
                proceed();
            });
        } else {
            if (workingDaysCache.payload) {
                $workingDays.val(workingDaysCache.payload.working_days || '');
                var cachedBadge = '<?php echo addslashes($working_days_label); ?>: ' + (workingDaysCache.payload.working_days || '0');
                if (workingDaysCache.payload.holidays && workingDaysCache.payload.holidays.length) {
                    cachedBadge += ' | Holidays: ' + workingDaysCache.payload.holidays.length;
                }
                $('#working-days-badge').text(cachedBadge).toggle(!!workingDaysCache.payload.working_days);
            }
            proceed();
        }
    }

    function collectSelectedStaff(includeDays, skipZeroOrNegative) {
        var employeeIds = [];
        var daysPresent = {};
        var skipped = 0;
        $tableBody.find('tr').each(function(){
            var $row = $(this);
            var selected = $row.find('.employee-select').prop('checked');
            if (!selected) {
                return;
            }
            var empId = $row.data('employee-id');
            var includeEmployee = true;
            employeeIds.push(empId);
            if (includeDays) {
                var value = parseFloat($row.find('.days-present').val());
                if (!isFinite(value)) {
                    value = 0;
                }
                if (skipZeroOrNegative && value <= 0) {
                    includeEmployee = false;
                    skipped++;
                }
                if (includeEmployee) {
                    daysPresent[empId] = value;
                }
            }
            if (!includeEmployee) {
                employeeIds.pop();
            }
        });
        return {
            ids: employeeIds,
            days: daysPresent,
            skipped: skipped
        };
    }

    function postAction(url, payload, successMessage) {
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: payload,
            success: function(response){
                if (response && response.status === 'success') {
                    showMessage('success', response.message || successMessage);
                } else {
                    showMessage('danger', (response && response.message) ? response.message : successMessage);
                }
            },
            error: function(xhr){
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : '<?php echo addslashes($this->lang->line('something_went_wrong') ?: 'Operation failed.'); ?>';
                showMessage('danger', msg);
            }
        });
    }

    $('#refresh_working_days').on('click', function(){
        fetchWorkingDays();
    });

    $('#load_employees').on('click', function(){
        loadEmployees();
    });

    $month.on('change', function(){
        workingDaysCache = null;
        fetchWorkingDays();
    });

    $year.on('change blur', function(){
        workingDaysCache = null;
        fetchWorkingDays();
    });

    $selectAll.on('change', function(){
        var checked = $(this).prop('checked');
        $tableBody.find('.employee-select').prop('checked', checked);
        updateButtonsState();
    });

    $tableBody.on('change', '.employee-select', function(){
        if (!$(this).prop('checked')) {
            $selectAll.prop('checked', false);
        }
        updateButtonsState();
    });

    $tableBody.on('input', '.days-present', function(){
        var max = parseFloat($(this).attr('max'));
        var value = parseFloat($(this).val());
        if (isFinite(max) && isFinite(value) && value > max) {
            $(this).val(max);
        }
    });

    $generateBtn.on('click', function(){
        var data = formDataValid(false);
        if (!data) {
            return;
        }
        var selection = collectSelectedStaff(true, true);
        if (!selection.ids.length) {
            showToast('warning', '<?php echo addslashes($positive_days_warning); ?>');
            return;
        }
        if (!window.confirm('<?php echo addslashes($confirm_generate_text); ?>')) {
            showMessage('info', '<?php echo addslashes($cancelled_text); ?>');
            return;
        }
        if (selection.skipped > 0) {
            showMessage('info', selection.skipped + ' <?php echo addslashes($skipped_days_info); ?>');
        }

        postAction(baseurl + 'admin/specialattendance/generate_attendance', {
            employee_ids: selection.ids,
            days_present: selection.days,
            month: data.month,
            year: data.year,
            reason: $reason.val()
        }, '<?php echo addslashes($generate_success_text); ?>');
    });

    $processBtn.on('click', function(){
        var data = formDataValid(false);
        if (!data) {
            return;
        }
        // use same logic as generate to skip rows without days value
        var selection = collectSelectedStaff(true, true);
        if (!selection.ids.length) {
            showMessage('warning', '<?php echo addslashes($select_staff_warning); ?>');
            return;
        }
        if (!window.confirm('<?php echo addslashes($confirm_process_text); ?>')) {
            showMessage('info', '<?php echo addslashes($cancelled_text); ?>');
            return;
        }
        if (selection.skipped > 0) {
            showMessage('info', selection.skipped + ' <?php echo addslashes($skipped_days_info); ?>');
        }

        postAction(baseurl + 'admin/specialattendance/process_attendance', {
            employee_ids: selection.ids,
            month: data.month,
            year: data.year,
            days_present: selection.days // send so server can double-check
        }, '<?php echo addslashes($process_success_text); ?>');
    });

    // Initial load
    fetchWorkingDays();
})(jQuery);
</script>
