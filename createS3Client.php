<?php

require_once('config.php');
require_once('/usr/share/php/Aws/aws.phar');

use Aws\S3\S3Client;

function createS3Client() {
	try {
		$client = S3Client::factory(array(
		    'key' => AWS_ACCESS_KEY_ID,
		    'secret' => AWS_SECRET_ACCESS_KEY,
		    'region'  => S3_REGION
		));
	} catch (exception $e) {
		echo 'S3接続の例外：', $e->getMessage(), "<br>";
		echo '再ログインしてリトライしてください';
		exit;
	}
	return $client;
}
