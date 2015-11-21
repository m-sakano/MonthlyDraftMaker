<?php

require_once('config.php');

function saveConfigItem($client,$email,$config) {
	$item['Email'] = array('S' => $email);
	foreach ($config as $key => $value) {
		if ($value != "") {
			$item[$key] = array('S' => openssl_encrypt($value, 'AES-128-ECB', OpenSSL_ENCRYPT_KEY));
		}
	}
	try {
		$result = $client->putItem(array(
		    'TableName' => DynamoDB_CONFIG_TABLE,
		    'Item' => $item
		));
	} catch (exception $e) {
		echo 'DynamoDB登録の例外：', $e->getMessage(), "<br>";
		echo '再ログインしてリトライしてください';
		exit;
	}
}
