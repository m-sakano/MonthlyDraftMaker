# MonthlyDraftMaker
月末申請書類のドラフトを作成するヘルパーアプリケーション

PHPExcelでテンプレートに記入して保存すると書式が大きく崩れる。
PHPでExcelを扱えるライブラリがほかに見当たらないのでPythonのライブラリをテストしたが、最もまともなopenpyxlでさえ書式が多少崩れる。
主な原因はNamedRangeにある様子。

サーバ側で編集するのは困難であるため、クライアント側でマクロ処理する。

月末申請書ドラフト_氏名_YYYYMM.zip
/MonthlyDraftMaker.xlsm
/etc/月末申請書_(課)_(氏名)_(yyyyMM).xlsx
/etc/data.xlsx
