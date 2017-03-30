<?php
	require_once ('conf.php');
//	error_reporting(0);
  $mssql = polacz_sql_kp();
  header("Cache-Control: max-age=2592000"); //30days (60sec * 60min * 24hours * 30days);
  header('Content-type: application/javascript');
	$date = new DateTime();
	$swieta = array();
	if (isset($_REQUEST["year"])){
    $query = "SELECT * FROM [wwwkop].[dbo].[kart_pr_swieta] WHERE rok = ".$_REQUEST["year"]." OR rok IS NULL;";
	} else {
    $query = "SELECT * FROM [wwwkop].[dbo].[kart_pr_swieta] WHERE rok = ".$date->format('Y')." OR rok IS NULL;";
	}
  
  $result = sqlsrv_query($mssql, $query);
  if ($result) {
    while($row = get_row_sql($result)) {
      // var_dump($row);
      if (!isset($swieta[$row["miesiac"]]))
        $swieta[$row["miesiac"]] = array();
      $swieta[$row["miesiac"]][$row["dzien"]] = $row["opis"];
    }
  } else {
    echo json_encode(sqlsrv_errors());
  }
  sqlsrv_close($mssql);
	echo 'var swieta = '.json_encode($swieta).';';
	// echo 'var q2 = '.json_encode($query).';';
?>