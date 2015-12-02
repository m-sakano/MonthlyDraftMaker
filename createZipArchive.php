<?php

require_once('config.php');
require_once('createS3Client.php');

function createZipArchive() {
	// S3クライアントをつくる
	$client = createS3Client();
	// テンプレートとマクロを取得
	$template = $client->getObject(array(
	    'Bucket' => S3_BUCKET,
	    'Key'    => EXCEL_TEMPLATE_FILE_NAME
	));
	$macro = $client->getObject(array(
	    'Bucket' => S3_BUCKET,
	    'Key'    => EXCEL_MACRO_FILE_NAME
	));
	
	// zipアーカイブのための一時ディレクトリをつくる
	$userdir = __DIR__.'/tmp/'.$_SESSION['email'].'/';
	$zippath = $userdir . BRAND . '.zip';
	if (file_exists($zippath)) {
		unlink($zippath);
	}

	// ZIPアーカイブを作る
	$zip = new ZipArchive();
	$res = $zip->open($zippath, ZipArchive::CREATE) ;
	if ($res === true) {
	    $zip->addFromString(BRAND.'/data/'.EXCEL_TEMPLATE_FILE_NAME, $template['Body']);
	    $zip->addFromString(BRAND.'/'.EXCEL_MACRO_FILE_NAME,         $macro['Body']);
	    $zip->addFile($userdir.EXCEL_DATA_FILE_NAME,     BRAND.'/data/'.EXCEL_DATA_FILE_NAME);
	    $zip->close();
	}
	
	// S3にアップロードする
	$result = $client->putObject(array(
	    'Bucket'     => S3_BUCKET,
	    'Key'        => $_SESSION['email'].'/'.BRAND.'.zip',
	    'SourceFile' => $zippath
    ));
    
    // S3オブジェクトにアクセスできるようになるまで待つ
	$client->waitUntil('ObjectExists', array(
	    'Bucket' => S3_BUCKET,
	    'Key'    => $_SESSION['email'].'/'.BRAND.'.zip'
	));

	// 一時ファイルを削除する
	if (file_exists($userdir)) {
		if ($files = scandir($userdir)) {
			foreach ($files as $file) {
				if (!is_dir($userdir.$file)) {
					unlink($userdir.$file);
				}
			}
		}
		rmdir($userdir);
	}
}
