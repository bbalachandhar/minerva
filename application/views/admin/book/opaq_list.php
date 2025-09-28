<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-book"></i> <?php echo $this->lang->line('opaq'); ?></h1>
    </section>

    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary" id="bklist">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('opaq'); ?></h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <form role="form" action="<?php echo site_url('admin/opaq/getopaqlist') ?>" method="post" class="form-horizontal" id="opaq_search_form">
                                <div class="box-body">
                                    <div class="row"> <!-- First Row -->
                                        <div class="col-sm-6 col-md-3">
                                            <div class="form-group" style="margin-right: 15px; margin-bottom: 15px;">
                                                <label><?php echo $this->lang->line('book_title'); ?></label>
                                                <select class="form-control" name="book_title" id="book_title">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($book_titles as $title_item) { ?>
                                                        <option value="<?php echo $title_item['book_title']; ?>"><?php echo $title_item['book_title']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <div class="form-group" style="margin-right: 15px; margin-bottom: 15px;">
                                                <label><?php echo $this->lang->line('author'); ?></label>
                                                <select class="form-control" name="author" id="author">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($authors as $author_item) { ?>
                                                        <option value="<?php echo $author_item['author']; ?>"><?php echo $author_item['author']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <div class="form-group" style="margin-right: 15px; margin-bottom: 15px;">
                                                <label><?php echo $this->lang->line('barcode'); ?></label>
                                                <input type="text" name="barcode" id="barcode" class="form-control">
                                            </div>
                                        </div>
                                    </div> <!-- End First Row -->
                                    <div class="row"> <!-- Second Row -->
                                        <div class="col-sm-6 col-md-3">
                                            <div class="form-group" style="margin-right: 15px; margin-bottom: 15px;">
                                                <label for="accession_no"><?php echo $this->lang->line('accession_no'); ?></label>
                                                <input type="text" name="accession_no" id="accession_no" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <div class="form-group" style="margin-right: 15px; margin-bottom: 15px;">
                                                <label><?php echo $this->lang->line('publisher'); ?></label>
                                                <select class="form-control" name="publisher" id="publisher">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($publishers as $publisher_item) { ?>
                                                        <option value="<?php echo $publisher_item['publish']; ?>"><?php echo $publisher_item['publish']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <div class="form-group" style="margin-right: 15px; margin-bottom: 15px;">
                                                <label><?php echo $this->lang->line('subject'); ?></label>
                                                <select class="form-control" name="subject" id="subject">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($subjects as $subject_item) { ?>
                                                        <option value="<?php echo $subject_item['subject']; ?>"><?php echo $subject_item['subject']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-3"> <!-- New column for the search button -->
                                            <div class="form-group" style="margin-bottom: 15px; margin-top: 25px;"> <!-- Added margin-top to align with dropdowns -->
                                                <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                            </div>
                                        </div>
                                    </div> <!-- End Second Row -->
                                </div>
                            </form>
                        </div><!--./row-->
                        <div class="mailbox-controls">
                            <!-- Check all button -->
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php 
                                    echo $this->session->flashdata('msg');
                                    $this->session->unset_userdata('msg');
                                ?>
                            <?php } ?>
                            <?php
                            if (isset($error_message)) {
                                echo "<div class='alert alert-danger'>" . $error_message . "</div>";
                            }
                            ?> 
                        </div>
                        <div class="mailbox-messages table-responsive overflow-visible-1">
                            <table width="100%" class="table table-striped table-bordered table-hover opaq-list" data-export-title="<?php echo $this->lang->line('book_list'); ?>">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('book_title'); ?></th>
                                        <th><?php echo $this->lang->line('description'); ?></th>
                                        <th><?php echo $this->lang->line('book_number'); ?></th>
                                        <th><?php echo $this->lang->line('isbn_number'); ?></th>
                                        <th><?php echo $this->lang->line('publisher'); ?></th>
                                        <th><?php echo $this->lang->line('author'); ?></th>
                                        <th><?php echo $this->lang->line('subject'); ?></th>
                                        <th><?php echo $this->lang->line('rack_number'); ?></th>
                                        <th><?php echo $this->lang->line('shelf_number'); ?></th>
                                        <th class="text-right"><?php echo $this->lang->line('book_price'); ?></th>
                                        <th><?php echo $this->lang->line('post_date'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table><!-- /.table -->
                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <div class="mailbox-controls">
                            <!-- Check all button -->
                            <div class="pull-right">
                            </div><!-- /.pull-right -->
                        </div>
                    </div>
                </div>
            </div><!--/.col (left) -->
            <!-- right column -->
        </div>
        <div class="row">
            <!-- left column -->
            <!-- right column -->
            <div class="col-md-12">
                <!-- Horizontal Form -->
                <!-- general form elements disabled -->
            </div><!--/.col (right) -->
        </div>   <!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script type="text/javascript">
    var base_url = '<?php echo base_url() ?>';
    function Popup(data)
    {
        var frame1 = $('<iframe />');
        frame1[0].name = "frame1";
        frame1.css({"position": "absolute", "top": "-1000000px"});
        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
        frameDoc.document.open();
        //Create a new HTML document.
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

    $("#print_div").click(function () {
        Popup($('#bklist').html());
    });   
</script>

<script type="text/javascript">
    var base_url = '<?php echo base_url() ?>';
    function Popup(data)
    {
        var frame1 = $('<iframe />');
        frame1[0].name = "frame1";
        frame1.css({"position": "absolute", "top": "-1000000px"});
        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
        frameDoc.document.open();
        //Create a new HTML document.
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

    $("#print_div").click(function () {
        Popup($('#bklist').html());
    });   
</script>

<script>
$(document).ready(function() {


    // Initialize Select2 on the dropdowns
    $('#book_title').select2();
    $('#author').select2();
    $('#publisher').select2();
    $('#subject').select2();
});

    ( function ( $ ) {
    'use strict';
    $(document).ready(function () {
        var table = $('.opaq-list').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": base_url + 'admin/opaq/getopaqlist',
                "type": "POST",
                "data": function (d) {
                    d.book_title = $('#book_title').val();
                    d.author = $('#author').val();
                    d.barcode = $('#barcode').val();
                    d.accession_no = $('#accession_no').val();
                    d.publisher = $('#publisher').val();
                    d.subject = $('#subject').val();
                }
            },
            "columns": [
                { "data": 0 }, // book_title
                { "data": 1 }, // description
                { "data": 2 }, // book_no
                { "data": 3 }, // isbn_no
                { "data": 4 }, // publish
                { "data": 5 }, // author
                { "data": 6 }, // subject
                { "data": 7 }, // rack_no
                { "data": 8 }, // shelf_id
                { "data": 9 }, // perunitcost
                { "data": 10 }  // postdate
            ],
            "order": [[ 0, "asc" ]],
            "dom": '<"top">rt<"bottom"ip><"clear">'
        });

        // Handle form submission for filtering
        $('#opaq_search_form').on('submit', function(e) {
            e.preventDefault();
            var form_data = $(this).serializeArray();
            var search_params = {};
            $.each(form_data, function(i, field){
                search_params[field.name] = field.value;
            });
            table.ajax.reload();
        });
    });
    } ( jQuery ) )
</script>