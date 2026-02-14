<!-- Attendance Exceptions Index View -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Biometric Punch Exceptions</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Attendance Exceptions</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Unresolved Exceptions (<?php echo isset($exception_count) ? $exception_count : 0; ?>)</h3>
                        </div>
                        <div class="card-body">
                            <?php if (isset($exceptions) && !empty($exceptions)) { ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Staff</th>
                                                <th>Punch Time</th>
                                                <th>Exception Reason</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($exceptions as $exception) { ?>
                                                <tr>
                                                    <td>
                                                        <?php 
                                                        $staff_id = $exception['staff_id'];
                                                        $staff_info = isset($exception['staff_name']) ? $exception['staff_name'] : 'Staff #' . $staff_id;
                                                        echo $staff_info;
                                                        ?>
                                                    </td>
                                                    <td><?php echo date('Y-m-d H:i:s', strtotime($exception['punch_time'])); ?></td>
                                                    <td>
                                                        <span class="badge badge-warning">
                                                            <?php 
                                                            if (isset($exception['exception_reason'])) {
                                                                echo $exception['exception_reason'];
                                                            } else {
                                                                echo 'Biometric Match Issue';
                                                            }
                                                            ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-danger">Unresolved</span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-xs btn-info" 
                                                                data-toggle="modal" 
                                                                data-target="#contextModal" 
                                                                onclick="viewPunchContext(<?php echo $exception['id']; ?>)">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                        <button class="btn btn-xs btn-success" 
                                                                onclick="quickResolve(<?php echo $exception['id']; ?>, 'assign_current_day')">
                                                            <i class="fas fa-check"></i> Resolve
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } else { ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> No unresolved exceptions found.
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Context Modal -->
<div class="modal fade" id="contextModal" tabindex="-1" role="dialog" aria-labelledby="contextModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contextModalLabel">Punch Context</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="contextBody">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i> Loading...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="resolveWithCurrentDay()">Assign to Current Day</button>
                <button type="button" class="btn btn-warning" onclick="resolveWithPreviousDay()">Assign to Previous Day</button>
                <button type="button" class="btn btn-danger" onclick="markAsInvalid()">Mark as Invalid</button>
            </div>
        </div>
    </div>
</div>

<script>
var currentPunchId = null;

function viewPunchContext(punchId) {
    currentPunchId = punchId;
    
    $.ajax({
        url: '<?php echo site_url("admin/attendance_exceptions/get_punch_context"); ?>',
        type: 'POST',
        data: { punch_id: punchId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                var html = '<div class="punch-context">';
                html += '<h6>Punch Details:</h6>';
                html += '<p><strong>Time:</strong> ' + response.punch.punch_time + '</p>';
                html += '<p><strong>Date:</strong> ' + response.punch_date + '</p>';
                
                // Current day punches
                html += '<hr><h6>Current Day Punches:</h6>';
                if (response.current_day_punches && response.current_day_punches.length > 0) {
                    html += '<ul>';
                    response.current_day_punches.forEach(function(punch) {
                        html += '<li>' + punch.punch_time + '</li>';
                    });
                    html += '</ul>';
                } else {
                    html += '<p class="text-muted">No punches for current day</p>';
                }
                
                // Previous day info
                html += '<hr><h6>Previous Day (' + response.previous_date + '):</h6>';
                if (response.previous_day_punches && response.previous_day_punches.length > 0) {
                    html += '<p><strong>Punches:</strong></p>';
                    html += '<ul>';
                    response.previous_day_punches.forEach(function(punch) {
                        html += '<li>' + punch.punch_time + '</li>';
                    });
                    html += '</ul>';
                } else {
                    html += '<p class="text-muted">No punches for previous day</p>';
                }
                
                if (response.previous_attendance) {
                    html += '<p><strong>Attendance Status:</strong> ' + response.previous_attendance.status + '</p>';
                } else {
                    html += '<p class="text-muted">No attendance record for previous day</p>';
                }
                
                html += '</div>';
                $('#contextBody').html(html);
            } else {
                $('#contextBody').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#contextBody').html('<div class="alert alert-danger">Error loading punch context</div>');
        }
    });
}

function quickResolve(punchId, action) {
    resolveException(punchId, action);
}

function resolveWithCurrentDay() {
    if (currentPunchId) {
        resolveException(currentPunchId, 'assign_current_day');
    }
}

function resolveWithPreviousDay() {
    if (currentPunchId) {
        resolveException(currentPunchId, 'assign_previous_day');
    }
}

function markAsInvalid() {
    if (currentPunchId) {
        resolveException(currentPunchId, 'mark_invalid');
    }
}

function resolveException(punchId, action) {
    $.ajax({
        url: '<?php echo site_url("admin/attendance_exceptions/resolve"); ?>',
        type: 'POST',
        data: { 
            punch_id: punchId,
            action: action
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000
                }, function() {
                    location.reload();
                });
                $('#contextModal').modal('hide');
            } else {
                swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            swal.fire('Error', 'Failed to resolve exception', 'error');
        }
    });
}
</script>

<style>
    .punch-context {
        font-size: 14px;
        line-height: 1.6;
    }
    
    .punch-context ul {
        margin-left: 20px;
    }
    
    .punch-context li {
        margin: 5px 0;
    }
    
    .btn-xs {
        padding: 3px 8px;
        font-size: 12px;
        line-height: 1.4;
        border-radius: 3px;
    }
</style>
