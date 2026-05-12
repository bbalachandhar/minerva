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
<link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/multi_branch.css">

<div class="content-wrapper mcc-wrapper">
<section class="content">

<!-- INSTITUTION CARDS -->
<div class="mcc-inst-strip">
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
<div class="mcc-inst-card" style="border-top:3px solid <?php echo $color; ?>">
    <div class="mcc-inst-top">
        <div class="mcc-inst-badge" style="background:<?php echo $color; ?>"><?php echo $abbr; ?></div>
        <div class="mcc-inst-meta">
            <a href="<?php echo $card_url; ?>" target="_blank" class="mcc-inst-name"><?php echo htmlspecialchars($disp_name); ?></a>
            <span class="mcc-inst-session">
                <?php if ($is_home): ?><span class="mcc-home-pill">Home</span><?php endif; ?>
                <?php echo htmlspecialchars($bi->session); ?>
            </span>
        </div>
    </div>
    <div class="mcc-inst-stats">
        <div class="mcc-inst-stat">
            <i class="fa fa-users" style="color:<?php echo $color; ?>"></i>
            <span class="mcc-stat-num"><?php echo number_format($students); ?></span>
            <span class="mcc-stat-lbl">Students</span>
            <span class="mcc-gender-line">
                <span class="mcc-gender-m" title="Male">&#9794; <?php echo number_format($male_students); ?></span>
                <span class="mcc-gender-f" title="Female">&#9792; <?php echo number_format($female_students); ?></span>
            </span>
        </div>
        <div class="mcc-inst-stat">
            <i class="fa fa-id-badge" style="color:<?php echo $color; ?>"></i>
            <span class="mcc-stat-num"><?php echo number_format($staff); ?></span>
            <span class="mcc-stat-lbl">Staff</span>
            <span class="mcc-gender-line">
                <span class="mcc-gender-m" title="Male">&#9794; <?php echo number_format($male_staff); ?></span>
                <span class="mcc-gender-f" title="Female">&#9792; <?php echo number_format($female_staff); ?></span>
            </span>
        </div>
        <div class="mcc-inst-stat">
            <i class="fa fa-money" style="color:<?php echo $color; ?>"></i>
            <span class="mcc-stat-num mcc-fees-collected" data-db="<?php echo $db_name; ?>">
                <span class="sk-inline sk-w60"></span>
            </span>
            <span class="mcc-stat-lbl">Collected</span>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- KPI TILES -->
<div class="mcc-kpi-strip">
    <div class="mcc-kpi-tile" style="--kpi-color:#3c8dbc">
        <div class="mcc-kpi-icon"><i class="fa fa-graduation-cap"></i></div>
        <div class="mcc-kpi-body">
            <div class="mcc-kpi-val"><?php echo number_format($grand_students); ?></div>
            <div class="mcc-kpi-label">Total Students</div>
            <div class="mcc-kpi-gender">
                <span class="mcc-gender-m">&#9794; <?php echo number_format($grand_male_students); ?></span>
                &nbsp;
                <span class="mcc-gender-f">&#9792; <?php echo number_format($grand_female_students); ?></span>
            </div>
        </div>
    </div>
    <div class="mcc-kpi-tile" style="--kpi-color:#605ca8">
        <div class="mcc-kpi-icon"><i class="fa fa-id-badge"></i></div>
        <div class="mcc-kpi-body">
            <div class="mcc-kpi-val"><?php echo number_format($grand_staff); ?></div>
            <div class="mcc-kpi-label">Total Staff</div>
            <div class="mcc-kpi-gender">
                <span class="mcc-gender-m">&#9794; <?php echo number_format($grand_male_staff); ?></span>
                &nbsp;
                <span class="mcc-gender-f">&#9792; <?php echo number_format($grand_female_staff); ?></span>
            </div>
        </div>
    </div>
    <div class="mcc-kpi-tile" style="--kpi-color:#00a65a">
        <div class="mcc-kpi-icon"><i class="fa fa-inr"></i></div>
        <div class="mcc-kpi-body">
            <div class="mcc-kpi-val" id="kpi-fees-collected"><span class="sk-inline sk-w80"></span></div>
            <div class="mcc-kpi-label">Fees Collected</div>
        </div>
    </div>
    <div class="mcc-kpi-tile" style="--kpi-color:#f39c12">
        <div class="mcc-kpi-icon"><i class="fa fa-cubes"></i></div>
        <div class="mcc-kpi-body">
            <div class="mcc-kpi-val" id="kpi-asset-value"><span class="sk-inline sk-w80"></span></div>
            <div class="mcc-kpi-label">Asset Value</div>
        </div>
    </div>
    <div class="mcc-kpi-tile" style="--kpi-color:#00c0ef">
        <div class="mcc-kpi-icon"><i class="fa fa-book"></i></div>
        <div class="mcc-kpi-body">
            <div class="mcc-kpi-val" id="kpi-total-books"><span class="sk-inline sk-w60"></span></div>
            <div class="mcc-kpi-label">Library Books</div>
        </div>
    </div>
