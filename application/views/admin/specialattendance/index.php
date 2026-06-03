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
$payable_working_days_label = 'Payable Working Days';
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
$days_absent_label = 'LOP Days';
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
    $no_staff_message = 'No staff found for the selected criteria.';
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
$positive_days_warning = 'Enter LOP Days (0 or more) for at least one staff member.';
$skipped_days_info = 'staff member(s) skipped due to empty/invalid LOP Days (use 0, 0.5, 1, 1.5, etc.).';
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
#special-attendance-message.special-attendance-floating {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10000;
    min-width: 320px;
    max-width: 680px;
    width: auto;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.25);
    margin: 0;
}
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
                            <div class="col-md-3">
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
                                    <label for="role_id">Role</label>
                                    <select class="form-control" id="role_id">
                                        <option value="">-- All Roles --</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo (int)$role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
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
                            <div class="col-md-1">
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
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="payable_working_days"><?php echo htmlspecialchars($payable_working_days_label); ?></label>
                                    <input type="text" class="form-control" id="payable_working_days" readonly>
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

        <div id="employees-wrapper" style="display:none;">
            <div class="alert alert-info" style="margin-bottom:10px;">
                <strong>Instructions:</strong>
                Leave <strong>LOP Days</strong> empty to skip staff. Enter <strong>0</strong> for full attendance.<br>
                Enter LOP as <strong>target payroll LOP days</strong> for the month (e.g. 1 = pay approx 27/28 days in Feb, 21.5 = pay approx 6.5 days in Feb).<br>
                <strong>Sandwich Weekend Rule (only rule):</strong> If both adjacent working days are absent, weekend is treated as LOP; otherwise weekend is payable.<br>
                Example (Sunday-only weekend): Sat Absent + Mon Absent ⇒ Sat+Sun+Mon counted as LOP (3 days).<br>
                Example (Sunday-only weekend): Sat Present + Mon Present ⇒ Sunday is payable (not added to LOP).<br>
                Example (Saturday+Sunday weekend): Fri Absent + Mon Absent ⇒ Fri+Sat+Sun+Mon counted as LOP (4 days).<br>
                Example (Saturday+Sunday weekend): Fri Present + Mon Present ⇒ Sat/Sun payable (not added to LOP).<br>
                Allowed values are half-step only: <strong>0, 0.5, 1, 1.5, ...</strong>
            </div>
            <div class="table-responsive">
            <table class="table table-bordered table-striped" id="employees-table">
                <thead>
                    <tr>
                        <th><?php echo htmlspecialchars($staff_id_label); ?></th>
                        <th><?php echo htmlspecialchars($name_label); ?></th>
                        <th><?php echo htmlspecialchars($department_label); ?></th>
                        <th style="width:120px;" class="text-center">Attendance %</th>
                        <th style="width:140px;" class="text-center"><?php echo htmlspecialchars($days_absent_label); ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            </div>
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
    var $role = $('#role_id');
    var $month = $('#attendance_month');
    var $year = $('#attendance_year');
    var $workingDays = $('#working_days');
    var $payableWorkingDays = $('#payable_working_days');
    var $message = $('#special-attendance-message');
    var $wrapper = $('#employees-wrapper');
    var $tableBody = $('#employees-table tbody');
    var $generateBtn = $('#generate_attendance');
    var $processBtn = $('#process_attendance');
    var $reason = $('#special_reason');
    var workingDaysCache = null;
    var toastTimer = null;
    var messageTimer = null;

    $message.addClass('special-attendance-floating');

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
        if (messageTimer) {
            clearTimeout(messageTimer);
        }
        $message.removeClass('alert-success alert-danger alert-warning alert-info').addClass('alert-' + type).text(text).show();
        if (type !== 'danger') {
            messageTimer = setTimeout(function(){
                $message.fadeOut(200);
            }, 4500);
        }
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
        $generateBtn.prop('disabled', !hasRows);
        $processBtn.prop('disabled', !hasRows);
    }

    function getPrefillStorageKey() {
        var departmentId = $department.val() || 'all';
        var roleId = $role.val() || 'all';
        var month = $month.val() || '';
        var year = $.trim($year.val()) || '';
        return 'special_attendance_lop_' + departmentId + '_' + roleId + '_' + month + '_' + year;
    }

    function getStoredLopValues() {
        var key = getPrefillStorageKey();
        var raw = null;
        try {
            raw = window.localStorage.getItem(key);
        } catch (e) {
            return {};
        }
        if (!raw) {
            return {};
        }
        try {
            var parsed = JSON.parse(raw);
            return (parsed && typeof parsed === 'object') ? parsed : {};
        } catch (e) {
            return {};
        }
    }

    function saveStoredLopValues(values) {
        var key = getPrefillStorageKey();
        try {
            window.localStorage.setItem(key, JSON.stringify(values || {}));
        } catch (e) {
        }
    }

    function setStoredLopValue(employeeId, rawValue) {
        var values = getStoredLopValues();
        var key = String(employeeId);
        if (rawValue === null || typeof rawValue === 'undefined' || $.trim(String(rawValue)) === '') {
            if (Object.prototype.hasOwnProperty.call(values, key)) {
                delete values[key];
            }
        } else {
            var num = parseFloat(rawValue);
            if (isFinite(num) && num >= 0) {
                values[key] = num;
            }
        }
        saveStoredLopValues(values);
    }

    function renderEmployees(employees) {
        $tableBody.empty();
        if (!employees || !employees.length) {
            $wrapper.hide();
            showMessage('info', '<?php echo addslashes($no_staff_message); ?>');
            updateButtonsState();
            return;
        }

        var maxLopDays = parseFloat($payableWorkingDays.val());
        if (!isFinite(maxLopDays)) {
            maxLopDays = parseFloat($workingDays.val());
        }
        if (!isFinite(maxLopDays)) {
            maxLopDays = null;
        }

        var rows = [];
        $.each(employees, function(_, emp){
            var lopDays = '';
            var attendancePercentage = parseFloat(emp.attendance_percentage);
            var lopTooltip = '';
            if (!isFinite(attendancePercentage)) {
                attendancePercentage = 0;
            }
            if (emp.entered_lop_days !== null && typeof emp.entered_lop_days !== 'undefined' && emp.entered_lop_days !== '') {
                lopDays = emp.entered_lop_days;
            }

            if (emp.entered_lop_updated_at) {
                lopTooltip = 'Last saved: ' + emp.entered_lop_updated_at;
                if (emp.entered_lop_admin_user_id) {
                    lopTooltip += ' | Admin ID: ' + emp.entered_lop_admin_user_id;
                }
                if (emp.entered_lop_reason) {
                    lopTooltip += ' | Reason: ' + emp.entered_lop_reason;
                }
            }

            if (parseInt(emp.has_special_attendance, 10) === 1 && emp.lop_days !== null && typeof emp.lop_days !== 'undefined') {
                if (lopDays === '') {
                    lopDays = emp.lop_days;
                }
            }
            rows.push('<tr data-employee-id="' + emp.id + '">\n' +
                '   <td>' + (emp.code ? emp.code : '-') + '</td>\n' +
                '   <td>' + (emp.name ? emp.name : '-') + '</td>\n' +
                '   <td>' + (emp.department ? emp.department : '-') + '</td>\n' +
                '   <td class="text-center">' + attendancePercentage.toFixed(2) + '%</td>\n' +
                '   <td class="text-center"><input type="number" class="form-control input-sm days-absent" min="0" step="0.5"' +
                (lopTooltip ? ' title="' + String(lopTooltip).replace(/"/g, '&quot;') + '"' : '') +
                (maxLopDays !== null ? ' max="' + maxLopDays + '"' : '') + ' placeholder="e.g. 1 or 1.5" value="' + (lopDays === '' ? '' : lopDays) + '"></td>\n' +
                '</tr>');
        });
        $tableBody.html(rows.join('\n'));
        $wrapper.show();
        var deptText = $department.val() ? $department.find('option:selected').text() : '<?php echo addslashes($all_departments_label); ?>';
        var roleText = $role.val() ? $role.find('option:selected').text() : 'All Roles';
        showMessage('info', employees.length + ' staff member(s) loaded (' + deptText + ' / ' + roleText + ').');
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
                    $payableWorkingDays.val(response.payable_working_days || '');
                    var badgeText = '<?php echo addslashes($working_days_label); ?>: ' + response.working_days + ' | <?php echo addslashes($payable_working_days_label); ?>: ' + (response.payable_working_days || '0');
                    if (response.holidays && response.holidays.length) {
                        badgeText += ' | Holidays: ' + response.holidays.length;
                    }
                    $('#working-days-badge').text(badgeText).show();
                    if (callback) { callback(response); }
                } else {
                    $workingDays.val('');
                    $payableWorkingDays.val('');
                    $('#working-days-badge').hide();
                    workingDaysCache = null;
                    if (callback) { callback(null); }
                }
            },
            error: function(xhr){
                $workingDays.val('');
                $payableWorkingDays.val('');
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
                    role_id: $role.val(),
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
                $payableWorkingDays.val(workingDaysCache.payload.payable_working_days || '');
                var cachedBadge = '<?php echo addslashes($working_days_label); ?>: ' + (workingDaysCache.payload.working_days || '0') + ' | <?php echo addslashes($payable_working_days_label); ?>: ' + (workingDaysCache.payload.payable_working_days || '0');
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
        var daysAbsent = {};
        var skipped = 0;

        function isHalfStep(value) {
            return Math.abs((value * 2) - Math.round(value * 2)) < 0.000001;
        }

        $tableBody.find('tr').each(function(){
            var $row = $(this);
            var empId = $row.data('employee-id');
            var includeEmployee = true;
            employeeIds.push(empId);
            if (includeDays) {
                var rawValue = $.trim($row.find('.days-absent').val());
                if (rawValue === '') {
                    includeEmployee = false;
                    skipped++;
                } else {
                    var value = parseFloat(rawValue);
                    if (!isFinite(value) || value < 0 || !isHalfStep(value)) {
                        includeEmployee = false;
                        skipped++;
                    }
                    if (includeEmployee) {
                        daysAbsent[empId] = value;
                    }
                }
            }
            if (!includeEmployee) {
                employeeIds.pop();
            }
        });
        return {
            ids: employeeIds,
            days: daysAbsent,
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

    $tableBody.on('input', '.days-absent', function(){
        var $row = $(this).closest('tr');
        var empId = $row.data('employee-id');
        var max = parseFloat($(this).attr('max'));
        var value = parseFloat($(this).val());
        if (isFinite(max) && isFinite(value) && value > max) {
            $(this).val(max);
            value = max;
        }
        var raw = $.trim($(this).val());
        if (raw === '') {
            setStoredLopValue(empId, null);
        } else if (isFinite(value) && value >= 0) {
            setStoredLopValue(empId, value);
        }
    });

    $tableBody.on('blur', '.days-absent', function(){
        var $row = $(this).closest('tr');
        var empId = $row.data('employee-id');
        var raw = $.trim($(this).val());
        if (raw === '') {
            setStoredLopValue(empId, null);
            $(this).removeClass('input-error');
            return;
        }
        var value = parseFloat(raw);
        var validHalfStep = isFinite(value) && value >= 0 && Math.abs((value * 2) - Math.round(value * 2)) < 0.000001;
        if (!validHalfStep) {
            $(this).val('');
            setStoredLopValue(empId, null);
            showToast('warning', 'Use only 0.5 step values (0, 0.5, 1, 1.5, ...).');
        } else {
            setStoredLopValue(empId, value);
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
            days_absent: selection.days,
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
            days_absent: selection.days // send so server can double-check
        }, '<?php echo addslashes($process_success_text); ?>');
    });

    // Initial load
    fetchWorkingDays();
})(jQuery);
</script>
