<?php

require_once('config.php');
require_once('refreshAccessToken.php');
session_start();

/**
 * 指定したセルの文字列を取得する
 *
 * 色づけされたセルなどは cell->getValue()で文字列のみが取得できない
 * また、複数の配列に文字列データが分割されてしまうので、その部分も連結して返す
 *
 *
 * @param  $objCell Cellオブジェクト
 */
function getCellText($objCell = null)
{
     if (is_null($objCell)) {
         return false;
     }

     $txtCell = "";

     //まずはgetValue()を実行
     $valueCell = $objCell->getValue();

     if (is_object($valueCell)) {
         //オブジェクトが返ってきたら、リッチテキスト要素を取得
         $rtfCell = $valueCell->getRichTextElements();
         //配列で返ってくるので、そこからさらに文字列を抽出
         $txtParts = array();
         foreach ($rtfCell as $v) {
            $txtParts[] = $v->getText();
         }
         //連結する
         $txtCell = implode("", $txtParts);

     } else {
         if (!empty($valueCell)) {
             $txtCell = $valueCell;
         }
     }

     return $txtCell;
}

// 社員一覧のエクセルシートからメールアドレスをもとに
// 課、姓、名、を検索して配列で返す
function searchStaffID() {
	$ret = array();			// 戻り値の配列
	
	srand();				// 乱数生成器の初期化
	$hash = md5(rand());	// ランダムからハッシュ値の生成
	
	$fname = dirname(__FILE__)."/tmp/"."$hash".".xlsx";	// ファイル名を設定
	$fp = fopen("$fname", "w+");						// ファイルをr+wで作成

	// AccessTokenの有効期限が切れていたら再取得する
	if ($_SESSION['accesstokenexpire'] < time()) {
		refreshAccessToken();
	}

	// cURLの処理
	$ch = curl_init();							// cURLの初期化
	$header = array('Authorization: Bearer '.$_SESSION['accesstoken']);	/// "Authorization": "Bearer <access_token>"
	curl_setopt($ch, CURLOPT_URL, SHEET_URL);	// cURLのURL設定
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_FILE, $fp);		// cURLの出力先ファイル。指定しないとブラウザに表示される。
	curl_setopt($ch, CURLOPT_HEADER, 0);		// 0:HTTPヘッダを含めない
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);	// 1: LOCATIONを追う
	curl_exec($ch);								// cURLでHTTP GETを実行
	curl_close($ch);							// cURLを終了
	fclose($fp);								// ファイルを閉じる
	
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

