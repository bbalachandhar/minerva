<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-book"></i> </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Horizontal Form -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('add_book'); ?></h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('import_book', 'can_view')) {
                                ?>
                                <a class="btn btn-sm btn-primary" href="<?php echo base_url(); ?>admin/book/import" autocomplete="off"><i class="fa fa-plus"></i> <?php echo $this->lang->line('import_book'); ?></a> 
                            <?php }
                            ?>
                        </div>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    <form id="form1" action="<?php echo site_url('admin/book/create') ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8">
                        <div class="box-body row">
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
                            <?php echo $this->customlib->getCSRF(); ?>                     
                            <div class="form-group col-md-3">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('book_title'); ?></label><small class="req"> *</small>
                                <input autofocus=""  id="book_title" name="book_title" placeholder="" type="text" class="form-control"  value="<?php echo set_value('book_title'); ?>" />
                                <span class="text-danger"><?php echo form_error('book_title'); ?></span>
                            </div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1"><?php echo $this->lang->line('book_no'); ?> <small class="req"> *</small></label>
    <input autofocus="" id="book_no" name="book_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('book_no'); ?>" />
    <span class="text-danger"><?php echo form_error('book_no'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Barcode</label>
    <input id="barcode" name="barcode" placeholder="" type="text" class="form-control" value="<?php echo set_value('barcode'); ?>" />
    <span class="text-danger"><?php echo form_error('barcode'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Category Name <small class="req"> *</small></label>
    <input id="category_name" name="category_name" placeholder="" type="text" class="form-control" value="<?php echo set_value('category_name'); ?>" />
    <span class="text-danger"><?php echo form_error('category_name'); ?></span>
</div>
<div class="clearfix"></div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">SubCategory Name</label>
    <input id="subcategory_name" name="subcategory_name" placeholder="" type="text" class="form-control" value="<?php echo set_value('subcategory_name'); ?>" />
    <span class="text-danger"><?php echo form_error('subcategory_name'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1"><?php echo $this->lang->line('isbn_number'); ?></label>
    <input id="isbn_no" name="isbn_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('isbn_no'); ?>" />
    <span class="text-danger"><?php echo form_error('isbn_no'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1"><?php echo $this->lang->line('publisher'); ?></label>
    <input id="publish" name="publish" placeholder="" type="text" class="form-control"  value="<?php echo set_value('publish'); ?>" />
    <span class="text-danger"><?php echo form_error('publish'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Edition</label>
    <input id="edition" name="edition" placeholder="" type="text" class="form-control" value="<?php echo set_value('edition'); ?>" />
    <span class="text-danger"><?php echo form_error('edition'); ?></span>
</div>
<div class="clearfix"></div>
                            <div class="form-group col-md-3">
                                <label for="exampleInputEmail1">Class No</label>
                                <input id="class_no" name="class_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('class_no'); ?>" />
                                <span class="text-danger"><?php echo form_error('class_no'); ?></span>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="exampleInputEmail1">Edition Type <small class="req"> *</small></label>
                                <input id="edition_type" name="edition_type" placeholder="" type="text" class="form-control" value="<?php echo set_value('edition_type'); ?>" />
                                <span class="text-danger"><?php echo form_error('edition_type'); ?></span>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="exampleInputEmail1">Publish Year</label>
                                <input id="publish_year" name="publish_year" placeholder="" type="text" class="form-control" value="<?php echo set_value('publish_year'); ?>" />
                                <span class="text-danger"><?php echo form_error('publish_year'); ?></span>
                            </div>                            <div class="form-group col-md-3">
                                <label for="exampleInputEmail1">Medium</label>
                                <input id="medium" name="medium" placeholder="" type="text" class="form-control" value="<?php echo set_value('medium'); ?>" />
                                <span class="text-danger"><?php echo form_error('medium'); ?></span>
                            </div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">BookType</label>
    <input id="book_type" name="book_type" placeholder="" type="text" class="form-control" value="<?php echo set_value('book_type'); ?>" />
    <span class="text-danger"><?php echo form_error('book_type'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1"><?php echo $this->lang->line('author'); ?> <small class="req"> *</small></label>
    <input id="author" name="author" placeholder="" type="text" class="form-control" value="<?php echo set_value('author'); ?>" />
    <span class="text-danger"><?php echo form_error('author'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Author2</label>
    <input id="author2" name="author2" placeholder="" type="text" class="form-control" value="<?php echo set_value('author2'); ?>" />
    <span class="text-danger"><?php echo form_error('author2'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1"><?php echo $this->lang->line('subject'); ?> <small class="req"> *</small></label>
    <input id="subject" name="subject" placeholder="" type="text" class="form-control"  value="<?php echo set_value('subject'); ?>" />
    <span class="text-danger"><?php echo form_error('subject'); ?></span>
</div>
<div class="clearfix"></div>
                            <div class="form-group col-md-3">
    <label for="exampleInputEmail1">Position Rack <small class="req"> *</small></label>
    <input id="rack_no" name="rack_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('rack_no'); ?>" />
    <span class="text-danger"><?php echo form_error('rack_no'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Position Shelf</label>
    <input id="shelf_id" name="shelf_id" placeholder="" type="text" class="form-control" value="<?php echo set_value('shelf_id'); ?>" />
    <span class="text-danger"><?php echo form_error('shelf_id'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1"><?php echo $this->lang->line('price'); ?></label>
    <input id="perunitcost" name="perunitcost" placeholder="" type="text" class="form-control" value="<?php echo set_value('perunitcost'); ?>" />
    <span class="text-danger"><?php echo form_error('perunitcost'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Purchase Date</label>
    <input id="purchase_date" name="purchase_date" placeholder="" type="text" class="form-control date" value="<?php echo set_value('purchase_date'); ?>" />
    <span class="text-danger"><?php echo form_error('purchase_date'); ?></span>
</div>
<div class="clearfix"></div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Bill Number</label>
    <input id="bill_no" name="bill_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('bill_no'); ?>" />
    <span class="text-danger"><?php echo form_error('bill_no'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Bill Date</label>
    <input id="bill_date" name="bill_date" placeholder="" type="text" class="form-control date" value="<?php echo set_value('bill_date'); ?>" />
    <span class="text-danger"><?php echo form_error('bill_date'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Pages <small class="req"> *</small></label>
    <input id="pages" name="pages" placeholder="" type="text" class="form-control" value="<?php echo set_value('pages'); ?>" />
    <span class="text-danger"><?php echo form_error('pages'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Department <small class="req"> *</small></label>
    <input id="department" name="department" placeholder="" type="text" class="form-control" value="<?php echo set_value('department'); ?>" />
    <span class="text-danger"><?php echo form_error('department'); ?></span>
</div>
<div class="clearfix"></div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1"><?php echo $this->lang->line('post_date'); ?></label>
    <input id="postdate" name="postdate"  placeholder="" type="text" class="form-control date"  value="<?php echo set_value('postdate', date($this->customlib->getSchoolDateFormat())); ?>" />
    <span class="text-danger"><?php echo form_error('postdate'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1"><?php echo $this->lang->line('description'); ?></label>
    <textarea class="form-control" id="description" name="description" placeholder="" rows="3" placeholder="Enter ..."><?php echo set_value('description'); ?></textarea>
    <span class="text-danger"><?php echo form_error('description'); ?></span>
</div>
<div class="clearfix"></div>
                        </div><!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </form>
                </div>
            </div><!--/.col (right) -->
        </div>
        <div class="row">
            <div class="col-md-12">
            </div><!--/.col (right) -->
        </div>   <!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script type="text/javascript">
    $(document).ready(function () {
        $("#btnreset").click(function () {
            /* Single line Reset function executes on click of Reset Button */
            $("#form1")[0].reset();
        });

    });
</script>
<script>
    $(document).ready(function () {
        $('.detail_popover').popover({
            placement: 'right',
            trigger: 'hover',
            container: 'body',
            html: true,
            content: function () {
                return $(this).closest('td').find('.fee_detail_popover').html();
            }
        });
    });
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/js/savemode.js"></script>