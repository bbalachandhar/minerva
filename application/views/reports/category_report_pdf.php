<!DOCTYPE html>
<html>
<head>
    <title><?php echo $this->lang->line('category_report'); ?></title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/AdminLTE.min.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('category_report'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('class'); ?></th>
                                        <?php foreach ($overall_category_counts as $category) { ?>
                                            <th><?php echo $category['category_name']; ?></th>
                                        <?php } ?>
                                        <th><?php echo $this->lang->line('total'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $class_wise_data = [];
                                    foreach ($class_category_counts as $item) {
                                        $class_wise_data[$item['class_name']][$item['category_name']] = $item['student_count'];
                                    }

                                    foreach ($class_wise_data as $class_name => $categories) {
                                        echo '<tr>';
                                        echo '<td>' . $class_name . '</td>';
                                        $class_total = 0;
                                        foreach ($overall_category_counts as $overall_category) {
                                            $category_name = $overall_category['category_name'];
                                            $count = isset($categories[$category_name]) ? $categories[$category_name] : 0;
                                            echo '<td>' . $count . '</td>';
                                            $class_total += $count;
                                        }
                                        echo '<td>' . $class_total . '</td>';
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><?php echo $this->lang->line('overall_total'); ?></th>
                                        <?php
                                        $overall_grand_total = 0;
                                        foreach ($overall_category_counts as $category) {
                                            echo '<th>' . $category['total_student_count'] . '</th>';
                                            $overall_grand_total += $category['total_student_count'];
                                        }
                                        echo '<th>' . $overall_grand_total . '</th>';
                                        ?>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>