<?php

require_once('config.php');

session_start();

if (!isset($_GET['code'])) {
	// 認証前の処理
	
	//認証ダイアログの作成
	// CSRF対策
	$_SESSION['state'] = sha1(uniqid(mt_rand(), true));
	
	$params = array(
		'client_id' => CLIENT_ID,
		'redirect_uri' => SITE_URL.'redirect.php',
		'state' => $_SESSION['state'],
		'approval_prompt' => 'auto',
		'scope' => 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/drive',
		'response_type' => 'code'
	);
	
	// googleへ飛ばす
	$url = 'https://accounts.google.com/o/oauth2/auth?'.http_build_query($params);
	header('Location: '.$url);
	exit;
	
} else {
	// 認証後の処理
	// CSRF対策でstateのチェック
	if ($_SESSION['state'] != $_GET['state']) {
		echo "Ooops! 不正な処理でした！";
		exit;
	}
	// access_takenを取得
	$params = array(
        'client_id' => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'code' => $_GET['code'],
        'redirect_uri' => SITE_URL.'redirect.php',
        'grant_type' => 'authorization_code'
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
    
    // var_dump($json);exit;
	
	// ユーザー情報取得
	$url = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token='.$json->access_token;
	$me = json_decode(file_get_contents($url));
	//var_dump($me);exit;
/*
	// DBを格納
	$dbh = connectDb();
	
	$sql = "select * from users where google_user_id = :id limit 1";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(":id" => $me->id));
	$user = $stmt->fetch();
	
	if (!$user) {
	    $sql = "insert into users
	            (google_user_id, google_email, google_name, google_picture, google_access_token, created, modified)
	            values
	            (:google_user_id, :google_email, :google_name, :google_picture, :google_access_token, now(), now())";
        $stmt = $dbh->prepare($sql);
        $params = array(
            ":google_user_id" => $me->id, 
            ":google_email" => $me->email, 
            ":google_name" => $me->name, 
            ":google_picture" => $me->picture, 
            ":google_access_token" => $json->access_token
        );
        $stmt->execute($params);
        
        $myId = $dbh->lastInsertId();
        $sql = "select * from users where id = :id limit 1";
        $stmt = $dbh->prepare($sql);
        $stmt->execute(array(":id" => $myId));
        $user = $stmt->fetch();
	}
*/

/*	
	// var_dump($user);exit;
	// ログイン処理
	if (isset($user)) {
	    // セッションハイジャック対策
	    session_regenerate_id(true);
	    $_SESSION['me'] = $user;
	}
*/
	// ログイン処理
    session_regenerate_id(true);
    $_SESSION['me'] = $me->id;
    $_SESSION['email'] = $me->email;
    $_SESSION['given_name'] = $me->given_name;
    $_SESSION['family_name'] = $me->family_name;
    $_SESSION['picture'] = $me->picture;
    $_SESSION['accesstoken'] = $json->access_token;
    $_SESSION['refreshtoken'] = $json->refresh_token;
    $_SESSION['accesstokenexpire'] = time() + 3600;
    
    // 不正ドメイン対策
    $emailaddress = explode('@',$me->email);
    if ($emailaddress[1] != APPS_DOMAIN) {
    	header('Location: '.SITE_URL.'logout.php');
    	exit;
    }
	
	// ホーム画面へ飛ばす
	header('Location: '.SITE_URL);
}

