<?php

require_once('config.php');
session_start();

function removeTemporaryFiles() {
	$userDirectory = __DIR__.'/tmp/'.$_SESSION['email'].'/';
	foreach (dir($userDirectory) as $f) {
		unlink($userDirectory . $f);
		echo $f;
	}
	exit;
	rmdir($userDirectory);
}