<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-line-chart"></i> <?php echo $this->lang->line('reports'); ?> <small><?php echo $this->lang->line('category_report'); ?></small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                                                                    <div class="box-header ptbnull"></div>
                                                                    <div class="box-header ptbnull">
                                                                        <h3 class="box-title titlefix"><i class="fa fa-money"> </i> <?php echo $this->lang->line('category_report'); ?></h3>
                                                                        <div class="box-tools pull-right">
                                                                            <a href="<?php echo site_url('report/studentinformation'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-arrow-left"></i> <?php echo $this->lang->line('back'); ?></a>
                                                                        </div>
                                                                    </div>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover example">
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
                                        echo '</tr>';
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
    </section>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.example').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv',
                    {
                        extend: 'excelHtml5',
                        exportOptions: {
                            format: {
                                body: function ( data, row, column, node ) {
                                    // Strip HTML tags from data
                                    return data.replace( /(<([^>]+)>)/ig, '' );
                                },
                                footer: function ( data, row, column, node ) {
                                    // Strip HTML tags from data
                                    return data.replace( /(<([^>]+)>)/ig, '' );
                                }
                            }
                        },
                        footer: true
                    },
                    'pdf', 'print'
                ]
            });
        });
    </script>
</div>
