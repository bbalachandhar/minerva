<style>
.report-nav { margin-bottom: 24px; }
.report-nav-header { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
.report-nav-header .nav-icon { width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #5b73e8, #7c5ce7); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 16px; }
.report-nav-header h3 { margin: 0; font-size: 18px; font-weight: 700; color: #2c3e50; }
</style>
<div class="report-nav">
    <div class="report-nav-header">
        <span class="nav-icon"><i class="fa fa-money"></i></span>
        <h3><?php echo $this->lang->line('fees_collection') . " " . $this->lang->line('report'); ?></h3>
    </div>
</div>
