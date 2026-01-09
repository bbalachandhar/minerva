<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class PHPExcel {
    private $activeSheet;

    public function __construct() {
        $this->activeSheet = new PHPExcel_Worksheet();
    }

    public function setActiveSheetIndex($index) {
        // This is a dummy implementation
        return;
    }

    public function getActiveSheet() {
        return $this->activeSheet;
    }
}

class PHPExcel_Worksheet {
    private $title;
    private $sheetData = array();

    public function setTitle($title) {
        $this->title = $title;
    }

    public function fromArray($data, $nullValue = null, $startCell = 'A1') {
        // This is a dummy implementation
        // In a real scenario, this would populate the sheet data
        $this->sheetData[] = $data;
    }
}

class PHPExcel_IOFactory {
    public static function createWriter($objPHPExcel, $writerType) {
        // This is a dummy implementation
        return new PHPExcel_Writer_Excel5($objPHPExcel);
    }
}

class PHPExcel_Writer_Excel5 {
    private $phpExcel;

    public function __construct($objPHPExcel) {
        $this->phpExcel = $objPHPExcel;
    }

    public function save($filename) {
        // This is a dummy implementation
        // In a real scenario, this would generate and save the Excel file
        // For now, we'll just create an empty file to avoid errors.
        file_put_contents($filename, '');
    }
}
