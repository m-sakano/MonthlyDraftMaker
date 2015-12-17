<?php

// SITE Settings
define('SITE_URL', 'http://www.mydomain/MonthlyDraftMaker/');
define('BRAND', 'MonthlyDraftMaker');

// Cookie Settings
session_set_cookie_params(0, '/MonthlyDraftMaker/');

// Domain Settings
define('APPS_DOMAIN','****.co.jp');
define('COMPANY','株式会社****');

// Google Authentication Settings
define('CLIENT_ID', '********');
define('CLIENT_SECRET', '********');

// Google SpreadSheet Settings
define('SHEET_URL', '********');
define('SHEET_INDEX', 0);			// ブック内のシート番号。最初のシートは0
define('SHEET_COL_MAIL', 14);		// メールアドレスの列番号。列番号は0から始まる。
define('SHEET_COL_SECTION', 3);		// 課の列番号
define('SHEET_COL_TEAM', 4);		// 班の列番号
define('SHEET_COL_POSITION', 5);	// 役職の列番号
define('SHEET_COL_FAMILY', 6);		// 姓の列番号
define('SHEET_COL_NAME', 7);		// 名の列番号

// AWS Settings
define('DynamoDB_WORKTIME_TABLE', 'WorkTimeLogger');
define('DynamoDB_CONFIG_TABLE', 'MonthlyDraftMakerConfig');
define('DynamoDB_REGION', 'ap-northeast-1');
define('AWS_ACCESS_KEY_ID','********');
define('AWS_SECRET_ACCESS_KEY','********');
define('OpenSSL_ENCRYPT_KEY','********');
define('OpenSSL_ENCRYPT_METHOD','AES-256-ECB');

// Monthly Report Setting
define('EXCEL_TEMPLATE_FILE_NAME', '********.xlsx');
define('EXCEL_MACRO_FILE_NAME', 'MonthlyDraftMaker.xlsm');
define('EXCEL_DATA_FILE_NAME', 'data.xlsx');

// PHP error reporting
error_reporting(E_ALL &~E_NOTICE);
//ini_set( 'display_errors', 1 );

// Server Locale
setlocale(LC_ALL, 'ja_JP.UTF-8');

// timezone
date_default_timezone_set('Asia/Tokyo');


