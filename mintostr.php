<?php

function mintostr($min) {
	// 分を時間と分にする
	if ($min < 60) {
		$h = 0;
		$m = $min;
	} else {
		$m = $min % 60;
		$h = ($min - $m) / 60;
	}
	// 数字が1桁なら前に0をつける
	if ($h < 10) {
		$h = "0$h";
	}
	if ($m < 10) {
		$m = "0$m";
	}
	
	$str = "$h:$m";
	return $str;
}
