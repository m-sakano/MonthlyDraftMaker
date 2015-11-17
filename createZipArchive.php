<?php

require_once('config.php');
session_start();

function createZipArchive() {
	$userdir = __DIR__.'/tmp/'.$_SESSION['email'].'/';
	$zippath = $userdir . BRAND . '.zip';
	if (file_exists($zippath)) {
		unlink($zippath);
	}

	$zip = new ZipArchive();
	$res = $zip->open($zippath, ZipArchive::CREATE) ;
	if ($res === true) {
	    $zip->addFile(__DIR__.'/etc/'.EXCEL_TEMPLATE_FILE_NAME, BRAND.'/data/'.EXCEL_TEMPLATE_FILE_NAME);
	    $zip->addFile(__DIR__.'/etc/'.EXCEL_MACRO_FILE_NAME,    BRAND.'/'.EXCEL_MACRO_FILE_NAME);
	    $zip->addFile($userdir.EXCEL_DATA_FILE_NAME,     BRAND.'/data/'.EXCEL_DATA_FILE_NAME);
	    $zip->close();
	}
}
