<?php

require_once('config.php');
require_once('refreshAccessToken.php');
require_once('getCellText.php');
session_start();


// 社員一覧のエクセルシートからメールアドレスをもとに
// 課、姓、名、を検索して配列で返す
function searchStaffID() {
	$ret = array();			// 戻り値の配列
	
	srand();				// 乱数生成器の初期化
	$hash = md5(rand());	// ランダムからハッシュ値の生成
	
	$fname = dirname(__FILE__)."/tmp/"."$hash".".xlsx";	// ファイル名を設定
	$fp = fopen("$fname", "w+");						// ファイルをr+wで作成

	// AccessTokenをリフレッシュする
	//refreshAccessToken();

	// cURLの処理
	$ch = curl_init();							// cURLの初期化
	$header = array('Authorization: Bearer '.$_SESSION['access_token']);	/// "Authorization": "Bearer <access_token>"
	curl_setopt($ch, CURLOPT_URL, SHEET_URL);	// cURLのURL設定
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_FILE, $fp);		// cURLの出力先ファイル。指定しないとブラウザに表示される。
	curl_setopt($ch, CURLOPT_HEADER, 0);		// 0:HTTPヘッダを含めない
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);	// 1: LOCATIONを追う
	curl_exec($ch);								// cURLでHTTP GETを実行
	fclose($fp);								// ファイルを閉じる
	if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '401') {	// 401 Unauthorized の場合はTokenをRefresh
		refreshAccessToken();
		$fp = fopen($fname, 'w+');
		$header = array('Authorization: Bearer '.$_SESSION['access_token']);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_exec($ch);
		fclose($fp);
		if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '401') {	// TokenをRefresh後も401の場合は強制ログアウト
			curl_close($ch);
			header('Location: '.SITE_URL.'logout.php');
		}
	}
	curl_close($ch);							// cURLを終了

	// xlsxの処理
	require_once dirname(__FILE__) . '/PHPExcel/Classes/PHPExcel/IOFactory.php';
	try {
	    $objPHPExcel = PHPExcel_IOFactory::load($fname);	// Load $fname to a PHPExcel Object
	} catch(PHPExcel_Reader_Exception $e) {
	    die('Error loading file: '.$e->getMessage());
	}
	//$objPHPExcel->setActiveSheetIndexByName(SHEET_NAME);	// Sheetの設定
	$objPHPExcel->setActiveSheetIndex(SHEET_INDEX);			// Sheetの設定
	$sheet = $objPHPExcel->getActiveSheet();				// Sheetを開く";
	// シートからメールアドレスを検索して行番号を得る
	$i=2;		// 2行目から検索する
	$c = $sheet->getCell('B3');
	$s = getCellText($c);
	$a = $c->getValue();
	while ($sheet->getCellByColumnAndRow(SHEET_COL_MAIL,$i)->getValue() != "") {	// 空白セルまでループ
		if ($sheet->getCellByColumnAndRow(SHEET_COL_MAIL,$i)->getValue() == $_SESSION['email']){
			$ret['SECTION']=$sheet->getCellByColumnAndRow(SHEET_COL_SECTION,$i)->getValue();	// 課を取得
			$ret['TEAM']=$sheet->getCellByColumnAndRow(SHEET_COL_TEAM,$i)->getValue();		// 班を取得
			$ret['POSITION']=$sheet->getCellByColumnAndRow(SHEET_COL_POSITION,$i)->getValue();	// 役職を取得
			$ret['FAMILY']=$sheet->getCellByColumnAndRow(SHEET_COL_FAMILY,$i)->getValue();		// 姓を取得
			$ret['NAME']=$sheet->getCellByColumnAndRow(SHEET_COL_NAME,$i)->getValue();			// 名を取得
			break;
		}
		$i++;
	}
	
	unlink($fname);			// ファイルを削除
	
	return $ret;
}

