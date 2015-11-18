<?php

require_once('config.php');
session_start();

function refreshAccessToken() {
	// access_takenを取得
	$params = array(
        'client_id' => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'refresh_token' => $_SESSION['refresh_token'],
        'grant_type' => 'refresh_token'
    );
    $url = 'https://accounts.google.com/o/oauth2/token';
    
    // php5-curlパッケージをインストールしておく
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	
    $rs = curl_exec($curl);
    curl_close($curl);
    $json = json_decode($rs);
    
    $_SESSION['access_token'] = $json->access_token;
    $_SESSION['refresh_token'] = $json->refresh_token;
}
