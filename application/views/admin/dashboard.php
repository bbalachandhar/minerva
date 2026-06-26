<?php if (isset($extra_students) && count($extra_students) > 0) { ?>
    <div class="alert alert-warning" style="margin-top:10px;">
        <strong>Extra students (not male/female):</strong>
        <ul style="max-height:120px;overflow:auto;">
            <?php foreach ($extra_students as $stu) { ?>
                <li><?php echo htmlspecialchars($stu['firstname'].' '.$stu['lastname'].' (ID: '.$stu['id'].', Gender: '.($stu['gender'] ?: 'Not specified').')'); ?></li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>
<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>

<style>
/* ══════════════════════════════════════════════════════════════
   MINERVA DASHBOARD — Modern SaaS Design System
   ══════════════════════════════════════════════════════════════ */

/* ── Design Tokens ── */
.mn-dashboard {
    --mn-bg: #f1f5f9;
    --mn-card: #ffffff;
    --mn-border: #e2e8f0;
    --mn-radius: 12px;
    --mn-shadow: 0 1px 3px 0 rgba(0,0,0,0.04), 0 1px 2px -1px rgba(0,0,0,0.03);
    --mn-shadow-md: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
    --mn-text: #0f172a;
    --mn-text-sec: #64748b;
    --mn-text-muted: #94a3b8;
    --mn-indigo: #4f46e5;
    --mn-blue: #3b82f6;
    --mn-green: #10b981;
    --mn-emerald: #059669;
    --mn-amber: #f59e0b;
    --mn-red: #ef4444;
    --mn-purple: #8b5cf6;
    --mn-orange: #ea580c;
    --mn-cyan: #06b6d4;
    --mn-pink: #ec4899;
    --mn-gap: 16px;
}

/* ── Base ── */
.mn-dashboard .content { padding: 20px 24px !important; }

/* ── Card Component ── */
.mn-card {
    background: var(--mn-card);
    border: 1px solid var(--mn-border);
    border-radius: var(--mn-radius);
    box-shadow: var(--mn-shadow);
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}
.mn-card:hover { box-shadow: var(--mn-shadow-md); }

.mn-card-head {
    padding: 12px 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: var(--mn-text-sec);
    border-bottom: 1px solid var(--mn-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
}
.mn-card-head .mn-head-badge {
    font-size: 16px;
    font-weight: 800;
    color: var(--mn-text);
    text-transform: none;
    letter-spacing: -0.3px;
}
.mn-card-head-accent {
    padding: 14px 20px;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.2px;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: none;
}
.mn-card-body { padding: 16px 20px; }
.mn-card-body-compact { padding: 8px 20px 12px; }

/* ── Section Spacing ── */
.mn-section { margin-bottom: var(--mn-gap); }
.mn-section:last-child { margin-bottom: 0; }
.mn-row { display: flex; flex-wrap: wrap; margin: 0 -8px; }
.mn-row > [class*="mn-col"] { padding: 0 8px; margin-bottom: var(--mn-gap); }
.mn-row-eq > [class*="mn-col"] { display: flex; }
.mn-row-eq > [class*="mn-col"] > .mn-card,
.mn-row-eq > [class*="mn-col"] > .mn-metric { width: 100%; display: flex; flex-direction: column; }
.mn-row-eq > [class*="mn-col"] > .mn-card > .mn-card-body,
.mn-row-eq > [class*="mn-col"] > .mn-card > .mn-card-body-compact,
.mn-row-eq > [class*="mn-col"] > .mn-card > .mn-chart-wrap { flex: 1; display: flex; flex-direction: column; }
.mn-row-eq .mn-chart-wrap .mn-donut-wrap { flex: 1; display: flex; align-items: center; justify-content: center; }
.mn-row-eq .mn-chart-wrap .chart-async { flex: 1; }
.mn-col-20 { width: 20%; }
.mn-col-25 { width: 25%; }
.mn-col-33 { width: 33.333%; }
.mn-col-50 { width: 50%; }
.mn-col-58 { width: 58.333%; }
.mn-col-42 { width: 41.666%; }
.mn-col-75 { width: 75%; }
.mn-col-100 { width: 100%; }
@media (max-width: 991px) {
    .mn-col-20, .mn-col-25, .mn-col-33 { width: 50%; }
    .mn-col-42, .mn-col-50, .mn-col-58 { width: 100%; }
    .mn-col-75 { width: 100%; }
}
@media (max-width: 767px) {
    .mn-col-20, .mn-col-25, .mn-col-33, .mn-col-50 { width: 100%; }
}

/* ── 1. KEY METRICS STRIP ── */
.mn-metric {
    background: var(--mn-card);
    border: 1px solid var(--mn-border);
    border-radius: var(--mn-radius);
    box-shadow: var(--mn-shadow);
    padding: 18px 20px 14px;
    border-left: 4px solid var(--mn-indigo);
    transition: box-shadow 0.2s, transform 0.15s;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    height: 100%;
}
.mn-metric:hover { box-shadow: var(--mn-shadow-md); transform: translateY(-2px); }
.mn-metric-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}
.mn-metric-body { flex: 1; min-width: 0; }
.mn-metric-value {
    font-size: 22px; font-weight: 800; color: var(--mn-text);
    line-height: 1.1; letter-spacing: -0.5px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.mn-metric-label {
    font-size: 11px; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.5px; color: var(--mn-text-sec); margin-top: 4px;
}
.mn-metric-bar {
    height: 3px; background: #f1f5f9; border-radius: 2px;
    margin-top: 10px; overflow: hidden;
}
.mn-metric-bar .progress-bar {
    height: 100%; border-radius: 2px; transition: width 0.4s ease;
    background: currentColor;
}
.mn-metric.is-amber  { border-left-color: var(--mn-amber); }
.mn-metric.is-amber .mn-metric-icon  { background: #fffbeb; color: var(--mn-amber); }
.mn-metric.is-amber .mn-metric-bar .progress-bar { background: var(--mn-amber); }
.mn-metric.is-blue   { border-left-color: var(--mn-blue); }
.mn-metric.is-blue .mn-metric-icon   { background: #eff6ff; color: var(--mn-blue); }
.mn-metric.is-blue .mn-metric-bar .progress-bar { background: var(--mn-blue); }
.mn-metric.is-green  { border-left-color: var(--mn-green); }
.mn-metric.is-green .mn-metric-icon  { background: #ecfdf5; color: var(--mn-green); }
.mn-metric.is-green .mn-metric-bar .progress-bar { background: var(--mn-green); }
.mn-metric.is-red    { border-left-color: var(--mn-red); }
.mn-metric.is-red .mn-metric-icon    { background: #fef2f2; color: var(--mn-red); }
.mn-metric.is-red .mn-metric-bar .progress-bar { background: var(--mn-red); }
.mn-metric.is-purple { border-left-color: var(--mn-purple); }
.mn-metric.is-purple .mn-metric-icon { background: #f5f3ff; color: var(--mn-purple); }
.mn-metric.is-purple .mn-metric-bar .progress-bar { background: var(--mn-purple); }

/* ── 2. ATTENDANCE DATA ROWS ── */
.mn-att-row {
    display: flex; align-items: center; gap: 8px;
    padding: 7px 0; border-bottom: 1px solid #f1f5f9;
    font-size: 12px;
}
.mn-att-row:last-child { border-bottom: none; }
.mn-att-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.mn-att-label { width: 72px; font-weight: 600; color: var(--mn-text); text-transform: uppercase; font-size: 10px; letter-spacing: 0.3px; }
.mn-att-count { width: 36px; text-align: right; font-weight: 700; color: var(--mn-text); font-size: 13px; }
.mn-att-pct { width: 40px; text-align: right; color: var(--mn-text-sec); font-size: 11px; font-weight: 600; }
.mn-att-bar { flex: 1; height: 4px; background: #f1f5f9; border-radius: 2px; overflow: hidden; min-width: 30px; }
.mn-att-bar .progress-bar { height: 100%; border-radius: 2px; transition: width 0.4s; }

/* ── 3. BIRTHDAY CARDS ── */
.mn-bday-body { max-height: 210px; overflow: hidden; }
.birthday-ticker-container { position: relative; height: 100%; }
.birthday-ticker-content { animation: ticker-scroll var(--ticker-duration, 20s) linear infinite; }
.birthday-ticker-clipper { overflow: hidden; max-height: 100%; height: 100%; position: relative; }
.birthday-ticker-content ul { padding: 0; margin: 0; }
.birthday-ticker-content li { list-style: none; }
.mediarow { overflow: hidden; }
@keyframes ticker-scroll {
    0% { transform: translateY(0); }
    100% { transform: translateY(var(--ticker-translate-y, -50%)); }
}
.staffleft-box { position: relative; }
.birthday-date {
    position: absolute; bottom: 0; left: 0; right: 0;
    background-color: rgba(255,255,255,0.8); color: #000;
    text-align: center; padding: 2px; font-size: 12px; font-weight: bold; z-index: 10;
}

/* ── 4. ADMISSION OVERVIEW ── */
.mn-adm-row {
    display: flex; align-items: center; padding: 8px 0;
    border-bottom: 1px solid #f8fafc; font-size: 12px;
}
.mn-adm-row:last-child { border-bottom: none; }
.mn-adm-bar-indicator { width: 3px; height: 28px; border-radius: 2px; margin-right: 12px; flex-shrink: 0; }
.mn-adm-label { flex: 1; font-weight: 600; color: var(--mn-text); text-transform: uppercase; font-size: 11px; letter-spacing: 0.3px; }
.mn-adm-label .btn-xs { margin-left: 6px; opacity: 0.5; transition: opacity 0.15s; font-size: 10px; padding: 1px 5px; border-radius: 4px; }
.mn-adm-label .btn-xs:hover { opacity: 1; }
.mn-adm-count { font-weight: 700; color: var(--mn-text); font-size: 14px; min-width: 30px; text-align: right; }
.mn-adm-pct { font-size: 11px; color: var(--mn-text-sec); font-weight: 600; min-width: 54px; text-align: right; }

/* ── 5. HEAD COUNT ── */
.mn-hc-total { font-size: 36px; font-weight: 800; color: var(--mn-text); letter-spacing: -1px; line-height: 1; }
.mn-hc-grid { display: flex; gap: 24px; margin-top: 16px; }
.mn-hc-segment { flex: 1; }
.mn-hc-segment-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--mn-text-sec); margin-bottom: 4px; }
.mn-hc-segment-val { font-size: 20px; font-weight: 700; color: var(--mn-text); }
.mn-hc-segment-pct { font-size: 11px; color: var(--mn-text-muted); margin-bottom: 6px; }
.mn-hc-bar { height: 4px; background: #f1f5f9; border-radius: 2px; overflow: hidden; }
.mn-hc-bar .progress-bar { height: 100%; border-radius: 2px; transition: width 0.4s; }

/* ── 6. CHART CONTAINERS ── */
.mn-chart-wrap { padding: 20px; }
.mn-chart-title {
    font-size: 13px; font-weight: 600; color: var(--mn-text);
    margin-bottom: 16px; padding-bottom: 12px;
    border-bottom: 1px solid var(--mn-border);
}
.mn-donut-wrap { max-width: 100%; max-height: none; margin: 0 auto; }
.chart-async { position: relative; min-height: 120px; }
.chart-async.is-loading canvas { opacity: 0.35; }
.chart-async-loader {
    position: absolute; inset: 0; display: none;
    align-items: center; justify-content: center;
    background: rgba(255,255,255,0.7); z-index: 2;
}
.chart-async-spinner {
    width: 28px; height: 28px; border-radius: 50%;
    border: 3px solid rgba(79,70,229,0.15); border-top-color: var(--mn-indigo);
    animation: mn-spin 0.8s linear infinite;
}
@keyframes mn-spin { to { transform: rotate(360deg); } }
.chart-async.is-loading .chart-async-loader { display: flex; }

/* ── 7. FINANCIAL CARDS ── */
.mn-fin-row {
    display: flex; align-items: baseline; justify-content: space-between;
    padding: 8px 0; border-bottom: 1px solid #f8fafc; font-size: 12px;
}
.mn-fin-row:last-child { border-bottom: none; }
.mn-fin-label { font-weight: 700; color: var(--mn-text); font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px; }
.mn-fin-val { font-weight: 700; color: var(--mn-text); font-size: 13px; }
.mn-fin-sub { font-size: 11px; color: var(--mn-text-muted); margin-top: 1px; }
.mn-fin-pct { font-size: 11px; font-weight: 600; color: var(--mn-text-sec); }
.mn-fin-bar { height: 3px; background: #f1f5f9; border-radius: 2px; overflow: hidden; margin: 4px 0 2px; }
.mn-fin-bar .progress-bar { height: 100%; border-radius: 2px; transition: width 0.4s; }

/* ── 8. CLASSWISE TABLE ── */
.mn-dashboard #fees-classwise-widget .table-responsive { max-height: 350px; overflow-y: auto; }
.mn-dashboard .classwise-fees-table { border-collapse: collapse !important; font-size: 12px; }
.mn-dashboard .classwise-fees-table td,
.mn-dashboard .classwise-fees-table th { border: 1px solid #e2e8f0 !important; padding: 8px 10px !important; text-align: center; }
.mn-dashboard .classwise-fees-table thead { position: sticky; top: 0; z-index: 10; }
.mn-dashboard .classwise-fees-table thead th { font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px; }
.mn-dashboard .classwise-fees-table thead tr:first-child th { background: #f8fafc !important; color: var(--mn-text); }
.mn-dashboard .classwise-fees-table thead tr:nth-child(2) th { background: #f1f5f9 !important; color: var(--mn-text-sec); font-size: 10px; }
.mn-dashboard .classwise-fees-table tfoot td { font-weight: 700; background: #f8fafc !important; }
.mn-dashboard .classwise-fees-table tbody tr:hover { background: #f8fafc; }
.classwise-fees-loading { padding: 30px !important; }
.classwise-fees-loading .loading-spinner {
    display: inline-block; width: 32px; height: 32px;
    border: 3px solid #e2e8f0; border-top-color: var(--mn-indigo);
    border-radius: 50%; animation: mn-spin 0.8s linear infinite; margin-bottom: 8px;
}
.mn-dashboard .nav-tabs { border-bottom: 2px solid #e2e8f0; padding: 0 20px; }
.mn-dashboard .nav-tabs > li > a {
    border: none !important; border-radius: 0 !important; padding: 10px 16px;
    font-size: 12px; font-weight: 600; color: var(--mn-text-sec);
    border-bottom: 2px solid transparent !important; margin-bottom: -2px;
}
.mn-dashboard .nav-tabs > li.active > a {
    color: var(--mn-indigo) !important; border-bottom-color: var(--mn-indigo) !important;
    background: transparent !important;
}

/* ── 9. QUICK LINKS ── */
.mn-qlink {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 18px; background: var(--mn-card);
    border: 1px solid var(--mn-border); border-radius: var(--mn-radius);
    box-shadow: var(--mn-shadow); transition: box-shadow 0.2s, transform 0.15s;
    text-decoration: none !important; color: inherit !important; height: 100%;
}
.mn-qlink:hover { box-shadow: var(--mn-shadow-md); transform: translateY(-1px); }
.mn-qlink-icon {
    width: 42px; height: 42px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.mn-qlink-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--mn-text-sec); }
.mn-qlink-value { font-size: 16px; font-weight: 700; color: var(--mn-text); margin-top: 1px; }

/* ── 10. STAFF ROLES ── */
.mn-role-card {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px; background: var(--mn-card);
    border: 1px solid var(--mn-border); border-radius: 10px;
    margin-bottom: 8px; box-shadow: var(--mn-shadow);
}
.mn-role-icon {
    width: 36px; height: 36px; border-radius: 8px;
    background: #f1f5f9; display: flex; align-items: center;
    justify-content: center; color: var(--mn-text-sec); font-size: 14px;
}
.mn-role-name { font-size: 12px; font-weight: 600; color: var(--mn-text); }
.mn-role-count { font-size: 18px; font-weight: 800; color: var(--mn-text); margin-left: auto; }

/* ── 11. CALENDAR ── */
.mn-dashboard #calendar { height: auto; }
.mn-dashboard #calendar .fc-view-container { height: auto; }
.mn-dashboard .fc-toolbar { margin-bottom: 16px !important; }
.mn-dashboard .fc-toolbar h2 { font-size: 16px !important; font-weight: 700 !important; color: var(--mn-text) !important; letter-spacing: -0.3px; }
.mn-dashboard .fc-button { background: var(--mn-card) !important; border: 1px solid var(--mn-border) !important; color: var(--mn-text-sec) !important; font-size: 12px !important; font-weight: 600 !important; padding: 6px 12px !important; border-radius: 8px !important; text-transform: capitalize !important; box-shadow: none !important; }
.mn-dashboard .fc-button:hover { background: #f8fafc !important; color: var(--mn-text) !important; }
.mn-dashboard .fc-button.fc-state-active, .mn-dashboard .fc-state-active { background: var(--mn-indigo) !important; color: #fff !important; border-color: var(--mn-indigo) !important; }
.mn-dashboard .fc-prev-button, .mn-dashboard .fc-next-button { padding: 6px 10px !important; }
.mn-dashboard .fc th { font-size: 11px !important; font-weight: 600 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; color: var(--mn-text-sec) !important; padding: 10px 0 !important; border: none !important; background: transparent !important; }
.mn-dashboard .fc td { border-color: #f1f5f9 !important; }
.mn-dashboard .fc-day-number { font-size: 12px !important; font-weight: 500 !important; color: var(--mn-text-sec) !important; padding: 6px 8px !important; }
.mn-dashboard .fc-today { background: #f0f9ff !important; }
.mn-dashboard .fc-today .fc-day-number { color: var(--mn-indigo) !important; font-weight: 700 !important; }
.mn-dashboard .fc-event { border-radius: 6px !important; padding: 2px 6px !important; font-size: 11px !important; font-weight: 500 !important; border: none !important; margin: 1px 2px !important; }
.mn-dashboard .fc-unthemed td.fc-today { background: #eff6ff !important; }

/* ── Skeleton shimmer ── */
.fo-skeleton { position: relative; color: transparent !important; background: #e2e8f0; border-radius: 4px; display: inline-block; min-width: 28px; }
.fo-skeleton.fo-line { min-width: 80px; height: 12px; vertical-align: middle; }
.fo-skeleton::after {
    content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
    animation: mn-shimmer 1.4s infinite;
}
@keyframes mn-shimmer { 100% { transform: translateX(200%); } }

/* ── AdminLTE overrides ── */
.mn-dashboard .borderwhite { border-top-color: #fff !important; }
.sidebar-collapse #barChart { height: 100% !important; }
.sidebar-collapse #lineChart { height: 100% !important; }

/* ── Card header accent colors ── */
.mn-accent-indigo  { background: linear-gradient(135deg, #6366f1, #818cf8) !important; }
.mn-accent-emerald { background: linear-gradient(135deg, #059669, #34d399) !important; }
.mn-accent-blue    { background: linear-gradient(135deg, #2563eb, #60a5fa) !important; }
.mn-accent-orange  { background: linear-gradient(135deg, #ea580c, #fb923c) !important; }
.mn-accent-purple  { background: linear-gradient(135deg, #7c3aed, #a78bfa) !important; }
.mn-accent-cyan    { background: linear-gradient(135deg, #0891b2, #22d3ee) !important; }
.mn-accent-rose    { background: linear-gradient(135deg, #e11d48, #fb7185) !important; }
.mn-accent-sky     { background: linear-gradient(135deg, #0284c7, #38bdf8) !important; }
.mn-accent-violet  { background: linear-gradient(135deg, #7c3aed, #a78bfa) !important; }
.mn-accent-teal    { background: linear-gradient(135deg, #0d9488, #5eead4) !important; }
</style>

<!-- ══════════════════════════════════════════════════════════════
     DASHBOARD HTML
     ══════════════════════════════════════════════════════════════ -->
<div class="content-wrapper mn-dashboard">
    <section class="content">

        <!-- Alerts -->
        <div>
            <?php if (ENVIRONMENT != 'production') { ?>
                <div class="alert alert-danger">
                    Environment set to <?php echo ENVIRONMENT; ?>! <br>
                    Don't forget to set back to production in the main index.php file after finishing your tests or <?php echo ENVIRONMENT; ?>. <br>
                    Please be aware that in <?php echo ENVIRONMENT; ?> mode you may see some errors and deprecation warnings, for this reason, it's always recommended to set the environment to "production" if you are not actually developing some features/modules or trying to test some code.
                </div>
            <?php } ?>
            <?php if ($mysqlVersion && $sqlMode && strpos($sqlMode->mode, 'ONLY_FULL_GROUP_BY') !== false) { ?>
                <div class="alert alert-danger">
                    Minerva may not work properly because ONLY_FULL_GROUP_BY is enabled, consult with your hosting provider to disable ONLY_FULL_GROUP_BY in sql_mode configuration.
                </div>
            <?php } ?>
            <?php
            $show    = false;
            $role    = $this->customlib->getStaffRole();
            $role_id = json_decode($role)->id;
            foreach ($notifications as $notice_key => $notice_value) {
                if ($role_id == 7) {
                    $show = true;
                } elseif (date($this->customlib->getSchoolDateFormat()) >= date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($notice_value->publish_date))) {
                    $show = true;
                }
                if ($show) { ?>
                    <div class="dashalert alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="alertclose close close_notice" data-dismiss="alert" aria-label="Close" data-noticeid="<?php echo $notice_value->id; ?>"><span aria-hidden="true">&times;</span></button>
                        <a href="<?php echo site_url('admin/notification') ?>"><?php echo $notice_value->title; ?></a>
                    </div>
            <?php }
            } ?>
        </div>

        <!-- ═══ ROW 1 — KEY METRICS ═══ -->
        <div class="mn-section">
            <div class="mn-row" id="dashboard-widgets-row5">

                <?php if ($this->module_lib->hasActive('fees_collection') && $this->rbac->hasPrivilege('fees_awaiting_payment_widegts', 'can_view')) { ?>
                <div class="mn-col-20">
                    <div class="mn-metric is-amber">
                        <div class="mn-metric-icon"><i class="fa fa-money"></i></div>
                        <div class="mn-metric-body">
                            <div class="mn-metric-value fees-awaiting-amount fo-skeleton">0</div>
                            <div class="mn-metric-label"><?php echo $this->lang->line('fees_awaiting_payment'); ?></div>
                            <div class="mn-metric-bar"><div class="progress-bar fees-awaiting-progress-bar" style="width: <?php echo round(isset($fees_awaiting_progress) ? $fees_awaiting_progress : 0, 2); ?>%"></div></div>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php if ($this->rbac->hasPrivilege('staff_approved_leave_widegts', 'can_view')) { ?>
                <div class="mn-col-20">
                    <div class="mn-metric is-blue" id="staff-approved-leave-widget" data-url="<?php echo site_url('admin/admin/staff_approved_leave_widget'); ?>">
                        <div class="mn-metric-icon"><i class="fa fa-calendar-check-o"></i></div>
                        <div class="mn-metric-body">
                            <div class="mn-metric-value"><span class="sal-approved fo-skeleton">0</span><span style="color:var(--mn-text-muted);font-size:14px;font-weight:400;">/<span class="sal-total fo-skeleton">0</span></span></div>
                            <div class="mn-metric-label"><?php echo $this->lang->line('staff_approved_leave'); ?></div>
                            <div class="mn-metric-bar"><div class="progress-bar sal-progress" style="width: 0%"></div></div>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php if ($this->rbac->hasPrivilege('student_approved_leave_widegts', 'can_view')) { ?>
                <div class="mn-col-20">
                    <div class="mn-metric is-green" id="student-approved-leave-widget" data-url="<?php echo site_url('admin/admin/student_approved_leave_widget'); ?>">
                        <div class="mn-metric-icon"><i class="fa fa-calendar-check-o"></i></div>
                        <div class="mn-metric-body">
                            <div class="mn-metric-value"><span class="stl-approved fo-skeleton">0</span><span style="color:var(--mn-text-muted);font-size:14px;font-weight:400;">/<span class="stl-total fo-skeleton">0</span></span></div>
                            <div class="mn-metric-label"><?php echo $this->lang->line('student_approved_leave'); ?></div>
                            <div class="mn-metric-bar"><div class="progress-bar stl-progress" style="width: 0%"></div></div>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php if ($this->rbac->hasPrivilege('complaint', 'can_view')): ?>
                <div class="mn-col-20">
                    <div class="mn-metric is-red" id="complaint-widget" data-url="<?php echo site_url('admin/complaint/widget'); ?>">
                        <div class="mn-metric-icon"><i class="fa fa-commenting-o"></i></div>
                        <div class="mn-metric-body">
                            <div class="mn-metric-value"><span class="cw-open fo-skeleton">0</span><span style="color:var(--mn-text-muted);font-size:14px;font-weight:400;">/<span class="cw-total fo-skeleton">0</span></span></div>
                            <div class="mn-metric-label"><?php echo $this->lang->line('complaint_box'); ?></div>
                            <div class="mn-metric-bar"><div class="progress-bar cw-progress" style="width: 0%"></div></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($this->module_lib->hasActive('front_office') && $this->rbac->hasPrivilege('conveted_leads_widegts', 'can_view')) { ?>
                <div class="mn-col-20">
                    <div class="mn-metric is-purple" id="converted-leads-widget" data-url="<?php echo site_url('admin/admin/converted_leads_widget'); ?>">
                        <div class="mn-metric-icon"><i class="fa fa-exchange"></i></div>
                        <div class="mn-metric-body">
                            <div class="mn-metric-value"><span class="cl-complete fo-skeleton">0</span><span style="color:var(--mn-text-muted);font-size:14px;font-weight:400;">/<span class="cl-total fo-skeleton">0</span></span></div>
                            <div class="mn-metric-label">Converted Leads</div>
                            <div class="mn-metric-bar"><div class="progress-bar cl-progress" style="width: 0%"></div></div>
                        </div>
                    </div>
                </div>
                <?php } ?>

            </div>
        </div>

        <!-- ═══ ROW 2 — BIRTHDAYS + ATTENDANCE ═══ -->
        <div class="mn-section">
            <div class="mn-row mn-row-eq">

                <div class="mn-col-25">
                    <div class="mn-card" id="student-birthday-widget" data-url="<?php echo site_url('admin/admin/student_birthdays_widget'); ?>">
                        <div class="mn-card-head-accent mn-accent-indigo">
                            <span>Students Birthday</span>
                            <span class="mn-head-badge student-birthday-count" style="color:#fff;">0</span>
                        </div>
                        <div class="mn-card-body mn-bday-body birthday-widget-body">
                            <div class="fo-skeleton fo-line" style="width:80%;margin:8px auto;"></div>
                            <div class="fo-skeleton fo-line" style="width:65%;margin:8px auto;"></div>
                            <div class="fo-skeleton fo-line" style="width:75%;margin:8px auto;"></div>
                        </div>
                    </div>
                </div>

                <div class="mn-col-25">
                    <div class="mn-card" id="staff-birthday-widget" data-url="<?php echo site_url('admin/admin/staff_birthdays_widget'); ?>">
                        <div class="mn-card-head-accent mn-accent-emerald">
                            <span>Staff Birthdays</span>
                            <span class="mn-head-badge staff-birthday-count" style="color:#fff;">0</span>
                        </div>
                        <div class="mn-card-body mn-bday-body birthday-widget-body">
                            <div class="fo-skeleton fo-line" style="width:80%;margin:8px auto;"></div>
                            <div class="fo-skeleton fo-line" style="width:65%;margin:8px auto;"></div>
                            <div class="fo-skeleton fo-line" style="width:75%;margin:8px auto;"></div>
                        </div>
                    </div>
                </div>

                <?php if ($this->module_lib->hasActive('student_attendance') && $this->rbac->hasPrivilege('today_attendance_widegts', 'can_view')) { ?>
                <div class="mn-col-25">
                    <div class="mn-card" id="student-attendance-widget" data-url="<?php echo site_url('admin/admin/student_today_attendance_widget'); ?>">
                        <div class="mn-card-head-accent mn-accent-blue"><span><?php echo $this->lang->line('student_today_attendance'); ?></span></div>
                        <div class="mn-card-body-compact">
                            <div class="mn-att-row"><span class="mn-att-dot" style="background:#10b981;"></span><span class="mn-att-label"><?php echo $this->lang->line('present'); ?></span><span class="mn-att-count sta-present-count fo-skeleton">0</span><span class="mn-att-pct"><span class="sta-present-percent fo-skeleton">0%</span></span><div class="mn-att-bar"><div class="progress-bar sta-present-bar" style="width:0%;background:#10b981;"></div></div></div>
                            <div class="mn-att-row"><span class="mn-att-dot" style="background:#f59e0b;"></span><span class="mn-att-label"><?php echo $this->lang->line('late'); ?></span><span class="mn-att-count sta-late-count fo-skeleton">0</span><span class="mn-att-pct"><span class="sta-late-percent fo-skeleton">0%</span></span><div class="mn-att-bar"><div class="progress-bar sta-late-bar" style="width:0%;background:#f59e0b;"></div></div></div>
                            <div class="mn-att-row"><span class="mn-att-dot" style="background:#ef4444;"></span><span class="mn-att-label"><?php echo $this->lang->line('absent'); ?></span><span class="mn-att-count sta-absent-count fo-skeleton">0</span><span class="mn-att-pct"><span class="sta-absent-percent fo-skeleton">0%</span></span><div class="mn-att-bar"><div class="progress-bar sta-absent-bar" style="width:0%;background:#ef4444;"></div></div></div>
                            <div class="mn-att-row"><span class="mn-att-dot" style="background:#8b5cf6;"></span><span class="mn-att-label"><?php echo $this->lang->line('half_day'); ?></span><span class="mn-att-count sta-halfday-count fo-skeleton">0</span><span class="mn-att-pct"><span class="sta-halfday-percent fo-skeleton">0%</span></span><div class="mn-att-bar"><div class="progress-bar sta-halfday-bar" style="width:0%;background:#8b5cf6;"></div></div></div>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php if ($this->rbac->hasPrivilege('staff_present_today_widegts', 'can_view')) { ?>
                <div class="mn-col-25">
                    <div class="mn-card" id="staff-attendance-widget" data-url="<?php echo site_url('admin/admin/staff_today_attendance_widget'); ?>">
                        <div class="mn-card-head-accent mn-accent-orange"><span>Staff Today Attendance</span></div>
                        <div class="mn-card-body-compact">
                            <div class="mn-att-row"><span class="mn-att-dot" style="background:#10b981;"></span><span class="mn-att-label">Present</span><span class="mn-att-count sfa-present-count fo-skeleton">0</span><span class="mn-att-pct"><span class="sfa-present-percent fo-skeleton">0</span>%</span><div class="mn-att-bar"><div class="progress-bar sfa-present-bar" style="width:0%;background:#10b981;"></div></div></div>
                            <div class="mn-att-row"><span class="mn-att-dot" style="background:#f59e0b;"></span><span class="mn-att-label">Late</span><span class="mn-att-count sfa-late-count fo-skeleton">0</span><span class="mn-att-pct"><span class="sfa-late-percent fo-skeleton">0</span>%</span><div class="mn-att-bar"><div class="progress-bar sfa-late-bar" style="width:0%;background:#f59e0b;"></div></div></div>
                            <div class="mn-att-row"><span class="mn-att-dot" style="background:#ef4444;"></span><span class="mn-att-label">Absent</span><span class="mn-att-count sfa-absent-count fo-skeleton">0</span><span class="mn-att-pct"><span class="sfa-absent-percent fo-skeleton">0</span>%</span><div class="mn-att-bar"><div class="progress-bar sfa-absent-bar" style="width:0%;background:#ef4444;"></div></div></div>
                            <div class="mn-att-row"><span class="mn-att-dot" style="background:#8b5cf6;"></span><span class="mn-att-label">Half Day</span><span class="mn-att-count sfa-halfday-count fo-skeleton">0</span><span class="mn-att-pct"><span class="sfa-halfday-percent fo-skeleton">0</span>%</span><div class="mn-att-bar"><div class="progress-bar sfa-halfday-bar" style="width:0%;background:#8b5cf6;"></div></div></div>
                            <div class="mn-att-row"><span class="mn-att-dot" style="background:#06b6d4;"></span><span class="mn-att-label">Permission</span><span class="mn-att-count sfa-permission-count fo-skeleton">0</span><span class="mn-att-pct"><span class="sfa-permission-percent fo-skeleton">0</span>%</span><div class="mn-att-bar"><div class="progress-bar sfa-permission-bar" style="width:0%;background:#06b6d4;"></div></div></div>
                        </div>
                    </div>
                </div>
                <?php } ?>

            </div>
        </div>

        <!-- ═══ ROW 3 — ADMISSION + HEAD COUNT ═══ -->
        <div class="mn-section">
            <div class="mn-row">

                <?php if ($this->module_lib->hasActive('front_office') && $this->rbac->hasPrivilege('enquiry_overview_widegts', 'can_view')) { ?>
                <div class="mn-col-50">
                    <div class="mn-card" id="enquiry-overview-widget" data-url="<?php echo site_url('admin/admin/enquiry_overview_widget'); ?>">
                        <div class="mn-card-head-accent mn-accent-purple">Admission Overview</div>
                        <div class="mn-card-body">
                            <div class="mn-adm-row"><div class="mn-adm-bar-indicator" style="background:var(--mn-amber);"></div><div class="mn-adm-label">Application Received <a href="<?php echo site_url('admin/onlinestudent?preset_filter=application_received'); ?>" class="btn btn-xs btn-default" title="View"><i class="fa fa-eye"></i></a></div><div class="mn-adm-count eo-won-count fo-skeleton">0</div><div class="mn-adm-pct"><span class="eo-won-percent fo-skeleton">0</span>%</div></div>
                            <div class="mn-adm-row"><div class="mn-adm-bar-indicator" style="background:var(--mn-green);"></div><div class="mn-adm-label">Fully Paid <a href="<?php echo site_url('admin/onlinestudent?preset_filter=fully_paid'); ?>" class="btn btn-xs btn-default" title="View"><i class="fa fa-eye"></i></a></div><div class="mn-adm-count eo-active-count fo-skeleton">0</div><div class="mn-adm-pct"><span class="eo-active-percent fo-skeleton">0</span>%</div></div>
                            <div class="progress" style="height:3px;margin:0 0 2px;background:#f1f5f9;box-shadow:none;border-radius:2px;"><div class="progress-bar eo-active-bar" style="width:0%;background:var(--mn-green);border-radius:2px;"></div></div>
                            <div class="mn-adm-row"><div class="mn-adm-bar-indicator" style="background:var(--mn-blue);"></div><div class="mn-adm-label">Partially Paid <a href="<?php echo site_url('admin/onlinestudent?preset_filter=partially_paid'); ?>" class="btn btn-xs btn-default" title="View"><i class="fa fa-eye"></i></a></div><div class="mn-adm-count eo-app-count fo-skeleton">0</div><div class="mn-adm-pct"><span class="eo-app-total-percent fo-skeleton">0</span></div></div>
                            <div class="progress" style="height:3px;margin:0 0 2px;background:#f1f5f9;box-shadow:none;border-radius:2px;"><div class="progress-bar eo-app-total-bar" style="width:0%;background:var(--mn-blue);border-radius:2px;"></div></div>
                            <div class="mn-adm-row"><div class="mn-adm-bar-indicator" style="background:var(--mn-cyan);"></div><div class="mn-adm-label">Only App Fee Paid <a href="<?php echo site_url('admin/onlinestudent?preset_filter=only_app_fee_paid'); ?>" class="btn btn-xs btn-default" title="View"><i class="fa fa-eye"></i></a></div><div class="mn-adm-count eo-applied-count fo-skeleton">0</div><div class="mn-adm-pct"><span class="eo-applied-percent fo-skeleton">0</span></div></div>
                            <div class="progress" style="height:3px;margin:0 0 2px;background:#f1f5f9;box-shadow:none;border-radius:2px;"><div class="progress-bar eo-applied-bar" style="width:0%;background:var(--mn-cyan);border-radius:2px;"></div></div>
                            <div class="mn-adm-row"><div class="mn-adm-bar-indicator" style="background:var(--mn-red);"></div><div class="mn-adm-label">Revoked <a href="<?php echo site_url('admin/admission_cancellation'); ?>" class="btn btn-xs btn-default" title="View"><i class="fa fa-list"></i></a></div><div class="mn-adm-count eo-revoked-count fo-skeleton">0</div><div class="mn-adm-pct"></div></div>
                            <div class="mn-adm-row"><div class="mn-adm-bar-indicator" style="background:var(--mn-amber);"></div><div class="mn-adm-label"><span style="color:var(--mn-amber);font-weight:700;">Waiting List</span> <a href="<?php echo site_url('admin/onlinestudent?admission_status_filter=waiting_list'); ?>" class="btn btn-xs btn-warning" title="View"><i class="fa fa-list"></i></a></div><div class="mn-adm-count eo-waiting-list-count fo-skeleton">0</div><div class="mn-adm-pct"></div></div>
                            <div class="progress" style="height:3px;margin:0 0 2px;background:#f1f5f9;box-shadow:none;border-radius:2px;"><div class="progress-bar eo-won-bar" style="width:0%;background:var(--mn-amber);border-radius:2px;"></div></div>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php if ($this->rbac->hasPrivilege('student_head_count_widget', 'can_view')) { ?>
                <div class="mn-col-50">
                    <div class="mn-card" id="student-headcount-widget" data-url="<?php echo site_url('admin/admin/student_head_count_widget'); ?>">
                        <div class="mn-card-head-accent mn-accent-cyan"><?php echo $this->lang->line('student_head_count'); ?></div>
                        <div class="mn-card-body" style="padding:24px;">
                            <div class="mn-hc-total shc-total fo-skeleton">0</div>
                            <div style="font-size:11px;color:var(--mn-text-sec);text-transform:uppercase;letter-spacing:0.5px;font-weight:600;margin-top:4px;">Total Students</div>
                            <div class="mn-hc-grid">
                                <div class="mn-hc-segment">
                                    <div class="mn-hc-segment-label"><i class="fa fa-male" style="color:var(--mn-blue);"></i> Male</div>
                                    <div class="mn-hc-segment-val shc-male-count fo-skeleton">0</div>
                                    <div class="mn-hc-segment-pct"><span class="shc-male-percent fo-skeleton">0</span>%</div>
                                    <div class="mn-hc-bar"><div class="progress-bar shc-male-bar" style="width:0%;background:var(--mn-blue);"></div></div>
                                </div>
                                <div class="mn-hc-segment">
                                    <div class="mn-hc-segment-label"><i class="fa fa-female" style="color:var(--mn-pink);"></i> Female</div>
                                    <div class="mn-hc-segment-val shc-female-count fo-skeleton">0</div>
                                    <div class="mn-hc-segment-pct"><span class="shc-female-percent fo-skeleton">0</span>%</div>
                                    <div class="mn-hc-bar"><div class="progress-bar shc-female-bar" style="width:0%;background:var(--mn-pink);"></div></div>
                                </div>
                            </div>
                            <div class="shc-others" style="display:none;margin-top:12px;padding-top:12px;border-top:1px solid var(--mn-border);">
                                <div class="mn-hc-segment">
                                    <div class="mn-hc-segment-label"><i class="fa fa-genderless" style="color:var(--mn-amber);"></i> Others</div>
                                    <div class="mn-hc-segment-val shc-other-count">0</div>
                                    <div class="mn-hc-segment-pct"><span class="shc-other-percent">0</span>%</div>
                                    <div class="mn-hc-bar"><div class="progress-bar shc-other-bar" style="width:0%;background:var(--mn-amber);"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>

            </div>
        </div>

        <!-- ═══ ROW 4 — MONTHLY CHARTS ═══ -->
        <?php $bar_chart = true; $line_chart = true; ?>
        <div class="mn-section">
            <div class="mn-row mn-row-eq">
                <?php if (($this->module_lib->hasActive('fees_collection')) || ($this->module_lib->hasActive('expense'))) {
                    if ($this->rbac->hasPrivilege('fees_collection_and_expense_monthly_chart', 'can_view')) {
                        $userdata = $this->customlib->getUserData(); ?>
                <div class="mn-col-58">
                    <div class="mn-card">
                        <div class="mn-chart-wrap">
                            <div class="mn-chart-title"><?php echo $this->lang->line('fees_collection_expenses_for'); ?> <?php echo $this->lang->line(strtolower(date('F'))) . " " . date('Y'); ?></div>
                            <div class="chart-async" id="fees-collection-expenses-monthly">
                                <div class="chart-async-loader"><span class="chart-async-spinner"></span></div>
                                <div id="barChart" data-url="<?php echo site_url('admin/admin/fees_collection_expenses_monthly_widget'); ?>"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } } ?>

                <?php if ($this->module_lib->hasActive('income') && $this->rbac->hasPrivilege('income_donut_graph', 'can_view')) { ?>
                <div class="mn-col-42">
                    <div class="mn-card">
                        <div class="mn-chart-wrap">
                            <div class="mn-chart-title"><?php echo $this->lang->line('income') . " - " . $this->lang->line(strtolower(date('F'))) . " " . date('Y'); ?></div>
                            <div class="mn-donut-wrap"><div id="doughnut-chart"></div></div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- ═══ ROW 5 — SESSION CHARTS ═══ -->
        <div class="mn-section">
            <div class="mn-row mn-row-eq">
                <?php if (($this->module_lib->hasActive('fees_collection')) || ($this->module_lib->hasActive('expense'))) {
                    if ($this->rbac->hasPrivilege('fees_collection_and_expense_yearly_chart', 'can_view')) { ?>
                <div class="mn-col-58">
                    <div class="mn-card">
                        <div class="mn-chart-wrap">
                            <div class="mn-chart-title"><?php echo $this->lang->line('fees_collection_expenses_for_session'); ?> <?php echo $this->setting_model->getCurrentSessionName(); ?></div>
                            <div class="chart-async" id="fees-collection-expenses-session">
                                <div class="chart-async-loader"><span class="chart-async-spinner"></span></div>
                                <div id="lineChart" data-url="<?php echo site_url('admin/admin/fees_collection_expenses_session_widget'); ?>"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } } ?>

                <?php if ($this->module_lib->hasActive('expense') && $this->rbac->hasPrivilege('expense_donut_graph', 'can_view')) { ?>
                <div class="mn-col-42">
                    <div class="mn-card">
                        <div class="mn-chart-wrap">
                            <div class="mn-chart-title"><?php echo $this->lang->line('expense') . " - " . $this->lang->line(strtolower(date('F'))) . " " . date('Y'); ?></div>
                            <div class="mn-donut-wrap"><div id="doughnut-chart1"></div></div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- ═══ ROW 6 — FINANCIAL WIDGETS ═══ -->
        <div class="mn-section">
            <div class="mn-row">
                <?php if ($this->module_lib->hasActive('fees_collection') && $this->rbac->hasPrivilege('fees_overview_widegts', 'can_view')) { ?>

                <div class="mn-col-25">
                    <div class="mn-card" id="fees-overview-widget-payment" data-url="<?php echo site_url('admin/admin/fees_overview_widget'); ?>">
                        <div class="mn-card-head-accent mn-accent-blue">Payment Status</div>
                        <div class="mn-card-body-compact">
                            <div class="mn-fin-row"><div><div class="mn-fin-label"><?php echo $this->lang->line('unpaid'); ?></div><div class="mn-fin-sub">Paid: <span class="fo-unpaid-collected-sum fo-skeleton">0</span> | Bal: <span class="fo-unpaid-sum fo-skeleton">0</span></div></div><div style="text-align:right;"><div class="mn-fin-val fo-total-unpaid fo-skeleton">0</div><div class="mn-fin-pct"><span class="fo-unpaid-progress fo-skeleton">0</span>%</div></div></div>
                            <div class="mn-fin-bar"><div class="progress-bar fo-unpaid-bar" style="width:0%;background:var(--mn-red);"></div></div>
                            <div class="mn-fin-row"><div><div class="mn-fin-label"><?php echo $this->lang->line('partial'); ?></div><div class="mn-fin-sub">Paid: <span class="fo-partial-collected-sum fo-skeleton">0</span> | Bal: <span class="fo-partial-sum fo-skeleton">0</span></div></div><div style="text-align:right;"><div class="mn-fin-val fo-total-partial fo-skeleton">0</div><div class="mn-fin-pct"><span class="fo-partial-progress fo-skeleton">0</span>%</div></div></div>
                            <div class="mn-fin-bar"><div class="progress-bar fo-partial-bar" style="width:0%;background:var(--mn-amber);"></div></div>
                            <div class="mn-fin-row"><div><div class="mn-fin-label"><?php echo $this->lang->line('paid'); ?></div><div class="mn-fin-sub">Paid: <span class="fo-paid-sum fo-skeleton">0</span> | Bal: <span class="fo-paid-balance-sum fo-skeleton">0</span></div></div><div style="text-align:right;"><div class="mn-fin-val fo-total-paid fo-skeleton">0</div><div class="mn-fin-pct"><span class="fo-paid-progress fo-skeleton">0</span>%</div></div></div>
                            <div class="mn-fin-bar"><div class="progress-bar fo-paid-bar" style="width:0%;background:var(--mn-green);"></div></div>
                        </div>
                    </div>
                </div>

                <div class="mn-col-25">
                    <div class="mn-card" id="fees-overview-widget-collection" data-url="<?php echo site_url('admin/admin/fees_overview_widget'); ?>">
                        <div class="mn-card-head-accent mn-accent-teal">Collection Overview</div>
                        <div class="mn-card-body-compact">
                            <div class="mn-fin-row"><div><div class="mn-fin-label">Total Demand</div><div class="mn-fin-sub">Sum: <span class="fo-demand-sum fo-skeleton">0</span></div></div><div style="text-align:right;"><div class="mn-fin-val fo-total-demand-count fo-skeleton">0</div><div class="mn-fin-pct"><span class="fo-demand-progress fo-skeleton">0</span>%</div></div></div>
                            <div class="mn-fin-bar"><div class="progress-bar fo-demand-bar" style="width:0%;background:var(--mn-blue);"></div></div>
                            <div class="mn-fin-row"><div><div class="mn-fin-label">Total Collection</div><div class="mn-fin-sub">Sum: <span class="fo-collection-sum fo-skeleton">0</span></div></div><div style="text-align:right;"><div class="mn-fin-val fo-total-collection-count fo-skeleton">0</div><div class="mn-fin-pct"><span class="fo-collection-progress fo-skeleton">0</span>%</div></div></div>
                            <div class="mn-fin-bar"><div class="progress-bar fo-collection-bar" style="width:0%;background:var(--mn-green);"></div></div>
                            <div class="mn-fin-row"><div><div class="mn-fin-label">Total Awaiting</div><div class="mn-fin-sub">Sum: <span class="fo-awaiting-sum fo-skeleton">0</span></div></div><div style="text-align:right;"><div class="mn-fin-val fo-total-awaiting-count fo-skeleton">0</div><div class="mn-fin-pct"><span class="fo-awaiting-progress fo-skeleton">0</span>%</div></div></div>
                            <div class="mn-fin-bar"><div class="progress-bar fo-awaiting-bar" style="width:0%;background:var(--mn-amber);"></div></div>
                        </div>
                    </div>
                </div>

                <div class="mn-col-25">
                    <div class="mn-card" id="fees-overview-widget-pending" data-url="<?php echo site_url('admin/admin/fees_overview_widget'); ?>">
                        <div class="mn-card-head-accent mn-accent-rose">Last Year Pending</div>
                        <div class="mn-card-body-compact">
                            <div class="mn-fin-row"><div><div class="mn-fin-label">Pending Demand</div><div class="mn-fin-sub">Sum: <span class="fo-cfdemand-sum fo-skeleton">0</span></div></div><div style="text-align:right;"><div class="mn-fin-val fo-total-cfdemand-count fo-skeleton">0</div><div class="mn-fin-pct"><span class="fo-cfdemand-progress fo-skeleton">0</span>%</div></div></div>
                            <div class="mn-fin-bar"><div class="progress-bar fo-cfdemand-bar" style="width:0%;background:var(--mn-red);"></div></div>
                            <div class="mn-fin-row"><div><div class="mn-fin-label">Pending Collection</div><div class="mn-fin-sub">Sum: <span class="fo-cfcollection-sum fo-skeleton">0</span></div></div><div style="text-align:right;"><div class="mn-fin-val fo-total-cfcollection-count fo-skeleton">0</div><div class="mn-fin-pct"><span class="fo-cfcollection-progress fo-skeleton">0</span>%</div></div></div>
                            <div class="mn-fin-bar"><div class="progress-bar fo-cfcollection-bar" style="width:0%;background:var(--mn-purple);"></div></div>
                            <div class="mn-fin-row"><div><div class="mn-fin-label">Pending Balance</div><div class="mn-fin-sub">Sum: <span class="fo-cfbalance-sum fo-skeleton">0</span></div></div><div style="text-align:right;"><div class="mn-fin-val fo-total-cfbalance-count fo-skeleton">0</div><div class="mn-fin-pct"><span class="fo-cfbalance-progress fo-skeleton">0</span>%</div></div></div>
                            <div class="mn-fin-bar"><div class="progress-bar fo-cfbalance-bar" style="width:0%;background:var(--mn-orange);"></div></div>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php if ($this->module_lib->hasActive('library') && $this->rbac->hasPrivilege('book_overview_widegts', 'can_view')) { ?>
                <div class="mn-col-25">
                    <div class="mn-card" id="library-overview-widget" data-url="<?php echo site_url('admin/admin/library_overview_widget'); ?>">
                        <div class="mn-card-head-accent mn-accent-violet"><?php echo $this->lang->line('library_overview'); ?></div>
                        <div class="mn-card-body-compact">
                            <div class="mn-fin-row"><div class="mn-fin-label"><?php echo $this->lang->line('due_for_return'); ?></div><div class="mn-fin-val lib-dueforreturn fo-skeleton">0</div></div>
                            <div class="mn-fin-bar"><div class="progress-bar lib-dueforreturn-bar" style="width:0%;background:var(--mn-amber);"></div></div>
                            <div class="mn-fin-row"><div class="mn-fin-label"><?php echo $this->lang->line('returned'); ?></div><div class="mn-fin-val lib-forreturn fo-skeleton">0</div></div>
                            <div class="mn-fin-bar"><div class="progress-bar lib-forreturn-bar" style="width:0%;background:var(--mn-green);"></div></div>
                            <div class="mn-fin-row"><div class="mn-fin-label"><?php echo $this->lang->line('issued_out_of'); ?> <span class="lib-total fo-skeleton">0</span></div><div style="text-align:right;"><div class="mn-fin-val lib-total-issued fo-skeleton">0</div><div class="mn-fin-pct"><span class="lib-issued-progress fo-skeleton">0</span>%</div></div></div>
                            <div class="mn-fin-bar"><div class="progress-bar lib-issued-bar" style="width:0%;background:var(--mn-blue);"></div></div>
                            <div class="mn-fin-row"><div class="mn-fin-label"><?php echo $this->lang->line('available_out_of'); ?> <span class="lib-total fo-skeleton">0</span></div><div style="text-align:right;"><div class="mn-fin-val lib-availble fo-skeleton">0</div><div class="mn-fin-pct"><span class="lib-availble-progress fo-skeleton">0</span>%</div></div></div>
                            <div class="mn-fin-bar"><div class="progress-bar lib-availble-bar" style="width:0%;background:var(--mn-indigo);"></div></div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- ═══ ROW 7 — CLASS WISE FEE SUMMARY ═══ -->
        <?php if ($this->rbac->hasPrivilege('fees_classwise_summary_widget', 'can_view')) {
            $class_label = ($this->sch_setting_detail->institution_type === 'college') ? 'Department' : 'Class'; ?>
        <div class="mn-section">
            <div class="mn-card" id="fees-classwise-widget" data-url="<?php echo site_url('admin/admin/fees_classwise_summary_widget'); ?>" data-class-label="<?php echo htmlspecialchars($class_label, ENT_QUOTES); ?>" style="position:relative;z-index:1;">
                <div class="mn-card-head">Class Wise Fee Summary</div>
                <ul class="nav nav-tabs" role="tablist">
                    <li class="active"><a href="#classwise-all" role="tab" data-toggle="tab">All Classes</a></li>
                    <li><a href="#classwise-exclude-final" role="tab" data-toggle="tab">Without Final Year</a></li>
                </ul>
                <div class="tab-content" style="padding:0 20px 16px;">
                    <div class="tab-pane active" id="classwise-all">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered classwise-fees-table" data-scope="all"><thead></thead><tbody><tr><td colspan="4" class="text-center classwise-fees-loading"><div class="loading-spinner"></div><div>Loading fee summary...</div></td></tr></tbody><tfoot></tfoot></table>
                        </div>
                    </div>
                    <div class="tab-pane" id="classwise-exclude-final">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered classwise-fees-table" data-scope="exclude_final"><thead></thead><tbody><tr><td colspan="4" class="text-center classwise-fees-loading"><div class="loading-spinner"></div><div>Loading fee summary...</div></td></tr></tbody><tfoot></tfoot></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <!-- ═══ ROW 8 — QUICK LINKS + CALENDAR + STAFF ═══ -->
        <?php
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $div_col = 12; $div_rol = 12; $bar_chart = true; $line_chart = true;
        if ($this->rbac->hasPrivilege('staff_role_count_widget', 'can_view')) { $div_col = 9; }
        $widget_col = array();
        if ($this->rbac->hasPrivilege('Monthly fees_collection_widget', 'can_view')) { $widget_col[0] = 1; $div_rol = 3; }
        if ($this->rbac->hasPrivilege('monthly_expense_widget', 'can_view')) { $widget_col[1] = 2; $div_rol = 3; }
        if ($this->rbac->hasPrivilege('student_count_widget', 'can_view')) { $widget_col[2] = 3; $div_rol = 3; }
        $div = sizeof($widget_col);
        $widget = !empty($widget_col) ? 12 / $div : 12;
        ?>
        <div class="mn-section">
            <div class="mn-row">
                <div class="mn-col-75">
                    <div class="mn-row" style="margin-bottom:8px;">
                        <?php if ($this->module_lib->hasActive('fees_collection') && $this->rbac->hasPrivilege('Monthly fees_collection_widget', 'can_view')) { ?>
                        <div class="mn-col-25">
                            <a href="<?php echo site_url('studentfee'); ?>" class="mn-qlink" id="monthly-fees-collection-widget" data-url="<?php echo site_url('admin/admin/monthly_fees_collection_widget'); ?>">
                                <div class="mn-qlink-icon" style="background:#ecfdf5;color:var(--mn-green);"><i class="fa fa-money"></i></div>
                                <div><div class="mn-qlink-label"><?php echo $this->lang->line('monthly_fees_collection'); ?></div><div class="mn-qlink-value mfc-amount fo-skeleton">0</div></div>
                            </a>
                        </div>
                        <?php } ?>
                        <?php if ($this->module_lib->hasActive('income') && $this->rbac->hasPrivilege('monthly_income_widget', 'can_view')) { ?>
                        <div class="mn-col-25">
                            <a href="<?php echo site_url('admin/income'); ?>" class="mn-qlink" id="monthly-income-widget" data-url="<?php echo site_url('admin/admin/monthly_income_widget'); ?>">
                                <div class="mn-qlink-icon" style="background:#eff6ff;color:var(--mn-blue);"><i class="fa fa-bank"></i></div>
                                <div><div class="mn-qlink-label">Monthly <?php echo $this->lang->line('income'); ?></div><div class="mn-qlink-value mi-amount fo-skeleton">0</div></div>
                            </a>
                        </div>
                        <?php } ?>
                        <?php if ($this->module_lib->hasActive('expense') && $this->rbac->hasPrivilege('monthly_expense_widget', 'can_view')) { ?>
                        <div class="mn-col-25">
                            <a href="<?php echo site_url('admin/expense'); ?>" class="mn-qlink" id="monthly-expense-widget" data-url="<?php echo site_url('admin/admin/monthly_expense_widget'); ?>">
                                <div class="mn-qlink-icon" style="background:#fef2f2;color:var(--mn-red);"><i class="fa fa-credit-card"></i></div>
                                <div><div class="mn-qlink-label"><?php echo $this->lang->line('monthly_expenses'); ?></div><div class="mn-qlink-value me-amount fo-skeleton">0</div></div>
                            </a>
                        </div>
                        <?php } ?>
                        <?php if ($this->module_lib->hasActive('whatsapp_messaging')) { ?>
                        <div class="mn-col-25">
                            <a href="<?php echo site_url('whatsappconfig'); ?>" class="mn-qlink" id="whatsapp-msg-widget" data-url="<?php echo site_url('admin/admin/whatsapp_sent_widget'); ?>">
                                <div class="mn-qlink-icon" style="background:#dcfce7;color:#25D366;"><i class="fa fa-whatsapp"></i></div>
                                <div><div class="mn-qlink-label">WhatsApp Sent</div><div class="mn-qlink-value wa-count fo-skeleton">0</div></div>
                            </a>
                        </div>
                        <?php } ?>
                    </div>

                    <?php if ($this->module_lib->hasActive('calendar_to_do_list') && $this->rbac->hasPrivilege('calendar_to_do_list', 'can_view')) { ?>
                    <div class="mn-card">
                        <div class="mn-card-body" style="padding:20px;"><div id="calendar"></div></div>
                    </div>
                    <?php } ?>
                </div>

                <?php if ($this->rbac->hasPrivilege('staff_role_count_widget', 'can_view')) { ?>
                <div class="mn-col-25">
                    <div class="mn-card">
                        <div class="mn-card-head">Staff Roles</div>
                        <div class="mn-card-body" style="padding:12px 16px;">
                            <?php foreach ($roles as $key => $value) { ?>
                            <div class="mn-role-card">
                                <div class="mn-role-icon"><i class="fa fa-users"></i></div>
                                <div class="mn-role-name"><?php echo $key; ?></div>
                                <div class="mn-role-count"><?php echo $value; ?></div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

<div id="newEventModal" class="modal fade " role="dialog">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line("add_new_event"); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form role="form" id="addevent_form" method="post" enctype="multipart/form-data" action="">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('event_title'); ?></label><small class="req"> *</small>
                                <input class="form-control" name="title" id="input-field">
                                <span class="text-danger"><?php echo form_error('title'); ?></span>
                            </div>    
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('description'); ?></label>
                                <textarea name="description" class="form-control" id="desc-field"></textarea>
                            </div>    
                        </div>
                    <div class="col-md-12 col-lg-12 col-sm-12">        
                         <div class="row">
                            <div class="col-md-6 col-lg-6 col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('event_from'); ?><small class="req"> *</small></label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                        <input type="text" autocomplete="off" name="event_from" class="form-control pull-right event_from">
                                    </div>
                                </div>    
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('event_to'); ?><small class="req"> *</small></label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                        <input type="text" autocomplete="off" name="event_to" class="form-control pull-right event_to">
                                    </div>
                                </div>    
                            </div>
                        </div>
                    </div>    
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('event_color'); ?></label>
                                <input type="hidden" name="eventcolor" autocomplete="off" id="eventcolor" class="form-control">
                            </div>    
                        </div>
                        <div class="col-md-12">
                           <div class="form-group"> 
                            <?php
$i      = 0;
$colors = '';
foreach ($event_colors as $color) {
    $color_selected_class = 'cpicker-small';
    if ($i == 0) {
        $color_selected_class = 'cpicker-big';
    }
    $colors .= "<div class='calendar-cpicker cpicker " . $color_selected_class . "' data-color='" . $color . "' style='background:" . $color . ";border:1px solid " . $color . "; border-radius:100px'></div>";
    $i++;
}
echo '<div class="cpicker-wrapper">';
echo $colors;
echo '</div>';
?>
                           </div> 
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="pt15 displayblock overflow-hidden w-100"><?php echo $this->lang->line('event_type'); ?></label>
                                <label class="radio-inline w-xs-45">
                                    <input type="radio" name="event_type" value="public" id="public"><?php echo $this->lang->line('public'); ?>
                                </label>
                                <label class="radio-inline w-xs-45">
                                    <input type="radio" name="event_type" value="private" checked id="private"><?php echo $this->lang->line('private'); ?>
                                </label>
                                <label class="radio-inline w-xs-45 ml-xs-0">
                                    <input type="radio" name="event_type" value="sameforall" id="public"><?php echo $this->lang->line('all'); ?> <?php echo json_decode($role)->name; ?>
                                </label>
                                <label class="radio-inline w-xs-45">
                                    <input type="radio" name="event_type" value="protected" id="public"><?php echo $this->lang->line('protected'); ?>
                                </label>
                            </div>    
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <input type="submit" class="btn btn-primary submit_addevent pull-right" value="<?php echo $this->lang->line('save'); ?>"></div> 
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="viewEventModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('edit_event'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form role="form" method="post" id="updateevent_form" enctype="multipart/form-data" action="">
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_title') ?></label>
                            <input class="form-control" name="title" placeholder="" id="event_title">
                        </div>
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('description') ?></label>
                            <textarea name="description" class="form-control" placeholder="" id="event_desc"></textarea></div>
                      <div class="row">
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_from'); ?></label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" autocomplete="off" name="event_from" class="form-control pull-right event_from">
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_to'); ?></label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" autocomplete="off" name="event_to" class="form-control pull-right event_to">
                            </div>
                        </div>
                            </div>
                        <input type="hidden" name="eventid" id="eventid">
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_color') ?></label>
                            <input type="hidden" name="eventcolor" autocomplete="off" placeholder="Event Color" id="event_color" class="form-control">
                        </div>
                        <div class="form-group col-md-12">
                            <?php
$i      = 0;
$colors = '';
foreach ($event_colors as $color) {
    $colorid              = trim($color, "#");
    $color_selected_class = 'cpicker-small';
    if ($i == 0) {
        $color_selected_class = 'cpicker-big';
    }
    $colors .= "<div id=" . $colorid . " class='calendar-cpicker cpicker " . $color_selected_class . "' data-color='" . $color . "' style='background:" . $color . ";border:1px solid " . $color . "; border-radius:100px'></div>";
    $i++;
}
echo '<div class="cpicker-wrapper selectevent">';
echo $colors;
echo '</div>';
?>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_type') ?></label>
                            <label class="radio-inline">
                                <input type="radio" name="eventtype" value="public" id="public"><?php echo $this->lang->line('public') ?>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="eventtype" value="private" id="private"><?php echo $this->lang->line('private') ?>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="eventtype" value="sameforall" id="public"><?php echo $this->lang->line('all') ?> <?php echo json_decode($role)->name; ?>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="eventtype" value="protected" id="public"><?php echo $this->lang->line('protected') ?>
                            </label>
                        </div>
                        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-11">
                            <input type="submit" class="btn btn-primary submit_update pull-right" value="<?php echo $this->lang->line('save'); ?>">
                        </div>
                        <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1">
<?php if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_delete')) {?>
                                <input type="button" id="delete_event" class="btn btn-primary submit_delete pull-right" value="<?php echo $this->lang->line('delete'); ?>">
<?php }?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script>
$(document).ready(function () {
    $('#viewEventModal,#newEventModal').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });
});
</script> 

<style>
    canvas {
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.1/dist/apexcharts.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    var apexDefaults = {
        chart: { fontFamily: "'Inter', -apple-system, sans-serif", toolbar: { show: false }, animations: { enabled: true, easing: 'easeinout', speed: 600 } },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 0, xaxis: { lines: { show: false } }, yaxis: { lines: { show: true } } },
        xaxis: { labels: { style: { fontSize: '10px', colors: '#94a3b8', fontWeight: 500 } }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { style: { fontSize: '10px', colors: '#94a3b8', fontWeight: 500 } } },
        legend: { position: 'bottom', fontSize: '11px', fontWeight: 500, labels: { colors: '#64748b' }, markers: { width: 8, height: 8, radius: 8, offsetX: -4 }, itemMargin: { horizontal: 12, vertical: 4 } },
        tooltip: { theme: 'dark', style: { fontSize: '11px' }, y: { formatter: function(v) { return v ? v.toLocaleString('en-IN') : '0'; } } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth' }
    };

    <?php if ($this->rbac->hasPrivilege('income_donut_graph', 'can_view') && $this->module_lib->hasActive('income')) { ?>
    new ApexCharts(document.getElementById('doughnut-chart'), {
        chart: { type: 'donut', height: 320, fontFamily: apexDefaults.chart.fontFamily },
        series: [<?php foreach ($incomegraph as $value) { ?><?php echo $value['total']; ?>, <?php } ?>],
        labels: [<?php foreach ($incomegraph as $value) { ?>"<?php echo $value['income_category']; ?>", <?php } ?>],
        colors: [<?php $s = 1; foreach ($incomegraph as $value) { ?>"<?php echo incomegraphColors($s++); ?>", <?php if ($s == 8) { $s = 1; } } ?>],
        plotOptions: { pie: { donut: { size: '65%' }, startAngle: -90, endAngle: 90, offsetY: 10 } },
        legend: { position: 'bottom', fontSize: '11px', labels: { colors: '#64748b' }, markers: { width: 8, height: 8, radius: 8 } },
        dataLabels: { enabled: false },
        tooltip: { theme: 'dark', y: { formatter: function(v) { return '₹' + v.toLocaleString('en-IN'); } } }
    }).render();
    <?php } ?>

    <?php if ($this->rbac->hasPrivilege('expense_donut_graph', 'can_view') && $this->module_lib->hasActive('expense')) { ?>
    new ApexCharts(document.getElementById('doughnut-chart1'), {
        chart: { type: 'donut', height: 320, fontFamily: apexDefaults.chart.fontFamily },
        series: [<?php foreach ($expensegraph as $value) { ?><?php echo $value['total']; ?>, <?php } ?>],
        labels: [<?php foreach ($expensegraph as $value) { ?>"<?php echo $value['exp_category']; ?>", <?php } ?>],
        colors: [<?php $ss = 1; foreach ($expensegraph as $value) { ?>"<?php echo expensegraphColors($ss++); ?>", <?php if ($ss == 8) { $ss = 1; } } ?>],
        plotOptions: { pie: { donut: { size: '65%' }, startAngle: -90, endAngle: 90, offsetY: 10 } },
        legend: { position: 'bottom', fontSize: '11px', labels: { colors: '#64748b' }, markers: { width: 8, height: 8, radius: 8 } },
        dataLabels: { enabled: false },
        tooltip: { theme: 'dark', y: { formatter: function(v) { return '₹' + v.toLocaleString('en-IN'); } } }
    }).render();
    <?php } ?>

    <?php if (($this->module_lib->hasActive('fees_collection')) || ($this->module_lib->hasActive('expense')) || ($this->module_lib->hasActive('income'))) { ?>
    var bar_chart = "<?php echo $bar_chart ?>";
    var line_chart = "<?php echo $line_chart ?>";
    var hasIncome = <?php echo ($this->module_lib->hasActive('income')) ? 'true' : 'false'; ?>;
    var hasExpense = <?php echo ($this->module_lib->hasActive('expense')) ? 'true' : 'false'; ?>;

    if (bar_chart) {
        var $barEl = $('#barChart');
        var barUrl = $barEl.data('url');
        if ($barEl.length && barUrl) {
            var $barWrap = $barEl.closest('.chart-async');
            $barWrap.addClass('is-loading');
            $.ajax({ url: barUrl, method: 'GET', dataType: 'json' }).done(function(resp) {
                if (!resp || resp.status !== 'success' || !resp.data) { $barWrap.removeClass('is-loading'); return; }
                var series = [];
                if (hasIncome) {
                    series.push({ name: 'Fees Collection', data: (resp.data.collection || []).map(Number) });
                    series.push({ name: 'Incidental Fees', data: (resp.data.incidental || []).map(Number) });
                }
                if (hasExpense) {
                    series.push({ name: 'Expenses', data: (resp.data.expense || []).map(Number) });
                }
                new ApexCharts($barEl[0], $.extend(true, {}, apexDefaults, {
                    chart: { type: 'area', height: 280 },
                    series: series,
                    xaxis: { categories: resp.data.labels || [] },
                    colors: ['#008FFB', '#00E396', '#FF4560'],
                    stroke: { curve: 'smooth', width: 2.5 },
                    dataLabels: { enabled: false }
                })).render();
                $barWrap.removeClass('is-loading');
            }).fail(function() { $barWrap.removeClass('is-loading'); });
        }
    }

    if (line_chart) {
        var $lineEl = $('#lineChart');
        var lineUrl = $lineEl.data('url');
        if ($lineEl.length && lineUrl) {
            var $lineWrap = $lineEl.closest('.chart-async');
            $lineWrap.addClass('is-loading');
            $.ajax({ url: lineUrl, method: 'GET', dataType: 'json' }).done(function(resp) {
                if (!resp || resp.status !== 'success' || !resp.data) { $lineWrap.removeClass('is-loading'); return; }
                var series = [];
                if (hasExpense) {
                    series.push({ name: 'Expense', data: (resp.data.expense || []).map(Number) });
                }
                if (hasIncome) {
                    series.push({ name: 'Collection', data: (resp.data.collection || []).map(Number) });
                }
                new ApexCharts($lineEl[0], $.extend(true, {}, apexDefaults, {
                    chart: { type: 'area', height: 280 },
                    series: series,
                    xaxis: { categories: resp.data.labels || [] },
                    colors: ['#ef4444', '#10b981'],
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05, stops: [0, 90, 100] } },
                    stroke: { width: 2.5 }
                })).render();
                $lineWrap.removeClass('is-loading');
            }).fail(function() { $lineWrap.removeClass('is-loading'); });
        }
    }
    <?php } ?>
});

    $(document).ready(function () {
        $(document).on('click', '.close_notice', function () {
        var data = $(this).data();
        $.ajax({
        type: "POST",
                url: base_url + "admin/notification/read",
                data: {'notice': data.noticeid},
                dataType: "json",
                success: function (data) {
                if (data.status == "fail") {

                errorMsg(data.msg);
                } else {
                successMsg(data.msg);
                }

                }
        });
        });

        // Force flex-wrap: nowrap for equal-height-row elements
        $('.equal-height-row').css('flex-wrap', 'nowrap');
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
                // Add skeleton loading to new card fields on AJAX start
                $(document).ajaxStart(function() {
                    var $widget = $('#fees-overview-widget');
                    $widget.find('.fo-total-demand-count, .fo-demand-progress, .fo-demand-sum, .fo-demand-bar, .fo-total-collection-count, .fo-collection-progress, .fo-collection-sum, .fo-collection-bar, .fo-total-awaiting-count, .fo-awaiting-progress, .fo-awaiting-sum, .fo-awaiting-bar, .fo-total-cfdemand-count, .fo-cfdemand-progress, .fo-cfdemand-sum, .fo-cfdemand-bar, .fo-total-cfcollection-count, .fo-cfcollection-progress, .fo-cfcollection-sum, .fo-cfcollection-bar, .fo-total-cfbalance-count, .fo-cfbalance-progress, .fo-cfbalance-sum, .fo-cfbalance-bar').addClass('fo-skeleton');

                    var $headcount = $('#student-headcount-widget');
                    $headcount.find('.shc-total, .shc-male-count, .shc-male-percent, .shc-female-count, .shc-female-percent').addClass('fo-skeleton');

                    var $studentAttendance = $('#student-attendance-widget');
                    $studentAttendance.find('.sta-present-count, .sta-present-percent, .sta-late-count, .sta-late-percent, .sta-absent-count, .sta-absent-percent, .sta-halfday-count, .sta-halfday-percent').addClass('fo-skeleton');

                    var $staffAttendance = $('#staff-attendance-widget');
                    $staffAttendance.find('.sfa-present-count, .sfa-present-percent, .sfa-late-count, .sfa-late-percent, .sfa-absent-count, .sfa-absent-percent, .sfa-halfday-count, .sfa-halfday-percent, .sfa-permission-count, .sfa-permission-percent').addClass('fo-skeleton');

                    var $enquiryOverview = $('#enquiry-overview-widget');
                    $enquiryOverview.find('.eo-won-count, .eo-won-percent, .eo-active-count, .eo-active-percent, .eo-total-count, .eo-app-count, .eo-app-total-percent, .eo-applied-count, .eo-applied-percent, .eo-revoked-count').addClass('fo-skeleton');

                    var $libraryOverview = $('#library-overview-widget');
                    $libraryOverview.find('.lib-dueforreturn, .lib-forreturn, .lib-total-issued, .lib-total, .lib-issued-progress, .lib-availble, .lib-availble-progress').addClass('fo-skeleton');

                    $('#monthly-fees-collection-widget .mfc-amount, #monthly-income-widget .mi-amount, #monthly-expense-widget .me-amount').addClass('fo-skeleton');
                    $('#whatsapp-msg-widget .wa-count').addClass('fo-skeleton');

                    $('#staff-approved-leave-widget .sal-approved, #staff-approved-leave-widget .sal-total').addClass('fo-skeleton');
                    $('#student-approved-leave-widget .stl-approved, #student-approved-leave-widget .stl-total').addClass('fo-skeleton');
                    $('#converted-leads-widget .cl-complete, #converted-leads-widget .cl-total').addClass('fo-skeleton');
                    $('#complaint-widget .cw-open, #complaint-widget .cw-total').addClass('fo-skeleton');
                });
                // Remove skeleton loading on AJAX complete
                $(document).ajaxStop(function() {
                    var $widget = $('#fees-overview-widget');
                    $widget.find('.fo-total-demand-count, .fo-demand-progress, .fo-demand-sum, .fo-demand-bar, .fo-total-collection-count, .fo-collection-progress, .fo-collection-sum, .fo-collection-bar, .fo-total-awaiting-count, .fo-awaiting-progress, .fo-awaiting-sum, .fo-awaiting-bar, .fo-total-cfdemand-count, .fo-cfdemand-progress, .fo-cfdemand-sum, .fo-cfdemand-bar, .fo-total-cfcollection-count, .fo-cfcollection-progress, .fo-cfcollection-sum, .fo-cfcollection-bar, .fo-total-cfbalance-count, .fo-cfbalance-progress, .fo-cfbalance-sum, .fo-cfbalance-bar').removeClass('fo-skeleton');

                    var $headcount = $('#student-headcount-widget');
                    $headcount.find('.shc-total, .shc-male-count, .shc-male-percent, .shc-female-count, .shc-female-percent').removeClass('fo-skeleton');

                    var $studentAttendance = $('#student-attendance-widget');
                    $studentAttendance.find('.sta-present-count, .sta-present-percent, .sta-late-count, .sta-late-percent, .sta-absent-count, .sta-absent-percent, .sta-halfday-count, .sta-halfday-percent').removeClass('fo-skeleton');

                    var $staffAttendance = $('#staff-attendance-widget');
                    $staffAttendance.find('.sfa-present-count, .sfa-present-percent, .sfa-late-count, .sfa-late-percent, .sfa-absent-count, .sfa-absent-percent, .sfa-halfday-count, .sfa-halfday-percent, .sfa-permission-count, .sfa-permission-percent').removeClass('fo-skeleton');

                    var $enquiryOverview = $('#enquiry-overview-widget');
                    $enquiryOverview.find('.eo-won-count, .eo-won-percent, .eo-active-count, .eo-active-percent, .eo-total-count, .eo-app-count, .eo-app-total-percent, .eo-applied-count, .eo-applied-percent, .eo-revoked-count').removeClass('fo-skeleton');

                    var $libraryOverview = $('#library-overview-widget');
                    $libraryOverview.find('.lib-dueforreturn, .lib-forreturn, .lib-total-issued, .lib-total, .lib-issued-progress, .lib-availble, .lib-availble-progress').removeClass('fo-skeleton');

                    $('#monthly-fees-collection-widget .mfc-amount, #monthly-income-widget .mi-amount, #monthly-expense-widget .me-amount').removeClass('fo-skeleton');
                    $('#whatsapp-msg-widget .wa-count').removeClass('fo-skeleton');

                    $('#staff-approved-leave-widget .sal-approved, #staff-approved-leave-widget .sal-total').removeClass('fo-skeleton');
                    $('#student-approved-leave-widget .stl-approved, #student-approved-leave-widget .stl-total').removeClass('fo-skeleton');
                    $('#converted-leads-widget .cl-complete, #converted-leads-widget .cl-total').removeClass('fo-skeleton');
                    $('#complaint-widget .cw-open, #complaint-widget .cw-total').removeClass('fo-skeleton');
                });
        // Function to update ticker animation properties
        function updateTickerAnimation() {
            $('.birthday-ticker-content').each(function() {
                var $tickerContent = $(this);
                
                // Temporarily pause animation and reset transform to measure true scrollHeight
                // Also set height to auto temporarily to get natural scrollHeight
                $tickerContent.css({
                    'animation-play-state': 'paused',
                    'transform': 'translateY(0)',
                    'height': 'auto' // Allow content to determine height for measurement
                });

                // Calculate the height of one full set of unique items
                // The content is duplicated, so scrollHeight contains two sets of data
                var totalContentHeight = $tickerContent[0].scrollHeight;
                var singleCycleHeight = totalContentHeight / 2;
                console.log('Ticker Element:', $tickerContent);
                console.log('Total Content Height:', totalContentHeight);
                console.log('Single Cycle Height:', singleCycleHeight);

                // Set the CSS variables for translation and duration
                $tickerContent.css({
                    '--ticker-translate-y': -singleCycleHeight + 'px',
                    '--ticker-duration': '20s' // Enforce 20s duration dynamically
                });

                // Set explicit height to prevent reflow during animation
                // This ensures the 200% logic works as intended from the CSS perspective
                $tickerContent.css('height', totalContentHeight + 'px'); 

                // Resume animation
                $tickerContent.css('animation-play-state', 'running');
            });
        }
        window.updateTickerAnimation = updateTickerAnimation;

        // Run on document ready
        updateTickerAnimation();

        // Run on window resize, with a debounce for performance
        var resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                updateTickerAnimation();
            }, 250); // Debounce to prevent excessive calls
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        // Load data for all three fees overview widgets
        var widgetIds = ['#fees-overview-widget-payment', '#fees-overview-widget-collection', '#fees-overview-widget-pending'];
        
        $.each(widgetIds, function(index, widgetId) {
            var $widget = $(widgetId);
            if (!$widget.length) {
                return;
            }

            var url = $widget.data('url');
            if (!url) {
                return;
            }

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json'
            }).done(function(resp) {
                if (!resp || resp.status !== 'success' || !resp.data) {
                    return;
                }

                var d = resp.data;
                
                // Update all widgets with the same data
                $widget.find('.fo-total-unpaid').text(d.total_unpaid);
                $widget.find('.fo-unpaid-progress').text(d.unpaid_progress);
                $widget.find('.fo-unpaid-collected-sum').text(d.currency_zero);
                $widget.find('.fo-unpaid-sum').text(d.unpaid_sum_formatted);
                $widget.find('.fo-unpaid-bar').css('width', d.unpaid_progress + '%');

                $widget.find('.fo-total-partial').text(d.total_partial);
                $widget.find('.fo-partial-progress').text(d.partial_progress);
                $widget.find('.fo-partial-collected-sum').text(d.partial_collected_sum_formatted);
                $widget.find('.fo-partial-sum').text(d.partial_sum_formatted);
                $widget.find('.fo-partial-bar').css('width', d.partial_progress + '%');

                $widget.find('.fo-total-paid').text(d.total_paid);
                $widget.find('.fo-paid-progress').text(d.paid_progress);
                $widget.find('.fo-paid-sum').text(d.paid_sum_formatted);
                $widget.find('.fo-paid-balance-sum').text(d.currency_zero);
                $widget.find('.fo-paid-bar').css('width', d.paid_progress + '%');

                // Use new backend fields for each card
                $widget.find('.fo-total-demand-count').text(d.demand_count || 0);
                $widget.find('.fo-demand-progress').text(d.demand_progress);
                $widget.find('.fo-demand-sum').text(d.demand_sum_formatted);
                $widget.find('.fo-demand-bar').css('width', d.demand_progress + '%');

                $widget.find('.fo-total-collection-count').text(d.collection_count || 0);
                $widget.find('.fo-collection-progress').text(d.collection_progress);
                $widget.find('.fo-collection-sum').text(d.collection_sum_formatted);
                $widget.find('.fo-collection-bar').css('width', d.collection_progress + '%');

                $widget.find('.fo-total-awaiting-count').text(d.awaiting_count || 0);
                $widget.find('.fo-awaiting-progress').text(d.awaiting_progress);
                $widget.find('.fo-awaiting-sum').text(d.awaiting_sum_formatted);
                $widget.find('.fo-awaiting-bar').css('width', d.awaiting_progress + '%');

                $widget.find('.fo-total-cfdemand-count').text(d.cfdemand_count || 0);
                $widget.find('.fo-cfdemand-progress').text(d.cfdemand_progress);
                $widget.find('.fo-cfdemand-sum').text(d.cfdemand_sum_formatted);
                $widget.find('.fo-cfdemand-bar').css('width', d.cfdemand_progress + '%');

                $widget.find('.fo-total-cfcollection-count').text(d.cfcollection_count || 0);
                $widget.find('.fo-cfcollection-progress').text(d.cfcollection_progress);
                $widget.find('.fo-cfcollection-sum').text(d.cfcollection_sum_formatted);
                $widget.find('.fo-cfcollection-bar').css('width', d.cfcollection_progress + '%');

                $widget.find('.fo-total-cfbalance-count').text(d.cfbalance_count || 0);
                $widget.find('.fo-cfbalance-progress').text(d.cfbalance_progress);
                $widget.find('.fo-cfbalance-sum').text(d.cfbalance_sum_formatted);
                $widget.find('.fo-cfbalance-bar').css('width', d.cfbalance_progress + '%');

                $('.fees-awaiting-amount').text(d.fees_awaiting_total_net_balance_formatted);

                $widget.find('.fo-skeleton').removeClass('fo-skeleton');

                $('.fees-awaiting-amount').removeClass('fo-skeleton');

                var $awaitingBar = $('.fees-awaiting-progress-bar');
                if ($awaitingBar.length && typeof d.fees_awaiting_progress !== 'undefined') {
                    $awaitingBar.css('width', d.fees_awaiting_progress + '%');
                }
            }).fail(function() {
                $widget.find('.fo-skeleton').removeClass('fo-skeleton');
            });
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        var $classwiseWidget = $('#fees-classwise-widget');
        if (!$classwiseWidget.length) {
            return;
        }

        var url = $classwiseWidget.data('url');
        if (!url) {
            return;
        }

        // feeTypeCols: ordered array of {id, name}
        // transportActive: bool
        // classLabel: string ("Class" or "Department")
        function renderRows($table, rows, totals, feeTypeCols, transportActive, classLabel) {
            var $thead = $table.find('thead');
            var $tbody = $table.find('tbody');
            var $tfoot = $table.find('tfoot');

            // Build header
            var head1 = '<tr><th rowspan="2" style="min-width:140px;">' + classLabel + '</th>';
            var head2 = '<tr>';
            $.each(feeTypeCols, function(i, ft) {
                head1 += '<th colspan="3">' + ft.name + '</th>';
                head2 += '<th>Demand</th><th>Paid</th><th>Pending</th>';
            });
            if (transportActive) {
                head1 += '<th colspan="3">Transport</th>';
                head2 += '<th>Demand</th><th>Paid</th><th>Pending</th>';
            }
            head1 += '</tr>';
            head2 += '</tr>';
            $thead.html(head1 + head2);

            var totalCols = 1 + (feeTypeCols.length * 3) + (transportActive ? 3 : 0);

            if (!rows || !rows.length) {
                $tbody.html('<tr><td colspan="' + totalCols + '" class="text-center">No data available.</td></tr>');
                $tfoot.empty();
                return;
            }

            function buildDataCells(row) {
                var html = '';
                $.each(feeTypeCols, function(i, ft) {
                    var ftData = row.fee_types && row.fee_types[ft.id] ? row.fee_types[ft.id] : null;
                    html += '<td>' + (ftData ? ftData.demand_formatted  : '-') + '</td>';
                    html += '<td>' + (ftData ? ftData.paid_formatted    : '-') + '</td>';
                    html += '<td>' + (ftData ? ftData.pending_formatted : '-') + '</td>';
                });
                if (transportActive) {
                    html += '<td>' + (row.transport_demand_formatted  || '-') + '</td>';
                    html += '<td>' + (row.transport_paid_formatted    || '-') + '</td>';
                    html += '<td>' + (row.transport_pending_formatted || '-') + '</td>';
                }
                return html;
            }

            var bodyHtml = '';
            $.each(rows, function(i, row) {
                bodyHtml += '<tr><td>' + row.class_name + '</td>' + buildDataCells(row) + '</tr>';
            });
            $tbody.html(bodyHtml);

            if (totals) {
                $tfoot.html('<tr><th>Grand Total</th>' + buildDataCells(totals) + '</tr>');
            } else {
                $tfoot.empty();
            }
        }

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json'
        }).done(function(resp) {
            if (!resp || resp.status !== 'success' || !resp.data) {
                return;
            }

            var allRows     = resp.data.all || [];
            var excludeRows = resp.data.exclude_final || [];
            var totalsAll     = resp.data.totals ? resp.data.totals.all          : null;
            var totalsExclude = resp.data.totals ? resp.data.totals.exclude_final : null;

            // fee_type_columns from PHP: object {ft_id: ft_name, ...}
            // Convert to ordered array for rendering
            var rawCols = resp.data.fee_type_columns || {};
            var feeTypeCols = [];
            $.each(rawCols, function(ftId, ftName) {
                feeTypeCols.push({id: ftId, name: ftName});
            });

            var transportActive = resp.data.transport_active ? true : false;
            var classLabel = $classwiseWidget.data('class-label') || 'Class';

            var $tables = $classwiseWidget.find('.classwise-fees-table');
            $tables.each(function() {
                var $table = $(this);
                var scope  = $table.data('scope');
                if (scope === 'exclude_final') {
                    renderRows($table, excludeRows, totalsExclude, feeTypeCols, transportActive, classLabel);
                } else {
                    renderRows($table, allRows, totalsAll, feeTypeCols, transportActive, classLabel);
                }
            });
        }).fail(function() {
            $classwiseWidget.find('tbody').html('<tr><td colspan="4" class="text-center">Unable to load data.</td></tr>');
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        var $headcount = $('#student-headcount-widget');
        if (!$headcount.length) {
            return;
        }

        var url = $headcount.data('url');
        if (!url) {
            return;
        }

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json'
        }).done(function(resp) {
            if (!resp || resp.status !== 'success' || !resp.data) {
                return;
            }

            var d = resp.data;
            $headcount.find('.shc-total').text(d.total_students_heads || 0);

            $headcount.find('.shc-male-count').text(d.male_students || 0);
            $headcount.find('.shc-male-percent').text(d.male_percent || 0);
            $headcount.find('.shc-male-bar').css('width', (d.male_percent || 0) + '%');

            $headcount.find('.shc-female-count').text(d.female_students || 0);
            $headcount.find('.shc-female-percent').text(d.female_percent || 0);
            $headcount.find('.shc-female-bar').css('width', (d.female_percent || 0) + '%');

            if ((d.other_students || 0) > 0) {
                $headcount.find('.shc-others').show();
                $headcount.find('.shc-other-count').text(d.other_students || 0);
                $headcount.find('.shc-other-percent').text(d.other_percent || 0);
                $headcount.find('.shc-other-bar').css('width', (d.other_percent || 0) + '%');
            } else {
                $headcount.find('.shc-others').hide();
            }

            $headcount.find('.fo-skeleton').removeClass('fo-skeleton');
        }).fail(function() {
            $headcount.find('.fo-skeleton').removeClass('fo-skeleton');
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        var $studentAttendance = $('#student-attendance-widget');
        if ($studentAttendance.length) {
            var url = $studentAttendance.data('url');
            if (url) {
                $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }

                    var d = resp.data;
                    
                    // Update header with total student count
                    if (d.total_students) {
                        $studentAttendance.find('h5').html($studentAttendance.find('h5').text().split(' - ')[0] + ' - ' + d.total_students);
                    }
                    
                    $studentAttendance.find('.sta-present-count').text(d.total_present || 0);
                    $studentAttendance.find('.sta-present-percent').text(d.present || '0%');
                    $studentAttendance.find('.sta-present-bar').css('width', d.present || '0%');

                    $studentAttendance.find('.sta-late-count').text(d.total_late || 0);
                    $studentAttendance.find('.sta-late-percent').text(d.late || '0%');
                    $studentAttendance.find('.sta-late-bar').css('width', d.late || '0%');

                    $studentAttendance.find('.sta-absent-count').text(d.total_absent || 0);
                    $studentAttendance.find('.sta-absent-percent').text(d.absent || '0%');
                    $studentAttendance.find('.sta-absent-bar').css('width', d.absent || '0%');

                    $studentAttendance.find('.sta-halfday-count').text(d.total_half_day || 0);
                    $studentAttendance.find('.sta-halfday-percent').text(d.half_day || '0%');
                    $studentAttendance.find('.sta-halfday-bar').css('width', d.half_day || '0%');

                    $studentAttendance.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $studentAttendance.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }

        var $staffAttendance = $('#staff-attendance-widget');
        if ($staffAttendance.length) {
            var staffUrl = $staffAttendance.data('url');
            if (staffUrl) {
                $.ajax({
                    url: staffUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }

                    var d = resp.data;
                    
                    // Update header with total staff count
                    if (d.total_staff) {
                        $staffAttendance.find('h5').html($staffAttendance.find('h5').text().split(' - ')[0] + ' - ' + d.total_staff);
                    }
                    
                    $staffAttendance.find('.sfa-present-count').text(d.total_present || 0);
                    $staffAttendance.find('.sfa-present-percent').text(d.present || 0);
                    $staffAttendance.find('.sfa-present-bar').css('width', (d.present || 0) + '%');

                    $staffAttendance.find('.sfa-late-count').text(d.total_late || 0);
                    $staffAttendance.find('.sfa-late-percent').text(d.late || 0);
                    $staffAttendance.find('.sfa-late-bar').css('width', (d.late || 0) + '%');

                    $staffAttendance.find('.sfa-absent-count').text(d.total_absent || 0);
                    $staffAttendance.find('.sfa-absent-percent').text(d.absent || 0);
                    $staffAttendance.find('.sfa-absent-bar').css('width', (d.absent || 0) + '%');

                    $staffAttendance.find('.sfa-halfday-count').text(d.total_half_day || 0);
                    $staffAttendance.find('.sfa-halfday-percent').text(d.half_day || 0);
                    $staffAttendance.find('.sfa-halfday-bar').css('width', (d.half_day || 0) + '%');

                    $staffAttendance.find('.sfa-permission-count').text(d.total_permission || 0);
                    $staffAttendance.find('.sfa-permission-percent').text(d.permission || 0);
                    $staffAttendance.find('.sfa-permission-bar').css('width', (d.permission || 0) + '%');

                    $staffAttendance.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $staffAttendance.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }

        var $enquiryOverview = $('#enquiry-overview-widget');
        if ($enquiryOverview.length) {
            var enquiryUrl = $enquiryOverview.data('url');
            if (enquiryUrl) {
                $.ajax({
                    url: enquiryUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }

                    var d = resp.data;
                    $enquiryOverview.find('.eo-total-count').text(d.total || 0);


                    $enquiryOverview.find('.eo-won-count').text(d.won || 0);
                    var wp = (parseFloat(d.won_progress) || 0).toFixed(2);
                    $enquiryOverview.find('.eo-won-percent').text(wp);
                    $enquiryOverview.find('.eo-won-bar').css('width', wp + '%');

                    $enquiryOverview.find('.eo-active-count').text(d.active || 0);
                    var ap = (parseFloat(d.active_progress) || 0).toFixed(2);
                    $enquiryOverview.find('.eo-active-percent').text(ap);
                    $enquiryOverview.find('.eo-active-bar').css('width', ap + '%');

                    // applications stats
                    $enquiryOverview.find('.eo-app-count').text(d.applications_total || 0);
                    var atp = (parseFloat(d.applications_total_progress) || 0).toFixed(2);
                    $enquiryOverview.find('.eo-app-total-percent').text(atp + '%');
                    $enquiryOverview.find('.eo-app-total-bar').css('width', atp + '%');

                    $enquiryOverview.find('.eo-applied-count').text(d.applied || 0);
                    var aplp = (parseFloat(d.applied_progress) || 0).toFixed(2);
                    $enquiryOverview.find('.eo-applied-percent').text(aplp + '%');
                    $enquiryOverview.find('.eo-applied-bar').css('width', aplp + '%');

                    // Revoked and waiting list counts
                    $enquiryOverview.find('.eo-revoked-count').text(d.revoked || 0);
                    $enquiryOverview.find('.eo-waiting-list-count').text(d.waiting_list || 0);

                    $enquiryOverview.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $enquiryOverview.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        var $libraryOverview = $('#library-overview-widget');
        if ($libraryOverview.length) {
            var libraryUrl = $libraryOverview.data('url');
            if (libraryUrl) {
                $.ajax({
                    url: libraryUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }

                    var d = resp.data;
                    $libraryOverview.find('.lib-dueforreturn').text(d.dueforreturn || 0);
                    $libraryOverview.find('.lib-dueforreturn-bar').css('width', (d.dueforreturn || 0) + '%');

                    $libraryOverview.find('.lib-forreturn').text(d.forreturn || 0);
                    $libraryOverview.find('.lib-forreturn-bar').css('width', (d.forreturn || 0) + '%');

                    $libraryOverview.find('.lib-total-issued').text(d.total_issued || 0);
                    $libraryOverview.find('.lib-total').text(d.total || 0);
                    $libraryOverview.find('.lib-issued-progress').text(d.issued_progress || 0);
                    $libraryOverview.find('.lib-issued-bar').css('width', (d.issued_progress || 0) + '%');

                    $libraryOverview.find('.lib-availble').text(d.availble || 0);
                    $libraryOverview.find('.lib-availble-progress').text(d.availble_progress || 0);
                    $libraryOverview.find('.lib-availble-bar').css('width', (d.availble_progress || 0) + '%');

                    $libraryOverview.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $libraryOverview.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }

        var $monthlyFees = $('#monthly-fees-collection-widget');
        if ($monthlyFees.length) {
            var feesUrl = $monthlyFees.data('url');
            if (feesUrl) {
                $.ajax({
                    url: feesUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }
                    $monthlyFees.find('.mfc-amount').text(resp.data.amount_formatted || '');
                    $monthlyFees.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $monthlyFees.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }

        var $monthlyIncome = $('#monthly-income-widget');
        if ($monthlyIncome.length) {
            var incomeUrl = $monthlyIncome.data('url');
            if (incomeUrl) {
                $.ajax({
                    url: incomeUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }
                    $monthlyIncome.find('.mi-amount').text(resp.data.amount_formatted || '');
                    $monthlyIncome.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $monthlyIncome.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }

        var $monthlyExpense = $('#monthly-expense-widget');
        if ($monthlyExpense.length) {
            var expenseUrl = $monthlyExpense.data('url');
            if (expenseUrl) {
                $.ajax({
                    url: expenseUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }
                    $monthlyExpense.find('.me-amount').text(resp.data.amount_formatted || '');
                    $monthlyExpense.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $monthlyExpense.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }

        var $waWidget = $('#whatsapp-msg-widget');
        if ($waWidget.length) {
            var waUrl = $waWidget.data('url');
            if (waUrl) {
                $.ajax({
                    url: waUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success') {
                        return;
                    }
                    $waWidget.find('.wa-count').text(resp.data.count || 0);
                    $waWidget.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $waWidget.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        var $staffLeave = $('#staff-approved-leave-widget');
        if ($staffLeave.length) {
            var staffLeaveUrl = $staffLeave.data('url');
            if (staffLeaveUrl) {
                $.ajax({
                    url: staffLeaveUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }
                    $staffLeave.find('.sal-approved').text(resp.data.approved || 0);
                    $staffLeave.find('.sal-total').text(resp.data.total || 0);
                    $staffLeave.find('.sal-progress').css('width', (resp.data.percent || 0) + '%');
                    $staffLeave.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $staffLeave.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }

        var $studentLeave = $('#student-approved-leave-widget');
        if ($studentLeave.length) {
            var studentLeaveUrl = $studentLeave.data('url');
            if (studentLeaveUrl) {
                $.ajax({
                    url: studentLeaveUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }
                    $studentLeave.find('.stl-approved').text(resp.data.approved || 0);
                    $studentLeave.find('.stl-total').text(resp.data.total || 0);
                    $studentLeave.find('.stl-progress').css('width', (resp.data.percent || 0) + '%');
                    $studentLeave.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $studentLeave.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }

        var $convertedLeads = $('#converted-leads-widget');
        if ($convertedLeads.length) {
            var convertedUrl = $convertedLeads.data('url');
            if (convertedUrl) {
                $.ajax({
                    url: convertedUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }
                    $convertedLeads.find('.cl-complete').text(resp.data.complete || 0);
                    $convertedLeads.find('.cl-total').text(resp.data.total || 0);
                    $convertedLeads.find('.cl-progress').css('width', (resp.data.percent || 0) + '%');
                    $convertedLeads.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $convertedLeads.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }

        var $complaintWidget = $('#complaint-widget');
        if ($complaintWidget.length) {
            var complaintUrl = $complaintWidget.data('url');
            if (complaintUrl) {
                $.ajax({
                    url: complaintUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        $complaintWidget.find('.fo-skeleton').removeClass('fo-skeleton');
                        return;
                    }
                    $complaintWidget.find('.cw-open').text(resp.data.open || 0);
                    $complaintWidget.find('.cw-total').text(resp.data.total || 0);
                    var pct = resp.data.total > 0 ? Math.round((resp.data.open / resp.data.total) * 100) : 0;
                    $complaintWidget.find('.cw-progress').css('width', pct + '%');
                    $complaintWidget.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $complaintWidget.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        function loadBirthdayWidget($widget, countSelector) {
            if (!$widget.length) {
                return;
            }

            var url = $widget.data('url');
            if (!url) {
                return;
            }

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json'
            }).done(function(resp) {
                if (!resp || resp.status !== 'success') {
                    return;
                }

                if (typeof resp.count !== 'undefined') {
                    $widget.find(countSelector).text(resp.count);
                }

                if (resp.html) {
                    $widget.find('.birthday-widget-body').html(resp.html);
                    if (typeof window.updateTickerAnimation === 'function') {
                        window.updateTickerAnimation();
                    }
                }
            });
        }

        loadBirthdayWidget($('#student-birthday-widget'), '.student-birthday-count');
        loadBirthdayWidget($('#staff-birthday-widget'), '.staff-birthday-count');
    });
</script>