</div>

<!-- STICKY NAV -->
<div class="mcc-section-nav" id="mcc-section-nav">
    <a href="#section-fees"      class="mcc-nav-link active" data-section="fees"><i class="fa fa-money"></i> Fees</a>
    <a href="#section-hr"        class="mcc-nav-link" data-section="hr"><i class="fa fa-users"></i> HR &amp; Payroll</a>
    <a href="#section-assets"    class="mcc-nav-link" data-section="assets"><i class="fa fa-cubes"></i> Assets</a>
    <a href="#section-academics" class="mcc-nav-link" data-section="academics"><i class="fa fa-graduation-cap"></i> Academics</a>
    <span class="mcc-nav-spacer"></span>
    <button class="btn btn-default btn-xs mcc-print-btn" onclick="window.print()"><i class="fa fa-print"></i></button>
</div>

<!-- SECTION: FEES -->
<div class="mcc-section" id="section-fees" data-section="fees">
    <div class="mcc-section-header" style="background:#3c8dbc">
        <i class="fa fa-money"></i>
        <span>Fees Overview</span>
        <span class="mcc-section-sub">Current session — billed vs collected across all institutions</span>
    </div>
    <div class="mcc-section-body">
        <div class="row">
            <div class="col-md-8">
                <div class="mcc-chart-box" id="fees-chart-skeleton"><div class="sk-chart-placeholder"></div></div>
                <div class="mcc-chart-box" id="fees-chart-box" style="display:none;position:relative;height:300px">
                    <canvas id="fees_chart"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mcc-stat-card-group" id="fees-summary-cards">
                    <div class="sk-card-block"></div><div class="sk-card-block"></div><div class="sk-card-block"></div>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top:18px">
            <div class="col-md-12">
                <div class="mcc-data-table-wrap" id="fees-table-skeleton">
                    <div class="sk-table-row"></div><div class="sk-table-row sk-alt"></div>
                    <div class="sk-table-row"></div><div class="sk-table-row sk-alt"></div>
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

<!-- SECTION: HR -->
<div class="mcc-section" id="section-hr" data-section="hr">
    <div class="mcc-section-header" style="background:#00a65a">
        <i class="fa fa-users"></i>
        <span>HR &amp; Payroll</span>
        <span class="mcc-section-sub" id="hr-month-label">Last month payroll · today's attendance</span>
    </div>
    <div class="mcc-section-body">
        <div class="row">
            <div class="col-md-8">
                <div class="mcc-chart-box" id="hr-chart-skeleton"><div class="sk-chart-placeholder"></div></div>
                <div class="mcc-chart-box" id="hr-chart-box" style="display:none;position:relative;height:300px">
                    <canvas id="hr_chart"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mcc-stat-card-group" id="hr-summary-cards">
                    <div class="sk-card-block"></div><div class="sk-card-block"></div><div class="sk-card-block"></div>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top:18px">
            <div class="col-md-12">
                <div class="mcc-data-table-wrap" id="hr-table-skeleton">
                    <div class="sk-table-row"></div><div class="sk-table-row sk-alt"></div>
                    <div class="sk-table-row"></div><div class="sk-table-row sk-alt"></div>
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

<!-- SECTION: ASSETS -->
<div class="mcc-section" id="section-assets" data-section="assets">
    <div class="mcc-section-header" style="background:#f39c12">
        <i class="fa fa-cubes"></i>
        <span>Asset Inventory</span>
        <span class="mcc-section-sub">Stock value at purchase cost across all institutions</span>
    </div>
    <div class="mcc-section-body">
        <div class="row">
            <div class="col-md-7">
                <div class="mcc-chart-box" id="assets-chart-skeleton"><div class="sk-chart-placeholder"></div></div>
                <div class="mcc-chart-box" id="assets-chart-box" style="display:none;position:relative;height:280px">
                    <canvas id="assets_chart"></canvas>
                </div>
            </div>
            <div class="col-md-5">
                <div class="mcc-stat-card-group" id="assets-summary-cards">
                    <div class="sk-card-block"></div><div class="sk-card-block"></div><div class="sk-card-block"></div>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top:18px">
            <div class="col-md-12">
                <div class="mcc-data-table-wrap" id="assets-table-skeleton">
                    <div class="sk-table-row"></div><div class="sk-table-row sk-alt"></div>
                    <div class="sk-table-row"></div><div class="sk-table-row sk-alt"></div>
                </div>
                <div id="assets-table-box" style="display:none">
                    <div id="assets-institution-panels"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION: ACADEMICS -->
