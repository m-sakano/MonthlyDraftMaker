<?php

require_once('strtomin.php');
require_once('mintostr.php');

function correctWorkTime($worktimes, $config) {
	/* $w 配列作成
		$d: 日付 e.g. 1, 2, 3 ... 31 <= DynamoDBから取得したデータ
		$t: 時刻 e.g. 09:00, 18:00 <= DynamoDBから取得したデータ
		$a: 勤怠 案件先出社, 案件先退社, 自社出社, 自社退社 <= DynamoDBから取得したデータ
		$w: 配列 date, time, attendance, description（備考）
	*/
	$w = array();
	foreach ($worktimes as $worktime) {
		$d = date('d',$worktime['UnixTime']['N']);
		$t = date('H:i',$worktime['UnixTime']['N']);
		$a = $worktime['Attendance']['S'];
		$s = $worktime['Description']['S'];
		$w[] = array('date' => $d, 'time' => $t, 'attendance' => $a, 'description' => $s);
	}
	/* 深夜残業判定と補正- 案件先勤務
		退社時刻が始業時刻以前の場合（ e.g. 0:00〜9:00）、前日の深夜残業と判定
		修正内容: 日付 -1, 時刻 +24:00 e.g. 5日 8:00退社 -> 4日 32:00退社
	*/
	foreach ($w as $key => $val) {
		switch ($val['attendance']) {
			case '案件先退社':
				$startWorkTimeMin = strtomin($config['始業時刻']);
				break;
			case '自社退社':
				$startWorkTimeMin = 9*60; // 始業時刻: 9:00
				break;
			default:
		}
		if (($val['attendance'] == '案件先退社' || $val['attendance'] == '自社退社')
				&& strtomin($val['time']) <= $startWorkTimeMin) {
			$t = mintostr(strtomin($val['time']) + 24*60);
			$w[$key] = array('date' => $val['date'] - 1, 'time' => $t, 'attendance' => $val['attendance'], 'description' => $val['description']);
		}
	}
	// 2回出社日のデータ補正と休憩時間 - 案件先勤務
	/* 休憩時間帯（hh:mm-hh:mm）から開始時刻と終了時刻を切り出す
		$startBreakTime:	hh:mm
		$endBreakTime:		hh:mm
	*/
	$startBreakTime = mb_substr($config['休憩時間帯'], 0, mb_strpos($config['休憩時間帯'], ':'));
	$endBreakTime   = mb_substr($config['休憩時間帯'], mb_strpos($config['休憩時間帯'], ':') + 1,
						mb_strlen($config['休憩時間帯']) - mb_strpos($config['休憩時間帯'], ':') - 1);
	/*
		2回出社判定
		$d: 日付 e.g. 1, 2, 3 ... 31
		bool $is_started[$d]: true 1回出社している false まだ出社していない defalt
		bool $is_ended[$d]:   true 1回退社している false まだ退社していない defalt
	*/ 
	$is_started = array();
	$is_ended   = array();
	for ($i = 1; $i <= 31; $i++) {
		$is_started[$i] = false;
		$is_ended[$i] = false;
 	}
	foreach ($w as $key => $val) {
		if ($val['attendance'] == '案件先出社' || $val['attendance'] == '案件先退社') {
			// 1回目の出社
			if ($is_started[(int)$val['date']] == false && $val['attendance'] == '案件先出社') {
				$startWorkTime1 = $val['time'];
				$is_started[(int)$val['date']] = true;
			}
			// 1回目の退社
			elseif ($is_ended[(int)$val['date']] == false && $val['attendance'] == '案件先退社') {
				$endWorkTime1 = $val['time'];
				$is_ended[(int)$val['date']] = true;
				$key_to_del = $key; // 2回目の退社のときにレコードを削除する
				/*
					出社時刻 と 休憩開始時刻 の遅い方の時刻 から
					退社時刻 と 休憩終了時刻 の早い方の時刻 まで
					を、昼休憩として休憩時間に繰り込む
				*/
				$break1 = min(strtomin($endWorkTime1), strtomin($endBreakTime))
					- max(strtomin($startWorkTime1), strtomin($startBreakTime));
				$d = $val['date'];
				$t = $break1 > 0 ? $break1 : 0;	// $break1が0より大きければ$tに足しこむ
				$a = '案件先休憩時間';
				$s = '';
				$w[] = array('date' => $d, 'time' => mintostr($t), 'attendance' => $a, 'description' => $s);
			}
			// 2回目の出社
			elseif ($is_started[(int)$val['date']] == true && $val['attendance'] == '案件先出社') {
				$startWorkTime2 = $val['time'];
				$descriptionStartWorkTime2 = $val['description'];
				unset ($w[$key]);
			}
			// 2回目の退社
			elseif ($is_ended[(int)$val['date']] == true && $val['attendance'] == '案件先退社') {
				$descriptionEndWorkTime1 = $w[$key_to_del]['description'];
				unset ($w[$key_to_del]);	// 1回目の退社のレコードを削除
				// 2回目の退社のレコードに削除したレコードの備考を追加する
				$s = '';
				$s = $s == '' ? $s . $descriptionEndWorkTime1   : $s . "\n" . $descriptionEndWorkTime1;
				$s = $s == '' ? $s . $descriptionStartWorkTime2 : $s . "\n" . $descriptionStartWorkTime2;
				$s = $s == '' ? $s . $w[$key]['description']    : $s . "\n" . $w[$key]['description'];
				$w[$key]['description'] = $s;
				/* 退社時刻1 と 出社時刻2 の時間帯を休憩時間に繰り込み、descriptionに説明を記載する */
				$endWorkTime2 = $val['time'];
				$d = $val['date'];
				/* 退社時刻1 と 出社時刻2 の時間帯を休憩時間に繰り込む */
				$t = strtomin($startWorkTime2) - strtomin($endWorkTime1);
				/*
					出社時刻1 と 休憩開始時刻 の遅い方の時刻 から
					退社時刻1 と 休憩終了時刻 の早い方の時刻 まで
					を、昼休憩として休憩時間に繰り込む
				*/
				$break1 = min(strtomin($endWorkTime1), strtomin($endBreakTime))
					- max(strtomin($startWorkTime1), strtomin($startBreakTime));
				$t = $break1 > 0 ? $break1 : 0;	// $break1が0より大きければ$tに足しこむ
				/*
					出社時刻2 と 休憩開始時刻 の遅い方の時刻 から
					退社時刻2 と 休憩終了時刻 の早い方の時刻 まで
					を、昼休憩として休憩時間に繰り込む
				*/
				$break2 = min(strtomin($endWorkTime1), strtomin($endBreakTime))
					- max(strtomin($startWorkTime1), strtomin($startBreakTime));
				$t = $break2 > 0 ? $t + $break2 : $t;	// $break1が0より大きければ$tに足しこむ
				$a = '案件先休憩時間';
				$s = '2回出社 ';
				$s .= "$startWorkTime1 - $endWorkTime1, ";
				$s .= "$startWorkTime2 - $endWorkTime2";
				$setBreak = false;
				foreach ($w as $key2 => $val2) {
					if ($val2['date'] == $val['date'] && $val2['attendance'] == '案件先休憩時間') {
						$w[$key2] = array('date' => $d, 'time' => mintostr($t), 'attendance' => $a, 'description' => $s);
						$setBreak = true;
					}
				}
				if ($setBreak == false) {
					$w[] = array('date' => $d, 'time' => mintostr($t), 'attendance' => $a, 'description' => $s);
				}
			}
		}
	}
	// 2回出社日のデータ補正と休憩時間 - 自社勤務
	/* 休憩時間帯（hh:mm-hh:mm）から開始時刻と終了時刻を切り出す
		$startBreakTime:	hh:mm
		$endBreakTime:		hh:mm
	*/
	$startBreakTime = '12:00';
	$endBreakTime   = '13:00';
	/*
		2回出社判定
		$d: 日付 e.g. 1, 2, 3 ... 31
		bool $is_started[$d]: true 1回出社している false まだ出社していない defalt
		bool $is_ended[$d]:   true 1回退社している false まだ退社していない defalt
	*/ 
	$is_started = array();
	$is_ended   = array();
	for ($i = 1; $i <= 31; $i++) {
		$is_started[$i] = false;
		$is_ended[$i] = false;
 	}
	foreach ($w as $key => $val) {
		if ($val['attendance'] == '自社出社' || $val['attendance'] == '自社退社') {
			// 1回目の出社
			if ($is_started[(int)$val['date']] == false && $val['attendance'] == '自社出社') {
				$startWorkTime1 = $val['time'];
				$is_started[(int)$val['date']] = true;
			}
			// 1回目の退社
			elseif ($is_ended[(int)$val['date']] == false && $val['attendance'] == '自社退社') {
				$endWorkTime1 = $val['time'];
				$is_ended[(int)$val['date']] = true;
				$key_to_del = $key; // 2回目の退社のときにレコードを削除
				/*
					出社時刻 と 休憩開始時刻 の遅い方の時刻 から
					退社時刻 と 休憩終了時刻 の早い方の時刻 まで
					を、昼休憩として休憩時間に繰り込む
				*/
				$break1 = min(strtomin($endWorkTime1), strtomin($endBreakTime))
					- max(strtomin($startWorkTime1), strtomin($startBreakTime));
				$d = $val['date'];
				$t = $break1 > 0 ? $break1 : 0;	// $break1が0より大きければ$tに足しこむ
				$a = '自社休憩時間';
				$s = '';
				$w[] = array('date' => $d, 'time' => mintostr($t), 'attendance' => $a, 'description' => $s);
			}
			// 2回目の出社
			elseif ($is_started[(int)$val['date']] == true && $val['attendance'] == '自社出社') {
				$startWorkTime2 = $val['time'];
				$descriptionStartWorkTime2 = $w[$key]['description'];
				unset ($w[$key]);
			}
			// 2回目の退社
			elseif ($is_ended[(int)$val['date']] == true && $val['attendance'] == '自社退社') {
				$descriptionEndWorkTime1 = $w[$key_to_del]['description'];
				unset ($w[$key_to_del]);	// 1回目の退社のレコードを削除
				// 2回目の退社のレコードに削除したレコードの備考を追加する
				$s = '';
				$s = $s == '' ? $s . $descriptionEndWorkTime1   : $s . "\n" . $descriptionEndWorkTime1;
				$s = $s == '' ? $s . $descriptionStartWorkTime2 : $s . "\n" . $descriptionStartWorkTime2;
				$s = $s == '' ? $s . $w[$key]['description']    : $s . "\n" . $w[$key]['description'];
				$w[$key]['description'] = $s;
				/* 退社時刻1 と 出社時刻2 の時間帯を休憩時間に繰り込み、descriptionに説明を記載する */
				$endWorkTime2 = $val['time'];
				$d = $val['date'];
				/* 退社時刻1 と 出社時刻2 の時間帯を休憩時間に繰り込む */
				$t = strtomin($startWorkTime2) - strtomin($endWorkTime1);
				/*
					出社時刻1 と 休憩開始時刻 の遅い方の時刻 から
					退社時刻1 と 休憩終了時刻 の早い方の時刻 まで
					を、昼休憩として休憩時間に繰り込む
				*/
				$break1 = min(strtomin($endWorkTime1), strtomin($endBreakTime))
					- max(strtomin($startWorkTime1), strtomin($startBreakTime));
				$t = $break1 > 0 ? $t + $break1 : $t;	// $break1が0より大きければ$tに足しこむ
				/*
					出社時刻2 と 休憩開始時刻 の遅い方の時刻 から
					退社時刻2 と 休憩終了時刻 の早い方の時刻 まで
					を、昼休憩として休憩時間に繰り込む
				*/
				$break2 = min(strtomin($endWorkTime1), strtomin($endBreakTime))
					- max(strtomin($startWorkTime1), strtomin($startBreakTime));
				$t = $break2 > 0 ? $t + $break2 : $t;	// $break1が0より大きければ$tに足しこむ
				$a = '自社休憩時間';
				$s = '2回出社 ';
				$s .= "$startWorkTime1 - $endWorkTime1, ";
				$s .= "$startWorkTime2 - $endWorkTime2";
				$setBreak = false;
				foreach ($w as $key2 => $val2) {
					if ($val2['date'] == $val['date'] && $val2['attendance'] == '自社休憩時間') {
						$w[$key2] = array('date' => $d, 'time' => mintostr($t), 'attendance' => $a, 'description' => $s);
						$setBreak = true;
					}
				}
				if ($setBreak == false) {
					$w[] = array('date' => $d, 'time' => mintostr($t), 'attendance' => $a, 'description' => $s);
				}
			}
		}
	}
	
	return $w;
}
