<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Set Final Years</h3>
                    </div>
                    <form action="<?php echo site_url('admin/finalyearclasses/save'); ?>" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) {
    echo $this->session->flashdata('msg');
    $this->session->unset_userdata('msg');
}?>
                            <?php echo $this->customlib->getCSRF(); ?>

                            <p class="text-muted">Select the classes that should be treated as final year.</p>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;">Select</th>
                                            <th>Class</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($classlist)) {
    foreach ($classlist as $class) {
        $is_checked = in_array((int)$class['id'], $selected_class_ids, true);
        ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="class_ids[]" value="<?php echo $class['id']; ?>" <?php echo $is_checked ? 'checked="checked"' : ''; ?> />
                                                </td>
                                                <td><?php echo $class['class']; ?></td>
                                            </tr>
                                        <?php
    }
} else {
    ?>
                                            <tr>
                                                <td colspan="2" class="text-center">No classes found.</td>
                                            </tr>
                                        <?php
}
?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
