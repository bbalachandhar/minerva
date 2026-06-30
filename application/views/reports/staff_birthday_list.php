<?php $dateFormat = $this->customlib->getSchoolDateFormat(); ?>
<style>
.bd-page-header {
    background: linear-gradient(135deg, #2c3e7a 0%, #1a6fa0 100%);
    border-radius: 10px;
    padding: 24px 28px 20px;
    margin-bottom: 20px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.bd-page-header::after {
    content: '🎂';
    position: absolute;
    right: 24px;
    top: 12px;
    font-size: 54px;
    opacity: 0.2;
}
.bd-page-header h2 { margin: 0 0 4px; font-size: 22px; font-weight: 700; }
.bd-page-header p  { margin: 0; opacity: 0.85; font-size: 13px; }
.bd-filter-box {
    background: #fff;
    border-radius: 10px;
    padding: 18px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
    margin-bottom: 20px;
}
.bd-filter-box label { font-weight: 600; font-size: 12px; color: #555; text-transform: uppercase; letter-spacing:.4px; }
.bd-filter-box .form-control { border-radius: 6px; border: 1px solid #dce0e9; }
.bd-stats-bar { display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap; align-items:center; }
.bd-stat {
    background:#fff; border-radius:8px; padding:10px 18px;
    box-shadow:0 1px 6px rgba(0,0,0,.07); font-size:13px;
    display:flex; align-items:center; gap:8px;
}
.bd-stat .num { font-weight:700; font-size:20px; color:#2c3e7a; }
.bd-stat.today-stat .num { color:#e74c3c; }
.bd-export-btns { margin-left:auto; display:flex; gap:8px; }
.bd-view-toggle { margin-bottom:14px; }
.bd-view-toggle .btn { border-radius:6px; }

/* ── CARD GRID ───────────────── */
.bd-cards { display:flex; flex-wrap:wrap; gap:16px; }
.bd-card {
    background:#fff; border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,.09);
    width:calc(25% - 12px); overflow:hidden;
    transition:transform .15s, box-shadow .15s; position:relative;
}
.bd-card:hover { transform:translateY(-3px); box-shadow:0 6px 20px rgba(44,62,122,.18); }
@media(max-width:1199px){ .bd-card { width:calc(33.33% - 11px); } }
@media(max-width:767px) { .bd-card { width:calc(50% - 8px); } }
@media(max-width:480px) { .bd-card { width:100%; } }

.bd-card-top {
    background:linear-gradient(135deg,#2c3e7a,#1a6fa0);
    height:56px; position:relative;
}
.bd-card-top.today-top { background:linear-gradient(135deg,#f093fb,#f5576c); }
.bd-today-badge {
    position:absolute; top:6px; right:8px;
    background:#fff; color:#e74c3c;
    font-size:10px; font-weight:700; padding:2px 7px;
    border-radius:20px; letter-spacing:.3px;
}
.bd-card-avatar {
    width:72px; height:72px; border-radius:50%;
    border:3px solid #fff; background:#d4e0f7;
    object-fit:cover; position:absolute;
    bottom:-36px; left:50%; transform:translateX(-50%); overflow:hidden;
}
.bd-card-avatar-initials {
    width:72px; height:72px; border-radius:50%;
    border:3px solid #fff; background:#c5d8f0;
    position:absolute; bottom:-36px; left:50%; transform:translateX(-50%);
    display:flex; align-items:center; justify-content:center;
    font-size:24px; font-weight:700; color:#2c3e7a;
    box-shadow:0 2px 8px rgba(0,0,0,.12);
}
.bd-card-body { padding:44px 14px 16px; text-align:center; }
.bd-card-name { font-weight:700; font-size:14px; color:#222; line-height:1.3; margin-bottom:2px; }
.bd-card-emp  { font-size:11px; color:#999; margin-bottom:8px; }
.bd-card-dept {
    display:inline-block; background:#e8f0fc; color:#2c3e7a;
    border-radius:20px; padding:2px 10px;
    font-size:11px; font-weight:600; margin-bottom:4px;
}
.bd-card-desig {
    display:block; font-size:11px; color:#888; margin-bottom:8px;
}
.bd-card-info { font-size:12px; color:#555; line-height:1.9; }
.bd-card-info span { display:flex; align-items:center; justify-content:center; gap:5px; }
.bd-card-dob { color:#2c3e7a; font-weight:600; }
.bd-card-age  { font-size:10px; color:#aaa; display:block; margin-top:2px; }

/* Table */
#bd-table-view { display:none; }
.bd-table-wrap { border-radius:10px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.07); }
#bdStaffTable thead tr { background:linear-gradient(90deg,#2c3e7a,#1a6fa0); color:#fff; }
#bdStaffTable thead th { border:none; font-size:12px; font-weight:600; padding:10px 12px; }
#bdStaffTable tbody td { vertical-align:middle; font-size:13px; }
.bd-thumb { width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #d4e0f7; }
.bd-thumb-init {
    width:40px; height:40px; border-radius:50%;
    background:#c5d8f0; display:inline-flex;
    align-items:center; justify-content:center;
    font-weight:700; color:#2c3e7a; font-size:15px;
}
.badge-today { background:#f5576c; color:#fff; padding:2px 7px; border-radius:10px; font-size:10px; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <ol class="breadcrumb">
      <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
      <li>Reports</li>
      <li>Human Resource</li>
      <li class="active">Staff Birthday List</li>
    </ol>
  </section>
  <section class="content">
    <?php $this->load->view('reports/_human_resource'); ?>

    <!-- Page Header -->
    <div class="bd-page-header">
      <h2><i class="fa fa-birthday-cake"></i> Staff Birthday List</h2>
      <p>Search staff members by birthday range to send wishes or plan celebrations</p>
    </div>

    <!-- Filter -->
    <div class="bd-filter-box">
      <form method="post" action="<?php echo site_url('report/staff_birthday_list'); ?>" id="bd-form">
        <?php echo $this->customlib->getCSRF(); ?>
        <div class="row">
          <div class="col-sm-4 col-md-3">
            <div class="form-group" style="margin-bottom:0;">
              <label>From Date</label>
              <input type="text" id="from_date_bd" class="form-control date" name="from_date"
                     placeholder="<?php echo $dateFormat; ?>"
                     value="<?php echo htmlspecialchars($from_date); ?>" autocomplete="off">
            </div>
          </div>
          <div class="col-sm-4 col-md-3">
            <div class="form-group" style="margin-bottom:0;">
              <label>To Date</label>
              <input type="text" id="to_date_bd" class="form-control date" name="to_date"
                     placeholder="<?php echo $dateFormat; ?>"
                     value="<?php echo htmlspecialchars($to_date); ?>" autocomplete="off">
            </div>
          </div>
          <div class="col-sm-4 col-md-3" style="display:flex;align-items:flex-end;">
            <div class="form-group" style="margin-bottom:0;width:100%;">
              <label>&nbsp;</label>
              <button type="submit" class="btn btn-primary btn-block" style="border-radius:6px;">
                <i class="fa fa-search"></i> Search
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>

    <?php if ($from_date != '' && $to_date != ''): ?>
    <?php
      $today_md   = date('md');
      $total      = count($resultlist);
      $today_bday = 0;
      foreach ($resultlist as $r) {
          if ($r['dob'] && date('md', strtotime($r['dob'])) === $today_md) $today_bday++;
      }
    ?>

    <!-- Stats + Export bar -->
    <div class="bd-stats-bar">
      <div class="bd-stat">
        <i class="fa fa-users" style="color:#2c3e7a;font-size:20px;"></i>
        <div><div class="num"><?php echo $total; ?></div><div style="font-size:11px;color:#888;">Staff Found</div></div>
      </div>
      <?php if ($today_bday > 0): ?>
      <div class="bd-stat today-stat">
        <i class="fa fa-birthday-cake" style="color:#e74c3c;font-size:20px;"></i>
        <div><div class="num"><?php echo $today_bday; ?></div><div style="font-size:11px;color:#888;">Today's Birthdays</div></div>
      </div>
      <?php endif; ?>
      <div class="bd-export-btns">
        <a href="<?php echo site_url('report/staff_birthday_list_pdf') . '?date_from=' . urlencode($from_date) . '&date_to=' . urlencode($to_date); ?>"
           class="btn btn-danger btn-sm" style="border-radius:6px;" title="Export PDF">
          <i class="fa fa-file-pdf-o"></i> PDF
        </a>
        <a href="<?php echo site_url('report/staff_birthday_list_xls') . '?date_from=' . urlencode($from_date) . '&date_to=' . urlencode($to_date); ?>"
           class="btn btn-success btn-sm" style="border-radius:6px;" title="Export Excel">
          <i class="fa fa-file-excel-o"></i> Excel
        </a>
      </div>
    </div>

    <!-- View Toggle -->
    <div class="bd-view-toggle">
      <div class="btn-group">
        <button class="btn btn-default btn-sm active" id="btn-card-view"><i class="fa fa-th-large"></i> Cards</button>
        <button class="btn btn-default btn-sm" id="btn-table-view"><i class="fa fa-table"></i> Table</button>
      </div>
    </div>

    <?php if ($total === 0): ?>
    <div class="text-center" style="padding:50px 0;color:#aaa;">
      <i class="fa fa-birthday-cake" style="font-size:48px;"></i>
      <p style="margin-top:10px;font-size:15px;">No staff birthdays found in this date range.</p>
    </div>
    <?php else: ?>

    <!-- CARD VIEW -->
    <div id="bd-card-view">
      <div class="bd-cards">
        <?php foreach ($resultlist as $row):
          $name     = htmlspecialchars($row['staff_name']);
          $initials = strtoupper(substr($row['name'] ?? $row['staff_name'], 0, 1));
          $dob_md   = $row['dob'] ? date('md', strtotime($row['dob'])) : '';
          $is_today = ($dob_md === $today_md);
          $dob_fmt  = $row['dob'] ? date($dateFormat, strtotime($row['dob'])) : '—';

          // Countdown
          $countdown = '';
          if ($row['dob']) {
              $this_year_bd = date('Y') . '-' . date('m-d', strtotime($row['dob']));
              $bd_ts    = strtotime($this_year_bd);
              $today_ts = strtotime(date('Y-m-d'));
              if ($bd_ts < $today_ts) $bd_ts = strtotime((date('Y') + 1) . '-' . date('m-d', strtotime($row['dob'])));
              $days_left  = (int)(($bd_ts - $today_ts) / 86400);
              $countdown  = $is_today ? '🎉 Today!' : $days_left . ' days';
          }

          $img_url = !empty($row['image'])
              ? $this->media_storage->getImageURL('uploads/staff_images/' . $row['image'])
              : null;
        ?>
        <div class="bd-card">
          <div class="bd-card-top <?php echo $is_today ? 'today-top' : ''; ?>">
            <?php if ($is_today): ?>
            <span class="bd-today-badge">🎂 Today!</span>
            <?php endif; ?>
            <?php if ($img_url): ?>
            <img class="bd-card-avatar" src="<?php echo $img_url; ?>" alt="<?php echo $name; ?>">
            <?php else: ?>
            <div class="bd-card-avatar-initials"><?php echo $initials; ?></div>
            <?php endif; ?>
          </div>
          <div class="bd-card-body">
            <div class="bd-card-name"><?php echo $name; ?></div>
            <div class="bd-card-emp"><?php echo htmlspecialchars($row['employee_id']); ?></div>
            <span class="bd-card-dept"><?php echo htmlspecialchars($row['department_name'] ?? '—'); ?></span>
            <?php if (!empty($row['designation'])): ?>
            <span class="bd-card-desig"><?php echo htmlspecialchars($row['designation']); ?></span>
            <?php endif; ?>
            <div class="bd-card-info">
              <span class="bd-card-dob"><i class="fa fa-birthday-cake"></i> <?php echo $dob_fmt; ?></span>
              <?php if ($countdown): ?><span class="bd-card-age"><?php echo $countdown; ?></span><?php endif; ?>
              <?php if (!empty($row['contact_no'])): ?>
              <span style="margin-top:4px;"><i class="fa fa-phone" style="color:#aaa;"></i> <?php echo htmlspecialchars($row['contact_no']); ?></span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- TABLE VIEW -->
    <div id="bd-table-view">
      <div class="bd-table-wrap">
        <table id="bdStaffTable" class="table table-hover" style="margin-bottom:0;width:100%;">
          <thead>
            <tr>
              <th>#</th>
              <th>Photo</th>
              <th>Staff Name</th>
              <th>Emp ID</th>
              <th>Department</th>
              <th>Designation</th>
              <th>Birthday</th>
              <th>Contact</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = 1; foreach ($resultlist as $row):
              $name     = htmlspecialchars($row['staff_name']);
              $initials = strtoupper(substr($row['name'] ?? $row['staff_name'], 0, 1));
              $dob_md   = $row['dob'] ? date('md', strtotime($row['dob'])) : '';
              $is_today = ($dob_md === $today_md);
              $dob_fmt  = $row['dob'] ? date($dateFormat, strtotime($row['dob'])) : '—';
              $img_url  = !empty($row['image'])
                  ? $this->media_storage->getImageURL('uploads/staff_images/' . $row['image'])
                  : null;
            ?>
            <tr style="<?php echo $is_today ? 'background:#fff3e0;' : ''; ?>">
              <td><?php echo $i++; ?><?php if ($is_today) echo ' <span class="badge-today">Today</span>'; ?></td>
              <td>
                <?php if ($img_url): ?>
                <img class="bd-thumb" src="<?php echo $img_url; ?>" alt="">
                <?php else: ?>
                <div class="bd-thumb-init"><?php echo $initials; ?></div>
                <?php endif; ?>
              </td>
              <td><strong><?php echo $name; ?></strong></td>
              <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
              <td><?php echo htmlspecialchars($row['department_name'] ?? '—'); ?></td>
              <td><?php echo htmlspecialchars($row['designation'] ?? '—'); ?></td>
              <td><?php echo $dob_fmt; ?></td>
              <td><?php echo htmlspecialchars($row['contact_no'] ?? ''); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php endif; // $total > 0 ?>
    <?php endif; // date range selected ?>

  </section>
</div>

<script>
$(function () {
    $('#from_date_bd, #to_date_bd').datepicker({ format: date_format, autoclose: true, todayHighlight: true, weekStart: start_week });

    $('#btn-card-view').on('click', function () {
        $(this).addClass('active').siblings().removeClass('active');
        $('#bd-card-view').show(); $('#bd-table-view').hide();
    });
    $('#btn-table-view').on('click', function () {
        $(this).addClass('active').siblings().removeClass('active');
        $('#bd-card-view').hide(); $('#bd-table-view').show();
        if (!$.fn.DataTable.isDataTable('#bdStaffTable')) {
            $('#bdStaffTable').DataTable({ paging: true, searching: true, ordering: true, info: true, pageLength: 25 });
        }
    });
});
</script>
