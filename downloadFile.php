<?php

require_once('config.php');
require_once('createS3Client.php');
session_start();

function downloadFile() {
	$client = createS3Client();
	// Get a command object from the client and pass in any options
	// available in the GetObject command (e.g. ResponseContentDisposition)
	$command = $client->getCommand('GetObject', array(
	    'Bucket' => S3_BUCKET,
	    'Key' => $_SESSION['email'].'/'.BRAND.'.zip',
	    'ResponseContentDisposition' => 'attachment; filename="'.BRAND.'.zip"'
	));
	
	// Create a signed URL from the command object that will last for
	// 10 minutes from the current time
	$signedUrl = $command->createPresignedUrl('+1 minutes');
	//file_get_contents($signedUrl);
	// > Hello!
	header('Location: '.$signedUrl);
}