<div class="mcc-section" id="section-academics" data-section="academics">
    <div class="mcc-section-header" style="background:#605ca8">
        <i class="fa fa-graduation-cap"></i>
        <span>Academics &amp; Library</span>
        <span class="mcc-section-sub">Admissions, library activity and alumni</span>
    </div>
    <div class="mcc-section-body">
        <div class="mcc-data-table-wrap" id="academics-skeleton">
            <div class="sk-table-row"></div><div class="sk-table-row sk-alt"></div>
            <div class="sk-table-row"></div><div class="sk-table-row sk-alt"></div>
        </div>
        <div id="academics-content" style="display:none">
            <div class="row">
                <div class="col-md-6">
                    <div class="mcc-sub-section-title"><i class="fa fa-book"></i> Library</div>
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
                    <div class="mcc-sub-section-title"><i class="fa fa-pencil-square-o"></i> Admissions &amp; Alumni</div>
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

</section>
</div>

<script src="<?php echo base_url(); ?>backend/js/Chart.min.js"></script>
<script>
var MCC = {
    urls: {
        fees:      '<?php echo site_url("admin/multibranch/branch/fees_overview_async"); ?>',
        hr:        '<?php echo site_url("admin/multibranch/branch/hr_async"); ?>',
        assets:    '<?php echo site_url("admin/multibranch/branch/assets_async"); ?>',
        academics: '<?php echo site_url("admin/multibranch/branch/academics_async"); ?>'
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

var loaded = { fees: false, hr: false, assets: false, academics: false };

// ---- Helpers ----
function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function numFmt(v) { return Number(v).toLocaleString('en-IN'); }

// ---- Chart.js v2 defaults ----
Chart.defaults.global.defaultFontFamily = "'Helvetica Neue',Helvetica,Arial,sans-serif";
Chart.defaults.global.defaultFontSize   = 11;

function buildGroupedBar(ctx, labels, datasets, opts) {
    opts = opts || {};
    return new Chart(ctx, {
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

        // Chart
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

        $('#fees-chart-skeleton').hide(); $('#fees-table-skeleton').hide();
        $('#fees-chart-box').show();      $('#fees-table-box').show();

    }).fail(function(){
        $('#fees-chart-skeleton').html('<p class="mcc-load-err">Failed to load fee data.</p>');
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

        $('#hr-chart-skeleton').hide(); $('#hr-table-skeleton').hide();
        $('#hr-chart-box').show();      $('#hr-table-box').show();

    }).fail(function(){
        $('#hr-chart-skeleton').html('<p class="mcc-load-err">Failed to load HR data.</p>');
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
                '<div class="mcc-asset-panel" style="border-top:3px solid '+color+'">'+
                    '<div class="mcc-asset-panel-hdr">'+
                        '<span class="mcc-dot-lg" style="background:'+color+'"></span>'+
                        '<strong>'+escHtml(row.name)+'</strong>'+
                        '<span class="mcc-akpi-row">'+
                            '<span class="mcc-akpi"><i class="fa fa-tag"></i> '+row.total_items+' item types</span>'+
                            '<span class="mcc-akpi"><i class="fa fa-archive"></i> '+numFmt(row.total_stock)+' units</span>'+
                            '<span class="mcc-akpi mcc-akpi-val"><i class="fa fa-inr"></i> '+row.total_value_fmt+'</span>'+
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

        if (resp.chart) {
            var c=resp.chart;
            new Chart(document.getElementById('assets_chart').getContext('2d'), {
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

        $('#assets-chart-skeleton').hide(); $('#assets-table-skeleton').hide();
        $('#assets-chart-box').show();      $('#assets-table-box').show();

    }).fail(function(){
        $('#assets-chart-skeleton').html('<p class="mcc-load-err">Failed to load asset data.</p>');
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
        $('#academics-skeleton').html('<p class="mcc-load-err">Failed to load academics data.</p>');
    });
}

// ---- Stat card builder ----
function mkStatCard(color, label, value) {
    return '<div class="mcc-stat-card" style="border-left-color:'+color+'">'+
        '<div class="mcc-sc-lbl">'+escHtml(label)+'</div>'+
        '<div class="mcc-sc-val">'+value+'</div>'+
        '</div>';
}

// ---- Sticky nav active state ----
function updateNavActive() {
    var sections = ['fees','hr','assets','academics'], current='fees';
    sections.forEach(function(s){
        var el=document.getElementById('section-'+s);
        if(el && el.getBoundingClientRect().top <= 120) current=s;
    });
    $('.mcc-nav-link').removeClass('active');
    $('.mcc-nav-link[data-section="'+current+'"]').addClass('active');
}

// ---- Smooth scroll ----
$(document).on('click','.mcc-nav-link',function(e){
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
                else if(s==='academics') loadAcademics();
            });
        }, { rootMargin:'0px 0px -80px 0px', threshold:0.05 });

        ['hr','assets','academics'].forEach(function(s){
            var el=document.getElementById('section-'+s);
            if(el) obs.observe(el);
        });
    } else {
        loadHR(); loadAssets(); loadAcademics();
    }

    $(window).on('scroll', updateNavActive);
});
</script>
