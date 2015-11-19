<?php

require_once('config.php');
require_once('getWorkTimeItem.php');
require_once('createDynamoDBClient.php');
require_once(__DIR__ . '/PHPExcel/Classes/PHPExcel/IOFactory.php');
session_start();

function createDatafile($config) {
	$userDirectory = __DIR__.'/tmp/'.$_SESSION['email'].'/';
	if (!file_exists($userDirectory)) {
		mkdir($userDirectory, 0777, true);
	}
	
	// 就業月変換 'YYYY年MM月' -> 月初めのUnixTime
	$thisMonthUnixTime = strtotime(str_replace(array('年', '月'), '-', $config['就業月']).'01');
	$nextMonthUnixTime = strtotime('+1 Month ' . date('Y-m-d', $thisMonthUnixTime));
	
	// 就業月のWorkTimeをDynamoDBから取得
	$client = createDynamoDBClient();
	$worktimes = getWorkTimeItem($client, $_SESSION['email'], $thisMonthUnixTime, $nextMonthUnixTime);
	
	// ワークブックオブジェクト新規作成
	$objPHPExcel = new PHPExcel();
	
	// ヘッダ情報シート作成
	$configWorkSheet = new PHPExcel_Worksheet($objPHPExcel, '設定');
	$objPHPExcel->addSheet($configWorkSheet, 0);
	// 案件先勤務時間シート作成
	$PjWorktimeWorkSheet = new PHPExcel_Worksheet($objPHPExcel, '案件先勤務時間');
	$objPHPExcel->addSheet($PjWorktimeWorkSheet, 1);
	// 社内勤務時間シート作成
	$InnerWorktimeWorkSheet = new PHPExcel_Worksheet($objPHPExcel, '社内勤務時間');
	$objPHPExcel->addSheet($InnerWorktimeWorkSheet, 2);
	// 不要なワークシートの削除
	$sheetIndex = $objPHPExcel->getIndex($objPHPExcel-> getSheetByName('Worksheet'));
	$objPHPExcel->removeSheetByIndex($sheetIndex);
	
	// 設定書き込み
	$objPHPExcel->setActiveSheetIndexByName('設定');
	$i = 0;
	foreach ($config as $key => $value) {
		$i++;
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $i, $key);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $i, $value);
	}

	// 案件先勤務時間書き込み
	$objPHPExcel->setActiveSheetIndexByName('案件先勤務時間');
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, '日付');
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, '案件先出社');
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, '案件先退社');
	
	for ($row = 2 , $date = $thisMonthUnixTime; $date < $nextMonthUnixTime; $row++, $date += 60*60*24) {
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, date('Y/m/d',$date));
	}

	foreach ($worktimes as $worktime) {
		switch ($worktime['Attendance']['S']) {
			case '案件先出社':
				$row = (int)date('d',$worktime['UnixTime']['N']) + 1;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, date('H:i',$worktime['UnixTime']['N']));
				break;
			case '案件先退社':
				// 始業時刻を0時を起点にした分に変換
				$startHourMinites = split(':',$config['始業時刻']);
				$startMinites = (int)$startHourMinites[0] * 60 + (int)$startHourMinites[1];
				// 退社時刻を0時を起点にした分に変換
				$endMinites = (int)date('H',$worktime['UnixTime']['N']) * 60 + (int)date('i',$worktime['UnixTime']['N']);
				// 退社時刻(H:i)が始業時刻(H:i)以前の場合、前日の深夜残業
				if ($startMinites < $endMinites) {
					// 通常勤務
					$row = (int)date('d',$worktime['UnixTime']['N']) + 1;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, date('H:i',$worktime['UnixTime']['N']));
				} else {
					// 前日の深夜残業
					$row = (int)date('d',$worktime['UnixTime']['N']);
					$hour = (int)date('H',$worktime['UnixTime']['N']) + 24;
					$value = (string)$hour . ':' . (string)date('i',$worktime['UnixTime']['N']);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $value);
				}
				break;
			default:
		}
	}

	// 社内勤務時間書き込み
	$objPHPExcel->setActiveSheetIndexByName('社内勤務時間');
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, '日付');
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, '自社出社');
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, '自社退社');
	
	for ($row = 2 , $date = $thisMonthUnixTime; $date < $nextMonthUnixTime; $row++, $date += 60*60*24) {
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, date('Y/m/d',$date));
	}
	
	foreach ($worktimes as $worktime) {
		switch ($worktime['Attendance']['S']) {
			case '自社出社':
				$row = (int)date('d',$worktime['UnixTime']['N']) + 1;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, date('H:i',$worktime['UnixTime']['N']));
				break;
			case '自社退社':
				// 始業時刻を0時を起点にした分に変換
				$startHourMinites = split(':',$config['始業時刻']);
				$startMinites = (int)$startHourMinites[0] * 60 + (int)$startHourMinites[1];
				// 退社時刻を0時を起点にした分に変換
				$endMinites = (int)date('H',$worktime['UnixTime']['N']) * 60 + (int)date('i',$worktime['UnixTime']['N']);
				// 退社時刻(H:i)が始業時刻(H:i)よりも前の場合、前日の深夜残業
				if ($startMinites < $endMinites) {
					$row = (int)date('d',$worktime['UnixTime']['N']) + 1;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, date('H:i',$worktime['UnixTime']['N']));
				} else {
					$row = (int)date('d',$worktime['UnixTime']['N']);
					$hour = (int)date('H',$worktime['UnixTime']['N']) + 24;
					$value = (string)$hour . ':' . (string)date('i',$worktime['UnixTime']['N']);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $value);
				}
				break;
			default:
		}
	}
	
	// ファイル保存
	$objPHPExcel->setActiveSheetIndexByName('設定');
	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
	$objWriter->save($userDirectory.EXCEL_DATA_FILE_NAME);
}
