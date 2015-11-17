<?php

require_once('config.php');
require_once('createDynamoDBClient.php');
require_once('getConfigItem.php');
session_start();

function loadConfig() {
	$client = createDynamoDBClient();
	$result = getConfigItem($client, $_SESSION['email']);

	if (iterator_count($result) > 0) {
		foreach ($result as $item) {
			$ret['就業先企業名'] = openssl_decrypt($item['就業先企業名']['S'], 'AES-128-ECB', OpenSSL_ENCRYPT_KEY);
			$ret['プロジェクト名'] = openssl_decrypt($item['プロジェクト名']['S'], 'AES-128-ECB', OpenSSL_ENCRYPT_KEY);
			$ret['始業時刻'] = openssl_decrypt($item['始業時刻']['S'], 'AES-128-ECB', OpenSSL_ENCRYPT_KEY);
			$ret['終業時刻'] = openssl_decrypt($item['終業時刻']['S'], 'AES-128-ECB', OpenSSL_ENCRYPT_KEY);
			$ret['休憩時間帯'] = openssl_decrypt($item['休憩時間帯']['S'], 'AES-128-ECB', OpenSSL_ENCRYPT_KEY);
		}
	} else {
			$ret['就業先企業名'] = '';
			$ret['プロジェクト名'] = '';
			$ret['始業時刻'] = '';
			$ret['終業時刻'] = '';
			$ret['休憩時間帯'] = '';
	}
	return $ret;
}
