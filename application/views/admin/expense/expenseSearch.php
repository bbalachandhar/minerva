<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
<style>
.fc-page .fc-panel { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,.06); margin-bottom:20px; overflow:visible; }
.fc-panel-header { background:linear-gradient(135deg,#5b73e8 0%,#7c5ce7 100%); color:#fff; padding:14px 20px; border-radius:10px 10px 0 0; display:flex; align-items:center; justify-content:space-between; }
.fc-panel-header h3 { margin:0; font-size:16px; font-weight:600; }
.fc-panel-header h3 i { margin-right:8px; }
.fc-panel-body { padding:20px; }
.fc-label { font-weight:600; font-size:13px; color:#495057; margin-bottom:6px; display:block; }
.fc-label .req { color:#e74c3c; }
.fc-select, .fc-input {
    width:100%; background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px;
    padding:9px 14px; font-size:14px; color:#333; transition:border-color .2s;
    height:40px; box-sizing:border-box;
    -webkit-appearance:none; appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 12px center; padding-right:32px;
}
.fc-input { background-image:none; padding-right:14px; }
.fc-select:focus, .fc-input:focus { border-color:#5b73e8; outline:none; background:#fff; }
.fc-divider { display:flex; align-items:center; gap:12px; margin:0 -20px; padding:0 20px; }
.fc-divider-line { flex:1; height:1px; background:#e9ecef; }
.fc-divider-text { font-size:12px; font-weight:600; color:#adb5bd; text-transform:uppercase; letter-spacing:1px; }
.btn-fc-search {
    background:linear-gradient(135deg,#5b73e8,#7c5ce7); color:#fff; border:none;
    border-radius:8px; padding:9px 24px; font-size:14px; font-weight:600; cursor:pointer;
    transition:all .2s; display:inline-flex; align-items:center; gap:6px; height:40px;
}
.btn-fc-search:hover { opacity:.9; color:#fff; transform:translateY(-1px); }
.btn-fc-search:disabled { opacity:.6; }

/* Results table */
.fc-results { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
.fc-results-header { padding:16px 20px; border-bottom:1px solid #eee; display:flex; align-items:center; justify-content:space-between; }
.fc-results-header h4 { margin:0; font-size:15px; font-weight:600; color:#2c3e50; }
.fc-results-header h4 i { margin-right:6px; color:#5b73e8; }

.expense-list thead th {
    background:#f8f9fb !important; padding:11px 14px !important;
    font-size:11px !important; font-weight:700 !important; text-transform:uppercase !important;
    letter-spacing:.4px !important; color:#8492a6 !important; border-bottom:2px solid #eef0f3 !important;
}
.expense-list tbody td {
    padding:12px 14px !important; font-size:13px !important; color:#333 !important;
    border-bottom:1px solid #f0f0f0 !important; vertical-align:middle !important;
}
.expense-list tbody tr:hover { background:#f8f9ff !important; }

/* Date fields injected into #date_result */
#date_result .fc-label { font-weight:600; font-size:13px; color:#495057; margin-bottom:6px; display:block; }
#date_result .form-control { width:100%; background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px; padding:9px 14px; font-size:14px; color:#333; transition:border-color .2s; height:40px; box-sizing:border-box; }
#date_result .form-control:focus { border-color:#5b73e8; outline:none; background:#fff; }

@media print {
    .no-print, .no-print * { display:none !important; }
}
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-usd"></i> <?php echo $this->lang->line('expense'); ?></h1>
    </section>

    <section class="content">
        <div class="fc-page">

            <!-- Search Panel -->
            <div class="fc-panel">
                <div class="fc-panel-header">
                    <h3><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                </div>
                <div class="fc-panel-body">
                    <form role="form" id="expenseform" action="<?php echo site_url('admin/expense/search') ?>" method="post">
                        <?php echo $this->customlib->getCSRF(); ?>

                        <!-- Row 1: Filter by type + date -->
                        <div class="row">
                            <div class="col-md-3">
                                <label class="fc-label"><?php echo $this->lang->line('search_type'); ?> <span class="req">*</span></label>
                                <select class="fc-select" name="search_type" id="search_type" onchange="showdate(this.value)">
                                    <?php foreach ($searchlist as $key => $search): ?>
                                    <option value="<?php echo $key; ?>" <?php if (isset($search_type) && $search_type == $key) echo "selected"; ?>><?php echo $search; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="text-danger" id="error_search_type" style="font-size:12px;"></span>
                            </div>
                            <div id="date_result"></div>
                            <div class="col-md-2">
                                <label class="fc-label">&nbsp;</label>
                                <button type="submit" name="search" value="search_filter" class="btn-fc-search" style="width:100%;" data-loading-text="<?php echo $this->lang->line('please_wait'); ?>">
                                    <i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div style="margin:16px 0;">
                            <div class="fc-divider">
                                <div class="fc-divider-line"></div>
                                <span class="fc-divider-text">or search by keyword</span>
                                <div class="fc-divider-line"></div>
                            </div>
                        </div>

                        <!-- Row 2: Text search -->
                        <div class="row">
                            <div class="col-md-10">
                                <label class="fc-label"><?php echo $this->lang->line('search'); ?> <span class="req">*</span></label>
                                <input autofocus="" type="text" value="<?php echo set_value('search_text', ''); ?>" name="search_text" id="search_text" class="fc-input" placeholder="<?php echo $this->lang->line('search_by_expense'); ?>">
                                <span class="text-danger" id="error_search_text" style="font-size:12px;"></span>
                            </div>
                            <div class="col-md-2">
                                <label class="fc-label">&nbsp;</label>
                                <button type="submit" name="search" value="search_full" class="btn-fc-search" style="width:100%;background:linear-gradient(135deg,#3498db,#2980b9);" data-loading-text="<?php echo $this->lang->line('please_wait'); ?>">
                                    <i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results -->
            <div class="fc-results">
                <div class="fc-results-header">
                    <h4><i class="fa fa-money" style="margin-right:6px;color:#5b73e8;"></i> <?php echo $this->lang->line('expense_list'); ?></h4>
                </div>
                <div style="padding:0 12px 12px;">
                    <div class="download_label"><?php echo $this->lang->line('expense_list'); ?></div>
                    <table class="table table-hover expense-list" data-export-title="<?php echo $this->lang->line('expense_list'); ?>">
                        <thead>
                            <tr>
                                <th><?php echo $this->lang->line('name'); ?></th>
                                <th><?php echo $this->lang->line('invoice_number'); ?></th>
                                <th><?php echo $this->lang->line('expense_head'); ?></th>
                                <th><?php echo $this->lang->line('date'); ?></th>
                                <th class="text-right"><?php echo $this->lang->line('amount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>
</div>

<script type="text/javascript">
var base_url = '<?php echo base_url() ?>';

function printDiv(elem) {
    Popup(jQuery(elem).html());
}

function Popup(data)
{
    var frame1 = $('<iframe />');
    frame1[0].name = "frame1";
    frame1.css({"position": "absolute", "top": "-1000000px"});
    $("body").append(frame1);
    var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
    frameDoc.document.open();
    frameDoc.document.write('<html>');
    frameDoc.document.write('<head>');
    frameDoc.document.write('<title></title>');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/bootstrap/css/bootstrap.min.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/font-awesome.min.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/ionicons.min.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/AdminLTE.min.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/skins/_all-skins.min.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/iCheck/flat/blue.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/morris/morris.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/jvectormap/jquery-jvectormap-1.2.2.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/datepicker/datepicker3.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/daterangepicker/daterangepicker-bs3.css">');
    frameDoc.document.write('</head>');
    frameDoc.document.write('<body>');
    frameDoc.document.write(data);
    frameDoc.document.write('</body>');
    frameDoc.document.write('</html>');
    frameDoc.document.close();
    setTimeout(function () {
        window.frames["frame1"].focus();
        window.frames["frame1"].print();
        frame1.remove();
    }, 500);
    return true;
}
</script>

<script>
$(document).ready(function() {
    emptyDatatable('expense-list','data');
});
</script>

<script type="text/javascript">
$(document).ready(function(){
    $(document).on('submit','#expenseform',function(e){
        e.preventDefault();
        var $this = $(this).find("button[type=submit]:focus");
        var form = $(this);
        var url = form.attr('action');
        var form_data = form.serializeArray();
        form_data.push({name: 'button_type', value: $this.attr('value')});
        $.ajax({
            url: url,
            type: "POST",
            dataType:'JSON',
            data: form_data,
            beforeSend: function () {
                $('[id^=error]').html("");
                $this.button('loading');
                resetFields($this.attr('value'));
            },
            success: function(response) {
                if(!response.status){
                    $.each(response.error, function(key, value) {
                        $('#error_' + key).html(value);
                    });
                }else{
                    initDatatable('expense-list','admin/expense/getsearchexpenselist',response.params);
                }
            },
            error: function() {
                $this.button('reset');
            },
            complete: function() {
                $this.button('reset');
            }
        });
    });
});

function resetFields(search_type){
    if(search_type == "search_full"){
        $('#search_type').val('');
    }else if (search_type == "search_filter") {
        $('#search_text').val("");
    }
}
</script>
