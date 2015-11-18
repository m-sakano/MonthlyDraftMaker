<?php

require_once('config.php');
require_once('createDynamoDBClient.php');
require_once('getConfigItem.php');
session_start();

function loadConfig() {
	$client = createDynamoDBClient();
	$result = getConfigItem($client, $_SESSION['email']);
	
	$f = fopen(__DIR__.'/portal.php','r');
	if ($f) {
	    while (($buffer = fgets($f, 4096)) !== false) {
	        if ($posStart = mb_strpos($buffer, "\$config['")) {
	        	if ($posEnd = mb_strpos($buffer, "']", $posStart)) {
	        		$names[] = mb_substr($buffer, $posStart + 9, $posEnd - $posStart - 9);
	        	}
	        }
	    }
	    if (!feof($f)) {
	        echo "Error: unexpected fgets() fail\n";
	    }
	    fclose($f);
	}

	if (iterator_count($result) > 0) {
		foreach ($result as $item) {
			foreach ($names as $name) { 
				$ret[$name] = openssl_decrypt($item[$name]['S'], 'AES-128-ECB', OpenSSL_ENCRYPT_KEY);
			}
		}
	} else {
		foreach ($names as $name) {
			$ret[$name] = '';
		}
	}
	return $ret;
}
