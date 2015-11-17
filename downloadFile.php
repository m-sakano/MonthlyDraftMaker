<?php

require_once('config.php');
session_start();

function downloadFile($filepath) {
    header('Content-Disposition: inline; filename="'.basename($filepath).'"');
    header('Content-Length: '.filesize($filepath));
    header('Content-Type: application/octet-stream');
    
    readfile($filepath);
}
