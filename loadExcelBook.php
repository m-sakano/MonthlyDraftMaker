<?php

require_once('config.php');
require_once __DIR__ . '/PHPExcel/Classes/PHPExcel/IOFactory.php';

function loadExcelBook($inputFileName) {
	try {
	    /** Load $inputFileName to a PHPExcel Object  **/
	    $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
	} catch(PHPExcel_Reader_Exception $e) {
	    die('Error loading file: '.$e->getMessage());
	}
	return $objPHPExcel;
}
