<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-book"></i> <?php //echo $this->lang->line('library'); ?> </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Horizontal Form -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('edit_book'); ?></h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->

                    <form id="form1" action="<?php echo site_url('admin/book/edit/' . $id) ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8">
                        <div class="box-body">
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
                            <input  type="hidden" name="id" value="<?php echo set_value('id', $editbook['id']); ?>" >
                            <div class="form-group col-md-3">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('book_title'); ?> <small class="req"> *</small></label>
                                <input autofocus="" id="book_title" name="book_title" placeholder="" type="text" class="form-control"  value="<?php echo set_value('book_title', $editbook['book_title']); ?>" />
                                <span class="text-danger"><?php echo form_error('book_title'); ?></span>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('book_number'); ?> <small class="req"> *</small></label>
                                <input id="book_no" name="book_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('book_no', $editbook['book_no']); ?>" />
                                <span class="text-danger"><?php echo form_error('book_no'); ?></span>
                            </div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Barcode</label>
    <input id="barcode" name="barcode" placeholder="" type="text" class="form-control"  value="<?php echo set_value('barcode', $editbook['barcode']); ?>" />
    <span class="text-danger"><?php echo form_error('barcode'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Category Name <small class="req"> *</small></label>
    <select id="category_name" name="category_name" class="form-control">
        <option value="">Select Category</option>
        <?php foreach($categorylist as $category) { ?>
            <option value="<?php echo $category['id']; ?>" <?php if($editbook['category_name'] == $category['category_name']) echo 'selected'; ?>><?php echo $category['category_name']; ?></option>
        <?php } ?>
    </select>
    <span class="text-danger"><?php echo form_error('category_name'); ?></span>
</div>
<div class="clearfix"></div>

<div class="form-group col-md-3">
    <label for="exampleInputEmail1">SubCategory Name</label>
    <select id="subcategory_name" name="subcategory_name" class="form-control">
        <option value="">Select SubCategory</option>
        <?php foreach($subcategorylist as $subcategory) { ?>
            <option value="<?php echo $subcategory['subcategory_name']; ?>" <?php if($editbook['subcategory_name'] == $subcategory['subcategory_name']) echo 'selected'; ?>><?php echo $subcategory['subcategory_name']; ?></option>
        <?php } ?>
    </select>
    <span class="text-danger"><?php echo form_error('subcategory_name'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1"><?php echo $this->lang->line('isbn_number'); ?></label>
    <input id="isbn_no" name="isbn_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('isbn_no', $editbook['isbn_no']); ?>" />
    <span class="text-danger"><?php echo form_error('isbn_no'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1"><?php echo $this->lang->line('publisher'); ?></label>
    <select id="publish" name="publish" class="form-control">
        <option value="">Select Publisher</option>
        <?php foreach($publisherlist as $publisher) { ?>
            <option value="<?php echo $publisher['publisher_name']; ?>" <?php if($editbook['publish'] == $publisher['publisher_name']) echo 'selected'; ?>><?php echo $publisher['publisher_name']; ?></option>
        <?php } ?>
    </select>
    <span class="text-danger"><?php echo form_error('publish'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Vendor</label>
    <select id="vendor" name="vendor" class="form-control">
        <option value="">Select Vendor</option>
        <?php foreach($vendorlist as $vendor) { ?>
            <option value="<?php echo $vendor['vendor_name']; ?>" <?php if($editbook['vendor'] == $vendor['vendor_name']) echo 'selected'; ?>><?php echo $vendor['vendor_name']; ?></option>
        <?php } ?>
    </select>
    <span class="text-danger"><?php echo form_error('vendor'); ?></span>
</div>
<div class="clearfix"></div>

<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Edition</label>
    <input id="edition" name="edition" placeholder="" type="text" class="form-control"  value="<?php echo set_value('edition', $editbook['edition']); ?>" />
    <span class="text-danger"><?php echo form_error('edition'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Class No</label>
    <input id="class_no" name="class_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('class_no', $editbook['class_no']); ?>" />
    <span class="text-danger"><?php echo form_error('class_no'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Edition Type <small class="req"> *</small></label>
    <input id="edition_type" name="edition_type" placeholder="" type="text" class="form-control"  value="<?php echo set_value('edition_type', $editbook['edition_type']); ?>" />
    <span class="text-danger"><?php echo form_error('edition_type'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Publish Year</label>
    <input id="publish_year" name="publish_year" placeholder="" type="text" class="form-control"  value="<?php echo set_value('publish_year', $editbook['publish_year']); ?>" />
    <span class="text-danger"><?php echo form_error('publish_year'); ?></span>
</div>
<div class="clearfix"></div>

<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Medium</label>
    <input id="medium" name="medium" placeholder="" type="text" class="form-control"  value="<?php echo set_value('medium', $editbook['medium']); ?>" />
    <span class="text-danger"><?php echo form_error('medium'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">BookType</label>
    <select id="book_type" name="book_type" class="form-control">
        <option value="">Select BookType</option>
        <?php foreach($booktypelist as $booktype) { ?>
            <option value="<?php echo $booktype['book_type']; ?>" <?php if($editbook['book_type'] == $booktype['book_type']) echo 'selected'; ?>><?php echo $booktype['book_type']; ?></option>
        <?php } ?>
    </select>
    <span class="text-danger"><?php echo form_error('book_type'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1"><?php echo $this->lang->line('author'); ?> <small class="req"> *</small></label>
    <input id="amount" name="author" placeholder="" type="text" class="form-control"  value="<?php echo set_value('author', $editbook['author']); ?>" />
    <span class="text-danger"><?php echo form_error('author'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Author2</label>
    <input id="author2" name="author2" placeholder="" type="text" class="form-control"  value="<?php echo set_value('author2', $editbook['author2']); ?>" />
    <span class="text-danger"><?php echo form_error('author2'); ?></span>
</div>
<div class="clearfix"></div>

<div class="form-group col-md-3">
    <label for="exampleInputEmail1"><?php echo $this->lang->line('subject'); ?> <small class="req"> *</small></label>
    <select id="subject" name="subject" class="form-control">
        <option value="">Select Subject</option>
        <?php foreach($subjectlist as $subject) { ?>
            <option value="<?php echo $subject['subject_name']; ?>" <?php if($editbook['subject'] == $subject['subject_name']) echo 'selected'; ?>><?php echo $subject['subject_name']; ?></option>
        <?php } ?>
    </select>
    <span class="text-danger"><?php echo form_error('subject'); ?></span>
</div>
                            <div class="form-group col-md-3">
    <label for="exampleInputEmail1">Position Rack <small class="req"> *</small></label>
    <select id="rack_no" name="rack_no" class="form-control">
        <option value="">Select Position Rack</option>
        <?php foreach($racklist as $rack) { ?>
            <option value="<?php echo $rack['id']; ?>" <?php if($editbook['rack_no'] == $rack['rack_name']) echo 'selected'; ?>><?php echo $rack['rack_name']; ?></option>
        <?php } ?>
    </select>
    <span class="text-danger"><?php echo form_error('rack_no'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Position Shelf</label>
    <select id="shelf_id" name="shelf_id" class="form-control">
        <option value="">Select Position Shelf</option>
        <?php foreach($shelflist as $shelf) { ?>
            <option value="<?php echo $shelf['shelf_name']; ?>" <?php if($editbook['shelf_id'] == $shelf['shelf_name']) echo 'selected'; ?>><?php echo $shelf['shelf_name']; ?></option>
        <?php } ?>
    </select>
    <span class="text-danger"><?php echo form_error('shelf_id'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1"><?php echo $this->lang->line('book_price'); ?> (<?php echo $currency_symbol; ?>)</label>
    <input id="amount" name="perunitcost" placeholder="" type="text" class="form-control"  value="<?php echo convertBaseAmountCurrencyFormat($editbook['perunitcost']); ?>" />
    <span class="text-danger"><?php echo form_error('perunitcost'); ?></span>
</div>
<div class="clearfix"></div>

<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Purchase Date</label>
    <input id="purchase_date" name="purchase_date" placeholder="" type="text" class="form-control date"  value="<?php echo set_value('purchase_date', $this->customlib->dateformat($editbook['purchase_date'])); ?>" />
    <span class="text-danger"><?php echo form_error('purchase_date'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Bill Number</label>
    <input id="bill_no" name="bill_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('bill_no', $editbook['bill_no']); ?>" />
    <span class="text-danger"><?php echo form_error('bill_no'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Bill Date</label>
    <input id="bill_date" name="bill_date" placeholder="" type="text" class="form-control date"  value="<?php echo set_value('bill_date', $this->customlib->dateformat($editbook['bill_date'])); ?>" />
    <span class="text-danger"><?php echo form_error('bill_date'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Pages <small class="req"> *</small></label>
    <input id="pages" name="pages" placeholder="" type="text" class="form-control"  value="<?php echo set_value('pages', $editbook['pages']); ?>" />
    <span class="text-danger"><?php echo form_error('pages'); ?></span>
</div>
<div class="clearfix"></div>

<div class="form-group col-md-3">
    <label for="exampleInputEmail1">Department <small class="req"> *</small></label>
    <input id="department" name="department" placeholder="" type="text" class="form-control"  value="<?php echo set_value('department', $editbook['department']); ?>" />
    <span class="text-danger"><?php echo form_error('department'); ?></span>
</div>
<div class="form-group col-md-3">
    <label for="exampleInputEmail1"><?php echo $this->lang->line('post_date'); ?></label>
    <input id="postdate" name="postdate"  placeholder="" type="text" class="form-control date"  value="<?php echo set_value('postdate', $this->customlib->dateformat($editbook['postdate'])); ?>" />
    <span class="text-danger"><?php echo form_error('postdate'); ?></span>
</div>
<div class="form-group col-md-6">
    <label for="exampleInputEmail1"><?php echo $this->lang->line('description'); ?></label>
    <textarea class="form-control" id="description" name="description" placeholder="" rows="3" placeholder=""><?php echo set_value('description', $editbook['description']); ?></textarea>
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
            <!-- left column -->
            <!-- right column -->
            <div class="col-md-12">
            </div><!--/.col (right) -->
        </div>   <!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

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

        // Category to Subcategory Dependent Dropdown
        $('#category_name').on('change', function() {
            var category_id = $(this).val();
            var subcategory_dropdown = $('#subcategory_name');
            subcategory_dropdown.html($('<option value="">Select SubCategory</option>'));

            console.log('Category ID selected: ' + category_id);

            if (category_id) {
                $.ajax({
                    url: '<?php echo site_url('admin/book/get_subcategories_by_category_id'); ?>',
                    type: 'POST',
                    data: {category_id: category_id},
                    dataType: 'json',
                    success: function(data) {
                        console.log('Subcategories received: ', data);
                        $.each(data, function(key, value) {
                            subcategory_dropdown.append($('<option></option>').attr('value', value.subcategory_name).text(value.subcategory_name));
                        });
                        // Pre-select if editing and subcategory matches
                        var edited_subcategory_name = '<?php echo isset($editbook['subcategory_name']) ? $editbook['subcategory_name'] : ''; ?>';
                        if (edited_subcategory_name && subcategory_dropdown.find('option[value="' + edited_subcategory_name + '"]').length > 0) {
                            subcategory_dropdown.val(edited_subcategory_name);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error for subcategories: ' + status + ', ' + error + ', Response: ' + xhr.responseText);
                        alert('Error loading subcategories. Check console for details.');
                    }
                });
            }
        });

        // Rack to Shelf Dependent Dropdown
        $('#rack_no').on('change', function() {
            var rack_name = $(this).val();
            var shelf_dropdown = $('#shelf_id');
            shelf_dropdown.empty();
            shelf_dropdown.append($('<option value="">Select Position Shelf</option>'));

            if (rack_name) {
                $.ajax({
                    url: '<?php echo base_url(); ?>admin/book/get_shelves_by_rack_id',
                    type: 'POST',
                    data: {rack_id: rack_name},
                    dataType: 'json',
                    success: function(data) {
                        $.each(data, function(key, value) {
                            shelf_dropdown.append($('<option></option>').attr('value', value.shelf_name).text(value.shelf_name));
                        });
                        // Pre-select if editing
                        if ('<?php echo isset($editbook['shelf_id']) ? $editbook['shelf_id'] : ''; ?>') {
                            shelf_dropdown.val('<?php echo isset($editbook['shelf_id']) ? $editbook['shelf_id'] : ''; ?>');
                        }
                    }
                });
            }
        });

        // Trigger change on load if editing to populate dependent dropdowns
        if ('<?php echo isset($editbook) ? 'true' : 'false'; ?>' == 'true') {
            $('#category_name').trigger('change');
            $('#rack_no').trigger('change');
        }
    });
</script>