<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Birthday extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('customlib');
        $this->load->model('Birthday_model');
    }

    public function birthday_list()
    {
        if (!$this->rbac->hasPrivilege('birthday', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'admin/birthday/birthday_list');

        // DataTables AJAX handler (kept for backward compatibility)
        if ($this->input->server('REQUEST_METHOD') == 'POST' && $this->input->is_ajax_request()) {
            $date_from    = $this->input->post('date_from');
            $date_to      = $this->input->post('date_to');
            $draw         = $this->input->post('draw');
            $start        = $this->input->post('start');
            $length       = $this->input->post('length');
            $search_value = $this->input->post('search')['value'];
            $order_column = $this->input->post('order')[0]['column'];
            $order_dir    = $this->input->post('order')[0]['dir'];
            $columns      = ['admission_no','roll_no','firstname','class','section','dob','gender','mobileno','email','current_address'];
            $result = $this->Birthday_model->searchByBirthdayRangeDT(
                $date_from, $date_to, $start, $length, $search_value, $columns[$order_column], $order_dir
            );
            echo json_encode($result);
            exit;
        }

        $data['title']      = 'Birthday Report';
        $data['students']   = [];
        $data['date_from']  = '';
        $data['date_to']    = '';

        if ($this->input->post('date_from') && $this->input->post('date_to')) {
            $data['date_from'] = $this->input->post('date_from');
            $data['date_to']   = $this->input->post('date_to');
            $data['students']  = $this->Birthday_model->getByDateRange($data['date_from'], $data['date_to']);
        }

        $data['sch_setting'] = $this->sch_setting_detail;
        $this->load->view('layout/header', $data);
        $this->load->view('student/birthday_report', $data);
        $this->load->view('layout/footer', $data);
    }

    public function export_pdf()
    {
        if (!$this->rbac->hasPrivilege('birthday', 'can_view')) {
            access_denied();
        }
        $date_from = $this->input->get('date_from');
        $date_to   = $this->input->get('date_to');
        if (!$date_from || !$date_to) { show_error('Missing date range'); return; }

        $students    = $this->Birthday_model->getByDateRange($date_from, $date_to);
        $sch_setting = $this->sch_setting_detail;
        $dateFormat  = $this->customlib->getSchoolDateFormat();

        $html  = '<style>
            body { font-family: DejaVu Sans, sans-serif; font-size:11px; }
            h2 { text-align:center; margin:0; font-size:16px; }
            .sub { text-align:center; font-size:11px; color:#555; margin-bottom:10px; }
            table { width:100%; border-collapse:collapse; margin-top:10px; }
            th { background:#6f42c1; color:#fff; padding:7px 6px; text-align:left; font-size:10px; }
            td { padding:6px; border-bottom:1px solid #eee; vertical-align:middle; font-size:10px; }
            tr:nth-child(even) td { background:#f9f6ff; }
            .today { background:#fff3cd !important; font-weight:bold; }
            img.avatar { border-radius:50%; object-fit:cover; }
        </style>';
        $html .= '<h2>' . htmlspecialchars($sch_setting->name ?? '') . '</h2>';
        $html .= '<div class="sub">' . htmlspecialchars($sch_setting->address ?? '') . '</div>';
        $html .= '<div class="sub"><b>Student Birthday List &mdash; ' . $date_from . ' to ' . $date_to . '</b></div>';
        $html .= '<table>
            <thead><tr>
                <th width="45">#</th>
                <th width="55">Photo</th>
                <th>Name</th>
                <th>Adm No</th>
                <th>Class</th>
                <th>DOB</th>
                <th>Gender</th>
                <th>Mobile</th>
            </tr></thead><tbody>';

        $today_md = date('md');
        $i = 1;
        foreach ($students as $s) {
            $dob_md     = $s['dob'] ? date('md', strtotime($s['dob'])) : '';
            $is_today   = ($dob_md === $today_md);
            $rowclass   = $is_today ? ' class="today"' : '';
            $name       = trim($s['firstname'] . ' ' . $s['lastname']);
            $dob_fmt    = $s['dob'] ? date($dateFormat, strtotime($s['dob'])) : '—';
            $img_path   = !empty($s['image']) ? FCPATH . ltrim($s['image'], '/') : '';
            $img_tag    = '';
            if ($img_path && file_exists($img_path)) {
                $img_tag = '<img class="avatar" src="' . $img_path . '" width="40" height="40">';
            } else {
                $img_tag = '<div style="width:40px;height:40px;border-radius:50%;background:#e0d4f7;text-align:center;line-height:40px;font-weight:bold;color:#6f42c1;font-size:16px;">'
                    . strtoupper(substr($name, 0, 1)) . '</div>';
            }
            $html .= '<tr' . $rowclass . '>
                <td>' . $i++ . ($is_today ? ' 🎂' : '') . '</td>
                <td>' . $img_tag . '</td>
                <td><b>' . htmlspecialchars($name) . '</b></td>
                <td>' . htmlspecialchars($s['admission_no']) . '</td>
                <td>' . htmlspecialchars($s['class'] . ' – ' . $s['section']) . '</td>
                <td>' . $dob_fmt . '</td>
                <td>' . htmlspecialchars($s['gender'] ?? '') . '</td>
                <td>' . htmlspecialchars($s['mobileno'] ?? '') . '</td>
            </tr>';
        }
        $html .= '</tbody></table>';

        $this->load->library('m_pdf');
        $pdf = $this->m_pdf->load(['format' => 'A4', 'orientation' => 'L']);
        $pdf->WriteHTML($html);
        $pdf->Output('student_birthday_list.pdf', 'D');
    }

    public function export_xls()
    {
        if (!$this->rbac->hasPrivilege('birthday', 'can_view')) {
            access_denied();
        }
        $date_from = $this->input->get('date_from');
        $date_to   = $this->input->get('date_to');
        if (!$date_from || !$date_to) { show_error('Missing date range'); return; }

        require_once APPPATH . 'third_party/vendor/autoload.php';

        $students    = $this->Birthday_model->getByDateRange($date_from, $date_to);
        $sch_setting = $this->sch_setting_detail;
        $dateFormat  = $this->customlib->getSchoolDateFormat();
        $today_md    = date('md');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Student Birthdays');

        // School name / report title
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', ($sch_setting->name ?? '') . ' — Student Birthday List (' . $date_from . ' to ' . $date_to . ')');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->getRowDimension(1)->setRowHeight(20);

        // Headers row 2
        $headers = ['#', 'Photo', 'Student Name', 'Admission No', 'Class', 'DOB', 'Gender', 'Mobile'];
        foreach ($headers as $col => $h) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '2';
            $sheet->setCellValue($cell, $h);
        }
        $hStyle = $sheet->getStyle('A2:H2');
        $hStyle->getFont()->setBold(true)->setColor((new \PhpOffice\PhpSpreadsheet\Style\Color())->setARGB('FFFFFFFF'));
        $hStyle->getFill()->setFillType('solid')->getStartColor()->setARGB('FF6F42C1');
        $hStyle->getAlignment()->setHorizontal('center');
        $sheet->getRowDimension(2)->setRowHeight(18);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(10);
        $sheet->getColumnDimension('C')->setWidth(24);
        $sheet->getColumnDimension('D')->setWidth(14);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(14);

        $row = 3;
        foreach ($students as $i => $s) {
            $name     = trim($s['firstname'] . ' ' . $s['lastname']);
            $dob_md   = $s['dob'] ? date('md', strtotime($s['dob'])) : '';
            $is_today = ($dob_md === $today_md);
            $dob_fmt  = $s['dob'] ? date($dateFormat, strtotime($s['dob'])) : '';

            $sheet->getRowDimension($row)->setRowHeight(45);
            $sheet->setCellValue('A' . $row, ($i + 1) . ($is_today ? ' 🎂' : ''));
            $sheet->setCellValue('C' . $row, $name);
            $sheet->setCellValue('D' . $row, $s['admission_no']);
            $sheet->setCellValue('E' . $row, $s['class'] . ' – ' . $s['section']);
            $sheet->setCellValue('F' . $row, $dob_fmt);
            $sheet->setCellValue('G' . $row, $s['gender'] ?? '');
            $sheet->setCellValue('H' . $row, $s['mobileno'] ?? '');

            if ($is_today) {
                $sheet->getStyle('A' . $row . ':H' . $row)->getFill()
                    ->setFillType('solid')->getStartColor()->setARGB('FFFFF3CD');
            }

            // Embed photo
            $img_path = !empty($s['image']) ? FCPATH . ltrim($s['image'], '/') : '';
            if ($img_path && file_exists($img_path)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Photo');
                $drawing->setPath($img_path);
                $drawing->setCoordinates('B' . $row);
                $drawing->setOffsetX(3);
                $drawing->setOffsetY(3);
                $drawing->setWidth(38);
                $drawing->setHeight(38);
                $drawing->setWorksheet($sheet);
            } else {
                $sheet->setCellValue('B' . $row, substr($name, 0, 1));
                $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal('center')->setVertical('center');
            }

            // Borders
            $sheet->getStyle('A' . $row . ':H' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->getStyle('A' . $row . ':H' . $row)->getAlignment()->setVertical('center');

            $row++;
        }

        // Freeze header
        $sheet->freezePane('A3');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="student_birthday_list.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
