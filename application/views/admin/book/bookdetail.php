<div class="content-wrapper">
    <section class="content-header">
        <h1 style="color: black;"><i class="fa fa-book"></i> <?php echo $title; ?></h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title" style="color: black;">Book Details</h3>
            </div>
            <div class="box-body">
                <?php if (!empty($book)) { ?>
                    <table class="table table-striped table-bordered table-hover">
                        <tr>
                            <th><?php echo $this->lang->line('book_title'); ?></th>
                            <td><?php echo $book['book_title']; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $this->lang->line('book_number'); ?></th>
                            <td><?php echo $book['book_no']; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $this->lang->line('isbn_number'); ?></th>
                            <td><?php echo $book['isbn_no']; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $this->lang->line('publisher'); ?></th>
                            <td><?php echo $book['publish']; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $this->lang->line('author'); ?></th>
                            <td><?php echo $book['author']; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $this->lang->line('subject'); ?></th>
                            <td><?php echo $book['subject']; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $this->lang->line('rack_number'); ?></th>
                            <td><?php echo $book['rack_no']; ?></td>
                        </tr>
                        <tr>
                            <th>Shelf Number</th>
                            <td><?php echo !empty($book['shelf_id']) ? $book['shelf_id'] : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $this->lang->line('book_price'); ?></th>
                            <td><?php echo $book['perunitcost']; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $this->lang->line('post_date'); ?></th>
                            <td><?php echo $book['postdate']; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $this->lang->line('description'); ?></th>
                            <td><?php echo $book['description']; ?></td>
                        </tr>
                    </table>
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-12">
                            <p><strong>Total Books (matching criteria):</strong> <?php echo $total_count; ?></p>
                            <p><strong>Available Books (matching criteria):</strong> <?php echo $available_count; ?></p>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div>
                <?php } ?>
                <a href="<?php echo base_url('admin/book/getall'); ?>" class="btn btn-primary btn-lg" style="color: white;">Back</a>
            </div>
        </div>
    </section>
</div>