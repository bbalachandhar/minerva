<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.coe-stat-card { border-radius:12px;color:#fff;padding:18px 20px;display:flex;align-items:center;gap:16px;box-shadow:0 4px 15px rgba(0,0,0,.15);margin-bottom:18px; }
.coe-stat-card .stat-icon { font-size:2.4rem;opacity:.85;flex-shrink:0; }
.coe-stat-card .stat-body .stat-num { font-size:2rem;font-weight:700;line-height:1; }
.coe-stat-card .stat-body .stat-lbl { font-size:.95rem;opacity:.9;margin-top:2px; }
.card-blue   { background:linear-gradient(135deg,#1565c0,#1976d2); }
.card-green  { background:linear-gradient(135deg,#2e7d32,#388e3c); }
.card-purple { background:linear-gradient(135deg,#4527a0,#5c35c5); }
.card-orange { background:linear-gradient(135deg,#e65100,#f57c00); }
.roll-card { border-radius:8px;border:1px solid #e0e0e0;margin-bottom:14px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,.06); }
.roll-card .roll-header { padding:10px 16px;background:#f5f5f5;border-bottom:1px solid #e0e0e0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px; }
.roll-card .roll-header .sub-name { font-weight:700;color:#37474f;font-size:1.1rem; }
.roll-card .roll-header .sub-meta { font-size:.9rem;color:#777; }
.roll-card .roll-body { padding:12px 16px; }
.final-pill { background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:16px;padding:3px 12px;font-size:.88rem;font-weight:600; }
.draft-pill  { background:#fff3e0;color:#e65100;border:1px solid #ffcc80;border-radius:16px;padding:3px 12px;font-size:.88rem;font-weight:600; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1><?php echo lang('coe_nominalroll'); ?> <small><?php echo htmlspecialchars($batch_exam->exam); ?></small><button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo site_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?php echo site_url('coe/coe_nominalroll'); ?>">Nominal Roll</a></li>
      <li class="active"><?php echo htmlspecialchars($batch_exam->exam); ?></li>
    </ol>
  </section>

  <section class="content">
    <?php if ($this->session->flashdata('msg')) echo $this->session->flashdata('msg'); ?>

    <!-- Stat Cards -->
    <div class="row">
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-purple">
          <div class="stat-icon"><i class="fa fa-book"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $summary['subjects']; ?></div><div class="stat-lbl">Total Subjects</div></div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-blue">
          <div class="stat-icon"><i class="fa fa-list-alt"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $summary['total_rolls']; ?></div><div class="stat-lbl">Rolls Generated</div></div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-green">
          <div class="stat-icon"><i class="fa fa-lock"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $summary['finalized']; ?></div><div class="stat-lbl">Finalized</div></div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-orange">
          <div class="stat-icon"><i class="fa fa-users"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $summary['total_students']; ?></div><div class="stat-lbl">Total Student-Subjects</div></div>
        </div>
      </div>
    </div>

    <!-- Action Bar -->
    <div class="box box-default" style="margin-bottom:18px;">
      <div class="box-body" style="padding:12px 16px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <a href="<?php echo site_url('coe/coe_nominalroll'); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
        <?php if ($this->rbac->hasPrivilege('coe_nominalroll', 'can_add')): ?>
          <button class="btn btn-success btn-sm confirm-generate"
                  data-url="<?php echo site_url('coe/coe_nominalroll/generate/' . $batch_exam->id); ?>"
                  data-name="<?php echo htmlspecialchars($batch_exam->exam); ?>">
            <i class="fa fa-refresh"></i> <?php echo $summary['total_rolls'] > 0 ? 'Regenerate' : 'Generate'; ?> All Rolls
          </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Rolls per Subject -->
    <?php if (empty($rolls)): ?>
      <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> No nominal rolls generated yet.
        <?php if ($this->rbac->hasPrivilege('coe_nominalroll', 'can_add')): ?>
          <a href="<?php echo site_url('coe/coe_nominalroll/generate/' . $batch_exam->id); ?>" class="alert-link">Generate Now</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <?php foreach ($rolls as $roll): ?>
      <div class="roll-card">
        <div class="roll-header">
          <div>
            <span class="sub-name"><i class="fa fa-book"></i> <?php echo htmlspecialchars($roll->subject_name); ?></span>
            <span class="sub-meta" style="margin-left:8px;">
              <?php echo htmlspecialchars($roll->subject_code ?? ''); ?>
              <?php if ($roll->subject_type ?? null): ?> &bull; <?php echo ucfirst($roll->subject_type); ?><?php endif; ?>
            </span>
          </div>
          <?php if ($roll->is_final): ?>
            <span class="final-pill"><i class="fa fa-lock"></i> Finalized</span>
          <?php else: ?>
            <span class="draft-pill"><i class="fa fa-pencil"></i> Draft</span>
          <?php endif; ?>
        </div>
        <div class="roll-body">
          <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
            <div>
              <span style="font-size:1.5rem;font-weight:700;color:#1565c0;"><?php echo (int)$roll->total_students; ?></span>
              <span style="font-size:.95rem;color:#666;margin-left:4px;">students</span>
            </div>
            <div style="font-size:.92rem;color:#777;">
              Generated: <?php echo $roll->generated_at ? date('d M Y H:i', strtotime($roll->generated_at)) : '—'; ?>
            </div>
            <div style="margin-left:auto;display:flex;gap:8px;">
              <a href="<?php echo site_url('coe/coe_nominalroll/print_pdf/' . $roll->id); ?>"
                 class="btn btn-xs btn-primary" target="_blank">
                <i class="fa fa-print"></i> Print Roll
              </a>
              <?php if (!$roll->is_final && $this->rbac->hasPrivilege('coe_nominalroll', 'can_edit')): ?>
                <button class="btn btn-xs btn-warning confirm-finalize"
                        data-url="<?php echo site_url('coe/coe_nominalroll/finalize/' . $roll->id); ?>"
                        data-subject="<?php echo htmlspecialchars($roll->subject_name); ?>">
                  <i class="fa fa-lock"></i> Finalize
                </button>
              <?php endif; ?>
            </div>
          </div>
          <!-- Preview: top 3 students from snapshot -->
          <?php
          $students = json_decode($roll->roll_snapshot ?: '[]', true);
          $preview  = array_slice($students, 0, 3);
          ?>
          <?php if (!empty($preview)): ?>
            <div style="margin-top:10px;font-size:.92rem;color:#555;border-top:1px solid #f0f0f0;padding-top:8px;">
              <strong>Preview:</strong>
              <?php foreach ($preview as $i => $st): ?>
                <?php echo htmlspecialchars(($st['register_no'] ?? '') . ' — ' . ($st['firstname'] ?? '') . ' ' . ($st['lastname'] ?? '')); ?>
                <?php if ($i < count($preview)-1): ?>, <?php endif; ?>
              <?php endforeach; ?>
              <?php if (count($students) > 3): ?>
                <span class="text-muted"> and <?php echo count($students) - 3; ?> more...</span>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</div>

<script>
(function(){
  document.querySelectorAll('.confirm-generate').forEach(function(btn){
    btn.addEventListener('click', function(){
      var url = btn.dataset.url, name = btn.dataset.name;
      if (typeof swal !== 'undefined') {
        swal({ title:'Generate Nominal Rolls?', text:'For "'+name+'". Finalized rolls will NOT be changed.', type:'info',
          showCancelButton:true, confirmButtonColor:'#4527a0', confirmButtonText:'Generate'
        }, function(){ window.location.href = url; });
      } else if (confirm('Generate nominal rolls for "'+name+'"?')) { window.location.href = url; }
    });
  });

  document.querySelectorAll('.confirm-finalize').forEach(function(btn){
    btn.addEventListener('click', function(){
      var url = btn.dataset.url, sub = btn.dataset.subject;
      if (typeof swal !== 'undefined') {
        swal({ title:'Finalize Roll?', text:'"'+sub+'" will be locked. This cannot be undone.', type:'warning',
          showCancelButton:true, confirmButtonColor:'#c62828', confirmButtonText:'Finalize'
        }, function(){ window.location.href = url; });
      } else if (confirm('Finalize roll for "'+sub+'"? This cannot be undone.')) { window.location.href = url; }
    });
  });
})();
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'nominal_roll']); ?>
