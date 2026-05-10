<?php
// Require Chart.js 3.9.1 (modern, inline — project footer only has v1.0.2)
defined('BASEPATH') or exit('No direct script access allowed');
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<style>
/* ===================== CoE Hall Ticket Index ===================== */
.coe-stat-card {
    border-radius: 12px;
    color: #fff;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,.15);
    margin-bottom: 18px;
    transition: transform .15s;
}
.coe-stat-card:hover { transform: translateY(-2px); }
.coe-stat-card .stat-icon { font-size: 2.4rem; opacity: .85; flex-shrink: 0; }
.coe-stat-card .stat-body .stat-num  { font-size: 2rem; font-weight: 700; line-height: 1; }
.coe-stat-card .stat-body .stat-lbl  { font-size: .85rem; opacity: .9; margin-top: 2px; }
.card-teal   { background: linear-gradient(135deg,#00796b,#009688); }
.card-blue   { background: linear-gradient(135deg,#1565c0,#1976d2); }
.card-orange { background: linear-gradient(135deg,#e65100,#f57c00); }
.card-green  { background: linear-gradient(135deg,#2e7d32,#388e3c); }
.card-purple { background: linear-gradient(135deg,#4527a0,#5c35c5); }

.event-card {
    border-radius: 10px;
    border: 1px solid #e0e0e0;
    margin-bottom: 20px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
}
.event-card .event-header {
    padding: 14px 18px;
    background: linear-gradient(135deg,#37474f,#546e7a);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}
.event-card .event-header .ev-title { font-size: 1.05rem; font-weight: 600; }
.event-card .event-header .ev-meta  { font-size: .82rem; opacity: .85; }
.event-card .event-body { padding: 16px 18px; }
.ht-progress-wrap { height: 10px; border-radius: 5px; background: #e0e0e0; overflow: hidden; margin: 6px 0 8px; }
.ht-progress-bar  { height: 100%; border-radius: 5px; background: linear-gradient(90deg,#2e7d32,#66bb6a); transition: width .5s; }
.status-badge-complete { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; border-radius: 20px; padding: 3px 10px; font-size: .8rem; font-weight: 600; }
.status-badge-partial   { background: #fff3e0; color: #e65100; border: 1px solid #ffcc80; border-radius: 20px; padding: 3px 10px; font-size: .8rem; font-weight: 600; }
.status-badge-none      { background: #fce4ec; color: #b71c1c; border: 1px solid #f48fb1; border-radius: 20px; padding: 3px 10px; font-size: .8rem; font-weight: 600; }
.chart-box { background: #fff; border-radius: 10px; border: 1px solid #e0e0e0; padding: 20px; margin-bottom: 20px; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1><?php echo lang('coe_hallticket'); ?> <small>Generation &amp; Management</small></h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo site_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?php echo site_url('coe/coe_setup'); ?>">CoE</a></li>
      <li class="active">Hall Tickets</li>
    </ol>
  </section>

  <section class="content">
    <?php if ($this->session->flashdata('msg')) echo $this->session->flashdata('msg'); ?>

    <!-- Session Filter -->
    <div class="box box-default">
      <div class="box-body" style="padding:14px 18px;">
        <form method="get" action="" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
          <label style="margin:0;font-weight:600;">Session:</label>
          <select name="session_id" class="form-control" style="width:200px;" onchange="this.form.submit()">
            <?php foreach ($sessions as $sess): ?>
              <option value="<?php echo $sess["id"]; ?>" <?php echo ($sess["id"] == $selected_session) ? 'selected' : ''; ?>>
                <?php echo $sess["session"]; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>
    </div>

    <!-- Summary Stat Cards -->
    <?php
    $total_events    = count($events);
    $total_eligible  = array_sum(array_column($summaries, 'eligible'));
    $total_generated = array_sum(array_column($summaries, 'generated'));
    $total_pending   = array_sum(array_column($summaries, 'pending'));
    $complete_events = count(array_filter($summaries, fn($s) => $s['complete']));
    ?>
    <div class="row">
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-teal">
          <div class="stat-icon"><i class="fa fa-calendar-check-o"></i></div>
          <div class="stat-body">
            <div class="stat-num"><?php echo $total_events; ?></div>
            <div class="stat-lbl">Exam Events</div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-blue">
          <div class="stat-icon"><i class="fa fa-users"></i></div>
          <div class="stat-body">
            <div class="stat-num"><?php echo $total_eligible; ?></div>
            <div class="stat-lbl">Eligible Students</div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-green">
          <div class="stat-icon"><i class="fa fa-id-card"></i></div>
          <div class="stat-body">
            <div class="stat-num"><?php echo $total_generated; ?></div>
            <div class="stat-lbl">Hall Tickets Generated</div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-orange">
          <div class="stat-icon"><i class="fa fa-clock-o"></i></div>
          <div class="stat-body">
            <div class="stat-num"><?php echo $total_pending; ?></div>
            <div class="stat-lbl">Pending Generation</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Chart + Event List -->
    <div class="row">
      <!-- Progress Donut -->
      <div class="col-md-4">
        <div class="chart-box">
          <h4 style="margin-top:0;font-weight:600;color:#37474f;">Generation Progress</h4>
          <canvas id="htDonut" height="200"></canvas>
          <p class="text-center text-muted" style="margin-top:10px;font-size:.85rem;">
            <?php echo $total_generated; ?> of <?php echo $total_eligible; ?> tickets generated
          </p>
        </div>
        <div class="chart-box">
          <h4 style="margin-top:0;font-weight:600;color:#37474f;">Event Completion</h4>
          <canvas id="evBar" height="180"></canvas>
        </div>
      </div>

      <!-- Event Cards -->
      <div class="col-md-8">
        <?php if (empty($events)): ?>
          <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            No CoE-locked exam events found for this session.
            <a href="<?php echo site_url('coe/coe_application'); ?>" class="alert-link">Mark events as CoE</a>
            and run eligibility first.
          </div>
        <?php else: ?>
          <?php foreach ($events as $ev):
            $sum = $summaries[$ev->id] ?? ['eligible'=>0,'generated'=>0,'pending'=>0,'complete'=>false];
            $pct = ($sum['eligible'] > 0) ? round(($sum['generated'] / $sum['eligible']) * 100) : 0;
            if ($sum['eligible'] == 0)         $badge_class = 'status-badge-none';
            elseif ($sum['complete'])           $badge_class = 'status-badge-complete';
            else                               $badge_class = 'status-badge-partial';
            $badge_label = $sum['eligible'] == 0 ? 'No Eligible' : ($sum['complete'] ? 'Complete' : 'Partial');
          ?>
          <div class="event-card">
            <div class="event-header">
              <div>
                <div class="ev-title"><i class="fa fa-graduation-cap"></i> <?php echo htmlspecialchars($ev->exam_name); ?></div>
                <div class="ev-meta">
                  <?php echo htmlspecialchars($ev->exam_group_name); ?> &bull;
                  Session: <?php echo htmlspecialchars($ev->session_name); ?>
                  <?php if ($ev->date_from): ?>
                    &bull; <?php echo date('d M Y', strtotime($ev->date_from)); ?>
                    <?php if ($ev->date_to): ?> &ndash; <?php echo date('d M Y', strtotime($ev->date_to)); ?><?php endif; ?>
                  <?php endif; ?>
                </div>
              </div>
              <span class="<?php echo $badge_class; ?>"><?php echo $badge_label; ?></span>
            </div>
            <div class="event-body">
              <div class="row" style="margin-bottom:10px;">
                <div class="col-xs-4 text-center">
                  <div style="font-size:1.4rem;font-weight:700;color:#1565c0;"><?php echo $sum['eligible']; ?></div>
                  <div style="font-size:.78rem;color:#666;">Eligible</div>
                </div>
                <div class="col-xs-4 text-center">
                  <div style="font-size:1.4rem;font-weight:700;color:#2e7d32;"><?php echo $sum['generated']; ?></div>
                  <div style="font-size:.78rem;color:#666;">Generated</div>
                </div>
                <div class="col-xs-4 text-center">
                  <div style="font-size:1.4rem;font-weight:700;color:#e65100;"><?php echo $sum['pending']; ?></div>
                  <div style="font-size:.78rem;color:#666;">Pending</div>
                </div>
              </div>
              <!-- Progress bar -->
              <div class="ht-progress-wrap">
                <div class="ht-progress-bar" style="width:<?php echo $pct; ?>%;"></div>
              </div>
              <div style="font-size:.8rem;color:#666;margin-bottom:12px;"><?php echo $pct; ?>% generated</div>
              <!-- Action buttons -->
              <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <?php if ($sum['pending'] > 0 && $this->rbac->hasPrivilege('coe_hallticket', 'can_add')): ?>
                  <button class="btn btn-success btn-sm confirm-generate"
                          data-url="<?php echo site_url('coe/coe_hallticket/generate/' . $ev->id); ?>"
                          data-count="<?php echo $sum['pending']; ?>"
                          data-name="<?php echo htmlspecialchars($ev->exam_name); ?>">
                    <i class="fa fa-magic"></i> Generate <?php echo $sum['pending']; ?> Ticket(s)
                  </button>
                <?php endif; ?>
                <?php if ($sum['generated'] > 0): ?>
                  <a href="<?php echo site_url('coe/coe_hallticket/view/' . $ev->id); ?>" class="btn btn-primary btn-sm">
                    <i class="fa fa-list"></i> View Hall Tickets
                  </a>
                  <a href="<?php echo site_url('coe/coe_hallticket/print_all/' . $ev->id); ?>" class="btn btn-default btn-sm" target="_blank">
                    <i class="fa fa-print"></i> Print All
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div><!-- /.row -->
  </section>
</div>

<script>
(function () {
    'use strict';
    var generated = <?php echo (int)$total_generated; ?>;
    var pending   = <?php echo (int)$total_pending; ?>;

    // Donut: generated vs pending
    var htCtx = document.getElementById('htDonut');
    if (htCtx) {
        new Chart(htCtx, {
            type: 'doughnut',
            data: {
                labels: ['Generated', 'Pending'],
                datasets: [{
                    data: [generated, pending],
                    backgroundColor: ['#43a047', '#e65100'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                cutout: '65%',
                plugins: { legend: { position: 'bottom', labels: { font: { size: 12 } } } }
            }
        });
    }

    // Bar chart: per-event generated vs eligible
    var evCtx = document.getElementById('evBar');
    if (evCtx) {
        var evLabels  = <?php echo json_encode(array_map(fn($e) => mb_strimwidth($e->exam_name, 0, 20, '…'), $events)); ?>;
        var evElig    = <?php echo json_encode(array_map(fn($e) => $summaries[$e->id]['eligible'] ?? 0, $events)); ?>;
        var evGenerated = <?php echo json_encode(array_map(fn($e) => $summaries[$e->id]['generated'] ?? 0, $events)); ?>;

        new Chart(evCtx, {
            type: 'bar',
            data: {
                labels: evLabels,
                datasets: [
                    { label: 'Eligible',   data: evElig,      backgroundColor: 'rgba(21,101,192,.25)', borderColor: '#1565c0', borderWidth: 1 },
                    { label: 'Generated',  data: evGenerated, backgroundColor: 'rgba(46,125,50,.6)',   borderColor: '#2e7d32', borderWidth: 1 }
                ]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    // Confirm generate with SweetAlert
    document.querySelectorAll('.confirm-generate').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url   = btn.dataset.url;
            var count = btn.dataset.count;
            var name  = btn.dataset.name;
            if (typeof swal === 'undefined') {
                if (confirm('Generate ' + count + ' hall ticket(s) for "' + name + '"?')) {
                    window.location.href = url;
                }
                return;
            }
            swal({
                title: 'Generate Hall Tickets?',
                text: 'This will generate ' + count + ' hall ticket(s) for "' + name + '".',
                type: 'info',
                showCancelButton: true,
                confirmButtonColor: '#2e7d32',
                confirmButtonText: 'Yes, Generate',
                cancelButtonText: 'Cancel',
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function () {
                window.location.href = url;
            });
        });
    });
})();
</script>
