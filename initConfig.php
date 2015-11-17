<?php

function initConfig($config) {
	if ($config['始業時刻'] == false) $config['始業時刻'] = '09:00';
	if ($config['終業時刻'] == false) $config['終業時刻'] = '18:00';
	if ($config['休憩時間帯'] == false) $config['休憩時間帯'] = '12:00-13:00';
	
	return $config;
}
