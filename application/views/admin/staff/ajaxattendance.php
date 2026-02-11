<table class="table table-striped table-bordered table-hover" id="attendancetable">
    <thead>
        <tr>
            <th>
                <?php echo $this->lang->line('date') . " | " . $this->lang->line('month'); ?>
            </th>
            <?php foreach ($monthlist as $monthkey => $monthvalue) {
    ?>
                <th><?php echo $monthvalue; ?></th>
            <?php }
?>
        </tr>
    </thead>
    <tbody>
        <?php
if (!empty($resultlist)) {
    $j = 0;
    for ($i = 1; $i <= 31; $i++) {
        ?>
                <tr>
                    <td><?php echo $attendence_array[$j] ?></td>
                    <?php
foreach ($monthlist as $key => $value) {
            $datemonth = date("m", strtotime($key));
            $att_dates = $year . "-" . $datemonth . "-" . sprintf("%02d", $i);
            $display_key = '';
            $display_class = '';
            // Check holidays first (to prioritize 'H' over 'W' if a date is both)
            if (!empty($holiday_dates_year) && in_array($att_dates, $holiday_dates_year, true)) {
                $display_key = 'H';
                $display_class = 'att-cell-holiday';
            } elseif (!empty($weekend_day_dates_year) && in_array($att_dates, $weekend_day_dates_year, true)) {
                $display_key = 'W';
                $display_class = 'att-cell-weekend';
            } elseif (array_key_exists($att_dates, $resultlist) && !empty($resultlist[$att_dates]["key"]) && !in_array($att_dates, $holiday_dates_year, true)) {
                // Only show database record if it's NOT a holiday
                $display_key = $resultlist[$att_dates]["key"];
                if ($display_key === 'P') {
                    $display_class = 'att-cell-present';
                } elseif ($display_key === 'HD') {
                    $display_class = 'att-cell-halfday';
                } elseif ($display_key === 'A') {
                    $display_class = 'att-cell-absent';
                }
            }
            ?>
                        <td><span data-toggle="popover" class="detail_popover" data-original-title="" title=""><a href="#" class="att-cell <?php echo $display_class; ?>"><?php
echo $display_key;
            ?></a></span>
                            <div class="fee_detail_popover" style="display: none"><?php echo $resultlist[$att_dates]["remark"]; ?></div>
                        </td>
                    <?php }?>
                </tr>
                <?php
$j++;
    }
    ?>
            <?php
} else {
    echo "No Record Found";
}
?>
    </tbody>
</table>