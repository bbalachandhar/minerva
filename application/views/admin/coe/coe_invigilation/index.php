<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<style>
.coe-stat-card { border-radius:12px;color:#fff;padding:18px 20px;display:flex;align-items:center;gap:16px;box-shadow:0 4px 15px rgba(0,0,0,.15);margin-bottom:18px;transition:transform .15s; }
.coe-stat-card:hover { transform:translateY(-2px); }
.coe-stat-card .stat-icon { font-size:2.4rem;opacity:.85;flex-shrink:0; }
.coe-stat-card .stat-body .stat-num { font-size:2rem;font-weight:700;line-height:1; }
.coe-stat-card .stat-body .stat-lbl { font-size:.85rem;opacity:.9;margin-top:2px; }
.card-indigo  { background:linear-gradient(135deg,#283593,#3949ab); }
.card-green   { background:linear-gradient(135deg,#2e7d32,#388e3c); }
.card-orange  { background:linear-gradient(135deg,#e65100,#f57c00); }
.card-teal    { background:linear-gradient(135deg,#00796b,#009688); }
.event-card { border-radius:10px;border:1px solid #e0e0e0;margin-bottom:20px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.07); }
.event-card .event-header { padding:14px 18px;background:linear-gradient(135deg,#283593,#3949ab);color:#fff;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px; }
.event-card .event-body { padding:16px 18px; }
.prog-wrap { height:8px;border-radius:4px;background:#e0e0e0;overflow:hidden;margin:6px 0 8px; }
.prog-bar  { height:100%;border-radius:4px;background:linear-gradient(90deg,#283593,#7986cb);transition:width .5s; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1><?php echo lang('coe_invigilation'); ?> <small>Duty Roster Management</small><button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo site_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?php echo site_url('coe/coe_setup'); ?>">CoE</a></li>
      <li class="active">Invigilation</li>
    </ol>
  </section>

  <section class="content">
    <?php if ($this->session->flashdata('msg')) echo $this->session->flashdata('msg'); ?>

    <!-- Session Filter -->
    <div class="box box-default">
      <div class="box-body" style="padding:12px 18px;">
        <form method="get" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
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

    <!-- Stat Cards -->
    <?php
    $all_rooms  = array_sum(array_column($summaries, 'rooms'));
    $all_duties = array_sum(array_column($summaries, 'total_duties'));
    $all_covered= array_sum(array_column($summaries, 'rooms_with_duties'));
    $all_uncov  = array_sum(array_column($summaries, 'rooms_unassigned'));
    ?>
    <div class="row">
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-teal">
          <div class="stat-icon"><i class="fa fa-list-alt"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo count($events); ?></div><div class="stat-lbl">Exam Events</div></div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-indigo">
          <div class="stat-icon"><i class="fa fa-user-circle"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $all_duties; ?></div><div class="stat-lbl">Duties Assigned</div></div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-green">
          <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $all_covered; ?></div><div class="stat-lbl">Rooms Covered</div></div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-orange">
          <div class="stat-icon"><i class="fa fa-exclamation-triangle"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $all_uncov; ?></div><div class="stat-lbl">Rooms Uncovered</div></div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4">
        <div class="box box-default" style="border-radius:10px;">
          <div class="box-header with-border"><h3 class="box-title">Coverage Status</h3></div>
          <div class="box-body" style="text-align:center;">
            <canvas id="invDonut" height="200"></canvas>
            <div style="margin-top:10px;font-size:.9rem;color:#555;">
              <?php echo $all_rooms > 0 ? round(($all_covered/$all_rooms)*100) : 0; ?>% rooms have duties assigned
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <?php if (empty($events)): ?>
          <div class="alert alert-info"><i class="fa fa-info-circle"></i> No CoE-locked events for this session.</div>
        <?php else: ?>
          <?php foreach ($events as $ev):
            $sum = $summaries[$ev->id] ?? ['rooms'=>0,'total_duties'=>0,'rooms_with_duties'=>0,'rooms_unassigned'=>0];
            $pct = $sum['rooms'] > 0 ? round(($sum['rooms_with_duties'] / $sum['rooms']) * 100) : 0;
          ?>
          <div class="event-card">
            <div class="event-header">
              <div>
                <div style="font-size:1.05rem;font-weight:600;"><i class="fa fa-graduation-cap"></i> <?php echo htmlspecialchars($ev->exam_name); ?></div>
                <div style="font-size:.82rem;opacity:.85;"><?php echo htmlspecialchars($ev->exam_group_name); ?> &bull; <?php echo htmlspecialchars($ev->session_name); ?></div>
              </div>
              <span style="background:rgba(255,255,255,.2);border-radius:16px;padding:3px 12px;font-size:.82rem;">
                <?php echo $sum['total_duties']; ?> duties
              </span>
            </div>
            <div class="event-body">
              <div class="row" style="margin-bottom:8px;">
                <div class="col-xs-4 text-center">
                  <div style="font-size:1.3rem;font-weight:700;color:#283593;"><?php echo $sum['rooms']; ?></div>
                  <div style="font-size:.75rem;color:#666;">Total Rooms</div>
                </div>
                <div class="col-xs-4 text-center">
                  <div style="font-size:1.3rem;font-weight:700;color:#2e7d32;"><?php echo $sum['rooms_with_duties']; ?></div>
                  <div style="font-size:.75rem;color:#666;">Rooms Covered</div>
                </div>
                <div class="col-xs-4 text-center">
                  <div style="font-size:1.3rem;font-weight:700;color:#e65100;"><?php echo $sum['rooms_unassigned']; ?></div>
                  <div style="font-size:.75rem;color:#666;">Uncovered</div>
                </div>
              </div>
              <div class="prog-wrap"><div class="prog-bar" style="width:<?php echo $pct; ?>%;"></div></div>
              <div style="font-size:.78rem;color:#666;margin-bottom:12px;"><?php echo $pct; ?>% rooms with at least one duty assigned</div>
              <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="<?php echo site_url('coe/coe_invigilation/manage/' . $ev->id); ?>" class="btn btn-primary btn-sm">
                  <i class="fa fa-cog"></i> Manage Duties
                </a>
                <?php if ($sum['total_duties'] > 0): ?>
                  <a href="<?php echo site_url('coe/coe_invigilation/print_roster/' . $ev->id); ?>"
                     class="btn btn-default btn-sm" target="_blank">
                    <i class="fa fa-print"></i> Print Roster
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>
</div>

<script>
(function(){
  var ctx = document.getElementById('invDonut');
  if (ctx) {
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Covered', 'Uncovered'],
        datasets: [{ data: [<?php echo $all_covered; ?>, <?php echo $all_uncov; ?>],
          backgroundColor: ['#2e7d32','#e0e0e0'], borderWidth: 2, borderColor: '#fff' }]
      },
      options: { cutout:'65%', plugins:{ legend:{ position:'bottom' } } }
    });
  }
})();
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'invigilation']); ?>
