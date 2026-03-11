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
    $present_variant_keys = ['FHL', 'SHL', 'FHP', 'SHP', 'FHA', 'SHA'];
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
            // compensatory working day? mark with special style but still allow attendance override
            if (!empty($compensation_dates_year) && in_array($att_dates, $compensation_dates_year, true)) {
                $display_class = 'att-cell-compensation';
            }
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
                if ($display_key === 'P' || in_array($display_key, $present_variant_keys)) {
                    $display_class = 'att-cell-present';
                } elseif ($display_key === 'HD') {
                    $display_class = 'att-cell-halfday';
                } elseif ($display_key === 'A') {
                    $display_class = 'att-cell-absent';
                }
            }
            $tooltip_title = '';
            if (!empty($display_key) && !in_array($display_key, ['H', 'W']) && isset($resultlist[$att_dates])) {
                $in_t  = !empty($resultlist[$att_dates]['in_time'])  ? $resultlist[$att_dates]['in_time']  : '-';
                $out_t = !empty($resultlist[$att_dates]['out_time']) ? $resultlist[$att_dates]['out_time'] : '-';
                $tooltip_title = 'In: ' . $in_t . ' | Out: ' . $out_t;
            }
            ?>
                        <td>
                            <span <?php if ($tooltip_title): ?>data-toggle="tooltip" data-placement="top" title="<?php echo htmlspecialchars($tooltip_title, ENT_QUOTES); ?>"<?php endif; ?>><a href="#" class="att-cell <?php echo $display_class; ?>"><?php echo $display_key; ?></a></span>
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