<?php
// Dummy PHPExcel class
class PHPExcel {
    private $properties;
    private $activeSheet;

    public function __construct() {
        $this->properties = new PHPExcel_DocumentProperties();
        $this->activeSheet = new PHPExcel_Worksheet();
    }

    public function getProperties() {
        return $this->properties;
    }

    public function setActiveSheetIndex($index) {
        // Dummy method
    }

    public function getActiveSheet() {
        return $this->activeSheet;
    }
}

// Dummy PHPExcel_DocumentProperties class
class PHPExcel_DocumentProperties {
    public function setCreator($creator) { return $this; }
    public function setLastModifiedBy($lastModifiedBy) { return $this; }
    public function setTitle($title) { return $this; }
    public function setSubject($subject) { return $this; }
    public function setDescription($description) { return $this; }
    public function setKeywords($keywords) { return $this; }
    public function setCategory($category) { return $this; }
}

// Dummy PHPExcel_Worksheet class
class PHPExcel_Worksheet {
    private $title;
    private $sheetData = [];

    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    public function fromArray($data, $nullValue = null, $startCell = 'A1') {
        // This will just store the data in a simple array
        $this->sheetData[] = $data;
    }
    
    public function setCellValue($cell, $value){
         $this->sheetData[] = $value;
    }
    
    public function getData() {
        return $this->sheetData;
    }
}

// Dummy PHPExcel_IOFactory class
class PHPExcel_IOFactory {
    public static function createWriter($objPHPExcel, $writerType) {
        return new PHPExcel_Writer_Excel5($objPHPExcel);
    }
}

// Dummy PHPExcel_Writer_Excel5 class
class PHPExcel_Writer_Excel5 {
    private $phpExcel;

    public function __construct($objPHPExcel) {
        $this->phpExcel = $objPHPExcel;
    }

    public function save($filename) {
        $sheetData = $this->phpExcel->getActiveSheet()->getData();
        $output = '';

        foreach ($sheetData as $row) {
            // Check if $row is an array before using implode
            if (is_array($row)) {
                $output .= implode("\t", $row) . "\n";
            } else {
                // Handle the case where $row is not an array, e.g., log an error or skip it
                // For now, we just append the value itself
                $output .= $row . "\n";
            }
        }
        
        if (strpos($filename, 'php://') === 0) {
            echo $output;
        } else {
            file_put_contents($filename, $output);
        }
    }
}

