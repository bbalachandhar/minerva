<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-star"></i> Merit Scholarship – Exam Marks &amp; Assignment</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="<?php echo site_url('admin/scholarshipapplication'); ?>">Scholarship Applications</a></li>
            <li class="active">Merit Exam Assignment</li>
        </ol>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <!-- Stats row -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Applicants</span>
                        <span class="info-box-number"><?php echo $stats['all']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-pencil"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Scores Entered</span>
                        <span class="info-box-number"><?php echo $stats['with_score']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Scholarship Assigned</span>
                        <span class="info-box-number"><?php echo $stats['assigned']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-clock-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pending Assignment</span>
                        <span class="info-box-number"><?php echo $stats['unassigned']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tier reference -->
        <div class="box box-default collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Scholarship Tiers (click to expand)</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                </div>
            </div>
            <div class="box-body" style="display:none;">
                <table class="table table-bordered table-condensed" style="max-width:600px;">
                    <thead><tr><th>Category</th><th>% Range</th><th>Scholarship Amount</th></tr></thead>
                    <tbody>
                        <?php foreach ($tiers as $tid => $t): ?>
                        <tr>
                            <td><span class="label label-<?php echo $t['color']; ?>"><?php echo htmlspecialchars($t['label']); ?></span></td>
                            <td><?php echo $t['min']; ?>% – <?php echo $t['max']; ?>%</td>
                            <td>&#8377;<?php echo number_format($t['amount']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CSV Upload panel -->
        <div class="box box-warning collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-upload"></i> Bulk Import Scores via CSV</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                </div>
            </div>
            <div class="box-body" style="display:none;">
                <p class="text-muted">
                    CSV format: <code>reference_no, score</code> &nbsp;(score is out of 100 — header row optional)<br>
                    Example: <code>MCE2025001, 78</code>
                </p>
                <p>
                    <a href="<?php echo site_url('admin/meritscholarship/sample_csv'); ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-download"></i> Download Sample CSV (pre-filled with applicant reference numbers)
                    </a>
                </p>
                <form method="POST" action="<?php echo site_url('admin/meritscholarship/bulk_upload'); ?>"
                      enctype="multipart/form-data" class="form-inline">
                    <?php echo $this->security->get_csrf_token_name(); ?>
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                           value="<?php echo $this->security->get_csrf_hash(); ?>">
                    <div class="form-group">
                        <label class="sr-only">CSV file</label>
                        <input type="file" name="csv_file" accept=".csv,.txt" required class="form-control">
                    </div>
                    &nbsp;
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-upload"></i> Upload &amp; Import
                    </button>
                </form>
            </div>
        </div>

        <!-- Main table -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?php
                    $filter_labels = ['all'=>'All Applicants','with_score'=>'With Scores','assigned'=>'Assigned','unassigned'=>'Not Yet Assigned'];
                    echo $filter_labels[$filter] ?? 'All Applicants';
                    ?>
                    <span class="badge"><?php echo count($applicants); ?></span>
                </h3>
                <div class="box-tools pull-right">
                    <!-- Filter tabs -->
                    <?php
                    $filters = ['all'=>'All','with_score'=>'With Scores','assigned'=>'Assigned','unassigned'=>'Pending'];
                    foreach ($filters as $fkey => $flabel):
                        $active = ($filter === $fkey) ? 'btn-primary' : 'btn-default';
                    ?>
                    <a href="<?php echo site_url('admin/meritscholarship?filter=' . $fkey); ?>"
                       class="btn btn-sm <?php echo $active; ?>"><?php echo $flabel; ?></a>
                    <?php endforeach; ?>

                    <?php if ($stats['unassigned'] > 0): ?>
                    &nbsp;
                    <form method="POST" action="<?php echo site_url('admin/meritscholarship/assign_all'); ?>"
                          style="display:inline;"
                          onsubmit="return confirm('Assign scholarships to all <?php echo $stats['unassigned']; ?> eligible applicants?');">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                               value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fa fa-bolt"></i> Assign All Eligible (<?php echo $stats['unassigned']; ?>)
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body table-responsive">
                <?php if (empty($applicants)): ?>
                    <p class="text-muted text-center" style="padding:30px;">
                        No applicants found for this filter.
                    </p>
                <?php else: ?>
                <table class="table table-bordered table-hover table-striped" id="merit-table"
                       style="white-space:nowrap;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Reference No</th>
                            <th>Name</th>
                            <th>Score / 100</th>
                            <th>%</th>
                            <th>Tier</th>
                            <th>Scholarship Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applicants as $i => $row): ?>
                        <?php
                        $has_score  = $row['mat_exam_percentage'] !== null;
                        $has_sch    = !empty($row['sch_app_id']);
                        $tid        = $row['tier_id'];
                        $tier       = $row['tier'];
                        ?>
                        <tr id="row-<?php echo $row['id']; ?>">
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($row['reference_no']); ?></td>
                            <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>

                            <!-- Score input -->
                            <td>
                                <div class="input-group input-group-sm" style="width:130px;">
                                    <input type="number" min="0" max="100" step="0.5"
                                           class="form-control score-input"
                                           data-id="<?php echo $row['id']; ?>"
                                           value="<?php echo $has_score ? htmlspecialchars($row['mat_exam_score']) : ''; ?>"
                                           placeholder="0–100"
                                           <?php echo $has_sch ? 'disabled title="Scholarship already assigned"' : ''; ?>>
                                    <span class="input-group-btn">
                                        <button class="btn btn-default btn-save-score"
                                                data-id="<?php echo $row['id']; ?>"
                                                title="Save score"
                                                <?php echo $has_sch ? 'disabled' : ''; ?>>
                                            <i class="fa fa-save"></i>
                                        </button>
                                    </span>
                                </div>
                            </td>

                            <!-- Percentage -->
                            <td class="pct-cell-<?php echo $row['id']; ?>">
                                <?php if ($has_score): ?>
                                    <strong><?php echo number_format((float)$row['mat_exam_percentage'], 1); ?>%</strong>
                                <?php else: ?>
                                    <span class="text-muted">–</span>
                                <?php endif; ?>
                            </td>

                            <!-- Tier badge -->
                            <td class="tier-cell-<?php echo $row['id']; ?>">
                                <?php if ($has_sch && !empty($row['sch_type_name'])): ?>
                                    <?php
                                    // Determine color from assigned type_id
                                    $atid   = (int) $row['sch_type_id'];
                                    $acolor = isset($tiers[$atid]) ? $tiers[$atid]['color'] : 'default';
                                    $alabel = isset($tiers[$atid]) ? $tiers[$atid]['label'] : '';
                                    ?>
                                    <span class="label label-<?php echo $acolor; ?>">
                                        <?php echo htmlspecialchars($alabel); ?>
                                    </span>
                                <?php elseif ($has_score && $tier): ?>
                                    <span class="label label-<?php echo $tier['color']; ?>">
                                        <?php echo htmlspecialchars($tier['label']); ?>
                                        <small>(&#8377;<?php echo number_format($tier['amount']); ?>)</small>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">–</span>
                                <?php endif; ?>
                            </td>

                            <!-- Scholarship status -->
                            <td class="sch-cell-<?php echo $row['id']; ?>">
                                <?php if ($has_sch): ?>
                                    <?php
                                    $status_colors = [
                                        'approved' => 'success',
                                        'pending'  => 'warning',
                                        'verified' => 'info',
                                        'rejected' => 'danger',
                                    ];
                                    $sc = $status_colors[$row['sch_status']] ?? 'default';
                                    ?>
                                    <span class="label label-<?php echo $sc; ?>">
                                        <?php echo ucfirst($row['sch_status']); ?>
                                    </span>
                                    &nbsp;
                                    <small class="text-muted">&#8377;<?php echo number_format((float)$row['sch_amount']); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Not assigned</span>
                                <?php endif; ?>
                            </td>

                            <!-- Actions -->
                            <td class="text-right">
                                <?php if (!$has_sch && $has_score): ?>
                                <button class="btn btn-xs btn-success btn-assign-single"
                                        data-id="<?php echo $row['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?>">
                                    <i class="fa fa-check"></i> Assign
                                </button>
                                <?php elseif ($has_sch): ?>
                                <a href="<?php echo site_url('admin/scholarshipapplication'); ?>"
                                   class="btn btn-xs btn-default" target="_blank">
                                    <i class="fa fa-eye"></i> View
                                </a>
                                <?php else: ?>
                                <span class="text-muted small">Enter score first</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function () {

    var csrfName  = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var csrfToken = '<?php echo $this->security->get_csrf_hash(); ?>';

    // Helper: refresh CSRF token from hidden input (CI regenerates on each AJAX POST)
    function refreshCsrf(resp) {
        if (resp && resp.csrf) {
            csrfToken = resp.csrf;
        }
    }

    // ── Save score ─────────────────────────────────────────────────────────────
    $(document).on('click', '.btn-save-score', function () {
        var id    = $(this).data('id');
        var score = $('.score-input[data-id="' + id + '"]').val();
        var btn   = $(this);

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        var payload = { id: id, score: score };
        payload[csrfName] = csrfToken;

        $.post('<?php echo site_url("admin/meritscholarship/save_score"); ?>', payload, function (resp) {
            refreshCsrf(resp);
            btn.prop('disabled', false).html('<i class="fa fa-save"></i>');

            if (resp.status === 'ok') {
                if (resp.cleared) {
                    $('.pct-cell-'  + id).html('<span class="text-muted">–</span>');
                    $('.tier-cell-' + id).html('<span class="text-muted">–</span>');
                    toastr && toastr.success('Score cleared.');
                } else {
                    $('.pct-cell-' + id).html('<strong>' + parseFloat(score).toFixed(1) + '%</strong>');
                    $('.tier-cell-' + id).html(
                        '<span class="label label-' + resp.tier_color + '">' +
                        resp.tier_label +
                        ' <small>&#8377;' + parseInt(resp.amount).toLocaleString('en-IN') + '</small></span>'
                    );
                    // Show assign button if not already assigned
                    var row = $('#row-' + id);
                    var actionCell = row.find('td:last-child');
                    if (actionCell.find('.btn-assign-single').length === 0) {
                        actionCell.html(
                            '<button class="btn btn-xs btn-success btn-assign-single" ' +
                            'data-id="' + id + '" data-name="' + row.find('td:nth-child(3)').text().trim() + '">' +
                            '<i class="fa fa-check"></i> Assign</button>'
                        );
                    }
                    toastr && toastr.success('Score saved.');
                }
            } else {
                toastr ? toastr.error(resp.msg) : alert(resp.msg);
            }
        }, 'json').fail(function () {
            btn.prop('disabled', false).html('<i class="fa fa-save"></i>');
            toastr ? toastr.error('Request failed.') : alert('Request failed.');
        });
    });

    // Allow pressing Enter in score input to trigger save
    $(document).on('keypress', '.score-input', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $(this).closest('.input-group').find('.btn-save-score').trigger('click');
        }
    });

    // ── Assign single ──────────────────────────────────────────────────────────
    $(document).on('click', '.btn-assign-single', function () {
        var id   = $(this).data('id');
        var name = $(this).data('name');
        var btn  = $(this);

        if (!confirm('Assign Merit Scholarship to ' + name + '?')) return;

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        var payload = { id: id };
        payload[csrfName] = csrfToken;

        $.post('<?php echo site_url("admin/meritscholarship/assign_single"); ?>', payload, function (resp) {
            refreshCsrf(resp);
            btn.prop('disabled', false);

            if (resp.status === 'ok') {
                // Update scholarship status cell
                var statusColors = { 16:'success', 17:'primary', 18:'info', 19:'warning', 20:'default' };
                var color = statusColors[resp.tier_id] || 'default';

                $('.sch-cell-' + id).html(
                    '<span class="label label-success">Approved</span>' +
                    ' <small class="text-muted">&#8377;' + parseInt(resp.amount).toLocaleString('en-IN') + '</small>'
                );
                $('.tier-cell-' + id).html(
                    '<span class="label label-' + color + '">' + resp.tier_label + '</span>'
                );
                // Disable score input and replace action button with view link
                $('#row-' + id).find('.score-input, .btn-save-score').prop('disabled', true);
                $('#row-' + id).find('td:last-child').html(
                    '<a href="<?php echo site_url("admin/scholarshipapplication"); ?>" ' +
                    'class="btn btn-xs btn-default" target="_blank"><i class="fa fa-eye"></i> View</a>'
                );
                toastr && toastr.success(resp.msg || 'Scholarship assigned!');
            } else {
                btn.prop('disabled', false).html('<i class="fa fa-check"></i> Assign');
                toastr ? toastr.error(resp.msg) : alert(resp.msg);
            }
        }, 'json').fail(function () {
            btn.prop('disabled', false).html('<i class="fa fa-check"></i> Assign');
            toastr ? toastr.error('Request failed.') : alert('Request failed.');
        });
    });

});
</script>
