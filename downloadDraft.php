<?php

require_once('config.php');
require_once('saveConfig.php');
require_once('createDatafile.php');
require_once('createZipArchive.php');
require_once('downloadFile.php');
require_once('removeTemporaryFiles.php');
session_start();

// 未ログインのアクセスはホーム画面へ飛ばす
if (is_null($_SESSION['me'])) {
	header('Location: '.SITE_URL);
}

// ConfigをDynamoDBに保存
saveConfig($_POST);

// データファイルにConfigとWorkTimeを書き込む
createDatafile($_POST);

// フォーマット、データファイル、マクロファイルのzipアーカイブを作成する
createZipArchive();

// ファイルをブラウザからダウンロード
$filepath = __DIR__.'/tmp/'.$_SESSION['email'].'/'.BRAND.'.zip';
downloadFile($filepath);

// 一時ファイルを削除
removeTemporaryFiles();

// ページをリダイレクトして戻る
header('Location: '.SITE_URL);
