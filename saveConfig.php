<?php

require_once('config.php');
require_once('createDynamoDBClient.php');
require_once('saveConfigItem.php');
session_start();

function saveConfig($config) {
	
	$client = createDynamoDBClient();

	saveConfigItem($client, $_SESSION['email'], $config);
}
