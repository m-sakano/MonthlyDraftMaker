<?php

function strtomin($str) {
	$h = (int)mb_substr($str, 0, mb_strpos($str, ':'));
	$m = (int)mb_substr($str, mb_strpos($str, ':') + 1, mb_strlen($str) - mb_strpos($str, ':') - 1);
	return $h * 60 + $m;
}
