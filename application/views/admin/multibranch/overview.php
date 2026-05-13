<?php
// -----------------------------------------------------------------------
// Management Command Centre — Multi-Branch Overview
// -----------------------------------------------------------------------
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();

// Institution color palette
$inst_colors = ['#3c8dbc', '#dd4b39', '#00a65a', '#f39c12', '#605ca8', '#00c0ef'];

// Build branch URL map for card links
$branch_url_map = [];
if (!empty($branch_list)) {
    foreach ($branch_list as $bl) {
        $branch_url_map[$bl->database_name] = rtrim($bl->branch_url, '/') . '/admin';
    }
}

// Grand totals from server-side (fast COUNT queries)
$grand_students        = 0;
$grand_staff           = 0;
$grand_male_students   = 0;
$grand_female_students = 0;
$grand_male_staff      = 0;
$grand_female_staff    = 0;
foreach ($school_students as $sv) {
    $grand_students        += $sv['total_student'];
    $grand_male_students   += isset($sv['male_students'])   ? $sv['male_students']   : 0;
    $grand_female_students += isset($sv['female_students']) ? $sv['female_students'] : 0;
}
foreach ($staff_list as $sv) {
    $grand_staff        += $sv['total_staff'];
    $grand_male_staff   += isset($sv['male_staff'])   ? $sv['male_staff']   : 0;
    $grand_female_staff += isset($sv['female_staff']) ? $sv['female_staff'] : 0;
}

// Build arrays for JS
$js_branch_order  = [];
$js_branch_colors = [];
$js_branch_names  = [];
$ci = 0;
foreach ($branches as $db_name => $bi) {
    $js_branch_order[]          = $db_name;
    $js_branch_colors[]         = $inst_colors[$ci % count($inst_colors)];
    $js_branch_names[$db_name]  = $bi->name;
    $ci++;
}

