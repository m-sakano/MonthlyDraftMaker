<?php
	require_once('config.php');
	require_once('searchStaffID.php');
	require_once('loadConfig.php');
	require_once('initConfig.php');
	session_start();
	
	// 未ログインのアクセスはホーム画面へ飛ばす
	if (is_null($_SESSION['me'])) {
		header('Location: '.SITE_URL);
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
    	  <div class="col-lg-3"></div>
    	  <div class="form-group col-lg-6">
		    <label for="YearMonth">申請対象の就業月</label>
		    <input type="text" class="form-control" id="YearMonth" name="就業月" placeholder="YYYY年MM月" value="<?php echo $yearmonth;?>">
		  </div>
		  <div class="col-lg-3"></div>
		  <button type="submit" class="btn btn-success">ダウンロード <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></button>
      </div>

	  <div class="row">
	    <h2><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> 設定</h2>
		<div class="col-lg-6">
		  <div class="form-group">
		    <label for="Company">会社名</label>
		    <input type="text" class="form-control" id="Company" value="<?php echo COMPANY;?>" disabled>
		    <input type="hidden" name="会社名" value="<?php echo COMPANY;?>">
		  </div>
		  <div class="form-group">
		    <label for="SECTION">部署</label>
		    <input type="text" class="form-control" id="Section" value="<?php echo $staffID['SECTION'];?>" disabled>
		    <input type="hidden" name="部署" value="<?php echo $staffID['SECTION'];?>">
		  </div>
		  <div class="form-group">
		    <label for="Team">班</label>
		    <input type="text" class="form-control" id="Team" value="<?php echo $staffID['TEAM'] == '' ? '' : $staffID['TEAM'].'班';?>" disabled>
		    <input type="hidden" name="班" value="<?php echo $staffID['TEAM'] == '' ? '' : $staffID['TEAM'].'班';?>">
		  </div>
		  <div class="form-group">
		    <label for="Position">役職名</label>
		    <input type="text" class="form-control" id="Position" value="<?php echo $staffID['POSITION'];?>" disabled>
		    <input type="hidden" name="役職名" value="<?php echo $staffID['POSITION'];?>">
		  </div>
		  <div class="form-group">
		    <label for="Name">氏名</label>
		    <input type="text" class="form-control" id="Name" value="<?php echo $staffID['FAMILY'] . $staffID['NAME'];?>" disabled>
		    <input type="hidden" name="氏名" value="<?php echo $staffID['FAMILY'] . $staffID['NAME'];?>">
		  </div>
		</div>
		<div class="col-lg-6">
		  <div class="form-group">
		    <label for="Enterprise">就業先企業名</label>
		    <input type="text" class="form-control" id="Enterprise" name="就業先企業名" value="<?php echo $config['就業先企業名'];?>">
		  </div>
		  <div class="form-group">
		    <label for="Project">プロジェクト名</label>
		    <input type="text" class="form-control" id="Project" name="プロジェクト名" value="<?php echo $config['プロジェクト名'];?>">
		  </div>
		  <div class="form-group">
		    <label for="StartTime">始業時刻</label>
		    <input type="text" class="form-control" id="StartTime" name="始業時刻" placeholder="09:00" value="<?php echo $config['始業時刻'];?>">
		  </div>
		  <div class="form-group">
		    <label for="EndTime">終業時刻</label>
		    <input type="text" class="form-control" id="EndTime" name="終業時刻" placeholder="18:00" value="<?php echo $config['終業時刻'];?>">
		  </div>
		  <div class="form-group">
		    <label for="Company">休憩時間帯</label>
		    <input type="text" class="form-control" id="BreakTime" name="休憩時間帯" placeholder="12:00-13:00" value="<?php echo $config['休憩時間帯'];?>">
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
