<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
/* ===================== CoE Hall Ticket View ===================== */
.coe-stat-card {
    border-radius: 12px; color: #fff; padding: 18px 20px;
    display: flex; align-items: center; gap: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,.15); margin-bottom: 18px;
    transition: transform .15s;
}
.coe-stat-card:hover { transform: translateY(-2px); }
.coe-stat-card .stat-icon { font-size: 2.4rem; opacity: .85; flex-shrink: 0; }
.coe-stat-card .stat-body .stat-num { font-size: 2rem; font-weight: 700; line-height: 1; }
.coe-stat-card .stat-body .stat-lbl { font-size: .85rem; opacity: .9; margin-top: 2px; }
.card-teal   { background: linear-gradient(135deg,#00796b,#009688); }
.card-blue   { background: linear-gradient(135deg,#1565c0,#1976d2); }
.card-orange { background: linear-gradient(135deg,#e65100,#f57c00); }
.card-green  { background: linear-gradient(135deg,#2e7d32,#388e3c); }
.valid-pill   { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; border-radius:20px; padding:2px 9px; font-size:.78rem; font-weight:600; white-space:nowrap; }
.invalid-pill { background:#fce4ec; color:#b71c1c; border:1px solid #f48fb1; border-radius:20px; padding:2px 9px; font-size:.78rem; font-weight:600; white-space:nowrap; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1><?php echo lang('coe_hallticket'); ?> <small><?php echo htmlspecialchars($batch_exam->exam); ?></small></h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo site_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?php echo site_url('coe/coe_hallticket'); ?>">Hall Tickets</a></li>
      <li class="active"><?php echo htmlspecialchars($batch_exam->exam); ?></li>
    </ol>
  </section>

  <section class="content">
    <?php if ($this->session->flashdata('msg')) echo $this->session->flashdata('msg'); ?>

    <!-- Stat Cards -->
    <div class="row">
      <div class="col-sm-6 col-md-4">
        <div class="coe-stat-card card-blue">
          <div class="stat-icon"><i class="fa fa-users"></i></div>
          <div class="stat-body">
            <div class="stat-num"><?php echo $summary['eligible']; ?></div>
            <div class="stat-lbl">Eligible Students</div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="coe-stat-card card-green">
          <div class="stat-icon"><i class="fa fa-id-card"></i></div>
          <div class="stat-body">
            <div class="stat-num"><?php echo $summary['generated']; ?></div>
            <div class="stat-lbl">Hall Tickets Generated</div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="coe-stat-card card-orange">
          <div class="stat-icon"><i class="fa fa-clock-o"></i></div>
          <div class="stat-body">
            <div class="stat-num"><?php echo $summary['pending']; ?></div>
            <div class="stat-lbl">Pending</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Bar -->
    <div class="box box-default" style="margin-bottom:18px;">
      <div class="box-body" style="padding:12px 16px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <a href="<?php echo site_url('coe/coe_hallticket'); ?>" class="btn btn-default btn-sm">
          <i class="fa fa-arrow-left"></i> Back to Events
        </a>
        <?php if ($summary['pending'] > 0 && $this->rbac->hasPrivilege('coe_hallticket', 'can_add')): ?>
          <button class="btn btn-success btn-sm confirm-generate"
                  data-url="<?php echo site_url('coe/coe_hallticket/generate/' . $batch_exam->id); ?>"
                  data-count="<?php echo $summary['pending']; ?>"
                  data-name="<?php echo htmlspecialchars($batch_exam->exam); ?>">
            <i class="fa fa-magic"></i> Generate Remaining <?php echo $summary['pending']; ?> Ticket(s)
          </button>
        <?php endif; ?>
        <?php if (!empty($hall_tickets)): ?>
          <a href="<?php echo site_url('coe/coe_hallticket/print_all/' . $batch_exam->id); ?>"
             class="btn btn-primary btn-sm" target="_blank">
            <i class="fa fa-print"></i> Print All Hall Tickets
          </a>
        <?php endif; ?>
        <span class="text-muted" style="font-size:.85rem;margin-left:auto;">
          <i class="fa fa-calendar"></i>
          <?php echo $batch_exam->date_from ? date('d M Y', strtotime($batch_exam->date_from)) : 'N/A'; ?>
          <?php echo $batch_exam->date_to   ? ' &ndash; ' . date('d M Y', strtotime($batch_exam->date_to)) : ''; ?>
        </span>
      </div>
    </div>

    <!-- Hall Tickets DataTable -->
    <div class="box box-default">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-id-card"></i> Hall Tickets</h3>
      </div>
      <div class="box-body">
        <?php if (empty($hall_tickets)): ?>
          <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            No hall tickets generated yet.
            <?php if ($this->rbac->hasPrivilege('coe_hallticket', 'can_add')): ?>
              <a href="<?php echo site_url('coe/coe_hallticket/generate/' . $batch_exam->id); ?>" class="alert-link">Generate Now</a>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table id="htTable" class="table table-bordered table-striped table-hover" style="width:100%">
              <thead>
                <tr style="background:#37474f;color:#fff;">
                  <th>#</th>
                  <th>Hall Ticket No</th>
                  <th>Student Name</th>
                  <th>Register No</th>
                  <th>Programme</th>
                  <th>Generated At</th>
                  <th>Downloads</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($hall_tickets as $i => $ht): ?>
                <tr>
                  <td><?php echo $i + 1; ?></td>
                  <td><strong style="font-family:monospace;color:#1565c0;"><?php echo htmlspecialchars($ht->hall_ticket_no); ?></strong></td>
                  <td><?php echo htmlspecialchars(trim($ht->firstname . ' ' . $ht->lastname)); ?></td>
                  <td><?php echo htmlspecialchars($ht->register_no ?? '—'); ?></td>
                  <td>
                    <?php echo htmlspecialchars($ht->class_name ?? ''); ?>
                    <?php if ($ht->section_name ?? null): ?> <span class="text-muted">/ <?php echo htmlspecialchars($ht->section_name); ?></span><?php endif; ?>
                  </td>
                  <td><?php echo $ht->generated_at ? date('d M Y H:i', strtotime($ht->generated_at)) : '—'; ?></td>
                  <td class="text-center"><?php echo (int)$ht->downloaded_count; ?></td>
                  <td class="text-center">
                    <?php if ($ht->is_valid): ?>
                      <span class="valid-pill"><i class="fa fa-check-circle"></i> Valid</span>
                    <?php else: ?>
                      <span class="invalid-pill"><i class="fa fa-times-circle"></i> Invalidated</span>
                    <?php endif; ?>
                  </td>
                  <td style="white-space:nowrap;">
                    <a href="<?php echo site_url('coe/coe_hallticket/print_pdf/' . $ht->id); ?>"
                       class="btn btn-xs btn-primary" target="_blank" title="Print PDF">
                      <i class="fa fa-print"></i> Print
                    </a>
                    <?php if ($ht->is_valid && $this->rbac->hasPrivilege('coe_hallticket', 'can_edit')): ?>
                      <button class="btn btn-xs btn-danger confirm-invalidate"
                              data-url="<?php echo site_url('coe/coe_hallticket/invalidate/' . $ht->id); ?>"
                              data-ht="<?php echo htmlspecialchars($ht->hall_ticket_no); ?>"
                              title="Invalidate">
                        <i class="fa fa-ban"></i>
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</div>

<script>
(function () {
    'use strict';

    // DataTable
    if ($.fn.DataTable && document.getElementById('htTable')) {
        $('#htTable').DataTable({
            pageLength: 25,
            order: [[1, 'asc']],
            language: { search: 'Search:' }
        });
    }

    // Confirm generate
    document.querySelectorAll('.confirm-generate').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url   = btn.dataset.url;
            var count = btn.dataset.count;
            var name  = btn.dataset.name;
            if (typeof swal !== 'undefined') {
                swal({
                    title: 'Generate Hall Tickets?',
                    text: 'Generate ' + count + ' hall ticket(s) for "' + name + '"?',
                    type: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#2e7d32',
                    confirmButtonText: 'Generate',
                    cancelButtonText: 'Cancel'
                }, function () { window.location.href = url; });
            } else if (confirm('Generate ' + count + ' hall ticket(s)?')) {
                window.location.href = url;
            }
        });
    });

    // Confirm invalidate
    document.querySelectorAll('.confirm-invalidate').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = btn.dataset.url;
            var ht  = btn.dataset.ht;
            if (typeof swal !== 'undefined') {
                swal({
                    title: 'Invalidate Hall Ticket?',
                    text: 'This will invalidate hall ticket ' + ht + '. This cannot be undone.',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#c62828',
                    confirmButtonText: 'Invalidate',
                    cancelButtonText: 'Cancel'
                }, function () { window.location.href = url; });
            } else if (confirm('Invalidate hall ticket ' + ht + '?')) {
                window.location.href = url;
            }
        });
    });
})();
</script>
