<?php

// まだログインしていなければ、ログイン画面を表示
// 既にログインしていれば、ポータル画面を表示
session_start();
if (isset($_SESSION['me'])) {
	include_once('./portal.php');
} else {
	include_once('./login.php');
}
