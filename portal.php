<?php
	require_once('config.php');
	require_once('searchStaffID.php');
	require_once('loadConfig.php');
	require_once('initConfig.php');
	session_start();
	
	// 未ログインのアクセスはホーム画面へ飛ばす
	if (is_null($_SESSION['me'])) {
		header('Location: '.SITE_URL);
		exit;
	}
	
	// 記入する就業月の計算
	// 15日までなら前月。16日以降なら当月。
	if (date('d') < 16) {
		$yearmonth = date('Y年m月',strtotime('last month'));
	} else {
		$yearmonth = date('Y年m月',strtotime('this month'));
	}
	
	$staffID = searchStaffID();
	$config = loadConfig();	// DynamoDBから設定を読み込み
	$config = initConfig($config); // 始業時刻、終業時刻、休憩時間帯が未設定の場合に初期設定を入れる
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title><?php echo BRAND; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="jumbotron-narrow.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="bootstrap/docs/assets/js/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
  <?php include_once("analyticstracking.php") ?>

    <div class="container">
      <div class="header clearfix">
        <nav>
          <ul class="nav nav-pills pull-right">
          	<li class="dropdown">
          		<a href="logout.php" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img src="<?php echo $_SESSION['picture'];?>" height="32px" width="32px" class="img-circle"> ログアウト</a>
            </li>
          </ul>
        </nav>
        <h4 class="text-muted"><?php echo BRAND;?> <span class="glyphicon glyphicon-file" aria-hidden="true"></span></h4>
      </div>

	  <form action="downloadDraft.php" method="post">
      <div class="jumbotron">
        <h1>Draft Download</h1>
        <p class="lead">
        	月末申請書類のドラフトをダウンロードします。
        	WorkTimeLoggerで記録した時刻が反映されます。
    	</p>
    	<div class="row">
    	  <div class="col-sm-3"></div>
    	  <div class="form-group col-sm-6">
		    <label for="YearMonth">申請対象の就業月</label>
		    <input type="text" class="form-control" id="YearMonth" name="就業月" placeholder="YYYY年MM月" value="<?php echo $yearmonth;?>">
		  </div>
		  <div class="col-sm-3"></div>
		  <button type="submit" class="btn btn-success">ダウンロード <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></button>
		</div>
      </div>

	    <h2><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> 設定</h2>
	    <h3>社員情報</h3>
		<div class="row">
		<div class="col-sm-4">
		  <div class="form-group">
		    <label for="Company">会社名</label>
		    <input type="text" class="form-control" id="Company" value="<?php echo COMPANY;?>" disabled>
		    <input type="hidden" name="会社名" value="<?php echo COMPANY;?>">
		  </div>
		</div>
		<div class="col-sm-4">
		  <div class="form-group">
		    <label for="SECTION">部署</label>
		    <input type="text" class="form-control" id="Section" value="<?php echo $staffID['SECTION'];?>" disabled>
		    <input type="hidden" name="部署" value="<?php echo $staffID['SECTION'];?>">
		  </div>
		</div>
		<div class="col-sm-4">
		  <div class="form-group">
		    <label for="Team">班</label>
		    <input type="text" class="form-control" id="Team" value="<?php echo $staffID['TEAM'] == '' ? '' : $staffID['TEAM'].'班';?>" disabled>
		    <input type="hidden" name="班" value="<?php echo $staffID['TEAM'] == '' ? '' : $staffID['TEAM'].'班';?>">
		  </div>
		</div>
		</div>
		<div class="row">
		<div class="col-sm-4">
		  <div class="form-group">
		    <label for="Position">役職名</label>
		    <input type="text" class="form-control" id="Position" value="<?php echo $staffID['POSITION'];?>" disabled>
		    <input type="hidden" name="役職名" value="<?php echo $staffID['POSITION'];?>">
		  </div>
		</div>
		<div class="col-sm-4">
		  <div class="form-group">
		    <label for="Name">氏名</label>
		    <input type="text" class="form-control" id="Name" value="<?php echo $staffID['FAMILY'] . $staffID['NAME'];?>" disabled>
		    <input type="hidden" name="氏名" value="<?php echo $staffID['FAMILY'] . $staffID['NAME'];?>">
		  </div>
		</div>
		</div>
		<h3>勤務報告書（案件先）</h3>
		<div class="row">
		<div class="col-sm-4">
		  <div class="form-group">
		    <label for="Enterprise">就業先企業名</label>
		    <input type="text" class="form-control" id="Enterprise" name="就業先企業名" value="<?php echo $config['就業先企業名'];?>">
		  </div>
		</div>
		<div class="col-sm-4">
		  <div class="form-group">
		    <label for="Project">プロジェクト名</label>
		    <input type="text" class="form-control" id="Project" name="プロジェクト名" value="<?php echo $config['プロジェクト名'];?>">
		  </div>
		</div>
		<div class="col-sm-4">
		  <div class="form-group">
		    <label for="Project">常駐先</label>
		    <input type="text" class="form-control" id="Project" name="常駐先" placeholder="常駐先の地名（最寄駅）・ビル名・階数" value="<?php echo $config['常駐先'];?>">
		  </div>
		</div>
		</div>
		<div class="row">
		<div class="col-sm-4">
		  <div class="form-group">
		    <label for="StartTime">始業時刻</label>
		    <input type="text" class="form-control" id="StartTime" name="始業時刻" placeholder="9:00" value="<?php echo $config['始業時刻'];?>">
		  </div>
		</div>
		<div class="col-sm-4">
		  <div class="form-group">
		    <label for="EndTime">終業時刻</label>
		    <input type="text" class="form-control" id="EndTime" name="終業時刻" placeholder="18:00" value="<?php echo $config['終業時刻'];?>">
		  </div>
		</div>
		<div class="col-sm-4">
		  <div class="form-group">
		    <label for="Company">休憩時間帯</label>
		    <input type="text" class="form-control" id="BreakTime" name="休憩時間帯" placeholder="12:00-13:00" value="<?php echo $config['休憩時間帯'];?>">
		  </div>
		</div>
		</div>
		<h3>案件先（２）</h3>
		<p class="lead">月途中で案件先が変わったときに記入します</p>
		<div class="row">
		<div class="col-sm-4">
		  <div class="form-group">
		    <label for="Enterprise">就業先企業名</label>
		    <input type="text" class="form-control" id="Enterprise" name="就業先企業名2" value="<?php echo $config['就業先企業名2'];?>">
		  </div>
		</div>
		<div class="col-sm-4">
		  <div class="form-group">
		    <label for="Project">プロジェクト名</label>
		    <input type="text" class="form-control" id="Project" name="プロジェクト名2" value="<?php echo $config['プロジェクト名2'];?>">
		  </div>
		</div>
		<div class="col-sm-4">
		  <div class="form-group">
		    <label for="Project">常駐先</label>
		    <input type="text" class="form-control" id="Project" name="常駐先2" placeholder="常駐先の地名（最寄駅）・ビル名・階数" value="<?php echo $config['常駐先2'];?>">
		  </div>
		</div>
		</div>
		<h3>勤務報告書（社内）</h3>
		<div class="row">
		<div class="col-sm-4">
		  <div class="form-group">
		    <label for="assumption">みなし労働</label>
		    <input type="text" class="form-control" id="assumption" name="みなし労働" placeholder="5:00" value="<?php echo $config['みなし労働'];?>">
		  </div>
		</div>
		</div>
		<h3>経費申請書</h3>
		<h4>顧客請求経費・自社請求経費 定義</h4>
		<div class="row">
		<div class="col-sm-4">
		  <div class="form-group">
		    <label for="SalesStaff">担当営業氏名</label>
		    <input type="text" class="form-control" id="SalesStaff" name="担当営業氏名" value="<?php echo $config['担当営業氏名'];?>">
		  </div>
		</div>
		</div>
		  <div class="form-group">
		    <label for="CustomerCost">顧客請求経費</label>
		    <input type="text" class="form-control" id="CustomerCost" name="顧客請求経費" value="<?php echo $config['顧客請求経費'];?>">
		  </div>
		  <div class="form-group">
		    <label for="InnerCost">自社請求経費</label>
		    <input type="text" class="form-control" id="InnerCost" name="自社請求経費" value="<?php echo $config['自社請求経費'];?>">
		  </div>
		<h4>通勤交通費（定期代）</h4>
		<div class="row">
		<div class="col-sm-3">
		  <div class="form-group">
		    <label for="Traffic">交通機関</label>
		    <input type="text" class="form-control" id="Traffic" name="交通機関1" value="<?php echo $config['交通機関1'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <label for="TrafficFrom">経路(From)</label>
		    <input type="text" class="form-control" id="TrafficFrom" name="経路From1" value="<?php echo $config['経路From1'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <label for="TrafficTo">経路(To)</label>
		    <input type="text" class="form-control" id="TrafficTo" name="経路To1" value="<?php echo $config['経路To1'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <label for="TrafficCost">単価</label>
		    <input type="text" class="form-control" id="TrafficCost" name="単価1" value="<?php echo $config['単価1'];?>">
		  </div>
		</div>
		</div>
		<div class="row">
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="Traffic" name="交通機関2" value="<?php echo $config['交通機関2'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="TrafficFrom" name="経路From2" value="<?php echo $config['経路From2'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="TrafficTo" name="経路To2" value="<?php echo $config['経路To2'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="TrafficCost" name="単価2" value="<?php echo $config['単価2'];?>">
		  </div>
		</div>
		</div>
		<div class="row">
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="Traffic" name="交通機関3" value="<?php echo $config['交通機関3'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="TrafficFrom" name="経路From3" value="<?php echo $config['経路From3'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="TrafficTo" name="経路To3" value="<?php echo $config['経路To3'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="TrafficCost" name="単価3" value="<?php echo $config['単価3'];?>">
		  </div>
		</div>
		</div>
		<div class="row">
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="Traffic" name="交通機関4" value="<?php echo $config['交通機関4'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="TrafficFrom" name="経路From4" value="<?php echo $config['経路From4'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="TrafficTo" name="経路To4" value="<?php echo $config['経路To4'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="TrafficCost" name="単価4" value="<?php echo $config['単価4'];?>">
		  </div>
		</div>
		</div>
		<h3>借用物報告書</h3>
		<div class="row">
		<div class="col-sm-2">
		  <div class="form-group">
		    <label for="RentItemAction">区分</label>
		    <select class="form-control" id="RentItemAction" name="借用物区分1">
		      <option value=""></option>
			  <option value="借用" <?php $selected = $config['借用物区分1'] == '借用' ? ' selected' : '' ; echo $selected ?>>借用</option>
			  <option value="返却" <?php $selected = $config['借用物区分1'] == '返却' ? ' selected' : '' ; echo $selected ?>>返却</option>
			  <option value="－" <?php $selected = $config['借用物区分1'] == '－' ? ' selected' : '' ; echo $selected ?>>－（継続）</option>
			</select>
		  </div>
		</div>
		<div class="col-sm-2">
		  <div class="form-group">
		    <label for="RentItemKind">分類</label>
		    <input type="text" class="form-control" id="RentItemKind" name="借用物分類1" value="<?php echo $config['借用物分類1'];?>">
		  </div>
		</div>
		<div class="col-sm-2">
		  <div class="form-group">
		    <label for="RentItem">借用物</label>
		    <input type="text" class="form-control" id="RentItem" name="借用物1" value="<?php echo $config['借用物1'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <label for="RentItemUsage">用途</label>
		    <input type="text" class="form-control" id="RentItemUsage" name="借用物用途1" value="<?php echo $config['借用物用途1'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <label for="RentItemPlace">保管場所</label>
		    <input type="text" class="form-control" id="RentItemPlace" name="借用物保管場所1" value="<?php echo $config['借用物保管場所1'];?>">
		  </div>
		</div>
		</div>
		<div class="row">
		<div class="col-sm-2">
		  <div class="form-group">
		    <select class="form-control" id="RentItemAction" name="借用物区分2">
		      <option value=""></option>
			  <option value="借用" <?php $selected = $config['借用物区分2'] == '借用' ? ' selected' : '' ; echo $selected ?>>借用</option>
			  <option value="返却" <?php $selected = $config['借用物区分2'] == '返却' ? ' selected' : '' ; echo $selected ?>>返却</option>
			  <option value="－" <?php $selected = $config['借用物区分2'] == '－' ? ' selected' : '' ; echo $selected ?>>－（継続）</option>
			</select>
		  </div>
		</div>
		<div class="col-sm-2">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemKind" name="借用物分類2" value="<?php echo $config['借用物分類2'];?>">
		  </div>
		</div>
		<div class="col-sm-2">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItem" name="借用物2" value="<?php echo $config['借用物2'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemUsage" name="借用物用途2" value="<?php echo $config['借用物用途2'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemPlace" name="借用物保管場所2" value="<?php echo $config['借用物保管場所2'];?>">
		  </div>
		</div>
		</div>
		<div class="row">
		<div class="col-sm-2">
		  <div class="form-group">
		    <select class="form-control" id="RentItemAction" name="借用物区分3">
		      <option value=""></option>
			  <option value="借用" <?php $selected = $config['借用物区分3'] == '借用' ? ' selected' : '' ; echo $selected ?>>借用</option>
			  <option value="返却" <?php $selected = $config['借用物区分3'] == '返却' ? ' selected' : '' ; echo $selected ?>>返却</option>
			  <option value="－" <?php $selected = $config['借用物区分3'] == '－' ? ' selected' : '' ; echo $selected ?>>－（継続）</option>
			</select>
		  </div>
		</div>
		<div class="col-sm-2">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemKind" name="借用物分類3" value="<?php echo $config['借用物分類3'];?>">
		  </div>
		</div>
		<div class="col-sm-2">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItem" name="借用物3" value="<?php echo $config['借用物3'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemUsage" name="借用物用途3" value="<?php echo $config['借用物用途3'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemPlace" name="借用物保管場所3" value="<?php echo $config['借用物保管場所3'];?>">
		  </div>
		</div>
		</div>
		<div class="row">
		<div class="col-sm-2">
		  <div class="form-group">
		    <select class="form-control" id="RentItemAction" name="借用物区分4">
		      <option value=""></option>
			  <option value="借用" <?php $selected = $config['借用物区分4'] == '借用' ? ' selected' : '' ; echo $selected ?>>借用</option>
			  <option value="返却" <?php $selected = $config['借用物区分4'] == '返却' ? ' selected' : '' ; echo $selected ?>>返却</option>
			  <option value="－" <?php $selected = $config['借用物区分4'] == '－' ? ' selected' : '' ; echo $selected ?>>－（継続）</option>
			</select>
		  </div>
		</div>
		<div class="col-sm-2">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemKind" name="借用物分類4" value="<?php echo $config['借用物分類4'];?>">
		  </div>
		</div>
		<div class="col-sm-2">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItem" name="借用物4" value="<?php echo $config['借用物4'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemUsage" name="借用物用途4" value="<?php echo $config['借用物用途4'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemPlace" name="借用物保管場所4" value="<?php echo $config['借用物保管場所4'];?>">
		  </div>
		</div>
		</div>
		<div class="row">
		<div class="col-sm-2">
		  <div class="form-group">
		    <select class="form-control" id="RentItemAction" name="借用物区分5">
		      <option value=""></option>
			  <option value="借用" <?php $selected = $config['借用物区分5'] == '借用' ? ' selected' : '' ; echo $selected ?>>借用</option>
			  <option value="返却" <?php $selected = $config['借用物区分5'] == '返却' ? ' selected' : '' ; echo $selected ?>>返却</option>
			  <option value="－" <?php $selected = $config['借用物区分5'] == '－' ? ' selected' : '' ; echo $selected ?>>－（継続）</option>
			</select>
		  </div>
		</div>
		<div class="col-sm-2">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemKind" name="借用物分類5" value="<?php echo $config['借用物分類5'];?>">
		  </div>
		</div>
		<div class="col-sm-2">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItem" name="借用物5" value="<?php echo $config['借用物5'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemUsage" name="借用物用途5" value="<?php echo $config['借用物用途5'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemPlace" name="借用物保管場所5" value="<?php echo $config['借用物保管場所5'];?>">
		  </div>
		</div>
		</div>
		<div class="row">
		<div class="col-sm-2">
		  <div class="form-group">
		    <select class="form-control" id="RentItemAction" name="借用物区分6">
		      <option value=""></option>
			  <option value="借用" <?php $selected = $config['借用物区分6'] == '借用' ? ' selected' : '' ; echo $selected ?>>借用</option>
			  <option value="返却" <?php $selected = $config['借用物区分6'] == '返却' ? ' selected' : '' ; echo $selected ?>>返却</option>
			  <option value="－" <?php $selected = $config['借用物区分6'] == '－' ? ' selected' : '' ; echo $selected ?>>－（継続）</option>
			</select>
		  </div>
		</div>
		<div class="col-sm-2">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemKind" name="借用物分類6" value="<?php echo $config['借用物分類6'];?>">
		  </div>
		</div>
		<div class="col-sm-2">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItem" name="借用物6" value="<?php echo $config['借用物6'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemUsage" name="借用物用途6" value="<?php echo $config['借用物用途6'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemPlace" name="借用物保管場所6" value="<?php echo $config['借用物保管場所6'];?>">
		  </div>
		</div>
		</div>
		<div class="row">
		<div class="col-sm-2">
		  <div class="form-group">
		    <select class="form-control" id="RentItemAction" name="借用物区分7">
		      <option value=""></option>
			  <option value="借用" <?php $selected = $config['借用物区分7'] == '借用' ? ' selected' : '' ; echo $selected ?>>借用</option>
			  <option value="返却" <?php $selected = $config['借用物区分7'] == '返却' ? ' selected' : '' ; echo $selected ?>>返却</option>
			  <option value="－" <?php $selected = $config['借用物区分7'] == '－' ? ' selected' : '' ; echo $selected ?>>－（継続）</option>
			</select>
		  </div>
		</div>
		<div class="col-sm-2">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemKind" name="借用物分類7" value="<?php echo $config['借用物分類7'];?>">
		  </div>
		</div>
		<div class="col-sm-2">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItem" name="借用物7" value="<?php echo $config['借用物7'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemUsage" name="借用物用途7" value="<?php echo $config['借用物用途7'];?>">
		  </div>
		</div>
		<div class="col-sm-3">
		  <div class="form-group">
		    <input type="text" class="form-control" id="RentItemPlace" name="借用物保管場所7" value="<?php echo $config['借用物保管場所7'];?>">
		  </div>
		</div>
		</div>
		<div class="row">
		<div class="col-sm-12">
		  <div class="form-group">
		    <textarea class="form-control" id="textarea" name="借用物懸念事項" placeholder="懸念事項など"><?php $text = $config['借用物懸念事項'] == '' ? $text = $config['借用物懸念事項'] : $text = '懸念事項など' ; echo $text; ?></textarea>
		  </div>
		</div>
		</div>
		<h3>PJ規程報告書</h3>
		<div class="row">
		<div class="col-lg-12">
		  <div class="form-group">
		    <label for="PJ-1">文書やPCを持ち出す際の手順</label>
		    <textarea class="form-control" id="PJ-1" name="PJ規程報告書-1"><?php echo $config['PJ規程報告書-1']; ?></textarea>
		  </div>
		</div>
		</div>
		<div class="row">
		<div class="col-lg-12">
		  <div class="form-group">
		    <label for="PJ-2">持ち出した文書やPCを返却する際の手順</label>
		    <textarea class="form-control" id="PJ-2" name="PJ規程報告書-2"><?php echo $config['PJ規程報告書-2']; ?></textarea>
		  </div>
		</div>
		</div>
		<div class="row">
		<div class="col-lg-12">
		  <div class="form-group">
		    <label for="PJ-3">文書やPCの運搬方法</label>
		    <textarea class="form-control" id="PJ-3" name="PJ規程報告書-3"><?php echo $config['PJ規程報告書-3']; ?></textarea>
		  </div>
		</div>
		</div>
		<div class="row">
		<div class="col-lg-12">
		  <div class="form-group">
		    <label for="PJ-4">事故発生時に連絡するプロジェクト管理者の氏名と電話番号</label>
		    <textarea class="form-control" id="PJ-4" name="PJ規程報告書-4"><?php echo $config['PJ規程報告書-4']; ?></textarea>
		  </div>
		</div>
		</div>
		<div class="row">
		<div class="col-lg-12">
		  <div class="form-group">
		    <label for="PJ-5">事故発生時に行わなければならない行動とその優先順位</label>
		    <textarea class="form-control" id="PJ-5" name="PJ規程報告書-5"><?php echo $config['PJ規程報告書-5']; ?></textarea>
		  </div>
		</div>
		</div>
	  </form>

      <footer class="footer">
      <div align="center">
        <p><a href="https://github.com/m-sakano/MonthlyDraftMaker">MonthlyDraftMaker</a></p>
      </div>
      </footer>

    </div> <!-- /container -->


    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="bootstrap/docs/assets/js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>
