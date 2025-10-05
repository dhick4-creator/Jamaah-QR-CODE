<?php
require_once 'phpexcel/Classes/PHPExcel.php';

$objPHPExcel = new PHPExcel();
$sheet = $objPHPExcel->getActiveSheet();

// Set headers
$sheet->setCellValue('A1', 'NIS');
$sheet->setCellValue('B1', 'NISN');
$sheet->setCellValue('C1', 'Nama');
$sheet->setCellValue('D1', 'Kelas');

// Set column widths
$sheet->getColumnDimension('A')->setWidth(10);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(30);
$sheet->getColumnDimension('D')->setWidth(10);

// Save as Excel
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('template_siswa.xlsx');

echo "Template generated successfully.";
?>


