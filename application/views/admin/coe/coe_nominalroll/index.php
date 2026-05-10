<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<style>
.coe-stat-card { border-radius:12px;color:#fff;padding:18px 20px;display:flex;align-items:center;gap:16px;box-shadow:0 4px 15px rgba(0,0,0,.15);margin-bottom:18px;transition:transform .15s; }
.coe-stat-card:hover { transform:translateY(-2px); }
.coe-stat-card .stat-icon { font-size:2.4rem;opacity:.85;flex-shrink:0; }
.coe-stat-card .stat-body .stat-num { font-size:2rem;font-weight:700;line-height:1; }
.coe-stat-card .stat-body .stat-lbl { font-size:.85rem;opacity:.9;margin-top:2px; }
.card-teal   { background:linear-gradient(135deg,#00796b,#009688); }
.card-blue   { background:linear-gradient(135deg,#1565c0,#1976d2); }
.card-green  { background:linear-gradient(135deg,#2e7d32,#388e3c); }
.card-purple { background:linear-gradient(135deg,#4527a0,#5c35c5); }
.event-card { border-radius:10px;border:1px solid #e0e0e0;margin-bottom:20px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.07); }
.event-card .event-header { padding:14px 18px;background:linear-gradient(135deg,#37474f,#546e7a);color:#fff;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px; }
.event-card .event-header .ev-title { font-size:1.05rem;font-weight:600; }
.event-card .event-header .ev-meta  { font-size:.82rem;opacity:.85; }
.event-card .event-body { padding:16px 18px; }
.prog-wrap { height:8px;border-radius:4px;background:#e0e0e0;overflow:hidden;margin:6px 0 8px; }
.prog-bar  { height:100%;border-radius:4px;background:linear-gradient(90deg,#4527a0,#7c4dff);transition:width .5s; }
.badge-complete { background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:20px;padding:3px 10px;font-size:.8rem;font-weight:600; }
.badge-partial  { background:#fff3e0;color:#e65100;border:1px solid #ffcc80;border-radius:20px;padding:3px 10px;font-size:.8rem;font-weight:600; }
.badge-none     { background:#fce4ec;color:#b71c1c;border:1px solid #f48fb1;border-radius:20px;padding:3px 10px;font-size:.8rem;font-weight:600; }
.chart-box { background:#fff;border-radius:10px;border:1px solid #e0e0e0;padding:20px;margin-bottom:20px; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1><?php echo lang('coe_nominalroll'); ?> <small>Generation &amp; Management</small><button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo site_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?php echo site_url('coe/coe_setup'); ?>">CoE</a></li>
      <li class="active">Nominal Roll</li>
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
    $all_subjects  = array_sum(array_column($summaries, 'subjects'));
    $all_rolls     = array_sum(array_column($summaries, 'total_rolls'));
    $all_finalized = array_sum(array_column($summaries, 'finalized'));
    $all_students  = array_sum(array_column($summaries, 'total_students'));
    ?>
    <div class="row">
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-teal">
          <div class="stat-icon"><i class="fa fa-list-alt"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo count($events); ?></div><div class="stat-lbl">Exam Events</div></div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-blue">
          <div class="stat-icon"><i class="fa fa-book"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $all_rolls; ?></div><div class="stat-lbl">Rolls Generated</div></div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-purple">
          <div class="stat-icon"><i class="fa fa-lock"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $all_finalized; ?></div><div class="stat-lbl">Finalized</div></div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-green">
          <div class="stat-icon"><i class="fa fa-users"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $all_students; ?></div><div class="stat-lbl">Total Students</div></div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Chart -->
      <div class="col-md-4">
        <div class="chart-box">
          <h4 style="margin-top:0;font-weight:600;color:#37474f;">Roll Status</h4>
          <canvas id="rollDonut" height="200"></canvas>
        </div>
      </div>

      <!-- Event Cards -->
      <div class="col-md-8">
        <?php if (empty($events)): ?>
          <div class="alert alert-info"><i class="fa fa-info-circle"></i> No CoE-locked events for this session.</div>
        <?php else: ?>
          <?php foreach ($events as $ev):
            $sum = $summaries[$ev->id] ?? ['subjects'=>0,'total_rolls'=>0,'finalized'=>0,'pending'=>0,'total_students'=>0];
            $pct = ($sum['subjects'] > 0) ? round(($sum['total_rolls'] / $sum['subjects']) * 100) : 0;
            if ($sum['subjects'] == 0)                 { $badge = '<span class="badge-none">No Subjects</span>'; }
            elseif ($sum['total_rolls'] >= $sum['subjects']) { $badge = '<span class="badge-complete">All Generated</span>'; }
            else                                       { $badge = '<span class="badge-partial">Partial</span>'; }
          ?>
          <div class="event-card">
            <div class="event-header">
              <div>
                <div class="ev-title"><i class="fa fa-graduation-cap"></i> <?php echo htmlspecialchars($ev->exam_name); ?></div>
                <div class="ev-meta"><?php echo htmlspecialchars($ev->exam_group_name); ?> &bull; <?php echo htmlspecialchars($ev->session_name); ?></div>
              </div>
              <?php echo $badge; ?>
            </div>
            <div class="event-body">
              <div class="row" style="margin-bottom:8px;">
                <div class="col-xs-3 text-center"><div style="font-size:1.3rem;font-weight:700;color:#4527a0;"><?php echo $sum['subjects']; ?></div><div style="font-size:.75rem;color:#666;">Subjects</div></div>
                <div class="col-xs-3 text-center"><div style="font-size:1.3rem;font-weight:700;color:#1565c0;"><?php echo $sum['total_rolls']; ?></div><div style="font-size:.75rem;color:#666;">Rolls</div></div>
                <div class="col-xs-3 text-center"><div style="font-size:1.3rem;font-weight:700;color:#2e7d32;"><?php echo $sum['finalized']; ?></div><div style="font-size:.75rem;color:#666;">Finalized</div></div>
                <div class="col-xs-3 text-center"><div style="font-size:1.3rem;font-weight:700;color:#b71c1c;"><?php echo $sum['total_students']; ?></div><div style="font-size:.75rem;color:#666;">Students</div></div>
              </div>
              <div class="prog-wrap"><div class="prog-bar" style="width:<?php echo $pct; ?>%;"></div></div>
              <div style="font-size:.78rem;color:#666;margin-bottom:12px;"><?php echo $pct; ?>% rolls generated</div>
              <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <?php if ($this->rbac->hasPrivilege('coe_nominalroll', 'can_add')): ?>
                  <button class="btn btn-success btn-sm confirm-generate"
                          data-url="<?php echo site_url('coe/coe_nominalroll/generate/' . $ev->id); ?>"
                          data-name="<?php echo htmlspecialchars($ev->exam_name); ?>">
                    <i class="fa fa-refresh"></i> <?php echo $sum['total_rolls'] > 0 ? 'Regenerate' : 'Generate'; ?> Rolls
                  </button>
                <?php endif; ?>
                <?php if ($sum['total_rolls'] > 0): ?>
                  <a href="<?php echo site_url('coe/coe_nominalroll/view/' . $ev->id); ?>" class="btn btn-primary btn-sm">
                    <i class="fa fa-list"></i> View Rolls
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
  var ctx = document.getElementById('rollDonut');
  if (ctx) {
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Finalized', 'Draft', 'Not Generated'],
        datasets: [{ data: [<?php echo $all_finalized; ?>, <?php echo max(0,$all_rolls-$all_finalized); ?>, <?php echo max(0,$all_subjects-$all_rolls); ?>],
          backgroundColor: ['#2e7d32','#1565c0','#e0e0e0'], borderWidth: 2, borderColor: '#fff' }]
      },
      options: { cutout:'65%', plugins:{ legend:{ position:'bottom', labels:{ font:{ size:12 } } } } }
    });
  }

  document.querySelectorAll('.confirm-generate').forEach(function(btn){
    btn.addEventListener('click', function(){
      var url = btn.dataset.url, name = btn.dataset.name;
      if (typeof swal !== 'undefined') {
        swal({ title:'Generate Nominal Rolls?', text:'For "'+name+'". Finalized rolls will not be changed.', type:'info',
          showCancelButton:true, confirmButtonColor:'#4527a0', confirmButtonText:'Generate', cancelButtonText:'Cancel'
        }, function(){ window.location.href = url; });
      } else if (confirm('Generate nominal rolls for "'+name+'"?')) { window.location.href = url; }
    });
  });
})();
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'nominal_roll']); ?>
