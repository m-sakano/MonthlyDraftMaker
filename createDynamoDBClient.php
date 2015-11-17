<?php

require_once('config.php');
require_once('/usr/share/php/Aws/aws.phar');

use Aws\DynamoDb\DynamoDbClient;

function createDynamoDBClient() {
	try {
		$client = DynamoDbClient::factory(array(
		    'key' => AWS_ACCESS_KEY_ID,
		    'secret' => AWS_SECRET_ACCESS_KEY,
		    'region'  => DynamoDB_REGION
		));
	} catch (exception $e) {
		echo 'DynamoDB接続の例外：', $e->getMessage(), "\n";
		exit;
	}
	return $client;
}