// Abbreviation helper
function mcc_abbr($db_name) {
    $map = [
        'mcekknagar'  => 'MCE',
        'amacedu'     => 'AMACEDU',
        'amace'       => 'AMACE',
        'maasc'       => 'MAASC',
        'maptc'       => 'MAPTC',
        'minervademo' => 'DEMO',
    ];
    return isset($map[$db_name]) ? $map[$db_name] : strtoupper(substr($db_name, 0, 5));
}
?>
<style>
/* MCC — embedded styles (Bootstrap grid handles layout) */
@keyframes mcc-shimmer {
    0%   { background-position: -400px 0; }
    100% { background-position:  400px 0; }
}
.sk-shimmer {
    border-radius: 4px;
    background: linear-gradient(90deg, #e8e8e8 25%, #f5f5f5 50%, #e8e8e8 75%);
    background-size: 400px 100%;
    animation: mcc-shimmer 1.4s infinite linear;
    display: block;
}
.sk-inline  { display: inline-block !important; height: 14px; width: 80px; vertical-align: middle; }
.sk-block   { height: 38px; margin-bottom: 6px; }
.sk-block.alt { opacity: .6; }
.sk-chart   { height: 280px; margin-bottom: 0; }
.sk-card    { height: 64px; margin-bottom: 10px; }
.mcc-gender-m { color: #3c8dbc; font-size: 11px; font-weight: 700; }
.mcc-gender-f { color: #e91e63; font-size: 11px; font-weight: 700; }
.mcc-dot    { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 5px; vertical-align: middle; }
.mcc-dot-lg { display: inline-block; width: 13px; height: 13px; border-radius: 50%; margin-right: 8px; vertical-align: middle; }
.mcc-pct-wrap { background: #eee; border-radius: 3px; height: 6px; width: 80px; display: inline-block; vertical-align: middle; margin-right: 4px; }
.mcc-pct-bar  { height: 6px; border-radius: 3px; }
.mcc-tfoot-row td { background: #f0f4f8 !important; font-weight: 600; }
.mcc-table thead th { background: #f5f5f5; font-size: 12px; white-space: nowrap; }
.mcc-table td  { font-size: 13px; vertical-align: middle !important; }
.mcc-table-sm td, .mcc-table-sm th { padding: 5px 8px !important; font-size: 12px; }
.mcc-stat-card { border-left: 4px solid; border-radius: 3px; background: #fafafa; padding: 10px 14px; margin-bottom: 10px; }
.mcc-stat-card .lbl { font-size: 11px; color: #555; text-transform: uppercase; letter-spacing: .4px; font-weight: 600; }
.mcc-stat-card .val { font-size: 18px; font-weight: 700; color: #222; margin-top: 2px; }
.mcc-load-err { text-align: center; color: #cc0000; padding: 20px; font-size: 13px; }
.mcc-inst-box { border-radius: 4px; overflow: hidden; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,.08); background: #fff; }
.mcc-inst-box-header { padding: 10px 14px 6px; }
.mcc-inst-box-body   { padding: 0 14px 12px; }
.mcc-inst-name-link  { font-size: 13px; font-weight: 600; color: #333; text-decoration: none !important; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: 1.3; }
.mcc-inst-name-link:hover { color: #3c8dbc !important; }
.mcc-fees-collected   { font-size: 13px; font-weight: 600; color: #00a65a; }
#mcc-nav > li > a     { font-weight: 600; font-size: 13px; }
#mcc-nav > li.active > a { color: #3c8dbc; }
/* Card badge+name — flexbox so badge never overlaps text */
.mcc-card-header-row { display: flex; align-items: flex-start; gap: 8px; }
.mcc-card-badge      { flex: 0 0 auto; }
.mcc-card-name-wrap  { flex: 1 1 0; min-width: 0; }
/* KPI strip — equal tile heights via flex stretch */
.mcc-kpi-row { display: flex !important; flex-wrap: wrap; align-items: stretch; margin-right: -15px; margin-left: -15px; }
.mcc-kpi-row > [class*='col-'] { display: flex; float: none; }
.mcc-kpi-row .info-box { flex: 1; min-height: 0; margin-bottom: 15px; }
.mcc-kpi-row .info-box-icon { display: flex; align-items: center; justify-content: center; height: auto; min-height: 0; line-height: normal; }
</style>

<div class="content-wrapper" style="background:#f0f2f5">
<section class="content" style="padding:15px">

<!-- PAGE TITLE -->
<div class="row" style="margin-bottom:14px">
  <div class="col-xs-12">
    <h3 style="margin:0; font-size:20px; font-weight:700; color:#333; display:inline-block">
      <i class="fa fa-building-o" style="color:#3c8dbc; margin-right:8px"></i>Management Command Centre
    </h3>
    <small style="color:#666; margin-left:12px"><?php echo count($branches); ?> institutions &mdash; <?php echo date('d M Y'); ?></small>
  </div>
</div>

<!-- INSTITUTION CARDS -->
<div class="row">
<?php $ci = 0; foreach ($branches as $db_name => $bi):
    $color    = $inst_colors[$ci % count($inst_colors)];
    $abbr     = mcc_abbr($db_name);
    $is_home  = ($ci === 0);
    $card_url = $is_home ? base_url('admin') : (isset($branch_url_map[$db_name]) ? $branch_url_map[$db_name] : '#');
    $disp_name= $is_home ? $home_name : $bi->name;
    $students        = isset($school_students[$db_name]) ? $school_students[$db_name]['total_student']  : 0;
    $male_students   = isset($school_students[$db_name]) ? $school_students[$db_name]['male_students']  : 0;
    $female_students = isset($school_students[$db_name]) ? $school_students[$db_name]['female_students']: 0;
    $staff           = isset($staff_list[$db_name])      ? $staff_list[$db_name]['total_staff']         : 0;
    $male_staff      = isset($staff_list[$db_name])      ? $staff_list[$db_name]['male_staff']          : 0;
    $female_staff    = isset($staff_list[$db_name])      ? $staff_list[$db_name]['female_staff']        : 0;
    $ci++;
?>
<div class="col-xs-12 col-sm-6 col-md-3">
  <div class="mcc-inst-box" style="border-top:4px solid <?php echo $color; ?>">
    <div class="mcc-inst-box-header">
      <div class="mcc-card-header-row">
        <span class="mcc-card-badge" style="background:<?php echo $color; ?>; color:#fff; font-size:10px; font-weight:700; padding:3px 9px; border-radius:20px; letter-spacing:.5px; white-space:nowrap"><?php echo $abbr; ?><?php if($is_home): ?> <i class="fa fa-home"></i><?php endif; ?></span>
        <div class="mcc-card-name-wrap">
          <a href="<?php echo $card_url; ?>" target="_blank" class="mcc-inst-name-link" title="<?php echo htmlspecialchars($disp_name); ?>"><?php echo htmlspecialchars($disp_name); ?></a>
          <span style="font-size:11px; color:#bbb; display:block; line-height:1.3"><?php echo htmlspecialchars($bi->session); ?></span>
        </div>
      </div>
    </div>
    <div class="mcc-inst-box-body">
      <div style="border-top:1px solid #f5f5f5; margin:0 0 8px"></div>
      <table style="width:100%; border-collapse:collapse">
        <tr>
          <td style="border:0; padding:3px 6px 3px 0; width:50%; vertical-align:top">
            <i class="fa fa-graduation-cap" style="color:<?php echo $color; ?>; width:16px; text-align:center"></i>
            <strong style="font-size:16px; color:#222; margin-left:3px"><?php echo number_format($students); ?></strong>
            <div style="margin-left:20px; margin-top:2px">
              <span class="mcc-gender-m">&#9794;<?php echo number_format($male_students); ?></span>
              <span class="mcc-gender-f" style="margin-left:6px">&#9792;<?php echo number_format($female_students); ?></span>
            </div>
          </td>
          <td style="border:0; padding:3px 0; width:50%; vertical-align:top">
            <i class="fa fa-id-badge" style="color:<?php echo $color; ?>; width:16px; text-align:center"></i>
            <strong style="font-size:16px; color:#222; margin-left:3px"><?php echo number_format($staff); ?></strong>
            <div style="margin-left:20px; margin-top:2px">
              <span class="mcc-gender-m">&#9794;<?php echo number_format($male_staff); ?></span>
              <span class="mcc-gender-f" style="margin-left:6px">&#9792;<?php echo number_format($female_staff); ?></span>
            </div>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="border:0; border-top:1px solid #f5f5f5; padding:7px 0 0">
            <i class="fa fa-money" style="color:<?php echo $color; ?>; width:16px; text-align:center"></i>
            <span style="font-size:11px; color:#bbb; margin-left:3px; text-transform:uppercase; letter-spacing:.4px">Collected</span>
            <span class="mcc-fees-collected" data-db="<?php echo $db_name; ?>" style="float:right">
              <span class="sk-shimmer sk-inline" style="width:60px"></span>
            </span>
          </td>
        </tr>
      </table>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>

<!-- KPI STRIP -->
<div class="row mcc-kpi-row" style="margin-bottom:6px">
  <div class="col-xs-6 col-sm-4 col-md-2">
    <div class="info-box" style="border-radius:4px; margin-bottom:0">
      <span class="info-box-icon" style="background:#3c8dbc; width:60px; font-size:26px"><i class="fa fa-graduation-cap"></i></span>
      <div class="info-box-content" style="padding:10px 10px 8px">
        <span class="info-box-text" style="font-size:11px; text-transform:uppercase; letter-spacing:.4px">Students</span>
        <span class="info-box-number" style="font-size:22px; line-height:1.1"><?php echo number_format($grand_students); ?></span>
        <div style="margin-top:3px">
          <span class="mcc-gender-m">&#9794; <?php echo number_format($grand_male_students); ?></span>
          &nbsp;<span class="mcc-gender-f">&#9792; <?php echo number_format($grand_female_students); ?></span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xs-6 col-sm-4 col-md-2">
    <div class="info-box" style="border-radius:4px; margin-bottom:0">
      <span class="info-box-icon" style="background:#605ca8; width:60px; font-size:26px"><i class="fa fa-id-badge"></i></span>
      <div class="info-box-content" style="padding:10px 10px 8px">
        <span class="info-box-text" style="font-size:11px; text-transform:uppercase; letter-spacing:.4px">Staff</span>
        <span class="info-box-number" style="font-size:22px; line-height:1.1"><?php echo number_format($grand_staff); ?></span>
        <div style="margin-top:3px">
          <span class="mcc-gender-m">&#9794; <?php echo number_format($grand_male_staff); ?></span>
          &nbsp;<span class="mcc-gender-f">&#9792; <?php echo number_format($grand_female_staff); ?></span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xs-6 col-sm-4 col-md-2">
    <div class="info-box" style="border-radius:4px; margin-bottom:0">
      <span class="info-box-icon" style="background:#00a65a; width:60px; font-size:26px"><i class="fa fa-inr"></i></span>
      <div class="info-box-content" style="padding:10px 10px 8px">
        <span class="info-box-text" style="font-size:11px; text-transform:uppercase; letter-spacing:.4px">Fees Collected</span>
        <span class="info-box-number" id="kpi-fees-collected" style="font-size:20px; line-height:1.2"><span class="sk-shimmer sk-inline"></span></span>
      </div>
    </div>
  </div>
  <div class="col-xs-6 col-sm-4 col-md-2">
    <div class="info-box" style="border-radius:4px; margin-bottom:0">
      <span class="info-box-icon" style="background:#f39c12; width:60px; font-size:26px"><i class="fa fa-cubes"></i></span>
      <div class="info-box-content" style="padding:10px 10px 8px">
        <span class="info-box-text" style="font-size:11px; text-transform:uppercase; letter-spacing:.4px">Asset Value</span>
        <span class="info-box-number" id="kpi-asset-value" style="font-size:20px; line-height:1.2"><span class="sk-shimmer sk-inline"></span></span>
      </div>
    </div>
  </div>
  <div class="col-xs-6 col-sm-4 col-md-2">
    <div class="info-box" style="border-radius:4px; margin-bottom:0">
      <span class="info-box-icon" style="background:#00c0ef; width:60px; font-size:26px"><i class="fa fa-book"></i></span>
      <div class="info-box-content" style="padding:10px 10px 8px">
        <span class="info-box-text" style="font-size:11px; text-transform:uppercase; letter-spacing:.4px">Library Books</span>
        <span class="info-box-number" id="kpi-total-books" style="font-size:20px; line-height:1.2"><span class="sk-shimmer sk-inline" style="width:65px"></span></span>
      </div>
    </div>
  </div>
</div>

<!-- SECTION NAV -->
<ul class="nav nav-tabs" id="mcc-nav" style="margin-bottom:20px; border-bottom:2px solid #ddd">
  <li class="active" id="nav-fees">
    <a href="#section-fees" data-section="fees"><i class="fa fa-money"></i> Fees</a>
  </li>
  <li id="nav-hr">
    <a href="#section-hr" data-section="hr"><i class="fa fa-users"></i> HR &amp; Payroll</a>
  </li>
  <li id="nav-assets">
    <a href="#section-assets" data-section="assets"><i class="fa fa-cubes"></i> Assets</a>
  </li>
  <li id="nav-academics">
    <a href="#section-academics" data-section="academics"><i class="fa fa-graduation-cap"></i> Academics</a>
  </li>
  <li id="nav-attendance">
    <a href="#section-attendance" data-section="attendance"><i class="fa fa-check-square-o"></i> Attendance</a>
  </li>
  <li class="pull-right">
    <a href="javascript:window.print()" title="Print"><i class="fa fa-print"></i></a>
  </li>
</ul>

<!-- FEES -->
<div class="box" id="section-fees" data-section="fees" style="border-radius:4px; border-top:3px solid #3c8dbc">
  <div class="box-header with-border" style="background:#3c8dbc; padding:12px 18px">
    <h3 class="box-title" style="color:#fff; font-size:15px; font-weight:600"><i class="fa fa-money"></i> Fees Overview</h3>
    <span class="pull-right" style="color:rgba(255,255,255,.75); font-size:12px">Current session &mdash; billed vs collected across all institutions</span>
  </div>
  <div class="box-body">
    <div class="row">
      <div class="col-md-8">
        <div id="fees-chart-skeleton"><div class="sk-shimmer sk-chart"></div></div>
        <div id="fees-chart-box" style="display:none; position:relative; height:300px"><canvas id="fees_chart"></canvas></div>
      </div>
      <div class="col-md-4">
        <div id="fees-summary-cards">
          <div class="sk-shimmer sk-card"></div><div class="sk-shimmer sk-card"></div><div class="sk-shimmer sk-card"></div>
        </div>
      </div>
    </div>
    <div class="row" style="margin-top:18px">
      <div class="col-md-12">
        <div id="fees-table-skeleton">
          <div class="sk-shimmer sk-block"></div><div class="sk-shimmer sk-block alt"></div>
          <div class="sk-shimmer sk-block"></div><div class="sk-shimmer sk-block alt"></div>
        </div>
        <div class="table-responsive" id="fees-table-box" style="display:none">
          <table class="table table-hover table-bordered mcc-table">
            <thead><tr>
              <th>Institution</th><th>Session</th>
              <th class="text-right">Billed</th>
              <th class="text-right">Collected</th>
              <th class="text-right">Balance</th>
              <th class="text-center">Collection %</th>
            </tr></thead>
            <tbody id="fees-tbody"></tbody>
            <tfoot id="fees-tfoot"></tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- HR -->
<div class="box" id="section-hr" data-section="hr" style="border-radius:4px; border-top:3px solid #00a65a">
  <div class="box-header with-border" style="background:#00a65a; padding:12px 18px">
    <h3 class="box-title" style="color:#fff; font-size:15px; font-weight:600"><i class="fa fa-users"></i> HR &amp; Payroll</h3>
    <span class="pull-right" style="color:rgba(255,255,255,.75); font-size:12px" id="hr-month-label">Last month payroll &middot; today's attendance</span>
  </div>
  <div class="box-body">
    <div class="row">
      <div class="col-md-8">
        <div id="hr-chart-skeleton"><div class="sk-shimmer sk-chart"></div></div>
        <div id="hr-chart-box" style="display:none; position:relative; height:300px"><canvas id="hr_chart"></canvas></div>
      </div>
      <div class="col-md-4">
        <div id="hr-summary-cards">
          <div class="sk-shimmer sk-card"></div><div class="sk-shimmer sk-card"></div><div class="sk-shimmer sk-card"></div>
        </div>
      </div>
    </div>
    <div class="row" style="margin-top:18px">
      <div class="col-md-12">
        <div id="hr-table-skeleton">
          <div class="sk-shimmer sk-block"></div><div class="sk-shimmer sk-block alt"></div>
          <div class="sk-shimmer sk-block"></div><div class="sk-shimmer sk-block alt"></div>
        </div>
        <div class="table-responsive" id="hr-table-box" style="display:none">
          <table class="table table-hover table-bordered mcc-table">
            <thead><tr>
              <th>Institution</th>
              <th class="text-center">Total Staff</th>
              <th class="text-center">Payroll Generated</th>
              <th class="text-center">Payroll Paid</th>
              <th class="text-center">Not Generated</th>
              <th class="text-right">Net Payroll</th>
              <th class="text-right">Amount Paid</th>
              <th class="text-center">Present Today</th>
              <th class="text-center">Absent Today</th>
            </tr></thead>
            <tbody id="hr-tbody"></tbody>
            <tfoot id="hr-tfoot"></tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ASSETS -->
<div class="box" id="section-assets" data-section="assets" style="border-radius:4px; border-top:3px solid #f39c12">
  <div class="box-header with-border" style="background:#f39c12; padding:12px 18px">
    <h3 class="box-title" style="color:#fff; font-size:15px; font-weight:600"><i class="fa fa-cubes"></i> Asset Inventory</h3>
    <span class="pull-right" style="color:rgba(255,255,255,.75); font-size:12px">Stock value at purchase cost across all institutions</span>
  </div>
  <div class="box-body">
    <div class="row">
      <div class="col-md-7">
        <div id="assets-chart-skeleton"><div class="sk-shimmer sk-chart"></div></div>
        <div id="assets-chart-box" style="display:none; position:relative; height:280px"><canvas id="assets_chart"></canvas></div>
      </div>
      <div class="col-md-5">
        <div id="assets-summary-cards">
          <div class="sk-shimmer sk-card"></div><div class="sk-shimmer sk-card"></div><div class="sk-shimmer sk-card"></div>
        </div>
      </div>
    </div>
    <div class="row" style="margin-top:18px">
      <div class="col-md-12">
        <div id="assets-table-skeleton">
          <div class="sk-shimmer sk-block"></div><div class="sk-shimmer sk-block alt"></div>
          <div class="sk-shimmer sk-block"></div><div class="sk-shimmer sk-block alt"></div>
        </div>
        <div id="assets-table-box" style="display:none">
          <div id="assets-institution-panels"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ACADEMICS -->
<div class="box" id="section-academics" data-section="academics" style="border-radius:4px; border-top:3px solid #605ca8">
  <div class="box-header with-border" style="background:#605ca8; padding:12px 18px">
    <h3 class="box-title" style="color:#fff; font-size:15px; font-weight:600"><i class="fa fa-graduation-cap"></i> Academics &amp; Library</h3>
    <span class="pull-right" style="color:rgba(255,255,255,.75); font-size:12px">Admissions, library activity and alumni</span>
  </div>
  <div class="box-body">
    <div id="academics-skeleton">
      <div class="sk-shimmer sk-block"></div><div class="sk-shimmer sk-block alt"></div>
      <div class="sk-shimmer sk-block"></div><div class="sk-shimmer sk-block alt"></div>
    </div>
    <div id="academics-content" style="display:none">
      <div class="row">
        <div class="col-md-6">
          <p style="font-size:13px; font-weight:700; color:#444; border-bottom:1px solid #eee; padding-bottom:8px; margin-bottom:12px">
            <i class="fa fa-book" style="color:#3c8dbc"></i> Library
          </p>
          <div class="table-responsive">
            <table class="table table-hover table-bordered mcc-table">
              <thead><tr>
                <th>Institution</th>
                <th class="text-right">Books</th>
                <th class="text-right">Members</th>
                <th class="text-right">Issued</th>
              </tr></thead>
              <tbody id="lib-tbody"></tbody>
            </table>
          </div>
        </div>
        <div class="col-md-6">
          <p style="font-size:13px; font-weight:700; color:#444; border-bottom:1px solid #eee; padding-bottom:8px; margin-bottom:12px">
            <i class="fa fa-pencil-square-o" style="color:#605ca8"></i> Admissions &amp; Alumni
          </p>
          <div class="table-responsive">
            <table class="table table-hover table-bordered mcc-table">
              <thead><tr>
                <th>Institution</th>
                <th class="text-right">Offline Adm.</th>
                <th class="text-right">Online Adm.</th>
                <th class="text-right">Alumni</th>
              </tr></thead>
              <tbody id="adm-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ATTENDANCE -->
<div class="box" id="section-attendance" data-section="attendance" style="border-radius:4px; border-top:3px solid #d81b60; margin-top:20px">
  <div class="box-header with-border" style="background:#d81b60; padding:12px 18px">
    <h3 class="box-title" style="color:#fff; font-size:15px; font-weight:600"><i class="fa fa-check-square-o"></i> Attendance &mdash; Today</h3>
    <span class="pull-right" style="color:rgba(255,255,255,.75); font-size:12px" id="att-date-label"></span>
  </div>
  <div class="box-body">
    <div class="row">
      <div class="col-md-3" id="att-summary-cards">
        <div class="sk-shimmer sk-card"></div>
        <div class="sk-shimmer sk-card"></div>
      </div>
      <div class="col-md-9">
        <div id="att-skeleton">
          <div class="sk-shimmer sk-block"></div>
          <div class="sk-shimmer sk-block alt"></div>
          <div class="sk-shimmer sk-block"></div>
          <div class="sk-shimmer sk-block alt"></div>
        </div>
        <div id="att-content" style="display:none">
          <div class="row">
            <div class="col-sm-7">
              <p style="font-size:13px; font-weight:700; color:#444; border-bottom:1px solid #eee; padding-bottom:8px; margin-bottom:12px">
                <i class="fa fa-graduation-cap" style="color:#d81b60"></i> Student Attendance
              </p>
              <div class="table-responsive">
                <table class="table table-hover table-bordered mcc-table">
                  <thead><tr>
                    <th>Institution</th>
                    <th class="text-right">&#9794; Boys</th>
                    <th class="text-right">&#9792; Girls</th>
                    <th class="text-right text-danger">Absent</th>
                    <th class="text-right">Marked</th>
                  </tr></thead>
                  <tbody id="att-stu-tbody"></tbody>
                  <tfoot id="att-stu-tfoot"></tfoot>
                </table>
              </div>
            </div>
            <div class="col-sm-5">
              <p style="font-size:13px; font-weight:700; color:#444; border-bottom:1px solid #eee; padding-bottom:8px; margin-bottom:12px">
                <i class="fa fa-id-badge" style="color:#605ca8"></i> Staff Attendance
              </p>
              <div class="table-responsive">
                <table class="table table-hover table-bordered mcc-table">
                  <thead><tr>
                    <th>Institution</th>
                    <th class="text-right">Present</th>
                    <th class="text-right text-danger">Absent</th>
                    <th class="text-right">Total</th>
                  </tr></thead>
                  <tbody id="att-stf-tbody"></tbody>
                  <tfoot id="att-stf-tfoot"></tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

</section>
</div>

<script src="<?php echo base_url(); ?>backend/js/Chart.min.js"></script>
<script>
// Save Chart.js v2 reference immediately — footer.php loads v1.0.2 which overwrites Chart global
var ChartV2 = Chart;
var MCC = {
    urls: {
        fees:      '<?php echo site_url("admin/multibranch/branch/fees_overview_async"); ?>',
        hr:        '<?php echo site_url("admin/multibranch/branch/hr_async"); ?>',
        assets:    '<?php echo site_url("admin/multibranch/branch/assets_async"); ?>',
        academics:  '<?php echo site_url("admin/multibranch/branch/academics_async"); ?>',
        attendance: '<?php echo site_url("admin/multibranch/branch/attendance_async"); ?>'
    },
    branchOrder: <?php echo json_encode($js_branch_order); ?>,
    colors:      <?php echo json_encode($js_branch_colors); ?>,
    names:       <?php echo json_encode($js_branch_names); ?>,
    currency:    '<?php echo addslashes($currency_symbol); ?>'
};
var branchSessions = <?php
    $bs = [];
    foreach ($branches as $db => $bi) { $bs[$db] = $bi->session; }
    echo json_encode($bs);
?>;

var loaded = { fees: false, hr: false, assets: false, academics: false, attendance: false };

// ---- Helpers ----
function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function numFmt(v) { return Number(v).toLocaleString('en-IN'); }

// ---- Chart.js v2 defaults ----
ChartV2.defaults.global.defaultFontFamily = "'Helvetica Neue',Helvetica,Arial,sans-serif";
ChartV2.defaults.global.defaultFontSize   = 11;

function buildGroupedBar(ctx, labels, datasets, opts) {
    opts = opts || {};
    return new ChartV2(ctx, {
        type: 'bar',
        data: { labels: labels, datasets: datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } },
            scales: {
                yAxes: [{ ticks: { beginAtZero: true, callback: function(v) {
                    if (v >= 10000000) return (v/10000000).toFixed(1)+'Cr';
                    if (v >= 100000)   return (v/100000).toFixed(1)+'L';
                    if (v >= 1000)     return (v/1000).toFixed(0)+'K';
                    return v;
                } } }]
            },
            tooltips: { callbacks: { label: function(item, data) {
                var val   = item.yLabel;
                var label = data.datasets[item.datasetIndex].label || '';
                return label + ': ' + MCC.currency + Number(val).toLocaleString('en-IN');
            } } }
        }
    });
}

// ================================================================
// FEES
// ================================================================
function loadFees() {
    if (loaded.fees) return;
    loaded.fees = true;

    $.getJSON(MCC.urls.fees).done(function(resp) {
        if (!resp || resp.status !== 'success') return;

        var totalFees=0, totalPaid=0, totalBalance=0;

        if (resp.rows) {
            resp.rows.forEach(function(row) {
                totalFees    += row.total_fees;
                totalPaid    += row.total_paid;
                totalBalance += row.total_balance;

                // Card collected badge
                $('.mcc-fees-collected[data-db="'+row.db_name+'"]').html(row.total_paid_formatted);
            });

            // KPI
            $('#kpi-fees-collected').text(MCC.currency + Number(totalPaid).toLocaleString('en-IN'));

            // Summary cards
            var pct = totalFees > 0 ? ((totalPaid/totalFees)*100).toFixed(1) : 0;
            $('#fees-summary-cards').html(
                mkStatCard('#3c8dbc','Total Billed',    MCC.currency+numFmt(totalFees))    +
                mkStatCard('#00a65a','Collected',       MCC.currency+numFmt(totalPaid))    +
                mkStatCard('#dd4b39','Balance',         MCC.currency+numFmt(totalBalance)) +
                mkStatCard('#f39c12','Collection Rate', pct+'%')
            );

            // Table
            var tbody='', tfees=0, tpaid=0, tbal=0;
            resp.rows.forEach(function(row,i) {
                var rowPct   = row.total_fees>0 ? ((row.total_paid/row.total_fees)*100).toFixed(1) : 0;
                var barColor = rowPct>=75?'#00a65a': rowPct>=50?'#f39c12':'#dd4b39';
                var color    = MCC.colors[i]||'#3c8dbc';
                tfees+=row.total_fees; tpaid+=row.total_paid; tbal+=row.total_balance;
                tbody +=
                    '<tr>'+
                    '<td><span class="mcc-dot" style="background:'+color+'"></span>'+escHtml(MCC.names[row.db_name]||row.db_name)+'</td>'+
                    '<td>'+(branchSessions[row.db_name]||'—')+'</td>'+
                    '<td class="text-right">'+row.total_fees_formatted+'</td>'+
                    '<td class="text-right"><strong class="text-success">'+row.total_paid_formatted+'</strong></td>'+
                    '<td class="text-right text-danger">'+row.total_balance_formatted+'</td>'+
                    '<td class="text-center">'+
                        '<div class="mcc-pct-wrap"><div class="mcc-pct-bar" style="width:'+rowPct+'%;background:'+barColor+'"></div></div>'+
                        '<small>'+rowPct+'%</small>'+
                    '</td>'+
                    '</tr>';
            });
            var fpct=tfees>0?((tpaid/tfees)*100).toFixed(1):0;
            $('#fees-tbody').html(tbody);
            $('#fees-tfoot').html(
                '<tr class="mcc-tfoot-row"><td colspan="2"><strong>Grand Total</strong></td>'+
                '<td class="text-right"><strong>'+MCC.currency+numFmt(tfees)+'</strong></td>'+
                '<td class="text-right"><strong class="text-success">'+MCC.currency+numFmt(tpaid)+'</strong></td>'+
                '<td class="text-right text-danger"><strong>'+MCC.currency+numFmt(tbal)+'</strong></td>'+
                '<td class="text-center"><strong>'+fpct+'%</strong></td></tr>'
            );
        }

        $('#fees-chart-skeleton').hide(); $('#fees-table-skeleton').hide();
        $('#fees-chart-box').show();      $('#fees-table-box').show();

        // Chart — must build AFTER box is visible so canvas has dimensions
        if (resp.chart) {
            var c=resp.chart;
            buildGroupedBar(
                document.getElementById('fees_chart').getContext('2d'),
                c.labels,
                [
                    { label:'Billed',    data:c.total_fees,    backgroundColor:'rgba(60,141,188,0.75)',  borderColor:'#3c8dbc', borderWidth:1 },
                    { label:'Collected', data:c.total_paid,    backgroundColor:'rgba(0,166,90,0.75)',    borderColor:'#00a65a', borderWidth:1 },
                    { label:'Balance',   data:c.total_balance, backgroundColor:'rgba(221,75,57,0.75)',   borderColor:'#dd4b39', borderWidth:1 }
                ]
            );
        }

    }).fail(function(){
        var errMsg = '<p class="mcc-load-err"><i class="fa fa-exclamation-triangle"></i> Failed to load fee data. <a href="javascript:location.reload()">Reload page</a></p>';
        $('#fees-chart-skeleton').html(errMsg);
        $('#fees-table-skeleton').html(errMsg);
    });
}

// ================================================================
// HR
// ================================================================
function loadHR() {
    if (loaded.hr) return;
    loaded.hr = true;

    $.getJSON(MCC.urls.hr).done(function(resp) {
        if (!resp || resp.status !== 'success') return;
        if (resp.month) $('#hr-month-label').text(resp.month + ' payroll · today\'s attendance');

        var tStaff=0, tPayroll=0, tPaid=0, tPresent=0, tAbsent=0;
        var tbody='';

        resp.rows.forEach(function(row,i) {
            tStaff   += row.total_staff;
            tPayroll += row.payroll_amount;
            tPaid    += row.payroll_paid;
            tPresent += row.staff_present;
            tAbsent  += row.staff_absent;
            var color = MCC.colors[i]||'#3c8dbc';
            tbody +=
                '<tr>'+
                '<td><span class="mcc-dot" style="background:'+color+'"></span>'+escHtml(row.name)+'</td>'+
                '<td class="text-center">'+numFmt(row.total_staff)+'</td>'+
                '<td class="text-center">'+row.payroll_generated+'</td>'+
                '<td class="text-center text-success">'+row.payroll_paid_cnt+'</td>'+
                '<td class="text-center text-danger">'+row.payroll_not_gen+'</td>'+
                '<td class="text-right">'+row.payroll_amount_fmt+'</td>'+
                '<td class="text-right"><strong class="text-success">'+row.payroll_paid_fmt+'</strong></td>'+
                '<td class="text-center">'+(row.staff_present||'—')+'</td>'+
                '<td class="text-center">'+(row.staff_absent||'—')+'</td>'+
                '</tr>';
        });
        $('#hr-tbody').html(tbody);
        $('#hr-tfoot').html(
            '<tr class="mcc-tfoot-row">'+
            '<td><strong>Grand Total</strong></td>'+
            '<td class="text-center"><strong>'+numFmt(tStaff)+'</strong></td>'+
            '<td colspan="3"></td>'+
            '<td class="text-right"><strong>'+MCC.currency+numFmt(tPayroll)+'</strong></td>'+
            '<td class="text-right"><strong class="text-success">'+MCC.currency+numFmt(tPaid)+'</strong></td>'+
            '<td class="text-center"><strong>'+tPresent+'</strong></td>'+
            '<td class="text-center"><strong>'+tAbsent+'</strong></td>'+
            '</tr>'
        );

        var attPct = (tPresent+tAbsent)>0 ? ((tPresent/(tPresent+tAbsent))*100).toFixed(1)+'%' : '—';
        $('#hr-summary-cards').html(
            mkStatCard('#605ca8','Total Staff',    numFmt(tStaff)) +
            mkStatCard('#3c8dbc','Net Payroll',    MCC.currency+numFmt(tPayroll)) +
            mkStatCard('#00a65a','Amount Paid',    MCC.currency+numFmt(tPaid)) +
            mkStatCard('#f39c12','Attendance Today', attPct)
        );

        $('#hr-chart-skeleton').hide(); $('#hr-table-skeleton').hide();
        $('#hr-chart-box').show();      $('#hr-table-box').show();

        // Chart — must build AFTER box is visible so canvas has dimensions
        if (resp.chart) {
            var c=resp.chart;
            buildGroupedBar(
                document.getElementById('hr_chart').getContext('2d'),
                c.labels,
                [
                    { label:'Net Payroll', data:c.payroll, backgroundColor:'rgba(60,141,188,0.75)',  borderColor:'#3c8dbc', borderWidth:1 },
                    { label:'Paid',        data:c.paid,    backgroundColor:'rgba(0,166,90,0.75)',    borderColor:'#00a65a', borderWidth:1 }
                ]
            );
        }

    }).fail(function(){
        var errMsg = '<p class="mcc-load-err"><i class="fa fa-exclamation-triangle"></i> Failed to load HR data. <a href="javascript:location.reload()">Reload page</a></p>';
        $('#hr-chart-skeleton').html(errMsg);
        $('#hr-table-skeleton').html(errMsg);
        loaded.hr = false;
    });
}

// ================================================================
// ASSETS
// ================================================================
function loadAssets() {
    if (loaded.assets) return;
    loaded.assets = true;

    $.getJSON(MCC.urls.assets).done(function(resp) {
        if (!resp || resp.status !== 'success') return;

        var tValue=0, tStock=0, tItems=0, panelsHtml='';

        resp.rows.forEach(function(row,i) {
            tValue += row.total_value;
            tStock += row.total_stock;
            tItems += row.total_items;
            var color = MCC.colors[i]||'#3c8dbc';

            var catRows='';
            if (row.categories && row.categories.length) {
                row.categories.forEach(function(cat) {
                    catRows += '<tr>'+
                        '<td>'+escHtml(cat.name)+'</td>'+
                        '<td class="text-center">'+cat.item_types+'</td>'+
                        '<td class="text-center">'+numFmt(cat.total_stock)+'</td>'+
                        '<td class="text-right"><strong>'+cat.total_value_fmt+'</strong></td>'+
                        '</tr>';
                });
            } else {
                catRows = '<tr><td colspan="4" class="text-center text-muted" style="padding:10px">No inventory data</td></tr>';
            }

            panelsHtml +=
                '<div class="box" style="border-radius:4px; border-top:3px solid '+color+'; margin-bottom:12px">'+
                    '<div class="box-header" style="padding:10px 15px; background:#fafafa; border-bottom:1px solid #eee">'+
                        '<span class="mcc-dot-lg" style="background:'+color+'"></span>'+
                        '<strong style="font-size:14px">'+escHtml(row.name)+'</strong>'+
                        '<span class="pull-right" style="font-size:12px; color:#666">'+
                            '<i class="fa fa-tag"></i> '+row.total_items+' types &nbsp;'+
                            '<i class="fa fa-archive"></i> '+numFmt(row.total_stock)+' units &nbsp;'+
                            '<strong style="color:#e67e22"><i class="fa fa-inr"></i> '+row.total_value_fmt+'</strong>'+
                        '</span>'+
                    '</div>'+
                    '<div class="table-responsive">'+
                        '<table class="table table-condensed table-bordered mcc-table mcc-table-sm">'+
                            '<thead><tr><th>Category</th><th class="text-center">Items</th><th class="text-center">Units</th><th class="text-right">Value</th></tr></thead>'+
                            '<tbody>'+catRows+'</tbody>'+
                        '</table>'+
                    '</div>'+
                '</div>';
        });

        $('#kpi-asset-value').text(MCC.currency + numFmt(tValue));
        $('#assets-summary-cards').html(
            mkStatCard('#f39c12','Total Asset Value',  MCC.currency+numFmt(tValue)) +
            mkStatCard('#3c8dbc','Total Stock Units',  numFmt(tStock)) +
            mkStatCard('#605ca8','Distinct Item Types',tItems)
        );
        $('#assets-institution-panels').html(panelsHtml);

        $('#assets-chart-skeleton').hide(); $('#assets-table-skeleton').hide();
        $('#assets-chart-box').show();      $('#assets-table-box').show();

        // Chart — must build AFTER box is visible so canvas has dimensions
        if (resp.chart) {
            var c=resp.chart;
            new ChartV2(document.getElementById('assets_chart').getContext('2d'), {
                type: 'horizontalBar',
                data: {
                    labels: c.labels,
                    datasets:[{ label:'Asset Value', data:c.values, backgroundColor:MCC.colors.slice(0,c.labels.length), borderWidth:0 }]
                },
                options: {
                    responsive:true, maintainAspectRatio:false,
                    legend:{ display:false },
                    scales:{ xAxes:[{ ticks:{ beginAtZero:true, callback:function(v){
                        if(v>=10000000) return (v/10000000).toFixed(1)+'Cr';
                        if(v>=100000)   return (v/100000).toFixed(1)+'L';
                        if(v>=1000)     return (v/1000).toFixed(0)+'K';
                        return v;
                    } } }] },
                    tooltips:{ callbacks:{ label:function(item){ return MCC.currency+Number(item.xLabel).toLocaleString('en-IN'); } } }
                }
            });
        }

    }).fail(function(){
        var errMsg = '<p class="mcc-load-err"><i class="fa fa-exclamation-triangle"></i> Failed to load asset data. <a href="javascript:location.reload()">Reload page</a></p>';
        $('#assets-chart-skeleton').html(errMsg);
        $('#assets-table-skeleton').html(errMsg);
        loaded.assets = false;
    });
}

// ================================================================
// ACADEMICS
// ================================================================
function loadAcademics() {
    if (loaded.academics) return;
    loaded.academics = true;

    $.getJSON(MCC.urls.academics).done(function(resp) {
        if (!resp || resp.status !== 'success') return;

        var libTbody='', admTbody='';
        var tBooks=0, tMembers=0, tIssued=0, tOff=0, tOn=0, tAlumni=0;

        resp.rows.forEach(function(row,i) {
            tBooks   += row.total_books;
            tMembers += row.library_members;
            tIssued  += row.book_issued;
            tOff     += row.offline_admission;
            tOn      += row.online_admission;
            tAlumni  += row.total_alumni;
            var color = MCC.colors[i]||'#3c8dbc';
            var dot = '<span class="mcc-dot" style="background:'+color+'"></span>';

            libTbody += '<tr>'+
                '<td>'+dot+escHtml(row.name)+'</td>'+
                '<td class="text-right">'+numFmt(row.total_books)+'</td>'+
                '<td class="text-right">'+numFmt(row.library_members)+'</td>'+
                '<td class="text-right">'+numFmt(row.book_issued)+'</td>'+
                '</tr>';

            admTbody += '<tr>'+
                '<td>'+dot+escHtml(row.name)+'</td>'+
                '<td class="text-right">'+row.offline_admission+'</td>'+
                '<td class="text-right">'+row.online_admission+'</td>'+
                '<td class="text-right">'+numFmt(row.total_alumni)+'</td>'+
                '</tr>';
        });

        libTbody += '<tr class="mcc-tfoot-row"><td><strong>Total</strong></td>'+
            '<td class="text-right"><strong>'+numFmt(tBooks)+'</strong></td>'+
            '<td class="text-right"><strong>'+numFmt(tMembers)+'</strong></td>'+
            '<td class="text-right"><strong>'+numFmt(tIssued)+'</strong></td></tr>';
        admTbody += '<tr class="mcc-tfoot-row"><td><strong>Total</strong></td>'+
            '<td class="text-right"><strong>'+tOff+'</strong></td>'+
            '<td class="text-right"><strong>'+tOn+'</strong></td>'+
            '<td class="text-right"><strong>'+numFmt(tAlumni)+'</strong></td></tr>';

        $('#lib-tbody').html(libTbody);
        $('#adm-tbody').html(admTbody);
        $('#kpi-total-books').text(numFmt(tBooks));
        $('#academics-skeleton').hide();
        $('#academics-content').show();

    }).fail(function(){
        $('#academics-skeleton').html('<p class="mcc-load-err"><i class="fa fa-exclamation-triangle"></i> Failed to load academics data. <a href="javascript:location.reload()">Reload page</a></p>');
        loaded.academics = false;
    });
}

// ================================================================
// ATTENDANCE
// ================================================================
function loadAttendance() {
    if (loaded.attendance) return;
    loaded.attendance = true;

    $.getJSON(MCC.urls.attendance).done(function(resp) {
        if (!resp || resp.status !== 'success') return;

        if (resp.date) {
            var d = new Date(resp.date);
            $('#att-date-label').text(d.toLocaleDateString('en-IN', { day:'numeric', month:'short', year:'numeric' }));
        }

        var tStuPresent=0, tBoys=0, tGirls=0, tStuAbsent=0, tStuTotal=0;
        var tStfPresent=0, tStfAbsent=0, tStfTotal=0;
        var stuTbody='', stfTbody='';

        function attColor(present, total) {
            if (!total) return '';
            var pct = (present / total) * 100;
            if (pct >= 90) return 'style="color:#00a65a; font-weight:700"';
            if (pct >= 75) return 'style="color:#f39c12; font-weight:700"';
            return 'style="color:#dd4b39; font-weight:700"';
        }

        resp.rows.forEach(function(row, i) {
            tStuPresent += row.student_present;
            tBoys       += row.student_boys_present;
            tGirls      += row.student_girls_present;
            tStuAbsent  += row.student_absent;
            tStuTotal   += row.student_total;
            tStfPresent += row.staff_present;
            tStfAbsent  += row.staff_absent;
            tStfTotal   += row.staff_total;
            var color = MCC.colors[i] || '#d81b60';
            var dot   = '<span class="mcc-dot" style="background:'+color+'"></span>';

            stuTbody += '<tr>'+
                '<td>'+dot+escHtml(MCC.names[row.db_name]||row.db_name)+'</td>'+
                '<td class="text-right" '+attColor(row.student_boys_present, row.student_total)+'>'+numFmt(row.student_boys_present)+'</td>'+
                '<td class="text-right" '+attColor(row.student_girls_present, row.student_total)+'>'+numFmt(row.student_girls_present)+'</td>'+
                '<td class="text-right text-danger">'+numFmt(row.student_absent)+'</td>'+
                '<td class="text-right">'+numFmt(row.student_total)+'</td>'+
                '</tr>';

            stfTbody += '<tr>'+
                '<td>'+dot+escHtml(MCC.names[row.db_name]||row.db_name)+'</td>'+
                '<td class="text-right" '+attColor(row.staff_present, row.staff_total)+'>'+numFmt(row.staff_present)+'</td>'+
                '<td class="text-right text-danger">'+numFmt(row.staff_absent)+'</td>'+
                '<td class="text-right">'+numFmt(row.staff_total)+'</td>'+
                '</tr>';
        });

        // Totals
        stuTbody += '<tr class="mcc-tfoot-row">'+
            '<td><strong>Grand Total</strong></td>'+
            '<td class="text-right"><strong>'+numFmt(tBoys)+'</strong></td>'+
            '<td class="text-right"><strong>'+numFmt(tGirls)+'</strong></td>'+
            '<td class="text-right text-danger"><strong>'+numFmt(tStuAbsent)+'</strong></td>'+
            '<td class="text-right"><strong>'+numFmt(tStuTotal)+'</strong></td>'+
            '</tr>';
        stfTbody += '<tr class="mcc-tfoot-row">'+
            '<td><strong>Grand Total</strong></td>'+
            '<td class="text-right"><strong>'+numFmt(tStfPresent)+'</strong></td>'+
            '<td class="text-right text-danger"><strong>'+numFmt(tStfAbsent)+'</strong></td>'+
            '<td class="text-right"><strong>'+numFmt(tStfTotal)+'</strong></td>'+
            '</tr>';

        $('#att-stu-tbody, #att-stu-tfoot').html('');
        $('#att-stu-tbody').html(stuTbody);
        $('#att-stf-tbody, #att-stf-tfoot').html('');
        $('#att-stf-tbody').html(stfTbody);

        // Summary cards
        var stuPct = tStuTotal > 0 ? ((tStuPresent/tStuTotal)*100).toFixed(1)+'%' : 'No data';
        var stfPct = tStfTotal > 0 ? ((tStfPresent/tStfTotal)*100).toFixed(1)+'%' : 'No data';
        $('#att-summary-cards').html(
            mkStatCard('#d81b60', 'Students Present', stuPct) +
            mkStatCard('#605ca8', 'Staff Present',    stfPct)
        );

        $('#att-skeleton').hide();
        $('#att-content').show();

    }).fail(function(){
        $('#att-skeleton').html('<p class="mcc-load-err"><i class="fa fa-exclamation-triangle"></i> Failed to load attendance data. <a href="javascript:location.reload()">Reload page</a></p>');
        loaded.attendance = false;
    });
}

// ---- Stat card builder ----
function mkStatCard(color, label, value) {
    return '<div class="mcc-stat-card" style="border-left-color:'+color+'">'+
        '<div class="lbl">'+escHtml(label)+'</div>'+
        '<div class="val">'+value+'</div>'+
        '</div>';
}

// ---- Sticky nav active state ----
function updateNavActive() {
    var sections = ['fees','hr','assets','academics','attendance'], current='fees';
    sections.forEach(function(s){
        var el=document.getElementById('section-'+s);
        if(el && el.getBoundingClientRect().top <= 120) current=s;
    });
    $('#mcc-nav li').removeClass('active');
    $('#nav-'+current).addClass('active');
}

// ---- Smooth scroll ----
$(document).on('click','#mcc-nav a[data-section]',function(e){
    e.preventDefault();
    var $t = $($(this).attr('href'));
    if($t.length) $('html,body').animate({ scrollTop: $t.offset().top - 105 }, 350);
});

// ---- Boot ----
$(document).ready(function(){
    loadFees(); // fees always loads first

    if ('IntersectionObserver' in window) {
        var obs = new IntersectionObserver(function(entries){
            entries.forEach(function(e){
                if(!e.isIntersecting) return;
                var s = e.target.getAttribute('data-section');
                if(s==='hr')        loadHR();
                else if(s==='assets')    loadAssets();
                else if(s==='academics')  loadAcademics();
                else if(s==='attendance') loadAttendance();
            });
        }, { rootMargin:'0px 0px -80px 0px', threshold:0.05 });

        ['hr','assets','academics','attendance'].forEach(function(s){
            var el=document.getElementById('section-'+s);
            if(el) obs.observe(el);
        });
    } else {
        loadHR(); loadAssets(); loadAcademics(); loadAttendance();
    }

    $(window).on('scroll', updateNavActive);
});
</script>
